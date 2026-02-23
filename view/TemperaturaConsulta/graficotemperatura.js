$(document).ready(function() {
    // 1. Preparar los contenedores insertando el canvas de Chart.js
    $("#divgrafico").html('<canvas id="canvasSemanal"></canvas>');
    $("#divgraficotiempo").html('<canvas id="canvasMensual"></canvas>');

    cargarGraficoSemanal();
    cargarGraficoMensual();
});

function cargarGraficoSemanal() {
    $.post("../../controller/temperatura.php?op=grafico_semanal", function(data) {
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
    $.post("../../controller/temperatura.php?op=grafico_mensual", function(data) {
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