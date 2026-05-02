// =====================================================================
//  graficotemperatura.js
// =====================================================================

// Instancia global de DataTables para poder destruirla de forma segura
var dtTablaMensual = null;

$(document).ready(function () {

    // ── Gráficos ──────────────────────────────────────────────────────
    $("#divgrafico").html('<canvas id="canvasSemanal"></canvas>');
    $("#divgraficotiempo").html('<canvas id="canvasMensual"></canvas>');

    cargarGraficoSemanal();
    cargarGraficoMensual();

    // ── Fechas por defecto (mes actual) ───────────────────────────────
    var hoy       = new Date();
    var primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
                        .toISOString().split('T')[0];
    var ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0)
                        .toISOString().split('T')[0];

    $("#filter_fecha_ini").val(primerDia);
    $("#filter_fecha_fin").val(ultimoDia);

    // Carga inicial
    generarTablaMensual(primerDia, ultimoDia);

    // ── Botón Filtrar ─────────────────────────────────────────────────
    $("#btn_filtrar_mes").on("click", function () {
        var ini = $("#filter_fecha_ini").val();
        var fin = $("#filter_fecha_fin").val();

        if (!ini || !fin) {
            swal("Aviso", "Selecciona ambas fechas", "warning");
            return;
        }
        if (ini > fin) {
            swal("Error", "La fecha inicial no puede ser mayor a la final", "error");
            return;
        }
        generarTablaMensual(ini, fin);
    });

    // ── Modal al clic en celda ────────────────────────────────────────
    $(document).on("click", ".celda-modal", function () {
        var btn = $(this);
        $("#mdl_sitio").text(btn.data("sitio"));
        $("#mdl_fecha").text(btn.data("fecha"));
        $("#mdl_t07").text(btn.data("t07"));
        $("#mdl_t12").text(btn.data("t12"));
        $("#mdl_t19").text(btn.data("t19"));
        $("#mdl_u07").text("Por: " + btn.data("u07"));
        $("#mdl_u12").text("Por: " + btn.data("u12"));
        $("#mdl_u19").text("Por: " + btn.data("u19"));
        $("#modalDetalleTemp").modal("show");
    });

});


// ── Gráfico semanal ───────────────────────────────────────────────────
function cargarGraficoSemanal() {
    $.post("../../controller/temperatura.php?op=grafico_semanal", function (data) {
        var res = JSON.parse(data);
        new Chart(document.getElementById('canvasSemanal'), {
            type: 'line',
            data: { labels: res.labels, datasets: res.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 10, max: 45 } }
            }
        });
    });
}

// ── Gráfico mensual ───────────────────────────────────────────────────
function cargarGraficoMensual() {
    $.post("../../controller/temperatura.php?op=grafico_mensual", function (data) {
        var res = JSON.parse(data);
        new Chart(document.getElementById('canvasMensual'), {
            type: 'line',
            data: { labels: res.labels, datasets: res.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { autoSkip: true, maxTicksLimit: 10 } },
                    y: { min: 10, max: 45 }
                }
            }
        });
    });
}

// ── Tabla mensual ─────────────────────────────────────────────────────
function generarTablaMensual(f_inicio, f_fin) {

    // 1. Destruir instancia previa y recrear el <table> desde cero.
    //    Recrear el nodo garantiza que no queden atributos/clases residuales de DT.
    if (dtTablaMensual !== null) {
        try { dtTablaMensual.destroy(); } catch (e) { /* ignorar */ }
        dtTablaMensual = null;
    }

    $("#contenedor_tabla_mensual").html(
        '<table id="tabla_mensual" class="table table-bordered table-striped" style="width:100%;">' +
            '<thead><tr></tr></thead><tbody></tbody>' +
        '</table>'
    );

    // 2. Calcular rango de fechas
    var fechasRango = [];
    var dCursor = new Date(f_inicio + "T00:00:00");
    var dFin    = new Date(f_fin    + "T00:00:00");

    while (dCursor <= dFin) {
        fechasRango.push(dCursor.toISOString().split('T')[0]);
        dCursor.setDate(dCursor.getDate() + 1);
    }

    // 3. Encabezado
    var headHtml = '<th style="min-width:150px; white-space:nowrap;">Site / Día</th>';
    fechasRango.forEach(function (f) {
        headHtml += '<th class="text-center" style="min-width:50px;">' + f.split('-')[2] + '</th>';
    });
    $("#tabla_mensual thead tr").html(headHtml);

    // 4. Datos del servidor
    $.post("../../controller/temperatura.php?op=listar_y_datos", {
        f_inicio: f_inicio,
        f_fin:    f_fin
    }, function (data) {
        var res = JSON.parse(data);
        var htmlRows = "";

        res.sitios.forEach(function (sitio) {
            htmlRows += '<tr><td class="font-weight-bold" style="white-space:nowrap;">' +
                            sitio.sitio_nombre + '</td>';

            fechasRango.forEach(function (fecha) {
                var reg07 = res.registros.find(function (r) {
                    return r.sitio_id == sitio.sitio_id && r.fecha_hora === fecha + ' 07:00:00';
                });
                var reg12 = res.registros.find(function (r) {
                    return r.sitio_id == sitio.sitio_id && r.fecha_hora === fecha + ' 12:00:00';
                });
                var reg19 = res.registros.find(function (r) {
                    return r.sitio_id == sitio.sitio_id && r.fecha_hora === fecha + ' 19:00:00';
                });

                var val12 = reg12 ? reg12.temperatura : '-';
                var usu07 = (reg07 && reg07.usu_nom) ? reg07.usu_nom + ' ' + (reg07.usu_ape || '') : 'N/A';
                var usu12 = (reg12 && reg12.usu_nom) ? reg12.usu_nom + ' ' + (reg12.usu_ape || '') : 'N/A';
                var usu19 = (reg19 && reg19.usu_nom) ? reg19.usu_nom + ' ' + (reg19.usu_ape || '') : 'N/A';

                htmlRows +=
                    '<td class="text-center celda-modal" style="cursor:pointer;color:#0056b3;"' +
                        ' data-sitio="' + sitio.sitio_nombre + '"' +
                        ' data-fecha="' + fecha + '"' +
                        ' data-t07="'  + (reg07 ? reg07.temperatura + '°' : '-') + '"' +
                        ' data-u07="'  + usu07 + '"' +
                        ' data-t12="'  + (val12 !== '-' ? val12 + '°' : '-') + '"' +
                        ' data-u12="'  + usu12 + '"' +
                        ' data-t19="'  + (reg19 ? reg19.temperatura + '°' : '-') + '"' +
                        ' data-u19="'  + usu19 + '"' +
                    '>' + (val12 !== '-' ? val12 + '°' : '-') + '</td>';
            });

            htmlRows += '</tr>';
        });

        $("#tabla_mensual tbody").html(htmlRows);

        // 5. Inicializar y guardar referencia global
        dtTablaMensual = $('#tabla_mensual').DataTable({
            dom: 'Bfrtip',
            searching:      true,
            lengthChange:   false,
            colReorder:     false,
            autoWidth:      false,
            iDisplayLength: 15,
            buttons: [
                { extend: 'copyHtml5',  text: 'Copiar', className: 'btn btn-sm btn-default' },
                { extend: 'excelHtml5', text: 'Excel',  className: 'btn btn-sm btn-success' },
                { extend: 'csvHtml5',   text: 'CSV',    className: 'btn btn-sm btn-info'    },
                { extend: 'pdfHtml5',   text: 'PDF', orientation: 'landscape',
                  className: 'btn btn-sm btn-danger' }
            ],
            language: {
                sSearch:   "Buscar Site:",
                oPaginate: { sNext: "Sig.", sPrevious: "Ant." }
            }
        });
    });
}
