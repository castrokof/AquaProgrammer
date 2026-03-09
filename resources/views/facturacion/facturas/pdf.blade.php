<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @page {
            size: letter;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2e50e4;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-area {
            width: 40%;
        }
        .logo-area img {
            max-width: 150px;
            height: auto;
        }
        .factura-info {
            width: 60%;
            text-align: right;
        }
        .factura-info h2 {
            margin: 0;
            color: #2e50e4;
            font-size: 18pt;
            text-transform: uppercase;
        }
        .info-row {
            margin-top: 5px;
            font-size: 9pt;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .section-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            color: #2e50e4;
            text-transform: uppercase;
            font-size: 9pt;
            margin-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        td.number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .totals-area {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .totals-table {
            width: 40%;
        }
        .totals-table td {
            padding: 6px 8px;
        }
        .totals-table .total-final {
            font-size: 12pt;
            font-weight: bold;
            color: #d63384;
            border-top: 2px solid #333;
            background-color: #fff3cd;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .barcode {
            text-align: center;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

@foreach($facturas as $factura)
@php
    // Helper para formatear valores monetarios que pueden ser null (datos importados)
    $nf = fn($v) => number_format((float)($v ?? 0), 0, ',', '.');
@endphp

@if(!$loop->first)
<div class="fact-separator"></div>
@endif

{{-- ── HEADER ── --}}
<div class="header">
    <div class="header-left">
        <div class="empresa">EMPRESA DE AGUA Y ALCANTARILLADO</div>
        <div class="sub-empresa">Servicio Público Domiciliario</div>
        <div class="periodo-info">
            {{ $factura->mes_cuenta }}&nbsp;&mdash;&nbsp;
            Del {{ $factura->fecha_del?->format('d/m/Y') ?? '—' }}
            al {{ $factura->fecha_hasta?->format('d/m/Y') ?? '—' }}
        </div>
    </div>
    <div class="header-right">
        <div class="num-factura-lbl">Factura N°</div>
        <div class="num-factura-val">{{ $factura->numero_factura }}</div>
        <div class="fecha-info">
            Expide: {{ $factura->fecha_expedicion?->format('d/m/Y') ?? '—' }}<br>
            Vence:&nbsp;&nbsp;{{ $factura->fecha_vencimiento?->format('d/m/Y') ?? '—' }}<br>
            Corte:&nbsp;&nbsp;{{ $factura->fecha_corte?->format('d/m/Y') ?? '—' }}
        </div>
        <div class="factura-info">
            <h2>FACTURA DE SERVICIO</h2>
            <div class="info-row">
                <span class="info-label">No. Factura:</span> {{ $factura->numero_factura }}
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Expedición:</span> {{ $factura->fecha_expedicion->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Vencimiento:</span> {{ $factura->fecha_vencimiento->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="info-label">Período:</span> {{ $factura->periodo }}
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="section-box">
        <div class="section-title">Información del Suscriptor</div>
        <table style="margin-bottom:0;">
            <tr>
                <td style="width: 20%;"><strong>Suscriptor:</strong></td>
                <td>{{ $factura->suscriptor }}</td>
                <td style="width: 20%;"><strong>Sector:</strong></td>
                <td>{{ $factura->sector ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Nombre:</strong></td>
                <td colspan="3">{{ $factura->cliente->nombre ?? '' }} {{ $factura->cliente->apellido ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td colspan="3">{{ $factura->cliente->direccion ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Estrato:</strong></td>
                <td>{{ $factura->estrato_snapshot ?? 'N/A' }}</td>
                <td><strong>Tipo Uso:</strong></td>
                <td>{{ $factura->clase_uso ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Detalles de Lectura -->
    <div class="section-box">
        <div class="section-title">Detalles de Lectura y Consumo</div>
        <table style="margin-bottom:0;">
            <thead>
                <tr>
                    <th>Anterior</th>
                    <th>Actual</th>
                    <th>Consumo (m³)</th>
                    <th>Días</th>
                    <th>Promedio 6 meses</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cargo Fijo</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ $nf($factura->cargo_fijo_acueducto) }}</td>
                </tr>
                <tr>
                    <td>Consumo Básico</td>
                    <td class="right">{{ $factura->consumo_basico_acueducto_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_basico_acueducto_valor) }}</td>
                </tr>
                @if($factura->consumo_complementario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Complementario</td>
                    <td class="right">{{ $factura->consumo_complementario_acueducto_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_complementario_acueducto_valor) }}</td>
                </tr>
                @endif
                @if($factura->consumo_suntuario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Suntuario</td>
                    <td class="right">{{ $factura->consumo_suntuario_acueducto_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_suntuario_acueducto_valor) }}</td>
                </tr>
                @endif
                @if($factura->subsidio_emergencia != 0)
                @php $esSubsidio = $factura->subsidio_emergencia > 0; @endphp
                <tr>
                    <td>{{ $esSubsidio ? 'Subsidio Estrato' : 'Contribución Estrato' }}</td>
                    <td class="right">—</td>
                    <td class="right" style="color:{{ $esSubsidio ? '#166534' : '#991b1b' }};">
                        {{ $esSubsidio ? '- ' : '+ ' }}$ {{ $nf(abs($factura->subsidio_emergencia)) }}
                    </td>
                </tr>
                @endif
                @if($factura->cuota_otros_cobros_acueducto > 0)
                <tr>
                    <td>Otros Cobros Acueducto</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ $nf($factura->cuota_otros_cobros_acueducto) }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total Acueducto</strong></td>
                    <td class="right"><strong>$ {{ $nf($factura->subtotal_conexion_otros_acueducto) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Desglose de Valores -->
    <div class="section-box">
        <div class="section-title">Desglose de Valores</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Consumo (m³)</th>
                    <th>Valor Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Acueducto Básico -->
                @if($factura->consumo_basico_acueducto_m3 > 0)
                <tr>
                    <td>Cargo Fijo</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ $nf($factura->cargo_fijo_alcantarillado) }}</td>
                </tr>
                @endif
                
                <!-- Acueducto Complementario -->
                @if($factura->consumo_complementario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Básico</td>
                    <td class="right">{{ $factura->consumo_basico_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_basico_alcantarillado_valor) }}</td>
                </tr>
                @endif

                <!-- Acueducto Suntuario -->
                @if($factura->consumo_suntuario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Complementario</td>
                    <td class="right">{{ $factura->consumo_complementario_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_complementario_alcantarillado_valor) }}</td>
                </tr>
                @endif

                <!-- Cargo Fijo Acueducto -->
                @if($factura->cargo_fijo_acueducto > 0)
                <tr>
                    <td>Consumo Suntuario</td>
                    <td class="right">{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ $nf($factura->consumo_suntuario_alcantarillado_valor) }}</td>
                </tr>
                @endif

                <!-- Alcantarillado (Resumido o desglosado similar si se requiere) -->
                <tr>
                    <td>Otros Cobros Alcantarillado</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ $nf($factura->cuota_otros_cobros_alcantarillado) }}</td>
                </tr>

                <!-- Otros Cobros -->
                @php
                    $otrosTotal = ($factura->otros_cobros_acueducto + $factura->otros_cobros_alcantarillado + 
                                  $factura->cuota_otros_cobros_acueducto + $factura->cuota_otros_cobros_alcantarillado);
                @endphp
                @if($otrosTotal > 0)
                <tr>
                    <td colspan="2"><strong>Total Alcantarillado</strong></td>
                    <td class="right"><strong>$ {{ $nf($factura->subtotal_conexion_otros_alcantarillado) }}</strong></td>
                </tr>
                @endif

    {{-- SALDO ANTERIOR --}}
    @if($factura->saldo_anterior > 0)
    <div class="section mora-box">
        <div class="mora-title">&#9888; Saldo Anterior en Mora &mdash; {{ $factura->facturas_en_mora }} factura(s) pendiente(s)</div>
        <div class="mora-monto">$ {{ $nf($factura->saldo_anterior) }}</div>
    </div>

    {{-- TOTAL FINAL --}}
    <div class="section" style="background:#f9fafb;">
        <div class="total-final">
            <div class="total-final-left">
                <div class="total-lbl">TOTAL A PAGAR</div>
                <div class="total-sub">Incluye todos los conceptos del período</div>
            </div>
            <div class="total-final-right">
                <div class="total-val">$ {{ $nf($factura->total_a_pagar) }}</div>
            </div>
        </div>
    </div>

    {{-- PAGOS --}}
    @php
        $totalPagado    = $factura->pagos->sum('total_pago_realizado');
        $saldoPendiente = max(0, $factura->total_a_pagar - $totalPagado);
    @endphp
    @if($factura->pagos->count() > 0)
    <div class="section">
        <div class="section-title">&#10003; Pagos Registrados</div>
        @foreach($factura->pagos as $p)
        <div class="pago-row">
            <div class="pago-left">
                <div class="pago-recibo">{{ $p->numero_recibo ? 'Recibo: '.$p->numero_recibo : 'Sin número' }} — {{ $p->medio_pago }}</div>
                <div class="pago-fecha">{{ $p->fecha_pago?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="pago-right">
                <div class="pago-monto">+ $ {{ $nf($p->total_pago_realizado) }}</div>
            </div>
        </div>
        @endforeach
        <div class="resumen-saldo">
            <div class="resumen-item">
                <div class="resumen-lbl">Total Pagado</div>
                <div class="resumen-val" style="color:#166534;">$ {{ $nf($totalPagado) }}</div>
            </div>
            <div class="resumen-item">
                <div class="resumen-lbl">Saldo Pendiente</div>
                <div class="resumen-val" style="color:{{ $saldoPendiente > 0 ? '#dc2626' : '#166534' }};">
                    $ {{ $nf($saldoPendiente) }}
                </div>
            </div>
        </div>
    </div>

</body>
</html>
