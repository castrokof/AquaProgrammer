@php
$iduser = Session()->get('usuario');
$id= Session()->get('usuario_id');
@endphp



<nav class="main-header navbar navbar-expand navbar-white navbar-light">
 <!-- Left navbar links -->
 <ul class="navbar-nav">
  <li class="nav-item">
    <button class="btn btn-primary" data-widget="pushmenu"><i class="fas fa-bars"></i></button>
  </li>
  
</ul>

 <!-- SEARCH FORM -->
 <form class="form-inline ml-3">
  <div class="input-group input-group-sm">
    <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
    <div class="input-group-append">
      <button class="btn btn-navbar" type="submit">
        <i class="fas fa-search"></i>
      </button>
    </div>
  </div>
</form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"> {{Session()->get('usuario') ?? ''}}</i>
          <span class="badge badge-danger navbar-badge"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="{{asset("assets/$theme/dist/img/user_default.jpg")}}") alt="User Avatar" class="img-size-50 mr-2 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  <span class="float-right text-sm text-danger"><i></i></span>
                  Hola, {{$iduser ?? ''}}
                </h3>
              
                <i><a href="#" data-toggle="modal" data-target="#modal-xl" class="btn btn-block bg-gradient-info btn-xs btn-flat" >Cambiar Contraseña</a></i>
               
                <td><i><a href="{{route('logout')}}" class="btn btn-block bg-gradient-warning btn-xs btn-flat">Cerrar sesión</a></i></td>
                                          
          </div>
              
            </div>
            <!-- Message End -->
          </a>
        </div>
      </li>
     
     
    </ul>
  </nav>

  <div class="modal fade" tabindex="-1" id ="modal-xl" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog" role="document">
        <div class="modal-content">   
        <div class="row">
            <div class="col-lg-12">
             
               <div class="card card-warning">
                <div class="card-header">
                  <h3 class="card-title">Editar Contraseña del usuario:{{$iduser ?? ''}}</h3>
                  <div class="card-tools pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <form action="" id="form-general-pass" class="form-horizontal" method="POST">
                  @csrf @method('put')
                  <div class="card-body">
                                    @include('includes.form-password')
                  </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6">
                                    @include('includes.boton-form-editar-pass')
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








  <!-- /.navbar -->