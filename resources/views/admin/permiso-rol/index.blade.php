@extends("theme.$theme.layout")

@section('titulo')
Menú - Rol
@endsection

@section('scripts')
<script src="{{asset("assets/pages/scripts/admin/permiso-rol/index.js")}}" type="text/javascript"></script>
@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
         @include('includes.form-mensaje')
    <div class="card card-success">
        <div class="card-header with-border">
          <h3 class="card-title">Permiso - Rol</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0">
         @csrf   
      <table id="tabla-data" class="table table-hover table-bordered text-nowrap">
        <thead>
        <tr>
              <th>Menú</th>
              @foreach ($rols as $id => $nombre)
              <th style="text-align: center" >{{$nombre}}</th>
              @endforeach
        </tr>
        </thead>
        <tbody>
            @foreach ($permiso as $key => $permiso)
                @if ($permiso["permiso_id"]!=0)
                    @break
                @endif
                    <tr>
                        <td class="font-weight-bold"><i class="fa fa-arrows-alt"></i> {{$permiso["nombre"]}}</td>
                        @foreach ($rols as $id =>$nombre)
                        <td class="text-center">
                            <input
                            type="checkbox"
                            class="permiso_rol"
                            name="permiso_rol[]"
                            data-permisoid={{$permiso["id"]}}
                        value="{{$id}}" {{in_array($id, array_column($permisoRols[$permiso["id"]], "id"))? "checked" : "" }}>
                        </td>   
                        @endforeach
                    </tr>
            @endforeach              
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
</div>
</div>
</div>

  
@endsection


