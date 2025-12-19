// detalleticket.js - parche completo listo para pegar
var tabla;
var originalCategory = null;
var categoryChanged = false;

function init() {

}

$(document).ready(function () {
    var tick_id = getUrlParameter('ID');

    // Cargar combo de categorias primero para evitar condiciones de carrera
    $.post("../../controller/categoria.php?op=combo", function (data, status) {
        $('#cat_id').html(data);

        // Después de cargar las categorías, cargamos el detalle del ticket
        listardetalle(tick_id);
    });

    $('#cliente_id').select2({
        placeholder: 'Buscar y seleccionar cliente',
        allowClear: true,
        width: '100%',
    });

    // Cargar opciones desde el servidor para clientes
    $.post("../../controller/cliente.php?op=combo", function (data) {
        $('#cliente_id').html(data);
        $('#cliente_id').trigger('change.select2');
    });

    // Detectar cambio en el select de categoría
    $(document).on('change', '#cat_id', function () {
        var selected = $(this).val();
        categoryChanged = (selected != originalCategory);
    });

    $('#tickd_descrip').summernote({
        height: 400,
        lang: "es-ES",
        callbacks: {
            onImageUpload: function (image) {
                console.log("Image detect...");
                myimagetreat(image[0]);
            },
            onPaste: function (e) {
                console.log("Text detect...");
            }
        },
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });

    $('#tickd_descripusu').summernote({
        height: 400,
        lang: "es-ES",
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });

    $('#tickd_descripusu').summernote('disable');

    tabla = $('#documentos_data').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        "searching": true,
        lengthChange: false,
        colReorder: true,
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ],
        "ajax": {
            url: '../../controller/documento.php?op=listar',
            type: "post",
            data: { tick_id: tick_id },
            dataType: "json",
            error: function (e) {
                console.log(e.responseText);
            }
        },
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
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
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

});

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

$(document).on("click", "#btnenviar", function () {
    var tick_id = getUrlParameter('ID');
    var usu_id = $('#user_idx').val();
    var tickd_descrip = $('#tickd_descrip').val();
    var pot_desp = $('#pot_desp').val();
    var cat_id = $('#cat_id').val();

    var hasDescription = !$('#tickd_descrip').summernote('isEmpty');

    if (!hasDescription && !categoryChanged) {
        swal("Advertencia!", "Falta Descripción o no se detectó cambio de categoría", "warning");
        return;
    }

    // Función para guardar detalle (si existe descripción)
    var saveDetail = function (callback) {
        if (hasDescription) {
            $.post("../../controller/ticket.php?op=insertdetalle", { tick_id: tick_id, usu_id: usu_id, tickd_descrip: tickd_descrip }, function (data) {
                $('#tickd_descrip').summernote('reset');
                if (callback) callback();
            });
        } else {
            if (callback) callback();
        }
    };

    // Función para guardar categoría si cambió
    var saveCategory = function (callback) {
        if (categoryChanged) {
            console.log("Guardando categoría. tick_id:", tick_id, "cat_id:", cat_id);
            $.post("../../controller/ticket.php?op=update_categoria", { tick_id: tick_id, cat_id: cat_id }, function (data) {
                try {
                    var resp = (typeof data === 'object') ? data : JSON.parse(data);
                    if (resp.success) {
                        console.log("update_categoria: success");
                        // actualizar bandera y original
                        originalCategory = cat_id;
                        categoryChanged = false;
                        if (callback) callback();
                    } else {
                        console.error("update_categoria: fallo en servidor", resp);
                        swal("Error!", "No se pudo actualizar la categoría en el servidor.", "error");
                    }
                } catch (e) {
                    console.error("update_categoria: respuesta inválida", data, e);
                    swal("Error!", "Respuesta inválida del servidor al actualizar categoría.", "error");
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error("update_categoria: request failed", textStatus, errorThrown, jqXHR.responseText);
                swal("Error!", "Fallo en la petición para actualizar categoría.", "error");
            });
        } else {
            if (callback) callback();
        }
    };

    // Función para guardar potencia
    var savePotencia = function (callback) {
        $.post("../../controller/ticket.php?op=update_potencia", { tick_id: tick_id, pot_desp: pot_desp }, function (data) {
            if (callback) callback();
        });
    };

    // Ejecutar en secuencia: detalle -> categoria -> potencia -> refrescar
    saveDetail(function () {
        saveCategory(function () {
            savePotencia(function () {
                listardetalle(tick_id);
                swal("Correcto!", "Registrado Correctamente", "success");
            });
        });
    });
});

$(document).on("click", "#btnesperaticket", function () {
    swal({
        title: "HelpDesk",
        text: "¿Está seguro de poner el Ticket en Espera?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-warning",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false
    },
        function (isConfirm) {
            if (isConfirm) {
                var tick_id = getUrlParameter('ID');
                var usu_id = $('#user_idx').val();

                // Usamos 'cambiar_estado' y enviamos el estado explícito 'En espera'
                $.post("../../controller/ticket.php?op=cambiar_estado", { tick_id: tick_id, estado: 'En espera', usu_id: usu_id }, function (data) {

                    // Refrescamos la lista para ver el nuevo log de "Ticket en espera..."
                    listardetalle(tick_id);

                    swal({
                        title: "HelpDesk!",
                        text: "Ticket puesto en Espera correctamente.",
                        type: "success",
                        confirmButtonClass: "btn-success"
                    });
                });
            }
        });
});

$(document).on("click", "#btncerrarticket", function () {
    swal({
        title: "HelpDesk",
        text: "Esta seguro de Cerrar el Ticket?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-warning",
        confirmButtonText: "Si",
        cancelButtonText: "No",
        closeOnConfirm: false
    },
        function (isConfirm) {
            if (isConfirm) {
                var tick_id = getUrlParameter('ID');
                var usu_id = $('#user_idx').val();
                $.post("../../controller/ticket.php?op=update", { tick_id: tick_id, usu_id: usu_id }, function (data) {

                });

                $.post("../../controller/email.php?op=ticket_cerrado", { tick_id: tick_id }, function (data) {

                });


                listardetalle(tick_id);

                swal({
                    title: "HelpDesk!",
                    text: "Ticket Cerrado correctamente.",
                    type: "success",
                    confirmButtonClass: "btn-success"
                });
            }
        });
});

function listardetalle(tick_id) {
    $.post("../../controller/ticket.php?op=listardetalle", { tick_id: tick_id }, function (data) {
        $('#lbldetalle').html(data);
    });

    $.post("../../controller/ticket.php?op=mostrar", { tick_id: tick_id }, function (data) {
        data = JSON.parse(data);
        $('#lblestado').html(data.tick_estado);
        $('#lblnomusuario').html(data.usu_nom + ' ' + data.usu_ape);
        $('#lblfechcrea').html(data.fech_crea);

        $('#lblnomidticket').html("Detalle Ticket - " + data.tick_id);

        // Asignar categoría y guardar la original para detectar cambios
        if (typeof data.cat_id !== 'undefined' && data.cat_id !== null) {
            $('#cat_id').val(data.cat_id).trigger('change');
            originalCategory = data.cat_id;
            categoryChanged = false;
        }

        $('#tick_titulo').val(data.tick_titulo);
        $('#tickd_descripusu').summernote('code', data.tick_descrip);
        $('#pot_ant').val(data.pot_antes);
        $('#pot_desp').val(data.pot_desp);

        console.log(data.tick_estado_texto);
        if (data.tick_estado_texto == "Cerrado") {
            $('#pnldetalle').hide();
        } else {
            $('#pnldetalle').show();
        }
    });
}

init();