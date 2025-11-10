<?php
require_once("../config/conexion.php");
require_once("../models/Categoria.php");
$categoria = new Categoria();

// Forzar salida JSON cuando corresponde
header('Access-Control-Allow-Origin: *');

$op = isset($_GET["op"]) ? $_GET["op"] : '';

switch ($op) {
    case "combo":
        $datos = $categoria->get_categoria();
        $html = "";
        if (is_array($datos) && count($datos) > 0) {
            foreach ($datos as $row) {
                $html .= "<option value='" . $row['cat_id'] . "'>" . $row['cat_nom'] . "</option>";
            }
        }
        echo $html;
        break;

    case 'listar':
        // devuelve JSON array apto para DataTables
        $rspta = $categoria->listar();
        echo json_encode($rspta);
        break;

    case 'mostrar':
        // espera cat_id por POST (o GET)
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : (isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0);
        if ($cat_id > 0) {
            $rspta = $categoria->mostrar($cat_id);
            echo json_encode($rspta);
        } else {
            echo json_encode(["error" => "cat_id no provisto"]);
        }
        break;

    case 'insert':
        // recibe cat_nom y opcional cat_est (por POST)
        $cat_nom = isset($_POST['cat_nom']) ? trim($_POST['cat_nom']) : '';
        $cat_est = isset($_POST['cat_est']) ? intval($_POST['cat_est']) : 1;

        if ($cat_nom === '') {
            echo json_encode(["ok" => false, "msg" => "Nombre vacio"]);
            exit;
        }

        $newId = $categoria->insertar($cat_nom, $cat_est);
        if ($newId !== false) {
            echo json_encode(["ok" => true, "cat_id" => $newId, "cat_nom" => $cat_nom]);
        } else {
            echo json_encode(["ok" => false, "msg" => "Error al insertar"]);
        }
        break;

    case 'update':
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
        $cat_nom = isset($_POST['cat_nom']) ? trim($_POST['cat_nom']) : '';
        $cat_est = isset($_POST['cat_est']) ? intval($_POST['cat_est']) : 1;

        if ($cat_id <= 0 || $cat_nom === '') {
            echo json_encode(["ok" => false, "msg" => "Datos incompletos"]);
            exit;
        }

        $ok = $categoria->actualizar($cat_id, $cat_nom, $cat_est);
        echo json_encode(["ok" => $ok]);
        break;

    case 'delete':
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
        if ($cat_id <= 0) {
            echo json_encode(["ok" => false, "msg" => "cat_id no valido"]);
            exit;
        }
        $ok = $categoria->eliminar($cat_id);
        echo json_encode(["ok" => $ok]);
        break;

    default:
        echo json_encode(["error" => "Operación no soportada"]);
        break;
}
?>