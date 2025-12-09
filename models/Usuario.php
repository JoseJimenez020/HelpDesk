<?php
class Usuario extends Conectar
{

    public function login()
    {
        $conectar = parent::conexion();
        parent::set_names();
        if (isset($_POST["enviar"])) {
            $correo = $_POST["usu_correo"];
            $pass = $_POST["usu_pass"];
            $rol = $_POST["rol_id"];
            if (empty($correo) and empty($pass)) {
                header("Location:" . conectar::ruta() . "index.php?m=2");
                exit();
            } else {
                $sql = "SELECT * FROM tm_usuario WHERE usu_correo=? and usu_pass=MD5(?) and rol_id=? and est=1";
                $stmt = $conectar->prepare($sql);
                $stmt->bindValue(1, $correo);
                $stmt->bindValue(2, $pass);
                $stmt->bindValue(3, $rol);
                $stmt->execute();
                $resultado = $stmt->fetch();
                if (is_array($resultado) and count($resultado) > 0) {
                    $_SESSION["usu_id"] = $resultado["usu_id"];
                    $_SESSION["usu_nom"] = $resultado["usu_nom"];
                    $_SESSION["usu_ape"] = $resultado["usu_ape"];
                    $_SESSION["rol_id"] = $resultado["rol_id"];
                    header("Location:" . Conectar::ruta() . "view/Home/");
                    exit();
                } else {
                    header("Location:" . Conectar::ruta() . "index.php?m=1");
                    exit();
                }
            }
        }
    }

    public function insert_usuario($usu_nom, $usu_ape, $usu_correo, $usu_pass, $rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "INSERT INTO tm_usuario (usu_id, usu_nom, usu_ape, usu_correo, usu_pass, rol_id, fech_crea, fech_modi, fech_elim, est) VALUES (NULL,?,?,?,MD5(?),?,now(), NULL, NULL, '1');";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_nom);
        $sql->bindValue(2, $usu_ape);
        $sql->bindValue(3, $usu_correo);
        $sql->bindValue(4, $usu_pass);
        $sql->bindValue(5, $rol_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function update_usuario($usu_id, $usu_nom, $usu_ape, $usu_correo, $usu_pass, $rol_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE tm_usuario set
                usu_nom = ?,
                usu_ape = ?,
                usu_correo = ?,
                usu_pass = ?,
                rol_id = ?
                WHERE
                usu_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_nom);
        $sql->bindValue(2, $usu_ape);
        $sql->bindValue(3, $usu_correo);
        $sql->bindValue(4, $usu_pass);
        $sql->bindValue(5, $rol_id);
        $sql->bindValue(6, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function delete_usuario($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "call sp_d_usuario_01(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_usuario()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "call sp_l_usuario_01()";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_usuario_x_rol()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT * FROM tm_usuario where est=1 and rol_id=2";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_usuario_x_id($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "call sp_l_usuario_02(?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

   public function get_usuario_total_x_id($usu_id, $start_date = null, $end_date = null)
{
    $conectar = parent::conexion();
    parent::set_names();
    if ($start_date !== null && $end_date !== null) {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE fech_crea BETWEEN ? AND ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
        $stmt->bindValue(2, $start_date);
        $stmt->bindValue(3, $end_date);
    } else {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE usu_id = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}


public function get_usuario_totalabierto_x_id($usu_id, $start_date = null, $end_date = null)
{
    $conectar = parent::conexion();
    parent::set_names();
    if ($start_date !== null && $end_date !== null) {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE tick_estado='Abierto' AND fech_crea BETWEEN ? AND ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
        $stmt->bindValue(2, $start_date);
        $stmt->bindValue(3, $end_date);
    } else {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE usu_id = ? AND tick_estado='Abierto'";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}


    public function get_usuario_totalespera_x_id($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket and tick_estado='En espera'";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

   public function get_usuario_totalcerrado_x_id($usu_id, $start_date = null, $end_date = null)
{
    $conectar = parent::conexion();
    parent::set_names();
    if ($start_date !== null && $end_date !== null) {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE tick_estado='Cerrado' AND fech_crea BETWEEN ? AND ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
        $stmt->bindValue(2, $start_date);
        $stmt->bindValue(3, $end_date);
    } else {
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket WHERE tick_estado='Cerrado'";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $usu_id);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}


    public function get_usuario_grafico($usu_id, $start_date = null, $end_date = null)
    {
        $conectar = parent::conexion();
        parent::set_names();

        if ($start_date !== null && $end_date !== null) {
            $sql = "SELECT tm_categoria.cat_nom as nom, COUNT(*) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                  AND tm_ticket.usu_id = ?
                  AND tm_ticket.fech_crea BETWEEN ? AND ?
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id);
            $stmt->bindValue(2, $start_date);
            $stmt->bindValue(3, $end_date);
        } else {
            $sql = "SELECT tm_categoria.cat_nom as nom, COUNT(*) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                  AND tm_ticket.usu_id = ?
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $usu_id);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function get_usuario_grafico_tiempo($start_date = null, $end_date = null)
    {
        $conectar = parent::conexion();
        parent::set_names();

        if ($start_date !== null && $end_date !== null) {
            $sql = "SELECT tm_categoria.cat_nom as nom, ROUND(AVG(tm_ticket.tiempo_acumulado), 2) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                  AND tm_ticket.tick_estado = 'Cerrado'
                  AND tm_ticket.fech_crea BETWEEN ? AND ?
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $stmt = $conectar->prepare($sql);
            $stmt->bindValue(1, $start_date);
            $stmt->bindValue(2, $end_date);
        } else {
            $sql = "SELECT tm_categoria.cat_nom as nom, ROUND(AVG(tm_ticket.tiempo_acumulado), 2) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                  AND tm_ticket.tick_estado = 'Cerrado'
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $stmt = $conectar->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function get_semanas_disponibles()
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Agrupamos por YEARWEEK con modo 1 (semana empieza lunes) y calculamos inicio y fin
        $sql = "SELECT 
                MIN(DATE(fech_crea)) AS min_date,
                YEARWEEK(fech_crea, 1) AS yw
            FROM tm_ticket
            WHERE fech_crea IS NOT NULL
            GROUP BY yw
            ORDER BY min_date DESC";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $result = array();
        foreach ($rows as $r) {
            $yw = $r['yw'];
            // Convertir YEARWEEK a fecha inicio (lunes) y fin (domingo)
            // Usamos STR_TO_DATE para reconstruir: CONCAT(year, week)
            $min_date = $r['min_date'];
            $ts = strtotime($min_date);
            // Obtener lunes de esa semana
            $weekday = date('N', $ts); // 1 (Mon) .. 7 (Sun)
            $monday_ts = strtotime('-' . ($weekday - 1) . ' days', $ts);
            $sunday_ts = strtotime('+6 days', $monday_ts);
            $start = date('Y-m-d', $monday_ts);
            $end = date('Y-m-d', $sunday_ts);
            $result[] = array('start_date' => $start, 'end_date' => $end);
        }
        return $result;
    }

    public function update_usuario_pass($usu_id, $usu_pass)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE tm_usuario
                SET
                    usu_pass = MD5(?)
                WHERE
                    usu_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_pass);
        $sql->bindValue(2, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

}
?>