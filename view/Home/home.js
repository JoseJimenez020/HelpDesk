
function init() {
    // Placeholder de inicialización
}

// Formatea Date a YYYY-MM-DD
function formatDateISO(dt) {
    var y = dt.getFullYear();
    var m = ('0' + (dt.getMonth() + 1)).slice(-2);
    var d = ('0' + dt.getDate()).slice(-2);
    return y + '-' + m + '-' + d;
}

function loadMonthOptions() {
    var meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    
    // Obtenemos el año actual
    var year = new Date().getFullYear();
    
    // Limpiamos y agregamos opción por defecto
    $('#select_mes_graf').empty();
    $('#select_mes_graf').append('<option value="">Seleccionar Mes</option>');

    for (var i = 0; i < meses.length; i++) {
        // El value será el índice del mes (0 para Enero, 11 para Diciembre)
        $('#select_mes_graf').append('<option value="' + i + '">' + meses[i] + ' ' + year + '</option>');
    }
}

// Convierte "YYYY-MM-DD|YYYY-MM-DD" a objeto {start, end}
function getWeekRangeFromString(rangeStr) {
    var parts = rangeStr.split('|');
    return { start: parts[0], end: parts[1] };
}

// Calcula lunes y domingo de la semana actual/dada
function getWeekRange(date) {
    var d = new Date(date);
    var day = d.getDay(); 
    var diffToMonday = (day + 6) % 7;
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
    var total = Math.round(parseFloat(mins));
    var hours = Math.floor(total / 60);
    var minutes = total % 60;
    var parts = [];
    if (hours > 0) parts.push(hours + 'hr');
    parts.push(minutes + 'min');
    return parts.join(' ');
}

// --- NUEVA FUNCIÓN GENÉRICA PARA CARGAR SEMANAS EN CUALQUIER SELECT ---
function loadWeekOptions(selector_id, usu_id) {
    $.post("../../controller/usuario.php?op=semanas", { usu_id: usu_id }, function (html) {
        $(selector_id).html(html);
        
        // Si no hay opciones, crear la semana actual
        if ($(selector_id + ' option').length === 0) {
            var wk = getWeekRange(new Date());
            var display = wk.start.split('-').reverse().join('/') + " - " + wk.end.split('-').reverse().join('/');
            var val = wk.start + '|' + wk.end;
            $(selector_id).append($('<option>', { value: val, text: 'Semana ' + display }));
        }
        
        // Seleccionar la primera opción y disparar el evento change para cargar la gráfica
        $(selector_id).prop('selectedIndex', 0).trigger('change');
    }).fail(function () {
        var wk = getWeekRange(new Date());
        var display = wk.start.split('-').reverse().join('/') + " - " + wk.end.split('-').reverse().join('/');
        var val = wk.start + '|' + wk.end;
        $(selector_id).html('');
        $(selector_id).append($('<option>', { value: val, text: 'Semana ' + display }));
        $(selector_id).prop('selectedIndex', 0).trigger('change');
    });
}

// --- FUNCIÓN 1: Carga SOLO el Gráfico Estadístico (#divgrafico) ---
function loadGraficoEstadistico(usu_id, rol_id, start_date, end_date) {
    $('#divgrafico').empty(); // Limpiar solo este gráfico
    
    // Preparar fechas para el controlador
    var start_dt = start_date ? (start_date + " 00:00:00") : null;
    var end_dt = end_date ? (end_date + " 23:59:59") : null;

    if (rol_id == 1) {
        // --- USUARIO: Gráfico por categoría ---
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
                $('#divgrafico').html('<div class="text-muted text-center" style="padding-top:100px;">No hay datos para la semana seleccionada.</div>');
            }
        });
    } else {
        // --- SOPORTE: Gráfico global (ticket.php) ---
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
                $('#divgrafico').html('<div class="text-muted text-center" style="padding-top:100px;">No hay datos para la semana seleccionada.</div>');
            }
        });
    }
}

// --- FUNCIÓN 2: Carga el Gráfico de Tiempo y los Totales (Cards) ---
function loadChartsAndTotals(usu_id, rol_id, start_date, end_date) {
    $('#divgraficotiempo').empty(); // Limpiar solo gráfico de tiempo

    var start_dt = start_date ? (start_date + " 00:00:00") : null;
    var end_dt = end_date ? (end_date + " 23:59:59") : null;

    // 1. Cargar Totales (Cajas de colores)
    // Nota: Usamos las rutas dependiendo del rol
    var controller = (rol_id == 1) ? "../../controller/usuario.php" : "../../controller/ticket.php";
    var params = (rol_id == 1) ? { usu_id: usu_id, start_date: start_date, end_date: end_date } : { start_date: start_date, end_date: end_date };

    $.post(controller + "?op=total", params, function (data) {
        try { $('#lbltotal').html(JSON.parse(data).TOTAL || 0); } catch (e) { $('#lbltotal').html('0'); }
    });
    $.post(controller + "?op=totalabierto", params, function (data) {
        try { $('#lbltotalabierto').html(JSON.parse(data).TOTAL || 0); } catch (e) { $('#lbltotalabierto').html('0'); }
    });
    $.post(controller + "?op=totalcerrado", params, function (data) {
        try { $('#lbltotalcerrado').html(JSON.parse(data).TOTAL || 0); } catch (e) { $('#lbltotalcerrado').html('0'); }
    });

    // 2. Cargar Gráfico de Tiempo de Resolución (Siempre usa usuario.php?op=grafico_tiempo)
    // Nota: El controller de usuario maneja la lógica global si no se pasa usu_id o si es soporte, 
    // pero tu código original usaba la misma ruta para ambos roles en el gráfico de tiempo.
    $.post("../../controller/usuario.php?op=grafico_tiempo", { usu_id: usu_id, start_date: start_date, end_date: end_date }, function (data) {
        try {
            var parsed = JSON.parse(data);
            if (!parsed || parsed.length === 0) {
                $('#divgraficotiempo').html('<div class="text-muted text-center" style="padding-top:100px;">No hay datos para la semana seleccionada.</div>');
                return;
            }

            new Morris.Bar({
                element: 'divgraficotiempo',
                data: parsed,
                xkey: 'nom',
                ykeys: ['total'],
                labels: ['Promedio'],
                barColors: ["#17C2A4"],
                resize: true,
                hideHover: 'auto',
                postUnits: ' min',
                yLabelFormat: function (y) { return formatMinutesToHrMin(y); },
                hoverCallback: function (index, options, content, row) {
                    var label = options.labels[0] || 'Promedio';
                    return '<div class="morris-hover-row-label">' + row.nom + '</div>' +
                           '<div class="morris-hover-point">' + label + ': ' + formatMinutesToHrMin(row.total) + '</div>';
                }
            });
            $('#tiempo_unidad').text('Promedio (min)');
        } catch (e) {
            $('#divgraficotiempo').html('<div class="text-muted text-center" style="padding-top:100px;">No hay datos para la semana seleccionada.</div>');
        }
    });
}

$(document).ready(function () {
    init();

    var usu_id = $('#user_idx').val();
    var rol_id = $('#rol_idx').val();

    if (typeof usu_id === 'undefined' || typeof rol_id === 'undefined') {
        console.warn('home.js: faltan #user_idx o #rol_idx en la vista.');
        return;
    }

    // 1. Configurar SELECT para Gráfico Estadístico (#select_semana_graf)
    loadWeekOptions('#select_semana_graf', usu_id);
    loadMonthOptions(); // Cargar opciones de mes
    
    $('#select_mes_graf').on('change', function() {
        var mesIndex = $(this).val();

        // Si se selecciona "Seleccionar Mes" (vacío), no hacemos nada o recargamos la semana actual
        if (mesIndex === "") return;

        // Resetear el selector de semanas para que el usuario sepa que está viendo MESES
        // (Ponemos el valor en null o vacío visualmente)
        $('#select_semana_graf').val(''); 

        // Calcular primer y último día del mes seleccionado
        var year = new Date().getFullYear();
        var primerDia = new Date(year, mesIndex, 1);
        var ultimoDia = new Date(year, parseInt(mesIndex) + 1, 0); // El día 0 del siguiente mes es el último del actual

        // Usamos tu función existente formatDateISO [cite: 5]
        var start_date = formatDateISO(primerDia);
        var end_date = formatDateISO(ultimoDia);

        // Llamamos a tu función existente de Gráfico [cite: 17]
        loadGraficoEstadistico(usu_id, rol_id, start_date, end_date);
    });

    $('#select_semana_graf').on('change', function () {
        var val = $(this).val();
        if (!val) return;

        // Resetear el selector de Meses para indicar que ahora mandan las SEMANAS
        $('#select_mes_graf').val('');

        var range = getWeekRangeFromString(val);
        // LLAMA SOLO A LA FUNCIÓN DEL GRÁFICO ESTADÍSTICO
        loadGraficoEstadistico(usu_id, rol_id, range.start, range.end);
    });

    // 2. Configurar SELECT para Tiempo y Totales (#select_semana)
    loadWeekOptions('#select_semana', usu_id);

    $('#select_semana').on('change', function () {
        var val = $(this).val();
        if (!val) return;
        var range = getWeekRangeFromString(val);
        // LLAMA A LA FUNCIÓN DE TIEMPO Y TOTALES
        loadChartsAndTotals(usu_id, rol_id, range.start, range.end);
    });
});