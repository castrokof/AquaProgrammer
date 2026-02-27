@extends("theme.$theme.layout")

@section('titulo')
'Cliente: {{ $cliente->suscriptor }}'
@endsection

@section("styles")
<style>
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h4 { color: white; font-weight: 700; font-size: 1.2rem; margin: 0; }
.modern-card .card-body { padding: 26px; }
/* Datos */
.dato-row { display: flex; align-items: flex-start; margin-bottom: 12px; }
.dato-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #718096; min-width: 130px; padding-top: 2px; }
.dato-valor { font-size: 0.92rem; color: #2d3748; font-weight: 500; }
.dato-valor.nuip { font-family: monospace; font-size: 1.05rem; color: #2e50e4; font-weight: 700; }
.dato-valor.serie { font-family: monospace; font-size: 0.95rem; color: #11998e; font-weight: 700; background: #f0fff4; padding: 2px 10px; border-radius: 8px; }
/* Fotos */
.foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; }
.foto-card { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; background: #f8f9fa; }
.foto-card img { width: 100%; height: 165px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease; }
.foto-card img:hover { transform: scale(1.05); }
.foto-card .foto-info { padding: 7px 10px; font-size: 0.7rem; color: #718096; display: flex; justify-content: space-between; align-items: center; }
.badge-tipo-doc     { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2px 8px; border-radius: 8px; font-size: 0.68rem; font-weight: 700; }
.badge-tipo-medidor { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 2px 8px; border-radius: 8px; font-size: 0.68rem; font-weight: 700; }
.badge-tipo-predio  { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2px 8px; border-radius: 8px; font-size: 0.68rem; font-weight: 700; }
/* Historial órdenes */
#tblOrdenes { font-size: 0.82rem; }
#tblOrdenes thead th { background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%); color: white; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; padding: 11px 9px; border: none; text-align: center; }
#tblOrdenes tbody td { padding: 9px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; }
#tblOrdenes tbody tr:hover { background: #f8f9ff; }
/* Historial series */
#tblSeries { font-size: 0.82rem; }
#tblSeries thead th { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; padding: 11px 9px; border: none; text-align: center; }
#tblSeries tbody td { padding: 9px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; font-family: monospace; font-size: 0.85rem; }
/* Badges estado */
.badge-estado-1 { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; }
.badge-estado-2 { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333; padding: 3px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; }
.badge-estado-3,.badge-estado-4 { background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; }
/* Modal foto grande */
#modalFotoGrande .modal-content { background: #000; border-radius: 16px; border: none; }
/* Modal form */
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 20px 28px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; }
.modal-modern .modal-body { padding: 28px; background: #fafbfc; }
.modal-modern .form-control { border-radius: 10px; border: 2px solid #e2e8f0; padding: 11px 14px; transition: all 0.3s; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.btn-guardar { border-radius: 12px; padding: 11px 28px; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; margin: 16px 0 8px; padding-bottom: 5px; border-bottom: 2px solid #e2e8f0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:12px;border:none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">

        {{-- ══════════════════════════ COLUMNA IZQUIERDA ══════════════════════════ --}}
        <div class="col-md-4">

            <div class="modern-card">
                <div class="card-header">
                    <h4><i class="fa fa-id-card"></i> Perfil del Cliente</h4>
                    <a href="{{ route('clientes.index') }}" class="btn btn-light btn-sm" style="border-radius:10px;">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <div class="card-body">

                    <p class="section-title"><i class="fa fa-user"></i> Identificación</p>
                    <div class="dato-row">
                        <span class="dato-label">Suscriptor</span>
                        <span class="dato-valor"><strong>{{ $cliente->suscriptor }}</strong></span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Tipo Doc.</span>
                        <span class="dato-valor">{{ $cliente->tipo_documento ?? '—' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">NUIP / Doc.</span>
                        <span class="dato-valor nuip">{{ $cliente->nuip ?: 'No registrado' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Nombre</span>
                        <span class="dato-valor">{{ trim($cliente->nombre . ' ' . $cliente->apellido) ?: '—' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Teléfono</span>
                        <span class="dato-valor">{{ $cliente->telefono ?? '—' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Dirección</span>
                        <span class="dato-valor" style="font-size:0.85rem;">{{ $cliente->direccion ?? '—' }}</span>
                    </div>

                    <p class="section-title" style="margin-top:18px;"><i class="fa fa-tachometer"></i> Medidor</p>
                    <div class="dato-row">
                        <span class="dato-label">Serie actual</span>
                        <span class="dato-valor serie">{{ $cliente->serie_medidor ?: 'No registrada' }}</span>
                    </div>

                    <p class="section-title" style="margin-top:18px;"><i class="fa fa-clock-o"></i> Control</p>
                    <div class="dato-row">
                        <span class="dato-label">Última act.</span>
                        <span class="dato-valor" style="font-size:0.8rem;color:#718096;">
                            {{ $cliente->updated_at ? $cliente->updated_at->format('d/m/Y H:i') : '—' }}
                        </span>
                    </div>

                    <hr>
                    <button class="btn btn-warning btn-block" data-toggle="modal" data-target="#modalEditarCliente"
                            style="border-radius:12px;font-weight:700;">
                        <i class="fa fa-edit"></i> Editar Datos
                    </button>
                </div>
            </div>

            {{-- HISTORIAL DE SERIES --}}
            <div class="modern-card">
                <div class="card-header" style="background:linear-gradient(135deg,#11998e,#38ef7d);">
                    <h4><i class="fa fa-exchange"></i> Historial de Series</h4>
                    <span style="color:rgba(255,255,255,0.8);font-size:0.8rem;">Trazabilidad por período</span>
                </div>
                <div class="card-body" style="padding:0;overflow-x:auto;">
                    @if($cliente->series->count() > 0)
                    <table id="tblSeries" class="table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Serie</th>
                                <th>Fecha Reg.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->series as $serie)
                            <tr>
                                <td>
                                    @php
                                        $p = $serie->periodo;
                                        $año = substr($p, 0, 4);
                                        $mes = substr($p, 4, 2);
                                    @endphp
                                    <strong>{{ $mes }}/{{ $año }}</strong>
                                </td>
                                <td style="color:#11998e;">{{ $serie->serie }}</td>
                                <td style="font-size:0.75rem;color:#718096;font-family:sans-serif;">
                                    {{ $serie->fecha_registro ? $serie->fecha_registro->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <div style="text-align:center;padding:24px;color:#a0aec0;">
                            <i class="fa fa-exchange" style="font-size:1.8rem;margin-bottom:8px;display:block;"></i>
                            Sin historial de series aún.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ══════════════════════════ COLUMNA DERECHA ══════════════════════════ --}}
        <div class="col-md-8">

            {{-- FOTOS DE VERIFICACIÓN --}}
            <div class="modern-card">
                <div class="card-header">
                    <h4><i class="fa fa-camera"></i> Fotos de Verificación</h4>
                    <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#modalAgregarFoto"
                            style="border-radius:10px;font-weight:700;">
                        <i class="fa fa-plus"></i> Agregar Foto
                    </button>
                </div>
                <div class="card-body">
                    @if($cliente->fotos->count() > 0)
                        {{-- Mostrar primero medidor y predio en columnas destacadas --}}
                        @php
                            $fotoMedidor = $cliente->fotos->where('tipo', 'medidor')->first();
                            $fotoPredio  = $cliente->fotos->where('tipo', 'predio')->first();
                            $otrasFoots  = $cliente->fotos->whereNotIn('tipo', ['medidor','predio']);
                        @endphp

                        @if($fotoMedidor || $fotoPredio)
                        <div class="row" style="margin-bottom:16px;">
                            <div class="col-md-6">
                                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#11998e;margin-bottom:6px;">
                                    <i class="fa fa-tachometer"></i> Foto del Medidor
                                </div>
                                @if($fotoMedidor)
                                <div class="foto-card">
                                    <img src="{{ asset($fotoMedidor->ruta_foto) }}" alt="Medidor"
                                         onclick="verFotoGrande('{{ asset($fotoMedidor->ruta_foto) }}')">
                                    <div class="foto-info">
                                        <span class="badge-tipo-medidor">Medidor</span>
                                        <form action="{{ route('clientes.foto.eliminar', [$cliente->id, $fotoMedidor->id]) }}"
                                              method="POST" onsubmit="return confirm('¿Eliminar foto?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                    style="border-radius:6px;padding:2px 7px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div style="padding:0 10px 6px;font-size:0.66rem;color:#a0aec0;">
                                        {{ $fotoMedidor->created_at ? $fotoMedidor->created_at->format('d/m/Y H:i') : '' }}
                                    </div>
                                </div>
                                @else
                                <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:30px;text-align:center;color:#a0aec0;">
                                    <i class="fa fa-camera" style="font-size:1.5rem;"></i><br>
                                    <small>Sin foto de medidor</small>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#4facfe;margin-bottom:6px;">
                                    <i class="fa fa-home"></i> Foto del Predio / Fachada
                                </div>
                                @if($fotoPredio)
                                <div class="foto-card">
                                    <img src="{{ asset($fotoPredio->ruta_foto) }}" alt="Predio"
                                         onclick="verFotoGrande('{{ asset($fotoPredio->ruta_foto) }}')">
                                    <div class="foto-info">
                                        <span class="badge-tipo-predio">Predio</span>
                                        <form action="{{ route('clientes.foto.eliminar', [$cliente->id, $fotoPredio->id]) }}"
                                              method="POST" onsubmit="return confirm('¿Eliminar foto?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                    style="border-radius:6px;padding:2px 7px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div style="padding:0 10px 6px;font-size:0.66rem;color:#a0aec0;">
                                        {{ $fotoPredio->created_at ? $fotoPredio->created_at->format('d/m/Y H:i') : '' }}
                                    </div>
                                </div>
                                @else
                                <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:30px;text-align:center;color:#a0aec0;">
                                    <i class="fa fa-camera" style="font-size:1.5rem;"></i><br>
                                    <small>Sin foto de predio</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Otras fotos (documentos, etc.) --}}
                        @if($otrasFoots->count() > 0)
                        <div style="margin-top:10px;">
                            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#718096;margin-bottom:8px;">
                                Otras fotos
                            </div>
                            <div class="foto-grid">
                                @foreach($otrasFoots as $foto)
                                <div class="foto-card">
                                    <img src="{{ asset($foto->ruta_foto) }}" alt="{{ $foto->tipo }}"
                                         onclick="verFotoGrande('{{ asset($foto->ruta_foto) }}')">
                                    <div class="foto-info">
                                        <span class="badge-tipo-doc">{{ ucfirst($foto->tipo) }}</span>
                                        <form action="{{ route('clientes.foto.eliminar', [$cliente->id, $foto->id]) }}"
                                              method="POST" onsubmit="return confirm('¿Eliminar foto?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs"
                                                    style="border-radius:6px;padding:2px 7px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div style="padding:0 10px 6px;font-size:0.66rem;color:#a0aec0;">
                                        {{ $foto->created_at ? $foto->created_at->format('d/m/Y H:i') : '' }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    @else
                        <div style="text-align:center;padding:30px;color:#a0aec0;">
                            <i class="fa fa-camera" style="font-size:2.5rem;margin-bottom:12px;display:block;"></i>
                            Sin fotos de verificación.<br>
                            <small>Agrega foto del medidor y del predio para referencia visual.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- HISTORIAL DE LECTURAS --}}
            <div class="modern-card">
                <div class="card-header">
                    <h4><i class="fa fa-history"></i> Historial de Lecturas</h4>
                    <span style="color:rgba(255,255,255,0.7);font-size:0.82rem;">Últimas 24 órdenes</span>
                </div>
                <div class="card-body" style="padding:0;overflow-x:auto;">
                    @if($ordenes->count() > 0)
                    <table id="tblOrdenes" class="table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Serie / Ref. Medidor</th>
                                <th>L. Anterior</th>
                                <th>L. Actual</th>
                                <th>Consumo</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $orden)
                            <tr>
                                <td><strong>{{ $orden->Periodo }}</strong></td>
                                <td style="font-family:monospace;font-size:0.78rem;color:#11998e;">
                                    {{ $orden->Ref_Medidor ?? '—' }}
                                    @if($orden->new_medidor)
                                        <br><span style="color:#f5576c;font-size:0.68rem;" title="Medidor nuevo registrado">
                                            <i class="fa fa-exchange"></i> Nuevo: {{ $orden->new_medidor }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ number_format($orden->LA ?? 0) }}</td>
                                <td>
                                    @if($orden->Lect_Actual)
                                        <strong>{{ number_format($orden->Lect_Actual) }}</strong>
                                    @else
                                        <span style="color:#a0aec0;">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $orden->Cons_Act !== null ? number_format($orden->Cons_Act) : '—' }}</td>
                                <td>{{ number_format($orden->Promedio ?? 0) }}</td>
                                <td>
                                    @php $est = intval($orden->Estado ?? 0); @endphp
                                    @if($est === 1) <span class="badge-estado-1">Normal</span>
                                    @elseif($est === 2) <span class="badge-estado-2">Alerta</span>
                                    @elseif($est >= 3) <span class="badge-estado-3">Crítica</span>
                                    @else <span style="color:#a0aec0;">Sin leer</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <div style="text-align:center;padding:28px;color:#a0aec0;">
                            <i class="fa fa-inbox" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                            Sin órdenes de lectura asociadas.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL VER FOTO GRANDE --}}
<div class="modal fade" id="modalFotoGrande" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#000;border-radius:16px;border:none;">
            <div class="modal-header" style="border:none;padding:8px 14px;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:white;opacity:0.8;font-size:2rem;">&times;</button>
            </div>
            <div class="modal-body" style="padding:10px;text-align:center;">
                <img id="imgGrande" src="" alt="" style="max-width:100%;max-height:80vh;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

{{-- MODAL AGREGAR FOTO --}}
<div class="modal fade modal-modern" id="modalAgregarFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-camera"></i> Agregar Foto de Verificación</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('clientes.foto.agregar', $cliente->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Tipo de Foto</label>
                        <select name="tipo_foto" class="form-control">
                            <option value="medidor">Foto del Medidor</option>
                            <option value="predio">Foto del Predio / Fachada</option>
                            <option value="documento">Documento / Cédula</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Seleccionar Imagen *</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                        <small style="color:#718096;">Formatos: JPG, PNG. Máx. 8 MB.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid #e2e8f0;padding:14px 26px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            style="border-radius:10px;">Cancelar</button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa fa-upload"></i> Subir Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR DATOS --}}
<div class="modal fade modal-modern" id="modalEditarCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Editar Datos del Cliente</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="suscriptor" value="{{ $cliente->suscriptor }}">
                <div class="modal-body">

                    <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#a0aec0;border-bottom:2px solid #e2e8f0;padding-bottom:5px;margin-bottom:14px;">
                        Identificación
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Tipo Doc.</label>
                                <select name="tipo_documento" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    @foreach(['CC','TI','CE','PA','NIT'] as $td)
                                        <option value="{{ $td }}" {{ $cliente->tipo_documento === $td ? 'selected' : '' }}>{{ $td }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">NUIP / Número</label>
                                <input type="text" name="nuip" class="form-control" value="{{ $cliente->nuip }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Nombre(s)</label>
                                <input type="text" name="nombre" class="form-control" value="{{ $cliente->nombre }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Apellido(s)</label>
                                <input type="text" name="apellido" class="form-control" value="{{ $cliente->apellido }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ $cliente->telefono }}">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{ $cliente->direccion }}">
                            </div>
                        </div>
                    </div>

                    <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#a0aec0;border-bottom:2px solid #e2e8f0;padding-bottom:5px;margin: 16px 0 14px;">
                        Medidor
                    </p>
                    <div class="form-group">
                        <label style="font-weight:600;color:#4a5568;font-size:0.8rem;text-transform:uppercase;">Serie del Medidor</label>
                        <input type="text" name="serie_medidor" class="form-control"
                               value="{{ $cliente->serie_medidor }}"
                               placeholder="Número de serie">
                        <small style="color:#718096;">Al actualizar la serie se registrará en el historial del período actual.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid #e2e8f0;padding:14px 26px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            style="border-radius:10px;">Cancelar</button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="fa fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function verFotoGrande(url) {
    document.getElementById('imgGrande').src = url;
    $('#modalFotoGrande').modal('show');
}
</script>
@endsection
