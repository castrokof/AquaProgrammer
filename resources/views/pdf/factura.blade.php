<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        
        /* Header */
        .header { 
            background: linear-gradient(135deg, #2e50e4 0%, #2b0c49 100%); 
            color: white; 
            padding: 20px; 
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .header .empresa { font-size: 16px; font-weight: bold; }
        .header .subempresa { font-size: 10px; opacity: 0.9; margin-top: 4px; }
        .header .num-factura { text-align: right; }
        .header .num-factura .num { font-size: 22px; font-weight: bold; }
        .header .num-factura .lbl { font-size: 9px; opacity: 0.8; text-transform: uppercase; }
        .header .fechas { font-size: 10px; opacity: 0.9; margin-top: 8px; }
        
        /* Body */
        .body { background: white; padding: 20px; }
        
        /* Sections */
        .section { margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; }
        .section:last-child { border-bottom: none; }
        .section-title { 
            font-size: 10px; 
            font-weight: bold; 
            color: #4a5568; 
            text-transform: uppercase; 
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        
        /* Info grid */
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-cell { 
            display: table-cell; 
            width: 33.33%; 
            padding: 4px 0; 
            font-size: 10px; 
        }
        .info-label { color: #a0aec0; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        .info-value { font-weight: 600; color: #2d3748; margin-top: 2px; }
        
        /* Table */
        .tabla { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla th { 
            background: #f7fafc; 
            padding: 8px; 
            font-weight: bold; 
            color: #4a5568; 
            font-size: 9px; 
            text-transform: uppercase; 
            text-align: right;
            border: 1px solid #e2e8f0;
        }
        .tabla th:first-child { text-align: left; }
        .tabla td { 
            padding: 8px; 
            border: 1px solid #e2e8f0; 
            text-align: right; 
            font-size: 10px;
        }
        .tabla td:first-child { text-align: left; }
        .tabla tfoot td { 
            font-weight: bold; 
            background: #f7fafc;
        }
        
        /* Total */
        .total-box { 
            background: linear-gradient(135deg, #2e50e4, #2b0c49); 
            color: white; 
            padding: 15px 20px; 
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        .total-box .lbl { font-size: 11px; font-weight: 600; }
        .total-box .val { font-size: 18px; font-weight: bold; }
        
        /* Badge */
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 12px; 
            font-size: 9px; 
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-PENDIENTE { background: #fef3c7; color: #92400e; }
        .badge-PAGADA { background: #c6f6d5; color: #22543d; }
        .badge-VENCIDA { background: #fed7d7; color: #742a2a; }
        .badge-ANULADA { background: #e2e8f0; color: #718096; }
        
        /* Footer info */
        .footer-info { 
            margin-top: 20px; 
            padding-top: 15px; 
            border-top: 2px solid #e2e8f0;
            font-size: 9px;
            color: #718096;
        }
        .footer-info p { margin-bottom: 4px; }
        
        /* QR placeholder */
        .qr-section { text-align: center; margin-top: 15px; }
        .qr-placeholder { 
            width: 80px; 
            height: 80px; 
            border: 2px solid #e2e8f0; 
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="empresa">EMPRESA DE AGUA Y ALCANTARILLADO</div>
            <div class="subempresa">Servicio Público Domiciliario</div>
            <div class="fechas">
                {{ $factura->mes_cuenta }}<br>
                Del {{ \Carbon\Carbon::parse($factura->fecha_del)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($factura->fecha_hasta)->format('d/m/Y') }}
            </div>
        </div>
        <div class="num-factura">
            <div class="lbl">Factura N°</div>
            <div class="num">{{ $factura->numero_factura }}</div>
            <div class="fechas">
                Expide: {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }}<br>
                Vence: {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}<br>
                Corte: {{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}
            </div>
            <span class="badge badge-{{ $factura->estado }}" style="margin-top: 8px;">{{ $factura->estado }}</span>
        </div>
    </div>
    
    <div class="body">
        {{-- Datos del Suscriptor --}}
        <div class="section">
            <div class="section-title">Datos del Suscriptor</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Suscriptor</div>
                        <div class="info-value">{{ $factura->suscriptor }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Nombre</div>
                        <div class="info-value">{{ trim($factura->cliente->nombre . ' ' . $factura->cliente->apellido) }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Dirección</div>
                        <div class="info-value">{{ $factura->cliente->direccion ?? '—' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Estrato</div>
                        <div class="info-value">E{{ $factura->estrato_snapshot ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Tipo de Uso</div>
                        <div class="info-value">{{ $factura->clase_uso ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Servicios</div>
                        <div class="info-value">{{ $factura->servicios_snapshot }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Serie Medidor</div>
                        <div class="info-value">{{ $factura->serie_medidor ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Sector</div>
                        <div class="info-value">{{ $factura->sector ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Consumo</div>
                        <div class="info-value">{{ $factura->consumo_m3 }} m³</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Lectura del Período --}}
        <div class="section">
            <div class="section-title">Lectura del Período</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <div class="info-label">Lectura Anterior</div>
                        <div class="info-value">{{ $factura->lectura_anterior ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Lectura Actual</div>
                        <div class="info-value">{{ $factura->lectura_actual ?? '—' }}</div>
                    </div>
                    <div class="info-cell">
                        <div class="info-label">Consumo</div>
                        <div class="info-value" style="color: #2e50e4; font-size: 14px;">{{ $factura->consumo_m3 }} m³</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Acueducto --}}
        @if(in_array($factura->servicios_snapshot, ['AG', 'AG-AL']))
        <div class="section">
            <div class="section-title">Acueducto</div>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>m³</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cargo Fijo</td>
                        <td>—</td>
                        <td>$ {{ number_format($factura->cargo_fijo_acueducto, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Consumo Básico</td>
                        <td>{{ $factura->consumo_basico_acueducto_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_basico_acueducto_valor, 0, ',', '.') }}</td>
                    </tr>
                    @if($factura->consumo_complementario_acueducto_m3 > 0)
                    <tr>
                        <td>Consumo Complementario</td>
                        <td>{{ $factura->consumo_complementario_acueducto_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_complementario_acueducto_valor, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($factura->consumo_suntuario_acueducto_m3 > 0)
                    <tr>
                        <td>Consumo Suntuario</td>
                        <td>{{ $factura->consumo_suntuario_acueducto_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_suntuario_acueducto_valor, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($factura->subsidio_emergencia != 0)
                    <tr>
                        <td>Subsidio de Emergencia</td>
                        <td>—</td>
                        <td>- $ {{ number_format($factura->subsidio_emergencia, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($factura->otros_cobros_acueducto > 0)
                    <tr>
                        <td>Otros Cobros</td>
                        <td>—</td>
                        <td>$ {{ number_format($factura->cuota_otros_cobros_acueducto, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total Acueducto</td>
                        <td>$ {{ number_format($factura->subtotal_conexion_otros_acueducto, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
        
        {{-- Alcantarillado --}}
        @if(in_array($factura->servicios_snapshot, ['AL', 'AG-AL']))
        <div class="section">
            <div class="section-title">Alcantarillado</div>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>m³</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cargo Fijo</td>
                        <td>—</td>
                        <td>$ {{ number_format($factura->cargo_fijo_alcantarillado, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Consumo Básico</td>
                        <td>{{ $factura->consumo_basico_alcantarillado_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_basico_alcantarillado_valor, 0, ',', '.') }}</td>
                    </tr>
                    @if($factura->consumo_complementario_alcantarillado_m3 > 0)
                    <tr>
                        <td>Consumo Complementario</td>
                        <td>{{ $factura->consumo_complementario_alcantarillado_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_complementario_alcantarillado_valor, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($factura->consumo_suntuario_alcantarillado_m3 > 0)
                    <tr>
                        <td>Consumo Suntuario</td>
                        <td>{{ $factura->consumo_suntuario_alcantarillado_m3 }}</td>
                        <td>$ {{ number_format($factura->consumo_suntuario_alcantarillado_valor, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($factura->otros_cobros_alcantarillado > 0)
                    <tr>
                        <td>Otros Cobros</td>
                        <td>—</td>
                        <td>$ {{ number_format($factura->cuota_otros_cobros_alcantarillado, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total Alcantarillado</td>
                        <td>$ {{ number_format($factura->subtotal_conexion_otros_alcantarillado, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
        
        {{-- Saldo Anterior --}}
        @if($factura->saldo_anterior > 0)
        <div class="section" style="background: #fff5f5; padding: 10px; border-radius: 5px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #e53e3e;">Saldo Anterior en Mora</strong>
                    <div style="font-size: 9px; color: #718096;">Corresponde a facturas pendientes de pago</div>
                </div>
                <div style="font-size: 16px; font-weight: bold; color: #e53e3e;">
                    $ {{ number_format($factura->saldo_anterior, 0, ',', '.') }}
                </div>
            </div>
        </div>
        @endif
        
        {{-- Total a Pagar --}}
        <div class="total-box">
            <div class="lbl">TOTAL A PAGAR</div>
            <div class="val">$ {{ number_format($factura->total, 0, ',', '.') }}</div>
        </div>
        
        {{-- Footer Info --}}
        <div class="footer-info">
            <p><strong>Fecha de expedición:</strong> {{ \Carbon\Carbon::parse($factura->fecha_expedicion)->format('d/m/Y') }}</p>
            <p><strong>Fecha límite de pago:</strong> {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}</p>
            <p><strong>Fecha de corte:</strong> {{ \Carbon\Carbon::parse($factura->fecha_corte)->format('d/m/Y') }}</p>
            @if($factura->observaciones)
            <p><strong>Observaciones:</strong> {{ $factura->observaciones }}</p>
            @endif
            <p style="margin-top: 10px; font-style: italic;">Esta es una representación impresa de la factura electrónica.</p>
        </div>
        
        {{-- QR Section --}}
        <div class="qr-section">
            <div class="qr-placeholder">Código QR</div>
            <div style="font-size: 8px; color: #a0aec0; margin-top: 5px;">{{ $factura->numero_factura }}</div>
        </div>
    </div>
</body>
</html>
