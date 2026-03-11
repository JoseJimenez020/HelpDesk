<?php
require_once("../config/conexion.php");
require_once("../models/Temperatura.php");
$temperatura = new Temperatura();

switch ($_GET["op"]) {
    case "listar_y_datos":
        $sitios = $temperatura->get_sitios();
        $f_inicio = $_POST["f_inicio"] . " 00:00:00";
        $f_fin = $_POST["f_fin"] . " 23:59:59";
        $datos = $temperatura->get_temperaturas_por_rango($f_inicio, $f_fin);

        echo json_encode(["sitios" => $sitios, "registros" => $datos]);
        break;

    case "guardar_todo":
        $registros = $_POST["puntos"];
        $usu_id = $_SESSION["usu_id"];
        $ok = true;

        foreach ($registros as $reg) {
            if ($reg['valor'] != "") {
                $res = $temperatura->guardar_temperatura($reg['fecha_hora'], $reg['valor'], $reg['sitio_id'], $usu_id);
                if (!$res)
                    $ok = false;
            }
        }
        echo json_encode(["ok" => $ok]);
        break;

    case "grafico_semanal":
        $f_inicio = date('Y-m-d', strtotime('sunday last week')) . " 00:00:00";
        $f_fin = date('Y-m-d', strtotime('saturday this week')) . " 23:59:59";

        $sitios = $temperatura->get_sitios();
        $labels = [];
        $ejes_fechas = []; // Para comparar contra la DB

        // Generar etiquetas de tiempo reales
        for ($i = 0; $i < 7; $i++) {
            $fecha_temp = date('Y-m-d', strtotime("sunday last week +$i days"));
            foreach (["07:00", "12:00", "19:00"] as $h) {
                $labels[] = date('D', strtotime($fecha_temp)) . " $h";
                $ejes_fechas[] = "$fecha_temp $h:00";
            }
        }

        $datasets = [];
        foreach ($sitios as $s) {
            $dataSitio = [];
            // Obtener registros reales del sitio
            $registros = $temperatura->get_temperaturas_grafico($s['sitio_id'], $f_inicio, $f_fin);

            // Mapear: Si existe la fecha en la DB, poner el valor; si no, null
            foreach ($ejes_fechas as $eje) {
                $valor = null;
                foreach ($registros as $reg) {
                    if ($reg['fecha_hora'] == $eje) {
                        $valor = $reg['temperatura'];
                        break;
                    }
                }
                $dataSitio[] = $valor;
            }

            $datasets[] = [
                "label" => $s['sitio_nombre'],
                "data" => $dataSitio,
                "borderColor" => "hsl(" . (rand(0, 360)) . ", 70%, 50%)",
                "fill" => false,
                "spanGaps" => true
            ];
        }
        echo json_encode(["labels" => $labels, "datasets" => $datasets]);
        break;

    case "grafico_mensual":
        // 1. Definir rango del mes actual
        $f_inicio = date('Y-m-01') . " 00:00:00"; // Primer día del mes 
        $f_fin = date('Y-m-t') . " 23:59:59";    // Último día del mes 

        $sitios = $temperatura->get_sitios();
        $labels = [];
        $ejes_dias = [];
        $num_dias = date('t');

        // 2. Generar etiquetas y array de fechas para comparar
        for ($i = 1; $i <= $num_dias; $i++) {
            $labels[] = "Día $i";
            // Formato Y-m-d para buscar en la base de datos
            $ejes_dias[] = date('Y-m-') . str_pad($i, 2, "0", STR_PAD_LEFT);
        }

        $datasets = [];
        foreach ($sitios as $s) {
            $dataSitio = [];
            // Obtener todos los registros del mes para este sitio 
            $registros = $temperatura->get_temperaturas_grafico($s['sitio_id'], $f_inicio, $f_fin);

            foreach ($ejes_dias as $dia_buscado) {
                $valor = null;
                foreach ($registros as $reg) {
                    // Buscamos un registro que coincida con el día y sea de las 12:00:00 (o cualquier hora fija)
                    if (strpos($reg['fecha_hora'], $dia_buscado) !== false && strpos($reg['fecha_hora'], "12:00:00") !== false) {
                        $valor = $reg['temperatura'];
                        break;
                    }
                }
                $dataSitio[] = $valor;
            }

            $datasets[] = [
                "label" => $s['sitio_nombre'],
                "data" => $dataSitio,
                "borderColor" => "hsl(" . (rand(0, 360)) . ", 60%, 60%)",
                "fill" => false,
                "spanGaps" => true, // Para que la línea no se corte si falta un día
                "borderDash" => [5, 5]
            ];
        }
        echo json_encode(["labels" => $labels, "datasets" => $datasets]);
        break;

}
?>