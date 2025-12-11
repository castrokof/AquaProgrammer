@extends("theme.$theme.layout")

@section('titulo')
    Tablero de Control
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>

<style>
/* Estilo moderno iOS-inspired */
.modern-card {
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
    overflow: hidden;
}

.modern-card .card-header {
    background: linear-gradient(135deg, #2e50e4ff 0%, #2b0c49ff 100%);
    border: none;
    padding: 20px 24px;
    border-radius: 0;
}

.modern-card .card-header h3 {
    color: white;
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
}

.modern-card .card-body {
    padding: 24px;
    background: #fafbfc;
}

/* Dashboard Cards */
.dashboard-container {
    padding: 20px 0;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    height: 100%;
    position: relative;
    overflow: hidden;
    transform: translateY(0);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    opacity: 0.9;
    z-index: 1;
}

.stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.stat-card > * {
    position: relative;
    z-index: 2;
}

.stat-card.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card.primary::after {
    content: '📋';
    position: absolute;
    font-size: 8rem;
    right: -20px;
    bottom: -30px;
    opacity: 0.1;
    z-index: 1;
}

.stat-card.success {
    background: linear-gradient(135deg, #0b655eff 0%, #1a743dff 100%);
    color: white;
}

.stat-card.success::after {
    content: '✓';
    position: absolute;
    font-size: 8rem;
    right: -10px;
    bottom: -30px;
    opacity: 0.1;
    z-index: 1;
}

.stat-card.warning {
    background: linear-gradient(135deg, #b76983ff 0%, #70484dff 100%);
    color: white;
}

.stat-card.warning::after {
    content: '⏱';
    position: absolute;
    font-size: 8rem;
    right: -20px;
    bottom: -30px;
    opacity: 0.1;
    z-index: 1;
}

.stat-card.danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.stat-card.danger::after {
    content: '⚡';
    position: absolute;
    font-size: 8rem;
    right: -20px;
    bottom: -30px;
    opacity: 0.1;
    z-index: 1;
}

.stat-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-card.info::after {
    content: '📊';
    position: absolute;
    font-size: 8rem;
    right: -20px;
    bottom: -30px;
    opacity: 0.1;
    z-index: 1;
}

.stat-card.purple {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #333;
}

.stat-card.purple::after {
    content: '🎯';
    position: absolute;
    font-size: 8rem;
    right: -20px;
    bottom: -30px;
    opacity: 0.15;
    z-index: 1;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 15px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 12px 0;
    line-height: 1;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-change {
    font-size: 0.75rem;
    margin-top: 8px;
    opacity: 0.9;
}

/* Tabla compacta y moderna */
#tablero {
    font-size: 0.85rem;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

#tablero thead th {
    background: linear-gradient(135deg, #3d57ceff 0%, #776a84ff 100%);
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 8px;
    border: none;
    white-space: nowrap;
    text-align: center;
}

#tablero tbody td {
    padding: 10px 8px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    text-align: center;
    font-size: 0.82rem;
}

#tablero tbody tr {
    background: white;
    transition: all 0.2s ease;
}

#tablero tbody tr:hover {
    background: #f8f9ff;
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

#tablero tfoot th {
    background: #f8f9fa;
    font-weight: 700;
    padding: 14px 8px;
    border-top: 2px solid #667eea;
    color: #667eea;
    font-size: 0.85rem;
}

/* Tooltips modernos */
.tooltipsC {
    cursor: help;
    position: relative;
}

.tooltipsC:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 8px;
}

/* Contenedor de tabla responsivo */
.table-modern-container {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow-x: auto;
}

/* Scrollbar personalizado estilo iOS */
.table-modern-container::-webkit-scrollbar {
    height: 8px;
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

/* Botones modernos */
.dt-buttons .btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    margin: 0 4px 8px 0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.dt-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.dt-buttons .btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.dt-buttons .btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
}

.dt-buttons .btn-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
}

.dt-buttons .btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

/* DataTables search y length */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 20px;
    padding: 8px 16px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Badges para números */
#tablero tbody td:nth-child(6),
#tablero tbody td:nth-child(7),
#tablero tbody td:nth-child(8) {
    font-weight: 600;
}

#tablero tbody td:nth-child(9),
#tablero tbody td:nth-child(10),
#tablero tbody td:nth-child(11),
#tablero tbody td:nth-child(12),
#tablero tbody td:nth-child(13),
#tablero tbody td:nth-child(14) {
    font-weight: 600;
    font-size: 0.8rem;
}

/* Responsive mejoras */
@media (max-width: 768px) {
    #tablero {
        font-size: 0.75rem;
    }
    
    #tablero thead th,
    #tablero tbody td {
        padding: 8px 6px;
    }
    
    .modern-card .card-body {
        padding: 16px;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
}

/* Loader moderno */
.dataTables_processing {
    background: rgba(102, 126, 234, 0.95) !important;
    color: white !important;
    border-radius: 12px !important;
    border: none !important;
    padding: 20px 30px !important;
    font-weight: 600 !important;
}

/* Animación de entrada */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card {
    animation: fadeInUp 0.5s ease-out;
}
</style>
@endsection


@section('scripts')
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
        <div class="card modern-card card-secondary">
            <div class="card-header with-border">
                <h3 class="card-title">📊 Tablero de Control</h3>
            </div>  
            <div class="card-body">
                @csrf
                @include('admin.admin.form')
            </div>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="col-lg-12">
        <div class="dashboard-container">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card primary">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number" id="dash-asignados">0</div>
                        <div class="stat-label">Asignados</div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card warning">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-number" id="dash-pendientes">0</div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card success">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number" id="dash-ejecutadas">0</div>
                        <div class="stat-label">Ejecutadas</div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card danger">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-number" id="dash-criticas">0</div>
                        <div class="stat-label">Críticas</div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card info">
                        <div class="stat-icon">📊</div>
                        <div class="stat-number" id="dash-normales">0</div>
                        <div class="stat-label">Normales</div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="stat-card purple">
                        <div class="stat-icon">💯</div>
                        <div class="stat-number" id="dash-porcentaje">0%</div>
                        <div class="stat-label">Completado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="table-modern-container">
            <table id="tablero" class="table table-hover table-bordered">
                <thead>
                    <tr> 
                        <th>Ciclo</th>              
                        <th>Periodo</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>División</th>
                        <th>Asignados</th>
                        <th>Pendientes</th>
                        <th>Ejecutadas</th>
                        <th class="tooltipsC" title="Alto consumo">A</th>
                        <th class="tooltipsC" title="Bajo consumo">B</th>
                        <th class="tooltipsC" title="Negativo">N(-)</th>
                        <th class="tooltipsC" title="Consumo 0">C0</th>
                        <th class="tooltipsC" title="Normal">NL</th>
                        <th class="tooltipsC" title="Causado">C</th>
                        <th>Inicio</th>
                        <th>Final</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="5" style="text-align:left">Totales:</th>              
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>    
            </table>
        </div>
    </div>
</div>
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>

<script>
  $(document).ready(function() {

         fill_datatable();   

          function fill_datatable(periodo1 = '', zona1 = '' )
         {
          var datatable = $('#tablero').DataTable({
              language: idioma_espanol,
              lengthMenu: [ -1],
              processing: true,
              serverSide: true,
              
                  
          ajax:{
                url:"{{ route('tablero')}}",
                data:{periodo1:periodo1, zona1:zona1}
              },
              columns: [
                {
                    data:'Ciclo',
                    name:'Ciclo'
                },
                {
                    data:'Periodo',
                    name:'Periodo'
                },
                {
                  data:'nombreu'
                 
                },
                {
                  data:'Usuario'
                 
                },
                {
                    data:'idDivision',
                    name:'idDivision'
                },
                {
                    data:'Asignados',
                    name:'Asignados'
                },
                {
                    data:'Pendientes',
                    name:'Pendientes'
                },
                {
                    data:'Ejecutadas',
                    name:'Ejecutadas'
                },
                 {
                    data:'Altos',
                    name:'Altos'
                },
                {
                    data:'Bajos',
                    name:'Bajos'
                },
                {
                    data:'Negativo',
                    name:'Negativo'
                },
                {
                    data:'Consumo_cero',
                    name:'Consumo_cero'
                },
                {
                    data:'Normales',
                    name:'Normales'
                },
                {
                    data:'Causados',
                    name:'Causados'
                },
                
                {
                    data:'inicio',
                    name:'inicio'
                },
                {
                    data:'Final',
                    name:'Final'
                }

              ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
  
            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
  
            
           asignadas = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            pendientes = api
                .column( 6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            ejecutadas = api
                .column( 7, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            altos = api
                .column( 8, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            bajos = api
                .column( 9, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            negativo = api
                .column( 10, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                
            consumo_cero = api
                .column( 11, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            normales = api
                .column( 12, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            causados = api
                .column( 13, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );    
            
            // Actualizar Dashboard
            var criticas = altos + bajos + negativo + consumo_cero;
            var porcentaje = asignadas > 0 ? Math.round((ejecutadas / asignadas) * 100) : 0;
            
            $('#dash-asignados').text(asignadas.toLocaleString());
            $('#dash-pendientes').text(pendientes.toLocaleString());
            $('#dash-ejecutadas').text(ejecutadas.toLocaleString());
            $('#dash-criticas').text(criticas.toLocaleString());
            $('#dash-normales').text(normales.toLocaleString());
            $('#dash-porcentaje').text(porcentaje + '%');
                     
  
            
            $( api.column( 5 ).footer() ).html(
                asignadas 
            );
            $( api.column( 6 ).footer() ).html(
                pendientes 
            );
            $( api.column( 7 ).footer() ).html(
                ejecutadas 
            );
            $( api.column( 8 ).footer() ).html(
                altos 
            );
            $( api.column( 9 ).footer() ).html(
                bajos 
            );
            $( api.column( 10 ).footer() ).html(
                negativo 
            );
            $( api.column( 11 ).footer() ).html(
                consumo_cero 
            );
            $( api.column( 12 ).footer() ).html(
                normales 
            );
            $( api.column( 13 ).footer() ).html(
                causados 
            );
                
                
                
           
        },
              //Botones----------------------------------------------------------------------
        "dom":'Bfrtip',
               buttons: [
                   {

               extend:'copyHtml5',
               titleAttr: 'Copy',
               className: "btn btn-info"


                  },
                  {

               extend:'excelHtml5',
               titleAttr: 'Excel',
               className: "btn btn-success"


                  },
                   {

               extend:'csvHtml5',
               titleAttr: 'csv',
               className: "btn btn-warning"


                  },
                  {

               extend:'pdfHtml5',
               titleAttr: 'pdf',
               className: "btn btn-primary"


                  }
               ]
             });
}    

    
        
      $('#buscar').click(function(){

       var periodo1 = $('#periodo1').val();
       var zona1 = $('#zona1').val();

        if(periodo1 != '' && zona1 != ''){

            $('#tablero').DataTable().destroy();
            fill_datatable(periodo1, zona1);

        }else{
        
             swal({
            title: 'Debes digitar periodo y zona',
            icon: 'warning',
            buttons:{
                cancel: "Cerrar"
                
                    }
              })
        }
        
    });        

      $('#reset').click(function(){
        $('#zona1').val('');
        $('#periodo1').val('');
        $('#tablero').DataTable().destroy();
        fill_datatable();
      });
});

 

   var idioma_espanol =
                 {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
                } ;
                
           
  
         
  </script>
  


@endsection