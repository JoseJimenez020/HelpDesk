<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
    ?>
    <!DOCTYPE html>
    <html>
    <?php require_once("../MainHead/head.php"); ?>
    <title>HelpDesk | Tabulador</title>
    </head>

    <body class="with-side-menu">

        <?php require_once("../MainHeader/header.php"); ?>

        <div class="mobile-menu-left-overlay"></div>

        <?php require_once("../MainNav/nav.php"); ?>

        <div class="page-content">
            <div class="container-fluid">

                <header class="section-header">
                    <div class="tbl">
                        <div class="tbl-row">
                            <div class="tbl-cell">
                                <h3>Tabulador quincenal</h3>
                                <ol class="breadcrumb breadcrumb-simple">
                                    <li><a href="../Home/index.php">Home</a></li>
                                    <li class="active">Tabulador</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="row">
                    <!-- Captura -->
                    <div class="col-md-4">
                        <div class="box-typical box-typical-padding">
                            <h5 class="m-t-lg with-border">Captura</h5>
                            <form id="tab_form" onsubmit="return false;" autocomplete="off">
                                <div class="form-group">
                                    <label class="form-label">Salario quincenal</label>
                                    <input type="number" id="tab_salario" class="form-control" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SBC diario (Salario Base de Cotización)</label>
                                    <input type="number" id="tab_sbc" class="form-control" min="0" step="0.01" placeholder="0.00" value="392.35">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Domingos trabajados (prima dominical)</label>
                                    <input type="number" id="tab_domingos" class="form-control" min="0" step="1" value="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Festivos laborados</label>
                                    <input type="number" id="tab_festivos" class="form-control" min="0" step="1" value="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Horas extra</label>
                                    <input type="number" id="tab_hrs" class="form-control" min="0" step="0.5" value="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Horas extra nocturnas</label>
                                    <input type="number" id="tab_hrs_noct" class="form-control" min="0" step="0.5" value="0">
                                </div>
                                <button type="button" id="btn_limpiar" class="btn btn-inline btn-default">Limpiar</button>
                            </form>
                            <p class="text-muted" style="margin-top:12px;font-size:12px;">
                                Cálculo estimado en vivo; no se guarda información.
                            </p>
                        </div>
                    </div>

                    <!-- Resultado -->
                    <div class="col-md-8">
                        <div class="box-typical box-typical-padding">
                            <h5 class="m-t-lg with-border">Resultado</h5>
                            <table class="table table-bordered">
                                <thead><tr><th>Percepciones</th><th class="text-right">Importe</th></tr></thead>
                                <tbody>
                                    <tr><td>Sueldo quincenal</td><td class="text-right" id="r_salario"></td></tr>
                                    <tr><td>Prima dominical</td><td class="text-right" id="r_prima"></td></tr>
                                    <tr><td>Horas extra</td><td class="text-right" id="r_extra_d"></td></tr>
                                    <tr><td>Horas extra nocturnas</td><td class="text-right" id="r_extra_n"></td></tr>
                                    <tr><td>Festivos laborados</td><td class="text-right" id="r_festivo"></td></tr>
                                    <tr><th>Total percepciones</th><th class="text-right" id="r_percepciones"></th></tr>
                                </tbody>
                                <thead><tr><th>Deducciones</th><th class="text-right">Importe</th></tr></thead>
                                <tbody>
                                    <tr><td>ISR</td><td class="text-right" id="r_isr"></td></tr>
                                    <tr><td>IMSS</td><td class="text-right" id="r_imss"></td></tr>
                                    <tr class="success"><th>Neto a recibir</th><th class="text-right" id="r_neto"></th></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="box-typical box-typical-padding">
                            <h5 class="m-t-lg with-border">Detalle del ISR</h5>
                            <table class="table table-sm">
                                <tr><td>Tipo de cálculo</td><td id="d_tipo"></td></tr>
                                <tr><td>Horas</td><td id="d_hrs"></td></tr>
                                <tr><td>Prima dominical exenta</td><td id="d_prima_ex"></td></tr>
                                <tr><td>Extras + festivo exentos</td><td id="d_extra_ex"></td></tr>
                                <tr><td>Extras + festivo gravados</td><td id="d_extra_grav"></td></tr>
                                <tr><th>Base gravable</th><th id="d_base"></th></tr>
                                <tr><td>(−) Límite inferior</td><td id="d_li"></td></tr>
                                <tr><td>= Excedente</td><td id="d_exc"></td></tr>
                                <tr><td>× % sobre excedente</td><td id="d_pct"></td></tr>
                                <tr><td>= Impuesto marginal</td><td id="d_marg"></td></tr>
                                <tr><td>(+) Cuota fija</td><td id="d_cf"></td></tr>
                            </table>
                        </div>

                        <div class="box-typical box-typical-padding">
                            <h5 class="m-t-lg with-border">Detalle del IMSS</h5>
                            <table class="table table-sm" id="d_imss"></table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php require_once("../MainJs/js.php"); ?>
        <script type="text/javascript" src="tabulador.js"></script>

    </body>
    </html>
    <?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
}
?>