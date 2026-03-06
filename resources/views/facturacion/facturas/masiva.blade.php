@extends("theme.$theme.layout")

@section('titulo', 'Facturación Masiva')

@section('styles')
<style>
.modern-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; overflow: hidden; margin-bottom: 25px; background: white; animation: fadeIn 0.5s ease-out; }
.modern-card .card-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px; display: flex; justify-content: space-between; align-items: center; }
.modern-card .card-header h3 { color: white; font-weight: 700; font-size: 1.4rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
.filtros-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
.filtros-container .form-control { border-radius: 12px; border: 2px solid #e2e8f0; padding: 10px 14px; font-size: 0.9rem; transition: all 0.3s ease; }
.filtros-container .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.1); outline: none; }
.table-modern-container { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow-x: auto; }
#tblLecturas { font-size: 0.85rem; border-radius: 12px; overflow: hidden; }
#tblLecturas thead th { background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%); color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 10px; border: none; white-space: nowrap; text-align: center; }
#tblLecturas tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 0.82rem; }
#tblLecturas tbody tr { background: white; transition: all 0.2s ease; }
#tblLecturas tbody tr:hover { background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%); transform: scale(1.005); box-shadow: 0 4px 12px rgba(102,126,234,0.1); }
.badge-estado { padding: 4px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.badge-estado.FACTURADO_AUTOMATICO { background: #c6f6d5; color: #22543d; }
.badge-estado.PENDIENTE_REVISION { background: #fed7d7; color: #742a2a; }
.badge-estado.ERROR { background: #fed7d7; color: #742a2a; }
.badge-estado.SALTEADO { background: #e2e8f0; color: #4a5568; }
.btn-grad { border-radius: 12px; padding: 11px 32px; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); font-size: 0.92rem; }
.btn-grad:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-preview { border-radius: 12px; padding: 10px 24px; font-weight: 700; border: 2px solid #667eea; color: #667eea; background: white; font-size: 0.88rem; transition: all 0.3s; }
.btn-preview:hover { background: #667eea; color: white; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('contenido')
<div class="container-fluid">

    <div class="modern-card">
        <div class="card-header">
            <h3><i class="fa fa-bolt"></i> Facturación Masiva Automática</h3>
            <a href="{{ route('facturas.index') }}" class="btn btn-light" style="border-radius:12px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="filtros-container">
                <form method="GET" action="{{ route('facturas.masiva') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label style="font-weight:600;color:#4a5568;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">
                                <i class="fa fa-calendar" style="color:#667eea;"></i> Período de Lectura
                            </label>
                            <select class="form-control" id="selPeriodoFiltro" name="periodo_lectura_id">
                                <option value="">— Seleccione período —</option>
                                @foreach($periodos as $p)
                                <option value="{{ $p->id }}" {{ request('periodo_lectura_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }} — {{ $p->estado }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8" style="margin-top:10px;">
                            <button type="submit" class="btn btn-primary" style="border-radius:12px;font-weight:700;margin-right:8px;">
                                <i class="fa fa-search"></i> Cargar Lecturas
                            </button>
                            <a href="{{ route('facturas.masiva') }}" class="btn btn-secondary" style="border-radius:12px;">
                                <i class="fa fa-times"></i> Limpiar
                            </a>
                            <button type="button" class="btn btn-grad" id="btnFacturarNormales" style="margin-left:8px;" {{ !request('periodo_lectura_id') ? 'disabled' : '' }}>
                                <i class="fa fa-bolt"></i> Facturar Normales (54)
                            </button>
                            <button type="button" class="btn btn-preview" id="btnVerSeleccion" {{ !request('periodo_lectura_id') ? 'disabled' : '' }}>
                                <i class="fa fa-list-check"></i> Seleccionar Altos/Bajos
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tabla principal con DataTable --}}
            <div class="table-modern-container">
                <table id="tblLecturas" class="table table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="chkTodos"></th>
                            <th>#</th>
                            <th>Suscriptor</th>
                            <th>Cliente</th>
                            <th>Consumo</th>
                            <th>Crítica</th>
                            <th>Lectura Ant.</th>
                            <th>Lectura Act.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lecturas ?? [] as $l)
                        <tr>
                            <td><input type="checkbox" class="chk-lectura" value="{{ $l->id }}"></td>
                            <td>{{ $l->id }}</td>
                            <td><strong>{{ $l->suscriptor }}</strong></td>
                            <td>{{ trim($l->cliente->nombre . ' ' . $l->cliente->apellido) }}</td>
                            <td>{{ $l->consumo }} m³</td>
                            <td>
                                <span class="badge badge-{{ $l->critica == 'NORMAL-54' ? 'success' : 'warning' }}">
                                    {{ $l->critica }}
                                </span>
                            </td>
                            <td>{{ $l->lectura_anterior }}</td>
                            <td>{{ $l->lectura_actual }}</td>
                            <td>
                                @if($l->facturada)
                                    <span class="badge-estado FACTURADO_AUTOMATICO">FACTURADA</span>
                                @else
                                    <span class="badge-estado PENDIENTE_REVISION">PENDIENTE</span>
                                @endif
                            </td>
                            <td>
                                @if(!$l->facturada)
                                    <button class="btn btn-info btn-sm btn-facturar-individual" data-id="{{ $l->id }}" title="Facturar">
                                        <i class="fa fa-file-invoice"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="text-align:center;padding:40px;color:#a0aec0;">
                                <i class="fa fa-file-invoice" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                                No hay lecturas cargadas. Seleccione un período y cargue las lecturas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Panel selección oculto --}}
<div id="panelSeleccion" style="display:none;" class="container-fluid mt-3">
    <div class="filtros-container">
        <h5><i class="fa fa-hand-pointer"></i> Seleccionar Lecturas para Facturar (Altos/Bajos)</h5>
        <p style="color:#718096;font-size:.85rem;margin-bottom:16px;">
            Marque las lecturas con críticas ALTOS/BAJOS u otras que desee facturar manualmente.
        </p>
        
        <div style="max-height:500px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;">
            <table class="tabla-detalles" style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="chkTodosSeleccion"></th>
                        <th>Suscriptor</th>
                        <th>Consumo</th>
                        <th>Crítica</th>
                        <th>Lectura Ant.</th>
                        <th>Lectura Act.</th>
                    </tr>
                </thead>
                <tbody id="tablaSeleccionBody">
                </tbody>
            </table>
        </div>
        
        <div style="margin-top:16px;display:flex;gap:10px;">
            <button class="btn btn-grad" id="btnFacturarSeleccionados">
                <i class="fa fa-check"></i> Facturar Seleccionadas
            </button>
            <button class="btn btn-preview" id="btnCancelarSeleccion">
                <i class="fa fa-times"></i> Cancelar
            </button>
        </div>
    </div>
</div>

<style>
.tabla-detalles th { background:#f7fafc; padding:10px 12px; text-align:left; font-weight:700; color:#4a5568; text-transform:uppercase; font-size:.7rem; letter-spacing:.5px; }
.tabla-detalles td { padding:10px 12px; border-bottom:1px solid #e2e8f0; }
.tabla-detalles tr:hover { background:#f7fafc; }
</style>

{{-- Modal confirmación facturación seleccionados --}}
<div class="modal fade modal-modern" id="modalConfirmarSeleccion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirmar Facturación</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea facturar <strong id="lblCantidadSeleccion"></strong> lecturas seleccionadas?</p>
                <p style="color:#e53e3e;font-size:0.85rem;"><i class="fa fa-info-circle"></i> Esta acción no se puede deshacer.</p>
                <input type="hidden" id="idsSeleccionados">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-modal-save" id="btnConfirmarFacturacion">
                    <i class="fa fa-check"></i> Sí, Facturar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-modern .modal-content { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
.modal-modern .modal-header { background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%); border: none; padding: 24px 30px; }
.modal-modern .modal-header .modal-title { color: white; font-weight: 700; font-size: 1.3rem; }
.modal-modern .modal-header .close { color: white; opacity: 0.8; text-shadow: none; font-size: 1.8rem; font-weight: 300; transition: all 0.3s ease; }
.modal-modern .modal-header .close:hover { opacity: 1; transform: rotate(90deg); }
.modal-modern .modal-body { padding: 30px; background: #fafbfc; }
.modal-modern .modal-footer { padding: 18px 30px; border-top: 2px solid #e2e8f0; background: white; }
.btn-modal-save { border-radius: 12px; padding: 11px 30px; font-weight: 700; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4); }
.btn-modal-cancel { border-radius: 12px; padding: 11px 28px; font-weight: 600; border: 2px solid #e2e8f0; background: white; color: #718096; }
</style>
@endsection

@section('scriptsPlugins')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
@endsection

@section('scripts')
<script>

$(document).ready(function() {

    // Inicializar DataTable
    @if($lecturas && count($lecturas) > 0)
    tableLecturas = $('#tblLecturas').DataTable({
        paging: false,
        order: [[1, 'asc']],
        language: {
            search: 'Filtrar en página:',
            zeroRecords: 'No se encontraron resultados',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ lecturas',
            infoEmpty: 'Sin registros',
            infoFiltered: '(filtrados de _MAX_ en total)'
        },
        columnDefs: [
            { orderable: false, targets: [0, 9] }
        ]
    });
    @endif

    // Checkbox seleccionar todos
    $('#chkTodos').on('change', function() {
        $('.chk-lectura').prop('checked', this.checked);
    });

    $('#chkTodosSeleccion').on('change', function() {
        $('.chk-seleccion').prop('checked', this.checked);
    });

    // Cancelar selección
    $('#btnCancelarSeleccion').on('click', function() {
        $('#panelSeleccion').slideUp();
    });

    // Facturar normales (54)
    $('#btnFacturarNormales').on('click', function() {
        const periodoId = $('#selPeriodoFiltro').val();
        if (!periodoId) return;

        if (!confirm('¿Está seguro de facturar todas las lecturas NORMAL-54?')) {
            return;
        }

        $.ajax({
            url: '{{ route("facturas.masiva.facturar-normales") }}',
            method: 'POST',
            data: {
                periodo_lectura_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.ok) {
                    alert('Se facturaron ' + res.cantidad + ' lecturas exitosamente.');
                    location.reload();
                } else {
                    alert('Error: ' + res.mensaje);
                }
            },
            error: function(xhr) {
                alert('Error en la solicitud');
            }
        });
    });

    // Ver selección (altos/bajos)
    $('#btnVerSeleccion').on('click', function() {
        const periodoId = $('#selPeriodoFiltro').val();
        if (!periodoId) return;

        $.ajax({
            url: '{{ route("facturas.masiva.lecturas-no-normales") }}',
            method: 'GET',
            data: { periodo_lectura_id: periodoId },
            success: function(res) {
                if (res.ok && res.lecturas.length > 0) {
                    // Mostrar modal con opciones para filtrar altos/bajos
                    let html = '';
                    res.lecturas.forEach(function(l) {
                        html += '<tr>';
                        html += '<td><input type="checkbox" class="chk-seleccion" value="' + l.id + '"></td>';
                        html += '<td>' + l.suscriptor + '</td>';
                        html += '<td>' + l.consumo + ' m³</td>';
                        html += '<td><span class="badge badge-warning">' + l.critica + '</span></td>';
                        html += '<td>' + l.lectura_anterior + '</td>';
                        html += '<td>' + l.lectura_actual + '</td>';
                        html += '</tr>';
                    });
                    $('#tablaSeleccionBody').html(html);
                    $('#panelSeleccion').slideDown();
                } else {
                    alert('No hay lecturas diferentes a NORMAL-54 para este período.');
                }
            }
        });
    });

    // Facturar seleccionadas
    $('#btnFacturarSeleccionados').on('click', function() {
        const seleccionados = [];
        $('.chk-seleccion:checked').each(function() {
            seleccionados.push($(this).val());
        });

        if (seleccionados.length === 0) {
            alert('Seleccione al menos una lectura');
            return;
        }

        const periodoId = $('#selPeriodoFiltro').val();
        $('#lblCantidadSeleccion').text(seleccionados.length);
        $('#idsSeleccionados').val(seleccionados.join(','));
        $('#modalConfirmarSeleccion').modal('show');
    });

    // Confirmar facturación
    $('#btnConfirmarFacturacion').on('click', function() {
        const ids = $('#idsSeleccionados').val().split(',');
        const periodoId = $('#selPeriodoFiltro').val();

        $.ajax({
            url: '{{ route("facturas.masiva.facturar-seleccionadas") }}',
            method: 'POST',
            data: {
                periodo_lectura_id: periodoId,
                lecturas: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#modalConfirmarSeleccion').modal('hide');
                if (res.ok) {
                    alert('Se facturaron ' + res.cantidad + ' lecturas exitosamente.');
                    location.reload();
                } else {
                    alert('Error: ' + res.mensaje);
                }
            },
            error: function(xhr) {
                alert('Error en la solicitud');
            }
        });
    });

    // Facturar individual
    $(document).on('click', '.btn-facturar-individual', function() {
        const id = $(this).data('id');
        const periodoId = $('#selPeriodoFiltro').val();

        if (!confirm('¿Facturar esta lectura?')) {
            return;
        }

        $.ajax({
            url: '{{ route("facturas.masiva.facturar-individual") }}',
            method: 'POST',
            data: {
                lectura_id: id,
                periodo_lectura_id: periodoId,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.ok) {
                    alert('Factura generada exitosamente.');
                    location.reload();
                } else {
                    alert('Error: ' + res.mensaje);
                }
            },
            error: function(xhr) {
                alert('Error en la solicitud');
            }
        });
    });
});
</script>
@endsection
