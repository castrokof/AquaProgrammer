@extends("theme.$theme.layout")

@section('titulo')
    Tablero de Control
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       

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
        <div class="card card-secondary">
        <div class="card-header with-border">
          <h3 class="card-title">Tablero de control</h3>
        </div>  
          {{-- <form action="{{route('tablero')}}" id="form-general" class="form-horizontal" method="GET">
            @csrf --}}
            <div class="card-body">
              @csrf
               @include('admin.admin.form')
            </div>
          {{-- </form> --}}
        </div>

          
    </div>
  

        <!-- /.card-header -->
<div class="card-body table-responsive p-0" style="height:;">
      <table id="tablero"  class="table table-head-fixed text-nowrap table-hover table-bordered">
        <thead>
        <tr> 
              <th>Ciclo</th>              
              <th>Periodo</th>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>idDivision</th>
              <th>Asignados</th>
              <th>Pendientes</th>
              <th>Ejecutadas</th>
              <th>A</th>
              <th>B</th>
              <th>N</th>
              <th>C0</th>
              <th>NL</th>
              <th>C</th>
              <th>Fecha Inicio</th>
              <th>Fecha Final</th>
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
            </tr>
        </tfoot>    
      </table>
         
    </div>
  
    <!-- /.card-body -->
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
