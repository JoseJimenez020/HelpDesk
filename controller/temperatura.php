<?php
require_once("../config/conexion.php");
require_once("../models/Temperatura.php");
$temperatura = new Temperatura();

switch ($_GET["op"]) {
    case "listar_y_datos":
        $sitios = $temperatura->get_sitios();
        $f_inicio = $_POST["f_inicio"] . " 00:00:00";
        $f_fin = $_POST["f_fin"] . " 23:59:59";
        $datos = $temperatura->get_temperaturas_semana($f_inicio, $f_fin);
        
        echo json_encode(["sitios" => $sitios, "registros" => $datos]);
        break;

    case "guardar_todo":
        $registros = $_POST["puntos"]; 
        $usu_id = $_SESSION["usu_id"]; 
        $ok = true;

        foreach ($registros as $reg) {
            if ($reg['valor'] != "") {
                $res = $temperatura->guardar_temperatura($reg['fecha_hora'], $reg['valor'], $reg['sitio_id'], $usu_id);
                if (!$res) $ok = false;
            }
        }
        echo json_encode(["ok" => $ok]);
        break;
}
?>