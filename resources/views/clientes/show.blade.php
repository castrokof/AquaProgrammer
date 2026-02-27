@extends("theme.$theme.layout")

@section('titulo')
'Cliente: {{ $cliente->suscriptor }}'
@endsection

@section("styles")
<style>
/* ── LAYOUT ── */
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h4 { color: white; font-weight: 700; font-size: 1.2rem; margin: 0; }
.modern-card .card-body { padding: 28px; }
/* ── DATOS CLIENTE ── */
.dato-row { display: flex; align-items: center; margin-bottom: 14px; }
.dato-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #718096; min-width: 140px; }
.dato-valor { font-size: 0.95rem; color: #2d3748; font-weight: 500; }
.dato-valor.nuip { font-family: monospace; font-size: 1.1rem; color: #2e50e4; font-weight: 700; }
/* ── FOTOS ── */
.foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
.foto-card { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; background: #f8f9fa; }
.foto-card img { width: 100%; height: 160px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease; }
.foto-card img:hover { transform: scale(1.05); }
.foto-card .foto-info { padding: 8px 10px; font-size: 0.72rem; color: #718096; display: flex; justify-content: space-between; align-items: center; }
.foto-card .foto-tipo { font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.badge-tipo-doc { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2px 8px; border-radius: 8px; }
.badge-tipo-rostro { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2px 8px; border-radius: 8px; }
.badge-tipo-medidor { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 2px 8px; border-radius: 8px; }
/* ── HISTORIAL ── */
#tblOrdenes { font-size: 0.82rem; }
#tblOrdenes thead th { background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 12px 10px; border: none; text-align: center; }
#tblOrdenes tbody td { padding: 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; }
#tblOrdenes tbody tr:hover { background: #f8f9ff; }
/* ── BADGE ESTADO ORDEN ── */
.badge-estado-1 { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
.badge-estado-2 { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #333; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
.badge-estado-3,.badge-estado-4 { background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
/* ── MODAL FOTO GRANDE ── */
#modalFotoGrande .modal-content { background: #000; border-radius: 16px; border: none; }
#modalFotoGrande .modal-body { padding: 10px; text-align: center; }
#modalFotoGrande img { max-width: 100%; max-height: 80vh; border-radius: 8px; }
/* ── MODAL AGREGAR FOTO ── */
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 20px 28px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; }
.modal-modern .modal-body { padding: 30px; background: #fafbfc; }
.modal-modern .form-control { border-radius: 10px; border: 2px solid #e2e8f0; padding: 11px 14px; transition: all 0.3s; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.btn-guardar { border-radius: 12px; padding: 11px 30px; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:12px;border:none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">

        {{-- ══════════════════════════════ --}}
        {{-- COLUMNA IZQUIERDA: PERFIL      --}}
        {{-- ══════════════════════════════ --}}
        <div class="col-md-4">

            {{-- DATOS DEL CLIENTE --}}
            <div class="modern-card">
                <div class="card-header">
                    <h4><i class="fa fa-id-card"></i> Perfil del Cliente</h4>
                    <a href="{{ route('clientes.index') }}" class="btn btn-light btn-sm" style="border-radius:10px;">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div class="dato-row">
                        <span class="dato-label">Suscriptor</span>
                        <span class="dato-valor">{{ $cliente->suscriptor }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Tipo Doc.</span>
                        <span class="dato-valor">{{ $cliente->tipo_documento ?? '—' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">NUIP / Doc.</span>
                        <span class="dato-valor nuip">{{ $cliente->nuip ?? 'No registrado' }}</span>
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
                        <span class="dato-valor">{{ $cliente->direccion ?? '—' }}</span>
                    </div>
                    <div class="dato-row">
                        <span class="dato-label">Última actualiz.</span>
                        <span class="dato-valor" style="font-size:0.82rem;color:#718096;">
                            {{ $cliente->updated_at ? $cliente->updated_at->format('d/m/Y H:i') : '—' }}
                        </span>
                    </div>

                    <hr>

                    {{-- EDITAR PERFIL --}}
                    <button class="btn btn-warning btn-block" data-toggle="modal" data-target="#modalEditarCliente"
                            style="border-radius:12px;font-weight:700;">
                        <i class="fa fa-edit"></i> Editar Datos
                    </button>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════ --}}
        {{-- COLUMNA DERECHA: FOTOS + HISTORIAL --}}
        {{-- ══════════════════════════════════ --}}
        <div class="col-md-8">

            {{-- FOTOS DE REFERENCIA --}}
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
                        <div class="foto-grid">
                            @foreach($cliente->fotos as $foto)
                            <div class="foto-card">
                                <img src="{{ asset($foto->ruta_foto) }}"
                                     alt="Foto {{ $foto->tipo }}"
                                     onclick="verFotoGrande('{{ asset($foto->ruta_foto) }}')">
                                <div class="foto-info">
                                    <span class="foto-tipo">
                                        @if($foto->tipo === 'documento')
                                            <span class="badge-tipo-doc">Documento</span>
                                        @elseif($foto->tipo === 'rostro')
                                            <span class="badge-tipo-rostro">Rostro</span>
                                        @else
                                            <span class="badge-tipo-medidor">Medidor</span>
                                        @endif
                                    </span>
                                    <form action="{{ route('clientes.foto.eliminar', [$cliente->id, $foto->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar esta foto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Eliminar foto"
                                                style="border-radius:6px;padding:2px 7px;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @if($foto->created_at)
                                <div style="padding:0 10px 8px;font-size:0.68rem;color:#a0aec0;">
                                    {{ $foto->created_at->format('d/m/Y H:i') }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center;padding:30px;color:#a0aec0;">
                            <i class="fa fa-camera" style="font-size:2.5rem;margin-bottom:12px;display:block;"></i>
                            Sin fotos de verificación.<br>
                            <small>Puedes agregar fotos del documento, rostro o medidor del cliente.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- HISTORIAL DE LECTURAS --}}
            <div class="modern-card">
                <div class="card-header">
                    <h4><i class="fa fa-history"></i> Historial de Lecturas</h4>
                    <span style="color:rgba(255,255,255,0.7);font-size:0.85rem;">Últimas 24 órdenes</span>
                </div>
                <div class="card-body" style="padding:0;overflow-x:auto;">
                    @if($ordenes->count() > 0)
                    <table id="tblOrdenes" class="table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Ref. Medidor</th>
                                <th>L. Anterior</th>
                                <th>L. Actual</th>
                                <th>Consumo</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $orden)
                            <tr>
                                <td><strong>{{ $orden->Periodo }}</strong></td>
                                <td style="font-family:monospace;font-size:0.8rem;">{{ $orden->Ref_Medidor ?? '—' }}</td>
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
                                    @if($est === 1)
                                        <span class="badge-estado-1">Normal</span>
                                    @elseif($est === 2)
                                        <span class="badge-estado-2">Alerta</span>
                                    @elseif($est >= 3)
                                        <span class="badge-estado-3">Crítica</span>
                                    @else
                                        <span style="color:#a0aec0;">Sin leer</span>
                                    @endif
                                </td>
                                <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:0.78rem;">
                                    {{ $orden->Direccion ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <div style="text-align:center;padding:30px;color:#a0aec0;">
                            <i class="fa fa-inbox" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                            Sin órdenes de lectura asociadas a este suscriptor.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════ --}}
{{-- MODAL VER FOTO GRANDE                  --}}
{{-- ══════════════════════════════════════ --}}
<div class="modal fade" id="modalFotoGrande" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background:#000;border-radius:16px;border:none;">
            <div class="modal-header" style="border:none;padding:10px 14px;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:white;opacity:0.8;font-size:2rem;">&times;</button>
            </div>
            <div class="modal-body" style="padding:10px;text-align:center;">
                <img id="imgGrande" src="" alt="Foto" style="max-width:100%;max-height:80vh;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════ --}}
{{-- MODAL AGREGAR FOTO                     --}}
{{-- ══════════════════════════════════════ --}}
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
                        <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;">Tipo de Foto</label>
                        <select name="tipo_foto" class="form-control">
                            <option value="documento">Documento / Cédula</option>
                            <option value="rostro">Foto del Cliente (rostro)</option>
                            <option value="medidor">Medidor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;">Seleccionar Imagen *</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                        <small style="color:#718096;">Formatos: JPG, PNG, GIF. Máx. 8 MB.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid #e2e8f0;padding:16px 28px;">
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

{{-- ══════════════════════════════════════ --}}
{{-- MODAL EDITAR DATOS                     --}}
{{-- ══════════════════════════════════════ --}}
<div class="modal fade modal-modern" id="modalEditarCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Editar Datos del Cliente</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="suscriptor" value="{{ $cliente->suscriptor }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Tipo Doc.</label>
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
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">NUIP / Número de Documento</label>
                                <input type="text" name="nuip" class="form-control"
                                       value="{{ $cliente->nuip }}" placeholder="Número">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Nombre(s)</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="{{ $cliente->nombre }}" placeholder="Nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Apellido(s)</label>
                                <input type="text" name="apellido" class="form-control"
                                       value="{{ $cliente->apellido }}" placeholder="Apellido">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Teléfono</label>
                                <input type="text" name="telefono" class="form-control"
                                       value="{{ $cliente->telefono }}" placeholder="Teléfono">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label style="font-weight:600;color:#4a5568;font-size:0.82rem;text-transform:uppercase;">Dirección</label>
                                <input type="text" name="direccion" class="form-control"
                                       value="{{ $cliente->direccion }}" placeholder="Dirección">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:2px solid #e2e8f0;padding:16px 28px;">
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
