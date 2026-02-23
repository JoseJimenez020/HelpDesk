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
        // Consulta para obtener registros entre dos fechas cronológicamente
        $sql = "SELECT * FROM tm_temperaturas 
                WHERE fecha_hora BETWEEN ? AND ? 
                ORDER BY fecha_hora ASC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Guardar o actualizar registro
    public function guardar_temperatura($fecha_hora, $temp, $sitio_id, $usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Primero verificamos si ya existe un registro para ese sitio en esa hora exacta
        $sql_check = "SELECT temp_id FROM tm_temperaturas WHERE fecha_hora = ? AND sitio_id = ?";
        $stmt = $conectar->prepare($sql_check);
        $stmt->execute([$fecha_hora, $sitio_id]);
        $existe = $stmt->fetch();

        if ($existe) {
            $sql = "UPDATE tm_temperaturas SET temperatura = ?, usu_id = ? WHERE temp_id = ?";
            $stmt = $conectar->prepare($sql);
            return $stmt->execute([$temp, $usu_id, $existe['temp_id']]);
        } else {
            $sql = "INSERT INTO tm_temperaturas (fecha_hora, temperatura, sitio_id, usu_id) VALUES (?, ?, ?, ?)";
            $stmt = $conectar->prepare($sql);
            return $stmt->execute([$fecha_hora, $temp, $sitio_id, $usu_id]);
        }
    }
}
?>