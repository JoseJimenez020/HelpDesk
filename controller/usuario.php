<?php
require_once("../config/conexion.php");
require_once("../models/Usuario.php");
$usuario = new Usuario();

switch ($_GET["op"]) {
    case "guardaryeditar":
        if (empty($_POST["usu_id"])) {
            $usuario->insert_usuario($_POST["usu_nom"], $_POST["usu_ape"], $_POST["usu_correo"], $_POST["usu_pass"], $_POST["rol_id"]);
        } else {
            $usuario->update_usuario($_POST["usu_id"], $_POST["usu_nom"], $_POST["usu_ape"], $_POST["usu_correo"], $_POST["usu_pass"], $_POST["rol_id"]);
        }
        break;

    case "listar":
        $datos = $usuario->get_usuario();
        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["usu_nom"];
            $sub_array[] = $row["usu_ape"];
            $sub_array[] = $row["usu_correo"];
            $sub_array[] = $row["usu_pass"];

            if ($row["rol_id"] == "1") {
                $sub_array[] = '<span class="label label-pill label-success">Usuario</span>';
            } else {
                $sub_array[] = '<span class="label label-pill label-info">Soporte</span>';
            }

            $sub_array[] = '<button type="button" onClick="editar(' . $row["usu_id"] . ');"  id="' . $row["usu_id"] . '" class="btn btn-inline btn-warning btn-sm ladda-button"><i class="fa fa-edit"></i></button>';
            $sub_array[] = '<button type="button" onClick="eliminar(' . $row["usu_id"] . ');"  id="' . $row["usu_id"] . '" class="btn btn-inline btn-danger btn-sm ladda-button"><i class="fa fa-trash"></i></button>';
            $data[] = $sub_array;
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;

    case "eliminar":
        $usuario->delete_usuario($_POST["usu_id"]);
        break;

    case "mostrar";
        $datos = $usuario->get_usuario_x_id($_POST["usu_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["usu_id"] = $row["usu_id"];
                $output["usu_nom"] = $row["usu_nom"];
                $output["usu_ape"] = $row["usu_ape"];
                $output["usu_correo"] = $row["usu_correo"];
                $output["usu_pass"] = $row["usu_pass"];
                $output["rol_id"] = $row["rol_id"];
            }
            echo json_encode($output);
        }
        break;

    case "total":
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }
        $datos = $usuario->get_usuario_total_x_id($_POST["usu_id"], $start_dt, $end_dt);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;

    case "totalabierto":
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }
        $datos = $usuario->get_usuario_totalabierto_x_id($_POST["usu_id"], $start_dt, $end_dt);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;

    case "totalcerrado":
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }
        $datos = $usuario->get_usuario_totalcerrado_x_id($_POST["usu_id"], $start_dt, $end_dt);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;


    case "semanas":
        // Devuelve opciones <option value="YYYY-MM-DD|YYYY-MM-DD">Semana DD/MM/YYYY - DD/MM/YYYY</option>
        $datos = $usuario->get_semanas_disponibles(); // método en el modelo
        $html = "";
        if (is_array($datos) && count($datos) > 0) {
            foreach ($datos as $row) {
                // row tiene start_date y end_date en formato YYYY-MM-DD
                $start = $row['start_date'];
                $end = $row['end_date'];
                $display = date("d/m/Y", strtotime($start)) . " - " . date("d/m/Y", strtotime($end));
                $html .= "<option value='{$start}|{$end}'>Semana {$display}</option>";
            }
        }
        echo $html;
        break;

    case "grafico":
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        // Asegurar formato y añadir horas para BETWEEN si vienen fechas
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }
        $datos = $usuario->get_usuario_grafico($_POST["usu_id"], $start_dt, $end_dt);
        echo json_encode($datos);
        break;

    case "grafico_tiempo":
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }
        $datos = $usuario->get_usuario_grafico_tiempo($start_dt, $end_dt);
        echo json_encode($datos);
        break;


    case "combo";
        $datos = $usuario->get_usuario_x_rol();
        if (is_array($datos) == true and count($datos) > 0) {
            $html .= "<option label='Seleccionar'></option>";
            foreach ($datos as $row) {
                $html .= "<option value='" . $row['usu_id'] . "'>" . $row['usu_nom'] . "</option>";
            }
            echo $html;
        }
        break;
        
    case "password":
        $usuario->update_usuario_pass($_POST["usu_id"], $_POST["usu_pass"]);
        break;

}
?>