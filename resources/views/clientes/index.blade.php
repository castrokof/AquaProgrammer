@extends("theme.$theme.layout")

@section('titulo')
'Control de Clientes'
@endsection

@section("styles")
<style>
/* ── CARD & HEADER ── */
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.4rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
/* ── FILTROS ── */
.filtros-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
.filtros-container .form-control { border-radius: 12px; border: 2px solid #e2e8f0; padding: 10px 14px; font-size: 0.9rem; transition: all 0.3s ease; }
.filtros-container .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
/* ── TABLA ── */
.table-modern-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow-x: auto; }
#tblClientes { font-size: 0.85rem; border-radius: 12px; overflow: hidden; }
#tblClientes thead th { background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 10px; border: none; white-space: nowrap; text-align: center; }
#tblClientes tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 0.82rem; }
#tblClientes tbody tr { background: white; transition: all 0.2s ease; }
#tblClientes tbody tr:hover { background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%); transform: scale(1.005); box-shadow: 0 4px 12px rgba(102,126,234,0.1); }
/* ── BADGES ── */
.badge-fotos { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
.badge-sin-foto { background: #e2e8f0; color: #718096; }
/* ── MODAL ── */
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px 30px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; font-size: 1.4rem; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; font-size: 1.8rem; font-weight: 300; transition: all 0.3s ease; }
.modal-modern .modal-header .close:hover { opacity: 1; transform: rotate(90deg); }
.modal-modern .modal-body { padding: 35px 30px; background: #fafbfc; }
.modal-modern .modal-footer { padding: 20px 30px; border-top: 2px solid #e2e8f0; background: white; }
.modal-modern .form-group label { font-weight: 600; color: #4a5568; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
.modal-modern .form-control { border-radius: 12px; border: 2px solid #e2e8f0; padding: 12px 15px; transition: all 0.3s ease; }
.modal-modern .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.btn-modal-save { border-radius: 12px; padding: 12px 35px; font-size: 0.95rem; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); text-transform: uppercase; letter-spacing: 0.5px; }
.btn-modal-cancel { border-radius: 12px; padding: 12px 30px; font-size: 0.95rem; font-weight: 600; border: 2px solid #e2e8f0; background: white; color: #718096; }
label.requerido::after { content: " *"; color: #f5576c; font-weight: 700; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:12px;border:none;box-shadow:0 4px 15px rgba(17,153,142,0.2);">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:12px;border:none;box-shadow:0 4px 15px rgba(245,87,108,0.2);">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- HEADER CARD --}}
    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-id-card"></i> Control de Clientes / Verificación NUIP</h3>
            <button class="btn btn-light" data-toggle="modal" data-target="#modalNuevoCliente" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-plus"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="filtros-container">
        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">
                        <i class="fa fa-search" style="color:#667eea;"></i> Buscar por NUIP, Suscriptor o Nombre
                    </label>
                    <input type="text" name="buscar" class="form-control"
                           value="{{ request('buscar') }}"
                           placeholder="Ej: 10234567 / S-0001 / Juan Pérez">
                </div>
                <div class="col-md-4" style="margin-top:10px;">
                    <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;margin-right:8px;">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary" style="border-radius:12px;">
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
                    <th>Teléfono</th>
                    <th>Dirección</th>
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
                    <td>{{ $c->telefono ?? '—' }}</td>
                    <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $c->direccion ?? '—' }}
                    </td>
                    <td>
                        @if($c->fotos->count() > 0)
                            <span class="badge-fotos">{{ $c->fotos->count() }} foto(s)</span>
                        @else
                            <span class="badge-fotos badge-sin-foto">Sin fotos</span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem;color:#718096;">
                        {{ $c->updated_at ? $c->updated_at->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td>
                        <a href="{{ route('clientes.show', $c->id) }}"
                           class="btn btn-info btn-sm" title="Ver perfil">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:40px;color:#a0aec0;">
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

        {{-- PAGINACIÓN --}}
        <div style="margin-top:20px;">
            {{ $clientes->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- MODAL NUEVO CLIENTE                         --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="modal fade modal-modern" id="modalNuevoCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-user-plus"></i> Nuevo Perfil de Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="requerido">Código Suscriptor</label>
                                <input type="text" name="suscriptor" class="form-control" placeholder="Ej: S-00012345" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Documento</label>
                                <select name="tipo_documento" class="form-control">
                                    <option value="">— Seleccione —</option>
                                    <option value="CC">Cédula de Ciudadanía (CC)</option>
                                    <option value="TI">Tarjeta de Identidad (TI)</option>
                                    <option value="CE">Cédula de Extranjería (CE)</option>
                                    <option value="PA">Pasaporte (PA)</option>
                                    <option value="NIT">NIT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                <input type="text" name="nombre" class="form-control" placeholder="Nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Apellido(s)</label>
                                <input type="text" name="apellido" class="form-control" placeholder="Apellido">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="Teléfono">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Dirección del predio">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Foto</label>
                                <select name="tipo_foto" class="form-control">
                                    <option value="documento">Documento / Cédula</option>
                                    <option value="rostro">Foto del Cliente</option>
                                    <option value="medidor">Medidor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Foto de referencia (opcional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="fa fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
