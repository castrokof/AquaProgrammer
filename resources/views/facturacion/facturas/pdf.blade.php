<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Facturas</title>
<style>
@page { size: letter; margin: 12mm 15mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 9pt; color: #222; line-height: 1.4; }

/* Separador entre facturas */
.page-break { page-break-after: always; }

/* ── Header ── */
.fact-header { border-bottom: 2.5px solid #2e50e4; margin-bottom: 12px; padding-bottom: 10px; }
.fact-header table { width: 100%; }
.empresa { font-size: 13pt; font-weight: bold; color: #2e50e4; letter-spacing: .4px; }
.sub-empresa { font-size: 8pt; color: #555; margin-top: 2px; }
.periodo-txt { font-size: 8pt; color: #666; margin-top: 6px; }
.num-fact-lbl { font-size: 7pt; color: #888; text-transform: uppercase; text-align: right; }
.num-fact-val { font-size: 20pt; font-weight: bold; color: #2e50e4; text-align: right; letter-spacing: 1px; }
.fechas-fact { font-size: 7.5pt; color: #555; text-align: right; margin-top: 4px; }

/* ── Secciones ── */
.section { margin-bottom: 10px; }
.section-title { font-size: 7.5pt; font-weight: bold; color: #2e50e4; text-transform: uppercase;
    letter-spacing: .5px; border-bottom: 1px solid #c7d2fe; padding-bottom: 3px; margin-bottom: 7px; }
.info-grid { width: 100%; }
.info-grid td { padding: 2px 5px; font-size: 8.5pt; vertical-align: top; }
.info-lbl { font-weight: bold; color: #555; width: 110px; }

/* ── Tabla de conceptos ── */
.tabla-conceptos { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
.tabla-conceptos th { background: #eef2ff; color: #3730a3; font-size: 7.5pt; font-weight: bold;
    text-transform: uppercase; padding: 5px 8px; text-align: right; border-bottom: 1.5px solid #c7d2fe; }
.tabla-conceptos th:first-child { text-align: left; }
.tabla-conceptos td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; text-align: right; }
.tabla-conceptos td:first-child { text-align: left; }
.tabla-conceptos tfoot td { font-weight: bold; border-top: 1.5px solid #c7d2fe; padding: 5px 8px;
    background: #f5f7ff; text-align: right; }
.tabla-conceptos tfoot td:first-child { text-align: left; }
.subsidio-pos { color: #166534; }
.subsidio-neg { color: #991b1b; }

/* ── Mora ── */
.mora-box { background: #fff5f5; border: 1px solid #fca5a5; border-radius: 5px;
    padding: 7px 12px; display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px; }
.mora-lbl { font-size: 8pt; font-weight: bold; color: #b91c1c; }
.mora-sub { font-size: 7.5pt; color: #dc2626; }
.mora-val { font-size: 12pt; font-weight: bold; color: #b91c1c; }

/* ── Total final ── */
.total-box { background: linear-gradient(135deg, #2e50e4, #2b0c49); color: white;
    border-radius: 8px; padding: 12px 18px; display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 10px; }
.total-box .lbl { font-size: 9pt; font-weight: bold; opacity: .9; }
.total-box .sub { font-size: 7.5pt; opacity: .7; }
.total-box .val { font-size: 18pt; font-weight: bold; }

/* ── Tipo badge ── */
.tipo-auto   { background: #e0f2fe; color: #0369a1; border-radius: 4px; padding: 1px 7px; font-size: 7pt; font-weight: bold; }
.tipo-manual { background: #fef3c7; color: #b45309; border-radius: 4px; padding: 1px 7px; font-size: 7pt; font-weight: bold; }

/* ── Pagos ── */
.pago-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e5e7eb; font-size: 8.5pt; }
.pago-monto { font-weight: bold; color: #166534; }

/* ── Código de barras / pie ── */
.barcode { font-family: 'Courier New', monospace; font-size: 9pt; letter-spacing: 3px;
    text-align: center; margin: 10px 0 6px; }
.footer-txt { text-align: center; font-size: 7.5pt; color: #888; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>

@foreach($facturas as $factura)
@php
    $nf = function($v) { return number_format((float)($v ?? 0), 0, ',', '.'); };

    $fmtFecha = function($f) {
        if (!$f) return '—';
        if ($f instanceof \Carbon\Carbon) return $f->format('d/m/Y');
        return \Carbon\Carbon::parse($f)->format('d/m/Y');
    };
@endphp

@if(!$loop->first)
<div class="page-break"></div>
@endif

{{-- ── HEADER ── --}}
<div class="fact-header">
<table>
    <tr>
        <td style="width:55%;vertical-align:top;">
            <div class="empresa">ACUEDUCTO ALTO LOS MANGOS</div>
            <div class="sub-empresa">Servicio Público Domiciliario</div>
            <div class="periodo-txt">
                {{ $factura->mes_cuenta }}<br>
                Del {{ $fmtFecha($factura->fecha_del) }} al {{ $fmtFecha($factura->fecha_hasta) }}
            </div>
        </td>
        <td style="vertical-align:top;">
            <div class="num-fact-lbl">Factura N°</div>
            <div class="num-fact-val">{{ $factura->numero_factura }}</div>
            <div class="fechas-fact">
                Expide: {{ $fmtFecha($factura->fecha_expedicion) }}<br>
                Vence:&nbsp; {{ $fmtFecha($factura->fecha_vencimiento) }}<br>
                Corte:&nbsp; {{ $fmtFecha($factura->fecha_corte) }}
            </div>
            <div style="text-align:right;margin-top:4px;">
                @if($factura->es_automatica)
                    <span class="tipo-auto">AUTO</span>
                @else
                    <span class="tipo-manual">MANUAL</span>
                @endif
                &nbsp;<span style="font-size:7.5pt;color:#555;">{{ $factura->periodo }}</span>
            </div>
        </td>
    </tr>
</table>
</div>

{{-- ── DATOS SUSCRIPTOR ── --}}
<div class="section">
    <div class="section-title">&#128100; Datos del Suscriptor</div>
    <table class="info-grid">
        <tr>
            <td class="info-lbl">Suscriptor:</td>
            <td>{{ $factura->suscriptor }}</td>
            <td class="info-lbl">Estrato:</td>
            <td>E{{ $factura->estrato_snapshot ?? '—' }}</td>
            <td class="info-lbl">Tipo Uso:</td>
            <td>{{ $factura->clase_uso ?? '—' }}</td>
        </tr>
        <tr>
            <td class="info-lbl">Nombre:</td>
            <td colspan="5">
                @if($factura->cliente)
                    {{ trim($factura->cliente->nombre . ' ' . $factura->cliente->apellido) }}
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-lbl">Dirección:</td>
            <td>{{ $factura->cliente ? ($factura->cliente->direccion ?? '—') : '—' }}</td>
            <td class="info-lbl">Sector:</td>
            <td>{{ $factura->sector ?? '—' }}</td>
            <td class="info-lbl">Servicios:</td>
            <td>{{ $factura->servicios_snapshot ?? '—' }}</td>
        </tr>
    </table>
</div>

{{-- ── LECTURA ── --}}
<div class="section">
    <div class="section-title">&#128207; Lectura y Consumo</div>
    <table class="tabla-conceptos">
        <thead>
            <tr>
                <th style="text-align:left;">Lect. Anterior</th>
                <th>Lect. Actual</th>
                <th>Consumo m³</th>
                <th>Promedio 6m</th>
                <th>Serie Medidor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:left;">{{ $factura->lectura_anterior ?? '—' }}</td>
                <td>{{ $factura->lectura_actual ?? '—' }}</td>
                <td><strong>{{ $factura->consumo_m3 }}</strong></td>
                <td>{{ $factura->promedio_consumo_snapshot ?? '—' }}</td>
                <td>{{ $factura->serie_medidor ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── ACUEDUCTO ── --}}
@if(in_array($factura->servicios_snapshot, ['AG','AG-AL']))
<div class="section">
    <div class="section-title">&#128166; Acueducto</div>
    <table class="tabla-conceptos">
        <thead>
            <tr><th style="text-align:left;">Concepto</th><th>m³</th><th>Valor</th></tr>
        </thead>
        <tbody>
            <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ $nf($factura->cargo_fijo_acueducto) }}</td></tr>
            <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_acueducto_m3 }}</td><td>$ {{ $nf($factura->consumo_basico_acueducto_valor) }}</td></tr>
            @if($factura->consumo_complementario_acueducto_m3 > 0)
            <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_acueducto_m3 }}</td><td>$ {{ $nf($factura->consumo_complementario_acueducto_valor) }}</td></tr>
            @endif
            @if($factura->consumo_suntuario_acueducto_m3 > 0)
            <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_acueducto_m3 }}</td><td>$ {{ $nf($factura->consumo_suntuario_acueducto_valor) }}</td></tr>
            @endif
            @if($factura->subsidio_emergencia != 0)
            @php $esSubsidio = $factura->subsidio_emergencia > 0; @endphp
            <tr>
                <td class="{{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidio ? 'Subsidio Estrato' : 'Contribución Estrato' }}
                </td>
                <td>—</td>
                <td class="{{ $esSubsidio ? 'subsidio-pos' : 'subsidio-neg' }}">
                    {{ $esSubsidio ? '- ' : '+ ' }}$ {{ $nf(abs($factura->subsidio_emergencia)) }}
                </td>
            </tr>
            @endif
            @if($factura->cuota_otros_cobros_acueducto > 0)
            <tr><td>Otros Cobros</td><td>—</td><td>$ {{ $nf($factura->cuota_otros_cobros_acueducto) }}</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total Acueducto</td><td>$ {{ $nf($factura->subtotal_conexion_otros_acueducto) }}</td></tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── ALCANTARILLADO ── --}}
@if(in_array($factura->servicios_snapshot, ['AL','AG-AL']))
<div class="section">
    <div class="section-title">&#128028; Alcantarillado</div>
    <table class="tabla-conceptos">
        <thead>
            <tr><th style="text-align:left;">Concepto</th><th>m³</th><th>Valor</th></tr>
        </thead>
        <tbody>
            <tr><td>Cargo Fijo</td><td>—</td><td>$ {{ $nf($factura->cargo_fijo_alcantarillado) }}</td></tr>
            <tr><td>Consumo Básico</td><td>{{ $factura->consumo_basico_alcantarillado_m3 }}</td><td>$ {{ $nf($factura->consumo_basico_alcantarillado_valor) }}</td></tr>
            @if($factura->consumo_complementario_alcantarillado_m3 > 0)
            <tr><td>Consumo Complementario</td><td>{{ $factura->consumo_complementario_alcantarillado_m3 }}</td><td>$ {{ $nf($factura->consumo_complementario_alcantarillado_valor) }}</td></tr>
            @endif
            @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
            <tr><td>Consumo Suntuario</td><td>{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td><td>$ {{ $nf($factura->consumo_suntuario_alcantarillado_valor) }}</td></tr>
            @endif
            @if($factura->cuota_otros_cobros_alcantarillado > 0)
            <tr><td>Otros Cobros</td><td>—</td><td>$ {{ $nf($factura->cuota_otros_cobros_alcantarillado) }}</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total Alcantarillado</td><td>$ {{ $nf($factura->subtotal_conexion_otros_alcantarillado) }}</td></tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ── MORA ── --}}
@if($factura->saldo_anterior > 0)
<div class="mora-box">
    <div>
        <div class="mora-lbl">&#9888; Saldo Anterior en Mora</div>
        <div class="mora-sub">{{ $factura->facturas_en_mora ?? 0 }} factura(s) pendiente(s) de períodos anteriores</div>
    </div>
    <div class="mora-val">$ {{ $nf($factura->saldo_anterior) }}</div>
</div>
@endif

{{-- ── TOTAL ── --}}
<div class="total-box">
    <div>
        <div class="lbl">TOTAL A PAGAR</div>
        <div class="sub">Incluye todos los conceptos del período</div>
    </div>
    <div class="val">$ {{ $nf($factura->total_a_pagar) }}</div>
</div>

{{-- ── PAGOS ── --}}
@php
    $totalPagado    = $factura->pagos->sum('total_pago_realizado');
    $saldoPendiente = max(0, $factura->total_a_pagar - $totalPagado);
@endphp
@if($factura->pagos->count() > 0)
<div class="section">
    <div class="section-title">&#10003; Pagos Registrados</div>
    @foreach($factura->pagos as $p)
    <div class="pago-row">
        <div>
            <strong>{{ $p->numero_recibo ? 'Recibo: '.$p->numero_recibo : 'Sin número' }}</strong>
            — {{ $p->medio_pago }}
            <span style="color:#888;font-size:8pt;">
                {{ $p->fecha_pago ? \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') : '—' }}
            </span>
        </div>
        <div class="pago-monto">+ $ {{ $nf($p->total_pago_realizado) }}</div>
    </div>
    @endforeach
    <table style="width:100%;margin-top:6px;font-size:8.5pt;">
        <tr>
            <td style="color:#555;">Total pagado: <strong style="color:#166534;">$ {{ $nf($totalPagado) }}</strong></td>
            <td style="text-align:right;">Saldo pendiente: <strong style="color:{{ $saldoPendiente > 0 ? '#dc2626' : '#166534' }};">$ {{ $nf($saldoPendiente) }}</strong></td>
        </tr>
    </table>
</div>
@endif

{{-- ── PIE ── --}}
<div class="barcode">{{ str_pad($factura->numero_factura, 20, '0', STR_PAD_LEFT) }}</div>
<div class="footer-txt">
    Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} &mdash; ACUEDUCTO ALTO LOS MANGOS &mdash; Servicio Público Domiciliario
</div>

@endforeach

</body>
</html>
