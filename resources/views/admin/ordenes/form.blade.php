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
        <label for="estado" class="col-xs-4 control-label requerido">Estado</label>
        <select name="Estado" id="Estado" class="form-control select2bs4" style="width: 100%;" required>
            <option value="">---seleccione---</option>
            <option value="CARGADO">CARGADO</option>
            <option value="PENDIENTE">PENDIENTE</option>
        </select>
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
    <label>&nbsp;</label>
    <div class="form-group row">
        <button type="button" name="reset" id="reset"  class="btn btn-default btn-xl col-md-5">Limpiar</button>
        <button type="button" name="buscar" id="buscar" class="btn btn-success btn-xl col-md-5">Buscar</button>
    </div>    
</div>
</div> 