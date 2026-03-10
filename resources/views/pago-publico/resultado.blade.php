<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Resultado de Pago — {{ $empresa->nombre }}</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
body { background:linear-gradient(135deg,#1e3a8a 0%,#2e50e4 50%,#7c3aed 100%);
    min-height:100vh; font-family:'Segoe UI',Arial,sans-serif;
    display:flex; align-items:center; justify-content:center; }
.result-card { background:white; border-radius:24px; padding:40px 36px;
    box-shadow:0 20px 60px rgba(0,0,0,.25); max-width:500px; width:100%; text-align:center; margin:20px; }
.icon-ok  { font-size:4rem; color:#059669; margin-bottom:16px; }
.icon-err { font-size:4rem; color:#dc2626; margin-bottom:16px; }
.icon-pend{ font-size:4rem; color:#d97706; margin-bottom:16px; }
h2 { font-weight:800; margin-bottom:8px; }
.ok-color   { color:#059669; }
.err-color  { color:#dc2626; }
.pend-color { color:#d97706; }
.detail-row { display:flex; justify-content:space-between; padding:8px 0;
    border-bottom:1px solid #f0f0f0; font-size:.92rem; }
.detail-row:last-child { border-bottom:none; }
.btn-back { background:linear-gradient(135deg,#2e50e4,#7c3aed); color:white;
    border:none; border-radius:12px; padding:12px 28px; font-weight:700;
    font-size:.95rem; margin-top:24px; display:inline-block; text-decoration:none; }
.btn-back:hover { color:white; opacity:.88; }
</style>
</head>
<body>
<div class="result-card">

    @if($empresa->logo_path)
    <img src="{{ $empresa->logoUrl() }}" style="max-height:50px;object-fit:contain;margin-bottom:20px;">
    @endif

    @php $status = $transaccion['status'] ?? null; @endphp

    @if($status === 'APPROVED')
        <div class="icon-ok"><i class="fa fa-check-circle"></i></div>
        <h2 class="ok-color">¡Pago aprobado!</h2>
        <p style="color:#6b7280;">Tu pago fue procesado exitosamente. Gracias.</p>

    @elseif($status === 'DECLINED')
        <div class="icon-err"><i class="fa fa-times-circle"></i></div>
        <h2 class="err-color">Pago rechazado</h2>
        <p style="color:#6b7280;">Tu pago fue rechazado. Verifica los datos e intenta de nuevo.</p>

    @elseif($status === 'VOIDED')
        <div class="icon-err"><i class="fa fa-ban"></i></div>
        <h2 class="err-color">Pago anulado</h2>
        <p style="color:#6b7280;">La transacción fue cancelada.</p>

    @elseif($status === 'PENDING')
        <div class="icon-pend"><i class="fa fa-clock"></i></div>
        <h2 class="pend-color">Pago en proceso</h2>
        <p style="color:#6b7280;">Tu pago está siendo procesado. Recibirás confirmación pronto.</p>

    @else
        <div class="icon-pend"><i class="fa fa-question-circle"></i></div>
        <h2 style="color:#374151;">Estado desconocido</h2>
        <p style="color:#6b7280;">No pudimos verificar el estado de tu pago en este momento.</p>
    @endif

    @if($transaccion)
    <div style="background:#f9fafb;border-radius:14px;padding:16px 20px;margin-top:20px;text-align:left;">
        <div class="detail-row"><span style="color:#6b7280;">Referencia</span><strong>{{ $transaccion['reference'] ?? '—' }}</strong></div>
        <div class="detail-row"><span style="color:#6b7280;">ID transacción</span><strong>{{ $transaccion['id'] ?? $transaccionId }}</strong></div>
        @if(isset($transaccion['amount_in_cents']))
        <div class="detail-row"><span style="color:#6b7280;">Valor</span>
            <strong>${{ number_format($transaccion['amount_in_cents'], 0, ',', '.') }}</strong>
        </div>
        @endif
        @if(isset($transaccion['payment_method_type']))
        <div class="detail-row"><span style="color:#6b7280;">Método</span><strong>{{ $transaccion['payment_method_type'] }}</strong></div>
        @endif
    </div>
    @endif

    @if($factura)
    <div style="margin-top:14px;background:#d1fae5;border-radius:12px;padding:12px 16px;font-size:.88rem;color:#065f46;">
        <i class="fa fa-file-invoice"></i>
        Factura <strong>{{ $empresa->prefijo_factura }}{{ $factura->numero_factura }}</strong>
        — Estado: <strong>{{ $factura->estado }}</strong>
    </div>
    @endif

    <a href="{{ route('pago-publico.index') }}" class="btn-back">
        <i class="fa fa-arrow-left"></i> Volver al inicio
    </a>
</div>
</body>
</html>
