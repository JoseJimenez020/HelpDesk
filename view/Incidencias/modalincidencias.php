<!-- Modal Incidencias -->
<div id="modalIncidencias" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalIncidenciasLabel">Nueva Incidencia</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form id="incidencia_form">

                    <input type="hidden" id="inc_id"       name="incidencia_id" value="">
                    <input type="hidden" id="inc_anio"     name="anio"          value="">
                    <input type="hidden" id="inc_mes"      name="mes"           value="">
                    <input type="hidden" id="inc_quincena" name="quincena"      value="">

                    <!-- Quincena (solo lectura) -->
                    <div class="form-group">
                        <label>Quincena</label>
                        <input type="text" id="inc_quincena_label" class="form-control" readonly>
                    </div>

                    <!-- Prima dominical -->
                    <div class="form-group">
                        <label>Primas dominicales</label>
                        <div class="inc-field-row">
                            <input type="number" id="inc_prima" name="prima_dominical"
                                   class="form-control inc-num" min="0" value="0">
                            <div class="input-group inc-picker-group">
                                <input type="date" id="picker_prima" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-add-fecha"
                                            data-tipo="prima">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div id="tags_prima" class="fechas-tags-wrap"></div>
                    </div>

                    <!-- Horas extra -->
                    <div class="form-group">
                        <label>Horas extra</label>
                        <div class="inc-field-row">
                            <input type="number" id="inc_hrs" name="hrs_extra"
                                   class="form-control inc-num" min="0" value="0">
                            <div class="input-group inc-picker-group">
                                <input type="date" id="picker_hrs" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-add-fecha"
                                            data-tipo="hrs_extra">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div id="tags_hrs_extra" class="fechas-tags-wrap"></div>
                    </div>

                    <!-- Festivos laborados -->
                    <div class="form-group">
                        <label>Festivos laborados</label>
                        <div class="inc-field-row">
                            <input type="number" id="inc_festivo" name="festivo_laborado"
                                   class="form-control inc-num" min="0" value="0">
                            <div class="input-group inc-picker-group">
                                <input type="date" id="picker_festivo" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-add-fecha"
                                            data-tipo="festivo">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div id="tags_festivo" class="fechas-tags-wrap"></div>
                    </div>

                    <!-- Faltas -->
                    <div class="form-group">
                        <label>Faltas</label>
                        <div class="inc-field-row">
                            <input type="number" id="inc_faltas" name="faltas"
                                   class="form-control inc-num" min="0" value="0">
                            <div class="input-group inc-picker-group">
                                <input type="date" id="picker_falta" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-add-fecha"
                                            data-tipo="falta">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div id="tags_falta" class="fechas-tags-wrap"></div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_guardar_inc" class="btn btn-primary">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .inc-field-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .inc-num {
        width: 90px !important;
        flex-shrink: 0;
        text-align: center;
    }
    .inc-picker-group {
        flex: 1;
        max-width: 260px;
    }
    .fechas-tags-wrap {
        margin-top: 6px;
        min-height: 28px;
    }
    .fecha-tag {
        display: inline-block;
        background: #e8f4fd;
        border: 1px solid #aed6f1;
        border-radius: 3px;
        padding: 2px 8px;
        margin: 2px 4px 2px 0;
        font-size: 12px;
        color: #2471a3;
    }
    .fecha-tag .remove-fecha {
        cursor: pointer;
        margin-left: 5px;
        color: #c0392b;
        font-weight: bold;
    }
    .fecha-tag .remove-fecha:hover { color: #96281b; }
</style>