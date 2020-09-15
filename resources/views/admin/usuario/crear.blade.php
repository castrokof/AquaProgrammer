@extends("theme.$theme.layout")
@section('titulo')
 Sistema de Usuarios
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/usuario/crear.js")}}" type="text/javascript"></script>    
@endsection

@section('contenido')
<div class="row">
<div class="col-lg-12">    
  @include('includes.form-error')
  @include('includes.form-mensaje')
<div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">Crear Usuario</h3>
      <div class="card-tools pull-right">
        <a href="{{route('rol')}}" type="button" class="btn btn-block btn-info btn-sm">
              <i class="fa fa-fw fa-reply-all"></i> Volver al listado
        </a>
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
                      @include('includes.boton-form-crear')
                  </div>
                   </div>
                  <!-- /.card-footer -->
                </form>
             

    
  </div>
</div>
</div>
@endsection