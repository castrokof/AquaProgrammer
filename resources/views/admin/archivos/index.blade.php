@extends("theme.$theme.layout")
@section('titulo')
  Archivos de entrada  
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/rol/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/archivo/crear.js")}}" type="text/javascript"></script>    
@endsection
@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       



<style>
.loader { visibility: hidden; background-color: rgba(255, 253, 253, 0.952); 
position: absolute; 
z-index: +100 !important; 
width: 100%;  
height:100%; } 
.loader img { position: relative; top:5%; left:40%;
  width: 200px; height: 200px; } 
</style>


@endsection



@section('contenido')
<div class="row">
<div class="col-lg-12">
  @include('includes.form-error')
  @include('includes.form-mensaje')  
<div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Archivos</h3>
      <div class="card-tools pull-right">
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-lg"><i class="fa fa-fw fa-plus-circle"></i> Subir Archivo</button>
        </button>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
      <table id="tarchivos" class="table table-hover table-bordered text-nowrap">
            <thead>
            <tr>
            
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Registros</th>
                    <th>Estado</th>
                    <th>Usuario</th>
                    <th>Periodo</th>
                    <th>Ciclo</th>
                    <th>Total de registros</th>
            </tr>
            </thead>
            <tbody>
                    {{-- @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->id}}</td>
                            <td>{{$data->nombre}}</td>
                            <td>{{$data->fecha}}</td>
                            <td>{{$data->registros}}</td>
                            <td>{{$data->estado}}</td>
                            <td>{{$data->usuario}}</td>
                            <td>{{$data->periodo}}</td>
                            <td>{{$data->zona}}</td>
                            <td>{{$data->cantidad}}</td>
                            
                        </tr>
                    @endforeach --}}
            </tbody>
      </table>
    </div>
    
  </div>
</div>
</div>
<!--Modal-->

<div class="modal fade" tabindex="-1" id ="modal-lg" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">   
  <div class="row">
      <div class="col-lg-12">
        <div class="card card-warning">
          <div class="card-header">
            <h3 class="card-title">Subir archivo</h3>
            <div class="card-tools pull-right">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
          </div>
        <form action="" id="Form" name="Form"class="form-horizontal" method="POST" enctype="multipart/form-data" >
          @csrf
          <div class="loader"> <img src="{{asset("assets/$theme/dist/img/loader6.gif")}}" class="" /> </div>   
         
         <div class="card-body">
                            @include('admin.archivos.form')
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                          
                            <div class="col-lg-3"></div>
                            <div class="col-lg-6">
                            @include('includes.boton-form-crear')
                            </div>
                        </div>
                        <!-- /.card-footer -->
            </form>
                   
      
         
    </div>
  </div>
</div>
</div>
</div>
</div>
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>


<script src="https://cdn.datatables.net/plug-ins/1.10.20/api/sum().js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>

<script>




jQuery(document).ready(function() {
        
  var datatable = $('#tarchivos').DataTable({
     language: idioma_espanol,
     lengthMenu: [ [25, 50, 100, 500, -1 ], [25, 50, 100, 500, "Mostrar Todo"] ],
     processing: true,
     serverSide: true,
     ajax:{
       url:"{{ route('archivo')}}",
          },
     columns: [
      {data:'id'},
      {data:'nombre'},
      {data:'fecha'},
      {data:'registros'},
      {data:'estado'},
      {data:'usuario'},
      {data:'periodo'},
      {data:'zona'},
      {data:'cantidad'}
     ],

     //Botones----------------------------------------------------------------------
     
     "dom":'<"row"<"col-md-9 form-inline"l><"col-xs-3 form-inline"B>>rt<"row"<"col-md-8 form-inline"i> <"col-md-4 form-inline"p>>',
                   
                   buttons: [
                      {
    
                   extend:'copyHtml5',
                   titleAttr: 'Copy',
                   title:"seguimiento",
                   className: "btn btn-info"
    
    
                      },
                      {
    
                   extend:'excelHtml5',
                   titleAttr: 'Excel',
                   title:"seguimiento",
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
                   className: "btn btn-primary",
                   orientation: 'landscape',
                   pageSize: 'LEGAL'
                   
                   }
                   ],
       
     

 
 
     
     
    });//initiate dataTables plugin
$('#subir').click(function(){

var formData = new FormData(document.getElementById("Form"));


$.ajax({  
  beforeSend: function(){ 
            $('.loader').css("visibility", "visible"); }, 
                url:"{{ route('subir_archivo')}}",
                method:'post',
                data: formData,
                contentType:false,
                processData:false,
                success:function(response)
                { 
                  
                  if(response.mensaje == 'ok') {
                    $('#modal-lg').modal('hide'); 
                      Manteliviano.notificaciones('Archivo cargado exitosamente', 'Sistema AcuasurRural', 'success');
                      $('#tarchivos').DataTable().ajax.reload();
                  }else if(response.mensaje == 'vacio'){
                      
                      Manteliviano.notificaciones('No seleccionaste ningun arhivo', 'Sistema AcuasurRural', 'info');
                  }else if(response.mensaje =='ng'){
                      $('#modal-lg').modal('hide'); 
                      Manteliviano.notificaciones('Registros duplicados en base de datos', 'Sistema AcuasurRural', 'warning');
                      $('#tarchivos').DataTable().ajax.reload();
                      
                }
                },
                
                complete: function(){ 
                  $('.loader').css("visibility", "hidden");
                 }
                 
                 }).fail( function( jqXHR, textStatus, errorThrown ) {

                  if (jqXHR.status === 0) {

                    alert('Not connect: Verify Network.');

                  } else if (jqXHR.status == 404) {

                    alert('Requested page not found [404]');

                  } else if (jqXHR.status == 500) {
                    
                    Manteliviano.notificaciones('El archivo no tienen la estructura adecuada', 'Sistema AcuasurRural', 'warning');
                    $('#tarchivos').DataTable().ajax.reload();

                  } else if (textStatus === 'parsererror') {

                    alert('Requested JSON parse failed.');

                  } else if (textStatus === 'timeout') {

                    alert('Time out error.');

                  } else if (textStatus === 'abort') {

                    alert('Ajax request aborted.');

                  } else {

                    
                    Manteliviano.notificaciones('El campo file debe ser un archivo de tipo: xls, xlsx', 'Sistema AcuasurRural', 'warning');
                    $('#tarchivos').DataTable().ajax.reload();

                  }

});


          
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
                }   
</script>
@endsection


