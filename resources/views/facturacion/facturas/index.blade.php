@extends("theme.$theme.layout")

@section('titulo', 'Listado de Facturas')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; }
.filtros-box .form-control, .filtros-box .form-control-sm { border-radius:10px; border:2px solid #e2e8f0; }
.filtros-box .form-control:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
#tblFacturas thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 8px; border:none; white-space:nowrap; text-align:center; }
#tblFacturas tbody td { padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; text-align:center; font-size:.82rem; }
#tblFacturas tbody tr:hover { background:#f8f9ff; }
.badge-est { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.badge-PAGADA    { background:#c6f6d5; color:#22543d; }
.badge-VENCIDA   { background:#fed7d7; color:#742a2a; }
.badge-ANULADA   { background:#e2e8f0; color:#718096; }
.kpi-box { border-radius:16px; padding:18px 22px; color:white; margin-bottom:20px; }
.kpi-box .kpi-val { font-size:1.6rem; font-weight:800; }
.kpi-box .kpi-lbl { font-size:.78rem; opacity:.88; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-file-invoice-dollar"></i> Facturas</h3>
            <a href="{{ route('facturas.generar') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Generar Factura Manual
            </a>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-box">
        <form method="GET" action="{{ route('facturas.index') }}">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Período</label>
                    <select name="periodo" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($periodos as $p)
                        <option value="{{ $p->codigo }}" {{ request('periodo')==$p->codigo?'selected':'' }}>
                            {{ $p->nombre }} ({{ $p->codigo }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Suscriptor</label>
                    <input type="text" name="suscriptor" class="form-control"
                           value="{{ request('suscriptor') }}" placeholder="Código suscriptor">
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
                <div class="col-md-4" style="margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;margin-right:6px;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('facturas.index') }}" class="btn btn-secondary" style="border-radius:12px;">
                        <i class="fa fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div style="background:white;border-radius:16px;padding:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);overflow-x:auto;">
        <table id="tblFacturas" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>N° Factura</th>
                    <th>Suscriptor</th>
                    <th>Período</th>
                    <th>Expide</th>
                    <th>Vence</th>
                    <th>Acueducto</th>
                    <th>Alcantar.</th>
                    <th>Otros Cobros</th>
                    <th>Saldo Ant.</th>
                    <th>Total</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td><strong style="font-family:monospace;">{{ $f->numero_factura }}</strong></td>
                    <td>
                        <strong>{{ $f->suscriptor }}</strong>
                        @if($f->cliente)
                        <br><span style="font-size:.75rem;color:#718096;">{{ \Illuminate\Support\Str::limit(trim($f->cliente->nombre . ' ' . $f->cliente->apellido), 20) }}</span>
                        @endif
                    </td>
                    <td>{{ $f->periodo }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_expedicion)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>$ {{ number_format($f->total_facturacion_acueducto, 0, ',', '.') }}</td>
                    <td>$ {{ number_format($f->subtotal_alcantarillado, 0, ',', '.') }}</td>
                    <td>$ {{ number_format($f->otros_cobros_acueducto + $f->otros_cobros_alcantarillado, 0, ',', '.') }}</td>
                    <td>
                        @if($f->saldo_anterior > 0)
                        <span style="color:#e53e3e;font-weight:700;">$ {{ number_format($f->saldo_anterior, 0, ',', '.') }}</span>
                        @else
                        <span style="color:#a0aec0;">—</span>
                        @endif
                    </td>
                    <td><strong style="color:#2d3748;">$ {{ number_format($f->total_a_pagar, 0, ',', '.') }}</strong></td>
                    <td>
                        @if($f->es_automatica)
                            <span style="font-size:.68rem;background:#e0f2fe;color:#0369a1;border-radius:8px;padding:2px 8px;font-weight:700;">AUTO</span>
                        @else
                            <span style="font-size:.68rem;background:#fef3c7;color:#b45309;border-radius:8px;padding:2px 8px;font-weight:700;">MANUAL</span>
                        @endif
                    </td>
                    <td><span class="badge-est badge-{{ $f->estado }}">{{ $f->estado }}</span></td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('facturas.show', $f->id) }}" class="btn btn-info btn-sm" title="Ver detalle">
                            <i class="fa fa-eye"></i>
                        </a>
                        @if($f->estado !== 'ANULADA')
                        <button class="btn btn-danger btn-sm btn-anular"
                                data-id="{{ $f->id }}" title="Anular factura">
                            <i class="fa fa-ban"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" style="text-align:center;padding:50px;color:#a0aec0;">
                        <i class="fa fa-file-invoice-dollar" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
                        No se encontraron facturas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $facturas->links() }}</div>
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
@endsection

@section('scripts')
<script>
var CSRF = $("meta[name='csrf-token']").attr("content");

$(document).on('click', '.btn-anular', function () {
    $('#anularId').val($(this).data('id'));
    $('#anularMotivo').val('');
    $('#modalAnular').modal('show');
});

$('#btnConfirmarAnular').on('click', function () {
    var id     = $('#anularId').val();
    var motivo = $('#anularMotivo').val().trim();
    if (!motivo) {
        Swal.fire('Campo requerido', 'Debe indicar el motivo de anulación.', 'warning');
        return;
    }
    $.ajax({
        url: '/facturacion/facturas/' + id + '/anular', method: 'POST',
        data: { motivo: motivo, _token: CSRF },
        success: function (r) {
            if (r.ok) {
                $('#modalAnular').modal('hide');
                Manteliviano.notificaciones(r.mensaje, 'Facturas', 'success');
                setTimeout(() => location.reload(), 1200);
            }
        },
        error: function (xhr) {
            Swal.fire('No se puede anular', xhr.responseJSON?.mensaje || 'Error.', 'error');
        }
    });
});
</script>
@endsection
