@extends("theme.$theme.layout")

@section('titulo')
  Exportar ordenes
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       

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
        <div class="card card-secondary">
        <div class="card-header with-border">
          <h3 class="card-title">Exportar ordenes</h3>
        </div>  
         
            @csrf
            <div class="card-body">
              
              @include('admin.ordenes.forms.form-exportar')
              
            </tr>
            </td> 
            </div>
           </div>
          
           
            @include('admin.ordenes.exportar.listGenerate')
           
     </div>
</div>
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>

<script>
 $(document).ready(function() {

fill_datatable();

     function fill_datatable(periodo = '', zona = '', estado = '', fechaini = '', fechafin = '')
    {
        
          $.ajax({
                    url: "{{ route('exportarCicloExcel')}}",
                    type: 'POST',
                    data:{periodo:periodo, zona:zona, estado:estado, fechaini:fechaini, fechafin:fechafin, _token:"{{ csrf_token() }}" },
                    method: 'post',
                           success: function(data) {
                            // Manejar la respuesta del servidor
                            if (data.icon == 'success') {
                               Manteliviano.notificaciones(data.respuesta, data.titulo, data.icon);

                    // Limpiar el contenedor antes de agregar los nuevos elementos
                                $('#lista-archivos').empty();
            
                                // Iterar sobre la lista de archivos y agregar cada uno como un elemento de lista con un enlace de descarga
                                $.each(data.ruta, function(i, archivo) {
                                    var nombreArchivo = archivo.split('/').pop();
                                    var $li = $('<li>');
                                    var $a = $('<a>')
                                        .attr('href', archivo)
                                        .attr('download', nombreArchivo) // Forzar descarga al hacer clic
                                        .text(nombreArchivo);
            
                                    $li.append($a);
                                    $('#lista-archivos').append($li);
                                });
                            } else {
                                // Manejar la respuesta del servidor
                                Manteliviano.notificaciones(data.respuesta, data.titulo, data.icon);
                            }
                                
                            
                    }
                  
                });
    
       
        
       
    
   }
   






$('#exportar').click(function(){
    

var periodo = $('#periodo').val();
var zona = $('#zona').val();
var estado = $('#estado').val();
var fechaini = $('#fechaini').val();
var fechafin = $('#fechafin').val();



if((periodo != '' && zona != '' && estado != '') || (fechaini != '' && fechafin != '') ){

      fill_datatable(periodo, zona, estado, fechaini, fechafin);

}else{

  Swal.fire({
            title: 'Debes digitar algun campo Ej: periodo, ciclo y estado',
            type: 'warning',
            buttons:{
                cancel: "Cerrar"
                
                    }
              });
}
});        



$('#reset').click(function(){

$('#periodo').val('');
$('#zona').val('');
$('#estado').val('');
$('#fechaini').val('');
$('#fechafin').val('');

fill_datatable();
});



 // Función que envia el id al controlador y cambia el estado del registro
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
