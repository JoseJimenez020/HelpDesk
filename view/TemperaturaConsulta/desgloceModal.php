<!-- Modal Detalle Temperaturas -->
<div class="modal fade" id="modalDetalleTemp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: bold;">
                    Detalles - <span id="mdl_sitio"></span>
                </h5>
                <p class="m-0 text-muted" id="mdl_fecha" style="font-size: 13px;"></p>
                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                    <i class="font-icon-close-2"></i>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>07:00 hrs:</strong> <span id="mdl_t07" class="float-right badge badge-primary">-</span>
                        <div style="font-size: 11px; color: gray;" id="mdl_u07"></div>
                    </li>
                    <li class="list-group-item">
                        <strong>12:00 hrs:</strong> <span id="mdl_t12" class="float-right badge badge-primary">-</span>
                        <div style="font-size: 11px; color: gray;" id="mdl_u12"></div>
                    </li>
                    <li class="list-group-item">
                        <strong>19:00 hrs:</strong> <span id="mdl_t19" class="float-right badge badge-primary">-</span>
                        <div style="font-size: 11px; color: gray;" id="mdl_u19"></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>