<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
    ?>
    <!DOCTYPE html>
    <html>
    <?php require_once("../MainHead/head.php"); ?>
    <title>HelpDesk | Incidencias</title>
    </head>

    <body class="with-side-menu">

        <?php require_once("../MainHeader/header.php"); ?>

        <div class="mobile-menu-left-overlay"></div>

        <?php require_once("../MainNav/nav.php"); ?>

        <!-- Contenido -->
        <div class="page-content">
            <div class="container-fluid">

                <header class="section-header">
                    <div class="tbl">
                        <div class="tbl-row">
                            <div class="tbl-cell">
                                <h3>Incidencias</h3>
                                <ol class="breadcrumb breadcrumb-simple">
                                    <li><a href="../Home/index.php">Home</a></li>
                                    <li class="active">Incidencias</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical box-typical-padding">

                    <!-- Filtros -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-3">
                            <label>Año</label>
                            <select id="filter_anio" class="form-control"></select>
                        </div>
                        <div class="col-md-3">
                            <label>Mes</label>
                            <select id="filter_mes" class="form-control">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Quincena</label>
                            <select id="filter_quincena" class="form-control">
                                <option value="1">Primera (1 – 15)</option>
                                <option value="2">Segunda (16 – fin)</option>
                            </select>
                        </div>
                        <div class="col-md-3" style="display:flex; align-items:flex-end; gap:6px;">
                            <button id="btn_filtrar" class="btn btn-primary">Consultar</button>
                            <?php if ($_SESSION['usu_id'] != 14): ?>
                            <button type="button" id="btnnuevo" class="btn btn-inline btn-success">
                                <i class="fa fa-plus"></i> Nuevo Registro
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tabla (sin columna Fechas — las fechas van dentro de cada celda) -->
                    <table id="incidencias_data" class="table table-bordered table-striped table-vcenter js-dataTable-full">
                        <thead>
                            <tr>
                                <th style="width: 24%;">Nombre</th>
                                <th class="text-center" style="width: 14%;">Prima Dominical</th>
                                <th class="text-center" style="width: 14%;">Horas Extra</th>
                                <th class="text-center" style="width: 14%;">Festivo Laborado</th>
                                <th class="text-center" style="width: 14%;">Faltas</th>
                                <th class="text-center" style="width: 20%;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>

            </div>
        </div>
        <!-- Contenido -->

        <?php require_once("modalincidencias.php"); ?>
        <?php require_once("../MainJs/js.php"); ?>

        <script type="text/javascript" src="incidencias.js"></script>

    </body>
    </html>
    <?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
}
?>