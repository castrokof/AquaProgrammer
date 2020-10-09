@extends("theme.$theme.layout")

@section('titulo')
    Motivos
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/rol/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/rol/crear.js")}}" type="text/javascript"></script>
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       
@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.form-mensaje')
    <div class="card card-info">
        <div class="card-header with-border">
          <h3 class="card-title">Motivos de no lectura</h3>
          <div class="card-tools pull-right">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-lg"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Motivo</button>
            </button>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0">
      <table id="tabla-data" class="table table-head-fixed text-nowrap table-hover table-bordered">
        <thead>
        <tr>
              <th class="width70">Codigo</th>
              <th>Nombre</th>
              <th>Descripcion</th>
              <th class="width80">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($datas as $data)
            <tr>
                <td>{{$data->marca_id}}</td>
                <td>{{$data->codigo}}</td>
                <td>{{$data->descripcion}}</td>
                <td>
                <a href="{{url("admin/marca/$data->id/editar")}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                  <i class="fa fa-fw fa-pencil-alt"></i>
                </a>
                </td>
            </tr>
        @endforeach          
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
</div>
</div>
</div>

    <div class="modal fade" tabindex="-1" id ="modal-lg" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">   
        <div class="row">
            <div class="col-lg-12">    
               <div class="card card-warning">
                <div class="card-header">
                  <h3 class="card-title">Crear Motivo</h3>
                  <div class="card-tools pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
              <form action="{{route('guardar_marca')}}" id="form-general" class="form-horizontal" method="POST">
                @csrf
                <div class="card-body">
                                  @include('admin.marcas.form')
                              </div>
                              <!-- /.card-body -->
                              <div class="card-footer">
                                
                                  <div class="col-lg-3"></div>
                                  <div class="col-lg-6">
                                  @include('includes.boton-form-crear-marcas')
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

<script>
  $(document).ready(function() {

        $('#tabla-data').DataTable({
              processing: true,
              
              
            });
});
  
         
  </script>
  


@endsection
