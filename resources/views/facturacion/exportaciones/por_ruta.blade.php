@extends("theme.$theme.layout")

@section('titulo', 'PDFs por Ruta')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.modern-card .card-body { padding:24px 28px; }

.filtros-box { background:#f8f9ff; border-radius:14px; padding:20px; margin-bottom:24px; border:1px solid #e2e8f0; }

.btn-generar { background:linear-gradient(135deg,#2e50e4,#2b0c49); color:white; border:none; border-radius:12px; padding:10px 26px; font-weight:700; font-size:.9rem; cursor:pointer; transition:.2s; }
.btn-generar:hover { opacity:.88; }
.btn-generar:disabled { opacity:.5; cursor:not-allowed; }

.tbl-rutas thead th { background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%); color:white; font-weight:600; font-size:.73rem; text-transform:uppercase; padding:12px 10px; border:none; white-space:nowrap; }
.tbl-rutas tbody td { padding:11px 10px; vertical-align:middle; border-bottom:1px solid #f0f0f0; font-size:.85rem; }
.tbl-rutas tbody tr:hover { background:#f8f9ff; }

.badge-PENDIENTE   { background:#fef3c7; color:#92400e; padding:3px 10px; border-radius:20px; font-weight:700; font-size:.72rem; }
.badge-PROCESANDO  { background:#dbeafe; color:#1e3a8a; padding:3px 10px; border-radius:20px; font-weight:700; font-size:.72rem; }
.badge-LISTO       { background:#c6f6d5; color:#22543d; padding:3px 10px; border-radius:20px; font-weight:700; font-size:.72rem; }
.badge-ERROR       { background:#fed7d7; color:#742a2a; padding:3px 10px; border-radius:20px; font-weight:700; font-size:.72rem; }

.barra-wrap { background:#e2e8f0; border-radius:8px; height:8px; overflow:hidden; min-width:80px; }
.barra-fill { height:100%; border-radius:8px; background:linear-gradient(90deg,#2e50e4,#667eea); transition:width .4s ease; }

.btn-dl { background:linear-gradient(135deg,#48bb78,#38a169); color:white; border:none; border-radius:8px; padding:5px 14px; font-weight:700; font-size:.8rem; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-dl:hover { opacity:.85; color:white; text-decoration:none; }
.btn-dl:disabled, .btn-dl.disabled { background:#a0aec0; cursor:not-allowed; pointer-events:none; }

#alertBox { display:none; border-radius:12px; padding:14px 18px; margin-bottom:16px; font-size:.88rem; }
#alertBox.success { background:#c6f6d5; color:#22543d; border:1px solid #68d391; display:block; }
#alertBox.error   { background:#fed7d7; color:#742a2a; border:1px solid #fc8181; display:block; }
</style>
@endsection

@section('contenido')
<div class="modern-card">
    <div class="card-header">
        <h3><i class="fa fa-folder-open"></i> Generación de PDFs por Ruta</h3>
    </div>
    <div class="card-body">

        {{-- ── Filtro período ────────────────────────────────────────────── --}}
        <div class="filtros-box">
            <form method="GET" action="{{ route('exportaciones.ruta.index') }}" id="frmFiltro">
                <div class="row">
                    <div class="col-md-4">
                        <label class="control-label" style="font-weight:600;color:#4a5568;">Período</label>
                        <select name="periodo" id="selPeriodo" class="form-control" required>
                            <option value="">-- Seleccione un período --</option>
                            @foreach($periodos as $p)
                                <option value="{{ $p->codigo }}" {{ $periodo == $p->codigo ? 'selected' : '' }}>
                                    {{ $p->nombre }} ({{ $p->codigo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" style="display:flex;align-items:flex-end;gap:8px;">
                        <button type="submit" class="btn btn-default" style="border-radius:10px;padding:8px 18px;">
                            <i class="fa fa-search"></i> Ver rutas
                        </button>
                        @if($periodo)
                        <button type="button" id="btnGenerar" class="btn-generar">
                            <i class="fa fa-cogs"></i> Generar PDFs
                        </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div id="alertBox"></div>

        {{-- ── Tabla de rutas ────────────────────────────────────────────── --}}
        @if($periodo)
        <p style="color:#718096;font-size:.85rem;margin-bottom:10px;">
            Período: <strong>{{ $periodo }}</strong> &mdash;
            <span id="txtTotal">{{ $exportaciones->count() }}</span> ruta(s) encontrada(s).
        </p>

        <div class="table-responsive">
            <table class="table tbl-rutas" id="tblRutas">
                <thead>
                    <tr>
                        <th>Ruta</th>
                        <th>Facturas</th>
                        <th>Estado</th>
                        <th style="min-width:100px;">Progreso</th>
                        <th>Generado por</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyRutas">
                @forelse($exportaciones as $exp)
                    <tr id="row-{{ $exp->id }}">
                        <td><strong>{{ $exp->id_ruta }}</strong></td>
                        <td style="text-align:center;">{{ $exp->total }}</td>
                        <td><span class="badge badge-{{ $exp->estado }}" id="estado-{{ $exp->id }}">{{ $exp->estado }}</span></td>
                        <td>
                            <div class="barra-wrap">
                                <div class="barra-fill" id="barra-{{ $exp->id }}" style="width:{{ $exp->progreso }}%"></div>
                            </div>
                            <span id="pct-{{ $exp->id }}" style="font-size:.72rem;color:#718096;">{{ $exp->progreso }}%</span>
                        </td>
                        <td>{{ optional($exp->usuario)->name ?? 'Sistema' }}</td>
                        <td>{{ $exp->updated_at ? $exp->updated_at->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            @if($exp->estado === 'LISTO')
                                <a href="{{ route('exportaciones.ruta.descargar', $exp->id) }}"
                                   class="btn-dl" id="btn-dl-{{ $exp->id }}">
                                    <i class="fa fa-download"></i> Descargar
                                </a>
                            @elseif($exp->estado === 'ERROR')
                                <span style="color:#e53e3e;font-size:.78rem;" title="{{ $exp->mensaje_error }}">
                                    <i class="fa fa-exclamation-triangle"></i> Error
                                </span>
                            @else
                                <span class="btn-dl disabled" id="btn-dl-{{ $exp->id }}">
                                    <i class="fa fa-clock-o"></i> Espere...
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="rowVacio"><td colspan="7" style="text-align:center;color:#a0aec0;padding:30px;">
                        Ningún PDF generado para este período. Haga clic en <strong>Generar PDFs</strong>.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    var periodo  = @json($periodo);
    var pollers  = {};  // exportacion_id -> intervalId

    /* ── Botón Generar ──────────────────────────────────────────────────── */
    var btnGenerar = document.getElementById('btnGenerar');
    if (btnGenerar) {
        btnGenerar.addEventListener('click', function () {
            if (!periodo) return;

            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando...';
            showAlert('', '');

            fetch('{{ route("exportaciones.ruta.generar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ periodo: periodo }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showAlert(data.mensaje, 'error');
                    btnGenerar.disabled = false;
                    btnGenerar.innerHTML = '<i class="fa fa-cogs"></i> Generar PDFs';
                    return;
                }

                showAlert(data.mensaje, 'success');

                // Insertar filas para las rutas creadas
                var tbody = document.getElementById('tbodyRutas');
                var rowVacio = document.getElementById('rowVacio');
                if (rowVacio) rowVacio.remove();

                data.rutas.forEach(function (item) {
                    insertarFila(tbody, item);
                    iniciarPoller(item.exportacion_id);
                });

                document.getElementById('txtTotal').textContent = data.rutas.length;

                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="fa fa-cogs"></i> Generar PDFs';
            })
            .catch(function (err) {
                showAlert('Error de red: ' + err, 'error');
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="fa fa-cogs"></i> Generar PDFs';
            });
        });
    }

    /* ── Polling de filas existentes en estado no-final ─────────────────── */
    document.querySelectorAll('[id^="estado-"]').forEach(function (el) {
        var id = el.id.replace('estado-', '');
        var estado = el.textContent.trim();
        if (estado === 'PENDIENTE' || estado === 'PROCESANDO') {
            iniciarPoller(parseInt(id));
        }
    });

    /* ── Helpers ─────────────────────────────────────────────────────────── */
    function insertarFila(tbody, item) {
        // Si ya existe la fila, no duplicar
        if (document.getElementById('row-' + item.exportacion_id)) return;

        var tr = document.createElement('tr');
        tr.id = 'row-' + item.exportacion_id;
        tr.innerHTML =
            '<td><strong>' + item.ruta + '</strong></td>' +
            '<td style="text-align:center;">' + item.total + '</td>' +
            '<td><span class="badge badge-PENDIENTE" id="estado-' + item.exportacion_id + '">PENDIENTE</span></td>' +
            '<td>' +
                '<div class="barra-wrap"><div class="barra-fill" id="barra-' + item.exportacion_id + '" style="width:0%"></div></div>' +
                '<span id="pct-' + item.exportacion_id + '" style="font-size:.72rem;color:#718096;">0%</span>' +
            '</td>' +
            '<td>—</td>' +
            '<td>—</td>' +
            '<td><span class="btn-dl disabled" id="btn-dl-' + item.exportacion_id + '"><i class="fa fa-clock-o"></i> Espere...</span></td>';
        tbody.appendChild(tr);
    }

    function iniciarPoller(id) {
        if (pollers[id]) return; // ya hay un poller activo

        pollers[id] = setInterval(function () {
            fetch('{{ url("facturacion/exportaciones/ruta") }}/' + id + '/estado', {
                headers: { 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (d) { actualizarFila(id, d); })
            .catch(function () {});
        }, 3000);
    }

    function actualizarFila(id, d) {
        var elEstado = document.getElementById('estado-' + id);
        var elBarra  = document.getElementById('barra-'  + id);
        var elPct    = document.getElementById('pct-'    + id);
        var elBtnDl  = document.getElementById('btn-dl-' + id);

        if (elEstado) {
            elEstado.className = 'badge badge-' + d.estado;
            elEstado.textContent = d.estado;
        }
        if (elBarra) elBarra.style.width = (d.progreso || 0) + '%';
        if (elPct)   elPct.textContent   = (d.progreso || 0) + '%';

        if (d.estado === 'LISTO' && d.url_descarga) {
            clearInterval(pollers[id]);
            delete pollers[id];

            if (elBtnDl) {
                elBtnDl.outerHTML =
                    '<a href="' + d.url_descarga + '" class="btn-dl" id="btn-dl-' + id + '">' +
                    '<i class="fa fa-download"></i> Descargar</a>';
            }
        }

        if (d.estado === 'ERROR') {
            clearInterval(pollers[id]);
            delete pollers[id];

            if (elBtnDl) {
                elBtnDl.outerHTML =
                    '<span style="color:#e53e3e;font-size:.78rem;" title="' + (d.error || '') + '">' +
                    '<i class="fa fa-exclamation-triangle"></i> Error</span>';
            }
        }
    }

    function showAlert(msg, type) {
        var el = document.getElementById('alertBox');
        if (!msg) { el.style.display = 'none'; return; }
        el.className = type;
        el.textContent = msg;
    }
})();
</script>
@endsection
