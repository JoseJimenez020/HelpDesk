$(document).ready(function () {
    generarTablaTemperaturas();
});

function generarTablaTemperaturas() {
    const diasSemanas = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    let hoy = new Date();

    // 1. Normalizar la fecha a medianoche local para evitar cálculos erróneos
    hoy.setHours(0, 0, 0, 0);

    // Ajustar al domingo de esta semana
    let domingo = new Date(hoy);
    domingo.setDate(hoy.getDate() - hoy.getDay());

    let fechasSemana = [];

    $("#tem thead tr").html('<th style="width: 150px;">Sitio</th>');
    for (let i = 0; i < 7; i++) {
        let f = new Date(domingo);
        f.setDate(f.getDate() + i);

        // 2. Generar formato YYYY-MM-DD usando métodos locales (EVITA toISOString)
        let anio = f.getFullYear();
        let mes = String(f.getMonth() + 1).padStart(2, '0');
        let dia = String(f.getDate()).padStart(2, '0');
        let fechaFormato = `${anio}-${mes}-${dia}`;

        let fechaVisual = f.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });

        fechasSemana.push(fechaFormato);
        $("#tem thead tr").append(`<th>${diasSemanas[i]}<br><small>${fechaVisual}</small></th>`);
    }

    // 2. Cargar datos del servidor
    $.post("../../controller/temperatura.php?op=listar_y_datos", {
        f_inicio: fechasSemana[0],
        f_fin: fechasSemana[6]
    }, function (data) {
        let res = JSON.parse(data);
        let htmlRows = "";

        res.sitios.forEach(sitio => {
            htmlRows += `<tr><td class="font-weight-bold">${sitio.sitio_nombre}</td>`;

            fechasSemana.forEach(fecha => {
                htmlRows += `<td>`;
                ["07:00", "12:00", "19:00"].forEach(hora => {
                    // Buscar si ya existe valor para este input
                    let buscado = res.registros.find(r => r.sitio_id == sitio.sitio_id && r.fecha_hora == `${fecha} ${hora}:00`);
                    let valor = buscado ? buscado.temperatura : "";

                    htmlRows += `
                        <div class="input-group input-group-sm mb-1">
                            <div class="input-group-prepend"><span class="input-group-text">${hora}</span></div>
                            <input type="number" step="1" class="form-control temp-input" 
                                data-sitio="${sitio.sitio_id}" data-fecha="${fecha}" data-hora="${hora}" value="${valor}">
                        </div>`;
                });
                htmlRows += `</td>`;
            });
            htmlRows += `</tr>`;
        });

        $("#tem tbody").html(htmlRows);
        // Agregar botón de guardar al final si no existe
        if ($("#btnGuardar").length === 0) {
            $(".box-typical").append('<button id="btnGuardar" class="btn btn-rounded btn-primary mt-3">Guardar</button>');
        }
    });
}

// 4. Lógica del Botón Guardar
$(document).on("click", "#btnGuardar", function () {
    let puntos = [];
    $(".temp-input").each(function () {
        if ($(this).val() !== "") {
            puntos.push({
                sitio_id: $(this).data("sitio"),
                fecha_hora: $(this).data("fecha") + " " + $(this).data("hora") + ":00",
                valor: $(this).val()
            });
        }
    });

    $.post("../../controller/temperatura.php?op=guardar_todo", { puntos: puntos }, function (data) {
        let res = JSON.parse(data);
        if (res.ok) {
            swal("Correcto", "Temperaturas guardadas correctamente", "success");
        } else {
            swal("Error", "No se pudieron guardar los datos", "error");
        }
    });
});