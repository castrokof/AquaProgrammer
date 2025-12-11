<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Grupo</label>
    <div class="col-lg-8">
        <label for="marca_id" class="col-xs-4 control-label requerido">Grupo:</label>
            <select id="marca_id" name="marca_id" class="form-control select2bs4" required>
                <option value="">---seleccione---</option>
                <option value="CAUSAS">CAUSAS</option>
                <option value="CRITICA">CRITICA</option>
                <option value="OBSERVACIONES">OBSERVACIONES</option>
                
            </select>
    
    </div>
</div>
<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Codigo</label>
    <div class="col-lg-8">
    <input type="text" name="codigo" id="codigo" class="form-control" value="{{old('codigo' ?? '')}}" required >
    </div>
</div>
<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Nombre</label>
    <div class="col-lg-8">
    <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{old('descripcion' ?? '')}}" required >
    </div>
</div>
