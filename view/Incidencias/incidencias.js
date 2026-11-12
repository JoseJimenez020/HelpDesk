/* =========================================================
 * incidencias.js
 * Vista: view/Incidencias/index.php
 * ========================================================= */

var incidenciasTable = null;

// Meses en español para el label de quincena
var MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
             'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Fechas seleccionadas por tipo — se reinician al abrir/cerrar el modal
var fechas = {
    prima:    [],
    hrs_extra: [],
    festivo:  [],
    falta:    []
};

// Mapeo tipo → picker y contenedor de tags
var TIPO_CFG = {
    prima:    { picker: '#picker_prima',   tags: '#tags_prima' },
    hrs_extra: { picker: '#picker_hrs',    tags: '#tags_hrs_extra' },
    festivo:  { picker: '#picker_festivo', tags: '#tags_festivo' },
    falta:    { picker: '#picker_falta',   tags: '#tags_falta' }
};

/* ---------------------------------------------------------
 * INIT
 * --------------------------------------------------------- */
$(document).ready(function () {

    // Llenar combo de años (año actual y 3 anteriores)
    var anioActual = new Date().getFullYear();
    for (var y = anioActual; y >= anioActual - 3; y--) {
        $('#filter_anio').append('<option value="' + y + '">' + y + '</option>');
    }

    // Mes y quincena por defecto según hoy
    var hoy       = new Date();
    var mesActual = hoy.getMonth() + 1;
    var dia       = hoy.getDate();
    $('#filter_mes').val(mesActual);
    $('#filter_quincena').val(dia <= 15 ? '1' : '2');

    cargarTabla();

    // ----- Eventos -----

    $('#btn_filtrar').on('click', function () { cargarTabla(); });
    $('#btnnuevo').on('click',    function () { abrirModalNuevo(); });
    $('#btn_guardar_inc').on('click', function () { guardarIncidencia(); });

    // Botones "+" de cada tipo (delegado en el modal)
    $('#modalIncidencias').on('click', '.btn-add-fecha', function () {
        var tipo = $(this).data('tipo');
        agregarFecha(tipo);
    });

    // Enter en cualquier picker
    $('#modalIncidencias').on('keydown', 'input[type="date"]', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var tipo = $(this).closest('.inc-picker-group').find('.btn-add-fecha').data('tipo');
            if (tipo) agregarFecha(tipo);
        }
    });

    // Quitar tag (delegado)
    $('#modalIncidencias').on('click', '.remove-fecha', function () {
        var tipo = $(this).data('tipo');
        var idx  = parseInt($(this).data('idx'));
        quitarFecha(tipo, idx);
    });

    // Limpiar modal al cerrarse
    $('#modalIncidencias').on('hidden.bs.modal', function () { resetModal(); });
});

/* ---------------------------------------------------------
 * TABLA DataTables — 6 columnas (sin Fechas)
 * --------------------------------------------------------- */
function cargarTabla() {

    var anio     = $('#filter_anio').val();
    var mes      = $('#filter_mes').val();
    var quincena = $('#filter_quincena').val();

    if (incidenciasTable) {
        incidenciasTable.settings()[0].ajax.data = { anio: anio, mes: mes, quincena: quincena };
        incidenciasTable.ajax.reload();
        return;
    }

    incidenciasTable = $('#incidencias_data').DataTable({
        ajax: {
            url:  '../../controller/incidencia.php?op=listar',
            type: 'POST',
            data: { anio: anio, mes: mes, quincena: quincena }
        },
        columns: [
            { title: 'Nombre' },
            { title: 'Prima Dominical',  className: 'text-center' },
            { title: 'Horas Extra',      className: 'text-center' },
            { title: 'Festivo Laborado', className: 'text-center' },
            { title: 'Faltas',           className: 'text-center' },
            { title: '', orderable: false, className: 'text-center' }
        ],
        language: {
            url: '../../assets/datatables-es.json'
        },
        order: [[0, 'asc']],
        pageLength: 25
    });
}

/* ---------------------------------------------------------
 * MODAL — NUEVO
 * --------------------------------------------------------- */
function abrirModalNuevo() {
    resetModal();

    var anio     = $('#filter_anio').val();
    var mes      = $('#filter_mes').val();
    var quincena = $('#filter_quincena').val();

    $('#inc_anio').val(anio);
    $('#inc_mes').val(mes);
    $('#inc_quincena').val(quincena);
    $('#inc_quincena_label').val(
        (quincena == 1 ? 'Primera (1–15) ' : 'Segunda (16–fin) ') +
        MESES[parseInt(mes)] + ' ' + anio
    );

    $('#modalIncidenciasLabel').text('Nueva Incidencia');
    $('#modalIncidencias').modal('show');
}

/* ---------------------------------------------------------
 * MODAL — EDITAR
 * --------------------------------------------------------- */
function editarIncidencia(incidencia_id) {
    resetModal();

    $.post('../../controller/incidencia.php?op=mostrar',
        { incidencia_id: incidencia_id },
        function (data) {
            var d = typeof data === 'object' ? data : JSON.parse(data);
            if (!d || d.error) {
                swal('Error', 'No se pudo cargar la incidencia', 'error');
                return;
            }

            $('#inc_id').val(d.incidencia_id);
            $('#inc_anio').val(d.anio);
            $('#inc_mes').val(d.mes);
            $('#inc_quincena').val(d.quincena);
            $('#inc_quincena_label').val(
                (d.quincena == 1 ? 'Primera (1–15) ' : 'Segunda (16–fin) ') +
                MESES[parseInt(d.mes)] + ' ' + d.anio
            );

            $('#inc_prima').val(d.prima_dominical    || 0);
            $('#inc_hrs').val(d.hrs_extra             || 0);
            $('#inc_festivo').val(d.festivo_laborado  || 0);
            $('#inc_faltas').val(d.faltas             || 0);

            // Cargar fechas agrupadas por tipo
            if (d.fechas) {
                $.each(['prima', 'hrs_extra', 'festivo', 'falta'], function (i, tipo) {
                    if (d.fechas[tipo] && d.fechas[tipo].length) {
                        $.each(d.fechas[tipo], function (j, f) {
                            if (f) agregarFechaDirecta(tipo, f);
                        });
                    }
                });
            }

            $('#modalIncidenciasLabel').text('Editar Incidencia — ' + d.usu_nom + ' ' + d.usu_ape);
            $('#modalIncidencias').modal('show');
        }
    );
}

/* ---------------------------------------------------------
 * GUARDAR
 * --------------------------------------------------------- */
function guardarIncidencia() {

    var incidencia_id = $('#inc_id').val();
    var op = incidencia_id ? 'actualizar' : 'insertar';

    $.ajax({
        url:  '../../controller/incidencia.php?op=' + op,
        type: 'POST',
        data: {
            incidencia_id:    incidencia_id,
            anio:             $('#inc_anio').val(),
            mes:              $('#inc_mes').val(),
            quincena:         $('#inc_quincena').val(),
            prima_dominical:  $('#inc_prima').val(),
            hrs_extra:        $('#inc_hrs').val(),
            festivo_laborado: $('#inc_festivo').val(),
            faltas:           $('#inc_faltas').val(),
            fechas_prima:     fechas.prima,
            fechas_hrs:       fechas.hrs_extra,
            fechas_festivo:   fechas.festivo,
            fechas_falta:     fechas.falta
        },
        success: function (resp) {
            var r = typeof resp === 'object' ? resp : JSON.parse(resp);
            if (r.ok) {
                swal('Correcto',
                    op === 'insertar' ? 'Incidencia registrada' : 'Incidencia actualizada',
                    'success');
                $('#modalIncidencias').modal('hide');
                incidenciasTable.ajax.reload();
            } else {
                swal('Error', r.msg || 'No se pudo guardar', 'error');
            }
        },
        error: function () {
            swal('Error', 'Error de comunicación con el servidor', 'error');
        }
    });
}

/* ---------------------------------------------------------
 * ELIMINAR
 * --------------------------------------------------------- */
function eliminarIncidencia(incidencia_id) {
    swal({
        title:  '¿Eliminar incidencia?',
        text:   'Esta acción no se puede deshacer',
        type:   'warning',
        showCancelButton:  true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText:  'Cancelar'
    }, function (confirm) {
        if (!confirm) return;
        $.post('../../controller/incidencia.php?op=eliminar',
            { incidencia_id: incidencia_id },
            function (resp) {
                var r = typeof resp === 'object' ? resp : JSON.parse(resp);
                if (r.ok) {
                    swal('Eliminado', 'Incidencia eliminada', 'success');
                    incidenciasTable.ajax.reload();
                } else {
                    swal('Error', 'No se pudo eliminar', 'error');
                }
            }
        );
    });
}

/* ---------------------------------------------------------
 * GESTIÓN DE FECHAS POR TIPO
 * --------------------------------------------------------- */
function agregarFecha(tipo) {
    var cfg = TIPO_CFG[tipo];
    if (!cfg) return;
    var val = $(cfg.picker).val();
    if (!val) {
        swal('', 'Selecciona una fecha', 'warning');
        return;
    }
    if (fechas[tipo].indexOf(val) >= 0) {
        swal('', 'Esa fecha ya está agregada', 'info');
        return;
    }
    agregarFechaDirecta(tipo, val);
    $(cfg.picker).val('');
}

function agregarFechaDirecta(tipo, fechaISO) {
    fechas[tipo].push(fechaISO);
    renderizarTags(tipo);
}

function quitarFecha(tipo, idx) {
    fechas[tipo].splice(idx, 1);
    renderizarTags(tipo);
}

function renderizarTags(tipo) {
    var cfg   = TIPO_CFG[tipo];
    var $wrap = $(cfg.tags);
    $wrap.empty();

    $.each(fechas[tipo], function (i, f) {
        var p       = f.split('-');
        var display = p[2] + '/' + p[1] + '/' + p[0];
        $wrap.append(
            '<span class="fecha-tag">' + display +
            ' <span class="remove-fecha" data-tipo="' + tipo +
            '" data-idx="' + i + '">&times;</span></span>'
        );
    });
}

/* ---------------------------------------------------------
 * RESET MODAL
 * --------------------------------------------------------- */
function resetModal() {
    $('#incidencia_form')[0].reset();
    $('#inc_id').val('');
    $('#inc_quincena_label').val('');
    $('#modalIncidenciasLabel').text('Nueva Incidencia');

    fechas = { prima: [], hrs_extra: [], festivo: [], falta: [] };

    $.each(TIPO_CFG, function (tipo, cfg) {
        $(cfg.tags).empty();
        $(cfg.picker).val('');
    });
}