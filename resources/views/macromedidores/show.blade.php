{{-- resources/views/macromedidores/show.blade.php --}}
@extends("theme.$theme.layout")

@section('titulo')
'Macromedidor: ' . $macro->codigo_macro
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.4rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
.modern-card .card-body { padding: 30px; background: #fafbfc; }
.info-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.info-table tr:hover { background: #f0f4ff; }
.info-table th { width: 40%; padding: 12px 16px; color: #4a5568; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #edf2f7; }
.info-table td { padding: 12px 16px; color: #2d3748; font-size: 0.95rem; border-bottom: 1px solid #edf2f7; }
.btn-accion { border-radius: 12px; padding: 10px 24px; font-weight: 600; font-size: 0.9rem; border: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; }
.btn-accion:hover { transform: translateY(-2px); }
.btn-volver { background: #e2e8f0; color: #4a5568; }
.btn-volver:hover { background: #cbd5e0; color: #2d3748; }
.btn-editar { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.btn-editar:hover { color: white; box-shadow: 0 4px 15px rgba(245,87,108,0.4); }
.btn-eliminar { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; }
.btn-eliminar:hover { color: white; box-shadow: 0 4px 15px rgba(235,51,73,0.4); }

/* Timeline */
.timeline { position: relative; padding-left: 40px; }
.timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); border-radius: 3px; }
.timeline-item { position: relative; margin-bottom: 30px; }
.timeline-item::before { content: ''; position: absolute; left: -32px; top: 12px; width: 14px; height: 14px; border-radius: 50%; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: 3px solid white; box-shadow: 0 0 0 3px #e2e8f0; }
.timeline-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 20px 24px; border-left: 4px solid #4facfe; transition: all 0.3s ease; }
.timeline-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.12); transform: translateX(4px); }
.timeline-date { font-size: 0.75rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
.timeline-lectura { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 10px; }
.lectura-chip { background: #f0f4ff; border-radius: 10px; padding: 6px 14px; font-size: 0.85rem; font-weight: 600; color: #4338ca; }
.lectura-chip.actual { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.consumo-chip { border-radius: 10px; padding: 6px 14px; font-size: 0.85rem; font-weight: 700; }
.consumo-pos { background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); color: #00695c; }
.consumo-neg { background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%); color: #c62828; }
.foto-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; cursor: pointer; transition: transform 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.foto-thumb:hover { transform: scale(1.08); }
.badge-usuario { display: inline-block; padding: 3px 10px; border-radius: 20px; background: linear-gradient(135deg,#667eea,#764ba2); color: white; font-size: 0.7rem; font-weight: 600; }
.map-container { border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); height: 320px; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:12px; border:none; box-shadow:0 4px 15px rgba(17,153,142,0.2);">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:12px; border:none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- CARD DATOS BASICOS --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-tachometer"></i> Macromedidor: {{ $macro->codigo_macro }}</h3>
            <span style="color:rgba(255,255,255,0.8); font-size:0.85rem;">
                <i class="fa fa-list-ol"></i> {{ $macro->lecturas->count() }} lectura(s)
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="info-table">
                        <tr>
                            <th><i class="fa fa-hashtag" style="color:#667eea;"></i> ID</th>
                            <td>{{ $macro->id }}</td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-barcode" style="color:#667eea;"></i> Código</th>
                            <td><strong style="color:#2e50e4;">{{ $macro->codigo_macro }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-map-marker" style="color:#667eea;"></i> Ubicación</th>
                            <td>{{ $macro->ubicacion ?: 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-user" style="color:#667eea;"></i> Usuario Asignado</th>
                            <td>{{ $macro->usuario ? $macro->usuario->nombre : 'Sin asignar' }}</td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-calendar" style="color:#667eea;"></i> Creado</th>
                            <td>{{ $macro->created_at ? $macro->created_at->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @php $ultima = $macro->lecturas->first(); @endphp
                    <table class="info-table">
                        <tr>
                            <th><i class="fa fa-dashboard" style="color:#11998e;"></i> Lectura Base</th>
                            <td><strong>{{ number_format($macro->lectura_anterior) }}</strong></td>
                        </tr>
                        @if($ultima)
                        <tr>
                            <th><i class="fa fa-tachometer" style="color:#11998e;"></i> Última Lectura</th>
                            <td><strong style="color:#11998e; font-size:1.1rem;">{{ number_format($ultima->lectura_actual) }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-line-chart" style="color:#11998e;"></i> Consumo Último</th>
                            <td>
                                @php $c = $ultima->consumo; @endphp
                                <strong style="color:{{ $c < 0 ? '#f5576c' : '#11998e' }}; font-size:1.05rem;">
                                    {{ $c >= 0 ? '+' : '' }}{{ number_format($c) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fa fa-clock-o" style="color:#11998e;"></i> Fecha Última</th>
                            <td>{{ $ultima->fecha_lectura ? $ultima->fecha_lectura->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MAPA LEAFLET (solo si la última lectura tiene GPS) --}}
    @if($ultima && $ultima->gps_latitud && $ultima->gps_longitud)
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-map-marker"></i> Ubicación GPS (última lectura)</h3>
            <span style="color:rgba(255,255,255,0.8); font-size:0.85rem;">
                {{ $ultima->gps_latitud }}, {{ $ultima->gps_longitud }}
            </span>
        </div>
        <div class="card-body" style="padding:0;">
            <div id="mapaMacro" class="map-container"></div>
        </div>
    </div>
    @endif

    {{-- TIMELINE DE LECTURAS --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-history"></i> Historial de Lecturas</h3>
        </div>
        <div class="card-body">
            @if($macro->lecturas->isEmpty())
                <div style="text-align:center; padding:40px; color:#a0aec0;">
                    <i class="fa fa-inbox" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                    No hay lecturas registradas. La lectura se captura desde la app Android.
                </div>
            @else
                <div class="timeline">
                    @foreach($macro->lecturas as $lectura)
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-date">
                                <i class="fa fa-calendar"></i>
                                {{ $lectura->fecha_lectura ? $lectura->fecha_lectura->format('d/m/Y H:i') : '-' }}
                                &nbsp;&nbsp;
                                <span class="badge-usuario">
                                    <i class="fa fa-user"></i>
                                    {{ $lectura->usuario ? $lectura->usuario->nombre : 'Desconocido' }}
                                </span>
                            </div>

                            <div class="timeline-lectura">
                                <div>
                                    <div style="font-size:0.7rem; color:#718096; margin-bottom:3px;">ANTERIOR</div>
                                    <span class="lectura-chip">{{ number_format($lectura->lectura_anterior) }}</span>
                                </div>
                                <i class="fa fa-arrow-right" style="color:#cbd5e0; font-size:1.2rem;"></i>
                                <div>
                                    <div style="font-size:0.7rem; color:#718096; margin-bottom:3px;">ACTUAL</div>
                                    <span class="lectura-chip actual">{{ number_format($lectura->lectura_actual) }}</span>
                                </div>
                                <div>
                                    <div style="font-size:0.7rem; color:#718096; margin-bottom:3px;">CONSUMO</div>
                                    @php $consumo = $lectura->consumo; @endphp
                                    <span class="consumo-chip {{ $consumo < 0 ? 'consumo-neg' : 'consumo-pos' }}">
                                        {{ $consumo >= 0 ? '+' : '' }}{{ number_format($consumo) }}
                                    </span>
                                </div>
                                @if($lectura->gps_latitud && $lectura->gps_longitud)
                                <a href="https://www.google.com/maps?q={{ $lectura->gps_latitud }},{{ $lectura->gps_longitud }}" target="_blank"
                                   style="display:inline-flex; align-items:center; gap:4px; padding:6px 12px; border-radius:10px; background:linear-gradient(135deg,#4facfe,#00f2fe); color:white; font-size:0.75rem; font-weight:600; text-decoration:none;">
                                    <i class="fa fa-map-marker"></i> GPS
                                </a>
                                @endif
                            </div>

                            @if($lectura->observacion)
                                <div style="margin-top:8px; padding:8px 12px; background:#f7f8fa; border-radius:8px; font-size:0.85rem; color:#4a5568;">
                                    <i class="fa fa-comment" style="color:#667eea;"></i> {{ $lectura->observacion }}
                                </div>
                            @endif

                            @if($lectura->fotos->count() > 0)
                                <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                                    @foreach($lectura->fotos as $foto)
                                        <a href="{{ asset($foto->ruta_foto) }}" target="_blank">
                                            <img src="{{ asset($foto->ruta_foto) }}" class="foto-thumb" alt="Foto lectura">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ACCIONES --}}
    <div style="margin-bottom:30px; animation: fadeIn 0.6s ease-out; display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('macromedidores.index') }}" class="btn-accion btn-volver">
            <i class="fa fa-arrow-left"></i> Volver al listado
        </a>
        <a href="{{ route('macromedidores.edit', $macro->id) }}" class="btn-accion btn-editar">
            <i class="fa fa-pencil"></i> Editar
        </a>
        @if($macro->lecturas->isEmpty())
            <form action="{{ route('macromedidores.destroy', $macro->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('¿Eliminar este macromedidor? Esta acción no se puede deshacer.')">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="btn-accion btn-eliminar">
                    <i class="fa fa-trash"></i> Eliminar
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('scriptsPlugins')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/7+0=" crossorigin=""></script>
@if(isset($ultima) && $ultima && $ultima->gps_latitud && $ultima->gps_longitud)
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ $ultima->gps_latitud }};
    var lng = {{ $ultima->gps_longitud }};
    var map = L.map('mapaMacro').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    L.marker([lat, lng])
        .addTo(map)
        .bindPopup('<strong>{{ $macro->codigo_macro }}</strong><br>{{ addslashes($macro->ubicacion) }}<br>Última lectura: {{ $ultima ? number_format($ultima->lectura_actual) : "" }}')
        .openPopup();
});
</script>
@endif
@endsection
