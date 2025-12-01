<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
	?>
	<!DOCTYPE html>
	<html>
	<?php require_once("../MainHead/head.php"); ?>
	<title>HelpDesk | Consulta de Tickets</title>
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
								<h3>Consultar Ticket</h3>
								<ol class="breadcrumb breadcrumb-simple">
									<li><a href="../Home/index.php">Home</a></li>
									<li class="active">Consultar Ticket</li>
								</ol>
							</div>
						</div>
					</div>
				</header>

				<div class="box-typical box-typical-padding">

					<!-- Filtros: Estado, Cliente y Rango de Fechas -->
					<div class="row" style="margin-bottom:15px;">
						<div class="col-md-3">
							<label>Estado</label>
							<select id="filter_estado" class="form-control">
								<option value="">Todos</option>
								<option value="Abierto">Abierto</option>
								<option value="En espera">En espera</option>
								<option value="Cerrado">Cerrado</option>
							</select>
						</div>

						<div class="col-md-4">
							<label>Cliente</label>
							<select id="filter_cliente" class="form-control">
								<option value="">Todos</option>
								<!-- Opciones cargadas por AJAX desde controller/cliente.php?op=combo -->
							</select>
						</div>

						<div class="col-md-5">
							<label>Fechas (Fecha de creación)</label>
							<div class="input-group">
								<input type="date" id="filter_fecha_ini" class="form-control" />
								<span class="input-group-addon" style="padding:6px 10px;">a</span>
								<input type="date" id="filter_fecha_fin" class="form-control" />
								<span class="input-group-btn" style="margin-left:8px;">
									<button id="btn_filtrar" class="btn btn-primary">Filtrar</button>
									<button id="btn_reset" class="btn btn-default">Limpiar</button>
								</span>
							</div>
						</div>
					</div>

					<!-- Tabla existente -->
					<div class="box-typical box-typical-padding">
						<table id="ticket_data" class="table table-bordered table-striped table-vcenter js-dataTable-full">
							<thead>
								<tr>
									<th style="width: 2%;">N°</th>
									<th style="width: 15%;">Categoría</th>
									<th class="d-none d-sm-table-cell" style="width: 30%;">Servicios afectados</th>
									<th class="d-none d-sm-table-cell" style="width: 4%;">P Antes RX|TX</th>
									<th class="d-none d-sm-table-cell" style="width: 4%;">P Desp RX|TX</th>
									<th class="d-none d-sm-table-cell" style="width: 10%">Cliente</th>
									<th class="d-none d-sm-table-cell" style="width: 5%;">Estado</th>
									<th class="d-none d-sm-table-cell" style="width: 5%;">Tiempo</th>
									<th class="d-none d-sm-table-cell" style="width: 10%;">Fecha Creación</th>
									<th class="d-none d-sm-table-cell" style="width: 10%;">Fecha Asignación</th>
									<th class="d-none d-sm-table-cell" style="width: 5%;">Soporte</th>
									<th class="text-center" style="width: 5%;"></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>

				</div>

			</div>
		</div>
		<!-- Contenido -->
		<?php require_once("modalasignar.php"); ?>
		<?php require_once("modalestado.php"); ?>

		<?php require_once("../MainJs/js.php"); ?>

		<script type="text/javascript" src="consultarticket.js"></script>

	</body>

	</html>
	<?php
} else {
	header("Location:" . Conectar::ruta() . "index.php");
}
?>