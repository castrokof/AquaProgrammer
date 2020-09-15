<div class="form-group row">
    <div class="col-md-3">
            <label for="orden" class="col-xs-4 control-label">Orden Inicial</label>
            <input type="text" name="orden" id="orden" class="form-control col-md-12" value="{{old('orden', $data->ordenesmtl_id ?? '')}}">
        </div>
        <div class="col-md-3">
        <label for="ordenf" class="col-xs-4 control-label">Orden Final</label>
        <input type="text" name="ordenf" id="ordenf" class="form-control col-md-12 " value="{{old('ordenf', $data->ordenesmtl_id ?? '')}}" >
    </div>
    <div class="col-md-3">
        <label for="Usuario" class="col-xs-2 control-label requerido">Usuario</label>
        <select name="Usuario" id="Usuario" class="form-control select2bs4" style="width: 100%;">
            <option value="">---seleccione el usuario---</option>
            @foreach ($usuarios as $id =>$usuario)
            <option value="{{$usuario}}">{{$usuario}}</option>
            @endforeach
        </select>
    </div>      
    <div class="col-md-3">    
        <label>&nbsp;</label>
        <div class="form-group">
        <button value ="asignar" id="asignar" name="asignar" type="button" class="btn btn-success btn-xm col-xs-2">Asignar</button>
        <button value ="desasignar" id= "desasignar" name="desasignar" type="button" class="btn btn-danger btn-xm col-xs-2">Desasignar</button>
        </div>    
    </div>

    
</div>    