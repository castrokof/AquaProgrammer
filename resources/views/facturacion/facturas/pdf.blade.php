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

    <!-- Encabezado -->
    <div class="header">
        <div class="logo-area">
            {{-- Reemplaza con la ruta real de tu logo o usa texto si no hay imagen --}}
            <h3 style="margin:0; color:#2e50e4;">ACUEDUCTO MUNICIPAL</h3>
            <p style="margin:2px 0; font-size:8pt;">NIT: 900.123.456-7</p>
            <p style="margin:2px 0; font-size:8pt;">Dirección Principal #123</p>
            <p style="margin:2px 0; font-size:8pt;">Tel: (601) 123-4567</p>
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
                    <td class="number">{{ number_format($factura->lectura_anterior, 2) }}</td>
                    <td class="number">{{ number_format($factura->lectura_actual, 2) }}</td>
                    <td class="number" style="font-weight:bold; color:#2e50e4;">{{ number_format($factura->consumo_m3, 2) }}</td>
                    <td class="number">{{ $factura->dias_facturados }}</td>
                    <td class="number">{{ number_format($factura->promedio_consumo_snapshot ?? 0, 2) }}</td>
                </tr>
            </tbody>
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
                    <td>Acueducto (Básico)</td>
                    <td class="number">{{ number_format($factura->consumo_basico_acueducto_m3, 2) }}</td>
                    <td class="number">$ {{ number_format($factura->tarifaPeriodo->valor_basico_acueducto ?? 0, 0) }}</td>
                    <td class="number">$ {{ number_format($factura->consumo_basico_acueducto_valor, 0) }}</td>
                </tr>
                @endif
                
                <!-- Acueducto Complementario -->
                @if($factura->consumo_complementario_acueducto_m3 > 0)
                <tr>
                    <td>Acueducto (Complementario)</td>
                    <td class="number">{{ number_format($factura->consumo_complementario_acueducto_m3, 2) }}</td>
                    <td class="number">$ {{ number_format($factura->tarifaPeriodo->valor_complementario_acueducto ?? 0, 0) }}</td>
                    <td class="number">$ {{ number_format($factura->consumo_complementario_acueducto_valor, 0) }}</td>
                </tr>
                @endif

                <!-- Acueducto Suntuario -->
                @if($factura->consumo_suntuario_acueducto_m3 > 0)
                <tr>
                    <td>Acueducto (Suntuario)</td>
                    <td class="number">{{ number_format($factura->consumo_suntuario_acueducto_m3, 2) }}</td>
                    <td class="number">$ {{ number_format($factura->tarifaPeriodo->valor_suntuario_acueducto ?? 0, 0) }}</td>
                    <td class="number">$ {{ number_format($factura->consumo_suntuario_acueducto_valor, 0) }}</td>
                </tr>
                @endif

                <!-- Cargo Fijo Acueducto -->
                @if($factura->cargo_fijo_acueducto > 0)
                <tr>
                    <td>Cargo Fijo Acueducto</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="number">$ {{ number_format($factura->cargo_fijo_acueducto, 0) }}</td>
                </tr>
                @endif

                <!-- Alcantarillado (Resumido o desglosado similar si se requiere) -->
                <tr>
                    <td>Alcantarillado (Total)</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="number">$ {{ number_format($factura->subtotal_alcantarillado, 0) }}</td>
                </tr>

                <!-- Otros Cobros -->
                @php
                    $otrosTotal = ($factura->otros_cobros_acueducto + $factura->otros_cobros_alcantarillado + 
                                  $factura->cuota_otros_cobros_acueducto + $factura->cuota_otros_cobros_alcantarillado);
                @endphp
                @if($otrosTotal > 0)
                <tr>
                    <td>Otros Cobros / Conexiones</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="number">$ {{ number_format($otrosTotal, 0) }}</td>
                </tr>
                @endif

                <!-- Saldo Anterior -->
                @if($factura->saldo_anterior > 0)
                <tr style="background-color: #fff3cd;">
                    <td><strong>Saldo Anterior</strong></td>
                    <td>-</td>
                    <td>-</td>
                    <td class="number"><strong>$ {{ number_format($factura->saldo_anterior, 0) }}</strong></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Totales -->
    <div class="totals-area">
        <table class="totals-table">
            <tr>
                <td>Subtotal Acueducto:</td>
                <td class="number">$ {{ number_format($factura->total_facturacion_acueducto, 0) }}</td>
            </tr>
            <tr>
                <td>Subtotal Alcantarillado:</td>
                <td class="number">$ {{ number_format($factura->subtotal_alcantarillado, 0) }}</td>
            </tr>
            @if($factura->saldo_anterior > 0)
            <tr>
                <td>Saldo Anterior:</td>
                <td class="number">$ {{ number_format($factura->saldo_anterior, 0) }}</td>
            </tr>
            @endif
            <tr class="total-final">
                <td>TOTAL A PAGAR:</td>
                <td class="number">$ {{ number_format($factura->total_a_pagar, 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Esta es una representación impresa de la factura electrónica o equivalente según la normativa vigente.</p>
        <p>En caso de discrepancia en la lectura, por favor comuníquese a nuestras oficinas antes de la fecha de corte.</p>
        <div class="barcode">
            ||||| {{ $factura->numero_factura }} |||||
        </div>
    </div>

</body>
</html>
