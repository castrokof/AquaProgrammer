@extends("theme.$theme.layout")

@section('titulo', 'Facturación Especial Masiva')

@section('styles')
<style>
/* Mismos estilos modernos que la vista de masiva normal */
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

.rc-title { font-weight:700; color:#2d3748; font-size:1rem; margin-bottom:8px; }
.rc-val { font-size:1.8rem; font-weight:800; color:#2d3748; }
.rc-subtitle { font-size:.75rem; color:#718096; text-transform:uppercase; letter-spacing:.4px; }

/* Tabla de detalles y selección */
.tabla-detalles { width:100%; border-collapse:collapse; font-size:.85rem; }
.tabla-detalles th { background:#f7fafc; padding:10px 12px; text-align:left; font-weight:700; color:#4a5568; text-transform:uppercase; font-size:.7rem; letter-spacing:.5px; }
.tabla-detalles td { padding:10px 12px; border-bottom:1px solid #e2e8f0; }
.tabla-detalles tr:hover { background:#f7fafc; }
.tabla-detalles input[type="checkbox"] { transform: scale(1.3); cursor: pointer; }

.badge-estado { padding:4px 10px; border-radius:12px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.badge-estado.FACTURADO_MANUAL { background:#c6f6d5; color:#22543d; }
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

/* Contenedor de tabla con scroll */
.tabla-container { max-height: 500px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <!-- Header -->
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-bolt"></i> Facturación Especial (Altas, Bajas, Sin Lectura)</h3>
            <div>
                <a href="{{ route('facturas.index') }}" class="btn btn-light mr-2" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('facturas.masiva') }}" class="btn btn-outline-light" style="border-radius:12px;font-weight:700;">
                    <i class="fa fa-cogs"></i> Ir a Masiva Normal
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Configuración -->
        <div class="col-md-4">
            <div class="form-box">
                <h5><i class="fa fa-cog"></i> Configuración</h5>
                
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
                        <div class="rb-row"><span class="rb-lbl">Especiales Total:</span><span class="rb-val" id="rTotal">—</span></div>
                        <div class="rb-row"><span class="rb-lbl">Ya Facturadas:</span><span class="rb-val" id="rFacturadas">—</span></div>
                        <div class="rb-row"><span class="rb-lbl">Pendientes:</span><span class="rb-val" id="rPendientes">—</span></div>
                    </div>
                </div>

                <div style="background:#fffaf0;border-radius:10px;padding:14px;margin-bottom:16px;font-size:.82rem;color:#c05621;">
                    <i class="fa fa-exclamation-triangle" style="color:#dd6b20;"></i>
                    <strong>Atención:</strong>
                    <ul style="margin:8px 0 0 20px;padding:0;">
                        <li>Solo se muestran lecturas <strong>NO normales</strong> (Altas, Bajas, etc).</li>
                        <li>Debe seleccionar manualmente cuáles facturar.</li>
                        <li>Verifique lecturas anteriores y actuales antes de procesar.</li>
                    </ul>
                </div>

                <button class="btn btn-grad w-100" id="btnCargarTabla" disabled>
                    <i class="fa fa-table"></i> Cargar Lecturas
                </button>
                <button class="btn btn-grad w-100 mt-2" id="btnProcesar" disabled style="display:none;">
                    <i class="fa fa-play"></i> Facturar Seleccionadas
                </button>
            </div>
        </div>

        <!-- Columna Derecha: Tabla y Resultados -->
        <div class="col-md-8">
            
            {{-- Spinner --}}
            <div class="spinner-proceso" id="spinnerProceso">
                <i class="fa fa-spinner fa-spin"></i>
                <h5 style="margin-top:20px;color:#4a5568;">Procesando facturas...</h5>
            </div>

            {{-- Mensaje Inicial --}}
            <div id="mensajeInicial" style="background:#f7fafc;border-radius:14px;padding:40px;text-align:center;">
                <i class="fa fa-file-invoice" style="font-size:3rem;color:#cbd5e0;margin-bottom:16px;"></i>
                <h5 style="color:#4a5568;font-weight:700;">Seleccione un período para comenzar</h5>
            </div>

            {{-- Tabla de Selección --}}
            <div id="panelTabla" style="display:none;">
                <div class="form-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fa fa-list"></i> Lecturas Disponibles</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodo(true)">Marcar Todas</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodo(false)">Desmarcar</button>
                        </div>
                    </div>

                    <div class="tabla-container">
                        <table class="tabla-detalles" id="tablaEspeciales">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="checkAll" onclick="seleccionarTodo(this.checked)"></th>
                                    <th>Suscriptor</th>
                                    <th>Cliente</th>
                                    <th>Anterior</th>
                                    <th>Actual</th>
                                    <th>Consumo</th>
                                    <th>Crítica</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBody">
                                <!-- Se llena con JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted small" id="contadorSeleccion">0 seleccionadas</div>
                </div>
            </div>

            {{-- Resultados --}}
            <div id="panelResultados">
                <div class="form-box">
                    <h5><i class="fa fa-check-circle"></i> Resultados</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="resultado-card rc-success">
                                <div class="rc-subtitle">Facturadas</div>
                                <div class="rc-val" id="resFacturadas">0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="resultado-card rc-danger">
                                <div class="rc-subtitle">Errores</div>
                                <div class="rc-val" id="resErrores">0</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="resultado-card" style="border-left-color:#cbd5e0; background:#f7fafc;">
                                <div class="rc-subtitle">Procesadas</div>
                                <div class="rc-val" id="resProcesadas">0</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:20px; max-height:300px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px;">
                        <table class="tabla-detalles">
                            <thead>
                                <tr>
                                    <th>Suscriptor</th>
                                    <th>Estado</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetallesBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let periodoId = null;
    let lecturasDisponibles = [];

    $('#selPeriodo').on('change', function() {
        periodoId = $(this).val();
        if (periodoId) {
            $('#btnCargarTabla').prop('disabled', false);
            $('#resumenPeriodo').hide();
            $('#panelTabla').hide();
            $('#panelResultados').hide();
            $('#mensajeInicial').show();
            $('#btnProcesar').hide();
        } else {
            periodoId = null;
            $('#btnCargarTabla').prop('disabled', true);
        }
    });

    // Cargar Resumen
    $('#btnCargarTabla').on('click', function() {
        if (!periodoId) return;

        // 1. Cargar resumen numérico
        $.ajax({
            url: '{{ route("facturacion.especial.resumen") }}', // Asegúrate de crear esta ruta
            method: 'GET',
            data: { periodo_lectura_id: periodoId },
            success: function(res) {
                if (res.ok) {
                    const r = res.resumen;
                    $('#rTotal').text(r.especiales_total);
                    $('#rFacturadas').text(r.especiales_facturadas);
                    $('#rPendientes').text(r.especiales_pendientes);
                    $('#resumenPeriodo').slideDown();
                    
                    // 2. Cargar tabla de datos
                    cargarTablaLecturas();
                }
            },
            error: function() { alert('Error al cargar resumen'); }
        });
    });

    function cargarTablaLecturas() {
        $.ajax({
            url: '{{ route("facturacion.especial.lecturas") }}', // Ruta nueva para obtener lista
            method: 'GET',
            data: { periodo_lectura_id: periodoId },
            success: function(res) {
                if (res.ok && res.lecturas.length > 0) {
                    lecturasDisponibles = res.lecturas;
                    renderizarTabla(res.lecturas);
                    $('#panelTabla').slideDown();
                    $('#mensajeInicial').hide();
                    $('#btnProcesar').show();
                } else {
                    alert('No hay lecturas especiales pendientes en este período.');
                    $('#btnCargarTabla').prop('disabled', true);
                }
            }
        });
    }

    function renderizarTabla(datos) {
        let html = '';
        datos.forEach((l, index) => {
            html += `<tr>
                <td><input type="checkbox" class="check-item" value="${index}" onchange="verificarSeleccion()"></td>
                <td><strong>${l.suscriptor}</strong></td>
                <td>${l.nombre}</td>
                <td>${l.la}</td>
                <td>${l.lect_actual}</td>
                <td><strong>${l.consumo}</strong></td>
                <td><span class="badge badge-info">${l.critica}</span></td>
            </tr>`;
        });
        $('#tablaBody').html(html);
    }

    // Selección
    window.seleccionarTodo = function(marcar) {
        $('.check-item').prop('checked', marcar);
        $('#checkAll').prop('checked', marcar);
        verificarSeleccion();
    };

    window.verificarSeleccion = function() {
        const count = $('.check-item:checked').length;
        $('#contadorSeleccion').text(`${count} seleccionadas`);
        $('#btnProcesar').prop('disabled', count === 0);
        if(count > 0) {
            $('#btnProcesar').html(`<i class="fa fa-play"></i> Facturar (${count})`);
        } else {
            $('#btnProcesar').html('<i class="fa fa-play"></i> Facturar Seleccionadas');
        }
    };

    // Procesar
    $('#btnProcesar').on('click', function() {
        if (!confirm('¿Confirmar facturación de las lecturas seleccionadas?')) return;

        const seleccionados = [];
        $('.check-item:checked').each(function() {
            seleccionados.push(lecturasDisponibles[$(this).val()]);
        });

        if (seleccionados.length === 0) return;

        $('#btnProcesar').prop('disabled', true);
        $('#spinnerProceso').show();
        $('#panelResultados').hide();

        $.ajax({
            url: '{{ route("facturacion.especial.facturar-seleccionadas") }}',
            method: 'POST',
            data: {
                periodo_lectura_id: periodoId,
                lecturas: seleccionados,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#spinnerProceso').hide();
                if (res.ok) {
                    mostrarResultados(res.resultado);
                    // Recargar tabla para quitar las facturadas
                    setTimeout(cargarTablaLecturas, 2000); 
                } else {
                    alert('Error: ' + res.mensaje);
                    $('#btnProcesar').prop('disabled', false);
                }
            },
            error: function(xhr) {
                $('#spinnerProceso').hide();
                alert('Error en solicitud: ' + xhr.responseText);
                $('#btnProcesar').prop('disabled', false);
            }
        });
    });

    function mostrarResultados(res) {
        $('#resFacturadas').text(res.facturadas);
        $('#resErrores').text(res.errores);
        $('#resProcesadas').text(res.procesadas);

        let html = '';
        res.detalles.forEach(d => {
            const cls = d.estado === 'ERROR' ? 'rc-danger' : (d.estado === 'SALTEADO' ? '' : 'rc-success');
            html += `<tr>
                <td>${d.suscriptor}</td>
                <td><span class="badge-estado ${d.estado}">${d.estado}</span></td>
                <td>${d.mensaje}</td>
            </tr>`;
        });
        $('#tablaDetallesBody').html(html);
        $('#panelResultados').slideDown();
    }
});
</script>
@endsection