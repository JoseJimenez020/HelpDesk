function init() {

}
// home.js - completo listo para pegar
// Requiere: jQuery, Morris.js, raphael
// Usa los inputs #user_idx y #rol_idx que ya existen en la vista

// Formatea Date a YYYY-MM-DD
function formatDateISO(dt) {
    var y = dt.getFullYear();
    var m = ('0' + (dt.getMonth() + 1)).slice(-2);
    var d = ('0' + dt.getDate()).slice(-2);
    return y + '-' + m + '-' + d;
}

// Convierte "YYYY-MM-DD|YYYY-MM-DD" a objeto {start, end}
function getWeekRangeFromString(rangeStr) {
    var parts = rangeStr.split('|');
    return { start: parts[0], end: parts[1] };
}

// Calcula lunes y domingo de la semana que contiene la fecha dada
function getWeekRange(date) {
    var d = new Date(date);
    var day = d.getDay(); // 0 (Dom) .. 6 (Sab)
    // Queremos semana que empieza Lunes (1)
    var diffToMonday = (day + 6) % 7; // 0->6, 1->0, 2->1, ...
    var monday = new Date(d);
    monday.setDate(d.getDate() - diffToMonday);
    monday.setHours(0, 0, 0, 0);
    var sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    sunday.setHours(23, 59, 59, 999);
    return { start: formatDateISO(monday), end: formatDateISO(sunday) };
}

function formatMinutesToHrMin(mins) {
    if (mins === null || mins === undefined || isNaN(mins)) return '';
    var total = Math.round(parseFloat(mins)); // redondea al minuto más cercano
    var hours = Math.floor(total / 60);
    var minutes = total % 60;
    var parts = [];
    if (hours > 0) parts.push(hours + 'hr');
    parts.push(minutes + 'min');
    return parts.join(' ');
}


// Carga las opciones de semanas desde el servidor (controller/usuario.php?op=semanas)
function loadWeekOptions(usu_id) {
    $.post("../../controller/usuario.php?op=semanas", { usu_id: usu_id }, function (html) {
        $('#select_semana').html(html);
        // Si no hay opciones, crear la semana actual como opción
        if ($('#select_semana option').length === 0) {
            var wk = getWeekRange(new Date());
            var display = wk.start.split('-').reverse().join('/') + " - " + wk.end.split('-').reverse().join('/');
            var val = wk.start + '|' + wk.end;
            $('#select_semana').append($('<option>', { value: val, text: 'Semana ' + display }));
        }
        // Seleccionar la primera opción (más reciente) por defecto y disparar change
        $('#select_semana').prop('selectedIndex', 0).trigger('change');
    }).fail(function () {
        // En caso de fallo, crear opción con semana actual
        var wk = getWeekRange(new Date());
        var display = wk.start.split('-').reverse().join('/') + " - " + wk.end.split('-').reverse().join('/');
        var val = wk.start + '|' + wk.end;
        $('#select_semana').html('');
        $('#select_semana').append($('<option>', { value: val, text: 'Semana ' + display }));
        $('#select_semana').prop('selectedIndex', 0).trigger('change');
    });
}

// Limpia los contenedores de las gráficas antes de dibujar
function clearCharts() {
    $('#divgrafico').empty();
    $('#divgraficotiempo').empty();
}

// Carga totales y gráficas según rol y rango de fechas
function loadChartsAndTotals(usu_id, rol_id, start_date, end_date) {
    clearCharts();

    // Normalizar a DATETIME para BETWEEN en el servidor
    var start_dt = start_date ? (start_date + " 00:00:00") : null;
    var end_dt = end_date ? (end_date + " 23:59:59") : null;

    if (rol_id == 1) {
        // Totales por usuario (filtrados por semana si vienen fechas)
        $.post("../../controller/usuario.php?op=total", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotal').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotal').html('0');
            }
        });

        $.post("../../controller/usuario.php?op=totalabierto", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotalabierto').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotalabierto').html('0');
            }
        });

        $.post("../../controller/usuario.php?op=totalcerrado", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotalcerrado').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotalcerrado').html('0');
            }
        });

        // Grafico por categoria (usuario)
        $.post("../../controller/usuario.php?op=grafico", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
            try {
                var parsed = JSON.parse(data);
                new Morris.Bar({
                    element: 'divgrafico',
                    data: parsed,
                    xkey: 'nom',
                    ykeys: ['total'],
                    labels: ['Tickets'],
                    barColors: ["#1AB244"],
                    resize: true,
                    hideHover: 'auto'
                });
            } catch (e) {
                $('#divgrafico').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
            }
        });

        // Grafico tiempo (promedio)
        $.post("../../controller/usuario.php?op=grafico_tiempo", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
            try {
                var parsed = JSON.parse(data);
                if (!parsed || parsed.length === 0) {
                    $('#divgraficotiempo').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
                    return;
                }

                // Dibujar gráfico con Morris y mostrar unidades en etiquetas del eje Y
                new Morris.Bar({
                    element: 'divgraficotiempo',
                    data: parsed,
                    xkey: 'nom',
                    ykeys: ['total'],
                    labels: ['Promedio'],
                    barColors: ["#17C2A4"],
                    resize: true,
                    hideHover: 'auto',
                    // Añade " min" a cada tick del eje Y
                    postUnits: ' min',
                    // Formatea el texto del hover y ticks (opcional, por si quieres decimales)
                    yLabelFormat: function (y) {
                        return formatMinutesToHrMin(y);
                    },

                    // Opcional: mostrar hover siempre en formato "Promedio: Yhr Xmin"
                    hoverCallback: function (index, options, content, row) {
                        var label = options.labels[0] || 'Promedio';
                        var value = row.total;
                        return '<div class="morris-hover-row-label">' + row.nom + '</div>' +
                            '<div class="morris-hover-point">' + label + ': ' + formatMinutesToHrMin(value) + '</div>';
                    }

                });

                // Asegurar que la etiqueta fija "Promedio (min)" esté visible en el header
                $('#tiempo_unidad').text('Promedio (min)');
            } catch (e) {
                $('#divgraficotiempo').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
            }
        });

    } else {
        // Rol soporte: totales y grafico global (ticket.php) y grafico tiempo (usuario.php)
        $.post("../../controller/ticket.php?op=total", { start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotal').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotal').html('0');
            }
        });

        $.post("../../controller/ticket.php?op=totalabierto", { start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotalabierto').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotalabierto').html('0');
            }
        });

        $.post("../../controller/ticket.php?op=totalcerrado", { start_date: start_date, end_date: end_date }, function (data) {
            try {
                data = JSON.parse(data);
                $('#lbltotalcerrado').html(data.TOTAL || 0);
            } catch (e) {
                $('#lbltotalcerrado').html('0');
            }
        });

        // Grafico global por categoria (ticket.php)
        $.post("../../controller/ticket.php?op=grafico", { start_date: start_date, end_date: end_date }, function (data) {
            try {
                var parsed = JSON.parse(data);
                new Morris.Bar({
                    element: 'divgrafico',
                    data: parsed,
                    xkey: 'nom',
                    ykeys: ['total'],
                    labels: ['Tickets'],
                    resize: true,
                    hideHover: 'auto'
                });
            } catch (e) {
                $('#divgrafico').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
            }
        });

        // Grafico tiempo (promedio global por categoria)
        $.post("../../controller/usuario.php?op=grafico_tiempo", { start_date: start_date, end_date: end_date }, function (data) {
            try {
                var parsed = JSON.parse(data);
                if (!parsed || parsed.length === 0) {
                    $('#divgraficotiempo').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
                    return;
                }

                // Dibujar gráfico con Morris y mostrar unidades en etiquetas del eje Y
                new Morris.Bar({
                    element: 'divgraficotiempo',
                    data: parsed,
                    xkey: 'nom',
                    ykeys: ['total'],
                    labels: ['Promedio'],
                    barColors: ["#17C2A4"],
                    resize: true,
                    hideHover: 'auto',
                    // Añade " min" a cada tick del eje Y
                    postUnits: ' min',
                    // Formatea el texto del hover y ticks (opcional, por si quieres decimales)
                    yLabelFormat: function (y) {
                        return formatMinutesToHrMin(y);
                    },

                    // Opcional: mostrar hover siempre en formato "Promedio: Yhr Xmin"
                    hoverCallback: function (index, options, content, row) {
                        var label = options.labels[0] || 'Promedio';
                        var value = row.total;
                        return '<div class="morris-hover-row-label">' + row.nom + '</div>' +
                            '<div class="morris-hover-point">' + label + ': ' + formatMinutesToHrMin(value) + '</div>';
                    }

                });

                // Asegurar que la etiqueta fija "Promedio (min)" esté visible en el header
                $('#tiempo_unidad').text('Promedio (min)');
            } catch (e) {
                $('#divgraficotiempo').html('<div class="text-muted">No hay datos para la semana seleccionada.</div>');
            }
        });
    }
}

// Inicialización mínima (mantiene compatibilidad con tu init original)
function init() {
    // placeholder si necesitas inicializar algo más
}

$(document).ready(function () {
    init();

    var usu_id = $('#user_idx').val();
    var rol_id = $('#rol_idx').val();

    // Si no existen los inputs esperados, no hacer nada
    if (typeof usu_id === 'undefined' || typeof rol_id === 'undefined') {
        console.warn('home.js: faltan #user_idx o #rol_idx en la vista.');
        return;
    }

    // Cargar opciones de semanas y establecer comportamiento al cambiar
    loadWeekOptions(usu_id);

    $('#select_semana').on('change', function () {
        var val = $(this).val(); // formato: "YYYY-MM-DD|YYYY-MM-DD"
        if (!val) return;
        var range = getWeekRangeFromString(val);
        loadChartsAndTotals(usu_id, rol_id, range.start, range.end);
    });

    // Si quieres recargar la semana actual con un botón, puedes añadirlo y llamar:
    // var wk = getWeekRange(new Date()); loadChartsAndTotals(usu_id, rol_id, wk.start, wk.end);
});