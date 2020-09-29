@extends("theme.$theme.layout")

@section('titulo')
   Adicionar Critica
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       

<style>
.loader { visibility: hidden; background-color: rgba(255, 253, 253, 0.952); 
position: absolute; 
z-index: +100 !important; 
width: 80%;  
height:70%; } 
.loader img { position: relative; top:10%; left:30%;
  width: 200px; height: 200px; } 
</style>

@endsection



@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/asignacion/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/rol/crear.js")}}" type="text/javascript"></script>    
@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.form-mensaje')
        <div class="card bg-gradient-dark">
        <div class="card-header with-border">
          <h3 class="card-title">Analisis de generación de critica</h3>
        </div>  
           
            <div class="card-body">
              <form action="{{route('generar_critica')}}" id="form-general" class="form-horizontal" method="GET ">
                @csrf  
                @include('admin.ordenes.form-critica')
                              
            </tr>
            </td> 
              </form>
            </div>
          

               </div>              
              </div>
        
                        
<div class="card-body table-responsive p-0" style="height: 600px;">
    <div class="loader"> <img src="{{asset("assets/$theme/dist/img/loader6.gif")}}" class="" /> </div> 
      <table id="criticat"  class="table text-nowrap table-head-fixed table-hover table-bordered">
        <thead>
        <tr> 
              <th class="width40"><input name="selectall" id="selectall" type="checkbox" class="select-all" /> Select / Deselect All</th>
              <th>Consecutivo</th>
              <th>Critica</th>
              <th>Medidor</th>
              <th>Promedio</th>
              <th>Consumo</th>
              <th>Lectura Actual</th>
              <th>Lectura Anterior</th>
              <th>Usuario</th>
              <th>Funcionario</th>
              <th>Orden</th>
              <th>Suscriptor</th>
              <th>Direccion</th>
              <th>Recorrido</th>
              <th>Periodo</th>
              <th>Ciclo</th>
        </tr>
        </thead>
      </table>
         
    </div>
 </div>
 </div>
</form>
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>

<script>
 jQuery(document).ready(function() {

  fill_datatable();
 
 function fill_datatable(Periodo = '', Ciclo = '', Ruta = '', Critica = '', Generado = '')
{
 var datatable = $('#criticat').DataTable({
     language: idioma_espanol,
     lengthMenu: [ [500, 10, 25, 50, 100,-1 ], [500, 10, 25, 50, 100, "Mostrar Todo"] ],
     processing: true,
     serverSide: true,
     ajax:{
       url:"{{ route('critica')}}",
       data:{Periodo:Periodo, Ciclo:Ciclo, Ruta:Ruta, Critica:Critica, Generado:Generado},
           },
     columns: [
       {
           data:'checkbox', orderable: false, searchable: false
           
       },
     
       {
           data:'Consecutivo'
           
       },
       {
           data:'Critica'
           
       },
       {
           data:'Ref_Medidor'
       },
       {
           data:'Promedio'
       },
       {
           data:'Cons_Act'
       },
       {
           data:'Lect_Actual'
       },
       {
           data:'LA'
       },
       {
           data:'Usuario'
       },
       {
           data:'nombreu'
       },
       {
          data:'ordenescu_id'
       },
       {
           data:'Suscriptor'
       },
       {
           data:'Direccion'
       },
       {
           data:'recorrido'
       },
       {
           data:'Periodo'
       },
       {
           data:'Ciclo'
       }

     ]
     

 
 
     
     
    });
}    

 


$('#buscar').click(function(){

var Periodo = $('#Periodo').val();
var Ciclo = $('#Ciclo').val();
var Ruta = $('#ruta').val();
var Critica = $('#Critica').val();
var Generado = $('#Generado').val();


if(Periodo != '' && Ciclo != '' && Critica != '' || Ruta != ''){

   $('#criticat').DataTable().destroy();
   fill_datatable(Periodo, Ciclo, Ruta, Critica, Generado);

}else{

  swal({
            title: 'Debes digitar el periodo, Ciclo, Ruta y Critica',
            icon: 'warning',
            buttons:{
                cancel: "Cerrar"
                
                    }
              })
}
});        

$('#reset').click(function(){
$('#Ciclo').val('');
$('#Periodo').val('');
$('#ruta').val('');
$('#Critica').val('');
$('#Generado').val('');
$('#criticat').DataTable().destroy();
fill_datatable();


});

// $('#generar').click(function(){
//   $('#criticat').DataTable().destroy();
// });
// Generador de pdf

// $(document).on('click', '#generar', function(){
  
// var Periodo = $('#Periodo').val();
// var Ciclo = $('#Ciclo').val();
// var Critica = $('#Critica').val();

                
//    Swal.fire({
//         title: "¿Estás seguro?",
//         text: "Estás por generar ordenes de critica",
//         icon: "success",
//         showCancelButton: true,
//         showCloseButton: true,
//         confirmButtonText: 'Aceptar',
//         }).then((result)=>{
//        if(result.value){  
//              $.ajax({
            
//                 url:"{{ route('generar_critica')}}",
//                 method:'get',
//                 data:{Periodo:Periodo, Ciclo:Ciclo,  Critica:Critica,
                
//                   "_token": $("meta[name='csrf-token']").attr("content")
                
//                 },
//                 success:function(respuesta)
//                 {  
//                   if(respuesta.mensaje = 'ok') {
//                   $('#criticat').DataTable().ajax.reload();
//                   Manteliviano.notificaciones('Ordenes generadas correctamente', 'Sistema AcuasurRural', 'success');
//                   }
//                 }
               
                 
//                  });

//           }
          
//        });
         
// });
    


$(document).on('click', '#adicionar', function(){
  
          var id = [];
          
   Swal.fire({
        title: "¿Estás seguro?",
        text: "Estás por adicionar ordenes de critica",
        icon: "success",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: 'Aceptar',
        }).then((result)=>{
       if(result.value){  
        $('input:checkbox:checked').each(function() {
        id.push($(this).val());
        
           });
        
       if(id.length > 0)
        { 
             
          $.ajax({
            
                url:"{{ route('adicionar_critica')}}",
                method:'post',
                data:{id:id,
                
                  "_token": $("meta[name='csrf-token']").attr("content")
                
                },
                success:function(respuesta)
                {  
                  if(respuesta.mensaje = 'ok') {
                  $('#criticat').DataTable().ajax.reload();
                  Manteliviano.notificaciones('Ordenes adicionadas correctamente', 'Sistema AcuasurRural', 'success');
                  }
                }
               
                 
                 });

          }
          else
          {

        Swal.fire({
            title: 'Por favor seleccione una orden del checkbox',
            icon: 'warning',
            buttons:{
                cancel: "Cerrar"
                
                    }
              })
            }
       }});
         
    });
    
    $(document).on('click', '#eliminar', function(){
  
  var id = [];
  
Swal.fire({
title: "¿Estás seguro?",
text: "Estás por eliminar ordenes de critica",
icon: "success",
showCancelButton: true,
showCloseButton: true,
confirmButtonText: 'Aceptar',
}).then((result)=>{
if(result.value){  
$('input:checkbox:checked').each(function() {
id.push($(this).val());

   });

if(id.length > 0)
{ 
     
  $.ajax({
    
        url:"{{ route('eliminar_critica')}}",
        method:'post',
        data:{id:id,
        
          "_token": $("meta[name='csrf-token']").attr("content")
        
        },
        success:function(respuesta)
        {  
          if(respuesta.mensaje = 'ok') {
          $('#criticat').DataTable().ajax.reload();
          Manteliviano.notificaciones('Ordenes eliminadas correctamente', 'Sistema AcuasurRural', 'success');
          }
        }
       
         
         });

  }
  else
  {

Swal.fire({
    title: 'Por favor seleccione una orden del checkbox',
    icon: 'warning',
    buttons:{
        cancel: "Cerrar"
        
            }
      })
    }
}});
 
});     


$('#Ciclo').on('change', function() {

var P = $('#Periodo').val();
var C = $('#Ciclo').val();

if(P != '' && C != ''){
   $.get('idDivision',{P:P, C:C}, function(idDivisions)
            {   
                $('#ruta').empty();
                $('#ruta').append("<option value=''>---seleccione la ruta---</option>")
                $.each(idDivisions, function(idDiv, value){
                $('#ruta').append("<option value='" + value + "'>" + value + "</option>")
                });
            }); 
     
          }
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
