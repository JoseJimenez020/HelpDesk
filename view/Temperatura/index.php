<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
    ?>
    <!DOCTYPE html>
    <html>
    <?php require_once("../MainHead/head.php"); ?>
    <title>HelpDesk | Registro de temperaturas</title>
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
                                <h3>Registro de temperaturas</h3>
                                <ol class="breadcrumb breadcrumb-simple">
                                    <li><a href="../Home/index.php">Home</a></li>
                                    <li class="active">Registro de temperaturas</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical box-typical-padding">
                    <table id="tem" class="table table-bordered table-striped table-vcenter">
                        <thead>
                            <tr id="header-row">
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="btn-container" class="text-right mt-3"></div>
                </div>
            </div>
        </div>
        <!-- Contenido -->
        <?php require_once("../MainJs/js.php"); ?>

        <script type="text/javascript" src="consultartemperatura.js"></script>

    </body>

    </html>
    <?php
} else {
    header("Location:" . Conectar::ruta() . "index.php");
}
?>