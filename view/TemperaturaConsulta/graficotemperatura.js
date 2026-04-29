$(document).ready(function () {
    // 1. Preparar los contenedores insertando el canvas de Chart.js
    $("#divgrafico").html('<canvas id="canvasSemanal"></canvas>');
    $("#divgraficotiempo").html('<canvas id="canvasMensual"></canvas>');

    cargarGraficoSemanal();
    cargarGraficoMensual();
});

function cargarGraficoSemanal() {
    $.post("../../controller/temperatura.php?op=grafico_semanal", function (data) {
        let res = JSON.parse(data);
        new Chart(document.getElementById('canvasSemanal'), {
            type: 'line',
            data: { labels: res.labels, datasets: res.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }, // Ocultar leyenda para no saturar
                scales: { y: { min: 10, max: 45 } }
            }
        });
    });
}

function cargarGraficoMensual() {
    $.post("../../controller/temperatura.php?op=grafico_mensual", function (data) {
        let res = JSON.parse(data);
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

var tabla;

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

$(document).ready(function () {
    // 1. Configurar fechas por defecto (Mes Actual)
    const fechaActual = new Date();
    const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1).toISOString().split('T')[0];
    const ultimoDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0).toISOString().split('T')[0];

    $("#filter_fecha_ini").val(primerDia);
    $("#filter_fecha_fin").val(ultimoDia);

    // Carga inicial
    generarTablaMensual(primerDia, ultimoDia);
});

// Evento para el botón de filtrar
$(document).on("click", "#btn_filtrar_mes", function () {
    let ini = $("#filter_fecha_ini").val();
    let fin = $("#filter_fecha_fin").val();
    if (ini && fin) {
        if (ini > fin) {
            swal("Error", "La fecha inicial no puede ser mayor a la final", "error");
            return;
        }
        generarTablaMensual(ini, fin);
    }
});

function generarTablaMensual(f_inicio, f_fin) {
    // 1. Destrucción "nuclear" de la tabla y su HTML
    if ($.fn.DataTable.isDataTable('#tabla_mensual')) {
        // destroy(true) elimina el DataTable y el elemento <table> del DOM
        $('#tabla_mensual').DataTable().destroy(true);
    }

    // 2. Recreamos el elemento table limpio dentro del contenedor
    $("#contenedor_tabla_mensual").html('<table id="tabla_mensual" class="table table-bordered table-responsive"><thead></thead><tbody></tbody></table>');

    // 3. Cálculo de fechas para las nuevas columnas
    let fechasRango = [];
    let inicio = new Date(f_inicio + "T00:00:00");
    let fin = new Date(f_fin + "T00:00:00");

    for (let d = new Date(inicio); d <= fin; d.setDate(d.getDate() + 1)) {
        fechasRango.push(new Date(d).toISOString().split('T')[0]);
    }

    // 4. Construcción del encabezado dinámico
    let headHtml = '<tr><th style="min-width:150px;">Site / Día</th>';
    fechasRango.forEach(f => {
        let dia = f.split('-')[2];
        headHtml += `<th class="text-center">${dia}</th>`;
    });
    headHtml += '</tr>';
    $("#tabla_mensual thead").html(headHtml);

    // 5. Petición AJAX
    $.post("../../controller/temperatura.php?op=listar_y_datos", {
        f_inicio: f_inicio,
        f_fin: f_fin
    }, function (data) {
        let res = JSON.parse(data);
        let htmlRows = "";

        res.sitios.forEach(sitio => {
            htmlRows += `<tr><td class="font-weight-bold">${sitio.sitio_nombre}</td>`;

            fechasRango.forEach(fecha => {
                let reg07 = res.registros.find(r => r.sitio_id == sitio.sitio_id && r.fecha_hora == `${fecha} 07:00:00`);
                let reg12 = res.registros.find(r => r.sitio_id == sitio.sitio_id && r.fecha_hora == `${fecha} 12:00:00`);
                let reg19 = res.registros.find(r => r.sitio_id == sitio.sitio_id && r.fecha_hora == `${fecha} 19:00:00`);

                let val12 = reg12 ? reg12.temperatura : "-";
                let usu07 = (reg07 && reg07.usu_nom) ? `${reg07.usu_nom} ${reg07.usu_ape || ''}` : 'N/A';
                let usu12 = (reg12 && reg12.usu_nom) ? `${reg12.usu_nom} ${reg12.usu_ape || ''}` : 'N/A';
                let usu19 = (reg19 && reg19.usu_nom) ? `${reg19.usu_nom} ${reg19.usu_ape || ''}` : 'N/A';

                let dataPayload = `
                    data-sitio="${sitio.sitio_nombre}" data-fecha="${fecha}"
                    data-t07="${reg07 ? reg07.temperatura + '°' : '-'}" data-u07="${usu07}"
                    data-t12="${val12 != "-" ? val12 + '°' : '-'}" data-u12="${usu12}"
                    data-t19="${reg19 ? reg19.temperatura + '°' : '-'}" data-u19="${usu19}"
                `;

                htmlRows += `<td class="text-center celda-modal" style="cursor: pointer; color: #0056b3;" ${dataPayload}>${val12}${val12 !== '-' ? '°' : ''}</td>`;
            });
            htmlRows += `</tr>`;
        });

        $("#tabla_mensual tbody").html(htmlRows);

        // 6. Inicialización de la nueva instancia de DataTables
        $('#tabla_mensual').DataTable({
            dom: 'Bfrtip',
            "searching": true,
            "lengthChange": false,
            "colReorder": true,
            "buttons": [
                { extend: 'copyHtml5', text: 'Copiar', className: 'btn btn-sm btn-default' },
                { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-sm btn-success' },
                { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-sm btn-info' },
                { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', className: 'btn btn-sm btn-danger' }
            ],
            "language": {
                "sSearch": "Buscar Site:",
                "oPaginate": { "sNext": "Sig.", "sPrevious": "Ant." }
            },
            "iDisplayLength": 15,
            "autoWidth": false,
            "scrollX": true 
        });
    });
}


// Evento para abrir el modal al dar clic en la celda
$(document).on("click", ".celda-modal", function () {
    let btn = $(this);

    // Llenar datos de encabezado
    $("#mdl_sitio").text(btn.data("sitio"));
    $("#mdl_fecha").text(btn.data("fecha"));

    // Llenar temperaturas
    $("#mdl_t07").text(btn.data("t07"));
    $("#mdl_t12").text(btn.data("t12"));
    $("#mdl_t19").text(btn.data("t19"));

    // Llenar responsables
    $("#mdl_u07").text("Por: " + btn.data("u07"));
    $("#mdl_u12").text("Por: " + btn.data("u12"));
    $("#mdl_u19").text("Por: " + btn.data("u19"));

    // Lanzar Modal
    $("#modalDetalleTemp").modal("show");
});
