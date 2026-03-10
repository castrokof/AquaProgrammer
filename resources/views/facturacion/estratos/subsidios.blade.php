@extends("theme.$theme.layout")

@section('titulo', 'Gestión de Subsidios por Estrato')

@section('styles')
<style>
.cfg-card { border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,.09); border:none; background:white; margin-bottom:20px; overflow:hidden; }
.cfg-card .card-header { background:linear-gradient(135deg,#2e50e4,#2b0c49); padding:18px 26px; display:flex; align-items:center; justify-content:space-between; }
.cfg-card .card-header h4 { color:white; font-weight:700; margin:0; font-size:1.1rem; }

/* Tabla */
#tblEstratos thead th { background:linear-gradient(135deg,#3d57ce,#776a84); color:white; font-size:.75rem; font-weight:700; text-transform:uppercase; padding:11px 10px; border:none; text-align:center; white-space:nowrap; }
#tblEstratos tbody td { padding:12px 10px; vertical-align:middle; border-bottom:1px solid #f0f0f0; font-size:.88rem; }
#tblEstratos tbody tr:hover { background:#f8f9ff; }

/* Inputs inline */
.inp-sub { border:2px solid #e2e8f0; border-radius:8px; padding:5px 9px; font-size:.88rem; width:110px; text-align:right; transition:border-color .2s; }
.inp-sub:focus { border-color:#2e50e4; outline:none; box-shadow:0 0 0 3px rgba(46,80,228,.1); }

/* Badges subsidio */
.badge-sub   { background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
.badge-sobre { background:#fee2e2; color:#991b1b; padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
.badge-cero  { background:#f3f4f6; color:#6b7280; padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }

/* Botón guardar fila */
.btn-guardar-fila { background:#2e50e4; color:white; border:none; border-radius:8px; padding:5px 14px; font-size:.82rem; font-weight:700; cursor:pointer; transition:background .2s; }
.btn-guardar-fila:hover { background:#1e3a8a; }
.btn-guardar-fila:disabled { opacity:.5; cursor:default; }

/* Tooltip info */
.info-tip { font-size:.72rem; color:#9ca3af; display:block; margin-top:2px; line-height:1.2; }

/* Alerta info */
.info-box { background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:12px; padding:14px 18px; margin-bottom:20px; font-size:.88rem; color:#1e40af; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="cfg-card">
        <div class="card-header">
            <h4><i class="fa fa-percentage"></i> Gestión de Subsidios y Sobretasas por Estrato</h4>
        </div>
    </div>

    <div class="info-box">
        <strong><i class="fa fa-info-circle"></i> ¿Cómo funciona?</strong><br>
        El subsidio se aplica sobre el <strong>consumo básico</strong> de acueducto Y alcantarillado.<br>
        • <strong>Porcentaje</strong>: se calcula como % del consumo básico. Positivo = descuento (estratos 1-3). Negativo = sobretasa (estratos 5-6, comercial, industrial).<br>
        • <strong>Valor fijo acueducto / Valor fijo alcantarillado</strong>: si se ingresa un valor &gt; 0, se usa ese monto fijo en lugar del porcentaje para ese servicio.<br>
        • Si ambos están en 0 y el porcentaje también es 0, no se aplica subsidio.
    </div>

    <div class="cfg-card">
        <div class="card-header" style="background:white;border-bottom:2px solid #f0f0f0;">
            <span style="font-weight:700;color:#2e50e4;font-size:.95rem;"><i class="fa fa-table"></i> Estratos configurados</span>
        </div>
        <div style="overflow-x:auto;">
        <table id="tblEstratos" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;">Estrato</th>
                    <th>Tipo actual</th>
                    <th>% Subsidio/Sobretasa</th>
                    <th>Valor fijo Acueducto ($)</th>
                    <th>Valor fijo Alcantarillado ($)</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            @foreach($estratos as $e)
            @php
                $pct = (float) $e->porcentaje_subsidio;
                $fijoAc = (float) ($e->subsidio_fijo_acueducto ?? 0);
                $fijoAl = (float) ($e->subsidio_fijo_alcantarillado ?? 0);
            @endphp
            <tr id="fila-{{ $e->id }}">
                <td>
                    <strong>{{ $e->nombre }}</strong>
                    <span style="font-size:.75rem;color:#9ca3af;margin-left:6px;">({{ $e->codigo ?? 'E'.$e->numero }})</span>
                </td>
                <td style="text-align:center;">
                    @if($fijoAc > 0 || $fijoAl > 0)
                        <span class="badge-sub">Fijo</span>
                    @elseif($pct > 0)
                        <span class="badge-sub">Subsidio {{ $pct }}%</span>
                    @elseif($pct < 0)
                        <span class="badge-sobre">Sobretasa {{ abs($pct) }}%</span>
                    @else
                        <span class="badge-cero">Sin subsidio</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <input type="number" class="inp-sub" step="0.01" min="-100" max="100"
                           id="pct-{{ $e->id }}" value="{{ $pct }}"
                           title="Positivo = subsidio (descuento). Negativo = sobretasa (cargo).">
                    <span class="info-tip">Positivo=subsidio · Negativo=sobretasa</span>
                </td>
                <td style="text-align:center;">
                    <input type="number" class="inp-sub" step="1" min="0"
                           id="fijoAc-{{ $e->id }}" value="{{ $fijoAc }}"
                           title="Monto fijo a descontar en acueducto (0 = usar porcentaje)">
                    <span class="info-tip">0 = usar porcentaje</span>
                </td>
                <td style="text-align:center;">
                    <input type="number" class="inp-sub" step="1" min="0"
                           id="fijoAl-{{ $e->id }}" value="{{ $fijoAl }}"
                           title="Monto fijo a descontar en alcantarillado (0 = usar porcentaje)">
                    <span class="info-tip">0 = usar porcentaje</span>
                </td>
                <td style="text-align:center;">
                    <button class="btn-guardar-fila" onclick="guardarEstrato({{ $e->id }})">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                    <div id="msg-{{ $e->id }}" style="font-size:.72rem;margin-top:3px;display:none;"></div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="info-box" style="background:#fefce8;border-color:#fde68a;color:#78350f;">
        <strong><i class="fa fa-exclamation-triangle"></i> Importante:</strong>
        Los cambios aplican a las facturas generadas a partir de este momento.
        Las facturas ya emitidas <strong>no se recalculan</strong>.
    </div>

</div>
@endsection

@section('scripts')
<script>
var CSRF = $("meta[name='csrf-token']").attr("content");

function guardarEstrato(id) {
    var btn  = $('button[onclick="guardarEstrato(' + id + ')"]');
    var msg  = $('#msg-' + id);
    var pct  = parseFloat($('#pct-' + id).val()) || 0;
    var fiAc = parseFloat($('#fijoAc-' + id).val()) || 0;
    var fiAl = parseFloat($('#fijoAl-' + id).val()) || 0;

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    msg.hide();

    $.ajax({
        url: '/facturacion/estratos/' + id,
        method: 'PUT',
        data: {
            _token:                        CSRF,
            porcentaje_subsidio:           pct,
            subsidio_fijo_acueducto:       fiAc,
            subsidio_fijo_alcantarillado:  fiAl,
        },
        success: function(r) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            msg.html('<span style="color:#059669;font-weight:700;"><i class="fa fa-check"></i> Guardado</span>').show();
            // Actualizar badge de tipo
            var badge = '';
            if (fiAc > 0 || fiAl > 0)    badge = '<span class="badge-sub">Fijo</span>';
            else if (pct > 0)             badge = '<span class="badge-sub">Subsidio ' + pct + '%</span>';
            else if (pct < 0)             badge = '<span class="badge-sobre">Sobretasa ' + Math.abs(pct) + '%</span>';
            else                          badge = '<span class="badge-cero">Sin subsidio</span>';
            $('#fila-' + id + ' td:nth-child(2)').html(badge);
            setTimeout(function(){ msg.fadeOut(); }, 3000);
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            var errores = xhr.responseJSON?.errors;
            var texto   = errores ? Object.values(errores).flat().join(' | ') : 'Error al guardar.';
            msg.html('<span style="color:#dc2626;">' + texto + '</span>').show();
        }
    });
}
</script>
@endsection
