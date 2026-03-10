<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pago de Factura — {{ $empresa->nombre }}</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
* { box-sizing:border-box; }
body { background:linear-gradient(135deg,#1e3a8a 0%,#2e50e4 50%,#7c3aed 100%);
    min-height:100vh; font-family:'Segoe UI',Arial,sans-serif; }

.pay-wrapper { max-width:680px; margin:0 auto; padding:30px 16px 60px; }

/* Header */
.pay-header { text-align:center; margin-bottom:32px; }
.pay-header .logo { max-height:70px; max-width:180px; object-fit:contain;
    background:white; padding:8px 16px; border-radius:12px; margin-bottom:14px; display:inline-block; }
.pay-header h1 { color:white; font-size:1.5rem; font-weight:700; margin:0; }
.pay-header p  { color:rgba(255,255,255,.75); font-size:.9rem; margin:4px 0 0; }

/* Cards */
.pay-card { background:white; border-radius:20px; padding:28px 28px 24px;
    box-shadow:0 20px 60px rgba(0,0,0,.22); margin-bottom:20px; }
.card-title { font-size:1rem; font-weight:700; color:#1e3a8a; margin-bottom:18px;
    display:flex; align-items:center; gap:8px; }
.card-title i { width:28px; height:28px; background:#e8eeff; border-radius:8px;
    display:flex; align-items:center; justify-content:center; color:#2e50e4; font-size:.85rem; }

/* Search */
.search-group { display:flex; gap:10px; }
.search-group input { flex:1; border:2px solid #e2e8f0; border-radius:12px;
    padding:12px 16px; font-size:1rem; transition:border-color .2s; }
.search-group input:focus { border-color:#2e50e4; outline:none; box-shadow:0 0 0 3px rgba(46,80,228,.12); }
.btn-buscar { background:linear-gradient(135deg,#2e50e4,#7c3aed); color:white;
    border:none; border-radius:12px; padding:12px 24px; font-weight:700; cursor:pointer;
    white-space:nowrap; transition:opacity .2s; }
.btn-buscar:hover { opacity:.88; }

/* Invoice detail */
.factura-header { display:flex; justify-content:space-between; align-items:flex-start;
    flex-wrap:wrap; gap:12px; margin-bottom:20px; padding-bottom:16px; border-bottom:2px solid #f0f0f0; }
.factura-num { font-size:1.4rem; font-weight:800; color:#1e3a8a; }
.factura-num small { font-size:.75rem; font-weight:600; color:#718096; display:block; }
.estado-badge { padding:4px 14px; border-radius:20px; font-size:.8rem; font-weight:700; }
.estado-PENDIENTE  { background:#fef3c7; color:#92400e; }
.estado-VENCIDA    { background:#fee2e2; color:#991b1b; }
.estado-PAGADA     { background:#d1fae5; color:#065f46; }
.estado-ANULADA    { background:#e5e7eb; color:#4b5563; }

.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; margin-bottom:20px; }
@media(max-width:480px){ .info-grid { grid-template-columns:1fr; } }
.info-item .lbl { font-size:.72rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.4px; }
.info-item .val { font-size:.92rem; color:#1f2937; font-weight:600; }

.totales-box { background:#f8faff; border:2px solid #e8eeff; border-radius:14px; padding:16px 20px; }
.totales-row { display:flex; justify-content:space-between; padding:5px 0;
    border-bottom:1px solid #f0f4ff; font-size:.9rem; }
.totales-row:last-child { border-bottom:none; }
.totales-row.grand { font-weight:800; font-size:1.1rem; color:#1e3a8a; padding-top:10px; margin-top:4px; border-top:2px solid #c7d2fe; }
.totales-row.saldo-rojo { color:#dc2626; font-weight:700; }

/* Wompi button wrapper */
.wompi-wrapper { margin-top:22px; text-align:center; }
.wompi-wrapper form { display:inline-block; }
.wompi-info { font-size:.78rem; color:#6b7280; margin-top:8px; }
.wompi-info a { color:#2e50e4; }

/* Alerta ya pagada */
.alert-pagada { background:#d1fae5; border:2px solid #6ee7b7; border-radius:14px;
    padding:18px 22px; display:flex; align-items:center; gap:14px; margin-top:16px; }
.alert-pagada i { font-size:2rem; color:#059669; }
.alert-pagada p { margin:0; font-size:.95rem; color:#065f46; font-weight:600; }

/* Error */
.error-box { background:#fee2e2; border:1.5px solid #fca5a5; border-radius:12px;
    padding:12px 16px; color:#991b1b; font-size:.9rem; margin-bottom:16px; }

/* Pagos registrados */
.pago-item { display:flex; justify-content:space-between; align-items:center;
    padding:10px 14px; border-radius:10px; background:#f9fafb; margin-bottom:8px;
    border:1px solid #e5e7eb; font-size:.88rem; }
.pago-item .pago-lbl { color:#374151; }
.pago-item .pago-val { font-weight:700; color:#059669; }

/* Footer */
.pay-footer { text-align:center; color:rgba(255,255,255,.55); font-size:.78rem; margin-top:20px; }
</style>
</head>
<body>
<div class="pay-wrapper">

    {{-- Header --}}
    <div class="pay-header">
        @if($empresa->logo_path)
        <div><img src="{{ $empresa->logoUrl() }}" class="logo" alt="{{ $empresa->nombre }}"></div>
        @endif
        <h1>{{ $empresa->nombre }}</h1>
        <p>Pago en línea de facturas de servicios públicos</p>
    </div>

    {{-- Errores de búsqueda --}}
    @if($errors->any())
    <div class="error-box">
        <i class="fa fa-exclamation-circle"></i>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Búsqueda --}}
    <div class="pay-card">
        <div class="card-title">
            <i class="fa fa-search"></i> Busca tu factura
        </div>
        <form action="{{ route('pago-publico.buscar') }}" method="POST">
            @csrf
            <div class="search-group">
                <input type="text" name="busqueda"
                    value="{{ old('busqueda') }}"
                    placeholder="Número de factura o código de suscriptor"
                    autofocus>
                <button type="submit" class="btn-buscar">
                    <i class="fa fa-search"></i> Buscar
                </button>
            </div>
            <div style="font-size:.78rem;color:#9ca3af;margin-top:8px;">
                Ingresa el número de factura (ej: {{ $empresa->prefijo_factura }}202403000001)
                o tu código de suscriptor.
            </div>
        </form>
    </div>

    {{-- Resultado de búsqueda --}}
    @isset($factura)
    <div class="pay-card">
        <div class="factura-header">
            <div>
                <div class="factura-num">
                    <small>Factura</small>
                    {{ $empresa->prefijo_factura }}{{ $factura->numero_factura }}
                </div>
                <div style="font-size:.85rem;color:#6b7280;margin-top:4px;">
                    {{ $factura->mes_cuenta }} · Vence: {{ $factura->fecha_vencimiento ? \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') : '—' }}
                </div>
            </div>
            <span class="estado-badge estado-{{ $factura->estado }}">{{ $factura->estado }}</span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="lbl">Nombre</div>
                <div class="val">
                    @if($factura->cliente)
                        {{ trim($factura->cliente->nombre . ' ' . $factura->cliente->apellido) }}
                    @else — @endif
                </div>
            </div>
            <div class="info-item">
                <div class="lbl">Suscriptor</div>
                <div class="val">{{ $factura->suscriptor }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Dirección / Sector</div>
                <div class="val">{{ $factura->sector ?? '—' }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Medidor</div>
                <div class="val">{{ $factura->serie_medidor ?? '—' }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Período</div>
                <div class="val">{{ $factura->mes_cuenta }}</div>
            </div>
            <div class="info-item">
                <div class="lbl">Consumo</div>
                <div class="val">{{ $factura->consumo_m3 }} m³</div>
            </div>
        </div>

        {{-- Totales --}}
        <div class="totales-box">
            @php $nf = fn($v) => '$'.number_format((float)($v??0),0,',','.'); @endphp
            @if($factura->total_facturacion_acueducto > 0)
            <div class="totales-row"><span>Acueducto</span><span>{{ $nf($factura->total_facturacion_acueducto) }}</span></div>
            @endif
            @if($factura->subtotal_alcantarillado > 0)
            <div class="totales-row"><span>Alcantarillado</span><span>{{ $nf($factura->subtotal_alcantarillado) }}</span></div>
            @endif
            @if($factura->saldo_anterior > 0)
            <div class="totales-row saldo-rojo"><span>Saldo anterior</span><span>{{ $nf($factura->saldo_anterior) }}</span></div>
            @endif
            <div class="totales-row grand">
                <span>TOTAL FACTURA</span><span>{{ $nf($factura->total_a_pagar) }}</span>
            </div>
        </div>

        {{-- Pagos previos --}}
        @if($factura->pagos->count() > 0)
        <div style="margin-top:16px;">
            <div style="font-size:.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;margin-bottom:8px;">Pagos registrados</div>
            @foreach($factura->pagos->sortByDesc('fecha_pago') as $p)
            <div class="pago-item">
                <div class="pago-lbl">
                    {{ $p->fecha_pago ? \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') : '—' }}
                    · {{ $p->medio_pago }}
                    @if($p->banco) · {{ $p->banco }}@endif
                </div>
                <div class="pago-val">- {{ $nf($p->total_pago_realizado) }}</div>
            </div>
            @endforeach
            <div style="text-align:right;font-size:.9rem;padding:4px 14px;">
                Saldo pendiente: <strong style="color:{{ $saldoPendiente > 0 ? '#dc2626':'#059669' }};">{{ $nf($saldoPendiente) }}</strong>
            </div>
        </div>
        @endif

        {{-- Botón de pago --}}
        @if(in_array($factura->estado, ['PAGADA','ANULADA']))
            <div class="alert-pagada">
                <i class="fa fa-check-circle"></i>
                <p>Esta factura ya se encuentra <strong>{{ $factura->estado }}</strong>. No requiere pago adicional.</p>
            </div>

        @elseif($saldoPendiente <= 0)
            <div class="alert-pagada">
                <i class="fa fa-check-circle"></i>
                <p>El saldo de esta factura ya está en $0. ¡Gracias por tu pago!</p>
            </div>

        @elseif($wompiActivo)
            <div class="wompi-wrapper">
                <form>
                    <script
                        src="{{ $empresa->wompi_test_mode
                            ? 'https://checkout.wompi.co/widget.js'
                            : 'https://checkout.wompi.co/widget.js' }}"
                        data-render="button"
                        data-public-key="{{ $empresa->wompi_public_key }}"
                        data-currency="COP"
                        data-amount-in-cents="{{ $amountCents }}"
                        data-reference="{{ $referencia }}"
                        data-signature:integrity="{{ $firma }}"
                        data-redirect-url="{{ $empresa->wompi_redirect_url ?: route('pago-publico.resultado') }}"
                        data-customer-data:email="{{ optional($factura->cliente)->email ?? '' }}"
                        data-customer-data:full-name="{{ optional($factura->cliente) ? trim($factura->cliente->nombre.' '.$factura->cliente->apellido) : '' }}"
                        data-customer-data:phone-number="{{ optional($factura->cliente)->telefono ?? '' }}"
                    ></script>
                </form>
                <div class="wompi-info">
                    Pago seguro procesado por <a href="https://wompi.co" target="_blank">Wompi</a> (Bancolombia).
                    Tu información está protegida con cifrado SSL.
                </div>
            </div>

        @else
            <div style="margin-top:20px;padding:16px;background:#fef3c7;border-radius:12px;border:1.5px solid #fbbf24;text-align:center;">
                <i class="fa fa-info-circle" style="color:#d97706;"></i>
                <strong style="color:#92400e;"> Pago en línea no disponible.</strong><br>
                <span style="font-size:.85rem;color:#78350f;">
                    Por favor acércate a nuestras oficinas o comunícate al
                    {{ $empresa->telefono ?? 'nuestra línea de atención' }}.
                </span>
                @if($empresa->nombre_banco)
                <div style="margin-top:8px;font-size:.85rem;color:#78350f;">
                    También puedes consignar en <strong>{{ $empresa->nombre_banco }}</strong>
                    @if($empresa->numero_cuenta)Cta: <strong>{{ $empresa->numero_cuenta }}</strong>@endif
                </div>
                @endif
            </div>
        @endif

    </div>
    @endisset

    <div class="pay-footer">
        &copy; {{ date('Y') }} {{ $empresa->nombre }}
        @if($empresa->texto_pie) · {{ $empresa->texto_pie }} @endif
    </div>
</div>
</body>
</html>
