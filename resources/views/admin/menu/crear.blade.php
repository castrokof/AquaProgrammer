@extends("theme.$theme.layout")
@section('titulo')
 Sistema de Menús
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/menu/crear.js")}}" type="text/javascript"></script>    
@endsection

@section('contenido')
<div class="row">
<div class="col-lg-12">    
  @include('includes.form-error')
  @include('includes.form-mensaje')
<div class="card card-warning">
    <div class="card-header">
      <h3 class="card-title">Crear Menús</h3>
      <div class="card-tools pull-right">
        <a href="{{route('menu')}}" class="btn btn-info btn-sm">Listar menú</a>
        </div>  
    </div>
  <form action="{{route('guardar_menu')}}" id="form-general" class="form-horizontal" method="POST">
    @csrf
    <div class="card-body">
                      @include('admin.menu.form')
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