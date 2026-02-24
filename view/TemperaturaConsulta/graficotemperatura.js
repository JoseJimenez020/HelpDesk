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

$(document).ready(function() {
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
$(document).on("click", "#btn_filtrar", function() {
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
    // Destruir DataTable previa si existe para poder reconstruir las columnas
    if ($.fn.DataTable.isDataTable('#tabla_mensual')) {
        $('#tabla_mensual').DataTable().destroy();
        $('#tabla_mensual empty()'); // Limpiar contenido
    }

    // 1. Calcular listado de fechas para las columnas
    let fechasRango = [];
    let inicio = new Date(f_inicio + "T00:00:00");
    let fin = new Date(f_fin + "T00:00:00");

    for (let d = new Date(inicio); d <= fin; d.setDate(d.getDate() + 1)) {
        fechasRango.push(new Date(d).toISOString().split('T')[0]);
    }

    // 2. Crear los encabezados de texto (Días del Mes)
    let headHtml = '<tr><th>Site / Día</th>';
    fechasRango.forEach(f => {
        let dia = f.split('-')[2];
        headHtml += `<th class="text-center">${dia}</th>`;
    });
    headHtml += '</tr>';
    $("#tabla_mensual thead").html(headHtml);

    // 3. Consultar datos
    $.post("../../controller/temperatura.php?op=listar_y_datos", {
        f_inicio: f_inicio,
        f_fin: f_fin
    }, function(data) {
        let res = JSON.parse(data);
        let htmlRows = "";

        res.sitios.forEach(sitio => {
            htmlRows += `<tr><td class="font-weight-bold">${sitio.sitio_nombre}</td>`;
            
            fechasRango.forEach(fecha => {
                // Buscamos el registro de las 12:00
                let buscado = res.registros.find(r => 
                    r.sitio_id == sitio.sitio_id && 
                    r.fecha_hora == `${fecha} 12:00:00`
                );
                
                let valor = buscado ? buscado.temperatura : "-";
                // Insertamos el valor como texto plano, sin input [cite: 182, 183]
                htmlRows += `<td class="text-center">${valor}°</td>`;
            });
            htmlRows += `</tr>`;
        });

        $("#tabla_mensual tbody").html(htmlRows);

        // 4. Inicializar DataTables con los botones de exportación 
        $('#tabla_mensual').DataTable({
            "aProcessing": true,
            "aServerSide": false, // Los datos ya están cargados en el DOM
            dom: 'Bfrtip',
            "searching": true,
            "lengthChange": false,
            "colReorder": true,
            "buttons": [
                {
                    extend: 'copyHtml5',
                    text: 'Copiar',
                    className: 'btn btn-sm btn-default'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Reporte de Temperaturas Mensual',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    className: 'btn btn-sm btn-info'
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Reporte de Temperaturas Mensual',
                    className: 'btn btn-sm btn-danger'
                }
            ],
            "language": {
                "sProcessing": "Procesando...",
                "sSearch": "Buscar Site:",
                "sZeroRecords": "No se encontraron resultados",
                "oPaginate": {
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            },
            "ordering": true,
            "responsive": false, // Desactivado para mantener la estructura de calendario
            "bInfo": true,
            "iDisplayLength": 15,
            "autoWidth": false
        });
    });
}