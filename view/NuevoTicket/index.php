<?php
require_once("../../config/conexion.php");
if (isset($_SESSION["usu_id"])) {
	?>
	<!DOCTYPE html>
	<html>
	<?php require_once("../MainHead/head.php"); ?>
	<title>HelpDesk | Nuevo Ticket</title>
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
								<h3>Nuevo Ticket</h3>
								<ol class="breadcrumb breadcrumb-simple">
									<li><a href="#">Home</a></li>
									<li class="active">Nuevo Ticket</li>
								</ol>
							</div>
						</div>
					</div>
				</header>

				<div class="box-typical box-typical-padding">
					<p>
						Desde esta ventana podrá generar nuevos tickets de HelpDesk.
					</p>

					<h5 class="m-t-lg with-border">Ingresar Información</h5>

					<div class="row">
						<form method="post" id="ticket_form">

							<input type="hidden" id="usu_id" name="usu_id" value="<?php echo $_SESSION["usu_id"] ?>">

							<div class="col-lg-6">
								<fieldset class="form-group">
									<label class="form-label semibold" for="exampleInput">Categoria</label>
									<select id="cat_id" name="cat_id" class="form-control"> </select>
									<div class="input-group-append">
										<button id="btnManageCats" type="button" class="btn btn-primary" data-toggle="modal"
											data-target="#modalCategorias">
											<i class="fa fa-plus"></i> Añadir
										</button>
								</fieldset>
							</div>

							<div class="col-lg-6">
								<fieldset class="form-group">
									<label class="form-label semibold" for="exampleInput">Cliente</label>
									<select id="cliente_id" name="cliente_id" class="form-control"> 
										
									</select>
									<div class="input-group-append">
										<button id="btnManageClients" type="button" class="btn btn-primary"
											data-toggle="modal" data-target="#modalClientes">
											<i class="fa fa-plus"></i> Añadir
										</button>
								</fieldset>
							</div>

							<div class="col-lg-12">
								<fieldset class="form-group">
									<label class="form-label semibold" for="tick_titulo">Servicios Afectados</label>
									<input type="text" class="form-control" id="tick_titulo" name="tick_titulo"
										placeholder="Ingrese los servicios">
								</fieldset>
							</div>

							<div class="col-lg-6">
								<fieldset class="form-group">
									<label class="form-label semibold" for="exampleInput">Documentos Adicionales</label>
									<input type="file" name="fileElem" id="fileElem" class="form-control" multiple>
								</fieldset>
							</div>

							<div class="col-lg-3">
								<fieldset class="form-group">
									<label class="form-label semibold" for="pot_antes">Potencia Antes RX|TX</label>
									<input type="text" class="form-control" id="pot_antes" name="pot_antes"
										placeholder="Ingrese la potencia">
								</fieldset>
							</div>

							<div class="col-lg-3">
								<fieldset class="form-group">
									<label class="form-label semibold" for="pot_desp">Potencia Después RX|TX</label>
									<input type="text" class="form-control" id="pot_desp" name="pot_desp"
										placeholder="Ingrese la potencia">
								</fieldset>
							</div>

							<div class="col-lg-12">
								<fieldset class="form-group">
									<label class="form-label semibold" for="tick_descrip">Descripción</label>
									<div class="summernote-theme-1">
										<textarea id="tick_descrip" name="tick_descrip" class="summernote"
											name="name"></textarea>
									</div>
								</fieldset>
							</div>
							<div class="col-lg-12">
								<button type="submit" name="action" value="add"
									class="btn btn-rounded btn-inline btn-primary">Guardar</button>
							</div>
						</form>
					</div>

				</div>
			</div>
		</div>
		<!-- Contenido -->

		<?php require_once("../MainJs/js.php"); ?>
		<?php require_once("modalcategoria.php") ?>
		<?php require_once("modalclientes.php") ?>

		<script type="text/javascript" src="nuevoticket.js"></script>

	</body>

	</html>
	<?php
} else {
	header("Location:" . Conectar::ruta() . "index.php");
}
?>