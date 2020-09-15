<div class="form-group row">
    <div class="col-lg-3">
        <label for="usuario" class="col-xs-4 control-label requerido">Usuario</label>
        <input disabled  type="text" name="usuario" id="usuario" class="form-control" value="{{old('usuario', $data->usuario ?? '')}}" required >
    </div>
    <div class="col-lg-3">
    <label for="nombre" class="col-xs-4 control-label requerido">Nombre</label>
    <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre', $data->nombre ?? '')}}" required >
    </div>
    <div class="col-lg-3">
                    <label for="tipodeusuario" class="col-xs-4 control-label requerido">tipo de usuario</label>
                    <select name="tipodeusuario" id="tipodeusuario" class="form-control select2bs4" style="width: 100%;">
                        <option value="">---seleccione el estado---</option>
                        <option value="administrativo" {{($data->tipodeusuario == 'administrativo') ? 'selected' : ''}}>administrativo</option>
                        <option value="movil" {{($data->tipodeusuario == 'movil') ? 'selected' : ''}}>movil</option>
                        </select>
    </div>
    <div class="col-lg-3">
        <label for="email" class="col-xs-4 control-label requerido">E-mail</label>
        <input type="email" name="email" id="email" class="form-control" value="{{old('email', $data->email ?? '')}}" required >
    </div>
</div>
<div class="form-group row">
    <div class="col-lg-3">
        <label for="empresa" class="col-xs-4 control-label requerido">Empresa</label>
        <input type="text" name="empresa" id="empresa" class="form-control" value="{{old('empresa', $data->empresa ?? '')}}" required >
    </div>
    
    <div class="col-lg-3">
        <label for="estado" class="col-xs-4 control-label requerido">Estado</label>
                    <select name="estado" id="estado" class="form-control select2bs4" style="width: 100%;">
                    <option value="">---seleccione el estado---</option>
                    <option value="activo" {{($data->estado === "activo") ?  "selected" : ""}}>activo</option>
                    <option value="inactivo" {{($data->estado === "inactivo") ?  "selected" : ""}}>inactivo</option>
                    </select>
        </div>
    <div class="col-lg-3">
        <label for="rol_id" class="col-xs-4 control-label requerido">Rol</label>
                        <select name="rol_id" id="rol_id" class="form-control select2bs4" style="width: 100%;">
                        <option value="">---seleccione el rol---</option>
                        @foreach ($Rols1 as $id => $nombre)
                        <option value="{{$id}}" {{old('rol_id', $data->roles1[0]->id ?? "") == $id ? "selected" : ""}}>{{$nombre}}</option>
                        @endforeach
                        </select>
    </div>   


   

</div>
