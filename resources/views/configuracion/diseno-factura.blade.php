@extends("theme.$theme.layout")

@section('titulo', 'Diseño de Factura')

@section('styles')
<style>
.cfg-card { border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,.09); border:none; overflow:hidden; background:white; margin-bottom:20px; }
.cfg-card .card-header { background:linear-gradient(135deg,#2e50e4,#2b0c49); padding:18px 24px; }
.cfg-card .card-header h4 { color:white; font-weight:700; margin:0; font-size:1.1rem; }
.form-lbl { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }
.form-inp { border-radius:10px; border:2px solid #e2e8f0; padding:9px 13px; font-size:.9rem; transition:border-color .2s; }
.form-inp:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
.section-sep { border:none; border-top:2px dashed #e2e8f0; margin:20px 0; }

/* Toggle switch */
.toggle-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; }
.toggle-row:last-child { border-bottom:none; }
.toggle-label { font-size:.88rem; color:#374151; font-weight:500; }
.toggle-desc { font-size:.75rem; color:#9ca3af; margin-top:1px; }
.switch { position:relative; display:inline-block; width:46px; height:24px; flex-shrink:0; }
.switch input { opacity:0; width:0; height:0; }
.slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#d1d5db; transition:.3s; border-radius:24px; }
.slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px;
    background:white; transition:.3s; border-radius:50%; }
input:checked + .slider { background:#2e50e4; }
input:checked + .slider:before { transform:translateX(22px); }

/* Preview panel */
.preview-panel { position:sticky; top:20px; }
.preview-frame { width:100%; height:800px; border:1px solid #e2e8f0; border-radius:12px; background:#f8f9fa; }
.preview-loading { display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="cfg-card">
        <div class="card-header">
            <h4><i class="fa fa-palette"></i> Diseño Personalizado de Factura PDF</h4>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" style="border-radius:12px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row">

        {{-- ── Panel izquierdo: configuración ── --}}
        <div class="col-lg-5">
            <form id="formDiseno" action="{{ route('diseno-factura.update') }}" method="POST">
                @csrf @method('PUT')

                {{-- COLORES --}}
                <div class="cfg-card">
                    <div style="padding:24px;">
                        <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-fill-drip"></i> Colores</h6>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-lbl">Color Principal</label>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <input type="color" name="factura_color_primario" id="colorPrimario"
                                               value="{{ $empresa->colorPrimario() }}"
                                               style="width:48px;height:42px;border-radius:10px;border:2px solid #e2e8f0;padding:2px;cursor:pointer;">
                                        <input type="text" id="colorPrimarioHex" class="form-control form-inp"
                                               value="{{ $empresa->colorPrimario() }}" maxlength="7" style="font-family:monospace;">
                                    </div>
                                    <small class="text-muted">Encabezado, totales y barras</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-lbl">Color Acento</label>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <input type="color" name="factura_color_acento" id="colorAcento"
                                               value="{{ $empresa->colorAcento() }}"
                                               style="width:48px;height:42px;border-radius:10px;border:2px solid #e2e8f0;padding:2px;cursor:pointer;">
                                        <input type="text" id="colorAcentoHex" class="form-control form-inp"
                                               value="{{ $empresa->colorAcento() }}" maxlength="7" style="font-family:monospace;">
                                    </div>
                                    <small class="text-muted">Valores destacados y número de factura</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TEXTOS --}}
                <div class="cfg-card">
                    <div style="padding:24px;">
                        <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-font"></i> Textos</h6>
                        <div class="form-group">
                            <label class="form-lbl">Subtítulo bajo el nombre de la empresa</label>
                            <input name="factura_subtitulo" class="form-control form-inp"
                                   value="{{ old('factura_subtitulo', $empresa->factura_subtitulo ?? 'Servicio Público Domiciliario') }}"
                                   placeholder="Ej: Servicio Público Domiciliario" maxlength="150">
                        </div>
                    </div>
                </div>

                {{-- VISIBILIDAD --}}
                <div class="cfg-card">
                    <div style="padding:24px;">
                        <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-eye"></i> Visibilidad de Secciones</h6>

                        @php
                        $toggles = [
                            ['factura_mostrar_logo',           'Logo de la empresa',           'Muestra el logo en la esquina superior izquierda'],
                            ['factura_mostrar_lectura',        'Sección de lectura del medidor','Tabla con lectura anterior, actual y consumo m³'],
                            ['factura_mostrar_serie_medidor',  'Serie / N° del medidor',        'Número de serie del medidor en datos del suscriptor'],
                            ['factura_mostrar_sector',         'Sector / Ubicación',            'Sector o zona geográfica del suscriptor'],
                            ['factura_mostrar_tipo_uso',       'Clase de servicio',             'Uso residencial, comercial, industrial, etc.'],
                            ['factura_mostrar_estrato',        'Estrato socioeconómico',        'Estrato 1 al 6 con su nombre descriptivo'],
                            ['factura_mostrar_tarifa',         'Nombre de la tarifa',           'Resolución tarifaria vigente en el período'],
                            ['factura_mostrar_saldo_anterior', 'Saldo anterior en mora',        'Aviso de deuda pendiente de períodos anteriores'],
                            ['factura_mostrar_creditos',       'Tabla de créditos',             'Financiaciones y otros cobros por cuotas'],
                            ['factura_mostrar_barras_consumo', 'Gráfica de consumos',           'Barras con los últimos 6 meses + consumo actual'],
                            ['factura_mostrar_observaciones',  'Observaciones',                 'Campo de observaciones o notas especiales'],
                            ['factura_mostrar_codigo_barras',  'Código numérico al pie',        'Número de factura en formato código para pagos'],
                        ];
                        @endphp

                        @foreach($toggles as [$campo, $etiqueta, $desc])
                        @php $valor = old($campo, $empresa->$campo ?? true); @endphp
                        <div class="toggle-row">
                            <div>
                                <div class="toggle-label">{{ $etiqueta }}</div>
                                <div class="toggle-desc">{{ $desc }}</div>
                            </div>
                            <label class="switch" style="margin-left:16px;">
                                <input type="checkbox" name="{{ $campo }}" value="1"
                                       {{ $valor ? 'checked' : '' }} class="toggle-check">
                                <span class="slider"></span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="text-align:right;margin-bottom:30px;display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" id="btnPreview" class="btn btn-outline-primary" style="border-radius:12px;font-weight:700;padding:11px 24px;">
                        <i class="fa fa-eye"></i> Actualizar Vista Previa
                    </button>
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;padding:11px 34px;font-size:.95rem;">
                        <i class="fa fa-save"></i> Guardar Diseño
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Panel derecho: vista previa ── --}}
        <div class="col-lg-7">
            <div class="preview-panel">
                <div class="cfg-card" style="overflow:hidden;">
                    <div class="card-header" style="padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
                        <h4 style="margin:0;font-size:1rem;"><i class="fa fa-file-pdf"></i> Vista Previa de Factura</h4>
                        <span style="font-size:.75rem;opacity:.8;">
                            @if($factura) Factura #{{ $factura->numero_factura }} — {{ $factura->suscriptor }} @else Sin facturas de muestra @endif
                        </span>
                    </div>
                    <div style="position:relative;">
                        <div id="previewLoading" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.8);z-index:10;display:flex;align-items:center;justify-content:center;">
                            <div><i class="fa fa-spinner fa-spin fa-2x" style="color:#2e50e4;"></i><div style="margin-top:8px;font-size:.85rem;color:#666;">Actualizando vista previa...</div></div>
                        </div>
                        @if($factura)
                        <iframe id="previewFrame" src="{{ route('diseno-factura.preview') }}"
                                class="preview-frame" frameborder="0"></iframe>
                        @else
                        <div style="height:400px;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#9ca3af;">
                            <i class="fa fa-file-invoice fa-3x" style="margin-bottom:12px;"></i>
                            <p>No hay facturas generadas aún para mostrar la vista previa.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    // ── Sincronizar color picker ↔ input texto ──────────────────────────────
    function syncColor(pickerId, hexId) {
        var picker = document.getElementById(pickerId);
        var hex    = document.getElementById(hexId);
        if (!picker || !hex) return;

        picker.addEventListener('input', function () {
            hex.value = picker.value;
        });
        hex.addEventListener('input', function () {
            var v = hex.value.trim();
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) picker.value = v;
        });
    }
    syncColor('colorPrimario', 'colorPrimarioHex');
    syncColor('colorAcento',   'colorAcentoHex');

    // ── Actualizar vista previa ─────────────────────────────────────────────
    function buildPreviewUrl() {
        var base   = '{{ route("diseno-factura.preview") }}';
        var params = new URLSearchParams();

        params.set('factura_color_primario', document.getElementById('colorPrimario').value);
        params.set('factura_color_acento',   document.getElementById('colorAcento').value);
        params.set('factura_subtitulo',      document.querySelector('[name="factura_subtitulo"]').value);

        document.querySelectorAll('.toggle-check').forEach(function (chk) {
            params.set(chk.name, chk.checked ? '1' : '0');
        });

        return base + '?' + params.toString();
    }

    function refreshPreview() {
        var frame = document.getElementById('previewFrame');
        if (!frame) return;

        var loading = document.getElementById('previewLoading');
        if (loading) loading.style.display = 'flex';

        frame.onload = function () {
            if (loading) loading.style.display = 'none';
        };
        frame.src = buildPreviewUrl();
    }

    document.getElementById('btnPreview').addEventListener('click', refreshPreview);

    // Preview automático al cambiar colores (con debounce)
    var debounceTimer;
    function debouncedRefresh() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refreshPreview, 800);
    }

    document.getElementById('colorPrimario').addEventListener('input', debouncedRefresh);
    document.getElementById('colorAcento').addEventListener('input', debouncedRefresh);
    document.querySelectorAll('.toggle-check').forEach(function (chk) {
        chk.addEventListener('change', debouncedRefresh);
    });
    document.querySelector('[name="factura_subtitulo"]').addEventListener('input', debouncedRefresh);
})();
</script>
@endsection
