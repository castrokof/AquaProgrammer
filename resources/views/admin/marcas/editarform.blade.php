<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Codigo</label>
    <div class="col-lg-8">
    <input type="text" name="marca_id" id="marca_id" class="form-control" value="{{old('marca_id', $data->marca_id ?? '')}}" required >
    </div>
</div>
<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Nombre</label>
    <div class="col-lg-8">
    <input type="text" name="codigo" id="codigo" class="form-control" value="{{old('codigo', $data->codigo ?? '')}}" required >
    </div>
</div>
<div class="form-group row">
    <label for="nombre" class="col-lg-3 control-label requerido">Descripción</label>
    <div class="col-lg-8">
    <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{old('descripcion', $data->descripcion ?? '')}}" required >
    </div>
</div>
