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
      <div class="card-body table-responsive p-2">
        
      <table id="usuarios" class="table table-hover p-2 table-bordered text-nowrap">
        <thead>
        <tr>
              <th>action</th>
              <th>Id</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Tipo de Usuario</th>
              <th>Email</th>
              <th>Empresa</th>
              <th>Estado</th>
              <th>Rol</th>
             
        </tr>
        </thead>
        <tbody>
           
        </tbody>
      </table>
    </div>
 
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


@include('admin.usuario.formpass')

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
 
    $(document).ready(function() {
   
        var myTable = 
       $('#usuarios').DataTable({
                        language: idioma_espanol,
                        processing: true,
                        lengthMenu: [
                            [25, 50, 100, 500, -1],
                            [25, 50, 100, 500, "Mostrar Todo"]
                        ],
                        processing: true,
                        serverSide: true,
                        aaSorting: [
                            [1, "asc"]
                        ],

                        ajax: {
                            url: "{{ route('usuario') }}",
                        },
                columns: [
                    {
                        data: 'action',
                        orderable: false
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'usuario'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'tipodeusuario'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'empresa'
                    },
                    {
                        data: 'estado'
                    },
                    {
                        data: 'roles1[0].nombre'
                    }
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
                   className: "btn btn-primary"
    
    
                      }
                   ],
        
    
        });
    
    
    
        $(document).on('click', '.epassword', function() {
            var id = $(this).attr('id');
            var usuario = $(this).attr('usuario1');

            $('#usuario_id1').val(usuario);
            $('#idpassu').val(id);
            $('#modal-xlpass').modal('show');


        });
    
   $('#actualizarpass').click(function(){
    event.preventDefault();
       var id = $('#idpassu').val();
       var passwordu = $('#passwordu').val();
       var remenber_tokenu = $('#remenber_tokenu').val();
   
       if(passwordu != remenber_tokenu){
         
        $('button[type="button"]').attr('enable','disabled'); 

       }else if(passwordu == '' || remenber_tokenu == ''){
  
        Swal.fire({
              title: 'Los campos no pueden estar vacios',
              type: 'warning',
              showCloseButton: true,
              confirmButtonText: 'Aceptar',
                }) 
        
     
       }else{
  
        Swal.fire({
          title: "¿Estás seguro?",
          text: "Estás por actualizar el password del usuario",
          type: "success",
          showCancelButton: true,
          showCloseButton: true,
          confirmButtonText: 'Aceptar',
          }).then((result)=>{
         if(result.value){  
         
            $.ajax({
                  url:"password1/" + id + "",
                  method:'put',
                  data:{password:passwordu, remenber_token:remenber_tokenu,
                  
                    "_token": $("meta[name='csrf-token']").attr("content")
                  
                  },
                  success:function(respuesta)
                  {  
                    if(respuesta.mensaje ='ok') {
                      $('#modal-xlpass').modal('hide');
                      $('#passwordu').val('');
                      $('#remenber_tokenu').val('');
                      Manteliviano.notificaciones('Password usuario actualizado correctamente', 'Sistema System App','success');
                   
                  }else if(respuesta.mensaje ='ng'){


                    Manteliviano.notificaciones('Las contraseñas deben coincidir', 'Sistema System App','error');
                  }
                  }
                   });
  
                }
           
              
            });
           
         }     
      });
      
      
        $(document).on('click', '.edit', function() {
            var id = $(this).attr('id');

            $.ajax({
                url: "usuario/" + id + "/editar",
                dataType: "json",
                success: function(data) {
                    $('#usuario').val(data.result.usuario);
                    $('#nombre').val(data.result.nombre);
                    $('#tipodeusuario').val(data.result.tipodeusuario);
                    $('#email').val(data.result.email);
                    $('#empresa').val(data.result.empresa);
                    $('#estado').val(data.result.estado);
                    $('#password').val(data.result.password).prop('disabled', true).prop(
                        'required', false);
                    $('#remenber_token').val(data.result.remenber_token).prop('disabled',
                        true).prop('required', false);
                    $('#rol_id').val(data.result.rol_id);
                    $('#hidden_id').val(id)
                    $('.card-title').text('Editar usuario');
                    $('#action_button').val('Edit');
                    $('#action').val('Edit');
                    $('#modal-u').modal('show');

                },
               


            }).fail(function(jqXHR, textStatus, errorThrown) {

                if (jqXHR.status === 403) {

                    Manteliviano.notificaciones('No tienes permisos para realizar esta accion',
                        'Sistema Ventas', 'warning');

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
