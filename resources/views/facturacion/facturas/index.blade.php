@extends("theme.$theme.layout")

@section('titulo', 'Listado de Facturas')

@section('styles')
<style>
/* Estilos Modernos */
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.08); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }

/* KPIs */
.kpi-container { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:25px; }
.kpi-box { border-radius:16px; padding:20px; color:white; position:relative; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.1); }
.kpi-box::after { content:''; position:absolute; top:-20px; right:-20px; width:80px; height:80px; background:rgba(255,255,255,.1); border-radius:50%; }
.kpi-val { font-size:1.8rem; font-weight:800; margin-bottom:5px; }
.kpi-lbl { font-size:.8rem; opacity:.9; text-transform:uppercase; letter-spacing:.5px; }
.bg-kpi-total { background:linear-gradient(135deg, #667eea, #764ba2); }
.bg-kpi-pendiente { background:linear-gradient(135deg, #f6ad55, #ed8936); }
.bg-kpi-pagada { background:linear-gradient(135deg, #68d391, #48bb78); }
.bg-kpi-anulada { background:linear-gradient(135deg, #fc8181, #f56565); }

/* Filtros */
.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; border-left:5px solid #667eea; }
.filtros-box label { font-weight:700; font-size:.75rem; color:#4a5568; text-transform:uppercase; margin-bottom:5px; }
.form-control-custom { border-radius:10px; border:1px solid #e2e8f0; padding:10px; font-size:.9rem; transition:all .3s; }
.form-control-custom:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.15); outline:none; }

/* Tabla */
.table-container { background:white; border-radius:16px; padding:20px; box-shadow:0 10px 40px rgba(0,0,0,.08); overflow-x:auto; }
#tblFacturas thead th { background:#f8fafc; color:#4a5568; font-weight:700; font-size:.75rem; text-transform:uppercase; padding:15px; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
#tblFacturas tbody td { padding:12px; vertical-align:middle; border-bottom:1px solid #f0f0f0; font-size:.85rem; color:#2d3748; }
#tblFacturas tbody tr:hover { background:#f8faff; }

.badge-est { padding:5px 12px; border-radius:20px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.badge-PAGADA { background:#c6f6d5; color:#22543d; }
.badge-VENCIDA { background:#fed7d7; color:#742a2a; }
.badge-ANULADA { background:#e2e8f0; color:#718096; }

.btn-action { border-radius:8px; padding:6px 10px; font-size:.8rem; transition:transform .2s; }
.btn-action:hover { transform:translateY(-2px); }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <!-- Header -->
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-file-invoice-dollar"></i> Gestión de Facturas</h3>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('facturas.masiva') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;color:#667eea;border:2px solid #667eea;">
                    <i class="fa fa-bolt"></i> Masiva
                </a>
                <a href="{{ route('facturas.generar') }}" class="btn btn-primary" style="border-radius:12px;font-weight:700;background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                    <i class="fa fa-plus"></i> Nueva Factura
                </a>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-container">
        <div class="kpi-box bg-kpi-total">
            <div class="kpi-val">{{ number_format($stats['total']) }}</div>
            <div class="kpi-lbl">Total Facturas</div>
        </div>
        <div class="kpi-box bg-kpi-pendiente">
            <div class="kpi-val">$ {{ number_format($stats['pendiente'], 0, ',', '.') }}</div>
            <div class="kpi-lbl">Por Cobrar</div>
        </div>
        <div class="kpi-box bg-kpi-pagada">
            <div class="kpi-val">$ {{ number_format($stats['pagada'], 0, ',', '.') }}</div>
            <div class="kpi-lbl">Recaudado</div>
        </div>
        <div class="kpi-box bg-kpi-anulada">
            <div class="kpi-val">{{ number_format($stats['anulada']) }}</div>
            <div class="kpi-lbl">Anuladas</div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="filtros-box">
        <form method="GET" action="{{ route('facturas.index') }}" id="formFiltros">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label>Período</label>
                    <select name="periodo" class="form-control form-control-custom">
                        <option value="">Todos</option>
                        @foreach($periodos as $p)
                        <option value="{{ $p->codigo }}" {{ request('periodo')==$p->codigo?'selected':'' }}>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Suscriptor</label>
                    <input type="text" name="suscriptor" class="form-control form-control-custom" value="{{ request('suscriptor') }}" placeholder="Ej: 102030">
                </div>
                <div class="col-md-2">
                    <label>ID Ruta</label>
                    <input type="number" name="id_ruta" class="form-control form-control-custom" value="{{ request('id_ruta') }}" placeholder="Ej: 5">
                </div>
                <div class="col-md-2">
                    <label>Crítica</label>
                    <input type="text" name="critica" class="form-control form-control-custom" value="{{ request('critica') }}" placeholder="Ej: ALTA, BAJA">
                </div>
                <div class="col-md-2">
                    <label>Estado</label>
                    <select name="estado" class="form-control form-control-custom">
                        <option value="">Todos</option>
                        <option value="PENDIENTE" {{ request('estado')=='PENDIENTE'?'selected':'' }}>Pendiente</option>
                        <option value="PAGADA" {{ request('estado')=='PAGADA'?'selected':'' }}>Pagada</option>
                        <option value="VENCIDA" {{ request('estado')=='VENCIDA'?'selected':'' }}>Vencida</option>
                        <option value="ANULADA" {{ request('estado')=='ANULADA'?'selected':'' }}>Anulada</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:10px;font-weight:700;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('facturas.index') }}" class="btn btn-secondary" style="border-radius:10px;">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla con DataTable -->
    <div class="table-container">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="text-muted font-weight-bold">Resultados</h5>
            @if($facturas->count() > 0)
            <form method="POST" action="{{ route('facturas.exportar-masivo') }}" target="_blank" style="display:inline;">
                @csrf
                <!-- Pasar los mismos filtros al exportar -->
                <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                <input type="hidden" name="suscriptor" value="{{ request('suscriptor') }}">
                <input type="hidden" name="id_ruta" value="{{ request('id_ruta') }}">
                <input type="hidden" name="critica" value="{{ request('critica') }}">
                <input type="hidden" name="estado" value="{{ request('estado') }}">
                
                <button type="submit" class="btn btn-success" style="border-radius:10px;font-weight:700;">
                    <i class="fa fa-file-archive"></i> Descargar Lote (ZIP)
                </button>
            </form>
            @endif
        </div>

        <table id="tblFacturas" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>N° Factura</th>
                    <th>Suscriptor</th>
                    <th>Cliente</th>
                    <th>Período</th>
                    <th>Vence</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td><strong style="font-family:monospace;color:#2e50e4;">{{ $f->numero_factura }}</strong></td>
                    <td>{{ $f->suscriptor }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $f->cliente->nombre ?? 'N/A' }} {{ $f->cliente->apellido ?? '' }}</div>
                        <small class="text-muted">{{ $f->cliente->direccion ?? '' }}</small>
                    </td>
                    <td><span class="badge badge-info" style="border-radius:8px;">{{ $f->periodo }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td><strong>$ {{ number_format($f->total_a_pagar, 0, ',', '.') }}</strong></td>
                    <td><span class="badge-est badge-{{ $f->estado }}">{{ $f->estado }}</span></td>
                    <td>
                        @if($f->es_automatica)
                            <span title="Automática" style="color:#48bb78;"><i class="fa fa-check-circle"></i></span>
                        @else
                            <span title="Manual" style="color:#ed8936;"><i class="fa fa-edit"></i></span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('facturas.show', $f->id) }}" class="btn btn-info btn-sm btn-action" title="Ver"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('facturas.pdf', $f->id) }}" class="btn btn-primary btn-sm btn-action" target="_blank" title="PDF"><i class="fa fa-file-pdf"></i></a>
                        @if($f->estado !== 'ANULADA')
                        <button class="btn btn-danger btn-sm btn-action btn-anular" data-id="{{ $f->id }}" title="Anular"><i class="fa fa-ban"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">No hay facturas registradas con estos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Anular (Mismo que antes) -->
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Anular Factura</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="anularId">
                <div class="form-group">
                    <label>Motivo de anulación</label>
                    <textarea class="form-control" id="anularMotivo" rows="3"></textarea>
                </div>
                <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmarAnular">Confirmar Anulación</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scriptsPlugins')
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet"/>   
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
@endsection

@section('scripts')
<script>
var idioma_espanol = { /* Tu objeto de idioma español */ };

$(function () {
    $('#tblFacturas').DataTable({
        dom: '<"row"<"col-md-6"l><"col-md-6"B>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: '<i class="fa fa-file-excel"></i> Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-danger btn-sm', text: '<i class="fa fa-file-pdf"></i> PDF Tabla' },
            { extend: 'print', className: 'btn btn-info btn-sm', text: '<i class="fa fa-print"></i> Imprimir' }
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todo"]],
        order: [[0, 'desc']],
        language: idioma_espanol,
        pageLength: 25
    });
});

// Lógica de Anulación (Igual que antes)
var CSRF = $("meta[name='csrf-token']").attr("content");
$(document).on('click', '.btn-anular', function () {
    $('#anularId').val($(this).data('id'));
    $('#anularMotivo').val('');
    $('#modalAnular').modal('show');
});

$('#btnConfirmarAnular').on('click', function () {
    var id = $('#anularId').val();
    var motivo = $('#anularMotivo').val().trim();
    if (!motivo) { alert('Digite el motivo'); return; }
    
    $.ajax({
        url: '/facturacion/facturas/' + id + '/anular',
        method: 'POST',
        data: { motivo: motivo, _token: CSRF },
        success: function (r) {
            if (r.ok) { location.reload(); }
            else { alert(r.mensaje); }
        },
        error: function (xhr) { alert('Error al anular'); }
    });
});
</script>
@endsection