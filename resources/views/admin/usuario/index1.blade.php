<!DOCTYPE html>
<html style="height: auto;" lang="en">
<head>

 
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
 
  <title>Usuarios PDF</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

</head>
 

<body>
<div class="row">
    <div class="col-lg-12">
      <div class="card card-info">
        <div class="card-header with-border">
          <h3 class="card-title">Usuarios</h3>
          <div class="card-tools pull-right">
            
          </div>
        </div>
        <!-- /.card-header -->
      <div class="card-body table-responsive p-0">
        
      <table id="usuarios" class="table table-striped table-bordered">
        <thead>
        <tr>
              <th class="btn-accion-tabla tooltipsC" title="Editar este registro"><i class="fa fa-fw fa-pencil-alt"></i></th>
              <th class="btn-accion-tabla tooltipsC" title="Editar password"><i class="fas fa-key"></i></th>    
              <th>Id</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Tipo de Usuario</th>
              <th>Email</th>
              <th>Empresa</th>
              <th>Password</th>
              <th>Estado</th>
              <th>Rol</th>
             
        </tr>
        </thead>
        <tbody>
            @foreach ($datas as $data1)
            <tr>
                 <td>
                <a href="{{url("admin/usuario/$data1->id/editar")}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                  <i class="fa fa-fw fa-pencil-alt"></i>
                </a>
                </td>
                <td>
                <a href="{{url("admin/usuario/$data1->id/password")}}" class="btn-accion-tabla tooltipsC" title="Editar password">
                  <i class="fas fa-key"></i>
                </a>
                </td>
                <td>{{$data1->id}}</td>
                <td>{{$data1->usuario}}</td>
                <td>{{$data1->nombre}}</td>
                <td>{{$data1->tipodeusuario}}</td>
                <td>{{$data1->email}}</td>
                <td>{{$data1->empresa}}</td>
                <td>{{$data1->password}}</td>
                <td>{{$data1->estado}}</td>
                <td>
                  @foreach($data1->roles1 as $rol)
                  
                  {{$rol->nombre}}    
                      
                  @endforeach
                  
              </td>
                </tr>
        @endforeach          
        </tbody>
      </table>
    </div>
  
  
</div>
</div>
</div>
<div class="container-fluid">
  <div class="col-12">
  <div class="card  bg-gradient-white ">
                <div class="card-header bg-gray disabled color-palette bg-gradient-dark">
                  <div class="card-title">
                  
                   <h4 class="text-center">ORDEN DE CRITICA </h4>
                   
                  </div>
               </div>
  <div class="card-body">
      
  <div class="row">
  
      
  <div class="col-md-8">
   
  <div class="card card-outline card-secondary">
                <div class="card-header">
                  <div class="card-title">
                   DATOS GENERALES
                  </div>
               </div>
   <div class="card-body ">                
                  
          <div class="row">
              
             <div class="col-lg-6 col-md-6 col-xs-6 thumb"> 
                      <p class="lead-center"><h5>SUSCRIPTOR:</h5></p>
                     <div class="table-responsive">
                      <table class="table table-striped table-hover">
                        <tbody>
                        <tr>
                          <th style="width:20%">Nombre:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Direccion:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Recorrido:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Medidor:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Zona:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Funcionario:</th>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Fecha ejecución:</th>
                          <td></td>
                        </tr>
                        </tbody></table>
                    </div>
                  </div>
               
                  </div>
                </div>    
               </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </div>
        
     

 
  
</body>
</html>


