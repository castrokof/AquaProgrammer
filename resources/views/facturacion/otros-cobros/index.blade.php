@extends("theme.$theme.layout")

@section('titulo', 'Otros Cobros')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; }
.filtros-box .form-control { border-radius:10px; border:2px solid #e2e8f0; }
.filtros-box .form-control:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
#tblCobros thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 8px; border:none; white-space:nowrap; text-align:center; }
#tblCobros tbody td { padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; text-align:center; font-size:.82rem; }
#tblCobros tbody tr:hover { background:#f8f9ff; }
.badge-est { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.badge-ACTIVO  { background:#c6f6d5; color:#22543d; }
.badge-PAGADO  { background:#bee3f8; color:#2a4365; }
.badge-ANULADO { background:#e2e8f0; color:#718096; }
.modal-header-cobro { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); }
.form-label-sm { font-weight:600; font-size:.8rem; color:#4a5568; text-transform:uppercase; margin-bottom:4px; }
.select2-container--default .select2-selection--single { border-radius:10px !important; border:2px solid #e2e8f0 !important; height:38px !important; line-height:36px !important; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px !important; padding-left:10px !important; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height:36px !important; }
.cuota-preview { background:#f0f9ff; border:2px solid #bee3f8; border-radius:12px; padding:14px 18px; margin-top:10px; display:none; }
.cuota-preview .val { font-size:1.4rem; font-weight:800; color:#2b6cb0; }
.cuota-preview .lbl { font-size:.78rem; color:#4a5568; }
/* Evitar que overflow:hidden corte el modal-footer */
#modalNuevoCobro .modal-content { overflow:visible; }
#modalNuevoCobro .modal-body    { max-height:72vh; overflow-y:auto; }
#modalAnular .modal-content     { overflow:visible; }
</style>
@endsection

@section('scriptsPlugins')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- ENCABEZADO --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fas fa-receipt"></i> Otros Cobros</h3>
            <button class="btn btn-light" id="btnNuevoCobro" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Nuevo Cobro
            </button>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-box">
        <form method="GET" action="{{ route('otros-cobros.index') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label-sm">Suscriptor</label>
                    <input type="text" name="suscriptor" class="form-control"
                           value="{{ request('suscriptor') }}" placeholder="Código suscriptor">
                </div>
                <div class="col-md-3">
                    <label class="form-label-sm">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach(['ACTIVO','PAGADO','ANULADO'] as $e)
                        <option value="{{ $e }}" {{ request('estado') == $e ? 'selected' : '' }}>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5" style="margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;margin-right:6px;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('otros-cobros.index') }}" class="btn btn-secondary" style="border-radius:12px;">
                        <i class="fa fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div style="background:white;border-radius:16px;padding:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);overflow-x:auto;">
        <table id="tblCobros" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Suscriptor</th>
                    <th>Tipo Cobro</th>
                    <th>Concepto</th>
                    <th>Servicio</th>
                    <th>Monto Total</th>
                    <th>Cuota Mens.</th>
                    <th>Cuotas</th>
                    <th>Saldo</th>
                    <th>Fecha Inicio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cobros as $cobro)
                <tr>
                    <td><strong style="font-family:monospace;">{{ $cobro->id }}</strong></td>
                    <td>
                        @if($cobro->cliente)
                        <strong>{{ $cobro->cliente->suscriptor }}</strong>
                        <br><span style="font-size:.75rem;color:#718096;">{{ Str::limit(trim($cobro->cliente->nombre . ' ' . $cobro->cliente->apellido), 22) }}</span>
                        @else
                        <span style="color:#a0aec0;">—</span>
                        @endif
                    </td>
                    <td>{{ optional($cobro->catalogo)->nombre ?? '—' }}</td>
                    <td style="text-align:left;max-width:180px;">{{ Str::limit($cobro->concepto, 40) }}</td>
                    <td>
                        <span style="font-size:.7rem;padding:3px 9px;border-radius:20px;font-weight:700;
                            background:{{ $cobro->tipo_servicio === 'ACUEDUCTO' ? '#e0f2fe' : '#fef3c7' }};
                            color:{{ $cobro->tipo_servicio === 'ACUEDUCTO' ? '#0369a1' : '#b45309' }};">
                            {{ $cobro->tipo_servicio }}
                        </span>
                    </td>
                    <td><strong>$ {{ number_format($cobro->monto_total, 0, ',', '.') }}</strong></td>
                    <td>$ {{ number_format($cobro->cuota_mensual, 0, ',', '.') }}</td>
                    <td>
                        <span style="font-size:.78rem;">{{ $cobro->cuotas_pagadas }} / {{ $cobro->num_cuotas }}</span>
                        <div style="height:4px;background:#e2e8f0;border-radius:4px;margin-top:4px;min-width:60px;">
                            <div style="height:4px;border-radius:4px;background:#48bb78;width:{{ $cobro->num_cuotas > 0 ? round(($cobro->cuotas_pagadas / $cobro->num_cuotas) * 100) : 0 }}%;"></div>
                        </div>
                    </td>
                    <td>
                        @if($cobro->saldo > 0)
                        <span style="color:#e53e3e;font-weight:700;">$ {{ number_format($cobro->saldo, 0, ',', '.') }}</span>
                        @else
                        <span style="color:#48bb78;font-weight:700;">Saldado</span>
                        @endif
                    </td>
                    <td>{{ $cobro->fecha_inicio ? $cobro->fecha_inicio->format('d/m/Y') : '—' }}</td>
                    <td><span class="badge-est badge-{{ $cobro->estado }}">{{ $cobro->estado }}</span></td>
                    <td style="white-space:nowrap;">
                        @if($cobro->estado === 'ACTIVO')
                        <button class="btn btn-danger btn-sm btn-anular"
                                data-id="{{ $cobro->id }}" title="Anular cobro">
                            <i class="fa fa-ban"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:50px;color:#a0aec0;">
                        <i class="fas fa-receipt" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
                        No se encontraron cobros adicionales.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $cobros->links() }}</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL: Nuevo Cobro
══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalNuevoCobro" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">

            <div class="modal-header modal-header-cobro border-0 px-4 py-3">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-receipt"></i> Nuevo Cobro Adicional
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">&times;</button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="row">

                    {{-- Cliente --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label-sm">Cliente / Suscriptor <span class="text-danger">*</span></label>
                        <select id="selCliente" style="width:100%;"></select>
                    </div>

                    {{-- Tipo de cobro (catálogo) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label-sm">Tipo de Cobro <span class="text-danger">*</span></label>
                        <select id="selCatalogo" class="form-control">
                            <option value="">— Seleccione —</option>
                            @foreach($catalogo as $cat)
                            <option value="{{ $cat->id }}"
                                data-aplica-acueducto="{{ $cat->aplica_acueducto ? 1 : 0 }}"
                                data-aplica-alcantarillado="{{ $cat->aplica_alcantarillado ? 1 : 0 }}"
                                data-requiere-diametro="{{ $cat->requiere_diametro ? 1 : 0 }}"
                                data-permite-cuotas="{{ $cat->permite_cuotas ? 1 : 0 }}">
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipo de servicio --}}
                    <div class="col-md-6 mb-3" id="wrapTipoServicio">
                        <label class="form-label-sm">Servicio <span class="text-danger">*</span></label>
                        <select id="selTipoServicio" class="form-control">
                            <option value="">— Seleccione catálogo primero —</option>
                        </select>
                    </div>

                    {{-- Concepto --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label-sm">Concepto <span class="text-danger">*</span></label>
                        <input type="text" id="inpConcepto" class="form-control" maxlength="255"
                               placeholder="Descripción detallada del cobro">
                    </div>

                    {{-- Diámetro (condicional) --}}
                    <div class="col-md-4 mb-3" id="wrapDiametro" style="display:none;">
                        <label class="form-label-sm">Diámetro <span class="text-danger">*</span></label>
                        <input type="text" id="inpDiametro" class="form-control" maxlength="50"
                               placeholder='Ej: 1/2"'>
                    </div>

                    {{-- Monto total --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Monto Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="border-radius:10px 0 0 10px;border:2px solid #e2e8f0;">$</span>
                            </div>
                            <input type="number" id="inpMontoTotal" class="form-control" min="1" step="1"
                                   placeholder="0" style="border-left:none;">
                        </div>
                    </div>

                    {{-- Número de cuotas --}}
                    <div class="col-md-4 mb-3" id="wrapCuotas">
                        <label class="form-label-sm">N° Cuotas <span class="text-danger">*</span></label>
                        <input type="number" id="inpNumCuotas" class="form-control" min="1" max="60"
                               value="1" placeholder="1">
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" id="inpFechaInicio" class="form-control"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>

                    {{-- Vista previa cuota --}}
                    <div class="col-md-12">
                        <div class="cuota-preview" id="cuotaPreview">
                            <div class="lbl">Cuota mensual estimada</div>
                            <div class="val" id="cuotaVal">$ 0</div>
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-md-12 mb-2 mt-3">
                        <label class="form-label-sm">Observaciones</label>
                        <textarea id="inpObservaciones" class="form-control" rows="2"
                                  placeholder="Opcional..." style="border-radius:10px;border:2px solid #e2e8f0;"></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-footer" style="border-top:2px solid #e2e8f0;">
                <button class="btn btn-secondary" data-dismiss="modal" style="border-radius:12px;">Cancelar</button>
                <button class="btn btn-primary" id="btnGuardarCobro" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-save"></i> Guardar Cobro
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL: Confirmar Anulación
══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:#e53e3e;border:none;padding:20px 24px;">
                <h5 class="modal-title" style="color:white;font-weight:700;">
                    <i class="fa fa-ban"></i> Anular Cobro
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white;opacity:.8;">&times;</button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="anularId">
                <p style="color:#4a5568;">¿Está seguro de que desea anular este cobro adicional?
                   Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer" style="border-top:2px solid #e2e8f0;">
                <button class="btn btn-secondary" data-dismiss="modal" style="border-radius:12px;">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmarAnular" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-ban"></i> Anular
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var CSRF = $("meta[name='csrf-token']").attr("content");

// ── Select2 búsqueda de cliente ──────────────────────────────────────────────
$('#selCliente').select2({
    dropdownParent: $('#modalNuevoCobro'),
    placeholder: 'Buscar por suscriptor o nombre...',
    minimumInputLength: 2,
    ajax: {
        url: '{{ route("otros-cobros.buscar-cliente") }}',
        dataType: 'json',
        delay: 250,
        data: function (p) { return { q: p.term }; },
        processResults: function (data) { return { results: data }; }
    }
});

// ── Abrir modal nuevo cobro ──────────────────────────────────────────────────
$('#btnNuevoCobro').on('click', function () {
    $('#selCliente').val(null).trigger('change');
    $('#selCatalogo').val('').trigger('change');
    $('#selTipoServicio').html('<option value="">— Seleccione catálogo primero —</option>');
    $('#inpConcepto, #inpDiametro, #inpObservaciones').val('');
    $('#inpMontoTotal').val('');
    $('#inpNumCuotas').val(1);
    $('#inpFechaInicio').val('{{ now()->format("Y-m-d") }}');
    $('#wrapDiametro').hide();
    $('#cuotaPreview').hide();
    $('#modalNuevoCobro').modal('show');
});

// ── Cambio de catálogo → ajustar servicios / diámetro ───────────────────────
$('#selCatalogo').on('change', function () {
    var opt = $(this).find(':selected');
    var aplAcue = opt.data('aplica-acueducto');
    var aplAlc  = opt.data('aplica-alcantarillado');
    var reqDiam = opt.data('requiere-diametro');
    var perCuot = opt.data('permite-cuotas');

    // Tipo servicio
    var html = '<option value="">— Seleccione —</option>';
    if (aplAcue)  html += '<option value="ACUEDUCTO">Acueducto</option>';
    if (aplAlc)   html += '<option value="ALCANTARILLADO">Alcantarillado</option>';
    $('#selTipoServicio').html(html);

    // Diámetro
    reqDiam ? $('#wrapDiametro').show() : $('#wrapDiametro').hide().find('input').val('');

    // Cuotas: si no permite, fijar en 1
    if (!perCuot) {
        $('#inpNumCuotas').val(1).prop('readonly', true);
    } else {
        $('#inpNumCuotas').prop('readonly', false);
    }

    calcularCuota();
});

// ── Calcular cuota en tiempo real ────────────────────────────────────────────
function calcularCuota() {
    var monto  = parseFloat($('#inpMontoTotal').val()) || 0;
    var cuotas = parseInt($('#inpNumCuotas').val()) || 1;
    if (monto > 0 && cuotas > 0) {
        var cuota = Math.round(monto / cuotas);
        $('#cuotaVal').text('$ ' + cuota.toLocaleString('es-CO'));
        $('#cuotaPreview').show();
    } else {
        $('#cuotaPreview').hide();
    }
}
$('#inpMontoTotal, #inpNumCuotas').on('input', calcularCuota);

// ── Guardar cobro ────────────────────────────────────────────────────────────
$('#btnGuardarCobro').on('click', function () {
    var clienteId   = $('#selCliente').val();
    var catalogoId  = $('#selCatalogo').val();
    var tipoSrv     = $('#selTipoServicio').val();
    var concepto    = $('#inpConcepto').val().trim();
    var monto       = $('#inpMontoTotal').val();
    var cuotas      = $('#inpNumCuotas').val();
    var fechaInicio = $('#inpFechaInicio').val();

    if (!clienteId || !catalogoId || !tipoSrv || !concepto || !monto || !cuotas || !fechaInicio) {
        Swal.fire('Campos requeridos', 'Complete todos los campos obligatorios.', 'warning');
        return;
    }

    var reqDiam = $('#selCatalogo').find(':selected').data('requiere-diametro');
    var diametro = $('#inpDiametro').val().trim();
    if (reqDiam && !diametro) {
        Swal.fire('Campo requerido', 'Debe ingresar el diámetro.', 'warning');
        return;
    }

    var data = {
        cliente_id:    clienteId,
        catalogo_id:   catalogoId,
        tipo_servicio: tipoSrv,
        concepto:      concepto,
        diametro:      diametro || null,
        monto_total:   monto,
        num_cuotas:    cuotas,
        fecha_inicio:  fechaInicio,
        observaciones: $('#inpObservaciones').val().trim() || null,
        _token:        CSRF
    };

    $('#btnGuardarCobro').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '{{ route("otros-cobros.store") }}',
        method: 'POST',
        data: data,
        success: function (r) {
            if (r.ok) {
                $('#modalNuevoCobro').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Cobro registrado',
                    text: 'Cuota mensual: $ ' + parseFloat(r.cuota_mensual).toLocaleString('es-CO', {maximumFractionDigits:0}),
                    timer: 2000,
                    showConfirmButton: false
                }).then(function () { location.reload(); });
            }
        },
        error: function (xhr) {
            var errors = xhr.responseJSON?.errors;
            var msg = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Error al guardar.');
            Swal.fire('Error', msg, 'error');
        },
        complete: function () {
            $('#btnGuardarCobro').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Cobro');
        }
    });
});

// ── Anular ───────────────────────────────────────────────────────────────────
$(document).on('click', '.btn-anular', function () {
    $('#anularId').val($(this).data('id'));
    $('#modalAnular').modal('show');
});

$('#btnConfirmarAnular').on('click', function () {
    var id = $('#anularId').val();
    $('#btnConfirmarAnular').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: '/facturacion/otros-cobros/' + id + '/anular',
        method: 'POST',
        data: { _token: CSRF },
        success: function (r) {
            if (r.ok) {
                $('#modalAnular').modal('hide');
                Swal.fire({ icon:'success', title: r.mensaje, timer:1500, showConfirmButton:false })
                    .then(function () { location.reload(); });
            }
        },
        error: function () {
            Swal.fire('Error', 'No se pudo anular el cobro.', 'error');
        },
        complete: function () {
            $('#btnConfirmarAnular').prop('disabled', false).html('<i class="fa fa-ban"></i> Anular');
        }
    });
});
</script>
@endsection
