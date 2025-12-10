<?php
class Ticket extends Conectar
{

    public function listar_ticket_filtrado($estado = '', $cliente_id = '', $fecha_ini = '', $fecha_fin = '', $usu_id = null)
    {
        $conectar = parent::conexion();
        parent::set_names();

        $sql = "SELECT
            tm_ticket.tick_id,
            tm_ticket.usu_id,
            tm_ticket.cat_id,
            tm_ticket.tick_titulo,
            tm_ticket.tick_descrip,
            tm_ticket.pot_antes,
            tm_ticket.pot_desp,
            tm_ticket.cli_id,
            tm_ticket.tick_estado,
            (tm_ticket.tiempo_acumulado + IF(tm_ticket.tick_estado = 'Abierto', TIMESTAMPDIFF(MINUTE, tm_ticket.fech_estado_ultimo, NOW()), 0)) as tiempo_total_minutos,
            tm_ticket.fech_crea,
            tm_ticket.usu_asig,
            tm_ticket.fech_asig,
            tm_usuario.usu_nom,
            tm_usuario.usu_ape,
            tm_categoria.cat_nom,
            tm_clientes.cli_nom,
            tm_clientes.cli_ape
            FROM 
            tm_ticket
            INNER JOIN tm_categoria on tm_ticket.cat_id = tm_categoria.cat_id
            INNER JOIN tm_usuario on tm_ticket.usu_id = tm_usuario.usu_id
            LEFT JOIN tm_clientes on tm_ticket.cli_id = tm_clientes.cli_id
            WHERE tm_ticket.est = 1";

        $params = array();

        if (!empty($usu_id)) {
            $sql .= " AND tm_ticket.usu_id = ?";
            $params[] = $usu_id;
        }

        if (!empty($estado)) {
            $sql .= " AND tm_ticket.tick_estado = ?";
            $params[] = $estado;
        }

        if (!empty($cliente_id)) {
            $sql .= " AND tm_ticket.cli_id = ?";
            $params[] = $cliente_id;
        }

        // Filtro por rango de fechas sobre fech_crea
        if (!empty($fecha_ini) && !empty($fecha_fin)) {
            $sql .= " AND DATE(tm_ticket.fech_crea) BETWEEN ? AND ?";
            $params[] = $fecha_ini;
            $params[] = $fecha_fin;
        } elseif (!empty($fecha_ini)) {
            $sql .= " AND DATE(tm_ticket.fech_crea) >= ?";
            $params[] = $fecha_ini;
        } elseif (!empty($fecha_fin)) {
            $sql .= " AND DATE(tm_ticket.fech_crea) <= ?";
            $params[] = $fecha_fin;
        }

        $sql .= " ORDER BY tm_ticket.fech_crea DESC";

        $stmt = $conectar->prepare($sql);

        if (count($params) > 0) {
            foreach ($params as $i => $p) {
                $stmt->bindValue($i + 1, $p);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function insert_ticket($usu_id, $cli_id, $cat_id, $tick_titulo, $tick_descrip, $pot_antes, $pot_desp)
    {
        $conectar = parent::conexion();
        parent::set_names();

        // Se agregaron las columnas pot_antes y pot_desp en el INSERT y los signos ? en VALUES
        $sql = "INSERT INTO tm_ticket 
                (tick_id,usu_id,cli_id,cat_id,tick_titulo,tick_descrip,pot_antes,pot_desp,tick_estado,fech_crea,usu_asig,fech_asig,est, tiempo_acumulado, fech_estado_ultimo) 
                VALUES 
                (NULL,?,?,?,?,?,?,?,'Abierto',now(),NULL,NULL,'1', 0, now());";

        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->bindValue(2, $cli_id);
        $sql->bindValue(3, $cat_id);
        $sql->bindValue(4, $tick_titulo);
        $sql->bindValue(5, $tick_descrip);
        // Nuevos bindValue para los parámetros 6 y 7
        $sql->bindValue(6, $pot_antes);
        $sql->bindValue(7, $pot_desp);

        $sql->execute();

        $sql1 = "select last_insert_id() as 'tick_id';";
        $sql1 = $conectar->prepare($sql1);
        $sql1->execute();
        return $resultado = $sql1->fetchAll(pdo::FETCH_ASSOC);
    }

    public function listar_ticket_x_usu($usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT 
            tm_ticket.tick_id,
            tm_ticket.usu_id,
            tm_ticket.cat_id,
            tm_ticket.tick_titulo,
            tm_ticket.tick_descrip,
            tm_ticket.pot_antes,
            tm_ticket.pot_desp,
            tm_ticket.tick_estado,
            (tm_ticket.tiempo_acumulado + IF(tm_ticket.tick_estado = 'Abierto', TIMESTAMPDIFF(MINUTE, tm_ticket.fech_estado_ultimo, NOW()), 0)) as tiempo_total_minutos,
            tm_ticket.fech_crea,
            tm_ticket.usu_asig,
            tm_ticket.fech_asig,
            tm_usuario.usu_nom,
            tm_usuario.usu_ape,
            tm_categoria.cat_nom,
            tm_clientes.cli_nom,
            tm_clientes.cli_ape
            FROM 
            tm_ticket
            INNER JOIN tm_categoria on tm_ticket.cat_id = tm_categoria.cat_id
            INNER JOIN tm_usuario on tm_ticket.usu_id = tm_usuario.usu_id
            LEFT JOIN tm_clientes on tm_ticket.cli_id = tm_clientes.cli_id
            WHERE
            tm_ticket.est = 1
            AND tm_usuario.usu_id=?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function listar_ticket_x_id($tick_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT 
                tm_ticket.tick_id,
                tm_ticket.usu_id,
                tm_ticket.cat_id,
                tm_ticket.tick_titulo,
                tm_ticket.tick_descrip,
                tm_ticket.pot_antes,
                tm_ticket.pot_desp,
                tm_ticket.cli_id,
                tm_ticket.tick_estado,
                tm_ticket.fech_crea,
                tm_usuario.usu_nom,
                tm_usuario.usu_ape,
                tm_usuario.usu_correo,
                tm_categoria.cat_nom,
                tm_clientes.cli_nom,
                tm_clientes.cli_ape
                FROM 
                tm_ticket
                INNER JOIN tm_categoria on tm_ticket.cat_id = tm_categoria.cat_id
                INNER JOIN tm_usuario on tm_ticket.usu_id = tm_usuario.usu_id
                LEFT JOIN tm_clientes on tm_ticket.cli_id = tm_clientes.cli_id
                WHERE
                tm_ticket.est = 1
                AND tm_ticket.tick_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }


    public function listar_ticket()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT
            tm_ticket.tick_id,
            tm_ticket.usu_id,
            tm_ticket.cat_id,
            tm_ticket.tick_titulo,
            tm_ticket.tick_descrip,
            tm_ticket.pot_antes,
            tm_ticket.pot_desp,
            tm_ticket.cli_id,
            tm_ticket.tick_estado,
            (tm_ticket.tiempo_acumulado + IF(tm_ticket.tick_estado = 'Abierto', TIMESTAMPDIFF(MINUTE, tm_ticket.fech_estado_ultimo, NOW()), 0)) as tiempo_total_minutos,
            tm_ticket.fech_crea,
            tm_ticket.usu_asig,
            tm_ticket.fech_asig,
            tm_usuario.usu_nom,
            tm_usuario.usu_ape,
            tm_categoria.cat_nom,
            tm_clientes.cli_nom,
            tm_clientes.cli_ape
            FROM 
            tm_ticket
            INNER JOIN tm_categoria on tm_ticket.cat_id = tm_categoria.cat_id
            INNER JOIN tm_usuario on tm_ticket.usu_id = tm_usuario.usu_id
            LEFT JOIN tm_clientes on tm_ticket.cli_id = tm_clientes.cli_id
            WHERE
            tm_ticket.est = 1
            ";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function listar_ticketdetalle_x_ticket($tick_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT
                td_ticketdetalle.tickd_id,
                td_ticketdetalle.tickd_descrip,
                td_ticketdetalle.fech_crea,
                tm_usuario.usu_nom,
                tm_usuario.usu_ape,
                tm_usuario.rol_id
                FROM 
                td_ticketdetalle
                INNER join tm_usuario on td_ticketdetalle.usu_id = tm_usuario.usu_id
                WHERE 
                tick_id =?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function insert_ticketdetalle($tick_id, $usu_id, $tickd_descrip)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "INSERT INTO td_ticketdetalle (tickd_id,tick_id,usu_id,tickd_descrip,fech_crea,est) VALUES (NULL,?,?,?,now(),'1');";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->bindValue(2, $usu_id);
        $sql->bindValue(3, $tickd_descrip);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function insert_ticketdetalle_cerrar($tick_id, $usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "call sp_i_ticketdetalle_01(?,?)";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->bindValue(2, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function insert_ticketdetalle_reabrir($tick_id, $usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "	INSERT INTO td_ticketdetalle 
                    (tickd_id,tick_id,usu_id,tickd_descrip,fech_crea,est) 
                    VALUES 
                    (NULL,?,?,'Ticket Re-Abierto...',now(),'1');";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->bindValue(2, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function update_ticket($tick_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Al cerrar, sumamos lo que lleve abierto hasta AHORA y limpiamos la fecha
        $sql = "UPDATE tm_ticket 
            SET 
                tick_estado = 'Cerrado',
                tiempo_acumulado = tiempo_acumulado + IFNULL(TIMESTAMPDIFF(MINUTE, fech_estado_ultimo, NOW()), 0),
                fech_estado_ultimo = NULL
            WHERE
                tick_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function update_ticket_potencia($tick_id, $pot_desp)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE tm_ticket 
            SET pot_desp = ? 
            WHERE tick_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $pot_desp);
        $sql->bindValue(2, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function cambiar_estado($tick_id, $estado)
    {
        $conectar = parent::conexion();
        parent::set_names();

        if ($estado == 'En espera') {
            // PAUSAR: Calculamos diferencia desde la ultima vez abierto y sumamos al acumulado
            // Seteamos fech_estado_ultimo a NULL porque ya no está corriendo el tiempo
            $sql = "UPDATE tm_ticket 
                SET 
                    tick_estado = ?,
                    tiempo_acumulado = tiempo_acumulado + TIMESTAMPDIFF(MINUTE, fech_estado_ultimo, NOW()),
                    fech_estado_ultimo = NULL
                WHERE tick_id = ?";
        } else if ($estado == 'Abierto') {
            // REANUDAR: Solo actualizamos la fecha de inicio del contador
            $sql = "UPDATE tm_ticket 
                SET 
                    tick_estado = ?,
                    fech_estado_ultimo = NOW()
                WHERE tick_id = ?";
        } else {
            // Para otros casos (fallback)
            $sql = "UPDATE tm_ticket SET tick_estado = ? WHERE tick_id = ?";
        }

        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $estado);
        $sql->bindValue(2, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function insert_ticketdetalle_suspender($tick_id, $usu_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "	INSERT INTO td_ticketdetalle 
                    (tickd_id,tick_id,usu_id,tickd_descrip,fech_crea,est) 
                    VALUES 
                    (NULL,?,?,'Ticket en espera...',now(),'0');";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->bindValue(2, $usu_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function reabrir_ticket($tick_id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Al reabrir, establecemos el NOW() para empezar a contar de nuevo
        $sql = "UPDATE tm_ticket 
            SET 
                tick_estado = 'Abierto',
                fech_estado_ultimo = NOW()
            WHERE
                tick_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function update_ticket_asignacion($tick_id, $usu_asig)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "update tm_ticket 
                set	
                    usu_asig = ?,
                    fech_asig = now()
                where
                    tick_id = ?";
        $sql = $conectar->prepare($sql);
        $sql->bindValue(1, $usu_asig);
        $sql->bindValue(2, $tick_id);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_ticket_total()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_ticket_totalabierto()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket where tick_estado='Abierto'";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    public function get_ticket_totalcerrado()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT COUNT(*) as TOTAL FROM tm_ticket where tick_estado='Cerrado'";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    /* Archivo: models/Ticket.php */

    public function get_ticket_grafico($start_date = null, $end_date = null)
    {
        $conectar = parent::conexion();
        parent::set_names();

        // Si vienen fechas, filtramos por fech_crea
        if ($start_date !== null && $end_date !== null) {
            $sql = "SELECT tm_categoria.cat_nom as nom, COUNT(*) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                AND tm_ticket.fech_crea BETWEEN ? AND ?
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $start_date);
            $sql->bindValue(2, $end_date);
        } else {
            // Si no vienen fechas, consulta global (histórica)
            $sql = "SELECT tm_categoria.cat_nom as nom, COUNT(*) AS total
                FROM tm_ticket
                JOIN tm_categoria ON tm_ticket.cat_id = tm_categoria.cat_id
                WHERE tm_ticket.est = 1
                GROUP BY tm_categoria.cat_nom
                ORDER BY total DESC";
            $sql = $conectar->prepare($sql);
        }

        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

}
?>