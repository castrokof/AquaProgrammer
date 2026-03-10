@extends("theme.$theme.layout")

@section('titulo', 'Configuración de Empresa')

@section('styles')
<style>
.cfg-card { border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,.09); border:none; overflow:hidden; background:white; margin-bottom:20px; }
.cfg-card .card-header { background:linear-gradient(135deg,#2e50e4,#2b0c49); padding:18px 24px; }
.cfg-card .card-header h4 { color:white; font-weight:700; margin:0; font-size:1.1rem; }
.form-lbl { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }
.form-inp { border-radius:10px; border:2px solid #e2e8f0; padding:9px 13px; font-size:.9rem; transition:border-color .2s; }
.form-inp:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
.logo-preview { max-height:100px; max-width:220px; border-radius:8px; border:2px solid #e2e8f0; padding:4px; object-fit:contain; }
.section-sep { border:none; border-top:2px dashed #e2e8f0; margin:20px 0; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="cfg-card">
        <div class="card-header">
            <h4><i class="fa fa-building"></i> Configuración de Empresa / Datos para Factura</h4>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" style="border-radius:12px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('empresa.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="cfg-card">
            <div style="padding:24px;">
                <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-id-card"></i> Datos de la Empresa</h6>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-lbl">Nombre de la Empresa *</label>
                            <input name="nombre" class="form-control form-inp" value="{{ old('nombre', $empresa->nombre) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-lbl">NIT</label>
                            <input name="nit" class="form-control form-inp" value="{{ old('nit', $empresa->nit) }}" placeholder="ej: 1130629762-8">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-lbl">Prefijo Consecutivo Factura</label>
                            <input name="prefijo_factura" class="form-control form-inp" value="{{ old('prefijo_factura', $empresa->prefijo_factura) }}" placeholder="ej: ASPD" maxlength="20">
                            <small class="text-muted">Se antepone al número de factura (ej: ASPD00001)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-lbl">Dirección</label>
                            <input name="direccion" class="form-control form-inp" value="{{ old('direccion', $empresa->direccion) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-lbl">Teléfono</label>
                            <input name="telefono" class="form-control form-inp" value="{{ old('telefono', $empresa->telefono) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-lbl">Email</label>
                            <input name="email" type="email" class="form-control form-inp" value="{{ old('email', $empresa->email) }}">
                        </div>
                    </div>
                </div>

                <hr class="section-sep">
                <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-file-invoice"></i> Textos de Factura</h6>

                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label class="form-lbl">Leyenda Documento Equivalente</label>
                            <input name="texto_documento_equivalente" class="form-control form-inp"
                                   value="{{ old('texto_documento_equivalente', $empresa->texto_documento_equivalente) }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-lbl">Texto Pie de Página PDF</label>
                            <input name="texto_pie" class="form-control form-inp"
                                   value="{{ old('texto_pie', $empresa->texto_pie) }}" placeholder="Ej: Horario atención...">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-lbl">Banco para pagos</label>
                            <input name="nombre_banco" class="form-control form-inp" value="{{ old('nombre_banco', $empresa->nombre_banco) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-lbl">Número de Cuenta</label>
                            <input name="numero_cuenta" class="form-control form-inp" value="{{ old('numero_cuenta', $empresa->numero_cuenta) }}">
                        </div>
                    </div>
                </div>

                <hr class="section-sep">
                <h6 style="font-weight:700;color:#2e50e4;margin-bottom:6px;"><i class="fa fa-credit-card"></i> Pasarela de Pago — Wompi</h6>
                <p style="font-size:.78rem;color:#718096;margin-bottom:14px;">
                    <a href="https://comercios.wompi.co" target="_blank">Regístrate en Wompi</a> (gratis) para obtener tus llaves.
                    La comisión es del 2,9% por transacción aprobada. Con el modo sandbox puedes probar sin cobros reales.
                    URL del webhook a configurar en Wompi: <code>{{ route('pago-publico.webhook') }}</code>
                </p>

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-lbl">Llave Pública (pub_test_… / pub_prod_…)</label>
                            <input name="wompi_public_key" class="form-control form-inp"
                                   value="{{ old('wompi_public_key', $empresa->wompi_public_key) }}"
                                   placeholder="pub_test_xxxxxxxxxxxxxxxxxxxxxxxx">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-lbl">Llave Privada (prv_test_… / prv_prod_…)</label>
                            <input name="wompi_private_key" class="form-control form-inp"
                                   value="{{ old('wompi_private_key', $empresa->wompi_private_key) }}"
                                   placeholder="prv_test_xxxxxxxxxxxxxxxxxxxxxxxx">
                            <small class="text-muted">Usada para consultar transacciones y validar webhooks</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-lbl">Modo</label>
                            <select name="wompi_test_mode" class="form-control form-inp">
                                <option value="1" {{ ($empresa->wompi_test_mode ?? true) ? 'selected':'' }}>Sandbox (pruebas)</option>
                                <option value="0" {{ !($empresa->wompi_test_mode ?? true) ? 'selected':'' }}>Producción</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-lbl">Llave de Integridad (Firma SHA-256)</label>
                            <input name="wompi_integrity_key" class="form-control form-inp"
                                   value="{{ old('wompi_integrity_key', $empresa->wompi_integrity_key) }}"
                                   placeholder="test_integrity_xxxxxxxxxxxxxxx">
                            <small class="text-muted">Se encuentra en el panel Wompi → Llaves</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-lbl">URL de Redirección tras el pago</label>
                            <input name="wompi_redirect_url" class="form-control form-inp"
                                   value="{{ old('wompi_redirect_url', $empresa->wompi_redirect_url) }}"
                                   placeholder="https://tudominio.com/pagar/resultado">
                            <small class="text-muted">Debe ser una URL pública accesible desde internet</small>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <a href="{{ route('pago-publico.index') }}" target="_blank"
                       style="font-size:.83rem;color:#2e50e4;font-weight:600;">
                        <i class="fa fa-external-link-alt"></i> Ver página de pago público
                    </a>
                </div>

                <hr class="section-sep">
                <h6 style="font-weight:700;color:#2e50e4;margin-bottom:16px;"><i class="fa fa-image"></i> Logo de la Empresa</h6>

                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-lbl">Subir Logo (PNG, JPG — máx 2 MB)</label>
                            <input type="file" name="logo" class="form-control form-inp" accept="image/*" id="inputLogo">
                            <small class="text-muted">Se mostrará en el encabezado de cada factura PDF</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @if($empresa->logo_path)
                            <div>
                                <label class="form-lbl d-block">Logo actual</label>
                                <img src="{{ $empresa->logoUrl() }}" class="logo-preview" alt="Logo empresa" id="previewImg">
                            </div>
                        @else
                            <img src="" class="logo-preview" style="display:none;" id="previewImg">
                            <span class="text-muted" id="sinLogo"><i class="fa fa-image"></i> Sin logo cargado</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <div style="text-align:right;margin-bottom:30px;">
            <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;padding:11px 34px;font-size:.95rem;">
                <i class="fa fa-save"></i> Guardar Configuración
            </button>
        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
$('#inputLogo').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        $('#previewImg').attr('src', e.target.result).show();
        $('#sinLogo').hide();
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
