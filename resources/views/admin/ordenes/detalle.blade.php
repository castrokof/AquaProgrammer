<!DOCTYPE html>
<html lang="es">
<head>
  <title>Img System App</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css">
  
  <!-- Magnify -->
  <link rel="stylesheet" href="{{asset("assets/css/jquery.magnify.css")}}">
  <link rel="stylesheet" href="{{asset("assets/css/jquery.magnify.min.css")}}">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 25%, #4facfe 50%, #00c6ff 75%, #0093e9 100%);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
      padding: 20px;
      color: #1a1a2e;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container-fluid {
      max-width: 1400px;
      margin: 0 auto;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      padding: 30px;
      margin-bottom: 20px;
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .header-card {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      animation-delay: 0.1s;
    }

    .title-main {
      font-size: 28px;
      font-weight: 700;
      color: #ffffff;
      text-align: center;
      margin: 0;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      letter-spacing: -0.5px;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      margin-top: 20px;
    }

    @media (min-width: 768px) {
      .content-grid {
        grid-template-columns: 1fr 2fr;
      }
    }

    .section-title {
      font-size: 18px;
      font-weight: 600;
      color: #ffffff;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section-title i {
      font-size: 20px;
    }

    .photo-container {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }

    .photo-container:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
    }

    .photo-container img {
      width: 100%;
      height: auto;
      display: block;
      transition: transform 0.3s ease;
    }

    .photo-container:hover img {
      transform: scale(1.05);
    }

    .data-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
    }

    .data-table tbody tr {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .data-table tbody tr:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateX(5px);
    }

    .data-table th {
      font-weight: 600;
      color: #ffffff;
      padding: 12px 16px;
      text-align: left;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-top-left-radius: 12px;
      border-bottom-left-radius: 12px;
    }

    .data-table td {
      color: #ffffff;
      padding: 12px 16px;
      font-size: 14px;
      border-top-right-radius: 12px;
      border-bottom-right-radius: 12px;
    }

    .section-header {
      background: linear-gradient(135deg, rgba(79, 172, 254, 0.3) 0%, rgba(0, 242, 254, 0.3) 100%);
      padding: 16px 20px;
      border-radius: 16px;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .progress-container {
      background: rgba(0, 0, 0, 0.2);
      border-radius: 20px;
      height: 36px;
      overflow: hidden;
      position: relative;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .progress-bar {
      height: 100%;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      color: #ffffff;
      position: relative;
      overflow: hidden;
      animation: progressSlide 2s ease-out;
    }

    @keyframes progressSlide {
      from { width: 0; }
    }

    .progress-bar::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% { left: -100%; }
      100% { left: 100%; }
    }

    .progress-danger {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .progress-warning {
      background: linear-gradient(135deg, #ffd89b 0%, #ff6b6b 100%);
    }

    .map-container {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      height: 350px;
      animation: fadeInUp 0.6s ease-out 0.4s both;
    }

    #map {
      height: 100%;
      width: 100%;
    }

    .info-badge {
      display: inline-block;
      padding: 6px 14px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      color: #ffffff;
      margin-right: 8px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .glass-card-secondary {
      animation-delay: 0.2s;
    }

    .glass-card-tertiary {
      animation-delay: 0.3s;
    }

    .two-column-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }

    @media (min-width: 992px) {
      .two-column-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    /* Magnify Modal Styles */
    .magnify-modal {
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.5);
      border-radius: 12px;
    }

    .magnify-header .magnify-toolbar {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
      backdrop-filter: blur(20px);
    }

    .magnify-footer .magnify-toolbar {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
      backdrop-filter: blur(20px);
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        padding: 10px;
      }

      .glass-card {
        padding: 20px;
      }

      .title-main {
        font-size: 22px;
      }

      .section-title {
        font-size: 16px;
      }

      .data-table th,
      .data-table td {
        padding: 10px 12px;
        font-size: 12px;
      }
    }

    /* Loading animation */
    .loading-shimmer {
      background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* Custom Leaflet Popup Styles */
    .leaflet-popup-content-wrapper {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      padding: 0;
      overflow: hidden;
    }

    .leaflet-popup-content {
      margin: 0;
      padding: 0;
    }

    .leaflet-popup-tip {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }

    .custom-popup .leaflet-popup-close-button {
      color: #667eea;
      font-size: 24px;
      font-weight: bold;
      padding: 8px 12px;
    }

    .custom-popup .leaflet-popup-close-button:hover {
      color: #764ba2;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    @foreach($datas as $poli)
    <div class="glass-card header-card">
      <h1 class="title-main">
        <i class="fas fa-user-circle"></i> DETALLE DE SUSCRIPTOR {{($poli->Suscriptor)}}
      </h1>
    </div>
    @endforeach

    @foreach($datas as $img)
    <div class="content-grid">
      <!-- Columna Izquierda: Foto del Medidor -->
      <div>
        <div class="glass-card glass-card-secondary">
          <div class="section-header">
            <div class="section-title">
              <i class="fas fa-camera"></i>
              FOTO MEDIDOR
            </div>
          </div>
          
          @if(!empty($img->foto1) || !empty($img->foto2))
          <div class="photo-container">
            <a data-magnify="gallery" href="{{asset('/tmp/'.$img->foto1)}}">
              <img src="{{asset('/tmp/'.$img->foto1)}}?auto=compress&cs=tinysrgb&dpr=2&h=650&w=480" alt="Foto Medidor" class="zoom img-fluid">
            </a>
          </div>
          @endif
        </div>
      </div>

      <!-- Columna Derecha: Datos Generales -->
      <div>
        <div class="glass-card glass-card-secondary">
          <div class="section-header">
            <div class="section-title">
              <i class="fas fa-info-circle"></i>
              DATOS GENERALES
            </div>
          </div>

          <div class="two-column-grid">
            <!-- Datos del Suscriptor -->
            <div>
              <div style="margin-bottom: 20px;">
                <span class="info-badge">
                  <i class="fas fa-user"></i> SUSCRIPTOR: {{($img->Suscriptor)}}
                </span>
              </div>
              
              <table class="data-table">
                <tbody>
                  <tr>
                    <th><i class="fas fa-id-card"></i> Nombre:</th>
                    <td>{{($img->Nombre)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-map-marker-alt"></i> Dirección:</th>
                    <td>{{($img->Direccion)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-route"></i> Recorrido:</th>
                    <td>{{($img->recorrido)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-tachometer-alt"></i> Medidor:</th>
                    <td>{{($img->Ref_Medidor)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-map"></i> Zona:</th>
                    <td>{{($img->Ciclo)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-user-tie"></i> Funcionario:</th>
                    <td>{{($img->nombreu)}} - {{($img->Usuario)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-calendar-check"></i> Fecha:</th>
                    <td>{{($img->fecha_de_ejecucion)}}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Detalle de Lectura -->
            <div>
              <div style="margin-bottom: 20px;">
                <span class="info-badge">
                  <i class="fas fa-chart-line"></i> DETALLE DE LECTURA
                </span>
              </div>
              
              <table class="data-table">
                <tbody>
                  <tr>
                    <th><i class="fas fa-eye"></i> Lectura:</th>
                    <td>{{($img->Lect_Actual)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-history"></i> Anterior:</th>
                    <td>{{($img->LA)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-water"></i> Consumo:</th>
                    <td>
                      <div class="progress-container">
                        <div class="progress-bar progress-danger">
                          {{($img->Cons_Act)}} m³
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-chart-bar"></i> Promedio:</th>
                    <td>
                      <div class="progress-container">
                        <div class="progress-bar progress-warning">
                          {{($img->Promedio)}} m³
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-exclamation-triangle"></i> Crítica:</th>
                    <td>{{($img->Critica)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-sync-alt"></i> Nuevo:</th>
                    <td>{{($img->new_medidor)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-clipboard-list"></i> Causa:</th>
                    <td colspan="3">{{($img->Causa_des)}}</td>
                  </tr>
                  <tr>
                    <th><i class="fas fa-comment-dots"></i> Observación:</th>
                    <td colspan="3">{{($img->Observacion_des)}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mapa GPS -->
    <div class="glass-card glass-card-tertiary">
      <div class="section-header">
        <div class="section-title">
          <i class="fas fa-map-marked-alt"></i>
          POSICIONAMIENTO GPS
        </div>
      </div>
      
      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>

    <!-- Scripts de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
    <script>
      var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          osm = L.tileLayer(osmUrl, {maxZoom: 18, attribution: osmAttrib});
      
      var map = L.map('map').setView([{{$img->Latitud}}, {{$img->Longitud}}], 17).addLayer(osm);
      
      // Crear el popup con la foto
      var popupContent = `
        <div style="min-width: 250px; font-family: 'Inter', sans-serif;">
          @if(!empty($img->foto1))
          <div style="margin-bottom: 12px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <img src="{{asset('/tmp/'.$img->foto1)}}?auto=compress&cs=tinysrgb&dpr=1&h=200&w=250" 
                 alt="Foto Medidor" 
                 style="width: 100%; height: auto; display: block; cursor: pointer;"
                 onclick="window.open('{{asset('/tmp/'.$img->foto1)}}', '_blank')">
          </div>
          @endif
          <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 12px; border-radius: 8px; color: white; margin-bottom: 8px;">
            <strong style="font-size: 16px;">📍 Suscriptor: {{$img->Suscriptor}}</strong>
          </div>
          <div style="padding: 8px; background: rgba(102, 126, 234, 0.1); border-radius: 6px; margin-bottom: 6px;">
            <strong style="color: #667eea;">👤 Nombre:</strong> {{$img->Nombre}}
          </div>
          <div style="padding: 8px; background: rgba(118, 75, 162, 0.1); border-radius: 6px; margin-bottom: 6px;">
            <strong style="color: #764ba2;">📍 Dirección:</strong> {{$img->Direccion}}
          </div>
          <div style="padding: 8px; background: rgba(79, 172, 254, 0.1); border-radius: 6px; margin-bottom: 6px;">
            <strong style="color: #4facfe;">💧 Lectura:</strong> {{$img->Lect_Actual}} m³
          </div>
          <div style="padding: 8px; background: rgba(240, 147, 251, 0.1); border-radius: 6px;">
            <strong style="color: #f093fb;">📅 Fecha:</strong> {{$img->fecha_de_ejecucion}}
          </div>
          @if(!empty($img->foto1))
          <div style="margin-top: 10px; text-align: center; font-size: 11px; color: #666;">
            <em>💡 Haz clic en la imagen para verla en tamaño completo</em>
          </div>
          @endif
        </div>
      `;
      
      L.marker([{{$img->Latitud}}, {{$img->Longitud}}])
        .addTo(map)
        .bindPopup(popupContent, {
          maxWidth: 300,
          className: 'custom-popup'
        })
        .openPopup();
    </script>
    @endforeach
  </div>

  <!-- jQuery -->
  <script src="{{asset("assets/$theme/plugins/jquery/jquery.min.js")}}"></script>
  
  <!-- Magnify -->
  <script src="{{asset("assets/js/jquery.magnify.js")}}"></script>
  <script src="{{asset("assets/js/jquery.magnify.min.js")}}"></script>
  
  <script>
    $('[data-magnify]').magnify({
      headToolbar: ['close'],
      footToolbar: ['zoomIn', 'zoomOut', 'prev', 'fullscreen', 'next', 'actualSize', 'rotateRight'],
      title: false
    });
  </script>
</body>
</html>