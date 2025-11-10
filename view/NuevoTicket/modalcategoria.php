<!-- Modal Categorias -->
<div id="modalCategorias" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestionar Categorías</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Botón para nueva categoría -->
                <div class="mb-2">
                    <button id="btnNewCat" class="btn btn-success">Agregar Categoría</button>
                </div>

                <!-- Tabla de categorías -->
                <table id="categorias_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- Form inline para crear/editar (oculto por defecto) -->
                <div id="cat_form_container" style="display:none; margin-top:15px;">
                    <form id="cat_form">
                        <input type="hidden" id="cat_id_edit" name="cat_id">
                        <div class="form-group">
                            <label for="cat_nom">Nombre</label>
                            <input type="text" id="cat_nom" name="cat_nom" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cat_est">Estado</label>
                            <select id="cat_est" name="cat_est" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" id="btnCancelCat" class="btn btn-secondary">Cancelar</button>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    // --- inicialización (puede ir dentro $(document).ready(...) ) ---
    var categoriasTable;

    $('#modalCategorias').on('shown.bs.modal', function () {
        if (!categoriasTable) {
            categoriasTable = $('#categorias_table').DataTable({
                ajax: {
                    url: '../../controller/categoria.php?op=listar', // debe devolver JSON para DataTables
                    type: 'POST',
                    dataSrc: ''
                },
                columns: [
                    { data: 'cat_id' },
                    { data: 'cat_nom' },
                    {
                        data: 'est',
                        render: function (data) { return data == 1 ? 'Activo' : 'Inactivo'; }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            return '<button class="btn btn-sm btn-info btn-edit-cat" data-id="' + row.cat_id + '">Editar</button> ' +
                                '<button class="btn btn-sm btn-danger btn-del-cat" data-id="' + row.cat_id + '">Eliminar</button>';
                        }
                    }
                ]
            });
        } else {
            categoriasTable.ajax.reload();
        }
    });

    // Mostrar form para nueva categoría
    $('#btnNewCat').on('click', function () {
        $('#cat_form_container').show();
        $('#cat_form')[0].reset();
        $('#cat_id_edit').val('');
        $('#cat_nom').focus();
    });

    // Cancelar form
    $('#btnCancelCat').on('click', function () {
        $('#cat_form_container').hide();
    });

    // Guardar (crear / actualizar)
    $('#cat_form').on('submit', function (e) {
        e.preventDefault();
        var form = $(this).serialize();
        var op = $('#cat_id_edit').val() ? 'update' : 'insert';
        $.post('../../controller/categoria.php?op=' + op, form, function (resp) {
            // resp ahora es JSON
            try {
                var r = typeof resp === 'object' ? resp : JSON.parse(resp);
            } catch (err) {
                console.error('Respuesta inválida', resp);
                return;
            }

            if (r.ok) {
                categoriasTable.ajax.reload();
                $('#cat_form_container').hide();

                // recargar el combo y seleccionar la nueva categoria si viene cat_id
                $.post('../../controller/categoria.php?op=combo', function (html) {
                    $('#cat_id').html(html);
                    if (r.cat_id) {
                        $('#cat_id').val(r.cat_id);
                    }
                });

            } else {
                alert(r.msg || 'Error en la operación');
            }
        }, 'json');
    });

    // Editar (llenar form con datos)
    $('#categorias_table').on('click', '.btn-edit-cat', function () {
        var id = $(this).data('id');
        $.post('../../controller/categoria.php?op=mostrar', { cat_id: id }, function (data) {
            // asumir data es JSON {cat_id, cat_nom, est}
            data = JSON.parse(data);
            $('#cat_id_edit').val(data.cat_id);
            $('#cat_nom').val(data.cat_nom);
            $('#cat_est').val(data.est);
            $('#cat_form_container').show();
        });
    });

    // Eliminar
    $('#categorias_table').on('click', '.btn-del-cat', function () {
        var id = $(this).data('id');
        if (confirm('Eliminar categoría?')) {
            $.post('../../controller/categoria.php?op=delete', { cat_id: id }, function (resp) {
                categoriasTable.ajax.reload();
                $.post('../../controller/categoria.php?op=combo', function (data) {
                    $('#cat_id').html(data);
                });
            });
        }
    });
</script>