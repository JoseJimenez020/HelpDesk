<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
	?>
	<!DOCTYPE html>
	<html>
	<?php require_once("../MainHead/head.php"); ?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
	<title>HelpDesk | Home</title>
	</head>

	<body class="with-side-menu">

		<?php require_once("../MainHeader/header.php"); ?>

		<div class="mobile-menu-left-overlay"></div>

		<?php require_once("../MainNav/nav.php"); ?>

		<!-- Contenido -->
		<div class="page-content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<div class="row">
							<div class="col-sm-4">
								<article class="statistic-box green">
									<div>
										<div class="number" id="lbltotal"></div>
										<div class="caption">
											<div>Total de Tickets</div>
										</div>
									</div>
								</article>
							</div>
							<div class="col-sm-4">
								<article class="statistic-box yellow">
									<div>
										<div class="number" id="lbltotalabierto"></div>
										<div class="caption">
											<div>Total de Tickets Abiertos</div>
										</div>
									</div>
								</article>
							</div>
							<div class="col-sm-4">
								<article class="statistic-box red">
									<div>
										<div class="number" id="lbltotalcerrado"></div>
										<div class="caption">
											<div>Total de Tickets Cerrados</div>
										</div>
									</div>
								</article>
							</div>

						</div>
					</div>
				</div>

				<section class="card">
					<header class="card-header">
						<div class="card-title" style="font-weight:600; font-size:16px;">
							Grafico Estadístico
						</div>

						<div class="form-inline" style="gap:8px;">

							<select id="select_semana_graf" class="form-control"
								style="display:inline-block; width:auto; font-size:13px; padding:4px 8px;">
								<!-- opciones cargadas por AJAX (semanas para la gráfica principal) -->
							</select>
						</div>

					</header>
					<div class="card-block">
						<div id="divgrafico" style="height: 250px;"></div>
					</div>
				</section>

				<section class="card">
					<header class="card-header d-flex align-items-center justify-content-between">
						<div class="card-title" style="font-weight:600; font-size:16px;">
							Tiempo de Resolución
						</div>

						<div class="form-inline">
							<select id="select_semana" class="form-control"
								style="display:inline-block; width:auto; font-size:13px; padding:4px 8px;"></select>
						</div>
					</header>
					<div class="card-block">
						<div id="divgraficotiempo" style="height: 250px;"></div>
					</div>
				</section>

			</div>
		</div>
		<!-- Contenido -->

		<?php require_once("../MainJs/js.php"); ?>

		<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
		<script type="text/javascript" src="home.js"></script>

	</body>

	</html>
	<?php
} else {
	header("Location:" . Conectar::ruta() . "index.php");
}
?>