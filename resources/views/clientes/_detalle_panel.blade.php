{{--
    Panel lateral de detalle rápido de cliente.
    Se carga vía AJAX y se inyecta en el drawer #panelClienteBody.
--}}
@php
    $est  = $cliente->estado ?? 'ACTIVO';
    $srv  = $cliente->servicios ?? '—';
    $ini  = strtoupper(substr($cliente->nombre ?? 'C', 0, 1) . substr($cliente->apellido ?? '', 0, 1));
    $fotoMedidor = $cliente->fotos->firstWhere('tipo_foto', 'medidor');
    $fotoPredio  = $cliente->fotos->firstWhere('tipo_foto', 'predio');
@endphp

<style>
.dp-section { padding: 14px 20px; border-bottom: 1px solid #f0f0f0; }
.dp-section:last-child { border-bottom: none; }
.dp-section-title {
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: #a0aec0;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px dashed #e2e8f0;
}
.dp-row { display: flex; margin-bottom: 7px; align-items: flex-start; }
.dp-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; color: #718096; min-width: 110px; padding-top: 2px; }
.dp-val { font-size: .85rem; font-weight: 500; color: #2d3748; }
.dp-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 800;
}
.dp-badge-activo     { background:#c6f6d5; color:#22543d; }
.dp-badge-suspendido { background:#fef3c7; color:#92400e; }
.dp-badge-cortado    { background:#fed7d7; color:#742a2a; }
.dp-badge-otro       { background:#e2e8f0; color:#718096; }

.dp-avatar {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg,#2e50e4,#2b0c49);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; font-weight: 900;
    flex-shrink: 0;
}
.dp-foto-thumb {
    width: 72px; height: 72px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
}

/* Tabla últimas lecturas */
.dp-tbl { width: 100%; border-collapse: collapse; font-size: .78rem; }
.dp-tbl thead th {
    background: linear-gradient(135deg,#3d57ce,#776a84);
    color: white;
    padding: 7px 8px;
    font-size: .67rem;
    text-transform: uppercase;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
}
.dp-tbl tbody td { padding: 6px 8px; border-bottom: 1px solid #f0f0f0; text-align: center; }
.dp-tbl tbody tr:last-child td { border: none; }
</style>

{{-- Hero: avatar + nombre + estado --}}
<div style="background:linear-gradient(135deg,#2e50e4 0%,#2b0c49 100%);padding:20px;display:flex;align-items:center;gap:14px;">
    <div class="dp-avatar">{{ $ini }}</div>
    <div style="flex:1;min-width:0;">
        <div style="color:white;font-weight:800;font-size:1rem;line-height:1.2;">
            {{ trim($cliente->nombre . ' ' . $cliente->apellido) ?: 'Sin nombre' }}
        </div>
        <div style="color:rgba(255,255,255,.75);font-size:.8rem;margin-top:2px;">
            Suscriptor: <strong style="color:white;">{{ $cliente->suscriptor }}</strong>
        </div>
        <div style="margin-top:6px;">
            @if($est === 'ACTIVO')
                <span class="dp-badge dp-badge-activo">ACTIVO</span>
            @elseif($est === 'SUSPENDIDO')
                <span class="dp-badge dp-badge-suspendido">SUSPENDIDO</span>
            @elseif($est === 'CORTADO')
                <span class="dp-badge dp-badge-cortado">CORTADO</span>
            @else
                <span class="dp-badge dp-badge-otro">{{ $est }}</span>
            @endif
        </div>
    </div>
</div>

{{-- Identificación --}}
<div class="dp-section">
    <div class="dp-section-title"><i class="fa fa-user"></i> Identificación</div>
    <div class="dp-row">
        <span class="dp-lbl">Tipo doc.</span>
        <span class="dp-val">{{ $cliente->tipo_documento ?? '—' }}</span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">NUIP / Doc.</span>
        <span class="dp-val" style="font-family:monospace;color:#2e50e4;font-weight:700;">{{ $cliente->nuip ?: 'No registrado' }}</span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Teléfono</span>
        <span class="dp-val">{{ $cliente->telefono ?? '—' }}</span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Dirección</span>
        <span class="dp-val" style="font-size:.82rem;">{{ $cliente->direccion ?? '—' }}</span>
    </div>
</div>

{{-- Facturación --}}
<div class="dp-section">
    <div class="dp-section-title"><i class="fa fa-file-invoice-dollar"></i> Facturación</div>
    <div class="dp-row">
        <span class="dp-lbl">Estrato</span>
        <span class="dp-val">
            @if($cliente->estrato)
                <span style="background:#e0f2fe;color:#0369a1;padding:1px 8px;border-radius:8px;font-weight:700;font-size:.82rem;">
                    E{{ $cliente->estrato->numero }} — {{ $cliente->estrato->nombre }}
                </span>
            @else
                <span style="color:#a0aec0;">No asignado</span>
            @endif
        </span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Servicios</span>
        <span class="dp-val">
            @if($srv === 'AG-AL')
                <span style="background:#c6f6d5;color:#22543d;padding:1px 8px;border-radius:8px;font-weight:700;font-size:.8rem;">Acueducto + Alcantarillado</span>
            @elseif($srv === 'AG')
                <span style="background:#bee3f8;color:#2c5282;padding:1px 8px;border-radius:8px;font-weight:700;font-size:.8rem;">Solo Acueducto</span>
            @elseif($srv === 'AL')
                <span style="background:#e9d8fd;color:#553c9a;padding:1px 8px;border-radius:8px;font-weight:700;font-size:.8rem;">Solo Alcantarillado</span>
            @else
                <span style="color:#a0aec0;">{{ $srv }}</span>
            @endif
        </span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Tipo de Uso</span>
        <span class="dp-val">{{ $cliente->tipo_uso ?? '—' }}</span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Sector</span>
        <span class="dp-val">{{ $cliente->sector ?? '—' }}</span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Ruta</span>
        <span class="dp-val">{{ $cliente->ruta ?? '—' }}</span>
    </div>
</div>

{{-- Medidor --}}
<div class="dp-section">
    <div class="dp-section-title"><i class="fa fa-tachometer-alt"></i> Medidor</div>
    <div class="dp-row">
        <span class="dp-lbl">Serie actual</span>
        <span class="dp-val" style="font-family:monospace;color:#11998e;font-weight:700;background:#f0fff4;padding:1px 8px;border-radius:8px;">
            {{ $cliente->serie_medidor ?: 'No registrada' }}
        </span>
    </div>
    <div class="dp-row">
        <span class="dp-lbl">Estado</span>
        <span class="dp-val">
            @if($cliente->tiene_medidor)
                <span style="color:#22543d;font-weight:700;"><i class="fa fa-check-circle"></i> Con medidor</span>
            @else
                <span style="color:#c05621;font-weight:700;"><i class="fa fa-times-circle"></i> Sin medidor</span>
            @endif
        </span>
    </div>

    {{-- Fotos rápidas --}}
    @if($fotoMedidor || $fotoPredio)
    <div style="display:flex;gap:10px;margin-top:10px;">
        @if($fotoMedidor)
        <div style="text-align:center;">
            <img src="{{ asset('storage/' . $fotoMedidor->ruta_foto) }}" class="dp-foto-thumb" title="Foto medidor">
            <div style="font-size:.62rem;color:#718096;margin-top:2px;">Medidor</div>
        </div>
        @endif
        @if($fotoPredio)
        <div style="text-align:center;">
            <img src="{{ asset('storage/' . $fotoPredio->ruta_foto) }}" class="dp-foto-thumb" title="Foto predio">
            <div style="font-size:.62rem;color:#718096;margin-top:2px;">Predio</div>
        </div>
        @endif
        @php $totalFotos = $cliente->fotos->count(); @endphp
        @if($totalFotos > 2)
        <div style="text-align:center;display:flex;align-items:center;justify-content:center;width:72px;height:72px;background:#f0f4ff;border-radius:10px;border:2px dashed #c3dafe;">
            <div style="font-size:.75rem;color:#2e50e4;font-weight:700;">+{{ $totalFotos - 2 }}<br>más</div>
        </div>
        @endif
    </div>
    @endif
</div>

{{-- Últimas lecturas --}}
@if($ordenes->count() > 0)
<div class="dp-section">
    <div class="dp-section-title"><i class="fa fa-list-alt"></i> Últimas Lecturas</div>
    <table class="dp-tbl">
        <thead>
            <tr>
                <th>Período</th>
                <th>L. Ant.</th>
                <th>L. Act.</th>
                <th>m³</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenes as $o)
            <tr>
                <td style="font-weight:700;color:#2e50e4;">{{ $o->Periodo }}</td>
                <td>{{ $o->LA ?? '—' }}</td>
                <td>{{ $o->LA2 ?? '—' }}</td>
                <td style="font-weight:700;">{{ $o->Cons_Act !== null ? $o->Cons_Act : '—' }}</td>
                <td>
                    @if($o->Estado == 1)
                        <span style="background:#e0f2fe;color:#0369a1;padding:1px 7px;border-radius:8px;font-size:.68rem;font-weight:700;">Pendiente</span>
                    @elseif($o->Estado == 2)
                        <span style="background:#c6f6d5;color:#22543d;padding:1px 7px;border-radius:8px;font-size:.68rem;font-weight:700;">Leído</span>
                    @else
                        <span style="background:#e2e8f0;color:#718096;padding:1px 7px;border-radius:8px;font-size:.68rem;">{{ $o->Estado }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
