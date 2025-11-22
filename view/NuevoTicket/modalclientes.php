<!-- Modal Clientes -->
<div id="modalClientes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestionar Clientes</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <button id="btnNewCli" class="btn btn-success">Agregar Cliente</button>
                </div>

                <table id="clientes_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número de cliente</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div id="cli_form_container" style="display:none; margin-top:15px;">
                    <form id="cli_form">
                        <input type="hidden" id="cli_id_edit" name="cli_id">
                        <div class="form-group">
                            <label for="cli_nom">Número de cliente</label>
                            <input type="text" id="cli_nom" name="cli_nom" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cli_ape">Nombre</label>
                            <input type="text" id="cli_ape" name="cli_ape" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" id="btnCancelCli" class="btn btn-secondary">Cancelar</button>
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
    var clientesTable;

    $('#modalClientes').on('shown.bs.modal', function () {
        if (!clientesTable) {
            clientesTable = $('#clientes_table').DataTable({
                ajax: {
                    url: '../../controller/cliente.php?op=listar',
                    type: 'POST',
                    dataSrc: ''
                },
                columns: [
                    { data: 'cli_id' },
                    { data: 'cli_nom' },
                    { data: 'cli_ape' },
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            return '<button class="btn btn-sm btn-info btn-edit-cli" data-id="' + row.cli_id + '">Editar</button> ' +
                                '<button class="btn btn-sm btn-danger btn-del-cli" data-id="' + row.cli_id + '">Eliminar</button>';
                        }
                    }
                ]
            });
        } else {
            clientesTable.ajax.reload();
        }
    });

    $('#btnNewCli').on('click', function () {
        $('#cli_form_container').show();
        $('#cli_form')[0].reset();
        $('#cli_id_edit').val('');
        $('#cli_nom').focus();
    });

    $('#btnCancelCli').on('click', function () {
        $('#cli_form_container').hide();
    });

    $('#cli_form').on('submit', function (e) {
        e.preventDefault();
        var form = $(this).serialize();
        var op = $('#cli_id_edit').val() ? 'update' : 'insert';
        $.post('../../controller/cliente.php?op=' + op, form, function (resp) {
            try {
                var r = typeof resp === 'object' ? resp : JSON.parse(resp);
            } catch (err) {
                console.error('Respuesta inválida', resp);
                alert('Error en la respuesta del servidor');
                return;
            }

            if (r.ok) {
                clientesTable.ajax.reload();
                $('#cli_form_container').hide();

                // recargar el combo de clientes en la vista de tickets (si lo hay)
                $.post('../../controller/cliente.php?op=combo', function (html) {
                    // si tienes un select #cli_id en la vista lo actualizas
                    $('#cli_id').html(html);
                    if (r.cli_id) {
                        $('#cli_id').val(r.cli_id);
                    }
                });
            } else {
                alert(r.msg || 'Error en la operación');
            }
        }, 'json');
    });

    // Editar
    $('#clientes_table').on('click', '.btn-edit-cli', function () {
        var id = $(this).data('id');
        $.post('../../controller/cliente.php?op=mostrar', { cli_id: id }, function (data) {
            try {
                var d = typeof data === 'object' ? data : JSON.parse(data);
            } catch (err) {
                console.error('Respuesta inválida mostrar', data);
                alert('Error al obtener datos del cliente');
                return;
            }
            $('#cli_id_edit').val(d.cli_id);
            $('#cli_nom').val(d.cli_nom);
            $('#cli_ape').val(d.cli_ape);
            $('#cli_form_container').show();
        });
    });

    // Eliminar
    $('#clientes_table').on('click', '.btn-del-cli', function () {
        var id = $(this).data('id');
        if (confirm('Eliminar cliente?')) {
            $.post('../../controller/cliente.php?op=delete', { cli_id: id }, function (resp) {
                try {
                    var r = typeof resp === 'object' ? resp : JSON.parse(resp);
                } catch (err) {
                    console.error('Respuesta inválida delete', resp);
                    alert('Error en la respuesta del servidor');
                    return;
                }
                clientesTable.ajax.reload();
                // actualizar combo si existe
                $.post('../../controller/cliente.php?op=combo', function (html) {
                    $('#cli_id').html(html);
                });
            }, 'json');
        }
    });
</script>