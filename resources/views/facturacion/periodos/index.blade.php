@extends("theme.$theme.layout")

@section('titulo', 'Períodos de Lectura')

@section('styles')
<link rel="stylesheet" href="{{ asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css") }}">
<style>
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4 0%, #2b0c49 100%); border: none; padding: 22px 28px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.3rem; margin: 0; }
.badge-estado { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.badge-PLANIFICADO    { background: #e2e8f0; color: #4a5568; }
.badge-ACTIVO         { background: #c6f6d5; color: #22543d; }
.badge-LECTURA_CERRADA{ background: #fef3c7; color: #92400e; }
.badge-FACTURADO      { background: #bee3f8; color: #2c5282; }
.badge-CERRADO        { background: #fed7d7; color: #742a2a; }
#tblPeriodos thead th { background: linear-gradient(135deg, #3d57ce 0%, #776a84 100%); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 14px 10px; border: none; white-space: nowrap; }
#tblPeriodos tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; font-size: 0.83rem; }
#tblPeriodos tbody tr:hover { background: #f8f9ff; }
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4 0%, #2b0c49 100%); border: none; padding: 22px 28px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; font-size: 1.8rem; font-weight: 300; }
.modal-modern .modal-body { padding: 28px; background: #fafbfc; }
.modal-modern .modal-footer { padding: 16px 28px; border-top: 2px solid #e2e8f0; background: white; }
.modal-modern .form-group label { font-weight: 600; color: #4a5568; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
.modal-modern .form-control { border-radius: 10px; border: 2px solid #e2e8f0; padding: 10px 13px; transition: all 0.3s ease; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.btn-grad { border-radius: 12px; padding: 10px 28px; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); }
.flujo-paso { display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; color: #718096; }
.flujo-paso .paso-activo { color: #3d57ce; font-weight: 700; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-calendar-alt"></i> Períodos de Lectura</h3>
            <button class="btn btn-light" id="btnNuevoPeriodo" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Nuevo Período
            </button>
        </div>
    </div>

    {{-- Flujo de estados --}}
    <div style="background:white;border-radius:16px;padding:16px 24px;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.05);">
        <span class="flujo-paso">
            <span class="badge-estado badge-PLANIFICADO">Planificado</span>
            <i class="fa fa-arrow-right"></i>
            <span class="badge-estado badge-ACTIVO">Activo</span>
            <i class="fa fa-arrow-right"></i>
            <span class="badge-estado badge-LECTURA_CERRADA">Lectura Cerrada</span>
            <i class="fa fa-arrow-right"></i>
            <span class="badge-estado badge-FACTURADO">Facturado</span>
            <i class="fa fa-arrow-right"></i>
            <span class="badge-estado badge-CERRADO">Cerrado</span>
        </span>
        <span style="margin-left:16px;font-size:0.75rem;color:#a0aec0;">Use el botón <i class="fa fa-play"></i> para avanzar el estado de cada período.</span>
    </div>

    <div style="background:white;border-radius:16px;padding:20px;box-shadow:0 10px 40px rgba(0,0,0,0.08);overflow-x:auto;">
        <table id="tblPeriodos" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Ciclo</th>
                    <th>Tarifa</th>
                    <th>Inicio Lectura</th>
                    <th>Fin Lectura</th>
                    <th>Expedición</th>
                    <th>Vencimiento</th>
                    <th>Corte</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periodos as $p)
                <tr id="fila-{{ $p->id }}">
                    <td><strong style="font-family:monospace;">{{ $p->codigo }}</strong></td>
                    <td>{{ $p->nombre }}</td>
                    <td style="text-align:center;">{{ $p->ciclo }}</td>
                    <td>
                        @if($p->tarifa)
                            <span style="font-size:0.78rem;color:#4a5568;">{{ Str::limit($p->tarifa->nombre, 30) }}</span>
                        @else
                            <span style="color:#a0aec0;font-style:italic;">Sin asignar</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_inicio_lectura)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_fin_lectura)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_expedicion)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_corte)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge-estado badge-{{ $p->estado }}" id="badge-{{ $p->id }}">
                            {{ str_replace('_',' ', $p->estado) }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        @if($p->estado !== 'CERRADO')
                        <button class="btn btn-success btn-sm btn-avanzar" title="Avanzar estado"
                                data-id="{{ $p->id }}" data-estado="{{ $p->estado }}">
                            <i class="fa fa-play"></i>
                        </button>
                        @endif
                        <button class="btn btn-warning btn-sm btn-editar" title="Editar"
                                data-periodo="{{ json_encode($p) }}">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:40px;color:#a0aec0;">
                        <i class="fa fa-calendar" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                        No hay períodos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $periodos->links() }}</div>
    </div>
</div>

{{-- MODAL CREAR/EDITAR PERÍODO --}}
<div class="modal fade modal-modern" id="modalPeriodo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPeriodoTitulo"><i class="fa fa-calendar-plus"></i> Nuevo Período</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="periodoId">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Código <span style="color:red">*</span></label>
                            <input type="text" class="form-control" id="pCodigo" maxlength="6"
                                   placeholder="YYYYMM" style="font-family:monospace;font-size:1.1rem;text-align:center;">
                            <small class="text-muted">Ej: 202404</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre del Período <span style="color:red">*</span></label>
                            <input type="text" class="form-control" id="pNombre" placeholder="Ej: ABRIL 2024">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ciclo <span style="color:red">*</span></label>
                            <input type="number" class="form-control" id="pCiclo" min="1" value="1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Resolución Tarifaria Vigente</label>
                            <select class="form-control" id="pTarifa">
                                <option value="">— Sin asignar —</option>
                                @foreach($tarifas as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre }} (desde {{ \Carbon\Carbon::parse($t->vigente_desde)->format('d/m/Y') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Inicio de Lectura <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pInicioLectura">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fin de Lectura <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pFinLectura">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Expedición <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pExpedicion">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Vencimiento <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pVencimiento">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Corte <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pCorte">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea class="form-control" id="pObs" rows="2" placeholder="Observaciones opcionales..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:12px;">Cancelar</button>
                <button type="button" class="btn btn-grad" id="btnGuardarPeriodo">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var CSRF = $("meta[name='csrf-token']").attr("content");

// ── Nuevo período ──────────────────────────────────────────────────────────
$('#btnNuevoPeriodo').on('click', function () {
    $('#periodoId').val('');
    $('#modalPeriodoTitulo').html('<i class="fa fa-calendar-plus"></i> Nuevo Período');
    $('#pCodigo').val('').prop('disabled', false);
    $('#pNombre,#pCiclo,#pInicioLectura,#pFinLectura,#pExpedicion,#pVencimiento,#pCorte,#pObs').val('');
    $('#pCiclo').val(1);
    $('#pTarifa').val('');
    $('#modalPeriodo').modal('show');
});

// ── Editar período ─────────────────────────────────────────────────────────
$(document).on('click', '.btn-editar', function () {
    var p = $(this).data('periodo');
    $('#periodoId').val(p.id);
    $('#modalPeriodoTitulo').html('<i class="fa fa-edit"></i> Editar Período: ' + p.codigo);
    $('#pCodigo').val(p.codigo).prop('disabled', true);
    $('#pNombre').val(p.nombre);
    $('#pCiclo').val(p.ciclo);
    $('#pTarifa').val(p.tarifa_periodo_id || '');
    $('#pInicioLectura').val(p.fecha_inicio_lectura);
    $('#pFinLectura').val(p.fecha_fin_lectura);
    $('#pExpedicion').val(p.fecha_expedicion);
    $('#pVencimiento').val(p.fecha_vencimiento);
    $('#pCorte').val(p.fecha_corte);
    $('#pObs').val(p.observaciones || '');
    $('#modalPeriodo').modal('show');
});

// ── Guardar período ────────────────────────────────────────────────────────
$('#btnGuardarPeriodo').on('click', function () {
    var id   = $('#periodoId').val();
    var data = {
        codigo:               $('#pCodigo').val(),
        nombre:               $('#pNombre').val(),
        ciclo:                $('#pCiclo').val(),
        tarifa_periodo_id:    $('#pTarifa').val() || null,
        fecha_inicio_lectura: $('#pInicioLectura').val(),
        fecha_fin_lectura:    $('#pFinLectura').val(),
        fecha_expedicion:     $('#pExpedicion').val(),
        fecha_vencimiento:    $('#pVencimiento').val(),
        fecha_corte:          $('#pCorte').val(),
        observaciones:        $('#pObs').val(),
        _token: CSRF
    };

    var url    = id ? '/facturacion/periodos/' + id : '/facturacion/periodos';
    var method = id ? 'PUT' : 'POST';

    $.ajax({ url: url, method: method, data: data,
        success: function (r) {
            if (r.ok) {
                $('#modalPeriodo').modal('hide');
                Manteliviano.notificaciones(r.mensaje, 'Períodos', 'success');
                setTimeout(() => location.reload(), 1200);
            }
        },
        error: function (xhr) {
            var err = xhr.responseJSON;
            var msg = err && err.errors ? Object.values(err.errors).flat().join('<br>') : (err?.mensaje || 'Error al guardar.');
            Swal.fire({ title: 'Validación', html: msg, icon: 'warning' });
        }
    });
});

// ── Avanzar estado ─────────────────────────────────────────────────────────
var flujoLabel = {
    PLANIFICADO:     'ACTIVO',
    ACTIVO:          'LECTURA CERRADA',
    LECTURA_CERRADA: 'FACTURADO',
    FACTURADO:       'CERRADO',
};

$(document).on('click', '.btn-avanzar', function () {
    var id     = $(this).data('id');
    var estado = $(this).data('estado');
    var sig    = flujoLabel[estado] || '?';
    var $btn   = $(this);

    Swal.fire({
        title: '¿Avanzar período?',
        html:  'El período pasará de <b>' + estado.replace('_',' ') + '</b> a <b>' + sig + '</b>.',
        icon:  'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, avanzar',
        cancelButtonText: 'Cancelar',
    }).then(function (res) {
        if (!res.value) return;
        $.ajax({
            url:    '/facturacion/periodos/' + id + '/estado',
            method: 'POST',
            data:   { _token: CSRF },
            success: function (r) {
                if (r.ok) {
                    var badge = $('#badge-' + id);
                    badge.attr('class', 'badge-estado badge-' + r.nuevo_estado);
                    badge.text(r.nuevo_estado.replace('_',' '));
                    $btn.data('estado', r.nuevo_estado);
                    if (r.nuevo_estado === 'CERRADO') $btn.remove();
                    Manteliviano.notificaciones(r.mensaje, 'Períodos', 'success');
                }
            },
            error: function () {
                Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
            }
        });
    });
});
</script>
@endsection
