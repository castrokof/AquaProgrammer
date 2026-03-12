<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Facturas</title>
<style>
@page { size: letter; margin: 7mm 9mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 7pt; color: #111; line-height: 1.2; }
.page-break { page-break-after: always; }

/* ── Bordes tipo tabla ── */
.tbl { width:100%; border-collapse:collapse; }
.tbl th, .tbl td { border:1px solid #bbb; padding:2px 3px; vertical-align:middle; }
.tbl th { background:#f0f0f0; font-weight:700; font-size:6.5pt; text-align:center; }
.cell-lbl { font-weight:700; font-size:6.5pt; }
.r { text-align:right; }
.c { text-align:center; }

/* ── Header empresa / datos suscriptor ── */
.hdr-outer { border:1.5px solid #888; border-collapse:collapse; width:100%; margin-bottom:4px; }
.hdr-empresa { border-right:1.5px solid #888; padding:5px 7px; vertical-align:top; width:42%; }
.hdr-suscriptor { padding:0; vertical-align:top; width:58%; }
.hdr-suscriptor-title { background:#2e50e4; color:white; font-weight:700; font-size:7pt;
    text-transform:uppercase; padding:3px 6px; letter-spacing:.4px; }
.hdr-suscriptor-body { padding:0; }
.hdr-suscriptor-body table { width:100%; border-collapse:collapse; }
.hdr-suscriptor-body table td { padding:2px 5px; font-size:7pt; border-bottom:1px solid #e8e8e8; }
.hdr-suscriptor-body table tr:last-child td { border-bottom:none; }
.empresa-name { font-size:9pt; font-weight:bold; color:#1a1a1a; }
.empresa-sub { font-size:6.5pt; color:#555; margin-top:1px; }
.empresa-num { font-size:7pt; color:#333; margin-top:3px; }
.num-fact { font-size:13pt; font-weight:bold; color:#2e50e4; }
.fact-badge { display:inline-block; padding:1px 5px; border-radius:4px; font-size:6pt; font-weight:bold; }
.badge-auto   { background:#e0f2fe; color:#0369a1; }
.badge-manual { background:#fef3c7; color:#b45309; }
.badge-obs    { background:#fef9c3; color:#713f12; border:1px solid #fde047; border-radius:4px;
    padding:1px 5px; font-size:6.5pt; margin-top:2px; display:block; }

/* ── Sección de consumo (2 columnas) ── */
.detalle-title { background:#2e50e4; color:white; font-weight:700; font-size:7pt;
    padding:3px 6px; text-transform:uppercase; letter-spacing:.4px; margin-bottom:0; }
.servicio-title { background:#e8eeff; color:#1e3a8a; font-weight:700; font-size:7pt;
    padding:2px 5px; text-align:center; border:1px solid #bbb; border-bottom:none; }
.tabla-consumo th { font-size:6.5pt; padding:2px 3px; background:#f5f7ff; color:#374151; }
.tabla-consumo td { font-size:6.5pt; padding:2px 3px; }
.tabla-consumo tfoot td { font-weight:700; background:#eef2ff; font-size:7pt; }
.subsidio-pos { color:#166534; }
.subsidio-neg { color:#991b1b; }

/* ── Resumen del cobro ── */
.resumen-title { background:#2e50e4; color:white; font-weight:700; font-size:7pt;
    padding:3px 6px; text-transform:uppercase; letter-spacing:.4px; }
.resumen-tbl td { padding:2px 6px; font-size:7pt; border-bottom:1px solid #f0f0f0; }
.resumen-tbl tr:last-child td { border-bottom:none; }
.resumen-total td { background:#2e50e4; color:white; font-weight:700; font-size:9pt; padding:4px 6px; }

/* ── Gráfica de barras (SVG) ── */
.barras-title { font-size:6.5pt; font-weight:700; color:#374151; margin-bottom:2px; text-align:center; }

/* ── Créditos ── */
.creditos-title { background:#f3f4f6; font-weight:700; font-size:6.5pt; padding:2px 5px;
    border:1px solid #bbb; border-bottom:none; color:#374151; text-transform:uppercase; }

/* ── Último pago / estado ── */
.estado-bar { border:1.5px solid #888; border-collapse:collapse; width:100%; margin-top:4px; }
.estado-cell { padding:4px 7px; vertical-align:top; font-size:7pt; }
.estado-lbl  { font-weight:700; font-size:6.5pt; color:#555; text-transform:uppercase;
    letter-spacing:.3px; display:block; margin-bottom:1px; }
.total-pagar-box { background:#2e50e4; color:white; border-radius:0; padding:4px 8px;
    text-align:right; }
.total-pagar-box .lbl { font-size:7pt; font-weight:700; }
.total-pagar-box .val { font-size:12pt; font-weight:bold; }
.footer-txt { text-align:center; font-size:6.5pt; color:#777; margin-top:3px;
    border-top:1px solid #ccc; padding-top:3px; }
.obs-box { border:1px solid #fde047; background:#fefce8; border-radius:4px; padding:2px 6px;
    font-size:6.5pt; color:#713f12; margin-top:3px; }
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
    $subsidioAc   = (float)($factura->subsidio_emergencia    ?? 0); // + = subsidio (descuento), - = contribución (cargo)
    $esSubsidio   = $subsidioAc > 0;
    $subsidioAl   = (float)($factura->subsidio_alcantarillado ?? 0);
    $esSubsidioAl = $subsidioAl > 0;

    // ── Neto (subtotal ya incluye subsidio aplicado) ──
    $netoAcueducto      = (float)($factura->total_facturacion_acueducto ?? 0);
    $netoAlcantarillado = (float)($factura->subtotal_alcantarillado      ?? 0);

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
            <img src="{{ $logoB64 }}" style="max-height:100px;max-width:100px;object-fit:contain;display:block;margin-bottom:2px;margin-top:6px;">
            @endif
        @endif
        <div class="empresa-name">{{ strtoupper($empresa->nombre) }}</div>
        @if($empresa->nit)
        <div class="empresa-sub">NIT {{ $empresa->nit }}</div>
        @endif
        <div class="empresa-sub">{{ $empresa->texto_documento_equivalente }}</div>
        <div class="empresa-num">No. <strong>{{ $numFacturaMostrar }}</strong></div>
        <div style="margin-top:3px;font-size:6.5pt;color:#444;">
            Período: <strong>{{ $factura->mes_cuenta }}</strong><br>
            Del {{ $fmtF($factura->fecha_del) }} al {{ $fmtF($factura->fecha_hasta) }}<br>
            Expedición: {{ $fmtF($factura->fecha_expedicion) }}
            &nbsp;|&nbsp; Vence: <strong>{{ $fmtF($factura->fecha_vencimiento) }}</strong><br>
            Corte: {{ $fmtF($factura->fecha_corte) }}
        </div>
        <div style="margin-top:2px;">
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
        </table>
        </div>
        {{-- Caja de pago destacada --}}
        <div style="margin:4px 6px 5px 6px;border:2px solid #2e50e4;border-radius:3px;overflow:hidden;">
            <div style="background:#2e50e4;color:white;font-weight:700;font-size:7pt;padding:3px 7px;text-transform:uppercase;letter-spacing:.4px;">
                Información de Pago
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 7px;vertical-align:middle;width:55%;border-right:1px solid #d1d5db;">
                        <div style="font-size:6pt;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;">Total a Pagar</div>
                        <div style="font-size:14pt;font-weight:bold;color:#2e50e4;line-height:1.1;">{{ $nf($factura->total_a_pagar) }}</div>
                        @if($factura->saldo_anterior > 0)
                        <div style="font-size:6pt;color:#dc2626;">Incluye saldo ant. {{ $nf($factura->saldo_anterior) }}</div>
                        @endif
                    </td>
                    <td style="padding:4px 7px;vertical-align:middle;font-size:6.5pt;">
                        <div style="margin-bottom:3px;">
                            <span style="font-weight:700;color:#374151;display:block;font-size:6pt;text-transform:uppercase;">Pague antes de</span>
                            <span style="font-size:8pt;font-weight:700;color:#166534;">{{ $fmtF($factura->fecha_vencimiento) }}</span>
                        </div>
                        <div style="border-top:1px solid #e5e7eb;padding-top:3px;">
                            <span style="font-weight:700;color:#374151;display:block;font-size:6pt;text-transform:uppercase;">Fecha de corte</span>
                            <span style="font-size:8pt;font-weight:700;color:#b45309;">{{ $fmtF($factura->fecha_corte) }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </td>
</tr>
</table>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- LECTURA + DETALLE DEL CONSUMO                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="detalle-title" style="margin-bottom:3px;">Detalle del Consumo</div>
<table class="tbl" style="margin-bottom:3px;">
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
<table style="width:100%;border-collapse:collapse;margin-bottom:3px;">
<tr style="vertical-align:top;">

@if($hasAcueducto)
<td style="width:{{ $hasAlcantarillado ? '50%' : '100%' }};padding-right:{{ $hasAlcantarillado ? '3px' : '0' }};">
    <div class="servicio-title">ACUEDUCTO</div>
    <table class="tbl tabla-consumo">
        <thead>
            <tr>
                <th style="text-align:left;">Concepto</th>
                <th>m³</th>
                <th>Tarifa</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- Cargo fijo --}}
            <tr>
                <td>Cargo fijo</td>
                <td class="c">—</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_acueducto,2,',','.') }}</td>
            </tr>
            {{-- Básico --}}
            @if($factura->consumo_basico_acueducto_m3 > 0 || true)
            <tr>
                <td>Consumo básico</td>
                <td class="c">{{ $factura->consumo_basico_acueducto_m3 }}</td>
                <td class="r">{{ number_format($refBasAc,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_acueducto_valor,2,',','.') }}</td>
            </tr>
            @endif
            {{-- Complementario --}}
            @if($factura->consumo_complementario_acueducto_m3 > 0)
            <tr>
                <td>Consumo comp.</td>
                <td class="c">{{ $factura->consumo_complementario_acueducto_m3 }}</td>
                <td class="r">{{ number_format($refCompAc,2,',','.') }}</td>
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
            </tr>
            @endif
            {{-- Subsidio como fila --}}
            @if($subsidioAc != 0)
            <tr>
                <td colspan="3" class="{{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}" style="font-style:italic;">
                    {{ $esSubsidio ? 'Subsidio acueducto' : 'Contribución acueducto' }}
                </td>
                <td class="r {{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidio ? '-' : '+' }}{{ number_format(abs($subsidioAc),2,',','.') }}
                </td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="c">{{ $factura->consumo_m3 }}</td>
                <td></td>
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
                <th>Tarifa</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cargo fijo</td>
                <td class="c">—</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->cargo_fijo_alcantarillado,2,',','.') }}</td>
            </tr>
            @if($factura->consumo_basico_alcantarillado_m3 > 0 || true)
            <tr>
                <td>Consumo básico</td>
                <td class="c">{{ $factura->consumo_basico_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refBasAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_basico_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
            @if($factura->consumo_complementario_alcantarillado_m3 > 0)
            <tr>
                <td>Consumo comp.</td>
                <td class="c">{{ $factura->consumo_complementario_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refCompAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_complementario_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
            @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
            <tr>
                <td>Consumo sunt.</td>
                <td class="c">{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td>
                <td class="r">{{ number_format($refSuntAl,2,',','.') }}</td>
                <td class="r">{{ number_format($factura->consumo_suntuario_alcantarillado_valor,2,',','.') }}</td>
            </tr>
            @endif
            {{-- Subsidio como fila --}}
            @if($subsidioAl != 0)
            <tr>
                <td colspan="3" class="{{ $esSubsidioAl ? 'subsidio-pos' : 'subsidio-neg' }}" style="font-style:italic;">
                    {{ $esSubsidioAl ? 'Subsidio alcantarillado' : 'Contribución alcantarillado' }}
                </td>
                <td class="r {{ $esSubsidioAl ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidioAl ? '-' : '+' }}{{ number_format(abs($subsidioAl),2,',','.') }}
                </td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="c">{{ $factura->consumo_m3 }}</td>
                <td></td>
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
<table style="width:100%;border-collapse:collapse;margin-bottom:3px;">
<tr style="vertical-align:top;">

{{-- Columna izquierda: créditos + barras --}}
<td style="width:46%;padding-right:3px;">

    {{-- Créditos y financiación --}}
    <div class="creditos-title">Créditos Otorgados y Financiación</div>
    <table class="tbl" style="font-size:7.5pt;">
        <thead>
            <tr>
                <th style="text-align:left;">Descripción</th>
                <th>Val. cuota</th>
                <th>Saldo</th>
                <th>Val. prestado</th>
                <th>Cuotas tot.</th>
                <th>Pagas</th>
                <th>Pend.</th>
            </tr>
        </thead>
        <tbody>
            @if($factura->cuota_otros_cobros_acueducto > 0)
            <tr>
                <td>Otros cobros acueducto</td>
                <td class="r">{{ number_format($factura->cuota_otros_cobros_acueducto,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->saldo_otros_cobros_acueducto,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->otros_cobros_acueducto ?? 0,0,',','.') }}</td>
                @php
                    $cuotasTotAc  = ($factura->cuota_otros_cobros_acueducto > 0 && $factura->otros_cobros_acueducto > 0)
                        ? ceil($factura->otros_cobros_acueducto / $factura->cuota_otros_cobros_acueducto) : 0;
                    $cuotasPagAc  = ($factura->cuota_otros_cobros_acueducto > 0 && $factura->saldo_otros_cobros_acueducto >= 0)
                        ? max(0, $cuotasTotAc - ceil($factura->saldo_otros_cobros_acueducto / $factura->cuota_otros_cobros_acueducto)) : 0;
                    $cuotasPendAc = max(0, $cuotasTotAc - $cuotasPagAc);
                @endphp
                <td class="c">{{ $cuotasTotAc ?: '—' }}</td>
                <td class="c">{{ $cuotasPagAc ?: '—' }}</td>
                <td class="c">{{ $cuotasPendAc ?: '—' }}</td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_alcantarillado > 0)
            <tr>
                <td>Otros cobros alcantarillado</td>
                <td class="r">{{ number_format($factura->cuota_otros_cobros_alcantarillado,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->saldo_otros_cobros_alcantarillado,0,',','.') }}</td>
                <td class="r">{{ number_format($factura->otros_cobros_alcantarillado ?? 0,0,',','.') }}</td>
                @php
                    $cuotasTotAl  = ($factura->cuota_otros_cobros_alcantarillado > 0 && $factura->otros_cobros_alcantarillado > 0)
                        ? ceil($factura->otros_cobros_alcantarillado / $factura->cuota_otros_cobros_alcantarillado) : 0;
                    $cuotasPagAl  = ($factura->cuota_otros_cobros_alcantarillado > 0 && $factura->saldo_otros_cobros_alcantarillado >= 0)
                        ? max(0, $cuotasTotAl - ceil($factura->saldo_otros_cobros_alcantarillado / $factura->cuota_otros_cobros_alcantarillado)) : 0;
                    $cuotasPendAl = max(0, $cuotasTotAl - $cuotasPagAl);
                @endphp
                <td class="c">{{ $cuotasTotAl ?: '—' }}</td>
                <td class="c">{{ $cuotasPagAl ?: '—' }}</td>
                <td class="c">{{ $cuotasPendAl ?: '—' }}</td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_acueducto == 0 && $factura->cuota_otros_cobros_alcantarillado == 0)
            <tr><td colspan="7" class="c" style="color:#aaa;">Sin créditos otorgados</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total cuota</td>
                <td class="r">{{ number_format(($factura->cuota_otros_cobros_acueducto ?? 0) + ($factura->cuota_otros_cobros_alcantarillado ?? 0), 0, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Últimos 6 consumos (barras HTML — DomPDF compatible) --}}
    <div style="margin-top:4px;border:1px solid #bbb;padding:3px 5px;border-radius:0;">
        <div class="barras-title">Últimos 6 consumos + actual (m³)</div>
        @php
            $labels = ['M-6','M-5','M-4','M-3','M-2','M-1','Actual'];
            $chartH = 38;
        @endphp
        <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
            {{-- Fila: valores --}}
            <tr>
                @foreach($consumos as $i => $val)
                @php $v = (int)($val ?? 0); @endphp
                <td style="text-align:center;font-size:5pt;vertical-align:bottom;padding:0 1px;height:10px;border:none;">{{ $v > 0 ? $v : '' }}</td>
                @endforeach
            </tr>
            {{-- Fila: barras --}}
            <tr>
                @foreach($consumos as $i => $val)
                @php
                    $v        = (int)($val ?? 0);
                    $barH     = $v > 0 ? max(3, (int)round($v / $maxConsumo * $chartH)) : 2;
                    $spH      = $chartH - $barH;
                    $isActual = ($i === count($consumos) - 1);
                    $color    = $isActual ? '#2e50e4' : '#93c5fd';
                @endphp
                <td style="vertical-align:bottom;padding:0 1px;height:{{ $chartH }}px;border:none;border-bottom:1px solid #ccc;">
                    <div style="height:{{ $barH }}px;background:{{ $color }};border-radius:2px 2px 0 0;"></div>
                </td>
                @endforeach
            </tr>
            {{-- Fila: etiquetas --}}
            <tr>
                @foreach($labels as $i => $lbl)
                @php $isActual = ($i === count($labels) - 1); @endphp
                <td style="text-align:center;font-size:5pt;padding:1px 0 0;border:none;color:{{ $isActual ? '#2e50e4' : '#6b7280' }};font-weight:{{ $isActual ? 'bold' : 'normal' }};">{{ $lbl }}</td>
                @endforeach
            </tr>
        </table>
    </div>

</td>

{{-- Columna derecha: Resumen del cobro --}}
<td style="width:54%;padding-left:3px;vertical-align:top;">
    <div class="resumen-title">Resumen del Cobro</div>
    <table class="tbl resumen-tbl" style="border-top:none;">
        <thead>
            <tr>
                <th style="text-align:left;background:#e8eeff;color:#1e3a8a;font-size:6.5pt;">Concepto</th>
                <th style="background:#e8eeff;color:#1e3a8a;font-size:6.5pt;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @if($hasAcueducto)
            <tr><td>Valor Acueducto</td><td class="r">{{ $nf($netoAcueducto) }}</td></tr>
            @endif
            @if($hasAlcantarillado)
            <tr><td>Valor Alcantarillado</td><td class="r">{{ $nf($netoAlcantarillado) }}</td></tr>
            @endif
            @php $otrosCobros = ($factura->cuota_otros_cobros_acueducto ?? 0) + ($factura->cuota_otros_cobros_alcantarillado ?? 0); @endphp
            @if($otrosCobros > 0)
            <tr><td>Otros Cobros</td><td class="r">{{ $nf($otrosCobros) }}</td></tr>
            @endif
            @if($factura->saldo_anterior > 0)
            <tr>
                <td style="color:#dc2626;">+ Saldo Anterior</td>
                <td class="r" style="color:#dc2626;font-weight:700;">{{ $nf($factura->saldo_anterior) }}</td>
            </tr>
            @endif
            @if($ultimoPago)
            <tr>
                <td style="color:#166534;">Último Pago
                    <span style="font-size:5.5pt;color:#555;">({{ $ultimoPago->fecha_pago ? \Carbon\Carbon::parse($ultimoPago->fecha_pago)->format('d/m/Y') : '—' }})</span>
                </td>
                <td class="r" style="color:#166534;font-weight:700;">{{ $nf($ultimoPago->total_pago_realizado) }}</td>
            </tr>
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
<div style="font-family:'Courier New',monospace;font-size:7.5pt;letter-spacing:2px;text-align:center;margin:3px 0 1px;">
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
