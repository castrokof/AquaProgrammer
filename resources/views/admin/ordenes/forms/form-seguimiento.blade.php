<div class="form-group row">
                    <div class="col-md-3">
                        <label for="usuario" class="control-label">Usuario</label>
                        <select name="usuario" id="usuario" class="form-control select2bs4">
                            <option value="">Seleccione el usuario</option>
                            @foreach ($usuarios as $usuario => $nombre)
                            <option value="{{$usuario}}">{{$nombre." => ".$usuario}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="fechadegestion" class="control-label">Fecha de Gestión</label>
                        <div class="form-group row">
                            <input type="date" name="fechaini" id="fechaini" class="form-control col-md-6" placeholder="Desde">
                            <input type="date" name="fechafin" id="fechafin" class="form-control col-md-6" placeholder="Hasta">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="estado" class="control-label">Estado</label>
                        <select name="estado" id="estado" class="form-control select2bs4">
                            <option value="">Seleccione estado</option>
                            <option value="CARGADO">CARGADO</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="EJECUTADO">EJECUTADO</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="critica" class="control-label">Crítica</label>
                        <select name="critica" id="critica" class="form-control select2bs4">
                            <option value="">Seleccione la crítica</option>
                            @foreach ($criticas as $id => $Critica)
                            <option value="{{$Critica}}">{{$Critica}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <div class="col-md-3">
                        <label for="periodo" class="control-label">Periodo</label>
                        <input type="text" name="periodo" id="periodo" class="form-control" placeholder="Ej: 202510">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="zona" class="control-label">Ciclo</label>
                        <input type="text" name="zona" id="zona" class="form-control" placeholder="Ej: 1">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="suscriptor" class="control-label">Suscriptor</label>
                        <input type="text" name="suscriptor" id="suscriptor" class="form-control" placeholder="Código suscriptor">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="medidor" class="control-label">Medidor</label>
                        <input type="text" name="medidor" id="medidor" class="form-control" placeholder="Número medidor">
                    </div>
                </div>
                
                <div class="form-group row justify-content-end">
                    <div class="col-md-3">
                        <button type="button" name="reset" id="reset" class="btn btn-modern btn-default btn-block">
                            🔄 Limpiar
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" name="buscar" id="buscar" class="btn btn-modern btn-success btn-block">
                            🔍 Buscar
                        </button>
                    </div>