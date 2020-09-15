@extends("theme.$theme.layout")
@section('titulo')
 Editar Roles
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/rol/crear.js")}}" type="text/javascript"></script>    
@endsection

@section('contenido')
<div class="row">
<div class="col-lg-12">    
  @include('includes.form-error')
  @include('includes.form-mensaje')
<div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">Editar Roles</h3>
      <div class="card-tools pull-right">
        <a href="{{route('rol')}}" type="button" class="btn btn-block btn-info btn-sm">
              <i class="fa fa-fw fa-reply-all"></i> Volver al listado
        </a>
        </div>
    </div>
  <form action="{{route('actualizar_rol', ['id' => $data->id])}}" id="form-general" class="form-horizontal" method="POST">
    @csrf @method('put')
    <div class="card-body">
                      @include('admin.rol.form')
                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer">
                      <div class="col-lg-3"></div>
                      <div class="col-lg-6">
                      @include('includes.boton-form-editar')
                  </div>
                   </div>
                  <!-- /.card-footer -->
                </form>
             

    
  </div>
</div>
</div>
@endsection