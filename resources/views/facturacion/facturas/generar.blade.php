@extends("theme.$theme.layout")

@section('titulo', 'Generar Factura Manual')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.form-box { background:white; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,.06); margin-bottom:20px; }
.form-box h5 { font-weight:700; color:#2d3748; font-size:.9rem; text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; border-bottom:2px solid #e2e8f0; padding-bottom:10px; }
.form-control-gen { border-radius:10px; border:2px solid #e2e8f0; padding:10px 13px; transition:all .3s; font-size:.88rem; }
.form-control-gen:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
label.lbl { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }
/* Panel cliente */
#panelCliente { display:none; }
.cliente-info { background:#f0f4ff; border-radius:14px; padding:18px 20px; margin-bottom:16px; border:2px solid #c7d2fe; }
.cliente-info .ci-val { font-weight:700; color:#2d3748; font-size:.92rem; }
.cliente-info .ci-lbl { font-size:.72rem; color:#718096; text-transform:uppercase; letter-spacing:.4px; }
/* Historial barras */
.hist-bar-wrap { display:flex; align-items:flex-end; gap:6px; height:60px; margin-top:8px; }
.hist-bar { background:linear-gradient(180deg,#667eea,#764ba2); border-radius:4px 4px 0 0; min-width:28px; display:flex; flex-direction:column; justify-content:flex-start; align-items:center; padding-top:4px; }
.hist-bar span { font-size:.62rem; color:white; font-weight:700; }
.hist-bar .mes { font-size:.58rem; color:rgba(255,255,255,.7); margin-top:auto; padding-bottom:3px; }
/* Preview factura */
#panelPreview { display:none; }
.preview-section { border-radius:14px; overflow:hidden; margin-bottom:16px; border:2px solid #e2e8f0; }
.preview-section .ps-header { background:#f7fafc; padding:10px 16px; font-weight:700; font-size:.8rem; text-transform:uppercase; color:#4a5568; display:flex; justify-content:space-between; }
.preview-section .ps-body { padding:14px 16px; }
.pv-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; border-bottom:1px solid #f0f0f0; font-size:.84rem; }
.pv-row:last-child { border:none; }
.pv-row .pv-lbl { color:#718096; }
.pv-row .pv-val { font-weight:600; color:#2d3748; }
.pv-row.pv-total { background:#f0f4ff; border-radius:8px; padding:8px 12px; margin-top:6px; }
.pv-row.pv-total .pv-lbl { color:#3d57ce; font-weight:700; }
.pv-row.pv-total .pv-val { color:#3d57ce; font-size:1.05rem; font-weight:800; }
.badge-normal { background:#c6f6d5; color:#22543d; padding:3px 10px; border-radius:12px; font-size:.72rem; font-weight:700; }
.badge-critica { background:#fed7d7; color:#742a2a; padding:3px 10px; border-radius:12px; font-size:.72rem; font-weight:700; }
.btn-grad { border-radius:12px; padding:11px 32px; font-weight:700; border:none; background:linear-gradient(135deg,#667eea,#764ba2); color:white; box-shadow:0 4px 15px rgba(102,126,234,.4); font-size:.92rem; }
.btn-preview { border-radius:12px; padding:10px 24px; font-weight:700; border:2px solid #667eea; color:#667eea; background:white; font-size:.88rem; transition:all .3s; }
.btn-preview:hover { background:#667eea; color:white; }
.spinner-preview { display:none; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-file-invoice"></i> Generar Factura Manual</h3>
            <a href="{{ route('facturas.index') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">

        {{-- ── Columna izquierda: formulario ─────────────────────────────────────── --}}
        <div class="col-md-5">

            <div class="form-box">
                <h5><i class="fa fa-calendar-alt"></i> 1. Período de Facturación</h5>
                <div class="form-group">
                    <label class="lbl">Período <span style="color:red">*</span></label>
                    <select class="form-control form-control-gen" id="selPeriodo">
                        <option value="">— Seleccione período —</option>
                        @foreach($periodos as $p)
                        <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}">
                            {{ $p->nombre }} — {{ $p->estado }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-box">
                <h5><i class="fa fa-user"></i> 2. Cliente / Suscriptor</h5>
                <div class="form-group">
                    <label class="lbl">Código Suscriptor <span style="color:red">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-gen" id="inputSuscriptor"
                               placeholder="Ej: 00189" style="border-right:none;">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="btnBuscarCliente"
                                    style="border-radius:0 10px 10px 0;font-weight:700;">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <small id="clienteMsg" style="color:#e53e3e;font-size:.8rem;"></small>
                </div>

                {{-- Info del cliente cargado --}}
                <div id="panelCliente">
                    <div class="cliente-info">
                        <div class="row">
                            <div class="col-6">
                                <div class="ci-lbl">Nombre</div>
                                <div class="ci-val" id="cNombre">—</div>
                            </div>
                            <div class="col-6">
                                <div class="ci-lbl">Dirección</div>
                                <div class="ci-val" id="cDireccion">—</div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-4">
                                <div class="ci-lbl">Estrato</div>
                                <div class="ci-val" id="cEstrato">—</div>
                            </div>
                            <div class="col-4">
                                <div class="ci-lbl">Servicios</div>
                                <div class="ci-val" id="cServicios">—</div>
                            </div>
                            <div class="col-4">
                                <div class="ci-lbl">Estado</div>
                                <div class="ci-val" id="cEstado">—</div>
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <div class="ci-lbl">Consumo últimos 6 meses (m³)</div>
                            <div class="hist-bar-wrap" id="histBarras"></div>
                            <div style="font-size:.75rem;color:#718096;margin-top:6px;">
                                Promedio: <strong id="cPromedio">—</strong> m³
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="hidClienteId">
                </div>
            </div>

            <div class="form-box">
                <h5><i class="fa fa-tachometer-alt"></i> 3. <span id="tituloLectura">Lectura del Medidor</span></h5>

                {{-- Aviso cliente sin medidor --}}
                <div id="noMedidorNotice" style="display:none;background:#fffbeb;border:2px solid #f6e05e;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:.84rem;color:#744210;">
                    <i class="fas fa-exclamation-triangle" style="color:#d69e2e;"></i>
                    <strong>Cliente sin medidor</strong> — se facturará con el promedio de consumo:
                    <strong id="noMedidorPromedio"></strong> m³
                </div>

                <div id="seccionLectura">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="lbl">Lectura Anterior (m³)</label>
                                <input type="number" class="form-control form-control-gen" id="lectAnterior" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="lbl">Lectura Actual (m³)</label>
                                <input type="number" class="form-control form-control-gen" id="lectActual" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="lbl" id="lblConsumoM3">Consumo del período (m³) <span style="color:red">*</span></label>
                    <input type="number" class="form-control form-control-gen" id="consumoM3" min="0" placeholder="Se auto-calcula al ingresar lecturas">
                    <small id="consumoHint" style="color:#718096;font-size:.78rem;">Si ingresa lecturas anterior y actual, el consumo se calcula automáticamente.</small>
                </div>
                <div class="form-group">
                    <label class="lbl">Observaciones</label>
                    <textarea class="form-control form-control-gen" id="observaciones" rows="2" placeholder="Observaciones opcionales..."></textarea>
                </div>
            </div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <button class="btn btn-preview" id="btnPreview">
                    <i class="fa fa-calculator"></i> Calcular Preview
                </button>
                <button class="btn btn-grad" id="btnGenerar" disabled>
                    <i class="fa fa-file-invoice-dollar"></i> Generar Factura
                </button>
                <span class="spinner-preview" id="spinner">
                    <i class="fa fa-spinner fa-spin" style="color:#667eea;font-size:1.2rem;margin-top:12px;"></i>
                </span>
            </div>
        </div>

        {{-- ── Columna derecha: preview ─────────────────────────────────────────── --}}
        <div class="col-md-7" id="panelPreview">
            <div style="background:white;border-radius:16px;padding:24px;box-shadow:0 4px 15px rgba(0,0,0,.06);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h5 style="font-weight:800;color:#2d3748;margin:0;font-size:1.1rem;">
                        <i class="fa fa-eye" style="color:#667eea;"></i> Previsualización de Factura
                    </h5>
                    <span id="badgeTipoFactura"></span>
                </div>

                {{-- Encabezado --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px;">
                    <div style="background:#f7fafc;border-radius:10px;padding:10px 14px;">
                        <div style="font-size:.7rem;color:#a0aec0;text-transform:uppercase;font-weight:700;">Período</div>
                        <div style="font-weight:700;color:#2d3748;" id="pvPeriodo">—</div>
                    </div>
                    <div style="background:#f7fafc;border-radius:10px;padding:10px 14px;">
                        <div style="font-size:.7rem;color:#a0aec0;text-transform:uppercase;font-weight:700;">Vencimiento</div>
                        <div style="font-weight:700;color:#2d3748;" id="pvVence">—</div>
                    </div>
                    <div style="background:#f7fafc;border-radius:10px;padding:10px 14px;">
                        <div style="font-size:.7rem;color:#a0aec0;text-transform:uppercase;font-weight:700;">Consumo</div>
                        <div style="font-weight:700;color:#2d3748;" id="pvConsumo">— m³</div>
                    </div>
                </div>

                {{-- Promedio 6 meses --}}
                <div style="background:#faf5ff;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:.82rem;">
                    <span style="color:#553c9a;font-weight:700;">Promedio 6 meses:</span>
                    <span id="pvProm" style="margin:0 20px;"></span>
                    <span id="pvBarras" style="font-family:monospace;font-size:.78rem;color:#718096;"></span>
                </div>

                {{-- ACUEDUCTO --}}
                <div class="preview-section">
                    <div class="ps-header">
                        <span><i class="fa fa-tint"></i> Acueducto</span>
                        <span id="pvTotalAcueducto" style="color:#3d57ce;">$ 0</span>
                    </div>
                    <div class="ps-body">
                        <div class="pv-row"><span class="pv-lbl">Cargo fijo</span><span class="pv-val" id="pvCfAcueducto">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvBasicoAcLbl">Básico (0 m³)</span><span class="pv-val" id="pvBasicoAcVal">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvCompleAcLbl">Complementario (0 m³)</span><span class="pv-val" id="pvCompleAcVal">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvSuntAcLbl">Suntuario (0 m³)</span><span class="pv-val" id="pvSuntAcVal">$ 0</span></div>
                        <div class="pv-row" id="rowSubsidio" style="display:none;"><span class="pv-lbl" id="lblSubsidio">Subsidio Estrato</span><span class="pv-val" id="pvSubsidio" style="color:#166534;font-weight:700;">$ 0</span></div>
                        <div class="pv-row pv-total"><span class="pv-lbl">Subtotal Acueducto</span><span class="pv-val" id="pvSubtotalAc">$ 0</span></div>
                    </div>
                </div>

                {{-- ALCANTARILLADO --}}
                <div class="preview-section">
                    <div class="ps-header">
                        <span><i class="fa fa-water"></i> Alcantarillado</span>
                        <span id="pvTotalAlc" style="color:#3d57ce;">$ 0</span>
                    </div>
                    <div class="ps-body">
                        <div class="pv-row"><span class="pv-lbl">Cargo fijo</span><span class="pv-val" id="pvCfAlc">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvBasicoAlcLbl">Básico (0 m³)</span><span class="pv-val" id="pvBasicoAlcVal">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvCompleAlcLbl">Complementario (0 m³)</span><span class="pv-val" id="pvCompleAlcVal">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl" id="pvSuntAlcLbl">Suntuario (0 m³)</span><span class="pv-val" id="pvSuntAlcVal">$ 0</span></div>
                        <div class="pv-row pv-total"><span class="pv-lbl">Subtotal Alcantarillado</span><span class="pv-val" id="pvSubtotalAlc">$ 0</span></div>
                    </div>
                </div>

                {{-- OTROS COBROS + SALDO --}}
                <div class="preview-section">
                    <div class="ps-header"><span><i class="fa fa-plus-circle"></i> Otros Conceptos</span></div>
                    <div class="ps-body">
                        <div class="pv-row"><span class="pv-lbl">Otros cobros acueducto</span><span class="pv-val" id="pvOtrosAc">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl">Otros cobros alcantarillado</span><span class="pv-val" id="pvOtrosAlc">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl">Saldo anterior</span><span class="pv-val" id="pvSaldoAnt" style="color:#e53e3e;">$ 0</span></div>
                        <div class="pv-row"><span class="pv-lbl">Facturas en mora</span><span class="pv-val" id="pvMora">0</span></div>
                    </div>
                </div>

                {{-- GRAN TOTAL --}}
                <div style="background:linear-gradient(135deg,#2e50e4,#2b0c49);border-radius:14px;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                    <span style="color:white;font-weight:700;font-size:1rem;">TOTAL A PAGAR</span>
                    <span style="color:white;font-weight:900;font-size:1.6rem;" id="pvTotalPagar">$ 0</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
var CSRF   = $("meta[name='csrf-token']").attr("content");
var maxConsumo = 0;
var previewOk  = false;

// ── Auto-calcular consumo desde lecturas ──────────────────────────────────
$('#lectActual, #lectAnterior').on('input', function () {
    var ant = parseInt($('#lectAnterior').val()) || 0;
    var act = parseInt($('#lectActual').val())   || 0;
    if (act >= ant && act > 0) {
        $('#consumoM3').val(act - ant);
    }
});

// ── Buscar cliente ─────────────────────────────────────────────────────────
function buscarCliente() {
    var sus = $('#inputSuscriptor').val().trim();
    if (!sus) { $('#clienteMsg').text('Ingrese el código del suscriptor.'); return; }
    $('#clienteMsg').text('');
    $.ajax({
        url: '{{ route("facturas.buscar-cliente") }}',
        method: 'POST',
        data: { suscriptor: sus, _token: CSRF },
        success: function (r) {
            if (!r.ok) { $('#clienteMsg').text(r.mensaje); return; }
            var c = r.cliente;
            $('#hidClienteId').val(c.id);
            $('#cNombre').text(c.nombre || '—');
            $('#cDireccion').text(c.direccion || '—');
            $('#cEstrato').text(c.estrato || '—');
            $('#cServicios').text(c.servicios || '—');
            $('#cEstado').text(c.estado || '—');
            $('#cPromedio').text(c.promedio_consumo);
            // Barras historial
            var hist  = r.historial || [];
            maxConsumo = Math.max.apply(null, hist.map(function(h){ return h.consumo || 0; })) || 1;
            var html = '';
            hist.forEach(function (h) {
                var pct = Math.round((h.consumo / maxConsumo) * 55);
                html += '<div class="hist-bar" style="height:' + pct + 'px;" title="' + h.periodo + ': ' + h.consumo + ' m³">';
                html += '<span>' + h.consumo + '</span>';
                html += '<span class="mes">' + (h.periodo ? h.periodo.slice(4) + '/' + h.periodo.slice(0,4).slice(2) : '') + '</span>';
                html += '</div>';
            });
            $('#histBarras').html(html || '<span style="color:#a0aec0;font-size:.78rem;">Sin historial</span>');

            // Adaptar sección de lectura según si el cliente tiene medidor
            var promedio = parseFloat(c.promedio_consumo) || 0;
            var consumoSinMedidor = Math.round(promedio) || 1;
            if (!c.tiene_medidor) {
                $('#tituloLectura').text('Consumo Estimado (sin medidor)');
                $('#seccionLectura').hide();
                $('#noMedidorPromedio').text(consumoSinMedidor);
                $('#noMedidorNotice').show();
                $('#consumoM3').val(consumoSinMedidor).prop('readonly', true);
                $('#lblConsumoM3').html('Consumo estimado por promedio (m³)');
                $('#consumoHint').text('Calculado automáticamente sobre el promedio de los últimos 6 meses.');
            } else {
                $('#tituloLectura').text('Lectura del Medidor');
                $('#seccionLectura').show();
                $('#noMedidorNotice').hide();
                $('#consumoM3').val('').prop('readonly', false);
                $('#lblConsumoM3').html('Consumo del período (m³) <span style="color:red">*</span>');
                $('#consumoHint').text('Si ingresa lecturas anterior y actual, el consumo se calcula automáticamente.');
            }

            $('#panelCliente').show();
            previewOk = false; $('#btnGenerar').prop('disabled', true);
        },
        error: function (xhr) { $('#clienteMsg').text(xhr.responseJSON?.mensaje || 'No encontrado.'); $('#panelCliente').hide(); }
    });
}

$('#btnBuscarCliente').on('click', buscarCliente);
$('#inputSuscriptor').on('keypress', function (e) { if (e.which === 13) buscarCliente(); });

// ── Preview ────────────────────────────────────────────────────────────────
function fmt(n) { return '$ ' + (parseFloat(n)||0).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0}); }

$('#btnPreview').on('click', function () {
    var clienteId = $('#hidClienteId').val();
    var periodoId = $('#selPeriodo').val();
    var consumo   = $('#consumoM3').val();
    if (!clienteId || !periodoId || consumo === '') {
        Swal.fire('Campos requeridos', 'Complete el período, suscriptor y consumo antes de calcular.', 'warning');
        return;
    }
    $('#spinner').show();
    $.ajax({
        url: '{{ route("facturas.preview") }}', method: 'POST',
        data: {
            cliente_id: clienteId, periodo_lectura_id: periodoId,
            consumo_m3: consumo,
            lectura_anterior: $('#lectAnterior').val() || null,
            lectura_actual:   $('#lectActual').val()   || null,
            _token: CSRF
        },
        success: function (r) {
            $('#spinner').hide();
            if (!r.ok) return;
            var c = r.calculo;
            // Encabezado
            $('#pvPeriodo').text(c.mes_cuenta);
            $('#pvVence').text(c.fecha_vencimiento ? c.fecha_vencimiento.replace(/-/g,'/').split('/').reverse().join('/') : '—');
            $('#pvConsumo').text(c.consumo_m3 + ' m³');
            // Promedio
            var meses = [c.prom_m1,c.prom_m2,c.prom_m3,c.prom_m4,c.prom_m5,c.prom_m6].filter(function(v){return v!==null;});
            $('#pvProm').text('Prom: ' + (c.promedio_consumo_snapshot || 0).toFixed(1) + ' m³');
            $('#pvBarras').text('[' + meses.join(' | ') + '] m³');
            // Tipo de factura
            var badgeHtml = c.tiene_medidor_snapshot === false
                ? '<span style="background:#fefcbf;color:#744210;padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:700;margin-right:6px;"><i class="fas fa-times-circle"></i> Sin Medidor</span>'
                : '';
            badgeHtml += c.es_automatica
                ? '<span class="badge-normal"><i class="fa fa-check-circle"></i> Lectura Normal</span>'
                : '<span class="badge-critica"><i class="fa fa-exclamation-triangle"></i> Requiere Revisión</span>';
            $('#badgeTipoFactura').html(badgeHtml);
            // Acueducto
            $('#pvCfAcueducto').text(fmt(c.cargo_fijo_acueducto));
            $('#pvBasicoAcLbl').text('Básico (' + (c.consumo_basico_acueducto_m3||0) + ' m³)');
            $('#pvBasicoAcVal').text(fmt(c.consumo_basico_acueducto_valor));
            $('#pvCompleAcLbl').text('Complementario (' + (c.consumo_complementario_acueducto_m3||0) + ' m³)');
            $('#pvCompleAcVal').text(fmt(c.consumo_complementario_acueducto_valor));
            $('#pvSuntAcLbl').text('Suntuario (' + (c.consumo_suntuario_acueducto_m3||0) + ' m³)');
            $('#pvSuntAcVal').text(fmt(c.consumo_suntuario_acueducto_valor));
            // Subsidio / Contribución
            var subsidio = parseFloat(c.subsidio_emergencia) || 0;
            if (subsidio !== 0) {
                var esSubsidio = subsidio > 0;
                $('#lblSubsidio').text(esSubsidio ? 'Subsidio Estrato' : 'Contribución Estrato');
                $('#pvSubsidio').css('color', esSubsidio ? '#166534' : '#991b1b')
                    .text((esSubsidio ? '- ' : '+ ') + fmt(Math.abs(subsidio)));
                $('#rowSubsidio').show();
            } else {
                $('#rowSubsidio').hide();
            }
            $('#pvSubtotalAc').text(fmt(c.total_facturacion_acueducto));
            $('#pvTotalAcueducto').text(fmt(c.subtotal_conexion_otros_acueducto));
            // Alcantarillado
            $('#pvCfAlc').text(fmt(c.cargo_fijo_alcantarillado));
            $('#pvBasicoAlcLbl').text('Básico (' + (c.consumo_basico_alcantarillado_m3||0) + ' m³)');
            $('#pvBasicoAlcVal').text(fmt(c.consumo_basico_alcantarillado_valor));
            $('#pvCompleAlcLbl').text('Complementario (' + (c.consumo_complementario_alcantarillado_m3||0) + ' m³)');
            $('#pvCompleAlcVal').text(fmt(c.consumo_complementario_alcantarillado_valor));
            $('#pvSuntAlcLbl').text('Suntuario (' + (c.consumo_suntuario_alcantarillado_m3||0) + ' m³)');
            $('#pvSuntAlcVal').text(fmt(c.consumo_suntuario_alcantarillado_valor));
            $('#pvSubtotalAlc').text(fmt(c.subtotal_alcantarillado));
            $('#pvTotalAlc').text(fmt(c.subtotal_conexion_otros_alcantarillado));
            // Otros
            $('#pvOtrosAc').text(fmt(c.otros_cobros_acueducto));
            $('#pvOtrosAlc').text(fmt(c.otros_cobros_alcantarillado));
            $('#pvSaldoAnt').text(fmt(c.saldo_anterior));
            $('#pvMora').text(c.facturas_en_mora || 0);
            // Total
            $('#pvTotalPagar').text(fmt(c.total_a_pagar));
            $('#panelPreview').show();
            previewOk = true;
            $('#btnGenerar').prop('disabled', false);
        },
        error: function (xhr) {
            $('#spinner').hide();
            Swal.fire('Error', xhr.responseJSON?.mensaje || 'Error al calcular.', 'error');
        }
    });
});

// ── Generar factura definitivamente ──────────────────────────────────────
$('#btnGenerar').on('click', function () {
    if (!previewOk) return;
    Swal.fire({
        title: '¿Confirmar generación?',
        html: 'Se generará la factura para el suscriptor <b>' + $('#inputSuscriptor').val() + '</b>.<br>Total: <b>' + $('#pvTotalPagar').text() + '</b>',
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Generar', cancelButtonText: 'Cancelar'
    }).then(function (res) {
        if (!res.value) return;
        var btn = $('#btnGenerar');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');
        $.ajax({
            url: '{{ route("facturas.store") }}', method: 'POST',
            data: {
                cliente_id: $('#hidClienteId').val(),
                periodo_lectura_id: $('#selPeriodo').val(),
                consumo_m3: $('#consumoM3').val(),
                lectura_anterior: $('#lectAnterior').val() || null,
                lectura_actual:   $('#lectActual').val()   || null,
                observaciones: $('#observaciones').val(),
                _token: CSRF
            },
            success: function (r) {
                if (r.ok) {
                    Swal.fire({
                        title: '¡Factura generada!', icon: 'success',
                        html: 'N° <b>' + r.factura_id + '</b> registrada correctamente.',
                        confirmButtonText: 'Ver factura'
                    }).then(function () {
                        window.location = '/facturacion/facturas/' + r.factura_id;
                    });
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-file-invoice-dollar"></i> Generar Factura');
                Swal.fire('Error', xhr.responseJSON?.mensaje || 'No se pudo generar.', 'error');
            }
        });
    });
});
</script>
@endsection
