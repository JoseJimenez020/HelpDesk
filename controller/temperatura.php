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
        // Datos del mes actual
        $f_inicio = date('Y-m-01') . " 00:00:00";
        $f_fin = date('Y-m-t') . " 23:59:59";

        $sitios = $temperatura->get_sitios();
        $labels = [];
        $num_dias = date('t');
        for ($i = 1; $i <= $num_dias; $i++) {
            $labels[] = "Día $i";
        }

        $datasets = [];
        foreach ($sitios as $s) {
            $datasets[] = [
                "label" => $s['sitio_nombre'],
                "data" => array_fill(0, $num_dias, rand(20, 35)), // Simulación de promedio diario
                "borderColor" => "hsl(" . (rand(0, 360)) . ", 60%, 60%)",
                "fill" => false,
                "borderDash" => [5, 5] // Línea punteada para tendencia mensual
            ];
        }
        echo json_encode(["labels" => $labels, "datasets" => $datasets]);
        break;
}
?>