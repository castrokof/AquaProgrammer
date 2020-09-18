$(document).ready(function(){
         const reglas ={
        remenber_token:{
            equalTo: "#password"
        }
    };
    const mensajes ={
        remenber_token:{
            equalTo: 'las contraseñas no coinciden'
        }
    }
    Manteliviano.validacionGeneral('form-general', reglas, mensajes);
  
});