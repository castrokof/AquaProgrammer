<!DOCTYPE html>
<html style="height: auto;" lang="en">
<head>
 

  <title>Pdf Critica</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  html {
    margin: 0;
  }
  body {
    font-family: "Times New Roman", serif;
    margin: 8mm 8mm 2mm 8mm;
  }

  table, th, td {
  
  border: 1px solid black;
  
}

</style>


</head>
<body>
<div class="container">                
        @foreach ($datas as $data1)       
                 <p class="lead-center"><h6>ORDEN DE CRITICA POR: || {{($data1->Critica)}} || --- ACUASUR ---</h6></p>
                 -------------------------------------------------------------------------------------------------------------------------------------------
                 <p class="lead-center"><h6>SUSCRIPTOR: {{($data1->Suscriptor)}} -- NOMBRE: {{($data1->Nombre)}} -- DIR: {{($data1->Direccion)}} -- REC: {{($data1->recorrido)}} -- CICLO:{{$data1->Ciclo}}</h6></p>
                 
                   <div class="table-responsive">
                    <table style="width:100%" class="table table-hover table-striped">
                      <thead>
                        <tr style="width:100%"> <th colspan="6">--Datos de la Lectura--</th></tr> 
                       <tr style="width:100%">
                        <td>Medidor:{{$data1->Ref_Medidor}}</td>
                        <td>Lec_lector:{{$data1->Lect_Actual}}</td>
                        <td>Pro:{{$data1->Promedio}}</td>
                        <td>Consumo:{{$data1->Cons_Act}}</td>
                        <td colspan="2">Lec_Revisor:</td>
                           
                      </tr>
                     </thead> 
                      <tr style="width:100%"> 
                        <td colspan="2">Que surte:</td>
                        <td colspan="2">Estado de instalación:</td>
                        <td colspan="2">Estado de acometida:</td>
                      </tr> 
                      <tr style="width:100%"> <th colspan="6">--Censo de Población--</th></tr>
                      <tr style="width:100%">
                        <td colspan="4">Persona que atiende:</td> 
                        <td colspan="1">#Flias:</td> 
                        <td colspan="1">#Personas:</td>
                      </tr>
                      <tr style="width:100%"> <th colspan="6">--Censo hidraulico--</th></tr>
                      <tr style="width:100%">
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                      </tr>
                      <tr style="width:100%">  
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                        <td>Elemento:</td> 
                        <td>Cantidad:</td>
                     
                      </tr>
                      <tr style="width:100%"> <th colspan="2">Prueba de medidor:</th><th colspan="4"></th></tr>
                      <tr style="width:100%"> <th colspan="6">--Observación general--</th></tr>
                      <tr style="width:100%"> <th rowspan="20" colspan="6"></th></tr>
                      <tbody>
                                                                         
                    </tbody>
                    </table>
                    -------------------------------------------------------------------------------------------------------------------------------------------
                  </div>
              
             
               @endforeach    
             </div>     
<script>

// jQuery(document).ready(function() {

//   $.ajax({
//            success:function(data){  

//              $('tbody').html(data.table_data);   
                  
//            }
              
                 
//         });
          
// });

</script>

</body>
</html>