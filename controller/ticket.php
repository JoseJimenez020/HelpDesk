<?php
require_once("../config/conexion.php");
require_once("../models/Ticket.php");
$ticket = new Ticket();

require_once("../models/Usuario.php");
$usuario = new Usuario();

require_once("../models/Documento.php");
$documento = new Documento();

switch ($_GET["op"]) {

    case "insert":
        $datos = $ticket->insert_ticket(
            $_POST["usu_id"],
            $_POST["cliente_id"],
            $_POST["cat_id"],
            $_POST["tick_titulo"],
            $_POST["tick_descrip"],
            $_POST["pot_antes"],
            $_POST["pot_desp"]
        );

        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["tick_id"] = $row["tick_id"];


                if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {

                } else {
                    $countfiles = count($_FILES['files']['name']);
                    $ruta = "../public/document/" . $output["tick_id"] . "/";
                    $files_arr = array();

                    if (!file_exists($ruta)) {
                        mkdir($ruta, 0777, true);
                    }

                    for ($index = 0; $index < $countfiles; $index++) {
                        $doc1 = $_FILES['files']['tmp_name'][$index];
                        $destino = $ruta . $_FILES['files']['name'][$index];

                        $documento->insert_documento($output["tick_id"], $_FILES['files']['name'][$index]);

                        move_uploaded_file($doc1, $destino);
                    }
                }
            }
        }
        echo json_encode($datos);
        break;

    case "update":
        $ticket->update_ticket($_POST["tick_id"]);
        $ticket->insert_ticketdetalle_cerrar($_POST["tick_id"], $_POST["usu_id"]);
        break;

    case "update_potencia":
        $ticket->update_ticket_potencia($_POST["tick_id"], $_POST["pot_desp"]);
        break;

    case "reabrir":
        $ticket->reabrir_ticket($_POST["tick_id"]);
        $ticket->insert_ticketdetalle_reabrir($_POST["tick_id"], $_POST["usu_id"]);
        break;

    case "cambiar_estado":
        $tick_id = $_POST["tick_id"];
        $estado = $_POST["estado"];

        // 1. Actualizamos el estado general del ticket
        $ticket->cambiar_estado($tick_id, $estado);

        // 2. Obtenemos el usuario actual (sacamos esto fuera del IF para usarlo en ambos casos)
        $usu_id = isset($_SESSION["usu_id"]) ? $_SESSION["usu_id"] : (isset($_POST["usu_id"]) ? $_POST["usu_id"] : null);

        // 3. Lógica condicional para registrar el detalle
        if ($usu_id != null) {
            if ($estado == "En espera") {
                // Si es 'En espera', usa tu función de suspender
                $ticket->insert_ticketdetalle_suspender($tick_id, $usu_id);
            } elseif ($estado == "Abierto") {
                // Si es 'Abierto', usa tu función existente de reabrir
                $ticket->insert_ticketdetalle_reabrir($tick_id, $usu_id);
            } elseif ($estado === "Cerrado") {
                $ticket->update_ticket($_POST["tick_id"]);
                $ticket->insert_ticketdetalle_cerrar($_POST["tick_id"], $_POST["usu_id"]);
            }
        }

        echo json_encode(["success" => true]);
        break;

    case "asignar":
        $ticket->update_ticket_asignacion($_POST["tick_id"], $_POST["usu_asig"]);
        break;

    case "listar_x_usu":
        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
        $cliente_id = isset($_POST['cliente_id']) ? trim($_POST['cliente_id']) : '';
        $fecha_ini = isset($_POST['fecha_ini']) ? trim($_POST['fecha_ini']) : '';
        $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';
        $usu_id_post = isset($_POST['usu_id']) ? trim($_POST['usu_id']) : '';

        // Si no viene usu_id por POST, usar el de sesión si existe
        if (empty($usu_id_post) && isset($_SESSION["usu_id"])) {
            $usu_id_post = $_SESSION["usu_id"];
        }

        $datos = $ticket->listar_ticket_filtrado($estado, $cliente_id, $fecha_ini, $fecha_fin, $usu_id_post);

        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["tick_id"];
            $cat_id_val = $row["cat_id"];
            $cat_nom_esc = htmlspecialchars($row["cat_nom"]);
            $sub_array[] = '<a href="javascript:void(0);" class="btn-categoria" data-tick="' . $row["tick_id"] . '" data-cat="' . $cat_id_val . '">' . $cat_nom_esc . '</a>';

            $sub_array[] = $row["tick_titulo"];
            $sub_array[] = $row["pot_antes"];
            $sub_array[] = $row["pot_desp"];

            $minutos_totales = $row["tiempo_total_minutos"];
            $horas = floor($minutos_totales / 60);
            $minutos = $minutos_totales % 60;
            $tiempo_formato = $horas . "h " . $minutos . "m";

            if (empty($row["cli_nom"]) && empty($row["cli_ape"])) {
                $sub_array[] = '<span class="label label-pill label-default">Sin Cliente</span>';
            } else {
                $cliente_full = htmlspecialchars($row["cli_nom"] . ' ' . $row["cli_ape"]);
                $sub_array[] = '<span>' . $cliente_full . '</span>';
            }

            if ($row["tick_estado"] == "Abierto") {
                $sub_array[] = '<a onClick="MoverEstado(' . $row["tick_id"] . ')"><span class="label label-pill label-success">Abierto</span></a>';
            } elseif ($row["tick_estado"] == "En espera") {
                $sub_array[] = '<a onClick="MoverEstado(' . $row["tick_id"] . ')"> <span class="label label-pill label-warning">En espera</span> </a>';
            } else {
                $sub_array[] = '<a onClick="CambiarEstado(' . $row["tick_id"] . ')"><span class="label label-pill label-danger">Cerrado</span></a>';
            }

            $sub_array[] = $tiempo_formato;
            $sub_array[] = date("d/m/Y H:i:s", strtotime($row["fech_crea"]));

            if ($row["fech_asig"] == null) {
                $sub_array[] = '<span class="label label-pill label-default">Sin Asignar</span>';
            } else {
                $sub_array[] = date("d/m/Y H:i:s", strtotime($row["fech_asig"]));
            }

            if ($row["usu_asig"] == null) {
                $sub_array[] = '<span class="label label-pill label-warning">Sin Asignar</span>';
            } else {
                $datos1 = $usuario->get_usuario_x_id($row["usu_asig"]);
                foreach ($datos1 as $row1) {
                    $sub_array[] = '<span class="label label-pill label-success">' . $row1["usu_nom"] . '</span>';
                }
            }

            $sub_array[] = '<button type="button" onClick="ver(' . $row["tick_id"] . ');"  id="' . $row["tick_id"] . '" class="btn btn-inline btn-primary btn-sm ladda-button"><i class="fa fa-eye"></i></button>';
            $data[] = $sub_array;
        }

        $results = array(
            "data" => $data
        );
        echo json_encode($results);
        break;


    case "listar":
        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
        $cliente_id = isset($_POST['cliente_id']) ? trim($_POST['cliente_id']) : '';
        $fecha_ini = isset($_POST['fecha_ini']) ? trim($_POST['fecha_ini']) : '';
        $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : '';

        $datos = $ticket->listar_ticket_filtrado($estado, $cliente_id, $fecha_ini, $fecha_fin);

        $data = array();
        foreach ($datos as $row) {
            $sub_array = array();
            $sub_array[] = $row["tick_id"];
            $cat_id_val = $row["cat_id"];
            $cat_nom_esc = htmlspecialchars($row["cat_nom"]);
            $sub_array[] = '<a href="javascript:void(0);" class="btn-categoria" data-tick="' . $row["tick_id"] . '" data-cat="' . $cat_id_val . '">' . $cat_nom_esc . '</a>';
            $sub_array[] = $row["tick_titulo"];
            $sub_array[] = $row["pot_antes"];
            $sub_array[] = $row["pot_desp"];

            $minutos_totales = $row["tiempo_total_minutos"];
            $horas = floor($minutos_totales / 60);
            $minutos = $minutos_totales % 60;
            $tiempo_formato = $horas . "h " . $minutos . "m";

            if (empty($row["cli_nom"]) && empty($row["cli_ape"])) {
                $sub_array[] = '<span class="label label-pill label-default">Sin Cliente</span>';
            } else {
                $cliente_full = htmlspecialchars($row["cli_nom"] . ' ' . $row["cli_ape"]);
                $sub_array[] = '<span>' . $cliente_full . '</span>';
            }

            if ($row["tick_estado"] == "Abierto") {
                $sub_array[] = '<a onClick="MoverEstado(' . $row["tick_id"] . ')"><span class="label label-pill label-success">Abierto</span></a>';
            } elseif ($row["tick_estado"] == "En espera") {
                $sub_array[] = '<a onClick="MoverEstado(' . $row["tick_id"] . ')"> <span class="label label-pill label-warning">En espera</span> </a>';
            } else {
                $sub_array[] = '<a onClick="CambiarEstado(' . $row["tick_id"] . ')"><span class="label label-pill label-danger">Cerrado</span></a>';
            }

            $sub_array[] = $tiempo_formato;
            $sub_array[] = date("d/m/Y H:i:s", strtotime($row["fech_crea"]));

            if ($row["fech_asig"] == null) {
                $sub_array[] = '<span class="label label-pill label-default">Sin Asignar</span>';
            } else {
                $sub_array[] = date("d/m/Y H:i:s", strtotime($row["fech_asig"]));
            }

            if ($row["usu_asig"] == null) {
                $sub_array[] = '<span class="label label-pill label-warning">Sin Asignar</span>';
            } else {
                $datos1 = $usuario->get_usuario_x_id($row["usu_asig"]);
                foreach ($datos1 as $row1) {
                    $sub_array[] = '<span class="label label-pill label-success">' . $row1["usu_nom"] . '</span>';
                }
            }

            $sub_array[] = '<button type="button" onClick="ver(' . $row["tick_id"] . ');" id="' . $row["tick_id"] . '" class="btn btn-inline btn-primary btn-sm ladda-button"><i class="fa fa-eye"></i></button> ' .
                '<button type="button" onClick="asignar(' . $row["tick_id"] . ');" id="' . $row["tick_id"] . '" class="btn btn-inline btn-warning btn-sm ladda-button"><i class="fa fa-users"></i></button>';
            $data[] = $sub_array;
        }

        $results = array(
            "data" => $data
        );
        echo json_encode($results);
        break;

    case "listardetalle":
        $datos = $ticket->listar_ticketdetalle_x_ticket($_POST["tick_id"]);
        ?>
        <?php
        foreach ($datos as $row) {
            ?>
            <article class="activity-line-item box-typical">
                <div class="activity-line-date">
                    <?php echo date("d/m/Y", strtotime($row["fech_crea"])); ?>
                </div>
                <header class="activity-line-item-header">
                    <div class="activity-line-item-user">
                        <div class="activity-line-item-user-photo">
                            <a href="#">
                                <img src="../../public/<?php echo $row['rol_id'] ?>.jpg" alt="">
                            </a>
                        </div>
                        <div class="activity-line-item-user-name"><?php echo $row['usu_nom'] . ' ' . $row['usu_ape']; ?></div>
                        <div class="activity-line-item-user-status">
                            <?php
                            if ($row['rol_id'] == 1) {
                                echo 'Usuario';
                            } else {
                                echo 'Soporte';
                            }
                            ?>
                        </div>
                    </div>
                </header>
                <div class="activity-line-action-list">
                    <section class="activity-line-action">
                        <div class="time"><?php echo date("H:i:s", strtotime($row["fech_crea"])); ?></div>
                        <div class="cont">
                            <div class="cont-in">
                                <p>
                                    <?php echo $row["tickd_descrip"]; ?>
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </article>
            <?php
        }
        ?>
        <?php
        break;

    case "mostrar";
        $datos = $ticket->listar_ticket_x_id($_POST["tick_id"]);
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["tick_id"] = $row["tick_id"];
                $output["usu_id"] = $row["usu_id"];
                $output["cat_id"] = $row["cat_id"];

                $output["tick_titulo"] = $row["tick_titulo"];
                $output["tick_descrip"] = $row["tick_descrip"];
                $output["pot_antes"] = $row["pot_antes"];
                $output["pot_desp"] = $row["pot_desp"];
                if ($row["tick_estado"] == "Abierto") {
                    $output["tick_estado"] = '<span class="label label-pill label-success">Abierto</span>';
                } elseif ($row["tick_estado"] == "En espera") {
                    $sub_array[] = '<span class="label label-pill label-warning">En espera</span>';
                } else {
                    $output["tick_estado"] = '<span class="label label-pill label-danger">Cerrado</span>';
                }

                $output["tick_estado_texto"] = $row["tick_estado"];

                $output["fech_crea"] = date("d/m/Y H:i:s", strtotime($row["fech_crea"]));
                $output["usu_nom"] = $row["usu_nom"];
                $output["usu_ape"] = $row["usu_ape"];
                $output["cat_nom"] = $row["cat_nom"];
            }
            echo json_encode($output);
        }
        break;

    case "insertdetalle":
        $ticket->insert_ticketdetalle($_POST["tick_id"], $_POST["usu_id"], $_POST["tickd_descrip"]);
        break;

    case "total";
        $datos = $ticket->get_ticket_total();
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;

    case "totalabierto";
        $datos = $ticket->get_ticket_totalabierto();
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;

    case "totalcerrado";
        $datos = $ticket->get_ticket_totalcerrado();
        if (is_array($datos) == true and count($datos) > 0) {
            foreach ($datos as $row) {
                $output["TOTAL"] = $row["TOTAL"];
            }
            echo json_encode($output);
        }
        break;

    /* Archivo: controller/ticket.php */

    case "grafico":
        // 1. Capturamos las fechas del POST
        $start = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end = isset($_POST['end_date']) ? $_POST['end_date'] : null;

        // 2. Preparamos las variables con hora
        if ($start && $end) {
            $start_dt = $start . " 00:00:00";
            $end_dt = $end . " 23:59:59";
        } else {
            $start_dt = null;
            $end_dt = null;
        }

        // 3. Enviamos las fechas a la función del modelo
        $datos = $ticket->get_ticket_grafico($start_dt, $end_dt);
        echo json_encode($datos);
        break;

    case "update_categoria":
        $tick_id = isset($_POST["tick_id"]) ? intval($_POST["tick_id"]) : 0;
        $cat_id = isset($_POST["cat_id"]) ? intval($_POST["cat_id"]) : 0;
        if ($tick_id > 0 && $cat_id > 0) {
            $res = $ticket->update_ticket_categoria($tick_id, $cat_id);
            if ($res) {
                // opcional: registrar detalle de cambio
                /*if (isset($_SESSION["usu_id"])) {
                    $ticket->insert_ticketdetalle($tick_id, $_SESSION["usu_id"], "Categoría resignada");
                }*/
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "No se actualizó registro"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
        }
        break;
}

?>