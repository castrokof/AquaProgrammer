<!DOCTYPE html>
<html lang="en">
<head>
  <title>Img Acuasur rural</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">

  
</head>


<body>
<div class="contenedor">
<h1 class="text-center"><a href=#></a></h1>	

 
  @foreach($datas as $img)
    @if(!empty($img->foto1) || !empty($img->foto2) || !empty($img->foto3) || !empty($img->foto4) || !empty($img->foto5) || !empty($img->foto6) || !empty($img->foto7) || !empty($img->foto8))
    
   @if(!empty($img->foto1))
   <img src="{{asset('/tmp/'.$img->foto1)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto2))
   <img src="{{asset('/tmp/'.$img->foto2)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto3))
   <img src="{{asset('/tmp/'.$img->foto3)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto4))
   <img src="{{asset('/tmp/'.$img->foto4)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto5))
   <img src="{{asset('/tmp/'.$img->foto5)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto6))
   <img src="{{asset('/tmp/'.$img->foto6)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   
   @if(!empty($img->foto7))
   <img src="{{asset('/tmp/'.$img->foto7)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   <br>
   @endif
   
   @if(!empty($img->foto8))
   <img src="{{asset('/tmp/'.$img->foto8)}}" alt="" class="img-rounded" alt="" width="640" height="480">
   @endif
    

  
   
   
     
    @endif    
@endforeach

 </div>
</body>
</html>