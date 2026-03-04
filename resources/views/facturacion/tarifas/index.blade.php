@extends("theme.$theme.layout")

@section('titulo', 'Configuración de Tarifas')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:25px; background:white; animation:fadeIn .5s ease-out; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.tarifa-card { border-radius:16px; border:2px solid #e2e8f0; padding:20px; margin-bottom:16px; background:white; transition:all .3s ease; cursor:pointer; }
.tarifa-card:hover { border-color:#667eea; box-shadow:0 8px 25px rgba(102,126,234,.15); }
.tarifa-card.activa { border-color:#48bb78; background:#f0fff4; }
.tarifa-card .badge-vigente { background:#c6f6d5; color:#22543d; font-size:.72rem; font-weight:700; padding:4px 12px; border-radius:20px; }
.tab-config { border-radius:12px; overflow:hidden; border:2px solid #e2e8f0; margin-top:16px; }
.tab-config .nav-tabs { background:#f7fafc; border-bottom:2px solid #e2e8f0; padding:8px; }
.tab-config .nav-tabs .nav-link { border-radius:10px; border:none; font-weight:600; color:#718096; font-size:.85rem; padding:8px 18px; }
.tab-config .nav-tabs .nav-link.active { background:linear-gradient(135deg,#667eea,#764ba2); color:white; }
.tab-config .tab-content { padding:20px; }
.tabla-cargos th { background:#f7fafc; font-size:.75rem; font-weight:700; text-transform:uppercase; color:#4a5568; padding:10px 8px; }
.tabla-cargos td { padding:8px; vertical-align:middle; }
.tabla-cargos input[type=number] { border-radius:8px; border:1.5px solid #e2e8f0; padding:6px 10px; width:100%; text-align:right; font-size:.85rem; transition:border-color .2s; }
.tabla-cargos input[type=number]:focus { border-color:#667eea; outline:none; box-shadow:0 0 0 3px rgba(102,126,234,.12); }
.modal-modern .modal-content { border-radius:20px; border:none; }
.modal-modern .modal-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; border-radius:20px 20px 0 0; }
.modal-modern .modal-header .modal-title { color:white; font-weight:700; }
.modal-modern .modal-header .close { color:white; opacity:.8; font-size:1.8rem; font-weight:300; }
.modal-modern .modal-body { padding:24px; background:#fafbfc; max-height:65vh; overflow-y:auto; }
.modal-modern .modal-footer { border-radius:0 0 20px 20px; border-top:2px solid #e2e8f0; padding:16px 24px; background:white; display:flex; justify-content:flex-end; gap:10px; }
.modal-modern .form-group label { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; }
.modal-modern .form-control { border-radius:10px; border:2px solid #e2e8f0; padding:10px 13px; }
.modal-modern .form-control:focus { border-color:#667eea; box-shadow:0 0 0 4px rgba(102,126,234,.1); outline:none; }
.btn-grad { border-radius:12px; padding:10px 28px; font-weight:700; border:none; background:linear-gradient(135deg,#667eea,#764ba2); color:white; box-shadow:0 4px 15px rgba(102,126,234,.4); }
.btn-guardar-tabla { border-radius:10px; padding:8px 22px; font-weight:700; border:none; background:linear-gradient(135deg,#48bb78,#38a169); color:white; font-size:.85rem; }
@keyframes fadeIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-dollar-sign"></i> Resoluciones Tarifarias</h3>
            <button class="btn btn-light" data-toggle="modal" data-target="#modalNuevaTarifa" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Nueva Resolución
            </button>
        </div>
    </div>

    <div class="row">
        {{-- Panel izquierdo: lista de resoluciones --}}
        <div class="col-md-4">
            @forelse($tarifas as $t)
            <div class="tarifa-card {{ $t->activo ? 'activa' : '' }}" onclick="cargarTarifa({{ $t->id }})">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="font-weight:700;color:#2d3748;">{{ $t->nombre }}</div>
                        @if($t->numero_resolucion)
                        <div style="font-size:.78rem;color:#718096;">Res. {{ $t->numero_resolucion }}</div>
                        @endif
                        <div style="font-size:.78rem;color:#718096;margin-top:4px;">
                            Desde {{ \Carbon\Carbon::parse($t->vigente_desde)->format('d/m/Y') }}
                            @if($t->vigente_hasta)
                                hasta {{ \Carbon\Carbon::parse($t->vigente_hasta)->format('d/m/Y') }}
                            @else
                                — <span style="color:#48bb78;font-weight:600;">En curso</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                        @if($t->activo)
                            <span class="badge-vigente">VIGENTE</span>
                        @else
                            <button class="btn btn-outline-success btn-sm" onclick="event.stopPropagation();activarTarifa({{ $t->id }})"
                                    style="border-radius:8px;font-size:.72rem;" title="Activar como vigente">
                                <i class="fa fa-check"></i> Activar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:40px;color:#a0aec0;">
                <i class="fa fa-file-invoice-dollar" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                No hay resoluciones. Crea la primera.
            </div>
            @endforelse
        </div>

        {{-- Panel derecho: configuración de cargos y rangos --}}
        <div class="col-md-8">
            <div id="panelSinSeleccion" style="background:white;border-radius:16px;padding:60px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,.05);">
                <i class="fa fa-hand-pointer" style="font-size:3rem;color:#e2e8f0;display:block;margin-bottom:16px;"></i>
                <p style="color:#a0aec0;font-size:1rem;">Selecciona una resolución tarifaria para configurar sus precios.</p>
            </div>

            <div id="panelConfig" style="display:none;">
                <div class="tab-config">
                    <ul class="nav nav-tabs" id="tabsTarifa">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabCargos">
                                <i class="fa fa-money-bill"></i> Cargos Fijos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabRangos">
                                <i class="fa fa-chart-bar"></i> Rangos de Consumo
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">

                        {{-- TAB CARGOS FIJOS --}}
                        <div class="tab-pane fade show active" id="tabCargos">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <h6 style="font-weight:700;color:#2d3748;margin:0;">Cargo fijo mensual por estrato (en $)</h6>
                                <button class="btn btn-guardar-tabla" id="btnGuardarCargos">
                                    <i class="fa fa-save"></i> Guardar Cargos
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table tabla-cargos">
                                    <thead>
                                        <tr>
                                            <th>Estrato</th>
                                            <th>Acueducto $</th>
                                            <th>Alcantarillado $</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyTablasCargos">
                                        @foreach($estratos as $e)
                                        <tr data-estrato-id="{{ $e->id }}">
                                            <td>
                                                <strong>{{ $e->codigo }}</strong>
                                                <span style="font-size:.78rem;color:#718096;"> — {{ $e->nombre }}</span>
                                            </td>
                                            <td><input type="number" class="inp-cargo" data-servicio="ACUEDUCTO" data-estrato="{{ $e->id }}" min="0" step="1" value="0"></td>
                                            <td><input type="number" class="inp-cargo" data-servicio="ALCANTARILLADO" data-estrato="{{ $e->id }}" min="0" step="1" value="0"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB RANGOS --}}
                        <div class="tab-pane fade" id="tabRangos">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <h6 style="font-weight:700;color:#2d3748;margin:0;">Precio por m³ según rango (en $)</h6>
                                <button class="btn btn-guardar-tabla" id="btnGuardarRangos">
                                    <i class="fa fa-save"></i> Guardar Rangos
                                </button>
                            </div>

                            <div style="margin-bottom:12px;">
                                <select class="form-control" id="selServicioRango" style="width:220px;border-radius:10px;display:inline-block;">
                                    <option value="ACUEDUCTO">Acueducto</option>
                                    <option value="ALCANTARILLADO">Alcantarillado</option>
                                </select>
                            </div>

                            <div class="table-responsive">
                                <table class="table tabla-cargos">
                                    <thead>
                                        <tr>
                                            <th>Estrato</th>
                                            <th>Básico $/m³<br><small style="font-weight:400;text-transform:none">(0–16 m³)</small></th>
                                            <th>Complementario $/m³<br><small style="font-weight:400;text-transform:none">(17–32 m³)</small></th>
                                            <th>Suntuario $/m³<br><small style="font-weight:400;text-transform:none">(&gt;32 m³)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyTablasRangos">
                                        @foreach($estratos as $e)
                                        <tr data-estrato-id="{{ $e->id }}">
                                            <td>
                                                <strong>{{ $e->codigo }}</strong>
                                                <span style="font-size:.78rem;color:#718096;"> — {{ $e->nombre }}</span>
                                            </td>
                                            <td><input type="number" class="inp-rango" data-tipo="BASICO" data-estrato="{{ $e->id }}" min="0" step="0.01" value="0"></td>
                                            <td><input type="number" class="inp-rango" data-tipo="COMPLEMENTARIO" data-estrato="{{ $e->id }}" min="0" step="0.01" value="0"></td>
                                            <td><input type="number" class="inp-rango" data-tipo="SUNTUARIO" data-estrato="{{ $e->id }}" min="0" step="0.01" value="0"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL NUEVA RESOLUCIÓN --}}
<div class="modal fade modal-modern" id="modalNuevaTarifa" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-file-alt"></i> Nueva Resolución Tarifaria</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="tNombre" placeholder="Ej: Resolución CRA 2024-01">
                </div>
                <div class="form-group">
                    <label>Número de Resolución</label>
                    <input type="text" class="form-control" id="tResolucion" placeholder="Ej: CRA-2024-001">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Vigente desde <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="tDesde">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Vigente hasta</label>
                            <input type="date" class="form-control" id="tHasta">
                            <small class="text-muted">Dejar vacío si es la tarifa actual.</small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="tActivo">
                        <label class="custom-control-label" for="tActivo" style="font-weight:600;color:#4a5568;">Marcar como tarifa vigente (desactivará la actual)</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea class="form-control" id="tObs" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" style="border-radius:12px;">Cancelar</button>
                <button class="btn btn-grad" id="btnGuardarTarifa"><i class="fa fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var CSRF = $("meta[name='csrf-token']").attr("content");
var tarifaSelId = null;

// ── Crear resolución ───────────────────────────────────────────────────────
$('#btnGuardarTarifa').on('click', function () {
    $.ajax({
        url: '/facturacion/tarifas', method: 'POST',
        data: {
            nombre: $('#tNombre').val(), numero_resolucion: $('#tResolucion').val(),
            vigente_desde: $('#tDesde').val(), vigente_hasta: $('#tHasta').val() || null,
            activo: $('#tActivo').is(':checked') ? 1 : 0,
            observaciones: $('#tObs').val(), _token: CSRF
        },
        success: function (r) {
            if (r.ok) {
                $('#modalNuevaTarifa').modal('hide');
                Swal.fire({ icon:'success', title:'Guardado', text: r.mensaje, timer:1200, showConfirmButton:false })
                    .then(function() { location.reload(); });
            }
        },
        error: function (xhr) {
            var err = xhr.responseJSON || {};
            Swal.fire('Validación', err.mensaje || 'Verifique los campos requeridos.', 'warning');
        }
    });
});

// ── Cargar cargos y rangos de una tarifa ──────────────────────────────────
function cargarTarifa(id) {
    tarifaSelId = id;
    $.getJSON('/facturacion/tarifas/' + id + '/detalle', function (data) {
        // Cargos fijos
        $.each(data.cargos, function (_, c) {
            $('input.inp-cargo[data-servicio="' + c.servicio + '"][data-estrato="' + c.estrato_id + '"]').val(c.cargo_fijo);
        });
        // Rangos
        $.each(data.rangos, function (_, r) {
            if (r.servicio === $('#selServicioRango').val()) {
                $('input.inp-rango[data-tipo="' + r.tipo + '"][data-estrato="' + r.estrato_id + '"]').val(r.precio_m3);
            }
        });
        $('#panelSinSeleccion').hide();
        $('#panelConfig').show();
        $('html, body').animate({ scrollTop: $('#panelConfig').offset().top - 20 }, 300);
    });
}

// Al cambiar servicio en pestaña rangos, recargar si hay tarifa seleccionada
$('#selServicioRango').on('change', function () { if (tarifaSelId) cargarTarifa(tarifaSelId); });

// ── Guardar cargos fijos ──────────────────────────────────────────────────
$('#btnGuardarCargos').on('click', function () {
    if (!tarifaSelId) return;
    var cargos = [];
    $('input.inp-cargo').each(function () {
        cargos.push({ servicio: $(this).data('servicio'), estrato_id: $(this).data('estrato'), cargo_fijo: $(this).val() });
    });
    $.ajax({
        url: '/facturacion/tarifas/' + tarifaSelId + '/cargos', method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ cargos: cargos, _token: CSRF }),
        success: function (r) { if (r.ok) Swal.fire({ icon:'success', title:'Guardado', text:r.mensaje, timer:1200, showConfirmButton:false }); },
        error: function ()    { Swal.fire('Error', 'No se pudieron guardar los cargos.', 'error'); }
    });
});

// ── Guardar rangos ─────────────────────────────────────────────────────────
$('#btnGuardarRangos').on('click', function () {
    if (!tarifaSelId) return;
    var rangosConfig = {
        BASICO:         { desde: 0,  hasta: 16 },
        COMPLEMENTARIO: { desde: 17, hasta: 32 },
        SUNTUARIO:      { desde: 33, hasta: null },
    };
    var servicio = $('#selServicioRango').val();
    var rangos = [];
    $('input.inp-rango').each(function () {
        var tipo = $(this).data('tipo');
        rangos.push({
            servicio:   servicio,
            estrato_id: $(this).data('estrato'),
            tipo:       tipo,
            rango_desde:$(rangosConfig[tipo].desde),
            rango_hasta:rangosConfig[tipo].hasta,
            precio_m3:  $(this).val()
        });
    });
    $.ajax({
        url: '/facturacion/tarifas/' + tarifaSelId + '/rangos', method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ rangos: rangos, _token: CSRF }),
        success: function (r) { if (r.ok) Swal.fire({ icon:'success', title:'Guardado', text:r.mensaje, timer:1200, showConfirmButton:false }); },
        error: function ()    { Swal.fire('Error', 'No se pudieron guardar los rangos.', 'error'); }
    });
});

// ── Activar tarifa ─────────────────────────────────────────────────────────
function activarTarifa(id) {
    Swal.fire({ title: '¿Activar esta tarifa?', text: 'La tarifa actualmente vigente será desactivada.', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Activar', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (!r.value) return;
        $.post('/facturacion/tarifas/' + id + '/activar', { _token: CSRF }, function (res) {
            if (res.ok) {
                Swal.fire({ icon:'success', title:'Activada', text:res.mensaje, timer:1200, showConfirmButton:false })
                    .then(function() { location.reload(); });
            }
        });
    });
}
</script>
@endsection
