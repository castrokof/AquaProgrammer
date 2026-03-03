@extends("theme.$theme.layout")

@section('titulo')
'Macromedidores'
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>
<style>
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px 30px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; font-size: 1.4rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; font-size: 1.8rem; font-weight: 300; transition: all 0.3s ease; }
.modal-modern .modal-header .close:hover { opacity: 1; transform: rotate(90deg); }
.modal-modern .modal-body { padding: 30px; background: #fafbfc; }
.modal-modern .modal-footer { padding: 20px 30px; border-top: 2px solid #e2e8f0; background: white; }
.modal-modern .form-group { margin-bottom: 20px; }
.modal-modern .form-group label { font-weight: 600; color: #4a5568; font-size: 0.85rem; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px; }
.modal-modern .form-group label i { color: #667eea; }
.modal-modern .form-control { border-radius: 12px; border: 2px solid #e2e8f0; padding: 12px 16px; font-size: 1rem; transition: all 0.3s ease; background: white; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.modal-modern .btn-modal-cancel { border-radius: 12px; padding: 11px 28px; font-weight: 600; border: 2px solid #e2e8f0; background: white; color: #718096; }
.modal-modern .btn-modal-save { border-radius: 12px; padding: 11px 30px; font-weight: 700; border: none; background: linear-gradient(135deg,#667eea,#764ba2); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); text-transform: uppercase; }
label.requerido::after { content: " *"; color: #f5576c; font-weight: 700; }
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 22px 26px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.4rem; margin: 0; }
.modern-card .card-body { padding: 24px; background: #fafbfc; }
/* DataTables override */
#tblMacros_wrapper .dataTables_filter input,
#tblMacros_wrapper .dataTables_length select { border-radius: 10px; border: 2px solid #e2e8f0; padding: 6px 12px; }
#tblMacros_wrapper .dataTables_filter input:focus,
#tblMacros_wrapper .dataTables_length select:focus { border-color: #667eea; outline: none; }
#tblMacros thead th { background: linear-gradient(135deg,#3d57ceff,#776a84ff); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 13px 10px; border: none; white-space: nowrap; }
#tblMacros thead th.sorting::after,
#tblMacros thead th.sorting_asc::after,
#tblMacros thead th.sorting_desc::after { color: rgba(255,255,255,0.7); }
#tblMacros tbody td { padding: 11px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; font-size: 0.82rem; }
#tblMacros tbody tr:hover { background: #f8f9ff; }
.badge-lecturas { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: linear-gradient(135deg,#667eea,#764ba2); color: white; }
.badge-sin-lecturas { background: #e2e8f0; color: #718096; }
@keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
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
            <h3><i class="fa fa-tachometer"></i> Macromedidores</h3>
            <button class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;border:2px solid rgba(255,255,255,0.4);border-radius:10px;padding:8px 18px;font-weight:600;"
                    onclick="$('#modalCrear').modal('show')">
                <i class="fa fa-plus"></i> Crear Macromedidor
            </button>
        </div>
        <div class="card-body">

            {{-- Filtro por usuario (server-side, antes de DataTables) --}}
            <form method="GET" action="{{ route('macromedidores.index') }}" style="margin-bottom:16px;">
                <div class="row">
                    <div class="col-md-4">
                        <select name="usuario_id" class="form-control" style="border-radius:10px;border:2px solid #e2e8f0;">
                            <option value="">— Todos los usuarios —</option>
                            @foreach($usuarios as $id => $nombre)
                                <option value="{{ $id }}" {{ request('usuario_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary" style="border-radius:10px;font-weight:600;">
                            <i class="fa fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('macromedidores.index') }}" class="btn btn-default" style="border-radius:10px;">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>

            <table id="tblMacros" class="table table-hover" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Ubicación</th>
                        <th>L. Base</th>
                        <th>Última Lectura</th>
                        <th>Consumo Último</th>
                        <th>Fecha Última</th>
                        <th>Lecturas</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($macros as $macro)
                        @php $ultima = $macro->ultimaLectura; @endphp
                        <tr>
                            <td>{{ $macro->id }}</td>
                            <td><strong style="color:#2d3748;">{{ $macro->codigo_macro }}</strong></td>
                            <td style="text-align:left;max-width:200px;">{{ $macro->ubicacion ?: '-' }}</td>
                            <td>{{ number_format($macro->lectura_anterior) }}</td>
                            <td>
                                @if($ultima)
                                    <strong style="color:#11998e;font-size:1rem;">{{ number_format($ultima->lectura_actual) }}</strong>
                                @else
                                    <span style="color:#a0aec0;">Sin lecturas</span>
                                @endif
                            </td>
                            <td>
                                @if($ultima)
                                    @php $c = $ultima->consumo; @endphp
                                    <span style="color:{{ $c < 0 ? '#f5576c' : '#11998e' }};font-weight:700;">
                                        {{ $c >= 0 ? '+' : '' }}{{ number_format($c) }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-size:0.78rem;color:#718096;">
                                {{ $ultima ? $ultima->fecha_lectura->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td>
                                @php $total = $macro->lecturas()->count(); @endphp
                                @if($total > 0)
                                    <span class="badge-lecturas">{{ $total }}</span>
                                @else
                                    <span class="badge-lecturas badge-sin-lecturas">0</span>
                                @endif
                            </td>
                            <td>{{ $macro->usuario ? $macro->usuario->nombre : '-' }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('macromedidores.show', $macro->id) }}"
                                   class="btn btn-sm" style="background:linear-gradient(135deg,#4facfe,#00f2fe);color:white;border:none;" title="Ver timeline">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <button class="btn btn-sm" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white;border:none;" title="Editar"
                                        onclick="abrirModalEditar({{ $macro->id }},'{{ $macro->codigo_macro }}','{{ addslashes($macro->ubicacion) }}',{{ $macro->lectura_anterior ?: 0 }},{{ $macro->usuario_id ?: 'null' }})">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                @if($total == 0)
                                <form action="{{ route('macromedidores.destroy', $macro->id) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este macromedidor?')">
                                    {{ csrf_field() }}{{ method_field('DELETE') }}
                                    <button type="submit" class="btn btn-sm"
                                            style="background:linear-gradient(135deg,#eb3349,#f45c43);color:white;border:none;" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal fade modal-modern" id="modalCrear" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('macromedidores.store') }}">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Crear Macromedidor</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="requerido"><i class="fa fa-barcode"></i> Código</label>
                        <input type="text" class="form-control" name="codigo_macro" placeholder="Ej: MAC-001" required style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-map-marker"></i> Ubicación</label>
                        <textarea class="form-control" name="ubicacion" rows="2" placeholder="Dirección o descripción"></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-dashboard"></i> Lectura inicial</label>
                        <input type="number" class="form-control" name="lectura_anterior" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="requerido"><i class="fa fa-user"></i> Usuario Asignado</label>
                        <select class="form-control" name="usuario_id" required>
                            <option value="">-- Seleccionar usuario --</option>
                            @foreach($usuarios as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-modal-save"><i class="fa fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade modal-modern" id="modalEditar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="formEditar">
                {{ csrf_field() }}{{ method_field('PUT') }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Editar Macromedidor</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="requerido"><i class="fa fa-barcode"></i> Código</label>
                        <input type="text" class="form-control" name="codigo_macro" id="editCodigo" required style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-map-marker"></i> Ubicación</label>
                        <textarea class="form-control" name="ubicacion" id="editUbicacion" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-dashboard"></i> Lectura base</label>
                        <input type="number" class="form-control" name="lectura_anterior" id="editLectura" min="0">
                    </div>
                    <div class="form-group">
                        <label class="requerido"><i class="fa fa-user"></i> Usuario Asignado</label>
                        <select class="form-control" name="usuario_id" id="editUsuario" required>
                            <option value="">-- Seleccionar usuario --</option>
                            @foreach($usuarios as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-modal-save"><i class="fa fa-save"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scriptsPlugins")
<script src="{{ asset("assets/$theme/plugins/datatables/jquery.dataTables.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js") }}" type="text/javascript"></script>
<script>
$(document).ready(function() {
    var idioma = {"sProcessing":"Procesando...","sLengthMenu":"Mostrar _MENU_ registros","sZeroRecords":"No se encontraron resultados","sEmptyTable":"Ningún dato disponible","sInfo":"Mostrando del _START_ al _END_ de _TOTAL_ registros","sInfoEmpty":"Mostrando 0 registros","sInfoFiltered":"(filtrado de _MAX_ total)","sSearch":"Buscar:","sLoadingRecords":"Cargando...","oPaginate":{"sFirst":"Primero","sLast":"Último","sNext":"Siguiente","sPrevious":"Anterior"}};
    $('#tblMacros').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        language: idioma,
        columnDefs: [
            { orderable: false, targets: [9] }
        ]
    });
});

function abrirModalEditar(id, codigo, ubicacion, lectura, usuarioId) {
    document.getElementById('formEditar').action = '/macromedidores/' + id;
    document.getElementById('editCodigo').value   = codigo;
    document.getElementById('editUbicacion').value = ubicacion || '';
    document.getElementById('editLectura').value   = lectura;
    if (usuarioId) document.getElementById('editUsuario').value = usuarioId;
    $('#modalEditar').modal('show');
}
</script>
@endsection
