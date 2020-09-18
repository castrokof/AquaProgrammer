<!DOCTYPE html>
<html>
<head>
    
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <title>@yield("titulo",'AcuasurRural')</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  
    <!-- CSRF Token -->
 <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset("assets/$theme/plugins/fontawesome-free/css/all.min.css")}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
   <!-- Data tables -->
  
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset("assets/$theme/dist/css/adminlte.min.css")}}">
  
  <!-- Theme Toastr -->
  
  <!-- Theme Toastr -->
  <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
  
  
  <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">-->
  <link rel="stylesheet" href="{{asset("assets/$theme/plugins/toastr/toastr.min.css")}}">
  @yield("styles")
 
  <!-- Theme Custom -->
  <link rel="stylesheet" href="{{asset("assets/css/custom.css")}}">


  
 
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition skin-blue sidebar-mini layout-fixed sidebar-collapse">
@php
$iduser = Session()->get('usuario');
$id= Session()->get('usuario_id');
@endphp
    
    
    
    
    <div class="wrapper">
     <!---- Inicio Header ---->
     @include("theme/$theme/header")
     <!----   Fin Header  ---->
     <!---- Inicio Aside  ---->
     @include("theme/$theme/aside")
     <!----   Fin Aside   ---->

     
     <!-- Content Wrapper. Contains page content -->
          <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content">
                  @yield("contenido")       
            </section>

          </div>
          <!--- Inicio Footer --->
          @include("theme/$theme/footer")
          <!--- Fin Footer --->  
    </div>
<!-- jQuery -->
<script src="{{asset("assets/$theme/plugins/jquery/jquery.min.js")}}"></script>
<!-- data tables -->

<!-- Bootstrap 4 -->
<script src="{{asset("assets/$theme/plugins/bootstrap/js/bootstrap.bundle.min.js")}}"></script>
<!-- AdminLTE App -->
<script src="{{asset("assets/$theme/dist/js/adminlte.min.js")}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset("assets/$theme/dist/js/demo.js")}}"></script>

@yield("scriptsPlugins")

<script src="//cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.js"></script>
<!-- Jq Sweet alert cdn -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<!-- Jq Toastr cdn -->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>-->
<script src="{{asset("assets/$theme/plugins/toastr/toastr.min.js")}}"></script>
<!-- Jq Validate -->
<script src="{{asset("assets/js/jquery-validation/jquery.validate.min.js")}}"></script>
<script src="{{asset("assets/js/jquery-validation/localization/messages_es.min.js")}}"></script>
<script src="{{asset("assets/js/funciones.js")}}"></script>
<script src="{{asset("assets/js/scripts.js")}}"></script>
@yield("scripts")

<script src="{{asset("assets/pages/scripts/admin/usuario/crear.js")}}" type="text/javascript"></script>
<script>

  $(document).ready(function(){
  
    $('#actualizar').click(function(){
    event.preventDefault();
     
       var password = $('#password1').val();
       var remenber_token = $('#remenber_token1').val();
   
       if(password != remenber_token){
         
        $('button[type="button"]').attr('enable','disabled'); 

       }else if(password == '' || remenber_token == ''){
  
        Swal.fire({
              title: 'Los campos no pueden estar vacios',
              icon: 'warning',
              showCloseButton: true,
              confirmButtonText: 'Aceptar',
                }) 
        
     
       }else{
  
        Swal.fire({
          title: "¿Estás seguro?",
          text: "Estás por actualizar el password",
          icon: "success",
          showCancelButton: true,
          showCloseButton: true,
          confirmButtonText: 'Aceptar',
          }).then((result)=>{
         if(result.value){  
         
            $.ajax({
                  url:"{{route('actualizar_password1',['id' =>$id])}}",
                  method:'put',
                  data:{password:password, remenber_token:remenber_token,
                  
                    "_token": $("meta[name='csrf-token']").attr("content")
                  
                  },
                  success:function(respuesta)
                  {  
                    if(respuesta.mensaje ='ok') {
                      $('#modal-xl').modal('hide');
                      $('#password1').val('');
                      $('#remenber_token1').val('');
                      Manteliviano.notificaciones('Password actualizado correctamente', 'Sistema Catastro de usuario','success');
                   
                  }else if(respuesta.mensaje ='ng'){


                    Manteliviano.notificaciones('Las contraseñas deben coincidir', 'Sistema Catastro de usuario','error');
                  }
                  }
                   });
  
                }
           
              
            });
           
         }     
      });
  
      });  
  

  
  
   
  
  </script>


  </body>
</html>
