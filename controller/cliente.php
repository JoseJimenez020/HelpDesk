<?php
require_once("../config/conexion.php");
require_once("../models/Cliente.php");
$cliente = new Cliente();

// permitir llamadas AJAX desde el mismo origen; evita output no JSON en otros casos
header('Content-Type: application/json; charset=utf-8');

$op = isset($_GET["op"]) ? $_GET["op"] : '';

switch ($op) {

    case "combo":
        // Devuelve <option> para selects (no JSON)
        header('Content-Type: text/html; charset=utf-8');
        $datos = $cliente->get_clientes();
        $html = "";
        if (is_array($datos) && count($datos) > 0) {
            foreach ($datos as $row) {
                $label = trim($row['cli_nom'] . ' ' . $row['cli_ape']);
                $html .= "<option value='" . $row['cli_id'] . "'>" . htmlspecialchars($label) . "</option>";
            }
        }
        echo $html;
        break;

    case "listar":
        $rspta = $cliente->listar();
        echo json_encode($rspta);
        break;

    case "mostrar":
        $cli_id = isset($_POST['cli_id']) ? intval($_POST['cli_id']) : 0;
        if ($cli_id > 0) {
            $rspta = $cliente->mostrar($cli_id);
            echo json_encode($rspta);
        } else {
            echo json_encode(["error" => "cli_id no provisto"]);
        }
        break;

    case "insert":
        $cli_nom = isset($_POST['cli_nom']) ? trim($_POST['cli_nom']) : '';
        $cli_ape = isset($_POST['cli_ape']) ? trim($_POST['cli_ape']) : '';

        if ($cli_nom === '' || $cli_ape === '') {
            echo json_encode(["ok" => false, "msg" => "Campos incompletos"]);
            exit;
        }

        $newId = $cliente->insertar($cli_nom, $cli_ape);
        if ($newId !== false) {
            echo json_encode(["ok" => true, "cli_id" => $newId, "cli_nom" => $cli_nom, "cli_ape" => $cli_ape]);
        } else {
            echo json_encode(["ok" => false, "msg" => "Error al insertar cliente"]);
        }
        break;

    case "update":
        $cli_id = isset($_POST['cli_id']) ? intval($_POST['cli_id']) : 0;
        $cli_nom = isset($_POST['cli_nom']) ? trim($_POST['cli_nom']) : '';
        $cli_ape = isset($_POST['cli_ape']) ? trim($_POST['cli_ape']) : '';
        $cli_est = isset($_POST['cli_est']) ? intval($_POST['cli_est']) : 1;

        if ($cli_id <= 0 || $cli_nom === '' || $cli_ape === '') {
            echo json_encode(["ok" => false, "msg" => "Datos incompletos"]);
            exit;
        }

        $ok = $cliente->actualizar($cli_id, $cli_nom, $cli_ape, $cli_est);
        echo json_encode(["ok" => $ok]);
        break;

    case "delete":
        $cli_id = isset($_POST['cli_id']) ? intval($_POST['cli_id']) : 0;
        if ($cli_id <= 0) {
            echo json_encode(["ok" => false, "msg" => "cli_id no valido"]);
            exit;
        }
        $ok = $cliente->eliminar($cli_id);
        echo json_encode(["ok" => $ok]);
        break;

    default:
        echo json_encode(["error" => "Operación no soportada"]);
        break;
}
?>