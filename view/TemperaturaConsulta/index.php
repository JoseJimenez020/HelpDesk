<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
    ?>
    <!DOCTYPE html>
    <html>
    <?php require_once("../MainHead/head.php"); ?>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <title>HelpDesk | Temperaturas</title>
    </head>

    <body class="with-side-menu">

        <?php require_once("../MainHeader/header.php"); ?>

        <div class="mobile-menu-left-overlay"></div>

        <?php require_once("../MainNav/nav.php"); ?>

        <!-- Contenido -->
        <div class="page-content">
            <div class="container-fluid">
                <section class="card">
                    <header class="card-header">
                        <div class="card-title" style="font-weight:600; font-size:16px;">
                            Grafico Semanal
                        </div>
                        <div class="form-inline" style="gap:8px;">
                        </div>
                    </header>
                    <div class="card-block">
                        <div id="divgrafico" style="height: 250px;">
                            <canvas id="canvasSemanal"></canvas>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <header class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title" style="font-weight:600; font-size:16px;">
                            Grafico Mensual
                        </div>
                        <div class="form-inline">
                        </div>
                    </header>
                    <div class="card-block">
                        <div id="divgraficotiempo" style="height: 250px;">
                            <canvas id="canvasMensual"></canvas>
                        </div>
                    </div>
                </section>

                <div class="box-typical box-typical-padding">

                    <!-- Filtros: Estado, Cliente y Rango de Fechas -->
                    <div class="row" style="margin-bottom:15px;">

                        <div class="col-md-5">
                            <label>Fechas</label>
                            <div class="input-group">
                                <input type="date" id="filter_fecha_ini" class="form-control" />
                                <span class="input-group-addon">a</span>
                                <input type="date" id="filter_fecha_fin" class="form-control" />
                                <span class="input-group-btn">
                                    <button id="btn_filtrar_mes" class="btn btn-primary">Filtrar</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla existente -->
                    <div class="box-typical box-typical-padding">
                        <table id="tabla_mensual" class="table table-bordered table-responsive">
                            <thead>
                                <tr></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>


            </div>
        </div>
        <!-- Contenido -->

        <?php require_once("../MainJs/js.php"); ?>
        <?php require_once("desgloceModal.php"); ?>

        <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="../../public/js/lib/chart/chart.min.js"></script>
        <script type="text/javascript" src="graficotemperatura.js"></script>

    </body>

    </html>
    <?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
}
?>