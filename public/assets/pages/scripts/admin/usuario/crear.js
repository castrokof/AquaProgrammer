$(document).ready(function(){
         const reglas ={
        remenber_token1:{
            equalTo: "#password1"
        }
    };
    const mensajes ={
        remenber_token1:{
            equalTo: 'las contraseñas no coinciden'
        }
    }
    Manteliviano.validacionGeneral('form-general-pass', reglas, mensajes);
  
});