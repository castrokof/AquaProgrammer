<div class="form-group row">
    <div class="col-md-3">
        <label for="usuario" class="col-xs-2 control-label requerido">Usuario</label>
        <select name="usuario" id="usuario" class="form-control select2bs4" style="width: 100%;">
            <option value="">---seleccione el usuario---</option>
            @foreach ($usuarios as $usuario => $nombre))
            <option value="{{$usuario}}">{{$nombre."=>".$usuario}}</option>
            @endforeach
        </select>
    </div> 
  <div class="col-md-3">
  <label for="fechadegestion" class="col-xs-2 control-label requerido">Fecha de gestión</label>
  <div class="form-group row">
        <input type="date" name="fechaini" id="fechaini"  class="form-control col-md-6 " value="">
        <input type="date" name="fechafin" id="fechafin" class="form-control col-md-6 " value="">
  </div>
  </div>    
  <div class="col-md-3">
        <label for="estado" class="col-xs-4 control-label requerido">Estado</label>
        <select name="estado" id="estado" class="form-control select2bs4" style="width: 100%;">
            <option value="">---seleccione---</option>
            <option value="CARGADO">CARGADO</option>
            <option value="PENDIENTE">PENDIENTE</option>
            <option value="EJECUTADO">EJECUTADO</option>
        </select>
</div>
</div>

<div class="form-group row">
    <div class="col-md-3">
        <label for="periodo" class="col-xs-4 control-label requerido">Periodo</label>
        <input type="text" name="periodo" id="periodo" class="form-control col-md-12" value="">
    </div>
    <div class="col-md-3">
    <label for="zona" class="col-xs-4 control-label requerido">Ciclo</label>
    <input type="text" name="zona" id="zona" class="form-control col-md-12 " value="" >
    </div>
  <div class="col-md-3">
        <label for="critica" class="col-xs-4 control-label requerido">Critica</label>
        <select name="critica" id="critica" class="form-control select2bs4" style="width: 100%;">
            <option value="">---seleccione la critica---</option>
            @foreach ($criticas as $id =>$Critica)
            <option value="{{$Critica}}">{{$Critica}}</option>
            @endforeach
        </select>
</div>
    
</div>    
<div class="form-group row">
    <div class="col-md-3">
        <label for="suscriptor" class="col-xs-4 control-label requerido">suscriptor</label>
        <input type="text" name="suscriptor" id="suscriptor" class="form-control col-md-12" value="">
    </div>
    <div class="col-md-3">
    <label for="medidor" class="col-xs-4 control-label requerido">Medidor</label>
    <input type="text" name="medidor" id="medidor" class="form-control col-md-12 " value="" >
    </div>
    <div class="col-md-4">    
        <label>&nbsp;</label>
        <div class="form-group row">
            <button type="button" name="reset" id="reset"  class="btn btn-default btn-xl col-md-4">Limpiar</button>
            <button type="button" name="buscar" id="buscar" class="btn btn-success btn-xl col-md-4">Buscar</button>
           
        </div>    
    </div>
</div>