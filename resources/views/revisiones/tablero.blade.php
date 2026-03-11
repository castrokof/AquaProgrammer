@extends("theme.$theme.layout")

@section('titulo', 'Tablero de Revisiones')

@section('styles')
<style>
/* ── Tarjetas stat ─────────────────────────────────────────────── */
.stat-card {
    background: white; border-radius: 20px; padding: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
    border: none; height: 100%; position: relative; overflow: hidden;
    animation: fadeInUp 0.5s ease-out;
}
.stat-card:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.stat-card > * { position: relative; z-index: 2; }
.stat-card.primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
.stat-card.success { background: linear-gradient(135deg,#0b655e 0%,#1a743d 100%); color:white; }
.stat-card.warning { background: linear-gradient(135deg,#b76983 0%,#70484d 100%); color:white; }
.stat-card.info    { background: linear-gradient(135deg,#4facfe 0%,#00f2fe 100%); color:white; }
.stat-card.purple  { background: linear-gradient(135deg,#a8edea 0%,#fed6e3 100%); color:#333; }
.stat-card::after {
    position:absolute; font-size:8rem; right:-20px; bottom:-30px; opacity:.1; z-index:1;
}
.stat-card.primary::after { content:'📋'; }
.stat-card.success::after { content:'✓'; }
.stat-card.warning::after { content:'⏳'; }
.stat-card.info::after    { content:'📷'; }
.stat-card.purple::after  { content:'💯'; }
.stat-icon {
    width:60px; height:60px; border-radius:15px; display:flex; align-items:center;
    justify-content:center; font-size:1.8rem; margin-bottom:15px;
    background:rgba(255,255,255,0.2); backdrop-filter:blur(10px);
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
.stat-number { font-size:2.5rem; font-weight:800; margin:12px 0; line-height:1; text-shadow:0 2px 10px rgba(0,0,0,0.1); }
.stat-label  { font-size:0.9rem; opacity:.95; font-weight:600; text-transform:uppercase; letter-spacing:1px; }

/* ── Card principal ──────────────────────────────────────────── */
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.1); border:none; overflow:hidden; margin-bottom:20px; background:white; animation:fadeIn 0.5s ease-out; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.4rem; margin:0; text-shadow:0 2px 10px rgba(0,0,0,0.2); }
.modern-card .card-body { padding:28px; background:#fafbfc; }

/* ── Filtros ─────────────────────────────────────────────────── */
.filtros-container { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); margin-bottom:22px; }
.filtros-container .form-control { border-radius:12px; border:2px solid #e2e8f0; padding:10px 14px; font-size:0.9rem; transition:all 0.3s; }
.filtros-container .form-control:focus { border-color:#667eea; box-shadow:0 0 0 4px rgba(102,126,234,0.1); outline:none; }

/* ── Tabla por revisor ───────────────────────────────────────── */
.table-block { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.07); margin-bottom:22px; }
.table-block h5 { font-weight:700; color:#2d3748; margin-bottom:14px; font-size:1rem; text-transform:uppercase; letter-spacing:.5px; }
#tblRevisores thead th, #tblMotivos thead th, #tblAcometida thead th {
    background:linear-gradient(135deg,#3d57ce 0%,#776a84 100%);
    color:white; font-weight:600; font-size:.75rem; text-transform:uppercase;
    letter-spacing:.5px; padding:12px 8px; border:none; white-space:nowrap; text-align:center;
}
#tblRevisores tbody td, #tblMotivos tbody td, #tblAcometida tbody td {
    padding:10px 8px; vertical-align:middle; border-bottom:1px solid #f0f0f0;
    text-align:center; font-size:.84rem;
}
#tblRevisores tbody tr, #tblMotivos tbody tr, #tblAcometida tbody tr { background:white; transition:all .2s; }
#tblRevisores tbody tr:hover, #tblMotivos tbody tr:hover, #tblAcometida tbody tr:hover {
    background:#f8f9ff; transform:scale(1.005); box-shadow:0 4px 12px rgba(102,126,234,0.1);
}
#tblRevisores tfoot th, #tblMotivos tfoot th {
    background:#f8f9fa; font-weight:700; padding:12px 8px;
    border-top:2px solid #667eea; color:#667eea; font-size:.85rem; text-align:center;
}

/* ── Barra de progreso ───────────────────────────────────────── */
.progress-cell { min-width:90px; }
.prog-bar { height:8px; border-radius:10px; background:#e2e8f0; overflow:hidden; margin-top:3px; }
.prog-bar-fill { height:100%; border-radius:10px; background:linear-gradient(90deg,#11998e,#38ef7d); transition:width .6s ease; }
.prog-bar-fill.bajo { background:linear-gradient(90deg,#fa709a,#fee140); }
.prog-label { font-size:.7rem; font-weight:700; color:#4a5568; }

/* ── Badges ──────────────────────────────────────────────────── */
.badge-pendiente { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; background:linear-gradient(135deg,#f093fb,#f5576c); color:white; text-transform:uppercase; }
.badge-ejecutado { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; background:linear-gradient(135deg,#11998e,#38ef7d); color:white; text-transform:uppercase; }
.badge-motivo    { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; background:linear-gradient(135deg,#667eea,#764ba2); color:white; text-transform:uppercase; }

/* ── Scrollbar ───────────────────────────────────────────────── */
.table-block::-webkit-scrollbar { height:8px; }
.table-block::-webkit-scrollbar-track { background:#f1f1f1; border-radius:10px; }
.table-block::-webkit-scrollbar-thumb { background:linear-gradient(135deg,#667eea,#764ba2); border-radius:10px; }

@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeIn   { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- ── Header ── --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-tachometer-alt"></i> Tablero de Control — Revisiones</h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('revisiones.index') }}"
                   class="btn" style="background:rgba(255,255,255,0.18);color:white;border:2px solid rgba(255,255,255,0.4);border-radius:10px;padding:8px 18px;font-weight:600;">
                    <i class="fa fa-list"></i> Ver Listado
                </a>
                <a href="{{ route('revisiones.criticas') }}"
                   class="btn" style="background:rgba(255,255,255,0.18);color:white;border:2px solid rgba(255,255,255,0.4);border-radius:10px;padding:8px 18px;font-weight:600;">
                    <i class="fa fa-exclamation-triangle"></i> Gestionar Críticas
                </a>
            </div>
        </div>
        <div class="card-body" style="padding:20px 28px;">

            {{-- ── Filtros ── --}}
            <div class="filtros-container">
                <form method="GET" action="{{ route('revisiones.tablero') }}">
                    <div class="row">
                        <div class="col-md-2 col-sm-6" style="margin-bottom:10px;">
                            <label style="font-weight:600;font-size:.78rem;color:#4a5568;text-transform:uppercase;">Motivo</label>
                            <select name="motivo" class="form-control">
                                <option value="">— Todos —</option>
                                <option value="DESVIACION_ALTA"  {{ request('motivo')=='DESVIACION_ALTA'  ? 'selected':'' }}>Desviación Alta</option>
                                <option value="DESVIACION_BAJA"  {{ request('motivo')=='DESVIACION_BAJA'  ? 'selected':'' }}>Desviación Baja</option>
                                <option value="OTRO"             {{ request('motivo')=='OTRO'             ? 'selected':'' }}>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6" style="margin-bottom:10px;">
                            <label style="font-weight:600;font-size:.78rem;color:#4a5568;text-transform:uppercase;">Revisor</label>
                            <select name="usuario_id" class="form-control">
                                <option value="">— Todos —</option>
                                @foreach($usuarios as $uid => $unom)
                                    <option value="{{ $uid }}" {{ request('usuario_id')==$uid ? 'selected':'' }}>{{ $unom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6" style="margin-bottom:10px;">
                            <label style="font-weight:600;font-size:.78rem;color:#4a5568;text-transform:uppercase;">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                        </div>
                        <div class="col-md-2 col-sm-6" style="margin-bottom:10px;">
                            <label style="font-weight:600;font-size:.78rem;color:#4a5568;text-transform:uppercase;">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                        </div>
                        <div class="col-md-2 col-sm-6" style="margin-bottom:10px;display:flex;align-items:flex-end;gap:6px;">
                            <button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;padding:10px 18px;font-weight:600;">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('revisiones.tablero') }}" class="btn btn-sm" style="background:#e2e8f0;color:#4a5568;border:none;border-radius:10px;padding:10px 14px;font-weight:600;">
                                <i class="fa fa-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ── KPI Cards ── --}}
            <div class="row mb-4">
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card primary">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number">{{ $total }}</div>
                        <div class="stat-label">Asignadas</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card success">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number">{{ $ejecutadas }}</div>
                        <div class="stat-label">Ejecutadas</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card warning">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-number">{{ $pendientes }}</div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card info">
                        <div class="stat-icon">📷</div>
                        <div class="stat-number">{{ $porUsuario->sum('total_fotos') }}</div>
                        <div class="stat-label">Fotos</div>
                    </div>
                </div>
                <div class="col-lg col-md-4 col-sm-6 mb-3">
                    <div class="stat-card purple">
                        <div class="stat-icon">💯</div>
                        <div class="stat-number">{{ $porcentaje }}%</div>
                        <div class="stat-label">Completado</div>
                    </div>
                </div>
            </div>

            {{-- ── Tablas: Revisores | Motivos ── --}}
            <div class="row">

                {{-- Por Revisor --}}
                <div class="col-lg-8 mb-3">
                    <div class="table-block">
                        <h5><i class="fa fa-users" style="color:#667eea;"></i> Resumen por Revisor</h5>
                        <div style="overflow-x:auto;">
                        <table id="tblRevisores" class="table table-hover" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Revisor</th>
                                    <th>Asignadas</th>
                                    <th>Ejecutadas</th>
                                    <th>Pendientes</th>
                                    <th>% Avance</th>
                                    <th>Fotos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($porUsuario as $row)
                                @php
                                    $pct = $row->total > 0 ? round(($row->ejecutadas / $row->total) * 100) : 0;
                                @endphp
                                <tr>
                                    <td style="text-align:left;font-weight:600;color:#2d3748;">
                                        <i class="fa fa-user-circle" style="color:#667eea;margin-right:5px;"></i>
                                        {{ $row->usuario ? $row->usuario->nombre : '(sin asignar)' }}
                                    </td>
                                    <td><strong>{{ $row->total }}</strong></td>
                                    <td><span class="badge-ejecutado">{{ $row->ejecutadas }}</span></td>
                                    <td>
                                        @if($row->pendientes > 0)
                                            <span class="badge-pendiente">{{ $row->pendientes }}</span>
                                        @else
                                            <span style="color:#48bb78;font-weight:700;">0</span>
                                        @endif
                                    </td>
                                    <td class="progress-cell">
                                        <span class="prog-label">{{ $pct }}%</span>
                                        <div class="prog-bar">
                                            <div class="prog-bar-fill {{ $pct < 50 ? 'bajo' : '' }}" style="width:{{ $pct }}%;"></div>
                                        </div>
                                    </td>
                                    <td>{{ $row->total_fotos ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" style="padding:30px;color:#a0aec0;text-align:center;"><i class="fa fa-inbox"></i> Sin datos</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align:left;">Totales</th>
                                    <th>{{ $total }}</th>
                                    <th>{{ $ejecutadas }}</th>
                                    <th>{{ $pendientes }}</th>
                                    <th>{{ $porcentaje }}%</th>
                                    <th>{{ $porUsuario->sum('total_fotos') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                </div>

                {{-- Por Motivo --}}
                <div class="col-lg-4 mb-3">
                    <div class="table-block">
                        <h5><i class="fa fa-tags" style="color:#764ba2;"></i> Por Motivo de Revisión</h5>
                        <table id="tblMotivos" class="table table-hover" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Motivo</th>
                                    <th>Total</th>
                                    <th>Ejec.</th>
                                    <th>Pend.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($porMotivo as $row)
                                <tr>
                                    <td style="text-align:left;">
                                        <span class="badge-motivo" style="font-size:.65rem;">
                                            {{ str_replace('_', ' ', $row->motivo_revision ?? 'SIN MOTIVO') }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $row->total }}</strong></td>
                                    <td><span style="color:#11998e;font-weight:700;">{{ $row->ejecutadas }}</span></td>
                                    <td><span style="color:{{ $row->pendientes > 0 ? '#e53e3e' : '#48bb78' }};font-weight:700;">{{ $row->pendientes }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" style="padding:20px;color:#a0aec0;text-align:center;">Sin datos</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align:left;">Total</th>
                                    <th>{{ $porMotivo->sum('total') }}</th>
                                    <th>{{ $porMotivo->sum('ejecutadas') }}</th>
                                    <th>{{ $porMotivo->sum('pendientes') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Estado acometida --}}
                    @if($porAcometida->count())
                    <div class="table-block" style="margin-top:0;">
                        <h5><i class="fa fa-wrench" style="color:#e53e3e;"></i> Estado Acometida (ejecutadas)</h5>
                        <table id="tblAcometida" class="table table-hover" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Estado</th>
                                    <th>Cant.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($porAcometida as $ac)
                                <tr>
                                    <td style="text-align:left;font-weight:600;">{{ $ac->estado_acometida ?? '—' }}</td>
                                    <td>{{ $ac->cnt }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

            </div>{{-- /row --}}

        </div>
    </div>

</div>
@endsection

@section('scriptsPlugins')
<script src="{{ asset("assets/$theme/plugins/datatables/jquery.dataTables.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js") }}" type="text/javascript"></script>
<script>
var idioma = {"sProcessing":"Procesando...","sLengthMenu":"Mostrar _MENU_ registros","sZeroRecords":"Sin resultados","sEmptyTable":"Sin datos","sInfo":"_START_-_END_ de _TOTAL_","sInfoEmpty":"0 registros","sInfoFiltered":"(de _MAX_)","sSearch":"Buscar:","sLoadingRecords":"Cargando...","oPaginate":{"sFirst":"Primero","sLast":"Último","sNext":"Sig.","sPrevious":"Ant."}};

$(document).ready(function() {
    $('#tblRevisores').DataTable({
        language: idioma, paging: false, searching: false, info: false, ordering: true, order: [[1,'desc']]
    });
    $('#tblMotivos').DataTable({
        language: idioma, paging: false, searching: false, info: false, ordering: true, order: [[1,'desc']]
    });
    @if($porAcometida->count())
    $('#tblAcometida').DataTable({
        language: idioma, paging: false, searching: false, info: false, ordering: true, order: [[1,'desc']]
    });
    @endif
});
</script>
@endsection
