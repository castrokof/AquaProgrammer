<div class="form-group row">
    <div class="col-md-3">
        <label for="periodo" class="col-xs-4 control-label requerido">Periodo</label>
        <input type="text" name="Periodo" id="Periodo" class="form-control col-md-12" value="{{old('Periodo')}}" required>
    </div>
    <div class="col-md-3">
    <label for="zona" class="col-xs-4 control-label requerido">Ciclo</label>
    <input type="text" name="Ciclo" id="Ciclo" class="form-control col-md-12 " value="{{old('Ciclo')}}" required >
    </div>
    <div class="col-md-3">
        <label for="zona" class="col-xs-4 control-label requerido">Ruta</label>
        <input type="text" name="ruta" id="ruta" class="form-control col-md-12 " value="{{old('ruta')}}" required >
    </div>

<div class="col-md-3">    
    <label>&nbsp;</label>
    <div class="form-group row">
        <button type="button" name="reset" id="reset"  class="btn btn-default btn-xl col-md-5">Limpiar</button>
        <button type="button" name="buscar" id="buscar" class="btn btn-success btn-xl col-md-5">Buscar</button>
    </div>    
</div>
</div> 