@extends("theme.$theme.layout")

@section('titulo')
'Control de Clientes'
@endsection

@section("styles")
<style>
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.4rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
.filtros-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
.filtros-container .form-control { border-radius: 12px; border: 2px solid #e2e8f0; padding: 10px 14px; font-size: 0.9rem; transition: all 0.3s ease; }
.filtros-container .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.table-modern-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow-x: auto; }
#tblClientes { font-size: 0.85rem; border-radius: 12px; overflow: hidden; }
#tblClientes thead th { background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 10px; border: none; white-space: nowrap; text-align: center; }
#tblClientes tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 0.82rem; }
#tblClientes tbody tr { background: white; transition: all 0.2s ease; }
#tblClientes tbody tr:hover { background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%); transform: scale(1.005); box-shadow: 0 4px 12px rgba(102,126,234,0.1); }
.badge-fotos { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.badge-sin-foto { background: #e2e8f0; color: #718096; }
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column; max-height: calc(100vh - 60px); }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px 30px; flex-shrink: 0; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; font-size: 1.3rem; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; font-size: 1.8rem; font-weight: 300; transition: all 0.3s ease; }
.modal-modern .modal-header .close:hover { opacity: 1; transform: rotate(90deg); }
.modal-modern .modal-body { padding: 30px; background: #fafbfc; overflow-y: auto; flex: 1 1 auto; }
.modal-modern .modal-footer { padding: 18px 30px; border-top: 2px solid #e2e8f0; background: white; flex-shrink: 0; }
.modal-modern form { display: flex; flex-direction: column; flex: 1 1 auto; overflow: hidden; }
.modal-modern .form-group label { font-weight: 600; color: #4a5568; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; }
.modal-modern .form-control { border-radius: 10px; border: 2px solid #e2e8f0; padding: 11px 14px; transition: all 0.3s ease; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.btn-modal-save { border-radius: 12px; padding: 11px 30px; font-size: 0.92rem; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); text-transform: uppercase; }
.btn-modal-cancel { border-radius: 12px; padding: 11px 28px; font-size: 0.92rem; font-weight: 600; border: 2px solid #e2e8f0; background: white; color: #718096; }
label.requerido::after { content: " *"; color: #f5576c; font-weight: 700; }
.form-section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #e2e8f0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:12px;border:none;box-shadow:0 4px 15px rgba(17,153,142,0.2);">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:12px;border:none;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-id-card"></i> Control de Clientes / Verificación NUIP</h3>
            <button class="btn btn-light" data-toggle="modal" data-target="#modalNuevoCliente"
                    style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-container">
        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">
                        <i class="fa fa-search" style="color:#667eea;"></i>
                        Buscar (NUIP / Suscriptor / Serie / Nombre)
                    </label>
                    <input type="text" name="buscar" class="form-control"
                           value="{{ request('buscar') }}"
                           placeholder="Ej: 10234567 / S-0001 / Juan Pérez">
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">Ruta</label>
                    <select name="id_ruta" class="form-control">
                        <option value="">— Todas —</option>
                        @foreach($rutas as $r)
                            <option value="{{ $r }}" {{ request('id_ruta') == $r ? 'selected' : '' }}>Ruta {{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">Ciclo</label>
                    <select name="ciclo" class="form-control">
                        <option value="">— Todos —</option>
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo }}" {{ request('ciclo') == $ciclo ? 'selected' : '' }}>{{ $ciclo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">Medidor</label>
                    <select name="tiene_medidor" class="form-control">
                        <option value="">— Todos —</option>
                        <option value="1" {{ request('tiene_medidor') === '1' ? 'selected' : '' }}>Con medidor</option>
                        <option value="0" {{ request('tiene_medidor') === '0' ? 'selected' : '' }}>Sin medidor</option>
                    </select>
                </div>
                <div class="col-md-2" style="margin-top:10px;">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:12px;font-weight:700;">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary w-100 mt-1" style="border-radius:12px;">
                        <i class="fa fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div class="table-modern-container">
        <table id="tblClientes" class="table table-hover" style="width:100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Suscriptor</th>
                    <th>NUIP / Documento</th>
                    <th>Tipo Doc.</th>
                    <th>Nombre Completo</th>
                    <th>Serie Medidor</th>
                    <th>Teléfono</th>
                    <th>ID Ruta</th>
                    <th>Consecutivo</th>
                    <th>Fotos</th>
                    <th>Última Act.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td><strong>{{ $c->suscriptor }}</strong></td>
                    <td>
                        @if($c->nuip)
                            <span style="font-family:monospace;font-size:0.9rem;">{{ $c->nuip }}</span>
                        @else
                            <span style="color:#a0aec0;font-style:italic;">Sin NUIP</span>
                        @endif
                    </td>
                    <td>{{ $c->tipo_documento ?? '—' }}</td>
                    <td>{{ trim($c->nombre . ' ' . $c->apellido) ?: '—' }}</td>
                    <td>
                        @if($c->serie_medidor)
                            <span style="font-family:monospace;font-size:0.82rem;background:#f0f4ff;padding:3px 8px;border-radius:6px;">
                                {{ $c->serie_medidor }}
                            </span>
                        @else
                            <span style="color:#a0aec0;">—</span>
                        @endif
                    </td>
                    <td>{{ $c->telefono ?? '—' }}</td>
                    <td>{{ $c->id_ruta ?? '—' }}</td>
                    <td>{{ $c->consecutivo ?? '—' }}</td>
                    <td>
                        @php
                            $nFotos = $c->fotos->count();
                            $tieneM = $c->fotos->where('tipo','medidor')->count() > 0;
                            $tieneP = $c->fotos->where('tipo','predio')->count() > 0;
                        @endphp
                        @if($nFotos > 0)
                            <span class="badge-fotos">{{ $nFotos }}</span>
                            @if($tieneM) <span style="font-size:0.68rem;color:#38ef7d;" title="Tiene foto de medidor">M</span> @endif
                            @if($tieneP) <span style="font-size:0.68rem;color:#4facfe;" title="Tiene foto de predio">P</span> @endif
                        @else
                            <span class="badge-fotos badge-sin-foto">Sin fotos</span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem;color:#718096;">
                        {{ $c->updated_at ? $c->updated_at->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <button class="btn btn-info btn-sm btn-ver-cliente"
                                data-panel-url="{{ route('clientes.panel', $c->id) }}"
                                data-ver-url="{{ route('clientes.show', $c->id) }}"
                                title="Ver perfil">
                            <i class="fa fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:40px;color:#a0aec0;">
                        <i class="fa fa-users" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                        No se encontraron clientes.
                        @if(request('buscar'))
                            — <a href="{{ route('clientes.index') }}">Ver todos</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:20px;">
          
        </div>
    </div>
</div>

{{-- MODAL NUEVO CLIENTE --}}
<div class="modal fade modal-modern" id="modalNuevoCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Nuevo Perfil de Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <p class="form-section-title"><i class="fa fa-id-card"></i> Identificación</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="requerido">Código Suscriptor</label>
                                <input type="text" name="suscriptor" class="form-control"
                                       placeholder="Ej: S-00012345" required
                                       id="inputSuscriptor">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Documento</label>
                                <select name="tipo_documento" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    @foreach(['CC','TI','CE','PA','NIT'] as $td)
                                        <option value="{{ $td }}">{{ $td }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>NUIP / Número Documento</label>
                                <input type="text" name="nuip" class="form-control" placeholder="Número">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre(s)</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Nombre" id="inputNombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Apellido(s)</label>
                                <input type="text" name="apellido" class="form-control" placeholder="Apellido" id="inputApellido">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="Teléfono" id="inputTelefono">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Dirección del predio" id="inputDireccion">
                            </div>
                        </div>
                    </div>

                    <p class="form-section-title"><i class="fa fa-cogs"></i> Datos de Servicio</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Estrato</label>
                                <select name="estrato_id" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    @foreach($estratos as $e)
                                        <option value="{{ $e->id }}">{{ $e->numero }} — {{ $e->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Servicios</label>
                                <select name="servicios" class="form-control">
                                    <option value="AG-AL">Acueducto + Alcantarillado</option>
                                    <option value="AG">Solo Acueducto</option>
                                    <option value="AL">Solo Alcantarillado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Uso</label>
                                <select name="tipo_uso" class="form-control">
                                    <option value="RESIDENCIAL">Residencial</option>
                                    <option value="COMERCIAL">Comercial</option>
                                    <option value="INDUSTRIAL">Industrial</option>
                                    <option value="OFICIAL">Oficial</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>¿Tiene Medidor?</label>
                                <select name="tiene_medidor" class="form-control">
                                    <option value="1">Sí — Con medidor</option>
                                    <option value="0">No — Se factura por promedio</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sector / Zona (CU)</label>
                                <input type="text" name="sector" class="form-control" placeholder="Ej: CENTRO, NORTE, CU-01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Estado del Servicio</label>
                                <select name="estado" class="form-control">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="SUSPENDIDO">Suspendido</option>
                                    <option value="CORTADO">Cortado</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ID Ruta (Grupo)</label>
                                <input type="number" name="id_ruta" class="form-control"
                                       placeholder="Ej: 5" min="1" id="inputIdRuta">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ruta de Lectura</label>
                                <input type="text" name="ruta" class="form-control"
                                       placeholder="Ej: RUTA-01, NORTE-A" id="inputRuta">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Consecutivo en Ruta</label>
                                <input type="number" name="consecutivo" class="form-control"
                                       placeholder="Ej: 15" min="1" id="inputConsecutivo">
                            </div>
                        </div>
                    </div>

                    <p class="form-section-title"><i class="fa fa-tachometer"></i> Medidor</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Serie del Medidor</label>
                                <input type="text" name="serie_medidor" class="form-control"
                                       placeholder="Ej: M-00345 / XXXX-0001" id="inputSerie">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="padding: 10px 0 0; font-size:0.8rem; color:#718096;">
                                <i class="fa fa-info-circle" style="color:#667eea;"></i>
                                Si el suscriptor ya tiene órdenes cargadas, los datos de nombre, dirección y
                                serie se auto-completarán desde el sistema al guardar.
                            </div>
                        </div>
                    </div>

                    <p class="form-section-title"><i class="fa fa-camera"></i> Foto de referencia (opcional)</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Foto</label>
                                <select name="tipo_foto" class="form-control">
                                    <option value="medidor">Foto del Medidor</option>
                                    <option value="predio">Foto del Predio / Fachada</option>
                                    <option value="documento">Documento / Cédula</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Seleccionar imagen</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="fa fa-save"></i> Guardar Cliente
                    </button>

                </div>
                <div class="modal-footer">
                    
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     DRAWER — Panel lateral de detalle rápido de cliente
     Slide-in desde la derecha; no recarga la tabla ni pierde los filtros.
     ════════════════════════════════════════════════════════════════════════ --}}

{{-- Overlay oscuro --}}
<div id="panelClienteOverlay" onclick="cerrarPanelCliente()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1049;transition:opacity .3s;"></div>

{{-- Drawer --}}
<div id="panelCliente"
     style="
        position:fixed; top:0; right:-500px; width:460px; max-width:95vw; height:100vh;
        background:white; z-index:1050;
        transition:right .35s cubic-bezier(.4,0,.2,1);
        overflow-y:auto; overflow-x:hidden;
        box-shadow:-8px 0 40px rgba(0,0,0,.18);
     ">

    {{-- Barra superior fija del drawer --}}
    <div style="position:sticky;top:0;z-index:10;background:white;border-bottom:2px solid #f0f0f0;
                padding:12px 16px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;color:#2d3748;font-size:.9rem;">
            <i class="fa fa-id-card" style="color:#2e50e4;"></i> Perfil del Cliente
        </span>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="#" id="panelBtnVerPerfil" target="_blank"
               style="font-size:.78rem;font-weight:700;color:#2e50e4;text-decoration:none;
                      background:#eef2ff;padding:4px 12px;border-radius:10px;">
                <i class="fa fa-external-link-alt"></i> Ver perfil completo
            </a>
            <button onclick="cerrarPanelCliente()" title="Cerrar"
                    style="background:none;border:none;font-size:1.4rem;color:#718096;cursor:pointer;line-height:1;">
                &times;
            </button>
        </div>
    </div>

    {{-- Spinner --}}
    <div id="panelClienteSpinner" style="text-align:center;padding:60px 20px;">
        <i class="fa fa-circle-notch fa-spin fa-2x" style="color:#2e50e4;"></i>
        <div style="margin-top:12px;color:#718096;font-size:.85rem;">Cargando perfil…</div>
    </div>

    {{-- Contenido inyectado vía AJAX --}}
    <div id="panelClienteBody"></div>

</div>

@endsection

@section('scriptsPlugins')
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/select2/js/select2.full.min.js")}}" type="text/javascript"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
@endsection

@section('scripts')
<script>
$(function () {
    $('#tblClientes').DataTable({
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Mostrar Todo"]],
        order:    [[10, 'desc']],
        language: idioma_espanol,
        columnDefs: [
            { orderable: false, targets: [11] }
        ],
        dom: '<"row"<"col-md-6 form-inline"l><"col-md-6 form-inline justify-content-end"B>>rt<"row"<"col-md-8 form-inline"i><"col-md-4 form-inline"p>>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm',
              title: 'Listado de Clientes', exportOptions: { columns: ':not(:last-child)' } },
            { extend: 'csvHtml5',   text: '<i class="fa fa-file-csv"></i> CSV',   className: 'btn btn-secondary btn-sm',
              title: 'Listado de Clientes', exportOptions: { columns: ':not(:last-child)' } },
        ],
    });
});

// ── Panel lateral de cliente ──────────────────────────────────────────────────

function abrirPanelCliente(panelUrl, verUrl) {
    // Mostrar overlay y panel
    var overlay = document.getElementById('panelClienteOverlay');
    var panel   = document.getElementById('panelCliente');
    var body    = document.getElementById('panelClienteBody');
    var spinner = document.getElementById('panelClienteSpinner');

    body.innerHTML = '';
    spinner.style.display = 'block';
    overlay.style.display = 'block';
    panel.style.right      = '0';

    document.getElementById('panelBtnVerPerfil').href = verUrl;

    // Cargar contenido
    $.get(panelUrl)
        .done(function (html) {
            spinner.style.display = 'none';
            body.innerHTML = html;
        })
        .fail(function () {
            spinner.style.display = 'none';
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#e53e3e;">' +
                '<i class="fa fa-exclamation-circle fa-2x"></i>' +
                '<div style="margin-top:10px;">No se pudo cargar el perfil.</div></div>';
        });
}

function cerrarPanelCliente() {
    document.getElementById('panelCliente').style.right   = '-500px';
    document.getElementById('panelClienteOverlay').style.display = 'none';
    setTimeout(function () {
        document.getElementById('panelClienteBody').innerHTML = '';
    }, 400);
}

// Cerrar con Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarPanelCliente();
});

$(document).on('click', '.btn-ver-cliente', function () {
    abrirPanelCliente($(this).data('panel-url'), $(this).data('ver-url'));
});

// ─────────────────────────────────────────────────────────────────────────────

var idioma_espanol = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible en esta tabla =(",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix": "",
    "sSearch": "Buscar:",
    "sUrl": "",
    "sInfoThousands": ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
    },
    "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    },
    "buttons": {
        "copy": "Copiar",
        "colvis": "Visibilidad"
    }
};
</script>
@endsection
