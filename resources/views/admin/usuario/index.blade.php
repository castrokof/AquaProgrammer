@extends("theme.$theme.layout")

@section('titulo')
    Usuarios
@endsection
@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       
@endsection


@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/usuario/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/usuario/crearuser.js")}}" type="text/javascript"></script>    
@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.form-mensaje')
    <div class="card card-info">
        <div class="card-header with-border">
          <h3 class="card-title">Usuarios</h3>
          <div class="card-tools pull-right">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-u"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Usuario</button>
            </button>
          </div>
        </div>
      <div class="card-body table-responsive p-0">
        
      <table id="usuarios" class="table table-hover table-bordered text-nowrap">
        <thead>
        <tr>
               <th class="btn-accion-tabla tooltipsC" title="Editar este registro"><i class="fa fa-fw fa-pencil-alt"></i></th>
              <th class="btn-accion-tabla tooltipsC" title="Editar password"><i class="fas fa-key"></i></th>    
              <th>Id</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Tipo de Usuario</th>
              <th>Email</th>
              <th>Empresa</th>
              <th>Password</th>
              <th>Estado</th>
              <th>Rol</th>
             
        </tr>
        </thead>
        <tbody>
            @foreach ($datas as $data1)
            <tr>
                 <td>
                <a href="{{route('editar_usuario', ['id' => $data1->id])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                  <i class="fa fa-fw fa-pencil-alt"></i>
                </a>
                </td>
                <td>
                <a href="{{url("usuario/$data1->id/password")}}" class="btn-accion-tabla tooltipsC" title="Editar password">
                  <i class="fas fa-key"></i>
                </a>
                </td>
                <td>{{$data1->id}}</td>
                <td>{{$data1->usuario}}</td>
                <td>{{$data1->nombre}}</td>
                <td>{{$data1->tipodeusuario}}</td>
                <td>{{$data1->email}}</td>
                <td>{{$data1->empresa}}</td>
                <td>{{$data1->password}}</td>
                <td>{{$data1->estado}}</td>
                <td>
                    @foreach($data1->roles1 as $rol)
                    
                    {{$rol->nombre}}    
                        
                    @endforeach
                    
                </td>
            </tr>
        @endforeach          
        </tbody>
      </table>
    </div>
  </form>
    <!-- /.card-body -->
</div>
</div>
</div>

    <div class="modal fade" tabindex="-1" id ="modal-u" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">   
        <div class="row">
            <div class="col-lg-12">
              @include('includes.form-error')
              @include('includes.form-mensaje')    
               <div class="card card-warning">
                <div class="card-header">
                  <h3 class="card-title">Crear Usuarios</h3>
                  <div class="card-tools pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
              <form action="{{route('guardar_usuario')}}" id="form-general" class="form-horizontal" method="POST">
                @csrf
                <div class="card-body">
                                  @include('admin.usuario.form')
                              </div>
                              <!-- /.card-body -->
                              <div class="card-footer">
                                
                                  <div class="col-lg-3"></div>
                                  <div class="col-lg-6">
                                  @include('includes.boton-form-crear-user')    
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
 
 jQuery(function($) {
        //initiate dataTables plugin

        var myTable = 
        $('#usuarios')
        //.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
        .DataTable({
        language: idioma_espanol,
        processing: true,

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
                   className: "btn btn-primary"
    
    
                      }
                   ],
        
    
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
