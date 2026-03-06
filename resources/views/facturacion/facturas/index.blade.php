@extends("theme.$theme.layout")

@section('titulo', 'Listado de Facturas')

@section('styles')
<style>
/* Estilos Modernos (Mantenemos los tuyos y agregamos algunos) */
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; }
.filtros-box .form-control { border-radius:10px; border:2px solid #e2e8f0; }
.filtros-box .form-control:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }

/* Tabla */
#tblFacturas thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 8px; border:none; white-space:nowrap; text-align:center; }
#tblFacturas tbody td { padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; text-align:center; font-size:.82rem; }
#tblFacturas tbody tr:hover { background:#f8f9ff; }
#tblFacturas tbody tr.selected { background:#e0f2fe; }

/* Badges y KPIs */
.badge-est { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.badge-PAGADA    { background:#c6f6d5; color:#22543d; }
.badge-VENCIDA   { background:#fed7d7; color:#742a2a; }
.badge-ANULADA   { background:#e2e8f0; color:#718096; }

.kpi-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
.kpi-box { border-radius:16px; padding:18px 22px; color:white; text-align:center; }
.kpi-val { font-size:1.6rem; font-weight:800; display:block; }
.kpi-lbl { font-size:.78rem; opacity:.88; text-transform:uppercase; }

/* Tarjetas de Crítica */
.critica-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px; }
.critica-card { background: white; border-left: 4px solid #667eea; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; }
.critica-nombre { font-size: 0.75rem; font-weight: bold; color: #4a5568; text-transform: uppercase; }
.critica-cantidad { font-size: 1.2rem; font-weight: 800; color: #2d3748; }

/* Botón Flotante de Exportación */
#barraAcciones { display: none; background: #fff; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 10px; z-index: 100; margin-bottom: 15px; border: 1px solid #e2e8f0; align-items: center; justify-content: space-between; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <!-- Header -->
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-file-invoice-dollar"></i> Gestión de Facturas</h3>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('facturas.masiva') }}" class="btn btn-primary" style="border-radius:12px;font-weight:700;background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                    <i class="fa fa-bolt"></i> Masiva
                </a>
                <a href="{{ route('facturas.generar') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-plus"></i> Manual
                </a>
            </div>
        </div>
    </div>

    <!-- Barra de Acciones Masivas (Oculta hasta seleccionar) -->
    <div id="barraAcciones">
        <span><i class="fa fa-check-circle text-success"></i> <strong id="contadorSeleccionados">0</strong> facturas seleccionadas</span>
        <div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="seleccionarTodo(false)">Deseleccionar</button>
            <form action="{{ route('facturas.exportar_seleccionadas') }}" method="POST" id="formExportar" style="display:inline;">
                @csrf
                <div id="contenedorIds"></div>
                <button type="submit" class="btn btn-success" style="border-radius:10px;font-weight:700;">
                    <i class="fa fa-file-export"></i> Descargar ZIP
                </button>
            </form>
        </div>
    </div>

    <!-- KPIs Generales -->
    <div class="kpi-container">
        <div class="kpi-box" style="background:linear-gradient(135deg, #667eea, #764ba2);">
            <span class="kpi-lbl">Total Facturas</span>
            <span class="kpi-val">{{ $kpiTotal }}</span>
        </div>
        <div class="kpi-box" style="background:linear-gradient(135deg, #f6ad55, #ed8936);">
            <span class="kpi-lbl">$ Pendiente</span>
            <span class="kpi-val">${{ number_format($kpiPendiente, 0, ',', '.') }}</span>
        </div>
        <div class="kpi-box" style="background:linear-gradient(135deg, #48bb78, #38a169);">
            <span class="kpi-lbl">$ Pagado</span>
            <span class="kpi-val">${{ number_format($kpiPagada, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Tarjetas por Crítica -->
    <h6 class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:0.8rem;">Resumen por Crítica</h6>
    <div class="critica-grid">
        @foreach($agrupadoPorCritica as $critica => $datos)
        <div class="critica-card">
            <div class="critica-nombre">{{ $critica }}</div>
            <div class="critica-cantidad">{{ $datos['cantidad'] }}</div>
            <small class="text-muted">${{ number_format($datos['total_valor'], 0, ',', '.') }}</small>
        </div>
        @endforeach
    </div>

    <!-- Filtros -->
    <div class="filtros-box">
        <form method="GET" action="{{ route('facturas.index') }}">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Período</label>
                    <select name="periodo" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($periodos as $p)
                        <option value="{{ $p->codigo }}" {{ request('periodo')==$p->codigo?'selected':'' }}>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">ID Ruta</label>
                    <input type="number" name="id_ruta" class="form-control" value="{{ request('id_ruta') }}" placeholder="Ej: 101">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Crítica</label>
                    <input type="text" name="critica" class="form-control" value="{{ request('critica') }}" placeholder="Ej: ALTA">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Suscriptor</label>
                    <input type="text" name="suscriptor" class="form-control" value="{{ request('suscriptor') }}" placeholder="Código">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach(['PENDIENTE','PAGADA','VENCIDA','ANULADA'] as $e)
                        <option value="{{ $e }}" {{ request('estado')==$e?'selected':'' }}>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:12px;font-weight:700;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div style="background:white;border-radius:16px;padding:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);overflow-x:auto;">
        <table id="tblFacturas" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="checkAll" onchange="toggleSelectAll(this)"></th>
                    <th>N° Factura</th>
                    <th>Suscriptor</th>
                    <th>Ruta</th>
                    <th>Crítica</th>
                    <th>Período</th>
                    <th>Vence</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facturasConCritica as $item)
                    @php $f = $item['factura']; @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="check-item" value="{{ $f->id }}" onchange="updateBarra()">
                        </td>
                        <td><strong style="font-family:monospace;">{{ $f->numero_factura }}</strong></td>
                        <td>
                            <strong>{{ $f->suscriptor }}</strong>
                            <br><span style="font-size:.7rem;color:#718096;">{{ \Illuminate\Support\Str::limit($f->cliente->nombre ?? '', 20) }}</span>
                        </td>
                        <td><span class="badge badge-secondary">{{ $item['id_ruta'] }}</span></td>
                        <td><span class="badge badge-info">{{ $item['critica'] }}</span></td>
                        <td>{{ $f->periodo }}</td>
                        <td>{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td><strong>${{ number_format($f->total_a_pagar, 0, ',', '.') }}</strong></td>
                        <td><span class="badge-est badge-{{ $f->estado }}">{{ $f->estado }}</span></td>
                        <td>
                            <a href="{{ route('facturas.show', $f->id) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('facturas.pdf', $f->id) }}" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-file-pdf"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Anular (Mismo que tenías) -->
<!-- ... (mantener tu modal de anular) ... -->

@endsection

@section('scriptsPlugins')
<!-- DataTables & Buttons -->
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
var idioma_espanol = { /* Tu objeto de idioma */ };

$(function () {
    $('#tblFacturas').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
            { extend: 'print', text: '<i class="fa fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
        ],
        pageLength: 100, // Mostramos más por página
        language: idioma_espanol,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 9] } // No ordenar por checkbox ni acciones
        ]
    });
});

// Lógica de Selección Múltiple
function toggleSelectAll(source) {
    $('.check-item').prop('checked', source.checked);
    updateBarra();
}

function updateBarra() {
    const seleccionados = $('.check-item:checked');
    const count = seleccionados.length;
    const barra = $('#barraAcciones');
    const contenedor = $('#contenedorIds');
    
    $('#contadorSeleccionados').text(count);
    
    if (count > 0) {
        barra.slideDown();
        // Generar inputs hidden para el formulario
        let html = '';
        seleccionados.each(function() {
            html += `<input type="hidden" name="ids[]" value="${this.value}">`;
        });
        contenedor.html(html);
    } else {
        barra.slideUp();
        contenedor.html('');
    }
}

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