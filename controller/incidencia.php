<?php
require_once("../config/conexion.php");
require_once("../models/Incidencia.php");
$incidencia = new Incidencia();

header('Content-Type: application/json; charset=utf-8');

$op = isset($_GET["op"]) ? $_GET["op"] : '';

/* ----------------------------------------------------------------
 * Helper: recorre un array de fechas y las inserta para un tipo dado.
 * ---------------------------------------------------------------- */
function guardar_fechas_tipo($incidencia, $incidencia_id, $tipo, $fechas_raw)
{
    if (!is_array($fechas_raw)) return;
    foreach ($fechas_raw as $f) {
        $f = trim($f);
        if ($f !== '') {
            $incidencia->insertar_fecha($incidencia_id, $f, $tipo);
        }
    }
}

/* ----------------------------------------------------------------
 * Helper: formatea un string "YYYY-MM-DD,YYYY-MM-DD,..." a "DD/MM, DD/MM, ..."
 * Para mostrar en la tabla de forma compacta (sin año, que es el mismo).
 * ---------------------------------------------------------------- */
function fmt_fechas($fechas_str)
{
    if (empty($fechas_str)) return '';
    $arr = explode(',', $fechas_str);
    return implode(', ', array_map(function ($d) {
        $p = explode('-', trim($d));
        return $p[2] . '/' . $p[1]; // DD/MM
    }, $arr));
}

switch ($op) {

    /* ----------------------------------------------------------------
     * INSERTAR
     * Recibe los campos numéricos y cuatro arrays de fechas:
     *   fechas_prima[], fechas_hrs[], fechas_festivo[], fechas_falta[]
     * El usu_id viene de la sesión. usu_id 14 no puede registrar.
     * ---------------------------------------------------------------- */
    case "insertar":
        $usu_id   = isset($_SESSION['usu_id']) ? intval($_SESSION['usu_id']) : 0;
        $anio     = isset($_POST['anio'])             ? intval($_POST['anio'])            : intval(date('Y'));
        $mes      = isset($_POST['mes'])              ? intval($_POST['mes'])             : intval(date('n'));
        $quincena = isset($_POST['quincena'])         ? intval($_POST['quincena'])        : 1;
        $prima    = isset($_POST['prima_dominical'])  ? trim($_POST['prima_dominical'])   : '0';
        $hrs      = isset($_POST['hrs_extra'])        ? trim($_POST['hrs_extra'])         : '0';
        $festivo  = isset($_POST['festivo_laborado']) ? trim($_POST['festivo_laborado'])  : '0';
        $faltas   = isset($_POST['faltas'])           ? intval($_POST['faltas'])          : 0;

        $fechas_prima   = isset($_POST['fechas_prima'])   ? (array) $_POST['fechas_prima']   : [];
        $fechas_hrs     = isset($_POST['fechas_hrs'])     ? (array) $_POST['fechas_hrs']     : [];
        $fechas_festivo = isset($_POST['fechas_festivo']) ? (array) $_POST['fechas_festivo'] : [];
        $fechas_falta   = isset($_POST['fechas_falta'])   ? (array) $_POST['fechas_falta']   : [];

        if ($usu_id <= 0) {
            echo json_encode(["ok" => false, "msg" => "Sesión no válida"]);
            exit;
        }
        if ($usu_id === 14) {
            echo json_encode(["ok" => false, "msg" => "El administrador general no puede registrar incidencias"]);
            exit;
        }
        if ($incidencia->existe($usu_id, $anio, $mes, $quincena)) {
            echo json_encode(["ok" => false, "msg" => "Ya existe una incidencia registrada para esta quincena"]);
            exit;
        }

        $nuevo_id = $incidencia->insertar($usu_id, $anio, $mes, $quincena, $prima, $hrs, $festivo, $faltas);
        if ($nuevo_id === false) {
            echo json_encode(["ok" => false, "msg" => "Error al insertar en la base de datos"]);
            exit;
        }

        guardar_fechas_tipo($incidencia, $nuevo_id, 'prima',    $fechas_prima);
        guardar_fechas_tipo($incidencia, $nuevo_id, 'hrs_extra', $fechas_hrs);
        guardar_fechas_tipo($incidencia, $nuevo_id, 'festivo',  $fechas_festivo);
        guardar_fechas_tipo($incidencia, $nuevo_id, 'falta',    $fechas_falta);

        echo json_encode(["ok" => true, "incidencia_id" => (int) $nuevo_id]);
        break;

    /* ----------------------------------------------------------------
     * ACTUALIZAR
     * Las fechas anteriores se reemplazan completamente por tipo.
     * ---------------------------------------------------------------- */
    case "actualizar":
        $incidencia_id  = isset($_POST['incidencia_id']) ? intval($_POST['incidencia_id']) : 0;
        $prima          = isset($_POST['prima_dominical'])  ? trim($_POST['prima_dominical'])  : '0';
        $hrs            = isset($_POST['hrs_extra'])        ? trim($_POST['hrs_extra'])        : '0';
        $festivo        = isset($_POST['festivo_laborado']) ? trim($_POST['festivo_laborado']) : '0';
        $faltas         = isset($_POST['faltas'])           ? intval($_POST['faltas'])         : 0;

        $fechas_prima   = isset($_POST['fechas_prima'])   ? (array) $_POST['fechas_prima']   : [];
        $fechas_hrs     = isset($_POST['fechas_hrs'])     ? (array) $_POST['fechas_hrs']     : [];
        $fechas_festivo = isset($_POST['fechas_festivo']) ? (array) $_POST['fechas_festivo'] : [];
        $fechas_falta   = isset($_POST['fechas_falta'])   ? (array) $_POST['fechas_falta']   : [];

        if ($incidencia_id <= 0) {
            echo json_encode(["ok" => false, "msg" => "ID de incidencia no válido"]);
            exit;
        }

        $incidencia->actualizar($incidencia_id, $prima, $hrs, $festivo, $faltas);

        // Reemplazar todas las fechas de golpe
        $incidencia->eliminar_fechas($incidencia_id);
        guardar_fechas_tipo($incidencia, $incidencia_id, 'prima',    $fechas_prima);
        guardar_fechas_tipo($incidencia, $incidencia_id, 'hrs_extra', $fechas_hrs);
        guardar_fechas_tipo($incidencia, $incidencia_id, 'festivo',  $fechas_festivo);
        guardar_fechas_tipo($incidencia, $incidencia_id, 'falta',    $fechas_falta);

        echo json_encode(["ok" => true]);
        break;

    /* ----------------------------------------------------------------
     * ELIMINAR — las fechas caen en cascade por la FK.
     * ---------------------------------------------------------------- */
    case "eliminar":
        $incidencia_id = isset($_POST['incidencia_id']) ? intval($_POST['incidencia_id']) : 0;
        if ($incidencia_id <= 0) {
            echo json_encode(["ok" => false, "msg" => "ID de incidencia no válido"]);
            exit;
        }
        $ok = $incidencia->eliminar($incidencia_id);
        echo json_encode(["ok" => (bool) $ok]);
        break;

    /* ----------------------------------------------------------------
     * LISTAR
     * Cada celda numérica incluye las fechas de ese tipo como sub-línea.
     * ---------------------------------------------------------------- */
    case "listar":
        $anio     = isset($_POST['anio'])     ? intval($_POST['anio'])     : intval(date('Y'));
        $mes      = isset($_POST['mes'])      ? intval($_POST['mes'])      : intval(date('n'));
        $quincena = isset($_POST['quincena']) ? intval($_POST['quincena']) : 1;

        $datos = $incidencia->listar_x_quincena($anio, $mes, $quincena);
        $data  = [];

        foreach ($datos as $row) {

            // Celda con número + fechas debajo
            $cel_prima   = celda_con_fechas($row['prima_dominical'],  $row['fechas_prima']);
            $cel_hrs     = celda_con_fechas($row['hrs_extra'],        $row['fechas_hrs']);
            $cel_festivo = celda_con_fechas($row['festivo_laborado'], $row['fechas_festivo']);
            $cel_falta   = celda_con_fechas($row['faltas'],           $row['fechas_falta']);

            $acciones = '<button type="button" onclick="editarIncidencia(' . $row['incidencia_id'] . ')"'
                      . ' class="btn btn-inline btn-warning btn-sm"><i class="fa fa-edit"></i></button> '
                      . '<button type="button" onclick="eliminarIncidencia(' . $row['incidencia_id'] . ')"'
                      . ' class="btn btn-inline btn-danger btn-sm"><i class="fa fa-trash"></i></button>';

            $data[] = [
                htmlspecialchars($row['usu_nom'] . ' ' . $row['usu_ape']),
                $cel_prima,
                $cel_hrs,
                $cel_festivo,
                $cel_falta,
                $acciones
            ];
        }

        echo json_encode([
            "sEcho"                => 1,
            "iTotalRecords"        => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData"               => $data
        ]);
        break;

    /* ----------------------------------------------------------------
     * MOSTRAR — para pre-llenar el modal de edición.
     * Devuelve los campos + fechas agrupadas por tipo.
     * ---------------------------------------------------------------- */
    case "mostrar":
        $incidencia_id = isset($_POST['incidencia_id']) ? intval($_POST['incidencia_id']) : 0;
        if ($incidencia_id <= 0) {
            echo json_encode(["error" => "ID no válido"]);
            exit;
        }
        $row           = $incidencia->obtener_x_id($incidencia_id);
        $row['fechas'] = $incidencia->obtener_fechas_x_id($incidencia_id);
        // $row['fechas'] = ['prima'=>[...], 'hrs_extra'=>[...], 'festivo'=>[...], 'falta'=>[...]]
        echo json_encode($row);
        break;

    default:
        echo json_encode(["error" => "Operación no soportada: $op"]);
        break;
}

/* ----------------------------------------------------------------
 * Genera el HTML de una celda: número en grande + fechas en gris debajo.
 * ---------------------------------------------------------------- */
function celda_con_fechas($numero, $fechas_str)
{
    $n   = $numero ?: '0';
    $fmt = fmt_fechas($fechas_str);
    $sub = $fmt
        ? '<br><small style="color:#888; font-size:11px; white-space:normal;">' . htmlspecialchars($fmt) . '</small>'
        : '';
    return '<span style="font-weight:600;">' . $n . '</span>' . $sub;
}
?>