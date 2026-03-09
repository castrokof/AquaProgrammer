@extends("theme.$theme.layout")

@section('titulo', 'Listado de Facturas')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }

.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; }
.filtros-box .form-control { border-radius:10px; border:2px solid #e2e8f0; }
.filtros-box .form-control:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }

#tblFacturas thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 8px; border:none; white-space:nowrap; text-align:center; }
#tblFacturas tbody td { padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; text-align:center; font-size:.82rem; }
#tblFacturas tbody tr:hover { background:#f8f9ff; }
#tblFacturas tbody tr.fila-seleccionada { background:#eef2ff; }

.badge-est { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.badge-PAGADA    { background:#c6f6d5; color:#22543d; }
.badge-VENCIDA   { background:#fed7d7; color:#742a2a; }
.badge-ANULADA   { background:#e2e8f0; color:#718096; }

/* Bulk bar */
#bulkBar { display:none; background:linear-gradient(135deg,#2e50e4,#2b0c49); border-radius:12px; padding:12px 20px; margin-bottom:14px; align-items:center; justify-content:space-between; color:white; }
#bulkBar.visible { display:flex; }
#bulkBar .sel-count { font-size:.9rem; font-weight:700; }
#bulkBar .btn-dl-pdf { background:white; color:#2e50e4; border:none; border-radius:10px; padding:8px 18px; font-weight:700; font-size:.85rem; cursor:pointer; }
#bulkBar .btn-dl-pdf:hover { background:#e8edff; }
#bulkBar .btn-clear { background:rgba(255,255,255,.15); color:white; border:none; border-radius:10px; padding:8px 14px; font-weight:600; font-size:.82rem; cursor:pointer; margin-left:8px; }

.check-factura { width:16px; height:16px; cursor:pointer; accent-color:#2e50e4; }
#checkAll { width:16px; height:16px; cursor:pointer; accent-color:#2e50e4; }

/* KPI cards */
.kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
.kpi-card { border-radius:16px; padding:18px 22px; color:white; position:relative; overflow:hidden; }
.kpi-card .kpi-lbl { font-size:.72rem; font-weight:700; text-transform:uppercase; opacity:.85; letter-spacing:.5px; }
.kpi-card .kpi-cnt { font-size:1.8rem; font-weight:900; line-height:1.1; margin:4px 0 2px; }
.kpi-card .kpi-val { font-size:.82rem; font-weight:600; opacity:.85; }
.kpi-card .kpi-icon { position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:2.5rem; opacity:.18; }
.kpi-pendiente { background:linear-gradient(135deg,#f6ad55,#ed8936); }
.kpi-pagada    { background:linear-gradient(135deg,#48bb78,#38a169); }
.kpi-vencida   { background:linear-gradient(135deg,#fc8181,#e53e3e); }
.kpi-anulada   { background:linear-gradient(135deg,#a0aec0,#718096); }
@media(max-width:768px){ .kpi-row { grid-template-columns:repeat(2,1fr); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- Header --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-file-invoice-dollar"></i> Facturas</h3>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('facturas.masiva') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-bolt"></i> Facturación Masiva
                </a>
                <a href="{{ route('facturas.lote') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-layer-group"></i> Facturar por Lote
                </a>
                <a href="{{ route('facturas.generar') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-plus"></i> Generar Manual
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-row" id="kpiRow">
        <div class="kpi-card kpi-pendiente">
            <div class="kpi-lbl">Pendiente</div>
            <div class="kpi-cnt" id="kpiPendienteCnt">—</div>
            <div class="kpi-val" id="kpiPendienteVal">—</div>
            <i class="fa fa-clock kpi-icon"></i>
        </div>
        <div class="kpi-card kpi-pagada">
            <div class="kpi-lbl">Pagada</div>
            <div class="kpi-cnt" id="kpiPagadaCnt">—</div>
            <div class="kpi-val" id="kpiPagadaVal">—</div>
            <i class="fa fa-check-circle kpi-icon"></i>
        </div>
        <div class="kpi-card kpi-vencida">
            <div class="kpi-lbl">Vencida</div>
            <div class="kpi-cnt" id="kpiVencidaCnt">—</div>
            <div class="kpi-val" id="kpiVencidaVal">—</div>
            <i class="fa fa-exclamation-circle kpi-icon"></i>
        </div>
        <div class="kpi-card kpi-anulada">
            <div class="kpi-lbl">Anulada</div>
            <div class="kpi-cnt" id="kpiAnuladaCnt">—</div>
            <div class="kpi-val" id="kpiAnuladaVal">—</div>
            <i class="fa fa-ban kpi-icon"></i>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="filtros-box">
        <div class="row align-items-end">
            <div class="col-md-2">
                <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Período</label>
                <select id="fPeriodo" class="form-control">
                    <option value="">— Todos —</option>
                    @foreach($periodos as $p)
                    <option value="{{ $p->codigo }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">ID Ruta</label>
                <input type="number" id="fRuta" class="form-control" placeholder="Ej: 101">
            </div>
            <div class="col-md-2">
                <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Crítica</label>
                <input type="text" id="fCritica" class="form-control" placeholder="Ej: ALTA">
            </div>
            <div class="col-md-2">
                <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Suscriptor</label>
                <input type="text" id="fSuscriptor" class="form-control" placeholder="Código">
            </div>
            <div class="col-md-2">
                <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Estado</label>
                <select id="fEstado" class="form-control">
                    <option value="">— Todos —</option>
                    @foreach(['PENDIENTE','PAGADA','VENCIDA','ANULADA'] as $e)
                    <option value="{{ $e }}">{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button id="btnFiltrar" class="btn btn-primary w-100" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>

    {{-- Barra acciones masivas --}}
    <div id="bulkBar">
        <span class="sel-count"><i class="fa fa-check-square"></i> <span id="cntSel">0</span> factura(s) seleccionada(s)</span>
        <div>
            <button class="btn-dl-pdf" id="btnDescargaPDF">
                <i class="fa fa-file-pdf"></i> Descargar PDF
            </button>
            <button class="btn-clear" id="btnClearSel">
                <i class="fa fa-times"></i> Limpiar selección
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div style="background:white;border-radius:16px;padding:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);overflow-x:auto;">
        <table id="tblFacturas" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="checkAll" title="Seleccionar todos"></th>
                    <th>N° Factura</th>
                    <th>Suscriptor</th>
                    <th>Período</th>
                    <th>Expedición</th>
                    <th>Vencimiento</th>
                    <th>Total</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Modal anular --}}
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:#e53e3e;border:none;padding:20px 24px;">
                <h5 class="modal-title" style="color:white;font-weight:700;"><i class="fa fa-ban"></i> Anular Factura</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white;opacity:.8;">&times;</button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="anularId">
                <div class="form-group">
                    <label style="font-weight:600;color:#4a5568;font-size:.85rem;">Motivo de anulación <span style="color:red">*</span></label>
                    <textarea class="form-control" id="anularMotivo" rows="3" placeholder="Describa el motivo..."
                              style="border-radius:10px;border:2px solid #e2e8f0;"></textarea>
                </div>
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

{{-- Formulario oculto para descarga masiva de PDF --}}
<form id="formPdfMasivo" action="{{ route('facturas.pdf-masivo') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <div id="idsContainer"></div>
</form>

@endsection
@section('scriptsPlugins')
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>   
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/select2/js/select2.full.min.js")}}" type="text/javascript"></script>
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script> -->
@endsection

@section('scripts')
<script>
var CSRF     = '{{ csrf_token() }}';
var DATA_URL = '{{ route("facturas.data") }}';
var KPIS_URL = '{{ route("facturas.kpis") }}';

function fmtCOP(n) {
    return '$ ' + parseFloat(n || 0).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0});
}

function cargarKpis() {
    var params = {
        periodo:    $('#fPeriodo').val(),
        id_ruta:    $('#fRuta').val(),
        critica:    $('#fCritica').val(),
        suscriptor: $('#fSuscriptor').val(),
        estado:     $('#fEstado').val()
    };
    $.get(KPIS_URL, params, function (r) {
        $('#kpiPendienteCnt').text(r.pendiente.cantidad);
        $('#kpiPendienteVal').text(fmtCOP(r.pendiente.total));
        $('#kpiPagadaCnt').text(r.pagada.cantidad);
        $('#kpiPagadaVal').text(fmtCOP(r.pagada.total));
        $('#kpiVencidaCnt').text(r.vencida.cantidad);
        $('#kpiVencidaVal').text(fmtCOP(r.vencida.total));
        $('#kpiAnuladaCnt').text(r.anulada.cantidad);
        $('#kpiAnuladaVal').text(fmtCOP(r.anulada.total));
    });
}

$(function () {

    // ── DataTable ──────────────────────────────────────────────────────────────
    var tabla = $('#tblFacturas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: DATA_URL,
            data: function (d) {
                d.periodo    = $('#fPeriodo').val();
                d.id_ruta    = $('#fRuta').val();
                d.critica    = $('#fCritica').val();
                d.suscriptor = $('#fSuscriptor').val();
                d.estado     = $('#fEstado').val();
            }
        },
        columns: [
            {
                data: 'id', orderable: false,
                render: function (id) {
                    return '<input type="checkbox" class="check-factura" value="' + id + '">';
                }
            },
            {
                data: 'numero',
                render: function (v) { return '<strong style="font-family:monospace;">' + v + '</strong>'; }
            },
            {
                data: 'suscriptor',
                render: function (v, t, row) {
                    var html = '<strong>' + v + '</strong>';
                    if (row.nombre) html += '<br><span style="font-size:.75rem;color:#718096;">' + row.nombre + '</span>';
                    return html;
                }
            },
            { data: 'periodo' },
            { data: 'expedicion' },
            { data: 'vencimiento' },
            {
                data: 'total',
                render: function (v) { return '$ ' + v; }
            },
            {
                data: 'tipo',
                render: function (v) {
                    var style = v === 'AUTO'
                        ? 'background:#e0f2fe;color:#0369a1;'
                        : 'background:#fef3c7;color:#b45309;';
                    return '<span style="font-size:.68rem;' + style + 'border-radius:8px;padding:2px 8px;font-weight:700;">' + v + '</span>';
                }
            },
            {
                data: 'estado',
                render: function (v) {
                    return '<span class="badge-est badge-' + v + '">' + v + '</span>';
                }
            },
            {
                data: null, orderable: false,
                render: function (d, t, row) {
                    var html = '<a href="' + row.url_ver + '" class="btn btn-info btn-sm" title="Ver detalle"><i class="fa fa-eye"></i></a> ';
                    html    += '<a href="' + row.url_pdf + '" class="btn btn-secondary btn-sm" title="PDF" target="_blank"><i class="fa fa-file-pdf"></i></a>';
                    if (!row.anulada) {
                        html += ' <button class="btn btn-danger btn-sm btn-anular" data-id="' + row.id + '" title="Anular"><i class="fa fa-ban"></i></button>';
                    }
                    return html;
                }
            }
        ],
        pageLength: 25,
        order: [[4, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
            { extend: 'print',      text: '<i class="fa fa-print"></i> Imprimir',   className: 'btn btn-info btn-sm' }
        ],
        drawCallback: function () {
            // Restablecer checkAll al cambiar página
            $('#checkAll').prop('checked', false).prop('indeterminate', false);
            actualizarBulkBar();
        }
    });

    // Cargar KPIs al iniciar
    cargarKpis();

    // ── Filtrar ────────────────────────────────────────────────────────────────
    $('#btnFiltrar').on('click', function () {
        tabla.ajax.reload();
        cargarKpis();
    });

    // Filtrar también al presionar Enter en los inputs
    $('#fRuta, #fCritica, #fSuscriptor').on('keypress', function (e) {
        if (e.which === 13) { tabla.ajax.reload(); cargarKpis(); }
    });

    // ── Selección masiva ───────────────────────────────────────────────────────
    function actualizarBulkBar() {
        var n = $('.check-factura:checked').length;
        $('#cntSel').text(n);
        n > 0 ? $('#bulkBar').addClass('visible') : $('#bulkBar').removeClass('visible');
        $('.check-factura').each(function () {
            $(this).closest('tr').toggleClass('fila-seleccionada', $(this).is(':checked'));
        });
    }

    $('#checkAll').on('change', function () {
        $('.check-factura').prop('checked', this.checked);
        actualizarBulkBar();
    });

    $(document).on('change', '.check-factura', function () {
        var total   = $('.check-factura').length;
        var checked = $('.check-factura:checked').length;
        $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
        $('#checkAll').prop('checked', checked === total);
        actualizarBulkBar();
    });

    $('#btnClearSel').on('click', function () {
        $('.check-factura, #checkAll').prop('checked', false).prop('indeterminate', false);
        actualizarBulkBar();
    });

    // ── Descarga masiva PDF ────────────────────────────────────────────────────
    $('#btnDescargaPDF').on('click', function () {
        var ids = $('.check-factura:checked').map(function () { return this.value; }).get();
        if (!ids.length) return;
        var $cont = $('#idsContainer').empty();
        ids.forEach(function (id) {
            $cont.append('<input type="hidden" name="ids[]" value="' + id + '">');
        });
        $('#formPdfMasivo').submit();
    });

    // ── Anular ─────────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-anular', function () {
        $('#anularId').val($(this).data('id'));
        $('#anularMotivo').val('');
        $('#modalAnular').modal('show');
    });

    $('#btnConfirmarAnular').on('click', function () {
        var id     = $('#anularId').val();
        var motivo = $('#anularMotivo').val().trim();
        if (!motivo) { alert('Digite el motivo'); return; }

        $.ajax({
            url: '/facturacion/facturas/' + id + '/anular',
            method: 'POST',
            data: { motivo: motivo, _token: CSRF },
            success: function (r) {
                if (r.ok) {
                    $('#modalAnular').modal('hide');
                    tabla.ajax.reload(null, false);
                } else {
                    alert(r.mensaje);
                }
            },
            error: function () { alert('Error al anular'); }
        });
    });

});
</script>
@endsection
