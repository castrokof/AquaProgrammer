@extends("theme.$theme.layout")
@section('titulo')
  Permisos  
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/permiso/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/permiso/crear.js")}}" type="text/javascript"></script>    
@endsection


@section('contenido')
<div class="row">
<div class="col-lg-12">
  @include('includes.form-error')
  @include('includes.form-mensaje')    
<div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Permisos</h3>
      <div class="card-tools pull-right">
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-lg"><i class="fa fa-fw fa-plus-circle"></i> Nuevo permiso</button>
        </button>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
      <table id="tabla-data" class="table table-hover table-bordered text-nowrap">
            <thead>
                <tr>
                    <th class="width70">Id</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th class="width80">Acciones</th>

                </tr>
            </thead>
            <tbody>
                    @foreach ($datas as $permiso)
                        <tr>
                            <td>{{$permiso->id}}</td>
                            <td>{{$permiso->nombre}}</td>
                            <td>{{$permiso->slug}}</td>
                            <td>
                              <a href="{{url("admin/permiso/$permiso->id/editar")}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                <i class="fa fa-fw fa-pencil-alt"></i>
                              </a>
                              <form action="{{route("eliminar_permiso", ['id' => $permiso->id])}}" class="d-inline form-eliminar" method="POST">
                                @csrf @method("delete")
                                <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar registro">
                                  <i class="fa fa-fw fa-trash text-danger"></i>
                                </button>
                              </form>  
                            </td>
                        </tr>
                    @endforeach                
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
            <h3 class="card-title">Crear Permisos</h3>
            <div class="card-tools pull-right">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
          </div>
        <form action="{{route('guardar_permiso')}}" id="form-general" class="form-horizontal" method="POST">
          @csrf
          <div class="card-body">
                            @include('admin.permiso.form')
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