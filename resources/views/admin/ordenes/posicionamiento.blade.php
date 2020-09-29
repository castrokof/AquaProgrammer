@extends("theme.$theme.layout")

@section('titulo')
    Posicionamiento
@endsection

@section("styles")
<link href="{{asset("assets/$theme/plugins/datatables-bs4/css/dataTables.bootstrap4.css")}}" rel="stylesheet" type="text/css"/>  
<link href="{{asset("assets/css/Control.FullScreen.css")}}" rel="stylesheet"/>
<link  href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" rel="stylesheet"/>


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
<script src="{{asset("assets/js/Control.FullScreen.js")}}" type="text/javascript"></script>

<script>
  var base = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 20,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
		});
		
		var map = L.map('map', {
			layers: [base],
			center: new L.LatLng(3.125903, -76.5971593),
			zoom: 12,
			fullscreenControl: true,
			fullscreenControlOptions: { // optional
				title:"Show me the fullscreen !",
				titleCancel:"Exit fullscreen mode"
			}
		});

   

    map.on('enterFullscreen', function(){
			if(window.console) window.console.log('enterFullscreen');
		});
		map.on('exitFullscreen', function(){
			if(window.console) window.console.log('exitFullscreen');
		});


// var zoom = 12;

// var map = L.map('map').setView([3.125903, -76.5971593], zoom);

// L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//   attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
// }).addTo(map);
// detect fullscreen toggling


</script>


@endsection


@section("scriptsPlugins")


<script>

$(document).ready(function() {
 
    var marker;
    
		var LeafIcon = L.Icon.extend({
		options: {
			iconSize:     [50, 50],
			iconAnchor:   [15, 30],
			popupAnchor:  [0, -30]
			}
		});
    
    
    var icon = new LeafIcon({iconUrl: "http://127.0.0.1:8000/assets/img/icon.png"})
    

    fill_data();   

       
function fill_data(Periodo = '', Ciclo = '', ruta = '')
{
$.get('posicionamiento',{Periodo:Periodo, Ciclo:Ciclo, ruta:ruta},function(markers1)
  { 
    $.each(markers1,function(index, datos){
      
      marker = new L.marker([datos.Latitud,datos.Longitud])
          .bindPopup("Suscriptor:"+datos.Suscriptor+'<br>'+"Nombre:"+datos.Nombre+'<br>'+"Direccion:"+datos.Direccion)
          .openPopup()
          .addTo(map);

    });
   
  });
  
}

$('#buscar').click(function(){

var Periodo = $('#Periodo').val();
var Ciclo = $('#Ciclo').val();
var ruta = $('#ruta').val();

if(Periodo != '' && Ciclo != '' || ruta != ''){
   
  
  fill_data(Periodo, Ciclo, ruta);

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
$('#ruta').val('');
fill_data();
location.reload('#map');
});

$('#Ciclo').on('change', function() {

var P = $('#Periodo').val();
var C = $('#Ciclo').val();

if(P != '' && C != ''){
$.get('idDivision',{P:P, C:C}, function(idDivisions)
  {   
      $('#ruta').empty();
      $('#ruta').append("<option value=''>---seleccione la ruta---</option>")
      $.each(idDivisions, function(idDiv, value){
      $('#ruta').append("<option value='" + value + "'>" + value + "</option>")
      });
  }); 

}
}); 



});

 

   
</script>

<script src="{{asset("assets/$theme/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/$theme/plugins/datatables-bs4/js/dataTables.bootstrap4.js")}}" type="text/javascript"></script>
@endsection