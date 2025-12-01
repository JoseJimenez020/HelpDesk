var tabla;
var usu_id = $('#user_idx').val();
var rol_id = $('#rol_idx').val();

function init() {
    $("#ticket_form").on("submit", function (e) {
        guardar(e);
    });
}

function getFilters() {
    return {
        estado: $('#filter_estado').val() || '',
        cliente_id: $('#filter_cliente').val() || '',
        fecha_ini: $('#filter_fecha_ini').val() || '',
        fecha_fin: $('#filter_fecha_fin').val() || ''
    };
}

function initDataTable(urlAjax) {
    if ($.fn.DataTable.isDataTable('#ticket_data')) {
        $('#ticket_data').DataTable().destroy();
    }
    tabla = $('#ticket_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            // Convierte HTML a texto plano
                            var tmp = document.createElement("DIV");
                            tmp.innerHTML = data;
                            return tmp.textContent || tmp.innerText || "";
                        }
                    }
                },
                customize: function (doc) {
                    doc.pageMargins = [10, 10, 10, 10];
                    doc.defaultStyle = doc.defaultStyle || {};
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableHeader = doc.styles.tableHeader || {};
                    doc.styles.tableHeader.fontSize = 9;
                    doc.styles.tableHeader.alignment = 'left';

                    var colCount = doc.content[1].table.body[0].length;
                    var widths = [];
                    for (var i = 0; i < colCount; i++) {
                        if (i === 2) widths.push('*');
                        else widths.push('auto');
                    }
                    doc.content[1].table.widths = widths;

                    var body = doc.content[1].table.body;
                    for (var r = 0; r < body.length; r++) {
                        for (var c = 0; c < body[r].length; c++) {
                            body[r][c].margin = [3, 3, 3, 3];
                        }
                    }
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            var tmp = document.createElement("DIV");
                            tmp.innerHTML = data;
                            return tmp.textContent || tmp.innerText || "";
                        }
                    }
                }
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            var tmp = document.createElement("DIV");
                            tmp.innerHTML = data;
                            return tmp.textContent || tmp.innerText || "";
                        }
                    }
                }
            },
            {
                extend: 'copyHtml5',
                text: 'Copiar',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            var tmp = document.createElement("DIV");
                            tmp.innerHTML = data;
                            return tmp.textContent || tmp.innerText || "";
                        }
                    }
                }
            }
        ],
        "ajax": {
            url: urlAjax,
            type: "post",
            dataType: "json",
            data: function (d) {
                var f = getFilters();
                d.estado = f.estado;
                d.cliente_id = f.cliente_id;
                d.fecha_ini = f.fecha_ini;
                d.fecha_fin = f.fecha_fin;
                if (rol_id == 1) d.usu_id = usu_id;
            },
            error: function (e) {
                console.log(e.responseText);
            }
        },
        "ordering": false,
        "bDestroy": true,
        "responsive": true,
        "bInfo": true,
        "iDisplayLength": 10,
        "autoWidth": false,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    }).DataTable();
}

$(document).ready(function () {

    // Cargar combo de clientes (controller/cliente.php?op=combo ya existe)
    $.post("../../controller/cliente.php?op=combo", function (data) {
        $('#filter_cliente').append(data);
    });

    // Inicializar DataTable con la URL correspondiente
    if (rol_id == 1) {
        initDataTable('../../controller/ticket.php?op=listar_x_usu');
    } else {
        initDataTable('../../controller/ticket.php?op=listar');
    }

    // Botones de filtrar y reset
    $('#btn_filtrar').on('click', function () {
        // Validación simple de rango de fechas
        var fi = $('#filter_fecha_ini').val();
        var ff = $('#filter_fecha_fin').val();
        if (fi && ff && fi > ff) {
            swal("Error", "La fecha inicial no puede ser mayor a la final", "error");
            return;
        }
        $('#ticket_data').DataTable().ajax.reload();
    });

    $('#btn_reset').on('click', function () {
        $('#filter_estado').val('');
        $('#filter_cliente').val('');
        $('#filter_fecha_ini').val('');
        $('#filter_fecha_fin').val('');
        $('#ticket_data').DataTable().ajax.reload();
    });

    // Recargar al cambiar filtros (opcional)
    $('#filter_estado, #filter_cliente').on('change', function () {
        $('#ticket_data').DataTable().ajax.reload();
    });

});

function ver(tick_id) {
    window.open('http://fastnetflow.fast-net.net/view/DetalleTicket/?ID=' + tick_id + '');
}

function asignar(tick_id) {
    $.post("../../controller/ticket.php?op=mostrar", { tick_id: tick_id }, function (data) {
        data = JSON.parse(data);
        $('#tick_id').val(data.tick_id);
        $('#mdltitulo').html('Asignar Agente');
        $("#modalasignar").modal('show');
    });
}

function MoverEstado(tick_id) {
    $.post("../../controller/ticket.php?op=mostrar", { tick_id: tick_id }, function (data) {
        data = JSON.parse(data);
        $('#tick_id').val(data.tick_id);
        $('#estado').val(data.tick_estado_texto).trigger('change');
        $('#mdltitulo').html('Cambiar Estado');
        $("#modalestado").modal('show');
    });
}

$(document).on('submit', '#ticket_estado_form', function (e) {
    e.preventDefault();
    var form = $(this);
    var formData = {
        tick_id: $('#tick_id').val(),
        estado: $('#estado').val()
    };
    $.post("../../controller/ticket.php?op=cambiar_estado", formData, function (response) {
        $('#modalestado').modal('hide');
        $('#ticket_data').DataTable().ajax.reload();
        swal("Correcto!", "Estado actualizado", "success");
    });
});

function guardar(e) {
    e.preventDefault();
    var formData = new FormData($("#ticket_form")[0]);
    $.ajax({
        url: "../../controller/ticket.php?op=asignar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (datos) {
            var tick_id = $('#tick_id').val();
            $.post("../../controller/email.php?op=ticket_asignado", { tick_id: tick_id }, function (data) { });
            swal("Correcto!", "Asignado Correctamente", "success");
            $("#modalasignar").modal('hide');
            $('#ticket_data').DataTable().ajax.reload();
        }
    });
}

function CambiarEstado(tick_id) {
    swal({
        title: "HelpDesk",
        text: "Esta seguro de Reabrir el Ticket?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-warning",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false
    },
        function (isConfirm) {
            if (isConfirm) {
                $.post("../../controller/ticket.php?op=reabrir", { tick_id: tick_id, usu_id: usu_id }, function (data) { });
                $('#ticket_data').DataTable().ajax.reload();
                swal({
                    title: "HelpDesk!",
                    text: "Ticket Abierto.",
                    type: "success",
                    confirmButtonClass: "btn-success"
                });
            }
        });
}

init();