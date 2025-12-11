@extends("theme.$theme.layout")

@section('titulo')
   Seguimiento de Órdenes
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>
<link href="{{asset("assets/$theme/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css")}}" rel="stylesheet" type="text/css"/>
<link href="{{asset("assets/$theme/plugins/select2/css/select2.min.css")}}" rel="stylesheet" type="text/css"/>

<style>

/* Modal moderno */
.modal-modern .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
}

.modal-modern .modal-header {
    background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%);
    border: none;
    padding: 24px 30px;
    border-radius: 0;
}

.modal-modern .modal-header .modal-title {
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-modern .modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
    font-size: 1.8rem;
    font-weight: 300;
    transition: all 0.3s ease;
}

.modal-modern .modal-header .close:hover {
    opacity: 1;
    transform: rotate(90deg);
}

.modal-modern .modal-body {
    padding: 35px 30px;
    background: #fafbfc;
}

.modal-modern .modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #e2e8f0;
    background: white;
}

/* Contenedor de foto */
.foto-container {
    margin-bottom: 25px;
    text-align: center;
    position: relative;
}

.foto-preview {
    width: 100%;
    max-width: 400px;
    height: 250px;
    border-radius: 15px;
    object-fit: cover;
    border: 3px solid #e2e8f0;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: inline-block;
}

.foto-preview:hover {
    transform: scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.foto-loading {
    width: 100%;
    max-width: 400px;
    height: 250px;
    border-radius: 15px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #a0aec0;
    font-size: 1rem;
    border: 3px solid #e2e8f0;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.foto-error {
    width: 100%;
    max-width: 400px;
    height: 250px;
    border-radius: 15px;
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #c62828;
    font-size: 1rem;
    border: 3px dashed #ef5350;
    gap: 10px;
}

.foto-error i {
    font-size: 3rem;
    opacity: 0.5;
}

.foto-badge {
    position: absolute;
    top: 15px;
    right: calc(50% - 190px);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.foto-zoom-icon {
    position: absolute;
    bottom: 15px;
    right: calc(50% - 190px);
    background: rgba(0,0,0,0.6);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.foto-container:hover .foto-zoom-icon {
    opacity: 1;
}

/* Form groups en modal */
.modal-modern .form-group {
    margin-bottom: 24px;
}

.modal-modern .form-group label {
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-modern .form-group label i {
    color: #667eea;
    font-size: 1.1rem;
}

.modal-modern .form-control {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    padding: 14px 18px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.modal-modern .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
    background: white;
}

.modal-modern .form-control:disabled,
.modal-modern .form-control[readonly] {
    background: #f7fafc;
    color: #718096;
    border-color: #e2e8f0;
    cursor: not-allowed;
}

/* Input numérico especial */
.modal-modern input[type="number"] {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2d3748;
    text-align: center;
}

/* Botones del modal */
.modal-modern .btn-modal-cancel {
    border-radius: 12px;
    padding: 12px 30px;
    font-size: 0.95rem;
    font-weight: 600;
    border: 2px solid #e2e8f0;
    background: white;
    color: #718096;
    transition: all 0.3s ease;
}

.modal-modern .btn-modal-cancel:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    color: #4a5568;
    transform: translateY(-2px);
}

.modal-modern .btn-modal-save {
    border-radius: 12px;
    padding: 12px 35px;
    font-size: 0.95rem;
    font-weight: 700;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modal-modern .btn-modal-save:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.modal-modern .btn-modal-save:active {
    transform: translateY(-1px);
}

/* Animación del modal */
.modal.fade .modal-dialog {
    transform: scale(0.8);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal.show .modal-dialog {
    transform: scale(1);
    opacity: 1;
}

/* Badge info en campos readonly */
.info-badge {
    display: inline-block;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Divisor visual */
.form-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #e2e8f0 50%, transparent 100%);
    margin: 25px 0;
}

/* Icono de ayuda */
.help-icon {
    color: #a0aec0;
    font-size: 0.9rem;
    cursor: help;
    margin-left: 5px;
    transition: color 0.3s ease;
}

.help-icon:hover {
    color: #667eea;
}

/* Modal más ancho para acomodar la foto */
.modal-modern .modal-dialog {
    max-width: 600px;
}

@media (max-width: 768px) {
    .foto-preview,
    .foto-loading,
    .foto-error {
        max-width: 100%;
        height: 200px;
    }
    
    .foto-badge,
    .foto-zoom-icon {
        right: 15px;
    }
}



/* Estilo moderno iOS-inspired */
.modern-card {
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    border: none;
    overflow: hidden;
    margin-bottom: 25px;
    background: white;
    transform: translateY(0);
    transition: all 0.4s ease;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.15);
}

.modern-card .card-header {
    background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%);
    border: none;
    padding: 24px;
    border-radius: 0;
}

.modern-card .card-header h3 {
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.modern-card .card-body {
    padding: 30px;
    background: #fafbfc;
}

/* Formulario moderno */
.form-group label {
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

.select2-container--bootstrap4 .select2-selection {
    border-radius: 12px !important;
    border: 2px solid #e2e8f0 !important;
    padding: 8px 12px !important;
    min-height: 48px !important;
}

.select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #667eea !important;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1) !important;
}

/* Botones modernos */
.btn-modern {
    border-radius: 12px;
    padding: 12px 28px;
    font-size: 0.95rem;
    font-weight: 700;
    border: none;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-modern.btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.btn-modern.btn-default {
    background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
    color: white;
}

/* Input de fecha moderno */
input[type="date"] {
    position: relative;
    padding-right: 40px;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    border-radius: 4px;
    margin-right: 2px;
    opacity: 0.6;
    filter: invert(0.4) sepia(1) saturate(5) hue-rotate(220deg);
}

input[type="date"]:hover::-webkit-calendar-picker-indicator {
    opacity: 1;
}

/* Tabla moderna */
.table-modern-container {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow-x: auto;
}

#seguimiento {
    font-size: 0.85rem;
    border-radius: 12px;
    overflow: hidden;
}

#seguimiento thead th {
    background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%);
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 10px;
    border: none;
    white-space: nowrap;
    text-align: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

#seguimiento tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    text-align: center;
    font-size: 0.82rem;
}

#seguimiento tbody tr {
    background: white;
    transition: all 0.2s ease;
}

#seguimiento tbody tr:hover {
    background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%);
    transform: scale(1.005);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

/* Scrollbar personalizado */
.table-modern-container::-webkit-scrollbar {
    height: 10px;
}

.table-modern-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-modern-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.table-modern-container::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
}

/* Botones de DataTables */
.dt-buttons .btn {
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    margin: 0 6px 10px 0;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}

.dt-buttons .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

.dt-buttons .btn-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.dt-buttons .btn-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.dt-buttons .btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.dt-buttons .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

/* DataTables filter input */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 20px;
    padding: 10px 20px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
    margin-left: 10px;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

/* Badges y botones en tabla */
.btn-sm {
    border-radius: 8px;
    font-weight: 600;
    padding: 6px 12px;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-card .card-body {
        padding: 20px;
    }
    
    #seguimiento {
        font-size: 0.75rem;
    }
    
    #seguimiento thead th,
    #seguimiento tbody td {
        padding: 10px 8px;
    }
}

/* Loader moderno */
.dataTables_processing {
    background: rgba(102, 126, 234, 0.95) !important;
    color: white !important;
    border-radius: 15px !important;
    border: none !important;
    padding: 25px 40px !important;
    font-weight: 600 !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
}

/* Animaciones */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modern-card {
    animation: fadeIn 0.5s ease-out;
}

/* Espaciado mejorado */
.form-group {
    margin-bottom: 20px;
}

.row {
    margin-bottom: 10px;
}

/* Labels con icono */
label.requerido::after {
    content: " *";
    color: #f5576c;
    font-weight: 700;
}


</style>
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/rol/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/rol/crear.js")}}" type="text/javascript"></script>

<script src="https://cdn.datatables.net/plug-ins/1.10.20/api/sum().js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.form-mensaje')
        
        <div class="card modern-card">
            <div class="card-header">
                <h3 class="card-title">🔍 Seguimiento de Órdenes</h3>
            </div>  
            
            <div class="card-body">
                @csrf
                
                 @include('admin.ordenes.forms.form-seguimiento')
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="table-modern-container">
            <table id="seguimiento" class="table table-hover table-bordered">
                <thead>
                    <tr> 
                        <th>Devolver/Orden</th>
                        <th>Detalle</th>
                        <th>Detalle_Url</th>
                        <th>Orden</th>
                        <th>Fecha Ejecución</th>
                        <th>Usuario</th>
                        <th>Periodo</th>
                        <th>Ciclo</th>
                        <th>Ruta</th>
                        <th>Consecutivo</th>
                        <th>Dirección</th>
                        <th>Nombre</th>
                        <th>Suscriptor</th>
                        <th>Medidor</th>
                        <th>Obs. General</th>
                        <th>Lect. Actual</th>
                        <th>Editar Lectura</th>
                        <th>Lect. Ant.</th>
                        <th>Promedio</th>
                        <th>Consumo</th>
                        <th>Causa</th>
                        <th>Crítica</th>
                        <th>Foto</th>
                        <th>Foto_Url</th>
                        <th>Observación</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Estado</th>
                        <th>CriticaAdd</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('admin.ordenes.forms.form-modalEditLectura')
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/select2/js/select2.full.min.js")}}" type="text/javascript"></script>

<script>
$(document).ready(function() {
    
    // Inicializar Select2
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione una opción'
    });

    fill_datatable();

    function fill_datatable(usuario = '', periodo = '', zona = '', estado = '', fechaini = '', fechafin = '', suscriptor = '', medidor = '', critica = '') {
        var datatable = $('#seguimiento').DataTable({
            aaSorting: [[3, "desc"]],
            language: idioma_espanol,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Mostrar Todo"]],
            processing: true,
            serverSide: false,
            
            ajax: {
                url: "{{ route('seguimiento1')}}",
                data: {
                    usuario: usuario,
                    periodo: periodo,
                    zona: zona,
                    estado: estado,
                    fechaini: fechaini,
                    fechafin: fechafin,
                    suscriptor: suscriptor,
                    medidor: medidor,
                    critica: critica,
                    _token: "{{ csrf_token() }}"
                },
                method: 'post'
            },
            columns: [
                {data: 'action', name: 'action', orderable: false, searchable: false},
                {data: 'detalle', name: 'detalle', orderable: false, searchable: false},
                {data: 'detalle_Url', name: 'detalle_Url', orderable: false, searchable: false},
                {data: 'id', name: 'id'},
                {data: 'fecha_de_ejecucion', name: 'fecha_de_ejecucion', orderable: true, searchable: true},
                {data: 'Usuario', name: 'Usuario', orderable: true, searchable: true},
                {data: 'Periodo', name: 'periodo', orderable: true, searchable: true},
                {data: 'Ciclo', name: 'Ciclo', orderable: true, searchable: true},
                {data: 'id_Ruta', name: 'id_Ruta', orderable: true, searchable: true},
                {data: 'Consecutivo', name: 'Consecutivo', orderable: true, searchable: true},
                {data: 'Direccion', name: 'Direccion'},
                {data: 'Nombre', name: 'Nombre'},
                {data: 'Suscriptor', name: 'Suscriptor', orderable: true, searchable: true},
                {data: 'Ref_Medidor', name: 'Ref_Medidor'},
                {data: 'new_medidor', name: 'new_medidor'},
                {data: 'Lect_Actual', name: 'Lect_Actual'},
                {data: 'edit_lectura', name: 'edit_lectura', orderable: false, searchable: false},
                {data: 'LA', name: 'LA'},
                {data: 'Promedio', name: 'Promedio'},
                {data: 'Cons_Act', name: 'Cons_Act', orderable: true, searchable: true},
                {data: 'Causa_des', name: 'Causa_des'},
                {data: 'Critica', name: 'Critica', orderable: true, searchable: true},
                {data: 'foto', name: 'foto', orderable: false, searchable: false},
                {data: 'foto_Url', name: 'foto_Url', orderable: false, searchable: false},
                {data: 'Observacion_des', name: 'Observacion_des'},
                {data: 'Latitud', name: 'Latitud'},
                {data: 'Longitud', name: 'Longitud'},
                {data: 'Estado_des', name: 'Estado_des'},
                {data: 'Coordenada', name: 'Coordenada'}
            ],
            
            dom: '<"row"<"col-md-9 form-inline"l><"col-xs-3 form-inline"B>>rt<"row"<"col-md-8 form-inline"i><"col-md-4 form-inline"p>>',
            
            buttons: [
                {
                    extend: 'copyHtml5',
                    titleAttr: 'Copiar',
                    title: "seguimiento",
                    className: "btn btn-info"
                },
                {
                    extend: 'excelHtml5',
                    titleAttr: 'Excel',
                    title: "seguimiento",
                    className: "btn btn-success"
                },
                {
                    extend: 'csvHtml5',
                    titleAttr: 'CSV',
                    className: "btn btn-warning"
                },
                {
                    extend: 'pdfHtml5',
                    titleAttr: 'PDF',
                    className: "btn btn-primary",
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }
            ],
            
            columnDefs: [
                {targets: [2], visible: false, searchable: false},
                {targets: [23], visible: false, searchable: false},
                {targets: [28], visible: false, searchable: false}
            ]
        });
    }

    $('#buscar').click(function() {
        var usuario = $('#usuario').val();
        var periodo = $('#periodo').val();
        var zona = $('#zona').val();
        var estado = $('#estado').val();
        var fechaini = $('#fechaini').val();
        var fechafin = $('#fechafin').val();
        var suscriptor = $('#suscriptor').val();
        var medidor = $('#medidor').val();
        var critica = $('#critica').val();

        if (usuario != '' || periodo != '' || zona != '' || estado != '' || fechaini != '' || fechafin != '' || suscriptor != '' || medidor != '' || critica != '') {
            $('#seguimiento').DataTable().destroy();
            fill_datatable(usuario, periodo, zona, estado, fechaini, fechafin, suscriptor, medidor, critica);
        } else {
            swal({
                title: 'Debes digitar algún campo. Ej: periodo y ciclo',
                icon: 'warning',
                buttons: {
                    cancel: "Cerrar"
                }
            });
        }
    });

    $('#reset').click(function() {
        $('#usuario').val('').trigger('change');
        $('#periodo').val('');
        $('#zona').val('');
        $('#estado').val('').trigger('change');
        $('#fechaini').val('');
        $('#fechafin').val('');
        $('#suscriptor').val('');
        $('#medidor').val('');
        $('#critica').val('').trigger('change');
        $('#seguimiento').DataTable().destroy();
        fill_datatable();
    });

    // Función que envía el id al controlador y cambia el estado del registro
    $(document).on('click', '.update_orden', function() {
        var data = {
            orden: $(this).attr('id'),
            _token: $('input[name=_token]').val()
        };
        ajaxRequest('updateestado', data);
    });

    function ajaxRequest(url, data) {
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(data) {
                $('#seguimiento').DataTable().ajax.reload();
                Manteliviano.notificaciones(data.respuesta, data.titulo, data.icon);
            }
        });
    }


 // Función para cargar la foto
    function cargarFoto(ordenId) {
        $('#foto-loading').show();
        $('#foto-medidor').hide();
        $('#foto-error').hide();
        
        // Llamada AJAX para obtener la URL de la foto
        $.ajax({
            url: "{{ route('fotos.url', '') }}/" + ordenId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.tiene_foto && response.foto_url) {
                    // Precargar la imagen
                    var img = new Image();
                    
                    img.onload = function() {
                        $('#foto-medidor').attr('src', response.foto_url).show();
                        $('#foto-loading').hide();
                    };
                    
                    img.onerror = function() {
                        $('#foto-error').show();
                        $('#foto-loading').hide();
                    };
                    
                    img.src = response.foto_url;
                } else {
                    // No hay foto disponible
                    $('#foto-error').show();
                    $('#foto-loading').hide();
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la foto:', xhr);
                $('#foto-error').show();
                $('#foto-loading').hide();
            }
        });
    }
    
    // Abrir modal con animación
    $(document).on('click', '.edit_lectura', function() {
        var id = $(this).data('id');
        var lectura = $(this).data('lectura');
        var suscriptor = $(this).data('suscriptor');
        
        // Llenar los campos
        $('#orden_id').val(id);
        $('#suscriptor_display').val(suscriptor);
        $('#lectura_anterior').val(lectura);
        $('#lectura_nueva').val('');
        
        // Cargar la foto
        cargarFoto(id);
        
        // Mostrar modal
        $('#modalEditLectura').modal('show');
    });
    
    // Click en la foto para ver en tamaño completo
    $(document).on('click', '#foto-medidor', function() {
        var fotoUrl = $(this).attr('src');
        window.open(fotoUrl, '_blank');
    });
    
    // Limpiar formulario al cerrar
    $('#modalEditLectura').on('hidden.bs.modal', function () {
        $('#formEditLectura')[0].reset();
        $('#foto-medidor').attr('src', '').hide();
        $('#foto-loading').hide();
        $('#foto-error').hide();
    });
    
    // Auto-focus en el campo de nueva lectura
    $('#modalEditLectura').on('shown.bs.modal', function () {
        $('#lectura_nueva').focus();
    });
    
    // Enviar formulario
    $('#formEditLectura').on('submit', function(e) {
        e.preventDefault();
        
        // Deshabilitar botón de envío
        var btnSubmit = $(this).find('button[type="submit"]');
        var originalText = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        var formData = {
            orden_id: $('#orden_id').val(),
            lectura_nueva: $('#lectura_nueva').val(),
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: '{{ route("actualizar.lectura") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    // Cerrar modal
                    $('#modalEditLectura').modal('hide');
                    
                    // Recargar la tabla
                    $('#seguimiento').DataTable().ajax.reload(null, false);
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Lectura actualizada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar la lectura. Por favor intente nuevamente.'
                });
            },
            complete: function() {
                // Restaurar botón
                btnSubmit.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Validación en tiempo real
    $('#lectura_nueva').on('input', function() {
        var lecturaAnterior = parseFloat($('#lectura_anterior').val()) || 0;
        var lecturaNueva = parseFloat($(this).val()) || 0;
        
        if(lecturaNueva < lecturaAnterior) {
            $(this).css('border-color', '#f5576c');
            $(this).next('.form-text').remove();
            $(this).after('<small class="form-text text-danger mt-1"><i class="fas fa-exclamation-triangle"></i> Advertencia: La nueva lectura es menor que la anterior</small>');
        } else {
            $(this).css('border-color', '#11998e');
            $(this).next('.form-text.text-danger').remove();
        }
    });



});

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