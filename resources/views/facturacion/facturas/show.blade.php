@extends("theme.$theme.layout")

@section('titulo', 'Factura ' . $factura->numero_factura)

@section('styles')
<style>
body { background:#f0f4f8; }
.factura-wrap { max-width:900px; margin:0 auto; }
/* ── Header ── */
.fact-header { background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%); border-radius:20px 20px 0 0; padding:28px 32px; color:white; display:flex; justify-content:space-between; align-items:flex-start; }
.fact-header .empresa { font-size:1.2rem; font-weight:800; letter-spacing:.5px; }
.fact-header .subempresa { font-size:.78rem; opacity:.8; margin-top:4px; }
.fact-header .num-factura { text-align:right; }
.fact-header .num-factura .num { font-size:1.8rem; font-weight:900; letter-spacing:1px; }
.fact-header .num-factura .lbl { font-size:.7rem; opacity:.75; text-transform:uppercase; }
/* ── Body ── */
.fact-body { background:white; border-radius:0 0 20px 20px; box-shadow:0 20px 60px rgba(0,0,0,.12); overflow:hidden; }
.fact-section { padding:20px 32px; border-bottom:2px solid #f0f0f0; }
.fact-section:last-child { border-bottom:none; }
.fact-section h6 { font-weight:800; color:#4a5568; font-size:.75rem; text-transform:uppercase; letter-spacing:.8px; margin-bottom:14px; padding-bottom:8px; border-bottom:2px solid #e2e8f0; }
/* Info cliente */
.info-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:12px; }
.info-item .lbl { font-size:.68rem; color:#a0aec0; text-transform:uppercase; font-weight:700; letter-spacing:.4px; }
.info-item .val { font-size:.88rem; font-weight:600; color:#2d3748; margin-top:2px; }
/* Promedio barras */
.prom-grid { display:flex; gap:8px; align-items:flex-end; }
.prom-bar { display:flex; flex-direction:column; align-items:center; gap:2px; }
.prom-bar .barra { background:linear-gradient(180deg,#667eea,#764ba2); border-radius:4px 4px 0 0; width:32px; min-height:4px; }
.prom-bar .num { font-size:.68rem; font-weight:700; color:#4a5568; }
.prom-bar .mes { font-size:.62rem; color:#a0aec0; }
/* Tabla conceptos */
.tabla-fact { width:100%; border-collapse:collapse; font-size:.83rem; }
.tabla-fact thead th { background:#f7fafc; padding:9px 12px; font-weight:700; color:#4a5568; font-size:.72rem; text-transform:uppercase; text-align:right; }
.tabla-fact thead th:first-child { text-align:left; }
.tabla-fact tbody td { padding:8px 12px; border-bottom:1px solid #f5f5f5; text-align:right; }
.tabla-fact tbody td:first-child { text-align:left; color:#4a5568; }
.tabla-fact tbody tr:hover { background:#fafbfc; }
.tabla-fact tfoot td { padding:10px 12px; font-weight:700; font-size:.88rem; border-top:2px solid #e2e8f0; text-align:right; }
.tabla-fact tfoot td:first-child { text-align:left; }
.total-final { background:linear-gradient(135deg,#2e50e4,#2b0c49); border-radius:14px; padding:20px 28px; display:flex; justify-content:space-between; align-items:center; margin:0 32px 24px; color:white; }
.total-final .lbl { font-size:.85rem; font-weight:600; opacity:.9; }
.total-final .val { font-size:2rem; font-weight:900; }
/* Estados */
.badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.badge-PAGADA    { background:#c6f6d5; color:#22543d; }
.badge-VENCIDA   { background:#fed7d7; color:#742a2a; }
.badge-ANULADA   { background:#e2e8f0; color:#718096; }
.badge-est { display:inline-block; padding:4px 14px; border-radius:20px; font-size:.75rem; font-weight:800; }
/* Pagos */
.pago-item { background:#f7fafc; border-radius:12px; padding:14px 18px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; }
.pago-item .fechas { font-size:.78rem; color:#718096; }
.pago-item .recibo { font-size:.8rem; font-weight:700; color:#4a5568; }
.pago-item .monto { font-weight:800; color:#22543d; font-size:1rem; }
/* Modal pago */
.modal-pago .modal-content { border-radius:20px; overflow:hidden; }
.modal-pago .modal-header { background:linear-gradient(135deg,#48bb78,#38a169); border:none; padding:20px 26px; }
.modal-pago .modal-header .modal-title { color:white; font-weight:700; }
.modal-pago .modal-header .close { color:white; opacity:.8; font-size:1.6rem; }
.modal-pago .modal-body { padding:24px; background:#fafbfc; }
.modal-pago .form-group label { font-weight:600; color:#4a5568; font-size:.8rem; text-transform:uppercase; }
.modal-pago .form-control { border-radius:10px; border:2px solid #e2e8f0; padding:10px 13px; }
.modal-pago .form-control:focus { border-color:#48bb78; box-shadow:0 0 0 3px rgba(72,187,120,.12); outline:none; }
.btn-pagar { border-radius:12px; padding:11px 30px; font-weight:800; border:none; background:linear-gradient(135deg,#48bb78,#38a169); color:white; font-size:.92rem; box-shadow:0 4px 15px rgba(72,187,120,.4); }
.saldo-restante { background:white; border-radius:12px; padding:14px; margin-top:16px; border:2px solid #e2e8f0; }
.saldo-restante .monto { font-size:1.4rem; font-weight:800; color:#2e50e4; }
.inp-pago { border-radius:8px; border:1.5px solid #e2e8f0; padding:7px 10px; width:100%; text-align:right; font-size:.85rem; }
.inp-pago:focus { border-color:#48bb78; outline:none; }
</style>
@endsection

@section('contenido')
<div class="container-fluid">
<div class="factura-wrap">

    {{-- Barra de acciones --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <a href="{{ route('facturas.index') }}" class="btn btn-secondary" style="border-radius:12px;font-weight:700;">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
        <div style="display:flex;gap:10px;">
            <span class="badge-est badge-{{ $factura->estado }}">{{ $factura->estado }}</span>
            @if($factura->estado !== 'PAGADA' && $factura->estado !== 'ANULADA')
            <button class="btn btn-success" data-toggle="modal" data-target="#modalPago"
                    style="border-radius:12px;font-weight:700;">
                <i class="fa fa-dollar-sign"></i> Registrar Pago
            </button>
            @endif
            <a href="{{ route('facturas.pdf', $factura->id) }}" class="btn btn-primary"
               style="border-radius:12px;font-weight:700;" target="_blank">
                <i class="fa fa-file-pdf"></i> Descargar PDF
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary"
                    style="border-radius:12px;font-weight:700;">
                <i class="fa fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    {{-- FACTURA --}}
    <div class="fact-header">
        <div>
            <div class="empresa">EMPRESA DE AGUA Y ALCANTARILLADO</div>
            <div class="subempresa">Servicio Público Domiciliario</div>
            <div style="margin-top:12px;font-size:.78rem;opacity:.8;">
                {{ $factura->mes_cuenta }}<br>
                Del {{ \Carbon\Carbon::parse($factura->fecha_del)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($factura->fecha_hasta)->format('d/m/Y') }}
            </div>
        </div>
        <div class="num-factura">
            <div class="lbl">Factura N°</div>
            <div class="num">{{ $factura->numero_factura }}</div>
            <div style="font-size:.78rem;opacity:.8;margin-top:8px;">
                Expide: {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }}<br>
                Vence: {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}<br>
                Corte: {{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}
            </div>
            @if($factura->es_automatica)
                <span style="background:rgba(255,255,255,.25);border-radius:8px;padding:3px 10px;font-size:.68rem;font-weight:700;margin-top:6px;display:inline-block;">AUTO</span>
            @else
                <span style="background:rgba(255,200,0,.3);border-radius:8px;padding:3px 10px;font-size:.68rem;font-weight:700;margin-top:6px;display:inline-block;">MANUAL</span>
            @endif
        </div>
    </div>

    <div class="fact-body">

        {{-- INFO CLIENTE / PREDIO --}}
        <div class="fact-section">
            <h6><i class="fa fa-user"></i> Datos del Suscriptor</h6>
            <div class="info-grid">
                <div class="info-item">
                    <div class="lbl">Suscriptor</div>
                    <div class="val">{{ $factura->suscriptor }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Nombre</div>
                    <div class="val">{{ trim($factura->cliente->nombre . ' ' . $factura->cliente->apellido) }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Dirección</div>
                    <div class="val">{{ $factura->cliente->direccion ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Estrato</div>
                    <div class="val">E{{ $factura->estrato_snapshot ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Tipo de Uso</div>
                    <div class="val">{{ $factura->clase_uso ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Servicios</div>
                    <div class="val">{{ $factura->servicios_snapshot }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Serie Medidor</div>
                    <div class="val">{{ $factura->serie_medidor ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Sector</div>
                    <div class="val">{{ $factura->sector ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- LECTURA Y PROMEDIO --}}
        <div class="fact-section">
            <h6><i class="fa fa-tachometer-alt"></i> Lectura del Período</h6>
            <div style="display:flex;gap:32px;flex-wrap:wrap;">
                <div class="info-item">
                    <div class="lbl">Lectura Anterior</div>
                    <div class="val" style="font-size:1.1rem;">{{ $factura->lectura_anterior ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Lectura Actual</div>
                    <div class="val" style="font-size:1.1rem;">{{ $factura->lectura_actual ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Consumo del Período</div>
                    <div class="val" style="font-size:1.4rem;color:#2e50e4;font-weight:900;">{{ $factura->consumo_m3 }} m³</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Promedio 6 meses</div>
                    <div class="val" style="font-size:1.1rem;">{{ $factura->promedio_consumo_snapshot }} m³</div>
                </div>
            </div>
            {{-- Barras de promedio --}}
            @php
                $meses = array_filter([$factura->prom_m1,$factura->prom_m2,$factura->prom_m3,$factura->prom_m4,$factura->prom_m5,$factura->prom_m6], fn($v) => !is_null($v));
                $maxM  = max(array_merge($meses, [1]));
            @endphp
            @if(count($meses) > 0)
            <div class="prom-grid" style="margin-top:14px;">
                @foreach($meses as $i => $m)
                <div class="prom-bar">
                    <div class="barra" style="height:{{ max(4, round(($m/$maxM)*50)) }}px;"></div>
                    <div class="num">{{ $m }}</div>
                    <div class="mes">M-{{ count($meses)-$i }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ACUEDUCTO --}}
        @if(in_array($factura->servicios_snapshot, ['AG','AG-AL']))
        <div class="fact-section">
            <h6><i class="fa fa-tint" style="color:#3d57ce;"></i> Acueducto</h6>
            <table class="tabla-fact">
                <thead><tr><th>Concepto</th><th>m³</th><th>Valor</th></tr></thead>
                <tbody>
                    <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ number_format($factura->cargo_fijo_acueducto,0,',','.') }}</td></tr>
                    <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_basico_acueducto_valor,0,',','.') }}</td></tr>
                    @if($factura->consumo_complementario_acueducto_m3 > 0)
                    <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_complementario_acueducto_valor,0,',','.') }}</td></tr>
                    @endif
                    @if($factura->consumo_suntuario_acueducto_m3 > 0)
                    <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_suntuario_acueducto_valor,0,',','.') }}</td></tr>
                    @endif
                    @if($factura->subsidio_emergencia != 0)
                    <tr><td>Subsidio de Emergencia</td><td>—</td><td>- $ {{ number_format($factura->subsidio_emergencia,0,',','.') }}</td></tr>
                    @endif
                    @if($factura->otros_cobros_acueducto > 0)
                    <tr><td>Otros Cobros — Cuota {{ $factura->otros_cobros_acueducto>0?'':'0' }}</td><td>—</td><td>$ {{ number_format($factura->cuota_otros_cobros_acueducto,0,',','.') }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr><td colspan="2"><strong>Total Acueducto</strong></td><td><strong>$ {{ number_format($factura->subtotal_conexion_otros_acueducto,0,',','.') }}</strong></td></tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- ALCANTARILLADO --}}
        @if(in_array($factura->servicios_snapshot, ['AL','AG-AL']))
        <div class="fact-section">
            <h6><i class="fa fa-water" style="color:#3d57ce;"></i> Alcantarillado</h6>
            <table class="tabla-fact">
                <thead><tr><th>Concepto</th><th>m³</th><th>Valor</th></tr></thead>
                <tbody>
                    <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ number_format($factura->cargo_fijo_alcantarillado,0,',','.') }}</td></tr>
                    <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_basico_alcantarillado_valor,0,',','.') }}</td></tr>
                    @if($factura->consumo_complementario_alcantarillado_m3 > 0)
                    <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_complementario_alcantarillado_valor,0,',','.') }}</td></tr>
                    @endif
                    @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
                    <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_suntuario_alcantarillado_valor,0,',','.') }}</td></tr>
                    @endif
                    @if($factura->otros_cobros_alcantarillado > 0)
                    <tr><td>Otros Cobros — Cuota</td><td>—</td><td>$ {{ number_format($factura->cuota_otros_cobros_alcantarillado,0,',','.') }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr><td colspan="2"><strong>Total Alcantarillado</strong></td><td><strong>$ {{ number_format($factura->subtotal_conexion_otros_alcantarillado,0,',','.') }}</strong></td></tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- SALDO ANTERIOR Y MORA --}}
        @if($factura->saldo_anterior > 0)
        <div class="fact-section" style="background:#fff5f5;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <span style="font-weight:700;color:#e53e3e;font-size:.88rem;"><i class="fa fa-exclamation-triangle"></i> Saldo Anterior en Mora</span>
                    <div style="font-size:.78rem;color:#718096;margin-top:4px;">{{ $factura->facturas_en_mora }} factura(s) pendientes de períodos anteriores.</div>
                </div>
                <span style="font-size:1.2rem;font-weight:800;color:#e53e3e;">$ {{ number_format($factura->saldo_anterior,0,',','.') }}</span>
            </div>
        </div>
        @endif

        {{-- TOTAL FINAL --}}
        <div style="padding:20px 32px;">
            <div class="total-final">
                <div>
                    <div class="lbl">TOTAL A PAGAR</div>
                    <div style="font-size:.72rem;opacity:.7;">Incluye todos los conceptos del período</div>
                </div>
                <div class="val">$ {{ number_format($factura->total_a_pagar,0,',','.') }}</div>
            </div>
        </div>

        {{-- PAGOS REGISTRADOS --}}
        <div class="fact-section">
            <h6><i class="fa fa-check-circle" style="color:#48bb78;"></i> Pagos Registrados</h6>
            @php
                $totalPagado  = $factura->pagos->sum('total_pago_realizado');
                $saldoPendiente = max(0, $factura->total_a_pagar - $totalPagado);
            @endphp
            @forelse($factura->pagos as $p)
            <div class="pago-item">
                <div>
                    <div class="recibo">
                        <i class="fa fa-receipt"></i>
                        {{ $p->numero_recibo ? 'Recibo: ' . $p->numero_recibo : 'Sin número' }}
                        — {{ $p->medio_pago }}
                    </div>
                    <div class="fechas">{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</div>
                </div>
                <div class="monto">+ $ {{ number_format($p->total_pago_realizado,0,',','.') }}</div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#a0aec0;font-size:.85rem;">
                <i class="fa fa-coins" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                Sin pagos registrados.
            </div>
            @endforelse

            @if($totalPagado > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;background:#f0f4ff;border-radius:12px;padding:14px 18px;margin-top:12px;">
                <div>
                    <div style="font-size:.75rem;color:#4a5568;font-weight:700;text-transform:uppercase;">Total Pagado</div>
                    <div style="font-size:1.1rem;font-weight:800;color:#22543d;">$ {{ number_format($totalPagado,0,',','.') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.75rem;color:#4a5568;font-weight:700;text-transform:uppercase;">Saldo Pendiente</div>
                    <div style="font-size:1.1rem;font-weight:800;color:{{ $saldoPendiente > 0 ? '#e53e3e' : '#22543d' }};">
                        $ {{ number_format($saldoPendiente,0,',','.') }}
                        @if($saldoPendiente <= 0) <i class="fa fa-check-circle"></i> @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>{{-- end fact-body --}}
</div>{{-- end factura-wrap --}}
</div>

{{-- MODAL REGISTRAR PAGO --}}
<div class="modal fade modal-pago" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-dollar-sign"></i> Registrar Pago</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha de Pago <span style="color:red">*</span></label>
                            <input type="date" class="form-control" id="pFecha" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Medio de Pago <span style="color:red">*</span></label>
                            <select class="form-control" id="pMedio">
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="CONSIGNACION">Consignación</option>
                                <option value="DATAFONO">Datáfono</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>N° de Recibo</label>
                    <input type="text" class="form-control" id="pRecibo" placeholder="Número de recibo o comprobante">
                </div>

                <div style="background:#f7fafc;border-radius:12px;padding:16px;margin-bottom:14px;">
                    <div style="font-weight:700;font-size:.8rem;text-transform:uppercase;color:#4a5568;margin-bottom:12px;">Desglose del pago</div>
                    <div class="row">
                        <div class="col-6">
                            <label style="font-size:.78rem;color:#718096;">Acueducto</label>
                            <input type="number" class="inp-pago" id="pAcueducto" min="0" step="1" placeholder="0"
                                   value="{{ $factura->subtotal_conexion_otros_acueducto }}">
                        </div>
                        <div class="col-6">
                            <label style="font-size:.78rem;color:#718096;">Alcantarillado</label>
                            <input type="number" class="inp-pago" id="pAlcantarillado" min="0" step="1" placeholder="0"
                                   value="{{ $factura->subtotal_conexion_otros_alcantarillado }}">
                        </div>
                    </div>
                    <div class="row" style="margin-top:8px;">
                        <div class="col-6">
                            <label style="font-size:.78rem;color:#718096;">Otros cobros acueducto</label>
                            <input type="number" class="inp-pago" id="pOtrosAc" min="0" step="1" placeholder="0" value="0">
                        </div>
                        <div class="col-6">
                            <label style="font-size:.78rem;color:#718096;">Otros cobros alcantarillado</label>
                            <input type="number" class="inp-pago" id="pOtrosAlc" min="0" step="1" placeholder="0" value="0">
                        </div>
                    </div>
                </div>

                <div class="saldo-restante">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#718096;">Total del pago</div>
                            <div class="monto" id="totalPagoCalc">$ 0</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#718096;">Saldo pendiente</div>
                            <div style="font-size:1rem;font-weight:800;color:#e53e3e;" id="saldoQuedaraCalc">
                                $ {{ number_format($saldoPendiente ?? $factura->total_a_pagar,0,',','.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:14px;">
                    <label>Observaciones</label>
                    <textarea class="form-control" id="pObs" rows="2" style="border-radius:10px;border:2px solid #e2e8f0;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:2px solid #e2e8f0;">
                <button class="btn btn-secondary" data-dismiss="modal" style="border-radius:12px;">Cancelar</button>
                <button class="btn btn-pagar" id="btnConfirmarPago">
                    <i class="fa fa-dollar-sign"></i> Registrar Pago
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var CSRF         = $("meta[name='csrf-token']").attr("content");
var totalFactura = {{ $factura->total_a_pagar }};
var totalPagado  = {{ $factura->pagos->sum('total_pago_realizado') }};
var saldoActual  = Math.max(0, totalFactura - totalPagado);

function fmt(n) { return '$ ' + Math.abs(parseFloat(n)||0).toLocaleString('es-CO',{minimumFractionDigits:0,maximumFractionDigits:0}); }

// ── Calcular total de pago en tiempo real ──────────────────────────────────
function recalcularPago() {
    var total = (parseFloat($('#pAcueducto').val())||0)
              + (parseFloat($('#pAlcantarillado').val())||0)
              + (parseFloat($('#pOtrosAc').val())||0)
              + (parseFloat($('#pOtrosAlc').val())||0);
    var quedar = Math.max(0, saldoActual - total);
    $('#totalPagoCalc').text(fmt(total));
    $('#saldoQuedaraCalc').text(fmt(quedar)).css('color', quedar <= 0 ? '#22543d' : '#e53e3e');
}

$('.inp-pago').on('input', recalcularPago);
recalcularPago();

// ── Confirmar pago ─────────────────────────────────────────────────────────
$('#btnConfirmarPago').on('click', function () {
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
    $.ajax({
        url:    '/facturacion/facturas/{{ $factura->id }}/pago',
        method: 'POST',
        data: {
            fecha_pago:                       $('#pFecha').val(),
            medio_pago:                       $('#pMedio').val(),
            numero_recibo:                    $('#pRecibo').val(),
            pagos_acueducto:                  $('#pAcueducto').val()  || 0,
            pagos_alcantarillado:             $('#pAlcantarillado').val() || 0,
            pago_otros_cobros_acueducto:      $('#pOtrosAc').val()   || 0,
            pago_otros_cobros_alcantarillado: $('#pOtrosAlc').val()   || 0,
            observaciones:                    $('#pObs').val(),
            _token: CSRF
        },
        success: function (r) {
            if (r.ok) {
                $('#modalPago').modal('hide');
                Manteliviano.notificaciones(r.mensaje, 'Facturación', 'success');
                setTimeout(() => location.reload(), 1200);
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html('<i class="fa fa-dollar-sign"></i> Registrar Pago');
            Swal.fire('Error', xhr.responseJSON?.mensaje || 'No se pudo registrar el pago.', 'error');
        }
    });
});
</script>
@endsection
