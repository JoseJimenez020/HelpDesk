/* =========================================================
 * tabulador.js  —  Vista: view/Tabulador/index.php
 * Cálculo en vivo de percepciones / ISR quincenal. No guarda nada.
 * ========================================================= */

var TAB_CFG = {
    UMA_DIARIA: 117.31,
    SALARIO_MIN_QUINCENAL: 4725.24,
    IMSS_FIJO: 142.20,        // solo se usa si el usuario no captura SBC
    TOPE_SBC_UMAS: 25,        // tope de cotización IMSS: 25 UMA diarias
    IMSS_CUOTAS: [            // cuotas del trabajador (% sobre SBC * días)
        ['Gastos médicos pensionados', 0.00375],
        ['Prestaciones en dinero',     0.0025],
        ['Invalidez y vida',           0.00625],
        ['Cesantía y vejez',           0.01125]
    ],
    IMSS_EXCEDENTE_PCT: 0.004, // 0.40% sobre el SBC que exceda 3 UMA
    DIAS_QUINCENA: 15,
    JORNADA_DIURNA: 8,        // horas para el valor de la hora diurna
    JORNADA_NOCTURNA: 7,      // horas para el valor de la hora nocturna (igual que tu Excel: salario/7)
    SEMANAS_QUINCENA: 2,      // semanas usadas para topes semanales
    LIM_DOBLES_SEMANA: 9,     // horas dobles por semana; el resto va triple
    TOPE_UMA_SEMANA: 5,       // exención máx. de tiempo extra/festivo: 5 UMA por semana
    PCT_PRIMA_DOMINICAL: 0.25,
    // [límite inferior, límite superior, cuota fija, % excedente]
    TARIFA: [
        [0.01,      416.70,    0,        0.0192],
        [416.71,    3537.15,   7.95,     0.0640],
        [3537.16,   6216.15,   207.75,   0.1088],
        [6216.16,   7225.95,   499.20,   0.1600],
        [7225.96,   8651.40,   660.75,   0.1792],
        [8651.41,   17448.75,  916.20,   0.2136],
        [17448.76,  27501.60,  2795.25,  0.2352],
        [27501.61,  52505.25,  5159.70,  0.3000],
        [52505.26,  70006.95,  12660.75, 0.3200],
        [70006.96,  210020.70, 18261.30, 0.3400],
        [210020.71, Infinity,  65866.05, 0.3500]
    ]
};

function r2(n) { return Math.round((n + Number.EPSILON) * 100) / 100; }

/* ISR por tarifa: (base - límite inferior) * % + cuota fija */
function calcularISR(base, cfg) {
    cfg = cfg || TAB_CFG;
    if (!(base > 0)) { return { isr: 0, fila: null, excedente: 0, impuestoMarginal: 0 }; }
    var t = cfg.TARIFA, fila = t[t.length - 1];
    for (var i = 0; i < t.length; i++) {
        if (base <= t[i][1]) { fila = t[i]; break; }
    }
    var excedente = base - fila[0];
    var marginal  = excedente * fila[3];
    return { isr: r2(marginal + fila[2]), fila: fila, excedente: r2(excedente), impuestoMarginal: r2(marginal) };
}

/* IMSS trabajador: SBC diario * días * cuotas + 0.40% del excedente de 3 UMA */
function calcularIMSS(sbc, cfg) {
    cfg = cfg || TAB_CFG;
    sbc = Math.min(Math.max(0, +sbc || 0), cfg.TOPE_SBC_UMAS * cfg.UMA_DIARIA);
    var base = sbc * cfg.DIAS_QUINCENA, desglose = [], total = 0, i;
    for (i = 0; i < cfg.IMSS_CUOTAS.length; i++) {
        var raw = base * cfg.IMSS_CUOTAS[i][1];
        desglose.push([cfg.IMSS_CUOTAS[i][0], r2(raw)]); total += raw;
    }
    var exced = Math.max(0, sbc - 3 * cfg.UMA_DIARIA);
    var rawEx = exced * cfg.DIAS_QUINCENA * cfg.IMSS_EXCEDENTE_PCT;
    desglose.push(['Excedente de 3 UMA', r2(rawEx)]); total += rawEx;
    return { total: r2(total), desglose: desglose, sbc: sbc };
}

/* Cálculo completo. in: {salarioQuincenal, sbc, domingos, festivos, hrsExtra, hrsExtraNoct} */
function calcularTabulador(inp, cfg) {
    cfg = cfg || TAB_CFG;
    var sal  = Math.max(0, +inp.salarioQuincenal || 0);
    var dom  = Math.max(0, Math.floor(+inp.domingos  || 0));
    var fest = Math.max(0, Math.floor(+inp.festivos  || 0));
    var hd   = Math.max(0, +inp.hrsExtra     || 0);
    var hn   = Math.max(0, +inp.hrsExtraNoct || 0);

    var diario    = sal / cfg.DIAS_QUINCENA;
    var esMinimo  = sal <= cfg.SALARIO_MIN_QUINCENAL;
    var pctExento = esMinimo ? 1 : 0.5;

    // --- Prima dominical: 25% del diario por domingo; exento hasta 1 UMA por domingo
    var primaUnit = diario * cfg.PCT_PRIMA_DOMINICAL;
    var prima          = primaUnit * dom;
    var primaExenta    = Math.min(primaUnit, cfg.UMA_DIARIA) * dom;
    var primaGravada   = prima - primaExenta;

    // --- Horas extra: primeras 9/semana dobles, el resto triples (triples 100% gravadas)
    var limDobles = cfg.LIM_DOBLES_SEMANA * cfg.SEMANAS_QUINCENA;
    var valHrD = diario / cfg.JORNADA_DIURNA;
    var valHrN = diario / cfg.JORNADA_NOCTURNA;
    var dobD = Math.min(hd, limDobles);
    var dobN = Math.min(hn, Math.max(0, limDobles - dobD));
    var tripD = hd - dobD, tripN = hn - dobN;

    var pagoExtraDiurnas   = valHrD * (2 * dobD + 3 * tripD);
    var pagoExtraNocturnas = valHrN * (2 * dobN + 3 * tripN);
    var pagoDobles = valHrD * 2 * dobD + valHrN * 2 * dobN;

    // --- Festivo laborado: pago doble adicional al salario del día
    var festivo = diario * 2 * fest;

    // Exención compartida (tiempo extra + festivo): 50% (100% si mínimo), tope 5 UMA/semana
    var candidata = pctExento * (pagoDobles + festivo);
    var tope = cfg.TOPE_UMA_SEMANA * cfg.UMA_DIARIA * cfg.SEMANAS_QUINCENA;
    var exentoExtra = esMinimo ? candidata : Math.min(candidata, tope);
    var totalExtraFest = pagoExtraDiurnas + pagoExtraNocturnas + festivo;
    var gravadoExtra = totalExtraFest - exentoExtra;

    // --- Totales
    var percepciones = sal + prima + totalExtraFest;
    var baseISR = sal + primaGravada + gravadoExtra;
    var isr = calcularISR(baseISR, cfg);
    var imssCalc = calcularIMSS(inp.sbc, cfg);
    var usaFijo = !(+inp.sbc > 0);
    var imss = usaFijo ? (sal > 0 ? cfg.IMSS_FIJO : 0) : imssCalc.total;
    var neto = percepciones - isr.isr - imss;

    return {
        esMinimo: esMinimo, diario: r2(diario),
        salario: r2(sal), prima: r2(prima), primaExenta: r2(primaExenta), primaGravada: r2(primaGravada),
        hrsDobles: dobD + dobN, hrsTriples: tripD + tripN,
        extraDiurnas: r2(pagoExtraDiurnas), extraNocturnas: r2(pagoExtraNocturnas),
        festivo: r2(festivo), exentoExtra: r2(exentoExtra), gravadoExtra: r2(gravadoExtra),
        tope: r2(tope), percepciones: r2(percepciones), baseISR: r2(baseISR),
        isr: isr.isr, tarifa: isr, imss: imss, imssDetalle: usaFijo ? null : imssCalc.desglose, neto: r2(neto)
    };
}

if (typeof module !== 'undefined') { module.exports = { calcularTabulador: calcularTabulador, calcularISR: calcularISR, calcularIMSS: calcularIMSS, TAB_CFG: TAB_CFG }; }

/* ---------------------------------------------------------
 * UI
 * --------------------------------------------------------- */
function fmt(n) {
    return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderTabulador() {
    var r = calcularTabulador({
        salarioQuincenal: $('#tab_salario').val(),
        sbc:              $('#tab_sbc').val(),
        domingos:         $('#tab_domingos').val(),
        festivos:         $('#tab_festivos').val(),
        hrsExtra:         $('#tab_hrs').val(),
        hrsExtraNoct:     $('#tab_hrs_noct').val()
    });

    $('#r_salario').text(fmt(r.salario));
    $('#r_prima').text(fmt(r.prima));
    $('#r_extra_d').text(fmt(r.extraDiurnas));
    $('#r_extra_n').text(fmt(r.extraNocturnas));
    $('#r_festivo').text(fmt(r.festivo));
    $('#r_percepciones').text(fmt(r.percepciones));

    $('#r_isr').text('-' + fmt(r.isr));
    $('#r_imss').text('-' + fmt(r.imss));
    var h = '';
    if (r.imssDetalle) {
        for (var k = 0; k < r.imssDetalle.length; k++) {
            h += '<tr><td>' + r.imssDetalle[k][0] + '</td><td class="text-right">' + fmt(r.imssDetalle[k][1]) + '</td></tr>';
        }
        h += '<tr><th>Total IMSS</th><th class="text-right">' + fmt(r.imss) + '</th></tr>';
    } else {
        h = '<tr><td colspan="2" class="text-muted">Captura tu SBC para calcular el IMSS; mientras tanto se usa un monto fijo de ' + fmt(r.imss) + '.</td></tr>';
    }
    $('#d_imss').html(h);
    $('#r_neto').text(fmt(r.neto));

    // Detalle del cálculo ISR
    $('#d_base').text(fmt(r.baseISR));
    $('#d_prima_ex').text(fmt(r.primaExenta));
    $('#d_extra_ex').text(fmt(r.exentoExtra));
    $('#d_extra_grav').text(fmt(r.gravadoExtra));
    $('#d_hrs').text(r.hrsDobles + ' dobles / ' + r.hrsTriples + ' triples');
    $('#d_tipo').text(r.esMinimo ? 'Salario mínimo (extras dobles 100% exentas)' : 'Sobre mínimo (50% exento, tope ' + fmt(r.tope) + ')');
    if (r.tarifa.fila) {
        $('#d_li').text(fmt(r.tarifa.fila[0]));
        $('#d_exc').text(fmt(r.tarifa.excedente));
        $('#d_pct').text((r.tarifa.fila[3] * 100).toFixed(2) + '%');
        $('#d_marg').text(fmt(r.tarifa.impuestoMarginal));
        $('#d_cf').text(fmt(r.tarifa.fila[2]));
    } else {
        $('#d_li, #d_exc, #d_pct, #d_marg, #d_cf').text('—');
    }
}

$(document).ready(function () {
    $('#tab_form input').on('input change', renderTabulador);
    $('#btn_limpiar').on('click', function () {
        $('#tab_form input').val('');
        $('#tab_form input[type=number]').not('#tab_salario').val(0);
        renderTabulador();
    });
    renderTabulador();
});