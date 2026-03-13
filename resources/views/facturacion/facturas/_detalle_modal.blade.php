{{--
    Vista parcial: detalle de factura para mostrar dentro de un modal.
    No usa @extends — devuelve solo el fragmento HTML.
--}}
@php $nf = fn($v) => number_format((float)($v ?? 0), 0, ',', '.'); @endphp

<style>
/* Scoped al contenedor .factura-modal-wrap ────────────────────────────── */
.factura-modal-wrap .fact-header {
    background: linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%);
    border-radius: 16px 16px 0 0;
    padding: 22px 28px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.factura-modal-wrap .fact-header .empresa    { font-size:1.1rem; font-weight:800; letter-spacing:.5px; }
.factura-modal-wrap .fact-header .subempresa { font-size:.75rem; opacity:.8; margin-top:4px; }
.factura-modal-wrap .fact-header .num        { font-size:1.6rem; font-weight:900; letter-spacing:1px; }
.factura-modal-wrap .fact-header .lbl-sm     { font-size:.68rem; opacity:.75; text-transform:uppercase; }

.factura-modal-wrap .fact-body {
    background: white;
    border-radius: 0 0 16px 16px;
    overflow: hidden;
}
.factura-modal-wrap .fact-section {
    padding: 16px 24px;
    border-bottom: 2px solid #f0f0f0;
}
.factura-modal-wrap .fact-section:last-child { border-bottom: none; }
.factura-modal-wrap .fact-section h6 {
    font-weight: 800;
    color: #4a5568;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 2px solid #e2e8f0;
}
.factura-modal-wrap .info-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 10px;
}
.factura-modal-wrap .info-item .lbl {
    font-size: .66rem;
    color: #a0aec0;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .4px;
}
.factura-modal-wrap .info-item .val {
    font-size: .85rem;
    font-weight: 600;
    color: #2d3748;
    margin-top: 2px;
}
.factura-modal-wrap .tabla-fact {
    width: 100%;
    border-collapse: collapse;
    font-size: .82rem;
}
.factura-modal-wrap .tabla-fact thead th {
    background: #f7fafc;
    padding: 8px 10px;
    font-weight: 700;
    color: #4a5568;
    font-size: .7rem;
    text-transform: uppercase;
    text-align: right;
}
.factura-modal-wrap .tabla-fact thead th:first-child { text-align: left; }
.factura-modal-wrap .tabla-fact tbody td {
    padding: 7px 10px;
    border-bottom: 1px solid #f5f5f5;
    text-align: right;
}
.factura-modal-wrap .tabla-fact tbody td:first-child { text-align: left; color:#4a5568; }
.factura-modal-wrap .tabla-fact tfoot td {
    padding: 9px 10px;
    font-weight: 700;
    font-size: .85rem;
    border-top: 2px solid #e2e8f0;
    text-align: right;
}
.factura-modal-wrap .tabla-fact tfoot td:first-child { text-align: left; }
.factura-modal-wrap .total-final {
    background: linear-gradient(135deg,#2e50e4,#2b0c49);
    border-radius: 12px;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 24px 18px;
    color: white;
}
.factura-modal-wrap .total-final .lbl { font-size:.82rem; font-weight:600; opacity:.9; }
.factura-modal-wrap .total-final .val { font-size:1.8rem; font-weight:900; }
.factura-modal-wrap .badge-PENDIENTE { background:#fef3c7; color:#92400e; }
.factura-modal-wrap .badge-PAGADA    { background:#c6f6d5; color:#22543d; }
.factura-modal-wrap .badge-VENCIDA   { background:#fed7d7; color:#742a2a; }
.factura-modal-wrap .badge-ANULADA   { background:#e2e8f0; color:#718096; }
.factura-modal-wrap .badge-est {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 800;
}
.factura-modal-wrap .prom-grid { display:flex; gap:6px; align-items:flex-end; }
.factura-modal-wrap .prom-bar  { display:flex; flex-direction:column; align-items:center; gap:2px; }
.factura-modal-wrap .prom-bar .barra {
    background: linear-gradient(180deg,#667eea,#764ba2);
    border-radius: 4px 4px 0 0;
    width: 28px;
    min-height: 4px;
}
.factura-modal-wrap .prom-bar .num { font-size:.65rem; font-weight:700; color:#4a5568; }
.factura-modal-wrap .prom-bar .mes { font-size:.6rem; color:#a0aec0; }
.factura-modal-wrap .pago-item {
    background: #f7fafc;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.factura-modal-wrap .pago-item .recibo { font-size:.78rem; font-weight:700; color:#4a5568; }
.factura-modal-wrap .pago-item .fechas { font-size:.75rem; color:#718096; }
.factura-modal-wrap .pago-item .monto  { font-weight:800; color:#22543d; }
</style>

<div class="factura-modal-wrap">

    {{-- Header factura --}}
    <div class="fact-header">
        <div>
            <div class="empresa">ACUEDUCTO ALTO LOS MANGOS</div>
            <div class="subempresa">Servicio Público Domiciliario</div>
            <div style="margin-top:10px;font-size:.76rem;opacity:.8;">
                {{ $factura->mes_cuenta }}<br>
                Del {{ $factura->fecha_del ? $factura->fecha_del->format('d/m/Y') : '—' }}
                al {{ $factura->fecha_hasta ? $factura->fecha_hasta->format('d/m/Y') : '—' }}
            </div>
        </div>
        <div style="text-align:right;">
            <div class="lbl-sm">Factura N°</div>
            <div class="num">{{ $factura->numero_factura }}</div>
            <div style="font-size:.75rem;opacity:.8;margin-top:6px;">
                Expide: {{ $factura->fecha_expedicion ? $factura->fecha_expedicion->format('d/m/Y') : '—' }}<br>
                Vence: {{ $factura->fecha_vencimiento ? $factura->fecha_vencimiento->format('d/m/Y') : '—' }}
            </div>
            <span class="badge-est badge-{{ $factura->estado }}" style="margin-top:6px;">{{ $factura->estado }}</span>
            @if($factura->es_automatica)
                <span style="background:rgba(255,255,255,.22);border-radius:6px;padding:2px 8px;font-size:.65rem;font-weight:700;margin-left:4px;display:inline-block;">AUTO</span>
            @else
                <span style="background:rgba(255,200,0,.3);border-radius:6px;padding:2px 8px;font-size:.65rem;font-weight:700;margin-left:4px;display:inline-block;">MANUAL</span>
            @endif
        </div>
    </div>

    <div class="fact-body">

        {{-- Datos del suscriptor --}}
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

        {{-- Lectura y promedio --}}
        <div class="fact-section">
            <h6><i class="fa fa-tachometer-alt"></i> Lectura del Período</h6>
            <div style="display:flex;gap:28px;flex-wrap:wrap;">
                <div class="info-item">
                    <div class="lbl">Lectura Anterior</div>
                    <div class="val" style="font-size:1rem;">{{ $factura->lectura_anterior ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Lectura Actual</div>
                    <div class="val" style="font-size:1rem;">{{ $factura->lectura_actual ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Consumo del Período</div>
                    <div class="val" style="font-size:1.3rem;color:#2e50e4;font-weight:900;">{{ $factura->consumo_m3 }} m³</div>
                </div>
                <div class="info-item">
                    <div class="lbl">Promedio 6 meses</div>
                    <div class="val" style="font-size:1rem;">{{ $factura->promedio_consumo_snapshot }} m³</div>
                </div>
            </div>
            @php
                if (!empty($histConsumos)) {
                    $chartData = $histConsumos;
                } else {
                    $rawMeses = array_values(array_filter(
                        [$factura->prom_m6,$factura->prom_m5,$factura->prom_m4,$factura->prom_m3,$factura->prom_m2,$factura->prom_m1],
                        fn($v) => !is_null($v)
                    ));
                    $chartData = array_map(fn($v, $i) => [
                        'label'      => 'M-' . (count($rawMeses) - $i),
                        'consumo_m3' => (float) $v,
                        'isCurrent'  => $i === count($rawMeses) - 1,
                    ], $rawMeses, array_keys($rawMeses));
                }
                $maxM = max(array_merge(array_column($chartData, 'consumo_m3'), [1]));
            @endphp
            @if(count($chartData) > 0)
            <div class="prom-grid" style="margin-top:12px;">
                @foreach($chartData as $bar)
                @php $isCurrent = $bar['isCurrent'] ?? false; @endphp
                <div class="prom-bar">
                    <div class="barra" style="height:{{ max(4, round(($bar['consumo_m3']/$maxM)*44)) }}px;{{ $isCurrent ? 'background:#2e50e4;' : '' }}"></div>
                    <div class="num" style="{{ $isCurrent ? 'color:#2e50e4;font-weight:700;' : '' }}">{{ $bar['consumo_m3'] }}</div>
                    <div class="mes" style="{{ $isCurrent ? 'font-weight:700;' : '' }}">{{ $bar['label'] }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Acueducto --}}
        @if(in_array($factura->servicios_snapshot, ['AG','AG-AL']))
        @php
            $refBasAcShow  = ($factura->consumo_basico_acueducto_m3 > 0) ? round($factura->consumo_basico_acueducto_valor / $factura->consumo_basico_acueducto_m3, 2) : 0;
            $refCompAcShow = ($factura->consumo_complementario_acueducto_m3 > 0) ? round($factura->consumo_complementario_acueducto_valor / $factura->consumo_complementario_acueducto_m3, 2) : 0;
            $refSuntAcShow = ($factura->consumo_suntuario_acueducto_m3 > 0) ? round($factura->consumo_suntuario_acueducto_valor / $factura->consumo_suntuario_acueducto_m3, 2) : 0;
            $esSubsidio    = ($factura->subsidio_emergencia ?? 0) > 0;
        @endphp
        <div class="fact-section">
            <h6><i class="fa fa-tint" style="color:#3d57ce;"></i> Acueducto</h6>
            <table class="tabla-fact">
                <thead><tr><th>Concepto</th><th>m³</th><th>Tarifa</th><th>Sub Total</th></tr></thead>
                <tbody>
                    <tr><td>Cargo Fijo</td><td style="text-align:center;">—</td><td>$ {{ $nf($factura->cargo_fijo_acueducto) }}</td><td>$ {{ $nf($factura->cargo_fijo_acueducto) }}</td></tr>
                    <tr><td>Consumo Básico</td><td style="text-align:center;">{{ $factura->consumo_basico_acueducto_m3 }}</td><td>$ {{ $nf($refBasAcShow) }}</td><td>$ {{ $nf($factura->consumo_basico_acueducto_valor) }}</td></tr>
                    @if($factura->consumo_complementario_acueducto_m3 > 0)
                    <tr><td>Consumo Complementario</td><td style="text-align:center;">{{ $factura->consumo_complementario_acueducto_m3 }}</td><td>$ {{ $nf($refCompAcShow) }}</td><td>$ {{ $nf($factura->consumo_complementario_acueducto_valor) }}</td></tr>
                    @endif
                    @if($factura->consumo_suntuario_acueducto_m3 > 0)
                    <tr><td>Consumo Suntuario</td><td style="text-align:center;">{{ $factura->consumo_suntuario_acueducto_m3 }}</td><td>$ {{ $nf($refSuntAcShow) }}</td><td>$ {{ $nf($factura->consumo_suntuario_acueducto_valor) }}</td></tr>
                    @endif
                    @if(($factura->subsidio_emergencia ?? 0) != 0)
                    <tr style="color:{{ $esSubsidio ? '#166534' : '#991b1b' }};font-style:italic;">
                        <td colspan="3">{{ $esSubsidio ? 'Subsidio Estrato' : 'Contribución Estrato' }}</td>
                        <td>{{ $esSubsidio ? '- ' : '+ ' }}$ {{ $nf(abs($factura->subsidio_emergencia)) }}</td>
                    </tr>
                    @endif
                    <tr><td colspan="3">Otros Cobros — Cuota</td><td>$ {{ $nf($factura->cuota_otros_cobros_acueducto) }}</td></tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="3"><strong>Total Acueducto</strong></td><td><strong>$ {{ $nf($factura->subtotal_conexion_otros_acueducto) }}</strong></td></tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Alcantarillado --}}
        @if(in_array($factura->servicios_snapshot, ['AL','AG-AL']))
        @php
            $refBasAlShow  = ($factura->consumo_basico_alcantarillado_m3 > 0) ? round($factura->consumo_basico_alcantarillado_valor / $factura->consumo_basico_alcantarillado_m3, 2) : 0;
            $refCompAlShow = ($factura->consumo_complementario_alcantarillado_m3 > 0) ? round($factura->consumo_complementario_alcantarillado_valor / $factura->consumo_complementario_alcantarillado_m3, 2) : 0;
            $refSuntAlShow = ($factura->consumo_suntuario_alcantarillado_m3 > 0) ? round($factura->consumo_suntuario_alcantarillado_valor / $factura->consumo_suntuario_alcantarillado_m3, 2) : 0;
            $esSubAl       = ($factura->subsidio_alcantarillado ?? 0) > 0;
        @endphp
        <div class="fact-section">
            <h6><i class="fa fa-water" style="color:#3d57ce;"></i> Alcantarillado</h6>
            <table class="tabla-fact">
                <thead><tr><th>Concepto</th><th>m³</th><th>Tarifa</th><th>Sub Total</th></tr></thead>
                <tbody>
                    <tr><td>Cargo Fijo</td><td style="text-align:center;">—</td><td>$ {{ $nf($factura->cargo_fijo_alcantarillado) }}</td><td>$ {{ $nf($factura->cargo_fijo_alcantarillado) }}</td></tr>
                    <tr><td>Consumo Básico</td><td style="text-align:center;">{{ $factura->consumo_basico_alcantarillado_m3 }}</td><td>$ {{ $nf($refBasAlShow) }}</td><td>$ {{ $nf($factura->consumo_basico_alcantarillado_valor) }}</td></tr>
                    @if($factura->consumo_complementario_alcantarillado_m3 > 0)
                    <tr><td>Consumo Complementario</td><td style="text-align:center;">{{ $factura->consumo_complementario_alcantarillado_m3 }}</td><td>$ {{ $nf($refCompAlShow) }}</td><td>$ {{ $nf($factura->consumo_complementario_alcantarillado_valor) }}</td></tr>
                    @endif
                    @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
                    <tr><td>Consumo Suntuario</td><td style="text-align:center;">{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td><td>$ {{ $nf($refSuntAlShow) }}</td><td>$ {{ $nf($factura->consumo_suntuario_alcantarillado_valor) }}</td></tr>
                    @endif
                    @if(($factura->subsidio_alcantarillado ?? 0) != 0)
                    <tr style="color:{{ $esSubAl ? '#059669' : '#dc2626' }};font-style:italic;">
                        <td colspan="3">{{ $esSubAl ? 'Subsidio Alcantarillado' : 'Sobretasa Alcantarillado' }}</td>
                        <td>{{ $esSubAl ? '- ' : '+ ' }}$ {{ $nf(abs($factura->subsidio_alcantarillado)) }}</td>
                    </tr>
                    @endif
                    <tr><td colspan="3">Otros Cobros — Cuota</td><td>$ {{ $nf($factura->cuota_otros_cobros_alcantarillado) }}</td></tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="3"><strong>Total Alcantarillado</strong></td><td><strong>$ {{ $nf($factura->subtotal_conexion_otros_alcantarillado) }}</strong></td></tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Saldo anterior --}}
        <div class="fact-section" style="background:{{ $factura->saldo_anterior > 0 ? '#fff5f5' : '#f7fafc' }};">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-weight:700;color:{{ $factura->saldo_anterior > 0 ? '#e53e3e' : '#718096' }};font-size:.85rem;">
                    <i class="fa fa-{{ $factura->saldo_anterior > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                    Saldo Anterior en Mora
                </span>
                <span style="font-size:1.1rem;font-weight:800;color:{{ $factura->saldo_anterior > 0 ? '#e53e3e' : '#22543d' }};">
                    $ {{ $nf($factura->saldo_anterior) }}
                </span>
            </div>
        </div>

        {{-- Pagos --}}
        @php
            $totalPagado    = $factura->pagos->sum('total_pago_realizado');
            $saldoPendiente = max(0, $factura->total_a_pagar - $totalPagado);
        @endphp
        <div class="fact-section" style="background:{{ $totalPagado > 0 ? '#f0fdf4' : '#f7fafc' }};">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-weight:700;color:{{ $totalPagado > 0 ? '#166534' : '#718096' }};font-size:.85rem;">
                    <i class="fa fa-{{ $totalPagado > 0 ? 'check-circle' : 'coins' }}"></i>
                    Pagos Realizados
                </span>
                <span style="font-size:1.1rem;font-weight:800;color:{{ $totalPagado > 0 ? '#22543d' : '#a0aec0' }};">
                    $ {{ $nf($totalPagado) }}
                </span>
            </div>
        </div>

        {{-- Total a pagar --}}
        <div style="padding:16px 24px;">
            <div class="total-final">
                <div>
                    <div class="lbl">TOTAL A PAGAR</div>
                    <div style="font-size:.7rem;opacity:.7;">Incluye todos los conceptos del período</div>
                </div>
                <div class="val">$ {{ $nf($factura->total_a_pagar) }}</div>
            </div>
        </div>

        {{-- Detalle de pagos --}}
        @if($factura->pagos->count() > 0)
        <div class="fact-section">
            <h6><i class="fa fa-check-circle" style="color:#48bb78;"></i> Pagos Registrados</h6>
            @foreach($factura->pagos as $p)
            <div class="pago-item">
                <div>
                    <div class="recibo">
                        <i class="fa fa-receipt"></i>
                        {{ $p->numero_recibo ? 'Recibo: ' . $p->numero_recibo : 'Sin número' }}
                        — {{ $p->medio_pago }}
                    </div>
                    <div class="fechas">{{ $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : '—' }}</div>
                </div>
                <div class="monto">+ $ {{ $nf($p->total_pago_realizado) }}</div>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;align-items:center;background:#f0f4ff;border-radius:10px;padding:12px 16px;margin-top:8px;">
                <div>
                    <div style="font-size:.72rem;color:#4a5568;font-weight:700;text-transform:uppercase;">Saldo pendiente</div>
                    <div style="font-size:1.05rem;font-weight:800;color:{{ $saldoPendiente > 0 ? '#e53e3e' : '#22543d' }};">
                        $ {{ $nf($saldoPendiente) }}
                        @if($saldoPendiente <= 0) <i class="fa fa-check-circle"></i> @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- end fact-body --}}
</div>{{-- end factura-modal-wrap --}}
