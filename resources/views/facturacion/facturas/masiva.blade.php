@extends("theme.$theme.layout")

@section('titulo', 'Facturación Masiva')

@section('styles')
<style>
.modern-card { border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); border:none; overflow:hidden; margin-bottom:20px; background:white; }
.modern-card .card-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border:none; padding:22px 28px; display:flex; justify-content:space-between; align-items:center; }
.modern-card .card-header h3 { color:white; font-weight:700; font-size:1.3rem; margin:0; }
.form-box { background:white; border-radius:16px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,.06); margin-bottom:20px; }
.form-box h5 { font-weight:700; color:#2d3748; font-size:.9rem; text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; border-bottom:2px solid #e2e8f0; padding-bottom:10px; }
.form-control-gen { border-radius:10px; border:2px solid #e2e8f0; padding:10px 13px; transition:all .3s; font-size:.88rem; }
.form-control-gen:focus { border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12); outline:none; }
label.lbl { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; }

/* Panel de resultados */
#panelResultados { display:none; }
.resultado-card { background:white; border-radius:14px; padding:18px 20px; margin-bottom:16px; border-left:4px solid #667eea; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.resultado-card.rc-success { border-left-color:#48bb78; background:#f0fff4; }
.resultado-card.rc-warning { border-left-color:#ed8936; background:#fffaf0; }
.resultado-card.rc-danger { border-left-color:#f56565; background:#fff5f5; }
.resultado-card.rc-info { border-left-color:#4299e1; background:#ebf8ff; }

.rc-title { font-weight:700; color:#2d3748; font-size:1rem; margin-bottom:8px; }
.rc-val { font-size:1.8rem; font-weight:800; color:#2d3748; }
.rc-subtitle { font-size:.75rem; color:#718096; text-transform:uppercase; letter-spacing:.4px; }

/* Tabla de detalles */
.tabla-detalles { width:100%; border-collapse:collapse; font-size:.85rem; }
.tabla-detalles th { background:#f7fafc; padding:10px 12px; text-align:left; font-weight:700; color:#4a5568; text-transform:uppercase; font-size:.7rem; letter-spacing:.5px; }
.tabla-detalles td { padding:10px 12px; border-bottom:1px solid #e2e8f0; }
.tabla-detalles tr:hover { background:#f7fafc; }

.badge-estado { padding:4px 10px; border-radius:12px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.badge-estado.FACTURADO_AUTOMATICO { background:#c6f6d5; color:#22543d; }
.badge-estado.PENDIENTE_REVISION { background:#fed7d7; color:#742a2a; }
.badge-estado.ERROR { background:#fed7d7; color:#742a2a; }
.badge-estado.SALTEADO { background:#e2e8f0; color:#4a5568; }

.btn-grad { border-radius:12px; padding:11px 32px; font-weight:700; border:none; background:linear-gradient(135deg,#667eea,#764ba2); color:white; box-shadow:0 4px 15px rgba(102,126,234,.4); font-size:.92rem; }
.btn-grad:disabled { opacity:.5; cursor:not-allowed; }
.btn-preview { border-radius:12px; padding:10px 24px; font-weight:700; border:2px solid #667eea; color:#667eea; background:white; font-size:.88rem; transition:all .3s; }
.btn-preview:hover { background:#667eea; color:white; }

.spinner-proceso { display:none; text-align:center; padding:40px; }
.spinner-proceso i { font-size:3rem; color:#667eea; }

.resumen-box { background:linear-gradient(135deg,#667eea,#764ba2); border-radius:14px; padding:16px 20px; color:white; margin-bottom:16px; }
.resumen-box .rb-row { display:flex; justify-content:space-between; margin-bottom:8px; font-size:.85rem; }
.resumen-box .rb-row:last-child { margin-bottom:0; }
.resumen-box .rb-lbl { opacity:.8; }
.resumen-box .rb-val { font-weight:700; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-bolt"></i> Facturación Masiva Automática</h3>
            <a href="{{ route('facturas.index') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-box">
                <h5><i class="fa fa-cog"></i> Configuración del Proceso</h5>
                
                <div class="form-group">
                    <label class="lbl">Período de Lectura <span style="color:red">*</span></label>
                    <select class="form-control form-control-gen" id="selPeriodo">
                        <option value="">— Seleccione período —</option>
                        @foreach($periodos as $p)
                        <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}">
                            {{ $p->nombre }} — {{ $p->estado }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="resumenPeriodo" style="display:none;">
                    <div class="resumen-box">
                        <div class="rb-row">
                            <span class="rb-lbl">Total lecturas:</span>
                            <span class="rb-val" id="rTotal">—</span>
                        </div>
                        <div class="rb-row">
                            <span class="rb-lbl">Normales (54):</span>
                            <span class="rb-val" id="rNormales">—</span>
                        </div>
                        <div class="rb-row">
                            <span class="rb-lbl">Otras críticas:</span>
                            <span class="rb-val" id="rOtras">—</span>
                        </div>
                        <div class="rb-row">
                            <span class="rb-lbl">Ya facturadas:</span>
                            <span class="rb-val" id="rFacturadas">—</span>
                        </div>
                        <div class="rb-row">
                            <span class="rb-lbl">Pendientes:</span>
                            <span class="rb-val" id="rPendientes">—</span>
                        </div>
                    </div>
                </div>

                <div style="background:#ebf8ff;border-radius:10px;padding:14px;margin-bottom:16px;font-size:.82rem;color:#2c5282;">
                    <i class="fa fa-info-circle" style="color:#3182ce;"></i>
                    <strong>Información:</strong>
                    <ul style="margin:8px 0 0 20px;padding:0;">
                        <li>Solo se facturan automáticamente las lecturas con crítica <strong>NORMAL-54</strong></li>
                        <li>Las demás críticas quedan pendientes para revisión manual</li>
                        <li>Se crea una orden de revisión para cada lectura no normal</li>
                    </ul>
                </div>

                <button class="btn btn-grad w-100" id="btnCargarResumen" disabled>
                    <i class="fa fa-chart-bar"></i> Cargar Resumen
                </button>
                <button class="btn btn-grad w-100 mt-2" id="btnProcesar" disabled style="display:none;">
                    <i class="fa fa-play"></i> Ejecutar Facturación Masiva
                </button>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Spinner de proceso --}}
            <div class="spinner-proceso" id="spinnerProceso">
                <i class="fa fa-spinner fa-spin"></i>
                <h5 style="margin-top:20px;color:#4a5568;">Procesando facturación...</h5>
                <p style="color:#718096;font-size:.9rem;">Esto puede tomar varios minutos dependiendo de la cantidad de lecturas.</p>
            </div>

            {{-- Panel de resultados --}}
            <div id="panelResultados">
                <div class="form-box">
                    <h5><i class="fa fa-check-circle"></i> Resultados del Proceso</h5>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="resultado-card rc-info">
                                <div class="rc-subtitle">Procesadas</div>
                                <div class="rc-val" id="resProcesadas">0</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resultado-card rc-success">
                                <div class="rc-subtitle">Facturadas Auto.</div>
                                <div class="rc-val" id="resFacturadas">0</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resultado-card rc-warning">
                                <div class="rc-subtitle">Pendientes Revisión</div>
                                <div class="rc-val" id="resPendientes">0</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="resultado-card rc-danger">
                                <div class="rc-subtitle">Errores</div>
                                <div class="rc-val" id="resErrores">0</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:20px;">
                        <h6 style="font-weight:700;color:#2d3748;margin-bottom:12px;">
                            <i class="fa fa-list"></i> Detalle de Procesamiento
                        </h6>
                        <div style="max-height:400px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;">
                            <table class="tabla-detalles">
                                <thead>
                                    <tr>
                                        <th>Suscriptor</th>
                                        <th>Estado</th>
                                        <th>Consumo</th>
                                        <th>Crítica</th>
                                        <th>Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetallesBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mensaje inicial --}}
            <div id="mensajeInicial" style="background:#f7fafc;border-radius:14px;padding:40px;text-align:center;">
                <i class="fa fa-file-invoice" style="font-size:3rem;color:#cbd5e0;margin-bottom:16px;"></i>
                <h5 style="color:#4a5568;font-weight:700;">Seleccione un período para comenzar</h5>
                <p style="color:#718096;font-size:.9rem;">Cargue el resumen de lecturas y luego ejecute la facturación masiva.</p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let periodoSeleccionado = null;

    // Habilitar botón cargar resumen cuando seleccione período
    $('#selPeriodo').on('change', function() {
        const id = $(this).val();
        if (id) {
            periodoSeleccionado = id;
            $('#btnCargarResumen').prop('disabled', false);
            $('#resumenPeriodo').hide();
            $('#btnProcesar').hide();
            $('#panelResultados').hide();
            $('#mensajeInicial').show();
        } else {
            periodoSeleccionado = null;
            $('#btnCargarResumen').prop('disabled', true);
        }
    });

    // Cargar resumen
    $('#btnCargarResumen').on('click', function() {
        if (!periodoSeleccionado) return;

        $.ajax({
            url: '{{ route("facturas.masiva.resumen") }}',
            method: 'GET',
            data: { periodo_lectura_id: periodoSeleccionado },
            success: function(res) {
                if (res.ok) {
                    const r = res.resumen;
                    $('#rTotal').text(r.total_lecturas);
                    $('#rNormales').text(r.normales_54);
                    $('#rOtras').text(r.otras_criticas);
                    $('#rFacturadas').text(r.ya_facturadas);
                    $('#rPendientes').text(r.pendientes_facturar);
                    
                    $('#resumenPeriodo').slideDown();
                    $('#btnProcesar').show();
                    $('#btnProcesar').prop('disabled', false);
                } else {
                    alert('Error al cargar resumen: ' + res.mensaje);
                }
            },
            error: function(xhr) {
                alert('Error en la solicitud');
            }
        });
    });

    // Ejecutar facturación masiva
    $('#btnProcesar').on('click', function() {
        if (!periodoSeleccionado) return;

        if (!confirm('¿Está seguro de ejecutar la facturación masiva?\n\nSolo las lecturas NORMAL-54 se facturarán automáticamente.\nLas demás quedarán pendientes de revisión.')) {
            return;
        }

        $('#btnProcesar').prop('disabled', true);
        $('#btnCargarResumen').prop('disabled', true);
        $('#spinnerProceso').show();
        $('#panelResultados').hide();
        $('#mensajeInicial').hide();

        $.ajax({
            url: '{{ route("facturas.masiva.procesar") }}',
            method: 'POST',
            data: { 
                periodo_lectura_id: periodoSeleccionado,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#spinnerProceso').hide();
                
                if (res.ok) {
                    mostrarResultados(res.resultado);
                } else {
                    alert('Error en el proceso: ' + res.mensaje);
                    $('#btnCargarResumen').prop('disabled', false);
                }
            },
            error: function(xhr) {
                $('#spinnerProceso').hide();
                alert('Error en la solicitud: ' + xhr.responseText);
                $('#btnCargarResumen').prop('disabled', false);
            }
        });
    });

    function mostrarResultados(resultado) {
        $('#resProcesadas').text(resultado.procesadas);
        $('#resFacturadas').text(resultado.facturadas_automaticas);
        $('#resPendientes').text(resultado.pendientes_revision);
        $('#resErrores').text(resultado.errores);

        // Llenar tabla
        let html = '';
        resultado.detalles.forEach(function(d) {
            const badgeClass = d.estado || 'SALTEADO';
            html += '<tr>';
            html += '<td><strong>' + d.suscriptor + '</strong></td>';
            html += '<td><span class="badge-estado ' + badgeClass + '">' + d.estado + '</span></td>';
            html += '<td>' + (d.consumo !== undefined ? d.consumo + ' m³' : '—') + '</td>';
            html += '<td>' + (d.critica || '—') + '</td>';
            html += '<td>' + (d.mensaje || '') + '</td>';
            html += '</tr>';
        });
        $('#tablaDetallesBody').html(html);

        $('#panelResultados').slideDown();
        $('#btnCargarResumen').prop('disabled', false);
    }
});
</script>
@endsection
