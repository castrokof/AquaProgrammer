<!-- Modal para editar lectura -->
<div class="modal fade modal-modern" id="modalEditLectura" tabindex="-1" role="dialog" aria-labelledby="modalEditLecturaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLecturaLabel">
                    <i class="fas fa-edit"></i>
                    Editar Lectura Actual
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditLectura">
                <div class="modal-body">
                    <input type="hidden" id="orden_id" name="orden_id">
                    
                    <!-- Foto del medidor -->
                    <div class="foto-container">
                        <div class="foto-loading" id="foto-loading">
                            <i class="fas fa-spinner fa-spin"></i> Cargando foto...
                        </div>
                        <img src="" alt="Foto del medidor" class="foto-preview" id="foto-medidor" style="display: none;">
                        <div class="foto-error" id="foto-error" style="display: none;">
                            <i class="fas fa-image"></i>
                            <span>Foto no disponible</span>
                        </div>
                        <span class="foto-badge">
                            <i class="fas fa-camera"></i> Foto del Medidor
                        </span>
                        <div class="foto-zoom-icon">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    
                    <!-- Divisor visual -->
                    <div class="form-divider"></div>
                    
                    <!-- Información del suscriptor -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Suscriptor
                            <span class="info-badge">Solo lectura</span>
                        </label>
                        <input type="text" class="form-control" id="suscriptor_display" readonly>
                    </div>
                    
                    <!-- Lectura anterior -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-history"></i>
                            Lectura Actual
                            <span class="info-badge">Actual</span>
                        </label>
                        <input type="text" class="form-control" id="lectura_anterior" readonly>
                    </div>
                    
                    <!-- Nueva lectura -->
                    <div class="form-group">
                        <label class="requerido">
                            <i class="fas fa-tachometer-alt"></i>
                            Nueva Lectura
                            <i class="fas fa-info-circle help-icon" title="Ingrese el nuevo valor de la lectura del medidor"></i>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="lectura_nueva" 
                               name="lectura_nueva" 
                               placeholder="Ingrese la nueva lectura"
                               step="0.01"
                               required>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-lightbulb"></i> La lectura anterior se guardará automáticamente en el historial
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>