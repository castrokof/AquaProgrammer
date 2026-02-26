<div class="form-group row">
    <div class="col-md-3">
        <label for="periodo" class="col-xs-4 control-label requerido">Periodo</label>
        <input type="text" name="Periodo" id="Periodo" class="form-control col-md-12" value="{{old('Periodo')}}" required>
    </div>
    <div class="col-md-3">
    <label for="zona" class="col-xs-4 control-label requerido">Ciclo</label>
    <input type="text" name="Ciclo" id="Ciclo" class="form-control col-md-12 " value="{{old('Ciclo')}}" required >
    </div>
     <div class="col-md-2">
        <label for="ruta" class="col-xs-4 control-label requerido">Ruta</label>
        <select name="ruta" id="ruta" class="form-control select2bs4" style="width: 100%;" required>
           
        </select>
    </div>  

<div class="col-md-3">    
    <label>&nbsp;</label>
     <div class="col-12 ">
                        <button type="button" id="reset" class="btn btn-reset">
                            <i class="fas fa-undo"></i> Limpiar
                        </button>
                        <button type="button" id="buscar" class="btn btn-search">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>    
</div>
</div> 