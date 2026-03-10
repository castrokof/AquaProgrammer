<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Facturas</title>
<style>
@page { size: letter; margin: 10mm 12mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 8.5pt; color: #111; line-height: 1.35; }
.page-break { page-break-after: always; }

/* ── Bordes tipo tabla ── */
.tbl { width:100%; border-collapse:collapse; }
.tbl th, .tbl td { border:1px solid #bbb; padding:3px 5px; vertical-align:middle; }
.tbl th { background:#f0f0f0; font-weight:700; font-size:7.5pt; text-align:center; }
.cell-lbl { font-weight:700; font-size:7.5pt; }
.r { text-align:right; }
.c { text-align:center; }

/* ── Header empresa / datos suscriptor ── */
.hdr-outer { border:1.5px solid #888; border-collapse:collapse; width:100%; margin-bottom:6px; }
.hdr-empresa { border-right:1.5px solid #888; padding:7px 10px; vertical-align:top; width:42%; }
.hdr-suscriptor { padding:0; vertical-align:top; width:58%; }
.hdr-suscriptor-title { background:#2e50e4; color:white; font-weight:700; font-size:8pt;
    text-transform:uppercase; padding:4px 8px; letter-spacing:.4px; }
.hdr-suscriptor-body { padding:0; }
.hdr-suscriptor-body table { width:100%; border-collapse:collapse; }
.hdr-suscriptor-body table td { padding:3px 7px; font-size:8pt; border-bottom:1px solid #e8e8e8; }
.hdr-suscriptor-body table tr:last-child td { border-bottom:none; }
.empresa-name { font-size:11pt; font-weight:bold; color:#1a1a1a; }
.empresa-sub { font-size:7.5pt; color:#555; margin-top:2px; }
.empresa-num { font-size:8pt; color:#333; margin-top:4px; }
.num-fact { font-size:16pt; font-weight:bold; color:#2e50e4; }
.fact-badge { display:inline-block; padding:1px 7px; border-radius:4px; font-size:6.5pt; font-weight:bold; }
.badge-auto   { background:#e0f2fe; color:#0369a1; }
.badge-manual { background:#fef3c7; color:#b45309; }
.badge-obs    { background:#fef9c3; color:#713f12; border:1px solid #fde047; border-radius:4px;
    padding:2px 6px; font-size:7pt; margin-top:3px; display:block; }

/* ── Sección de consumo (2 columnas) ── */
.detalle-title { background:#2e50e4; color:white; font-weight:700; font-size:8pt;
    padding:4px 8px; text-transform:uppercase; letter-spacing:.4px; margin-bottom:0; }
.servicio-title { background:#e8eeff; color:#1e3a8a; font-weight:700; font-size:8pt;
    padding:3px 7px; text-align:center; border:1px solid #bbb; border-bottom:none; }
.tabla-consumo th { font-size:7pt; padding:3px 4px; background:#f5f7ff; color:#374151; }
.tabla-consumo td { font-size:7.5pt; padding:3px 5px; }
.tabla-consumo tfoot td { font-weight:700; background:#eef2ff; font-size:8pt; }
.subsidio-pos { color:#166534; }
.subsidio-neg { color:#991b1b; }

/* ── Resumen del cobro ── */
.resumen-title { background:#2e50e4; color:white; font-weight:700; font-size:8pt;
    padding:4px 8px; text-transform:uppercase; letter-spacing:.4px; }
.resumen-tbl td { padding:3px 8px; font-size:8pt; border-bottom:1px solid #f0f0f0; }
.resumen-tbl tr:last-child td { border-bottom:none; }
.resumen-total td { background:#2e50e4; color:white; font-weight:700; font-size:10pt; padding:5px 8px; }

/* ── Gráfica de barras (SVG) ── */
.barras-title { font-size:7.5pt; font-weight:700; color:#374151; margin-bottom:4px; text-align:center; }

/* ── Créditos ── */
.creditos-title { background:#f3f4f6; font-weight:700; font-size:7.5pt; padding:3px 7px;
    border:1px solid #bbb; border-bottom:none; color:#374151; text-transform:uppercase; }

/* ── Último pago / estado ── */
.estado-bar { border:1.5px solid #888; border-collapse:collapse; width:100%; margin-top:6px; }
.estado-cell { padding:6px 10px; vertical-align:top; font-size:8pt; }
.estado-lbl  { font-weight:700; font-size:7.5pt; color:#555; text-transform:uppercase;
    letter-spacing:.3px; display:block; margin-bottom:2px; }
.total-pagar-box { background:#2e50e4; color:white; border-radius:0; padding:5px 10px;
    text-align:right; }
.total-pagar-box .lbl { font-size:8pt; font-weight:700; }
.total-pagar-box .val { font-size:14pt; font-weight:bold; }
.footer-txt { text-align:center; font-size:7pt; color:#777; margin-top:5px;
    border-top:1px solid #ccc; padding-top:4px; }
.obs-box { border:1px solid #fde047; background:#fefce8; border-radius:4px; padding:4px 8px;
    font-size:7.5pt; color:#713f12; margin-top:5px; }
</style>
</head>
<body>

@foreach($facturas as $factura)
@php
    $nf = function($v) { return '$'.number_format((float)($v ?? 0), 0, ',', '.'); };
    $fmtF = function($f) {
        if (!$f) return '—';
        return ($f instanceof \Carbon\Carbon ? $f : \Carbon\Carbon::parse($f))->format('d/m/Y');
    };

    $hasAcueducto      = in_array($factura->servicios_snapshot ?? 'AG-AL', ['AG','AG-AL']);
    $hasAlcantarillado = in_array($factura->servicios_snapshot ?? 'AG-AL', ['AL','AG-AL']);

    // ── Referencia tarifaria (precio unitario) ──
    $refBasAc  = ($factura->consumo_basico_acueducto_m3 > 0)
                    ? round($factura->consumo_basico_acueducto_valor / $factura->consumo_basico_acueducto_m3, 2) : 0;
    $refCompAc = ($factura->consumo_complementario_acueducto_m3 > 0)
                    ? round($factura->consumo_complementario_acueducto_valor / $factura->consumo_complementario_acueducto_m3, 2) : 0;
    $refSuntAc = ($factura->consumo_suntuario_acueducto_m3 > 0)
                    ? round($factura->consumo_suntuario_acueducto_valor / $factura->consumo_suntuario_acueducto_m3, 2) : 0;

    $refBasAl  = ($factura->consumo_basico_alcantarillado_m3 > 0)
                    ? round($factura->consumo_basico_alcantarillado_valor / $factura->consumo_basico_alcantarillado_m3, 2) : 0;
    $refCompAl = ($factura->consumo_complementario_alcantarillado_m3 > 0)
                    ? round($factura->consumo_complementario_alcantarillado_valor / $factura->consumo_complementario_alcantarillado_m3, 2) : 0;
    $refSuntAl = ($factura->consumo_suntuario_alcantarillado_m3 > 0)
                    ? round($factura->consumo_suntuario_alcantarillado_valor / $factura->consumo_suntuario_alcantarillado_m3, 2) : 0;

    // ── Subsidio / contribución ──
    $subsidioAc = (float)($factura->subsidio_emergencia ?? 0);   // + = subsidio (descuento), - = contribución (cargo)
    $esSubsidio  = $subsidioAc > 0;

    // ── Neto acueducto (subtotal - subsidio) ──
    $netoAcueducto = (float)($factura->total_facturacion_acueducto ?? 0);
    $netoAlcantarillado = (float)($factura->subtotal_alcantarillado ?? 0);

    // ── Total pagado ──
    $totalPagado    = $factura->pagos->sum('total_pago_realizado');
    $ultimoPago     = $factura->pagos->sortByDesc('fecha_pago')->first();

    // ── Barras de consumo: prom_m1..m6 + actual ──
    $consumos = [
        $factura->prom_m6, $factura->prom_m5, $factura->prom_m4,
        $factura->prom_m3, $factura->prom_m2, $factura->prom_m1,
        $factura->consumo_m3,
    ];
    $maxConsumo = max(array_filter($consumos, fn($v) => $v !== null) ?: [1]);
    $maxConsumo = max($maxConsumo, 1);

    // Número de factura con prefijo empresa
    $numFacturaMostrar = ($empresa->prefijo_factura ? $empresa->prefijo_factura : '') . $factura->numero_factura;

    // Tarifa nombre
    $tarifaNombre = optional($factura->tarifaPeriodo)->nombre ?? '—';
@endphp

@if(!$loop->first)<div class="page-break"></div>@endif

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ENCABEZADO                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<table class="hdr-outer">
<tr>
    {{-- Empresa --}}
    <td class="hdr-empresa">
        @if($empresa->logo_path)
            @php $logoB64 = $empresa->logoBase64(); @endphp
            @if($logoB64)
            <img src="{{ $logoB64 }}" style="max-height:50px;max-width:130px;object-fit:contain;display:block;margin-bottom:5px;">
            @endif
        @endif
        <div class="empresa-name">{{ strtoupper($empresa->nombre) }}</div>
        @if($empresa->nit)
        <div class="empresa-sub">NIT {{ $empresa->nit }}</div>
        @endif
        <div class="empresa-sub">{{ $empresa->texto_documento_equivalente }}</div>
        <div class="empresa-num">No. <strong>{{ $numFacturaMostrar }}</strong></div>
        <div style="margin-top:5px;font-size:7.5pt;color:#444;">
            Período: <strong>{{ $factura->mes_cuenta }}</strong><br>
            Del {{ $fmtF($factura->fecha_del) }} al {{ $fmtF($factura->fecha_hasta) }}<br>
            Expedición: {{ $fmtF($factura->fecha_expedicion) }}
            &nbsp;|&nbsp; Vence: <strong>{{ $fmtF($factura->fecha_vencimiento) }}</strong><br>
            Corte: {{ $fmtF($factura->fecha_corte) }}
        </div>
        <div style="margin-top:4px;">
            @if($factura->es_automatica)
                <span class="fact-badge badge-auto">AUTO</span>
            @else
                <span class="fact-badge badge-manual">MANUAL</span>
            @endif
        </div>
    </td>
    {{-- Datos suscriptor --}}
    <td class="hdr-suscriptor">
        <div class="hdr-suscriptor-title">Datos del Suscriptor</div>
        <div class="hdr-suscriptor-body">
        <table>
            <tr>
                <td style="width:50%;"><span class="cell-lbl">Nombre:</span>
                    @if($factura->cliente){{ trim($factura->cliente->nombre.' '.$factura->cliente->apellido) }}@else—@endif
                </td>
                <td><span class="cell-lbl">Ubicación:</span> {{ $factura->sector ?? '—' }}</td>
            </tr>
            <tr>
                <td><span class="cell-lbl">Medidor:</span> {{ $factura->serie_medidor ?? '—' }}</td>
                <td><span class="cell-lbl">Suscriptor N°:</span> {{ $factura->suscriptor }}</td>
            </tr>
            <tr>
                <td><span class="cell-lbl">Clase de servicio:</span> {{ $factura->clase_uso ?? '—' }}</td>
                <td><span class="cell-lbl">Estrato:</span>
                    @php
                        $estrNombres = [1=>'BAJO-BAJO',2=>'BAJO',3=>'MEDIO BAJO',4=>'MEDIO',5=>'MEDIO ALTO',6=>'ALTO'];
                        $eN = $factura->estrato_snapshot ? ($estrNombres[$factura->estrato_snapshot] ?? '') : '';
                    @endphp
                    {{ $factura->estrato_snapshot ? $factura->estrato_snapshot.' - '.$eN : '—' }}
                </td>
            </tr>
            <tr>
                <td><span class="cell-lbl">Ciclo / Período:</span> {{ $factura->periodo }}</td>
                <td><span class="cell-lbl">Tarifa:</span> {{ $tarifaNombre }}</td>
            </tr>
            @if($factura->observaciones)
            <tr>
                <td colspan="2"><span class="badge-obs"><i>Obs:</i> {{ $factura->observaciones }}</span></td>
            </tr>
            @endif
        </table>
        </div>
    </td>
</tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- LECTURA + DETALLE DEL CONSUMO                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="detalle-title" style="margin-bottom:4px;">Detalle del Consumo</div>
<table class="tbl" style="margin-bottom:5px;">
    <thead>
        <tr>
            <th>Lect. Anterior</th><th>Lect. Actual</th>
            <th>Consumo m³</th><th>Promedio 6m</th>
            <th>Con Medidor</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="c">{{ $factura->lectura_anterior ?? '—' }}</td>
            <td class="c">{{ $factura->lectura_actual ?? '—' }}</td>
            <td class="c" style="font-weight:700;">{{ $factura->consumo_m3 }}</td>
            <td class="c">{{ number_format($factura->promedio_consumo_snapshot ?? 0, 1) }}</td>
            <td class="c">{{ $factura->tiene_medidor_snapshot ? 'Sí' : 'No' }}</td>
        </tr>
    </tbody>
</table>

{{-- ACUEDUCTO | ALCANTARILLADO side by side --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:5px;">
<tr style="vertical-align:top;">

@if($hasAcueducto)
<td style="width:{{ $hasAlcantarillado ? '50%' : '100%' }};padding-right:{{ $hasAlcantarillado ? '3px' : '0' }};">
    <div class="servicio-title">ACUEDUCTO</div>
    <table class="tbl tabla-consumo">
        <thead>
            <tr>
                <th style="text-align:left;">Concepto</th>
                <th>m³</th>
                <th>Referencia</th>
                <th>Tarifa</th>
                <th>Costo Real</th>
                <th>Subsidio</th>
                <th>Neto</th>
            </tr>
        </thead>
        <tbody>
            {{-- Cargo fijo --}}
            <tr>
                <td>Cargo fijo</td>
                <td class="c">—</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
            </tr>
            {{-- Básico --}}
            @if($factura->consumo_basico_acueducto_m3 > 0 || true)
            <tr>
                <td>Consumo básico</td>
                <td class="c">{{ $factura->consumo_basico_acueducto_m3 }}</td>
                <td class="r">{{ number_format($refBasAc,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_acueducto_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_acueducto_valor,2,',','.') }}</td>
                @if($esSubsidio)
                <td class="r subsidio-pos">-{{ number_format(abs($subsidioAc),2,',','.') }}</td>
                <td class="r subsidio-pos">{{ number_format($factura->consumo_basico_acueducto_valor - abs($subsidioAc),2,',','.') }}</td>
                @elseif($subsidioAc < 0)
                <td class="r subsidio-neg">+{{ number_format(abs($subsidioAc),2,',','.') }}</td>
                <td class="r subsidio-neg">{{ number_format($factura->consumo_basico_acueducto_valor + abs($subsidioAc),2,',','.') }}</td>
                @else
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_basico_acueducto_valor,2,',','.') }}</td>
                @endif
            </tr>
            @endif
            {{-- Complementario --}}
            @if($factura->consumo_complementario_acueducto_m3 > 0)
            <tr>
                <td>Consumo comp.</td>
                <td class="c">{{ $factura->consumo_complementario_acueducto_m3 }}</td>
                <td class="r">{{ number_format($refCompAc,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_complementario_acueducto_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_complementario_acueducto_valor,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_complementario_acueducto_valor,2,',','.') }}</td>
            </tr>
            @endif
            {{-- Suntuario --}}
            @if($factura->consumo_suntuario_acueducto_m3 > 0)
            <tr>
                <td>Consumo sunt.</td>
                <td class="c">{{ $factura->consumo_suntuario_acueducto_m3 }}</td>
                <td class="r">{{ number_format($refSuntAc,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_acueducto_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_acueducto_valor,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_acueducto_valor,2,',','.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="c">{{ $factura->consumo_m3 }}</td>
                <td></td>
                <td class="r">{{ number_format($factura->subtotal_facturacion_acueducto,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->subtotal_facturacion_acueducto,2,',','.') }}</td>
                <td class="r {{ $esSubsidio ? 'subsidio-pos' : ($subsidioAc < 0 ? 'subsidio-neg':'') }}">
                    {{ $subsidioAc != 0 ? ($esSubsidio?'-':'+').number_format(abs($subsidioAc),2,',','.') : '0,00' }}
                </td>
                <td class="r">{{ number_format($netoAcueducto,2,',','.') }}</td>
            </tr>
        </tfoot>
    </table>
</td>
@endif

@if($hasAlcantarillado)
<td style="width:{{ $hasAcueducto ? '50%' : '100%' }};padding-left:{{ $hasAcueducto ? '3px' : '0' }};">
    <div class="servicio-title">ALCANTARILLADO</div>
    <table class="tbl tabla-consumo">
        <thead>
            <tr>
                <th style="text-align:left;">Concepto</th>
                <th>m³</th>
                <th>Referencia</th>
                <th>Tarifa</th>
                <th>Costo Real</th>
                <th>Subsidio</th>
                <th>Neto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cargo fijo</td>
                <td class="c">—</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
            </tr>
            @if($factura->consumo_basico_alcantarillado_m3 > 0 || true)
            <tr>
                <td>Consumo básico</td>
                <td class="c">{{ $factura->consumo_basico_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refBasAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_alcantarillado_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_alcantarillado_valor,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_basico_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
            @if($factura->consumo_complementario_alcantarillado_m3 > 0)
            <tr>
                <td>Consumo comp.</td>
                <td class="c">{{ $factura->consumo_complementario_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refCompAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_complementario_alcantarillado_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_complementario_alcantarillado_valor,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_complementario_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
            @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
            <tr>
                <td>Consumo sunt.</td>
                <td class="c">{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refSuntAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_alcantarillado_valor,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_alcantarillado_valor,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="c">{{ $factura->consumo_m3 }}</td>
                <td></td>
                <td class="r">{{ number_format($netoAlcantarillado,2,',','.') }}</td>
                <td class="r">{{ number_format($netoAlcantarillado,2,',','.') }}</td>
                <td class="c">0,00</td>
                <td class="r">{{ number_format($netoAlcantarillado,2,',','.') }}</td>
            </tr>
        </tfoot>
    </table>
</td>
@endif

</tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- CRÉDITOS + BARRAS | RESUMEN DEL COBRO                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:5px;">
<tr style="vertical-align:top;">

{{-- Columna izquierda: créditos + barras --}}
<td style="width:46%;padding-right:4px;">

    {{-- Créditos y financiación --}}
    <div class="creditos-title">Créditos Otorgados y Financiación</div>
    <table class="tbl" style="font-size:7.5pt;">
        <thead>
            <tr>
                <th style="text-align:left;">Descripción</th>
                <th>Val. cuota</th>
                <th>Saldo</th>
                <th>Tot</th><th>Fac</th><th>Pend</th>
            </tr>
        </thead>
        <tbody>
            @if($factura->cuota_otros_cobros_acueducto > 0)
            <tr>
                <td>Otros cobros acueducto</td>
                <td class="r">{{ number_format($factura->cuota_otros_cobros_acueducto,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->saldo_otros_cobros_acueducto,0,',','.') }}</td>
                <td class="c">—</td><td class="c">—</td><td class="c">—</td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_alcantarillado > 0)
            <tr>
                <td>Otros cobros alcantarillado</td>
                <td class="r">{{ number_format($factura->cuota_otros_cobros_alcantarillado,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->saldo_otros_cobros_alcantarillado,0,',','.') }}</td>
                <td class="c">—</td><td class="c">—</td><td class="c">—</td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_acueducto == 0 && $factura->cuota_otros_cobros_alcantarillado == 0)
            <tr><td>—</td><td>—</td><td>—</td><td>—</td><td>—</td><td>—</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total créditos</td>
                <td class="r">{{ number_format(($factura->cuota_otros_cobros_acueducto ?? 0) + ($factura->cuota_otros_cobros_alcantarillado ?? 0), 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Últimos 6 consumos (barras SVG) --}}
    <div style="margin-top:8px;border:1px solid #bbb;padding:6px 8px;border-radius:0;">
        <div class="barras-title">Últimos 6 consumos + actual (m³)</div>
        @php
            $labels = ['M-6','M-5','M-4','M-3','M-2','M-1','Actual'];
            $barW   = 22;
            $barGap = 6;
            $chartH = 55;
            $svgW   = count($consumos) * ($barW + $barGap) + $barGap;
        @endphp
        <svg width="{{ $svgW }}" height="{{ $chartH + 22 }}" xmlns="http://www.w3.org/2000/svg">
        @foreach($consumos as $i => $val)
            @php
                $v       = (int)($val ?? 0);
                $barH    = $v > 0 ? max(3, round($v / $maxConsumo * $chartH)) : 2;
                $x       = $barGap + $i * ($barW + $barGap);
                $y       = $chartH - $barH;
                $isActual = ($i === count($consumos) - 1);
                $color   = $isActual ? '#2e50e4' : '#93c5fd';
            @endphp
            <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $barH }}"
                  fill="{{ $color }}" rx="2"/>
            {{-- valor encima --}}
            <text x="{{ $x + $barW/2 }}" y="{{ $y - 1 }}" text-anchor="middle"
                  font-size="6" fill="#374151">{{ $v > 0 ? $v : '' }}</text>
            {{-- etiqueta abajo --}}
            <text x="{{ $x + $barW/2 }}" y="{{ $chartH + 14 }}" text-anchor="middle"
                  font-size="6" fill="{{ $isActual ? '#2e50e4' : '#6b7280' }}"
                  font-weight="{{ $isActual ? 'bold' : 'normal' }}">{{ $labels[$i] }}</text>
        @endforeach
        {{-- línea base --}}
        <line x1="0" y1="{{ $chartH }}" x2="{{ $svgW }}" y2="{{ $chartH }}"
              stroke="#ccc" stroke-width="1"/>
        </svg>
    </div>

</td>

{{-- Columna derecha: Resumen del cobro --}}
<td style="width:54%;padding-left:4px;vertical-align:top;">
    <div class="resumen-title">Resumen del Cobro</div>
    <table class="tbl resumen-tbl" style="border-top:none;">
        <tbody>
            @if($factura->saldo_anterior > 0)
            <tr><td>Saldo anterior</td><td class="r" style="color:#dc2626;font-weight:700;">{{ $nf($factura->saldo_anterior) }}</td></tr>
            @endif
            @if($hasAcueducto)
            <tr><td>Cargo fijo acueducto</td><td class="r">{{ $nf($factura->cargo_fijo_acueducto) }}</td></tr>
            <tr><td>Consumo acueducto</td><td class="r">{{ $nf($factura->subtotal_facturacion_acueducto - $factura->cargo_fijo_acueducto) }}</td></tr>
            @if($subsidioAc != 0)
            <tr>
                <td class="{{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidio ? 'Subsidio acueducto' : 'Contribución acueducto' }}
                </td>
                <td class="r {{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidio ? '-' : '+' }}{{ $nf(abs($subsidioAc)) }}
                </td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_acueducto > 0)
            <tr><td>Otros cobros acueducto</td><td class="r">{{ $nf($factura->cuota_otros_cobros_acueducto) }}</td></tr>
            @endif
            @endif
            @if($hasAlcantarillado)
            <tr><td>Cargo fijo alcantarillado</td><td class="r">{{ $nf($factura->cargo_fijo_alcantarillado) }}</td></tr>
            <tr><td>Vertimiento alcantarillado</td><td class="r">{{ $nf($netoAlcantarillado - $factura->cargo_fijo_alcantarillado) }}</td></tr>
            @if($factura->cuota_otros_cobros_alcantarillado > 0)
            <tr><td>Otros cobros alcantarillado</td><td class="r">{{ $nf($factura->cuota_otros_cobros_alcantarillado) }}</td></tr>
            @endif
            @endif
        </tbody>
        <tfoot>
            <tr class="resumen-total">
                <td>TOTAL A PAGAR</td>
                <td class="r" style="font-size:12pt;">{{ $nf($factura->total_a_pagar) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($totalPagado > 0)
    <div style="margin-top:4px;border:1px solid #bbb;padding:4px 7px;font-size:7.5pt;">
        <strong>Pagos registrados:</strong>
        @foreach($factura->pagos->sortByDesc('fecha_pago') as $p)
        <div style="display:flex;justify-content:space-between;border-bottom:1px dashed #eee;padding:2px 0;">
            <span>{{ $p->numero_recibo ? 'Rec.'.$p->numero_recibo : 'Sin #' }}
                  — {{ $p->medio_pago }}
                  <span style="color:#888;">({{ $p->fecha_pago ? \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') : '—' }})</span>
            </span>
            <span style="color:#166534;font-weight:700;">{{ $nf($p->total_pago_realizado) }}</span>
        </div>
        @endforeach
        <div style="text-align:right;margin-top:3px;">
            Saldo pendiente: <strong style="color:{{ ($factura->total_a_pagar - $totalPagado) > 0 ? '#dc2626' : '#166534' }};">
                {{ $nf(max(0, $factura->total_a_pagar - $totalPagado)) }}
            </strong>
        </div>
    </div>
    @endif
</td>
</tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- PIE: ÚLTIMO PAGO | FECHAS IMPORTANTES                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<table class="estado-bar">
<tr>
    <td class="estado-cell" style="width:38%;border-right:1.5px solid #888;">
        <span class="estado-lbl">Último Pago</span>
        @if($ultimoPago)
        <table style="width:100%;font-size:7.5pt;border-collapse:collapse;">
            <tr>
                <th style="text-align:left;padding:1px 4px;background:#f3f4f6;border:1px solid #ddd;">Fecha</th>
                <th style="text-align:left;padding:1px 4px;background:#f3f4f6;border:1px solid #ddd;">Valor</th>
                <th style="text-align:left;padding:1px 4px;background:#f3f4f6;border:1px solid #ddd;">Medio</th>
            </tr>
            <tr>
                <td style="padding:1px 4px;border:1px solid #ddd;">{{ $fmtF($ultimoPago->fecha_pago) }}</td>
                <td style="padding:1px 4px;border:1px solid #ddd;">{{ $nf($ultimoPago->total_pago_realizado) }}</td>
                <td style="padding:1px 4px;border:1px solid #ddd;">{{ $ultimoPago->medio_pago }}</td>
            </tr>
        </table>
        <div style="margin-top:3px;font-size:7pt;color:#555;">Estado del servicio: <strong>CON SERVICIO</strong></div>
        @else
        <div style="color:#888;font-size:7.5pt;">Sin pagos registrados</div>
        <div style="margin-top:3px;font-size:7pt;color:#555;">Estado del servicio: <strong>CON SERVICIO</strong></div>
        @endif
    </td>
    <td class="estado-cell" style="width:62%;">
        <table style="width:100%;border-collapse:collapse;font-size:8pt;">
            <tr>
                <td style="padding:2px 8px;border-right:1px solid #ddd;">
                    <span class="estado-lbl">Pague Hasta</span>
                    <strong>{{ $fmtF($factura->fecha_vencimiento) }}</strong>
                </td>
                <td style="padding:2px 8px;border-right:1px solid #ddd;">
                    <span class="estado-lbl">Fecha Suspensión</span>
                    <strong>{{ $fmtF($factura->fecha_corte) }}</strong>
                </td>
                <td style="padding:2px 8px;">
                    <span class="estado-lbl">Medio de Pago</span>
                    @if($empresa->nombre_banco)
                        {{ $empresa->nombre_banco }}
                        @if($empresa->numero_cuenta) — Cta: {{ $empresa->numero_cuenta }}@endif
                    @else
                        EFECTIVO / TRANSFERENCIA
                    @endif
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

{{-- Código de barras --}}
<div style="font-family:'Courier New',monospace;font-size:9pt;letter-spacing:3px;text-align:center;margin:5px 0 2px;">
    {{ str_pad($factura->numero_factura, 22, '0', STR_PAD_LEFT) }}
</div>

<div class="footer-txt">
    Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    &mdash; {{ strtoupper($empresa->nombre) }}
    @if($empresa->texto_pie) &mdash; {{ $empresa->texto_pie }} @endif
</div>

@endforeach

</body>
</html>
