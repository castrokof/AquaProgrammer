@extends("theme.$theme.layout")

@section('titulo', 'Cargar Lecturas Anteriores')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:24px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:20px 26px; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.15rem; margin:0; }
.modern-card .card-body { padding:28px; }

.option-tabs { display:flex; gap:8px; margin-bottom:24px; }
.option-tab { flex:1; padding:14px; border-radius:14px; border:2px solid #e2e8f0; background:white;
              text-align:center; cursor:pointer; font-weight:600; color:#718096; transition:all .2s; }
.option-tab.activo { border-color:#2e50e4; background:#eef2ff; color:#2e50e4; }
.option-tab i { display:block; font-size:1.6rem; margin-bottom:4px; }

.panel-opcion { display:none; }
.panel-opcion.visible { display:block; }

.info-box { background:#f0f4ff; border-left:4px solid #2e50e4; border-radius:8px; padding:14px 18px; margin-bottom:20px; font-size:.88rem; color:#374151; }
.info-box strong { color:#2e50e4; }

.flow-arrow { display:flex; align-items:center; justify-content:center; gap:6px; font-size:.8rem; color:#6b7280; margin:4px 0; }
.flow-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.75rem; font-weight:700; }
.badge-orden { background:#dbeafe; color:#1d4ed8; }
.badge-hist  { background:#d1fae5; color:#065f46; }

.columnas-tabla { width:100%; border-collapse:collapse; font-size:.85rem; margin-top:10px; }
.columnas-tabla th { background:#2e50e4; color:white; padding:8px 12px; text-align:left; }
.columnas-tabla td { padding:7px 12px; border-bottom:1px solid #e2e8f0; }
.columnas-tabla tr:last-child td { border:none; }
.req { color:#e53e3e; font-weight:700; }

.form-label { font-weight:600; font-size:.82rem; color:#4a5568; text-transform:uppercase; letter-spacing:.4px; }
.btn-accion { padding:10px 28px; border-radius:12px; font-weight:700; font-size:.9rem; }

.section-divider { border-top:1px dashed #e2e8f0; margin:20px 0; padding-top:20px; }
</style>
@endsection

@section('contenido')
<div class="row justify-content-center">
<div class="col-lg-9">

    {{-- Alertas --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-upload"></i> Cargar Lecturas Anteriores a Ordenescu</h3>
        </div>
        <div class="card-body">

            <div class="info-box">
                <strong>¿Para qué sirve?</strong> Antes de que los lectores salgan a campo con el nuevo período,
                se carga en <strong>ordenescu</strong> la lectura anterior (<em>LA</em>) y el promedio de cada
                suscriptor. El consumo del período facturado se registra en el <strong>histórico</strong> para
                que el promedio del cliente se mantenga actualizado.
            </div>

            {{-- Tabs de opciones --}}
            <div class="option-tabs">
                <div class="option-tab activo" id="tab1" onclick="mostrar(1)">
                    <i class="fa fa-sync-alt"></i>
                    Desde Facturas del Sistema
                </div>
                <div class="option-tab" id="tab2" onclick="mostrar(2)">
                    <i class="fa fa-file-excel"></i>
                    Desde Excel (externo)
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- OPCIÓN 1: desde facturas del sistema                          --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="panel-opcion visible" id="panel1">

                <div class="info-box">
                    <strong>Qué hace:</strong><br>
                    <div class="flow-arrow mt-1">
                        <code>facturas.lectura_actual + promedio</code>
                        <i class="fa fa-long-arrow-alt-right"></i>
                        <span class="flow-badge badge-orden">ordenescu.LA / Promedio</span>
                        <small>(período lecturas)</small>
                    </div>
                    <div class="flow-arrow">
                        <code>facturas.consumo_m3 + lecturas</code>
                        <i class="fa fa-long-arrow-alt-right"></i>
                        <span class="flow-badge badge-hist">histórico consumos</span>
                        <small>(período facturado · actualiza promedio cliente)</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('lecturas.sincronizar') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="form-label">Período Facturado (fuente)</label>
                                <select name="periodo_factura" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach($periodos as $p)
                                    <option value="{{ $p->codigo }}">{{ $p->nombre }} ({{ $p->codigo }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">El mes que ya fue facturado</small>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-center pt-2">
                            <i class="fa fa-arrow-right fa-2x text-primary"></i>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="form-label">Período de Lecturas (destino)</label>
                                <select name="periodo_lectura" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach($periodos as $p)
                                    <option value="{{ $p->codigo }}">{{ $p->nombre }} ({{ $p->codigo }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">El mes de lecturas a actualizar</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-accion">
                        <i class="fa fa-sync-alt"></i> Sincronizar
                    </button>
                </form>
            </div>

            {{-- ══════════════════════════════════════════════════════════════ --}}
            {{-- OPCIÓN 2: desde Excel externo                                 --}}
            {{-- ══════════════════════════════════════════════════════════════ --}}
            <div class="panel-opcion" id="panel2">

                <div class="info-box">
                    <strong>Qué hace:</strong><br>
                    <div class="flow-arrow mt-1">
                        <code>lec_anterior + promedio</code>
                        <i class="fa fa-long-arrow-alt-right"></i>
                        <span class="flow-badge badge-orden">ordenescu.LA / Promedio</span>
                        <small>(período de lecturas)</small>
                    </div>
                    <div class="flow-arrow">
                        <code>consumo</code>
                        <i class="fa fa-long-arrow-alt-right"></i>
                        <span class="flow-badge badge-hist">histórico consumos</span>
                        <small>(período facturado · actualiza promedio cliente)</small>
                    </div>
                    <a href="{{ route('lecturas.plantilla') }}" class="font-weight-bold d-inline-block mt-2">
                        <i class="fa fa-download"></i> Descargar plantilla CSV
                    </a>
                </div>

                <table class="columnas-tabla mb-3">
                    <thead>
                        <tr>
                            <th>Columna en Excel</th>
                            <th>Descripción</th>
                            <th>Destino</th>
                            <th>Requerido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>suscriptor</code></td>
                            <td>Número de suscriptor</td>
                            <td>—</td>
                            <td class="req">Sí</td>
                        </tr>
                        <tr>
                            <td><code>lec_anterior</code></td>
                            <td>Última lectura registrada</td>
                            <td><span class="flow-badge badge-orden">ordenescu.LA</span></td>
                            <td>Opcional</td>
                        </tr>
                        <tr>
                            <td><code>promedio</code></td>
                            <td>Promedio de consumo (m³)</td>
                            <td><span class="flow-badge badge-orden">ordenescu.Promedio</span></td>
                            <td>Opcional</td>
                        </tr>
                        <tr>
                            <td><code>consumo</code></td>
                            <td>Consumo del mes facturado (m³)</td>
                            <td><span class="flow-badge badge-hist">histórico</span></td>
                            <td>Opcional</td>
                        </tr>
                    </tbody>
                </table>

                <form method="POST" action="{{ route('lecturas.importar-excel') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Período de Lecturas (destino en ordenescu)</label>
                                <select name="periodo_destino" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach($periodos as $p)
                                    <option value="{{ $p->codigo }}">{{ $p->nombre }} ({{ $p->codigo }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Las órdenes de este período se actualizarán</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Período Facturado <small class="text-muted font-weight-normal">(para histórico de consumos)</small></label>
                                <select name="periodo_facturado" class="form-control">
                                    <option value="">— No registrar en histórico —</option>
                                    @foreach($periodos as $p)
                                    <option value="{{ $p->codigo }}">{{ $p->nombre }} ({{ $p->codigo }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Si selecciona, el consumo se guarda en el histórico</small>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="form-group mb-0">
                            <label class="form-label">Archivo Excel / CSV</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="archivo"
                                           id="archivoExcel" accept=".xlsx,.xls,.csv" required>
                                    <label class="custom-file-label" for="archivoExcel">Seleccionar archivo…</label>
                                </div>
                            </div>
                            <small class="text-muted">Formatos: .xlsx, .xls, .csv</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-accion mt-2">
                        <i class="fa fa-upload"></i> Importar Excel
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
</div>
@endsection

@section('scripts')
<script>
function mostrar(n) {
    document.querySelectorAll('.panel-opcion').forEach(p => p.classList.remove('visible'));
    document.querySelectorAll('.option-tab').forEach(t => t.classList.remove('activo'));
    document.getElementById('panel' + n).classList.add('visible');
    document.getElementById('tab' + n).classList.add('activo');
}
document.getElementById('archivoExcel').addEventListener('change', function () {
    var nombre = this.files[0] ? this.files[0].name : 'Seleccionar archivo…';
    this.nextElementSibling.textContent = nombre;
});
</script>
@endsection
