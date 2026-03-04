@extends("theme.$theme.layout")

@section('titulo', 'Historial de Pagos')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.filtros-box { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,.05); margin-bottom:20px; }
.filtros-box .form-control { border-radius:10px; border:2px solid #e2e8f0; }
.filtros-box .form-control:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
#tblPagos thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 8px; border:none; white-space:nowrap; text-align:center; }
#tblPagos tbody td { padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0; text-align:center; font-size:.82rem; }
#tblPagos tbody tr:hover { background:#f8f9ff; }
.badge-medio { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.badge-EFECTIVO      { background:#c6f6d5; color:#22543d; }
.badge-TRANSFERENCIA { background:#bee3f8; color:#2a4365; }
.badge-CONSIGNACION  { background:#fefcbf; color:#744210; }
.badge-DATAFONO      { background:#e9d8fd; color:#553c9a; }
.badge-OTRO          { background:#e2e8f0; color:#4a5568; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fas fa-money-bill-wave"></i> Historial de Pagos</h3>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-box">
        <form method="GET" action="{{ route('pagos.index') }}">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Desde</label>
                    <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">Medio de Pago</label>
                    <select name="medio_pago" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach(['EFECTIVO','TRANSFERENCIA','CONSIGNACION','DATAFONO','OTRO'] as $m)
                        <option value="{{ $m }}" {{ request('medio_pago')==$m ? 'selected':'' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label style="font-weight:600;font-size:.8rem;color:#4a5568;text-transform:uppercase;">N° Recibo</label>
                    <input type="text" name="numero_recibo" class="form-control" placeholder="Buscar..." value="{{ request('numero_recibo') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary" style="border-radius:10px;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('pagos.index') }}" class="btn btn-secondary ml-2" style="border-radius:10px;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div class="modern-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tblPagos" class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>N° Recibo</th>
                            <th>Factura</th>
                            <th>Suscriptor</th>
                            <th>Cliente</th>
                            <th>Medio</th>
                            <th>Acueducto</th>
                            <th>Alcantarillado</th>
                            <th>Otros Cobros</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                        <tr>
                            <td>{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
                            <td>{{ $pago->numero_recibo ?: '—' }}</td>
                            <td>
                                @if($pago->factura)
                                <a href="{{ route('facturas.show', $pago->factura_id) }}" style="font-weight:600;">
                                    {{ $pago->factura->numero_factura }}
                                </a>
                                @else —
                                @endif
                            </td>
                            <td>{{ optional($pago->factura)->suscriptor ?? '—' }}</td>
                            <td style="text-align:left;">{{ optional(optional($pago->factura)->cliente)->nombre_completo ?? '—' }}</td>
                            <td>
                                <span class="badge-medio badge-{{ $pago->medio_pago }}">{{ $pago->medio_pago }}</span>
                            </td>
                            <td>$ {{ number_format($pago->pagos_acueducto, 0, ',', '.') }}</td>
                            <td>$ {{ number_format($pago->pagos_alcantarillado, 0, ',', '.') }}</td>
                            <td>$ {{ number_format($pago->pago_otros_cobros_acueducto + $pago->pago_otros_cobros_alcantarillado, 0, ',', '.') }}</td>
                            <td style="font-weight:700; color:#2e50e4;">
                                $ {{ number_format($pago->total_pago_realizado, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="padding:30px;color:#a0aec0;font-style:italic;">
                                No se encontraron pagos con los filtros aplicados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
        <span style="font-size:.82rem;color:#718096;">
            Mostrando {{ $pagos->firstItem() ?? 0 }}–{{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} pagos
        </span>
        {{ $pagos->links() }}
    </div>

</div>
@endsection
