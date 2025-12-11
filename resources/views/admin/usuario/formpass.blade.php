 <div class="modal fade" tabindex="-1" id="modal-xlpass" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Editar Contraseña</h3>

                                <div class="card-tools pull-right">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                            <form action="" id="form-general-pass" class="form-horizontal" method="POST">
                                @csrf @method('put')
                                <div class="card-body">
                                    <div class="form-group row col-lg-12">
                                        <div class="col-lg-6">
                                            <label for="Usuario" class="col-xs-12 control-label requerido">Usuario</label>
                                            <input type="Usuario" name="usuario_id1" id="usuario_id1" class="form-control"
                                                value="" minlength="6" required readonly>
                                        </div>

                                    </div>
                                    <div class="form-group row col-lg-12">
                                        <div class="col-lg-6">
                                            <label for="password" class="col-xs-12 control-label requerido">Password</label>
                                            <input type="password" name="passwordu" id="passwordu" class="form-control"
                                                value="" minlength="6" required>
                                        </div>
                                        <div class="col-lg-6">
                                            <label for="remenber_token" class="col-xs-12 control-label requerido">repita el
                                                password</label>
                                            <input type="password" name="remenber_tokenu" id="remenber_tokenu" class="form-control"
                                                value="" minlength="6" required>
                                        </div>

                                        <input type="hidden" name="idpassu" id="idpassu" class="form-control"
                                            value="" required>
                                    </div>


                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6">
                                        <button type="button" id="actualizarpass" name="actualizar"
                                            class="btn btn-success">Actualizar</button>
                                    </div>
                                </div>
                                <!-- /.card-footer -->
                            </form>



                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>