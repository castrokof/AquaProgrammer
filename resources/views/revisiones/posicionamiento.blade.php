@extends("theme.$theme.layout")

@section('titulo', 'Posicionamiento GPS — Revisiones')

@section('styles')
<link href="{{ asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css") }}" rel="stylesheet"/>
<link href="{{ asset('assets/css/Control.FullScreen.css') }}" rel="stylesheet"/>
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"/>
<link href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" rel="stylesheet"/>
<link href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" rel="stylesheet"/>

<style>
    .map-container { border-radius:8px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
    #map { height:550px; width:100%; z-index:1; }

    .stats-card { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border-radius:10px; padding:15px; margin-bottom:15px; transition:transform .3s; }
    .stats-card:hover { transform:translateY(-3px); }
    .stats-card h3 { margin:0; font-size:1.8rem; }
    .stats-card p  { margin:0; opacity:.9; font-size:.9rem; }
    .stats-card.green  { background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%); }
    .stats-card.orange { background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%); }
    .stats-card.blue   { background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%); }

    .filter-card { border-radius:10px; border:none; box-shadow:0 2px 10px rgba(0,0,0,0.08); }

    .btn-search { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border:none; color:white; padding:10px 25px; border-radius:5px; }
    .btn-search:hover { background:linear-gradient(135deg,#764ba2 0%,#667eea 100%); color:white; }
    .btn-reset  { background:#6c757d; border:none; color:white; padding:10px 25px; border-radius:5px; }
    .btn-reset:hover { background:#5a6268; color:white; }

    .loading-overlay { position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.9); display:flex; justify-content:center; align-items:center; z-index:1000; border-radius:8px; }
    .loading-overlay.d-none { display:none !important; }
    .spinner { width:50px; height:50px; border:5px solid #f3f3f3; border-top:5px solid #667eea; border-radius:50%; animation:spin 1s linear infinite; }
    @keyframes spin { 0%{transform:rotate(0deg);} 100%{transform:rotate(360deg);} }

    /* Popup */
    .custom-popup .leaflet-popup-content-wrapper { border-radius:10px; padding:0; overflow:hidden; }
    .custom-popup .leaflet-popup-content { margin:0; min-width:310px; }
    .popup-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:10px 15px; }
    .popup-header h6 { margin:0; font-weight:600; }
    .popup-body { padding:15px; }
    .popup-body p { margin:5px 0; font-size:13px; }
    .popup-body strong { color:#667eea; }
    .popup-photo { width:100%; max-height:200px; object-fit:cover; cursor:pointer; border-radius:5px; margin-top:10px; transition:opacity .3s; border:2px solid #eee; }
    .popup-photo:hover { opacity:.9; }
    .popup-order { display:inline-block; background:rgba(255,255,255,0.3); color:white; padding:2px 8px; border-radius:10px; font-size:11px; margin-left:5px; }
    .popup-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; }
    .popup-badge.motivo  { background:#764ba2; color:white; }
    .popup-badge.acometida { background:#11998e; color:white; }

    /* Modal foto */
    .modal-photo { max-width:100%; max-height:80vh; object-fit:contain; }

    /* Controles mapa */
    .map-controls { display:flex; gap:5px; flex-wrap:wrap; }
    .map-controls .btn { padding:5px 12px; font-size:13px; }
    .btn-active { background:#667eea !important; color:white !important; }

    /* Timeline */
    .route-timeline { max-height:450px; overflow-y:auto; padding:10px; }
    .timeline-item { display:flex; padding:10px; border-left:3px solid #667eea; margin-left:10px; margin-bottom:5px; background:#f8f9fa; border-radius:0 8px 8px 0; cursor:pointer; transition:all .3s; }
    .timeline-item:hover { background:#e9ecef; transform:translateX(5px); }
    .timeline-item.active { background:#667eea; color:white; }
    .timeline-item.start  { border-left-color:#28a745; }
    .timeline-item.end    { border-left-color:#dc3545; }
    .timeline-number { background:#667eea; color:white; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; margin-right:10px; flex-shrink:0; }
    .timeline-item.start .timeline-number { background:#28a745; }
    .timeline-item.end   .timeline-number { background:#dc3545; }
    .timeline-item.active .timeline-number { background:white; color:#667eea; }
    .timeline-info { flex:1; font-size:12px; overflow:hidden; }
    .timeline-info strong { display:block; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .timeline-info small { opacity:.8; }
    .timeline-item.active .timeline-info small { opacity:1; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- ── Estadísticas ── --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <h3 id="total-markers">0</h3>
                <p><i class="fas fa-map-marker-alt"></i> Revisiones en mapa</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card green">
                <h3 id="distancia-total">0 km</h3>
                <p><i class="fas fa-route"></i> Distancia recorrida</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card orange">
                <h3 id="tiempo-total">00:00</h3>
                <p><i class="fas fa-clock"></i> Tiempo de recorrido</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card blue">
                <h3 id="promedio-revision">0</h3>
                <p><i class="fas fa-tachometer-alt"></i> Revisiones/hora</p>
            </div>
        </div>
    </div>

    @include('includes.form-error')
    @include('includes.form-mensaje')

    {{-- ── Filtros ── --}}
    <div class="card filter-card mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0"><i class="fas fa-filter text-primary"></i> Filtros de búsqueda</h6>
        </div>
        <div class="card-body py-3">
            <div class="form-group row">
                {{-- Periodo --}}
                <div class="col-md-4">
                    <label class="control-label font-weight-bold">Periodo</label>
                    <select id="selPeriodo" class="form-control">
                        <option value="">— Seleccione un periodo —</option>
                        @foreach($periodos as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->nombre }}
                                @if($p->fecha_inicio_lectura)
                                    ({{ \Carbon\Carbon::parse($p->fecha_inicio_lectura)->format('M Y') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Revisor --}}
                <div class="col-md-3">
                    <label class="control-label font-weight-bold">Revisor</label>
                    <select id="selRevisor" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($usuarios as $uid => $unom)
                            <option value="{{ $uid }}">{{ $unom }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Botones --}}
                <div class="col-md-3 d-flex align-items-end" style="gap:8px;">
                    <button type="button" id="btn-buscar" class="btn btn-search">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-reset">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ── Mapa ── --}}
        <div class="col-lg-9 col-md-8">
            <div class="card filter-card">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-map text-primary"></i> Mapa de Seguimiento</h6>
                    <div class="map-controls">
                        <button type="button" id="btn-route"    class="btn btn-sm btn-outline-primary btn-active" title="Mostrar/Ocultar ruta"><i class="fas fa-route"></i> Ruta</button>
                        <button type="button" id="btn-markers"  class="btn btn-sm btn-outline-primary btn-active" title="Mostrar/Ocultar marcadores"><i class="fas fa-map-marker-alt"></i> Puntos</button>
                        <button type="button" id="btn-satellite" class="btn btn-sm btn-outline-secondary" title="Vista satélite"><i class="fas fa-satellite"></i></button>
                        <button type="button" id="btn-center"   class="btn btn-sm btn-outline-secondary" title="Centrar mapa"><i class="fas fa-crosshairs"></i></button>
                    </div>
                </div>
                <div class="card-body p-0 position-relative">
                    <div id="loading-map" class="loading-overlay d-none">
                        <div class="text-center">
                            <div class="spinner mb-3"></div>
                            <p class="text-muted">Cargando datos del recorrido...</p>
                        </div>
                    </div>
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Timeline lateral ── --}}
        <div class="col-lg-3 col-md-4">
            <div class="card filter-card">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-list-ol text-primary"></i> Recorrido</h6>
                    <small class="text-muted" id="timeline-count"></small>
                </div>
                <div class="card-body p-0">
                    <div id="route-timeline" class="route-timeline">
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-info-circle"></i> Selecciona un periodo y presiona Buscar
                        </p>
                    </div>
                </div>
            </div>

            {{-- Leyenda --}}
            <div class="card filter-card mt-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Leyenda</h6>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:15px;height:15px;background:#28a745;border-radius:50%;margin-right:10px;"></div>
                        <span style="font-size:13px;">Inicio del recorrido</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:15px;height:15px;background:#dc3545;border-radius:50%;margin-right:10px;"></div>
                        <span style="font-size:13px;">Fin del recorrido</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:15px;height:15px;background:#667eea;border-radius:50%;margin-right:10px;"></div>
                        <span style="font-size:13px;">Punto de revisión</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width:25px;height:4px;background:linear-gradient(90deg,#667eea,#764ba2);margin-right:10px;border-radius:2px;"></div>
                        <span style="font-size:13px;">Línea de recorrido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal foto ampliada --}}
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Foto de Revisión</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-2" style="background:#f5f5f5;">
                <img id="modalPhoto" src="" class="modal-photo" alt="Foto de revisión">
            </div>
            <div class="modal-footer">
                <div id="photoInfo" class="text-left flex-grow-1"></div>
                <a id="downloadPhoto" href="" download class="btn btn-primary">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scriptsPlugins')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset("assets/js/Control.FullScreen.js") }}"></script>

<script>
$(document).ready(function () {

    var BASE_URL = "{{ url('/') }}/";

    // ── Capas del mapa ─────────────────────────────────────────────────────
    var streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20, attribution: '&copy; OpenStreetMap'
    });
    var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 20, attribution: '&copy; Esri'
    });

    var map = L.map('map', {
        layers: [streets],
        center: [3.125903, -76.5971593],
        zoom: 12,
        fullscreenControl: true,
        fullscreenControlOptions: { title: "Pantalla completa", titleCancel: "Salir" }
    });

    var markersLayer = L.layerGroup().addTo(map);
    var routeLine    = null;
    var currentLayer = 'streets';
    var showRoute    = true;
    var showMarkers  = true;
    var allData      = [];
    var markersArray = [];

    // ── Iconos ──────────────────────────────────────────────────────────────
    function createIcon(color, number) {
        return L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background:' + color + ';width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:11px;">' + number + '</div>',
            iconSize: [28, 28], iconAnchor: [14, 14], popupAnchor: [0, -14]
        });
    }
    function createStartIcon() {
        return L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background:#28a745;width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fas fa-play"></i></div>',
            iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
        });
    }
    function createEndIcon() {
        return L.divIcon({
            className: 'custom-div-icon',
            html: '<div style="background:#dc3545;width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:14px;"><i class="fas fa-flag-checkered"></i></div>',
            iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────────
    function showLoading(show) {
        show ? $('#loading-map').removeClass('d-none') : $('#loading-map').addClass('d-none');
    }

    function formatDateTime(d) {
        if (!d) return 'No disponible';
        var dt = new Date(d);
        return dt.toLocaleDateString('es-CO') + ' ' + dt.toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit'});
    }

    function formatTime(d) {
        if (!d) return '--:--';
        var dt = new Date(d);
        return dt.toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit'});
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        var R = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2) +
                Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*
                Math.sin(dLon/2)*Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function formatDuration(minutes) {
        var h = Math.floor(minutes / 60), m = Math.floor(minutes % 60);
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    // ── Estadísticas ────────────────────────────────────────────────────────
    function updateStats(data) {
        $('#total-markers').text(data.length);
        $('#timeline-count').text(data.length + ' puntos');

        if (data.length > 1) {
            var dist = 0;
            for (var i = 1; i < data.length; i++) {
                dist += calcularDistancia(
                    parseFloat(data[i-1].gps_latitud_predio), parseFloat(data[i-1].gps_longitud_predio),
                    parseFloat(data[i].gps_latitud_predio),   parseFloat(data[i].gps_longitud_predio)
                );
            }
            $('#distancia-total').text(dist.toFixed(2) + ' km');

            if (data[0].fecha_ejecucion && data[data.length-1].fecha_ejecucion) {
                var inicio = new Date(data[0].fecha_ejecucion);
                var fin    = new Date(data[data.length-1].fecha_ejecucion);
                var diffMin = (fin - inicio) / 1000 / 60;
                if (diffMin > 0) {
                    $('#tiempo-total').text(formatDuration(diffMin));
                    $('#promedio-revision').text(Math.round(data.length / (diffMin / 60)));
                } else {
                    $('#tiempo-total').text('00:00');
                    $('#promedio-revision').text('0');
                }
            }
        } else {
            $('#distancia-total').text('0 km');
            $('#tiempo-total').text('00:00');
            $('#promedio-revision').text('0');
        }
    }

    // ── Timeline ────────────────────────────────────────────────────────────
    function buildTimeline(data) {
        if (data.length === 0) {
            $('#route-timeline').html('<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> Sin resultados</p>');
            return;
        }
        var html = '';
        data.forEach(function (item, i) {
            var extraClass = i === 0 ? 'start' : (i === data.length - 1 ? 'end' : '');
            var motivo = item.motivo_revision ? item.motivo_revision.replace('_', ' ') : '';
            html += '<div class="timeline-item ' + extraClass + '" data-index="' + i + '">' +
                '<div class="timeline-number">' + (i + 1) + '</div>' +
                '<div class="timeline-info">' +
                    '<strong>' + (item.nombre_suscriptor || 'N/A') + '</strong>' +
                    '<small><i class="fas fa-tag"></i> ' + motivo + '</small><br>' +
                    '<small><i class="fas fa-clock"></i> ' + formatTime(item.fecha_ejecucion) + '</small>' +
                '</div>' +
            '</div>';
        });
        $('#route-timeline').html(html);
    }

    // ── Popup ────────────────────────────────────────────────────────────────
    function createPopupContent(d, index, total) {
        var fecha     = formatDateTime(d.fecha_ejecucion);
        var motivo    = d.motivo_revision ? d.motivo_revision.replace(/_/g, ' ') : 'N/A';
        var acometida = d.estado_acometida || null;
        var photoHtml = '';

        if (d.foto) {
            var photoUrl = BASE_URL + d.foto;
            photoHtml = '<img src="' + photoUrl + '" class="popup-photo" ' +
                'onclick="openPhotoModal(\'' + photoUrl + '\', \'' + (d.codigo_predio || '') + '\', \'' + (d.nombre_suscriptor || '').replace(/'/g, '') + '\', \'' + fecha + '\')" ' +
                'onerror="this.style.display=\'none\'" alt="Foto">';
        }

        var acometidaHtml = acometida
            ? '<p><strong><i class="fas fa-wrench"></i> Acometida:</strong> <span class="popup-badge acometida">' + acometida + '</span></p>'
            : '';

        return '<div class="popup-header">' +
                '<h6><i class="fas fa-user"></i> ' + (d.nombre_suscriptor || 'N/A') +
                    ' <span class="popup-order">#' + (index + 1) + ' de ' + total + '</span></h6>' +
            '</div>' +
            '<div class="popup-body">' +
                '<p><strong><i class="fas fa-id-card"></i> Código Predio:</strong> ' + (d.codigo_predio || 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> ' + (d.direccion || 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-tag"></i> Motivo:</strong> <span class="popup-badge motivo">' + motivo + '</span></p>' +
                acometidaHtml +
                '<p><strong><i class="fas fa-tachometer-alt"></i> Medidor:</strong> ' + (d.ref_medidor || 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-digital-tachograph"></i> Lect. Actual:</strong> ' + (d.lectura_actual !== null ? d.lectura_actual : 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-history"></i> Lect. Anterior:</strong> ' + (d.lectura_anterior !== null ? d.lectura_anterior : 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-calculator"></i> Consumo:</strong> ' + (d.consumo_actual !== null ? d.consumo_actual : 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-user-tie"></i> Revisor:</strong> ' + (d.revisor || 'N/A') + '</p>' +
                '<p><strong><i class="fas fa-clock"></i> Fecha Ejec.:</strong> ' + fecha + '</p>' +
                photoHtml +
            '</div>';
    }

    // ── Carga de datos (AJAX) ────────────────────────────────────────────────
    function loadData(periodoId, usuarioId) {
        showLoading(true);
        markersLayer.clearLayers();
        markersArray = [];
        if (routeLine) { map.removeLayer(routeLine); routeLine = null; }
        allData = [];

        $.ajax({
            url: '{{ route("revisiones.posicionamiento") }}',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: { periodo_id: periodoId, usuario_id: usuarioId },
            success: function (data) {
                // Filtrar coords válidas
                data = data.filter(function (r) {
                    return r.gps_latitud_predio && r.gps_longitud_predio &&
                           parseFloat(r.gps_latitud_predio) !== 0 &&
                           parseFloat(r.gps_longitud_predio) !== 0;
                });

                allData = data;
                var routeCoords = [], bounds = [];

                data.forEach(function (d, i) {
                    var lat = parseFloat(d.gps_latitud_predio);
                    var lng = parseFloat(d.gps_longitud_predio);

                    var icon = i === 0 ? createStartIcon() :
                               i === data.length - 1 ? createEndIcon() :
                               createIcon('#667eea', i + 1);

                    var marker = L.marker([lat, lng], { icon: icon })
                        .bindPopup(createPopupContent(d, i, data.length), {
                            className: 'custom-popup', maxWidth: 360
                        });

                    markersLayer.addLayer(marker);
                    markersArray.push(marker);
                    routeCoords.push([lat, lng]);
                    bounds.push([lat, lng]);
                });

                if (routeCoords.length > 1) {
                    routeLine = L.polyline(routeCoords, {
                        color: '#667eea', weight: 4, opacity: 0.8,
                        dashArray: '10, 10', lineJoin: 'round'
                    }).addTo(map);
                }

                if (bounds.length > 0) map.fitBounds(bounds, { padding: [50, 50] });

                updateStats(data);
                buildTimeline(data);
                showLoading(false);
            },
            error: function () {
                showLoading(false);
                Swal.fire({ title: 'Error', text: 'No se pudieron cargar los datos', icon: 'error' });
            }
        });
    }

    // ── Eventos filtros ──────────────────────────────────────────────────────
    $('#btn-buscar').click(function () {
        var pId = $('#selPeriodo').val();
        if (!pId) {
            Swal.fire({ title: 'Atención', text: 'Selecciona un periodo', icon: 'warning', confirmButtonColor: '#667eea' });
            return;
        }
        loadData(pId, $('#selRevisor').val());
    });

    $('#btn-reset').click(function () {
        $('#selPeriodo').val('');
        $('#selRevisor').val('');
        markersLayer.clearLayers();
        markersArray = [];
        if (routeLine) { map.removeLayer(routeLine); routeLine = null; }
        allData = [];
        $('#total-markers').text('0');
        $('#distancia-total').text('0 km');
        $('#tiempo-total').text('00:00');
        $('#promedio-revision').text('0');
        $('#timeline-count').text('');
        $('#route-timeline').html('<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> Selecciona un periodo y presiona Buscar</p>');
        map.setView([3.125903, -76.5971593], 12);
    });

    // ── Controles del mapa ───────────────────────────────────────────────────
    $('#btn-route').click(function () {
        showRoute = !showRoute;
        $(this).toggleClass('btn-active', showRoute);
        if (routeLine) { showRoute ? map.addLayer(routeLine) : map.removeLayer(routeLine); }
    });

    $('#btn-markers').click(function () {
        showMarkers = !showMarkers;
        $(this).toggleClass('btn-active', showMarkers);
        showMarkers ? map.addLayer(markersLayer) : map.removeLayer(markersLayer);
    });

    $('#btn-satellite').click(function () {
        if (currentLayer === 'streets') {
            map.removeLayer(streets); map.addLayer(satellite);
            currentLayer = 'satellite'; $(this).addClass('btn-active');
        } else {
            map.removeLayer(satellite); map.addLayer(streets);
            currentLayer = 'streets'; $(this).removeClass('btn-active');
        }
    });

    $('#btn-center').click(function () {
        if (allData.length > 0) {
            map.fitBounds(allData.map(function (d) {
                return [parseFloat(d.gps_latitud_predio), parseFloat(d.gps_longitud_predio)];
            }), { padding: [50, 50] });
        } else {
            map.setView([3.125903, -76.5971593], 12);
        }
    });

    // ── Click en timeline ────────────────────────────────────────────────────
    $(document).on('click', '.timeline-item', function () {
        var idx = $(this).data('index');
        $('.timeline-item').removeClass('active');
        $(this).addClass('active');
        if (markersArray[idx]) {
            var ll = markersArray[idx].getLatLng();
            map.setView([ll.lat, ll.lng], 18);
            markersArray[idx].openPopup();
        }
    });
});

// Función global modal foto
function openPhotoModal(photoUrl, codigo, nombre, fecha) {
    $('#modalPhoto').attr('src', photoUrl);
    $('#photoModalTitle').text('Foto — ' + nombre);
    $('#downloadPhoto').attr('href', photoUrl);
    $('#photoInfo').html('<p class="mb-1"><strong>Predio:</strong> ' + codigo + '</p>' +
                         '<p class="mb-0"><strong>Fecha:</strong> ' + fecha + '</p>');
    $('#photoModal').modal('show');
}
</script>
@endsection
