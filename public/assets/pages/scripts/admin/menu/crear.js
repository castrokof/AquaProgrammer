$(document).ready(function(){
    Manteliviano.validacionGeneral('form-general');
    $('#icono').on('blur', function(){
        $('#mostrar-icono').removeClass().addClass($(this).val());
    });

});