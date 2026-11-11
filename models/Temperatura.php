<?php
class Temperatura extends Conectar
{
    // Obtener todos los sitios activos de la tabla proporcionada
    public function get_sitios()
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Basado en la lista de sitios: Pomoca, Jalpa, etc.
        $sql = "SELECT * FROM tm_sitios";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_temperaturas_grafico($sitio_id, $f_inicio, $f_fin)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT fecha_hora, temperatura FROM tm_temperaturas 
            WHERE sitio_id = ? AND fecha_hora BETWEEN ? AND ? 
            ORDER BY fecha_hora ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$sitio_id, $f_inicio, $f_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener temperaturas de la semana para precargar los inputs
    public function get_temperaturas_por_rango($fecha_inicio, $fecha_fin)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Se agrega el JOIN para obtener el nombre y apellido del usuario
        $sql = "SELECT t.*, u.usu_nom, u.usu_ape 
            FROM tm_temperaturas t
            LEFT JOIN tm_usuario u ON t.usu_id = u.usu_id
            WHERE t.fecha_hora BETWEEN ? AND ? 
            ORDER BY t.fecha_hora ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Guardar o actualizar registro
    public function guardar_temperatura($fecha_hora, $temp, $sitio_id, $usu_id, $sobrescribir = true)
    {
        $conectar = parent::conexion();
        parent::set_names();

        $stmt = $conectar->prepare(
            "SELECT temp_id, temperatura FROM tm_temperaturas WHERE fecha_hora = ? AND sitio_id = ?"
        );
        $stmt->execute([$fecha_hora, $sitio_id]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            if (!$sobrescribir) {
                return true;   // respetar lo ya capturado
            }
            // Mismo valor: no tocar el usuario que lo registró
            if (
                $existe['temperatura'] !== null
                && abs((float) $existe['temperatura'] - (float) $temp) < 0.0001
            ) {
                return true;
            }
            $stmt = $conectar->prepare("UPDATE tm_temperaturas SET temperatura = ?, usu_id = ? WHERE temp_id = ?");
            return $stmt->execute([$temp, $usu_id, $existe['temp_id']]);
        }

        $stmt = $conectar->prepare(
            "INSERT INTO tm_temperaturas (fecha_hora, temperatura, sitio_id, usu_id) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$fecha_hora, $temp, $sitio_id, $usu_id]);
    }
}
?>