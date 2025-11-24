<?php
class Cliente extends Conectar
{
    // Devuelve clientes activos (para combo)
    public function get_clientes()
    {
        $conectar = parent::conexion();
        parent::set_names();
        // [MODIFICADO] Agregamos WHERE est=1 para no mostrar los borrados
        $sql = "SELECT cli_id, cli_nom, cli_ape FROM tm_clientes WHERE est=1";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listado completo (DataTables)
    public function listar()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT cli_id, cli_nom, cli_ape, est FROM tm_clientes";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mostrar un cliente por id
    public function mostrar($id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT cli_id, cli_nom, cli_ape, est FROM tm_clientes WHERE cli_id = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insertar nuevo cliente, devuelve id o false
    public function insertar($nom, $ape)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // [MODIFICADO] Agregamos el campo 'est' con valor '1'
        $sql = "INSERT INTO tm_clientes (cli_nom, cli_ape, est) VALUES (?, ?, '1')";
        $stmt = $conectar->prepare($sql);
        $ok = $stmt->execute([$nom, $ape]);
        if ($ok) {
            return $conectar->lastInsertId();
        }
        $err = $stmt->errorInfo();
        error_log("Cliente::insertar error: SQLSTATE={$err[0]} Code={$err[1]} Msg={$err[2]}");
        return false;
    }

    // Actualizar cliente
    public function actualizar($id, $nom, $ape, $est)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Agregamos "est = ?" en el SQL y el parámetro en el execute
        $sql = "UPDATE tm_clientes SET cli_nom = ?, cli_ape = ?, est = ? WHERE cli_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$nom, $ape, $est, $id]);
    }

    // Eliminar cliente (físico). Si prefieres marca lógica, ajusta aquí
    public function eliminar($id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        // Cambiamos DELETE por UPDATE
        $sql = "UPDATE tm_clientes SET est = 0 WHERE cli_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>