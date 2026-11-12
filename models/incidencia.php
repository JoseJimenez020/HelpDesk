<?php
class Incidencia extends Conectar
{
    /**
     * Inserta una nueva incidencia y devuelve el ID generado, o false en error.
     */
    public function insertar($usu_id, $anio, $mes, $quincena, $prima_dominical, $hrs_extra, $festivo_laborado, $faltas)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "INSERT INTO incidencias
                    (usu_id, anio, mes, quincena, prima_dominical, hrs_extra, festivo_laborado, faltas, fech_crea)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conectar->prepare($sql);
        $ok   = $stmt->execute([$usu_id, $anio, $mes, $quincena,
                                 $prima_dominical, $hrs_extra, $festivo_laborado, $faltas]);
        return $ok ? $conectar->lastInsertId() : false;
    }

    /**
     * Inserta una fecha asociada a una incidencia con su tipo.
     * $tipo: 'prima' | 'hrs_extra' | 'festivo' | 'falta'
     */
    public function insertar_fecha($incidencia_id, $fecha, $tipo)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql  = "INSERT INTO incidencias_fechas (incidencia_id, tipo, fecha) VALUES (?, ?, ?)";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$incidencia_id, $tipo, $fecha]);
    }

    /**
     * Borra todas las fechas de una incidencia (usado al editar para reemplazarlas).
     * Si se pasa $tipo, borra solo las de ese tipo.
     */
    public function eliminar_fechas($incidencia_id, $tipo = null)
    {
        $conectar = parent::conexion();
        parent::set_names();
        if ($tipo) {
            $sql  = "DELETE FROM incidencias_fechas WHERE incidencia_id = ? AND tipo = ?";
            $stmt = $conectar->prepare($sql);
            return $stmt->execute([$incidencia_id, $tipo]);
        }
        $sql  = "DELETE FROM incidencias_fechas WHERE incidencia_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$incidencia_id]);
    }

    /**
     * Lista todas las incidencias de una quincena.
     * Devuelve las fechas de cada tipo en columnas separadas mediante GROUP_CONCAT condicional.
     */
    public function listar_x_quincena($anio, $mes, $quincena)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT
                    i.incidencia_id,
                    i.usu_id,
                    i.prima_dominical,
                    i.hrs_extra,
                    i.festivo_laborado,
                    i.faltas,
                    i.fech_crea,
                    u.usu_nom,
                    u.usu_ape,
                    GROUP_CONCAT(CASE WHEN f.tipo = 'prima'
                        THEN f.fecha END ORDER BY f.fecha SEPARATOR ',') AS fechas_prima,
                    GROUP_CONCAT(CASE WHEN f.tipo = 'hrs_extra'
                        THEN f.fecha END ORDER BY f.fecha SEPARATOR ',') AS fechas_hrs,
                    GROUP_CONCAT(CASE WHEN f.tipo = 'festivo'
                        THEN f.fecha END ORDER BY f.fecha SEPARATOR ',') AS fechas_festivo,
                    GROUP_CONCAT(CASE WHEN f.tipo = 'falta'
                        THEN f.fecha END ORDER BY f.fecha SEPARATOR ',') AS fechas_falta
                FROM incidencias i
                INNER JOIN tm_usuario u ON i.usu_id = u.usu_id
                LEFT JOIN  incidencias_fechas f ON i.incidencia_id = f.incidencia_id
                WHERE i.anio = ? AND i.mes = ? AND i.quincena = ?
                GROUP BY i.incidencia_id
                ORDER BY u.usu_nom ASC, u.usu_ape ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$anio, $mes, $quincena]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve una incidencia por su ID (incluye nombre de usuario).
     */
    public function obtener_x_id($incidencia_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT i.*, u.usu_nom, u.usu_ape
                FROM incidencias i
                INNER JOIN tm_usuario u ON i.usu_id = u.usu_id
                WHERE i.incidencia_id = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $incidencia_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve las fechas de una incidencia agrupadas por tipo.
     * Resultado: ['prima' => ['2026-11-02', ...], 'hrs_extra' => [...], ...]
     */
    public function obtener_fechas_x_id($incidencia_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql  = "SELECT tipo, fecha
                 FROM incidencias_fechas
                 WHERE incidencia_id = ?
                 ORDER BY tipo ASC, fecha ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $incidencia_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por tipo en un objeto asociativo
        $grupos = ['prima' => [], 'hrs_extra' => [], 'festivo' => [], 'falta' => []];
        foreach ($rows as $r) {
            if (isset($grupos[$r['tipo']])) {
                $grupos[$r['tipo']][] = $r['fecha'];
            }
        }
        return $grupos;
    }

    /**
     * Actualiza los campos numéricos de una incidencia.
     */
    public function actualizar($incidencia_id, $prima_dominical, $hrs_extra, $festivo_laborado, $faltas)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql  = "UPDATE incidencias
                 SET prima_dominical = ?, hrs_extra = ?, festivo_laborado = ?, faltas = ?
                 WHERE incidencia_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$prima_dominical, $hrs_extra, $festivo_laborado, $faltas, $incidencia_id]);
    }

    /**
     * Elimina una incidencia; las fechas se borran en cascade por la FK.
     */
    public function eliminar($incidencia_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql  = "DELETE FROM incidencias WHERE incidencia_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$incidencia_id]);
    }

    /**
     * Verifica si ya existe una incidencia para el usuario en la quincena indicada.
     */
    public function existe($usu_id, $anio, $mes, $quincena)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql  = "SELECT incidencia_id FROM incidencias
                 WHERE usu_id = ? AND anio = ? AND mes = ? AND quincena = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$usu_id, $anio, $mes, $quincena]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>