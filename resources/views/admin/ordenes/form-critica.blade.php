<div class="form-group row">
    <div class="col-md-2">
        <label for="periodo" class="col-xs-4 control-label requerido">Periodo</label>
        <input type="text" name="Periodo" id="Periodo" class="form-control col-md-12" value="" required>
    </div>
    <div class="col-md-2">
    <label for="zona" class="col-xs-4 control-label requerido">Ciclo</label>
    <input type="text" name="Ciclo" id="Ciclo" class="form-control col-md-12 " value="" required >
    </div>
  <div class="col-md-2">
        <label for="ruta" class="col-xs-4 control-label requerido">Ruta</label>
        <select name="ruta" id="ruta" class="form-control select2bs4" style="width: 100%;" required>
            {{-- <option value="">---seleccione la critica---</option>
            @foreach ($idDivisions as $id =>$idDivision)
            <option value="{{$idDivision}}">{{$idDivision}}</option>
            @endforeach --}}
        </select>
    </div>  
  <div class="col-md-3">
        <label for="estado" class="col-xs-4 control-label requerido">Critica</label>
        <select name="Critica" id="Critica" class="form-control select2bs4" style="width: 100%;" required>
            <option value="">---seleccione---</option>
            <option value="ALTO CONSUMO">ALTO CONSUMO</option>
            <option value="BAJO CONSUMO">BAJO CONSUMO</option>
            <option value="CAUSADO">CAUSADO</option>
            <option value="CONSUMO CERO">CONSUMO CERO</option>
            <option value="NEGATIVO">NEGATIVO</option>
            <option value="ALL">TODOS</option>
        </select>
</div>
<div class="col-md-3">    
    <label for="Generado" class="col-xs-4 control-label requerido">Generado</label>
    <select name="Generado" id="Generado" class="form-control select2bs4" style="width: 100%;" required>
        <option value="">---seleccione---</option>
        <option value="generar">SI</option>
    </select>
    
</div>

</div> 
<div class="form-group row">
    <div class="col-md-2">
    </div>
    <div class="col-md-2">
    </div>
    <div class="col-md-4">
    </div>
    <div class="col-md-4">
        <label>&nbsp;</label>
    <div class="form-group row">
        <button type="button" name="reset" id="reset"  class="btn btn-default btn-xl col-md-3">Limpiar</button>
        <button type="button" name="buscar" id="buscar" class="btn btn-success btn-xl col-md-3">Buscar</button>
        <button type="button" name="adicionar" id="adicionar" title="Crear Orden" class="btn btn-warning btn-xl col-md-3 tooltipsC">+Add</button>
        <button type="button" name="eliminar" id="eliminar" title="Eliminar Orden" class="btn btn-danger btn-xl col-md-3 tooltipsC">-Add</button>
                  
    </div>    
    </div>

</div> 