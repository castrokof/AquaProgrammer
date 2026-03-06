@extends('layouts.app')

@section('title', 'Facturación Especial Masiva')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-bolt"></i> Facturación Especial (Altas, Bajas, Sin Lectura)</h6>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    @if(session('errores') && count(session('errores')) > 0)
                        <div class="alert alert-warning">
                            <strong>Detalles de errores:</strong>
                            <ul class="mb-0">
                                @foreach(session('errores') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulario de selección de período -->
                    <form method="GET" action="{{ route('facturacion.especial.index') }}" class="mb-4">
                        <div class="form-row align-items-end">
                            <div class="col-md-4">
                                <label for="periodo_id"><strong>Seleccionar Período:</strong></label>
                                <select name="periodo_id" id="periodo_id" class="form-control" required onchange="this.form.submit()">
                                    <option value="">-- Seleccione un período --</option>
                                    @foreach($periodos as $periodo)
                                        <option value="{{ $periodo->id }}" {{ $periodoId == $periodo->id ? 'selected' : '' }}>
                                            {{ $periodo->nombre }} ({{ $periodo->fecha_inicio }} al {{ $periodo->fecha_fin }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted">Seleccione un período para cargar las lecturas especiales pendientes.</small>
                            </div>
                        </div>
                    </form>

                    @if($periodoId && $lecturas->count() > 0)
                        <!-- Formulario de facturación -->
                        <form method="POST" action="{{ route('facturacion.especial.facturar-seleccionadas') }}" id="formFacturacion">
                            @csrf
                            <input type="hidden" name="periodo_id" value="{{ $periodoId }}">
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-secondary">Lecturas Especiales Disponibles ({{ $lecturas->count() }})</h5>
                                <div>
                                    <button type="button" class="btn btn-sm btn-info" onclick="seleccionarTodo(true)">
                                        <i class="fas fa-check-square"></i> Seleccionar Todo
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="seleccionarTodo(false)">
                                        <i class="fas fa-square"></i> Deseleccionar Todo
                                    </button>
                                    <button type="submit" class="btn btn-success" id="btnFacturar" disabled>
                                        <i class="fas fa-file-invoice-dollar"></i> Facturar Seleccionadas
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla con DataTable -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaEspeciales">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">
                                                <input type="checkbox" id="checkAll" onclick="seleccionarTodo(this.checked)">
                                            </th>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>NIT/CC</th>
                                            <th>Dirección</th>
                                            <th>Lectura Anterior</th>
                                            <th>Lectura Actual</th>
                                            <th>Consumo</th>
                                            <th>Estado</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lecturas as $lectura)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="lecturas_ids[]" value="{{ $lectura->id }}" class="check-item" onchange="verificarSeleccion()">
                                                </td>
                                                <td>{{ $lectura->id }}</td>
                                                <td>{{ $lectura->cliente->nombre ?? 'N/A' }}</td>
                                                <td>{{ $lectura->cliente->documento ?? 'N/A' }}</td>
                                                <td>{{ $lectura->cliente->direccion ?? 'N/A' }}</td>
                                                <td>{{ number_format($lectura->lectura_anterior, 2) }}</td>
                                                <td>{{ number_format($lectura->lectura_actual, 2) }}</td>
                                                <td><strong>{{ number_format($lectura->consumo, 2) }}</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $lectura->estado == 54 ? 'success' : 'warning' }}">
                                                        {{ $lectura->estado }}
                                                    </span>
                                                </td>
                                                <td>{{ $lectura->observaciones ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @elseif($periodoId && $lecturas->count() == 0)
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5>No hay lecturas especiales pendientes en este período</h5>
                            <p>Todas las lecturas son normales o ya han sido procesadas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos y Scripts para DataTable -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaEspeciales').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: '<i class="fas fa-file-excel"></i> Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-danger btn-sm', text: '<i class="fas fa-file-pdf"></i> PDF' },
            { extend: 'print', className: 'btn btn-info btn-sm', text: '<i class="fas fa-print"></i> Imprimir' }
        ],
        pageLength: 10,
        order: [[1, 'asc']]
    });
});

function seleccionarTodo(seleccionar) {
    $('.check-item').prop('checked', seleccionar);
    $('#checkAll').prop('checked', seleccionar);
    verificarSeleccion();
}

function verificarSeleccion() {
    const seleccionados = $('.check-item:checked').length;
    $('#btnFacturar').prop('disabled', seleccionados === 0);
    
    if (seleccionados > 0) {
        $('#btnFacturar').html(`<i class="fas fa-file-invoice-dollar"></i> Facturar (${seleccionados})`);
    } else {
        $('#btnFacturar').html('<i class="fas fa-file-invoice-dollar"></i> Facturar Seleccionadas');
    }
}
</script>
@endsection
