@extends("theme.$theme.layout")

@section('titulo')
    Posicionamiento
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>  
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css">

@endsection




@section('contenido')


<div class="container-fluid">
<div class="col-12">
<div class="card  bg-gradient-white ">
        @include('includes.form-error')
        @include('includes.form-mensaje')
        <div class="card card-secondary">
        <div class="card-header with-border">
          <h3 class="card-title">Posicionamiento GPS</h3>
        </div>  
           <form action="" id="form-general" class="form-horizontal" method="GET">
            @csrf
            <div class="card-body">
              
               @include('admin.ordenes.formgps')
            </div>
          </form>
        </div>

          
</div>
 
 
<div class="card card-info">
              <div class="card-header">
                <div class="card-title">
                 POSICIONAMIENTO GPS
                 
                </div>
             </div>
 <div class="card-body">                
                
                
           <div class="col-lg-12 col-md-12 col-xs-12 thumb">  
    
                   <div id="map" class="map map-home" style="margin:12px 0 12px 0;height:400px; width:100%;"></div>
                     
               
            </div>
                
</div>
  
        
   
                
                      
                

</div>
</div>
</div>
</div>




<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>	

<script>




       
        var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        
	    osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                		osm = L.tileLayer(osmUrl, {maxZoom: 20, attribution: osmAttrib});
            var map = L.map('map').setView( [3.125903, -76.5971593], 13).addLayer(osm);
           
            @foreach($datas as $gps)
                    L.marker([{{$gps->Latitud}}, {{$gps->Longitud}}]).addTo(map)
                    .bindPopup('Suscriptor: {{$gps->Suscriptor}} <br> Nombre: {{$gps->Nombre}}<br> Direccion: {{$gps->Direccion}}<br> Lectura: {{$gps->Lect_Actual}} <br>  {{$gps->fecha_de_ejecucion}}')
                	.openPopup();
            @endforeach 
            
           
                  
                    
                  
      
</script>

  

@endsection

@section("scriptsPlugins")

<script>
$(document).ready(function() {

         fill_data();   

          function fill_data(Periodo = '', Ciclo = '')
         {
               $.ajax({
                url:"{{route('posicionamiento')}}",
                data:{Periodo, Ciclo},
                type: 'get'
                
                });
                
         }


      $('#buscar').click(function(){

       var Periodo = $('#Periodo').val();
       var Ciclo = $('#Ciclo').val();

        if(Periodo != '' && Ciclo != ''){

            fill_data(Periodo, Ciclo);

        }else{
        
             swal({
            title: 'Debes digitar periodo y ciclo',
            icon: 'warning',
            buttons:{
                cancel: "Cerrar"
                
                    }
              })
        }
        
    });        

      $('#reset').click(function(){
        $('#Ciclo').val('');
        $('#Periodo').val('');
        fill_data();
      });
});

 

   
</script>
<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>
@endsection