@extends("theme.$theme.layout")

@section('titulo')
    Detalle de Orden
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>       

@endsection


@section('scripts')

@endsection

@section('contenido')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.form-mensaje')
        <div class="card card-info">
        <div class="card-header with-border">
          <h3 class="card-title">Detalle de Orden</h3>
        </div>  
          {{-- <form action="{{route('tablero')}}" id="form-general" class="form-horizontal" method="GET">
            @csrf --}}
            <div class="card-body">
              @csrf
               @include('admin.detalleorden.form')
            </div>
          {{-- </form> --}}
        </div>

          
    </div>
  

        <!-- /.card-header -->
<div class="card-body table-responsive p-0" style="height: 500px;">
      <table id="detalle"  class="table table-head-fixed text-nowrap table-hover table-bordered">
        <thead>
        <tr> 
              <th>Zona</th>              
              <th>Periodo</th>
              <th>Usuario</th>
              <th>Lote</th>
              <th>Asignados</th>
              <th>Pendientes</th>
              <th>Ejecutadas</th>
              <th>Oposición</th>
              <th>MTL</th>
              <th>MTL TIPO C</th>
              <th>Fecha Inicio</th>
              <th>Fecha Final</th>
        </tr>
        </thead>
        {{-- <tbody>
            @foreach ($datas as $data)
            <tr>
                <td>{{$data->zona}}</td>
                <td>{{$data->periodo}}</td>
                <td>{{$data->usuario}}</td>
                <td>{{$data->lote}}</td>
                <td>{{$data->Asignados}}</td>
                <td>{{$data->Pendientes}}</td>
                <td>{{$data->Ejecutadas}}</td>
                <td>{{$data->oposicion}}</td>
                <td>{{$data->MTL}}</td>
                <td>{{$data->MTL_TIPO_C}}</td>
                <td>{{$data->inicio}}</td>
                <td>{{$data->Final}}</td>
               
            </tr>
        @endforeach          
        </tbody> --}}
      </table>
         
    </div>
  
    <!-- /.card-body -->
</div>
</div>
@endsection

@section("scriptsPlugins")
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>

<script>
  $(document).ready(function() {

         fill_datatable();   

          function fill_datatable(periodo1 = '', zona1 = '' )
         {
          var datatable = $('#detalle').DataTable({
              processing: true,
              serverSide: true,
              ajax:{
                url:"{{ route('detalle_de_ordenes')}}",
                data:{periodo1:periodo1, zona1:zona1}
              },
              columns: [
                {
                    data:'zona',
                    name:'zona'
                },
                {
                    data:'periodo',
                    name:'periodo'
                },
                {
                    data:'usuario',
                    name:'usuario'
                },
                {
                    data:'lote',
                    name:'lote'
                },
                {
                    data:'Asignados',
                    name:'Asignados'
                },
                {
                    data:'Pendientes',
                    name:'Pendientes'
                },
                {
                    data:'Ejecutadas',
                    name:'Ejecutadas'
                },
                {
                    data:'oposicion',
                    name:'oposicion'
                },
                {
                    data:'MTL',
                    name:'MTL'
                },
                {
                    data:'MTL_TIPO_C',
                    name:'MTL_TIPO_C'
                },
                {
                    data:'inicio',
                    name:'inicio'
                },
                {
                    data:'Final',
                    name:'Final'
                }

              ]
            });
     }    
        
      $('#buscar').click(function(){

       var periodo1 = $('#periodo1').val();
       var zona1 = $('#zona1').val();

        if(periodo1 != '' && zona1 != ''){

            $('#detalle').DataTable().destroy();
            fill_datatable(periodo1, zona1);

        }else{
        
             alert('Debes digitar el periodo y zona')
        }
    });        

      $('#reset').click(function(){
        $('#zona1').val('');
        $('#periodo1').val('');
        $('#detalle').DataTable().destroy();
        fill_datatable();
      });
});
  
         
  </script>
  


@endsection
