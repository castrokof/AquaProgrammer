<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Factura {{ $factura->numero_factura }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        background: #fff;
    }

    /* ── Página carta: 216mm × 279mm ── */
    @page {
        size: letter portrait;
        margin: 10mm 12mm 10mm 12mm;
    }

    .page-break { page-break-after: always; }

    /* ══ HEADER ══ */
    .header {
        background: #1e3a8a;
        color: white;
        padding: 10px 14px;
        border-radius: 6px 6px 0 0;
        display: table;
        width: 100%;
    }
    .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
    .header-right { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }
    .empresa { font-size: 13pt; font-weight: bold; letter-spacing: 0.5px; }
    .sub-empresa { font-size: 8pt; opacity: 0.85; margin-top: 2px; }
    .periodo-info { font-size: 7.5pt; margin-top: 6px; opacity: 0.9; }
    .num-factura-lbl { font-size: 7pt; text-transform: uppercase; opacity: 0.8; }
    .num-factura-val { font-size: 20pt; font-weight: bold; letter-spacing: 1px; }
    .fecha-info { font-size: 7.5pt; opacity: 0.9; margin-top: 4px; }

    /* ══ ESTADO BADGE ══ */
    .estado-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 10px;
        font-size: 7pt;
        font-weight: bold;
        margin-top: 4px;
    }
    .estado-PENDIENTE { background: #fef3c7; color: #92400e; }
    .estado-PAGADA    { background: #c6f6d5; color: #166534; }
    .estado-VENCIDA   { background: #fee2e2; color: #991b1b; }
    .estado-ANULADA   { background: #e5e7eb; color: #374151; }

    /* ══ SECCIONES ══ */
    .body-wrap { border: 1px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; }

    .section { padding: 8px 14px; border-bottom: 1px solid #e5e7eb; }
    .section:last-child { border-bottom: none; }

    .section-title {
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        color: #374151;
        letter-spacing: 0.6px;
        padding-bottom: 4px;
        margin-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .section-title .icon { color: #1e3a8a; }

    /* ══ INFO GRID (cliente) ══ */
    .info-table { width: 100%; border-collapse: collapse; }
    .info-table td { padding: 2px 4px; vertical-align: top; }
    .info-lbl { font-size: 6.5pt; color: #9ca3af; text-transform: uppercase; font-weight: bold; }
    .info-val { font-size: 8.5pt; font-weight: 600; color: #111827; margin-top: 1px; }

    /* ══ TABLA CONCEPTOS ══ */
    .tabla-fact { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
    .tabla-fact thead tr { background: #f3f4f6; }
    .tabla-fact thead th {
        padding: 5px 8px;
        text-align: left;
        font-size: 7pt;
        font-weight: bold;
        color: #374151;
        text-transform: uppercase;
        border-bottom: 2px solid #d1d5db;
    }
    .tabla-fact thead th.right { text-align: right; }
    .tabla-fact tbody td {
        padding: 4px 8px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    .tabla-fact tbody td.right { text-align: right; }
    .tabla-fact tfoot td {
        padding: 5px 8px;
        font-weight: bold;
        border-top: 2px solid #d1d5db;
        background: #f9fafb;
    }
    .tabla-fact tfoot td.right { text-align: right; }

    /* ══ TOTAL FINAL ══ */
    .total-final {
        background: #1e3a8a;
        color: white;
        padding: 12px 18px;
        margin: 0 14px 12px;
        border-radius: 6px;
        display: table;
        width: calc(100% - 28px);
    }
    .total-final-left  { display: table-cell; vertical-align: middle; }
    .total-final-right { display: table-cell; text-align: right; vertical-align: middle; }
    .total-lbl { font-size: 9pt; font-weight: bold; opacity: 0.9; }
    .total-sub { font-size: 6.5pt; opacity: 0.7; margin-top: 2px; }
    .total-val { font-size: 22pt; font-weight: bold; }

    /* ══ SALDO ANTERIOR ══ */
    .mora-box {
        background: #fef2f2;
        border-left: 4px solid #dc2626;
        padding: 6px 12px;
        margin-bottom: 0;
    }
    .mora-title { font-size: 8pt; font-weight: bold; color: #dc2626; }
    .mora-monto { font-size: 10pt; font-weight: bold; color: #dc2626; float: right; margin-top: -14px; }

    /* ══ BARRAS PROMEDIO ══ */
    .prom-wrap { margin-top: 6px; }
    .prom-bar-row { display: table; width: 100%; }
    .prom-bar-cell { display: table-cell; text-align: center; width: 14%; padding: 0 2px; }
    .prom-bar-outer { background: #e5e7eb; border-radius: 3px; height: 30px; vertical-align: bottom; position: relative; }
    .prom-bar-inner { background: #1e3a8a; border-radius: 3px; position: absolute; bottom: 0; left: 0; right: 0; }
    .prom-num { font-size: 7pt; font-weight: bold; color: #374151; text-align: center; margin-top: 2px; }
    .prom-mes { font-size: 6pt; color: #9ca3af; text-align: center; }

    /* ══ PAGOS ══ */
    .pago-row { background: #f9fafb; border-radius: 4px; padding: 5px 10px; margin-bottom: 4px; display: table; width: 100%; }
    .pago-left  { display: table-cell; vertical-align: middle; }
    .pago-right { display: table-cell; text-align: right; vertical-align: middle; }
    .pago-recibo { font-size: 8pt; font-weight: bold; color: #374151; }
    .pago-fecha  { font-size: 7pt; color: #6b7280; }
    .pago-monto  { font-size: 9pt; font-weight: bold; color: #166534; }

    /* ══ RESUMEN SALDO ══ */
    .resumen-saldo { display: table; width: 100%; border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 6px; }
    .resumen-item { display: table-cell; text-align: center; }
    .resumen-lbl { font-size: 7pt; color: #6b7280; text-transform: uppercase; font-weight: bold; }
    .resumen-val { font-size: 11pt; font-weight: bold; }

    /* ══ PIE ══ */
    .footer {
        text-align: center;
        font-size: 7pt;
        color: #9ca3af;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px dashed #d1d5db;
    }

    /* ══ SEPARADOR FACTURA (multi-factura) ══ */
    .fact-separator { border-top: 3px dashed #94a3b8; margin: 16px 0; }

    /* ══ DOS COLUMNAS ══ */
    .two-col { display: table; width: 100%; }
    .col-left  { display: table-cell; width: 50%; padding-right: 6px; vertical-align: top; }
    .col-right { display: table-cell; width: 50%; padding-left: 6px; vertical-align: top; }
</style>
</head>
<body>

@foreach($facturas as $factura)

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
            Del {{ \Carbon\Carbon::parse($factura->fecha_del)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($factura->fecha_hasta)->format('d/m/Y') }}
        </div>
    </div>
    <div class="header-right">
        <div class="num-factura-lbl">Factura N°</div>
        <div class="num-factura-val">{{ $factura->numero_factura }}</div>
        <div class="fecha-info">
            Expide: {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }}<br>
            Vence:&nbsp;&nbsp;{{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}<br>
            Corte:&nbsp;&nbsp;{{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}
        </div>
        <div>
            <span class="estado-badge estado-{{ $factura->estado }}">{{ $factura->estado }}</span>
        </div>
    </div>
</div>

{{-- ── BODY ── --}}
<div class="body-wrap">

    {{-- DATOS DEL SUSCRIPTOR --}}
    <div class="section">
        <div class="section-title">&#128100; Datos del Suscriptor</div>
        <table class="info-table">
            <tr>
                <td style="width:16%">
                    <div class="info-lbl">Suscriptor</div>
                    <div class="info-val">{{ $factura->suscriptor }}</div>
                </td>
                <td style="width:30%">
                    <div class="info-lbl">Nombre</div>
                    <div class="info-val">{{ trim(($factura->cliente->nombre ?? '') . ' ' . ($factura->cliente->apellido ?? '')) }}</div>
                </td>
                <td style="width:28%">
                    <div class="info-lbl">Dirección</div>
                    <div class="info-val">{{ $factura->cliente->direccion ?? '—' }}</div>
                </td>
                <td style="width:10%">
                    <div class="info-lbl">Estrato</div>
                    <div class="info-val">E{{ $factura->estrato_snapshot ?? '—' }}</div>
                </td>
                <td style="width:16%">
                    <div class="info-lbl">Servicios</div>
                    <div class="info-val">{{ $factura->servicios_snapshot }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-lbl">Sector</div>
                    <div class="info-val">{{ $factura->sector ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-lbl">Medidor</div>
                    <div class="info-val">{{ $factura->serie_medidor ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-lbl">Tipo de Uso</div>
                    <div class="info-val">{{ $factura->clase_uso ?? '—' }}</div>
                </td>
                <td colspan="2">
                    <div class="info-lbl">Tipo Factura</div>
                    <div class="info-val">{{ $factura->es_automatica ? 'Automática' : 'Manual' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- LECTURA --}}
    <div class="section">
        <div class="section-title">&#128336; Lectura del Período</div>
        <div class="two-col">
            <div class="col-left">
                <table class="info-table">
                    <tr>
                        <td style="width:33%">
                            <div class="info-lbl">Lect. Anterior</div>
                            <div class="info-val" style="font-size:10pt;">{{ $factura->lectura_anterior ?? '—' }}</div>
                        </td>
                        <td style="width:33%">
                            <div class="info-lbl">Lect. Actual</div>
                            <div class="info-val" style="font-size:10pt;">{{ $factura->lectura_actual ?? '—' }}</div>
                        </td>
                        <td style="width:34%">
                            <div class="info-lbl">Consumo</div>
                            <div class="info-val" style="font-size:12pt; color:#1e3a8a; font-weight:900;">{{ $factura->consumo_m3 }} m³</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding-top:4px;">
                            <div class="info-lbl">Promedio 6 meses</div>
                            <div class="info-val">{{ $factura->promedio_consumo_snapshot }} m³</div>
                        </td>
                    </tr>
                </table>
            </div>
            @php
                $meses = array_filter([$factura->prom_m1,$factura->prom_m2,$factura->prom_m3,$factura->prom_m4,$factura->prom_m5,$factura->prom_m6], fn($v)=>!is_null($v));
                $maxM  = count($meses) > 0 ? max(array_merge($meses, [1])) : 1;
            @endphp
            @if(count($meses)>0)
            <div class="col-right">
                <div style="display:table; width:100%;">
                    @foreach($meses as $i => $m)
                    <div style="display:table-cell; text-align:center; width:{{ floor(100/count($meses)) }}%; vertical-align:bottom; padding:0 2px;">
                        <div style="height:30px; background:#e5e7eb; border-radius:2px; position:relative;">
                            <div style="height:{{ max(10, round(($m/$maxM)*30)) }}px; background:#1e3a8a; border-radius:2px; position:absolute; bottom:0; left:0; right:0;"></div>
                        </div>
                        <div style="font-size:7pt; font-weight:bold; color:#374151;">{{ $m }}</div>
                        <div style="font-size:6pt; color:#9ca3af;">M-{{ count($meses)-$i }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ACUEDUCTO --}}
    @if(in_array($factura->servicios_snapshot, ['AG','AG-AL']))
    <div class="section">
        <div class="section-title">&#128167; Acueducto</div>
        <table class="tabla-fact">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">m³</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cargo Fijo</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ number_format($factura->cargo_fijo_acueducto,0,',','.') }}</td>
                </tr>
                <tr>
                    <td>Consumo Básico</td>
                    <td class="right">{{ $factura->consumo_basico_acueducto_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_basico_acueducto_valor,0,',','.') }}</td>
                </tr>
                @if($factura->consumo_complementario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Complementario</td>
                    <td class="right">{{ $factura->consumo_complementario_acueducto_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_complementario_acueducto_valor,0,',','.') }}</td>
                </tr>
                @endif
                @if($factura->consumo_suntuario_acueducto_m3 > 0)
                <tr>
                    <td>Consumo Suntuario</td>
                    <td class="right">{{ $factura->consumo_suntuario_acueducto_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_suntuario_acueducto_valor,0,',','.') }}</td>
                </tr>
                @endif
                @if($factura->subsidio_emergencia != 0)
                @php $esSubsidio = $factura->subsidio_emergencia > 0; @endphp
                <tr>
                    <td>{{ $esSubsidio ? 'Subsidio Estrato' : 'Contribución Estrato' }}</td>
                    <td class="right">—</td>
                    <td class="right" style="color:{{ $esSubsidio ? '#166534' : '#991b1b' }};">
                        {{ $esSubsidio ? '- ' : '+ ' }}$ {{ number_format(abs($factura->subsidio_emergencia),0,',','.') }}
                    </td>
                </tr>
                @endif
                @if($factura->cuota_otros_cobros_acueducto > 0)
                <tr>
                    <td>Otros Cobros Acueducto</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ number_format($factura->cuota_otros_cobros_acueducto,0,',','.') }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total Acueducto</strong></td>
                    <td class="right"><strong>$ {{ number_format($factura->subtotal_conexion_otros_acueducto,0,',','.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- ALCANTARILLADO --}}
    @if(in_array($factura->servicios_snapshot, ['AL','AG-AL']))
    <div class="section">
        <div class="section-title">&#127754; Alcantarillado</div>
        <table class="tabla-fact">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">m³</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cargo Fijo</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ number_format($factura->cargo_fijo_alcantarillado,0,',','.') }}</td>
                </tr>
                <tr>
                    <td>Consumo Básico</td>
                    <td class="right">{{ $factura->consumo_basico_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_basico_alcantarillado_valor,0,',','.') }}</td>
                </tr>
                @if($factura->consumo_complementario_alcantarillado_m3 > 0)
                <tr>
                    <td>Consumo Complementario</td>
                    <td class="right">{{ $factura->consumo_complementario_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_complementario_alcantarillado_valor,0,',','.') }}</td>
                </tr>
                @endif
                @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
                <tr>
                    <td>Consumo Suntuario</td>
                    <td class="right">{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td>
                    <td class="right">$ {{ number_format($factura->consumo_suntuario_alcantarillado_valor,0,',','.') }}</td>
                </tr>
                @endif
                @if($factura->cuota_otros_cobros_alcantarillado > 0)
                <tr>
                    <td>Otros Cobros Alcantarillado</td>
                    <td class="right">—</td>
                    <td class="right">$ {{ number_format($factura->cuota_otros_cobros_alcantarillado,0,',','.') }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total Alcantarillado</strong></td>
                    <td class="right"><strong>$ {{ number_format($factura->subtotal_conexion_otros_alcantarillado,0,',','.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- SALDO ANTERIOR --}}
    @if($factura->saldo_anterior > 0)
    <div class="section mora-box">
        <div class="mora-title">&#9888; Saldo Anterior en Mora &mdash; {{ $factura->facturas_en_mora }} factura(s) pendiente(s)</div>
        <div class="mora-monto">$ {{ number_format($factura->saldo_anterior,0,',','.') }}</div>
    </div>
    @endif

    {{-- TOTAL FINAL --}}
    <div class="section" style="background:#f9fafb;">
        <div class="total-final">
            <div class="total-final-left">
                <div class="total-lbl">TOTAL A PAGAR</div>
                <div class="total-sub">Incluye todos los conceptos del período</div>
            </div>
            <div class="total-final-right">
                <div class="total-val">$ {{ number_format($factura->total_a_pagar,0,',','.') }}</div>
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
                <div class="pago-fecha">{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</div>
            </div>
            <div class="pago-right">
                <div class="pago-monto">+ $ {{ number_format($p->total_pago_realizado,0,',','.') }}</div>
            </div>
        </div>
        @endforeach
        <div class="resumen-saldo">
            <div class="resumen-item">
                <div class="resumen-lbl">Total Pagado</div>
                <div class="resumen-val" style="color:#166534;">$ {{ number_format($totalPagado,0,',','.') }}</div>
            </div>
            <div class="resumen-item">
                <div class="resumen-lbl">Saldo Pendiente</div>
                <div class="resumen-val" style="color:{{ $saldoPendiente > 0 ? '#dc2626' : '#166534' }};">
                    $ {{ number_format($saldoPendiente,0,',','.') }}
                </div>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end body-wrap --}}

{{-- PIE DE PÁGINA --}}
<div class="footer">
    Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} &bull;
    Factura N° {{ $factura->numero_factura }} &bull;
    Este documento es válido como soporte de facturación del servicio público domiciliario.
</div>

@if(!$loop->last)
<div class="page-break"></div>
@endif

@endforeach

</body>
</html>
