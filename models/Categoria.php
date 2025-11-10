<?php
class Categoria extends Conectar
{

    public function get_categoria()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT * FROM tm_categoria WHERE est=1;";
        $sql = $conectar->prepare($sql);
        $sql->execute();
        return $resultado = $sql->fetchAll();
    }

    // Lista todas las categorías (puedes usarla para DataTables)
    public function listar()
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT cat_id, cat_nom, est FROM tm_categoria";
        $stmt = $conectar->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Muestra una sola categoría por id
    public function mostrar($id)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "SELECT cat_id, cat_nom, est FROM tm_categoria WHERE cat_id = ?";
        $stmt = $conectar->prepare($sql);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Inserta una nueva categoría; devuelve el id insertado o false en error
    public function insertar($nom, $est = 1)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "INSERT INTO tm_categoria (cat_nom, est) VALUES (?, ?)";
        $stmt = $conectar->prepare($sql);
        $ok = $stmt->execute(array($nom, $est));
        if ($ok) {
            return $conectar->lastInsertId();
        }
        return false;
    }
    public function actualizar($id, $nom, $est)
    {
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE tm_categoria SET cat_nom = ?, est = ? WHERE cat_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$nom, $est, $id]);
    }

    public function eliminar($id)
    {
        // Recomendado: marca como inactivo en lugar de borrar físicamente
        $conectar = parent::conexion();
        parent::set_names();
        $sql = "UPDATE tm_categoria SET est = 0 WHERE cat_id = ?";
        $stmt = $conectar->prepare($sql);
        return $stmt->execute([$id]);
    }


}
?>