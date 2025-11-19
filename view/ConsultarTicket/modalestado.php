<div id="modalestado" class="modal fade bd-example-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                    <i class="font-icon-close-2"></i>
                </button>
                <h4 class="modal-title" id="mdltitulo"></h4>
            </div>
            <form method="post" id="ticket_estado_form">
                <input type="hidden" id="tick_id" name="tick_id">

                <div class="form-group">
                    <label class="form-label" for="estado">Estado</label>
                    <select id="estado" class="select2" name="estado" data-placeholder="Seleccionar" required>
                        <option value="Abierto">Abierto</option>
                        <option value="En espera">En espera</option>
                        <option value="Cerrado">Cerrado</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-rounded btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-rounded btn-primary">Cambiar</button>
                </div>
            </form>
        </div>
    </div>
</div>