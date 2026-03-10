<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @page { size: letter; margin: 0.7cm 1.1cm; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 8.5px; color: #333; }

        /* ── Header (table para DomPDF) ── */
        .header { background: #2e50e4; color: white; padding: 8px 12px; border-radius: 6px 6px 0 0; margin-bottom: 8px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; color: white; padding: 0; }
        .empresa  { font-size: 12px; font-weight: bold; }
        .sub-emp  { font-size: 7.5px; margin-top: 2px; }
        .num-fac  { font-size: 17px; font-weight: bold; text-align: right; }
        .lbl-fac  { font-size: 7px; text-align: right; text-transform: uppercase; }
        .fechas   { font-size: 7.5px; margin-top: 3px; line-height: 1.4; }

        /* ── Secciones ── */
        .section { margin-bottom: 7px; padding-bottom: 7px; border-bottom: 1px solid #e0e0e0; page-break-inside: avoid; }
        .section:last-child { border-bottom: none; }
        .section-title { font-size: 7.5px; font-weight: bold; color: #4a5568; text-transform: uppercase;
                         border-bottom: 2px solid #e2e8f0; padding-bottom: 3px; margin-bottom: 6px; }

        /* ── Info grid (tabla HTML real para DomPDF) ── */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { width: 33.33%; padding: 2px 3px; vertical-align: top; }
        .info-label { color: #a0aec0; font-size: 6.5px; text-transform: uppercase; font-weight: bold; }
        .info-value { font-weight: 600; color: #2d3748; margin-top: 1px; font-size: 8px; }

        /* ── Tabla de consumos ── */
        .tabla { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 8px; }
        .tabla th { background: #f7fafc; padding: 4px 5px; font-weight: bold; color: #4a5568;
                    font-size: 7px; text-transform: uppercase; text-align: right; border: 1px solid #e2e8f0; }
        .tabla th:first-child { text-align: left; }
        .tabla td { padding: 4px 5px; border: 1px solid #e2e8f0; text-align: right; }
        .tabla td:first-child { text-align: left; }
        .tabla tfoot td { font-weight: bold; background: #f7fafc; }
        .td-green { color: #059669; }
        .td-red   { color: #dc2626; }

        /* ── Total a pagar ── */
        .total-box { background: #2e50e4; color: white; padding: 8px 12px; border-radius: 6px; margin-top: 8px; page-break-inside: avoid; }
        .total-box table { width: 100%; border-collapse: collapse; }
        .total-box td { color: white; vertical-align: middle; }
        .total-lbl { font-size: 9.5px; font-weight: 600; }
        .total-val { font-size: 15px; font-weight: bold; text-align: right; }

        /* ── Badge ── */
        .badge { display: inline; padding: 2px 6px; border-radius: 8px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-PENDIENTE { background: #fef3c7; color: #92400e; }
        .badge-PAGADA    { background: #c6f6d5; color: #22543d; }
        .badge-VENCIDA   { background: #fed7d7; color: #742a2a; }
        .badge-ANULADA   { background: #e2e8f0; color: #718096; }

        /* ── Saldo mora ── */
        .mora-box { background: #fff5f5; padding: 6px 8px; border-radius: 5px; margin-bottom: 6px; page-break-inside: avoid; }
        .mora-box table { width: 100%; border-collapse: collapse; }

        /* ── Footer ── */
        .footer-info { margin-top: 8px; padding-top: 8px; border-top: 2px solid #e2e8f0;
                       font-size: 7.5px; color: #718096; line-height: 1.5; }
    </style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <div class="header">
        <table>
            <tr>
                <td style="width:60%;">
                    <div class="empresa">{{ optional($factura->empresa)->razon_social ?? 'EMPRESA DE AGUA Y ALCANTARILLADO' }}</div>
                    <div class="sub-emp">Servicio Público Domiciliario</div>
                    <div class="fechas">
                        {{ $factura->mes_cuenta }}<br>
                        Del {{ \Carbon\Carbon::parse($factura->fecha_del)->format('d/m/Y') }}
                        al {{ \Carbon\Carbon::parse($factura->fecha_hasta)->format('d/m/Y') }}
                    </div>
                </td>
                <td style="width:40%; text-align:right;">
                    <div class="lbl-fac">Factura N°</div>
                    <div class="num-fac">{{ $factura->numero_factura }}</div>
                    <div class="fechas" style="text-align:right;">
                        Expide: {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }}<br>
                        Vence: {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}<br>
                        Corte: {{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}
                    </div>
                    <span class="badge badge-{{ $factura->estado }}" style="margin-top:5px;display:inline-block;">{{ $factura->estado }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── SUSCRIPTOR ── --}}
    <div class="section">
        <div class="section-title">Datos del Suscriptor</div>
        <table class="info-table">
            <tr>
                <td><div class="info-label">Suscriptor</div><div class="info-value">{{ $factura->suscriptor }}</div></td>
                <td><div class="info-label">Nombre</div><div class="info-value">{{ trim($factura->cliente->nombre . ' ' . $factura->cliente->apellido) }}</div></td>
                <td><div class="info-label">Dirección</div><div class="info-value">{{ $factura->cliente->direccion ?? '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="info-label">Estrato</div><div class="info-value">E{{ $factura->estrato_snapshot ?? '—' }}</div></td>
                <td><div class="info-label">Tipo de Uso</div><div class="info-value">{{ $factura->clase_uso ?? '—' }}</div></td>
                <td><div class="info-label">Servicios</div><div class="info-value">{{ $factura->servicios_snapshot }}</div></td>
            </tr>
            <tr>
                <td><div class="info-label">Serie Medidor</div><div class="info-value">{{ $factura->serie_medidor ?? '—' }}</div></td>
                <td><div class="info-label">Sector</div><div class="info-value">{{ $factura->sector ?? '—' }}</div></td>
                <td><div class="info-label">Consumo</div><div class="info-value" style="color:#2e50e4;">{{ $factura->consumo_m3 }} m³</div></td>
            </tr>
        </table>
    </div>

    {{-- ── LECTURA ── --}}
    <div class="section">
        <div class="section-title">Lectura del Período</div>
        <table class="info-table">
            <tr>
                <td><div class="info-label">Lectura Anterior</div><div class="info-value">{{ $factura->lectura_anterior ?? '—' }}</div></td>
                <td><div class="info-label">Lectura Actual</div><div class="info-value">{{ $factura->lectura_actual ?? '—' }}</div></td>
                <td><div class="info-label">Consumo m³</div><div class="info-value" style="color:#2e50e4;font-size:13px;">{{ $factura->consumo_m3 }} m³</div></td>
            </tr>
        </table>
    </div>

    {{-- ── ACUEDUCTO ── --}}
    @if(in_array($factura->servicios_snapshot, ['AG', 'AG-AL']))
    <div class="section">
        <div class="section-title">Acueducto</div>
        <table class="tabla">
            <thead><tr><th>Concepto</th><th>m³</th><th>Valor</th></tr></thead>
            <tbody>
                <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ number_format($factura->cargo_fijo_acueducto, 0, ',', '.') }}</td></tr>
                <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_basico_acueducto_valor, 0, ',', '.') }}</td></tr>
                @if($factura->consumo_complementario_acueducto_m3 > 0)
                <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_complementario_acueducto_valor, 0, ',', '.') }}</td></tr>
                @endif
                @if($factura->consumo_suntuario_acueducto_m3 > 0)
                <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_acueducto_m3 }}</td><td>$ {{ number_format($factura->consumo_suntuario_acueducto_valor, 0, ',', '.') }}</td></tr>
                @endif
                @if(($factura->subsidio_emergencia ?? 0) != 0)
                @php $esSub = ($factura->subsidio_emergencia ?? 0) > 0; @endphp
                <tr class="{{ $esSub ? 'td-green' : 'td-red' }}">
                    <td>{{ $esSub ? 'Subsidio' : 'Sobretasa' }} Acueducto</td>
                    <td>—</td>
                    <td>{{ $esSub ? '- ' : '+ ' }}$ {{ number_format(abs($factura->subsidio_emergencia), 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($factura->otros_cobros_acueducto > 0)
                <tr><td>Otros Cobros</td><td>—</td><td>$ {{ number_format($factura->cuota_otros_cobros_acueducto, 0, ',', '.') }}</td></tr>
                @endif
            </tbody>
            <tfoot>
                <tr><td colspan="2">Total Acueducto</td><td>$ {{ number_format($factura->subtotal_conexion_otros_acueducto, 0, ',', '.') }}</td></tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ── ALCANTARILLADO ── --}}
    @if(in_array($factura->servicios_snapshot, ['AL', 'AG-AL']))
    <div class="section">
        <div class="section-title">Alcantarillado</div>
        <table class="tabla">
            <thead><tr><th>Concepto</th><th>m³</th><th>Valor</th></tr></thead>
            <tbody>
                <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ number_format($factura->cargo_fijo_alcantarillado, 0, ',', '.') }}</td></tr>
                <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_basico_alcantarillado_valor, 0, ',', '.') }}</td></tr>
                @if($factura->consumo_complementario_alcantarillado_m3 > 0)
                <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_complementario_alcantarillado_valor, 0, ',', '.') }}</td></tr>
                @endif
                @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
                <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td><td>$ {{ number_format($factura->consumo_suntuario_alcantarillado_valor, 0, ',', '.') }}</td></tr>
                @endif
                @if(($factura->subsidio_alcantarillado ?? 0) != 0)
                @php $esSubAl = ($factura->subsidio_alcantarillado ?? 0) > 0; @endphp
                <tr class="{{ $esSubAl ? 'td-green' : 'td-red' }}">
                    <td>{{ $esSubAl ? 'Subsidio' : 'Sobretasa' }} Alcantarillado</td>
                    <td>—</td>
                    <td>{{ $esSubAl ? '- ' : '+ ' }}$ {{ number_format(abs($factura->subsidio_alcantarillado), 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($factura->otros_cobros_alcantarillado > 0)
                <tr><td>Otros Cobros</td><td>—</td><td>$ {{ number_format($factura->cuota_otros_cobros_alcantarillado, 0, ',', '.') }}</td></tr>
                @endif
            </tbody>
            <tfoot>
                <tr><td colspan="2">Total Alcantarillado</td><td>$ {{ number_format($factura->subtotal_conexion_otros_alcantarillado, 0, ',', '.') }}</td></tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ── SALDO ANTERIOR ── --}}
    @if($factura->saldo_anterior > 0)
    <div class="mora-box">
        <table>
            <tr>
                <td><strong style="color:#e53e3e;">Saldo Anterior en Mora</strong><br><span style="color:#718096;font-size:8.5px;">Corresponde a facturas pendientes de pago</span></td>
                <td style="text-align:right; font-size:14px; font-weight:bold; color:#e53e3e;">$ {{ number_format($factura->saldo_anterior, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ── TOTAL A PAGAR ── --}}
    <div class="total-box">
        <table>
            <tr>
                <td class="total-lbl">TOTAL A PAGAR</td>
                <td class="total-val">$ {{ number_format($factura->total_a_pagar, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer-info">
        <strong>Fecha de expedición:</strong> {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }} &nbsp;|&nbsp;
        <strong>Límite de pago:</strong> {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }} &nbsp;|&nbsp;
        <strong>Fecha de corte:</strong> {{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}
        @if($factura->observaciones)
        <br><strong>Observaciones:</strong> {{ $factura->observaciones }}
        @endif
        <br><em>Esta es una representación impresa de la factura electrónica.</em>
    </div>

</body>
</html>
