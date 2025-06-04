document.getElementById("btn_registrarse").addEventListener("click", register)
document.getElementById("btn_iniciar-sesion").addEventListener("click",login)
window.addEventListener("resize", anchoPagina);//evento para toda la pagina

//declaración variables
var contenedor_login_register = document.querySelector(".contenedor_login-register");
var formulario_login = document.querySelector(".formulario_login");
var formulario_register = document.querySelector(".formulario_register");
var caja_trasera_login = document.querySelector(".caja_trasera_login");
var caja_trasera_register = document.querySelector(".caja_trasera_register");

function anchoPagina(){
    if(window.innerWidth > 850){
        caja_trasera_login.style.display = "block";
        caja_trasera_register.style.display = "block";
    }else{
        caja_trasera_register.style.display = "block";
        caja_trasera_register.style.opacity = "1";
        caja_trasera_login.style.display = "none";
        formulario_login.style.display = "block";
        formulario_register.style.display = "none";
        contenedor_login_register.style.left = "0px";
    }
}

anchoPagina()//para iniciar de una vez la función

function register(){
    if(window.innerWidth > 850){
        formulario_register.style.display = "block";//muestra el formulario
        contenedor_login_register.style.left = "410px";//pasa el formulario a la derecha
        formulario_login.style.display = "none";
        caja_trasera_register.style.opacity = "0";//oculta
        caja_trasera_login.style.opacity = "1"//muestra
    }else{
        formulario_register.style.display = "block";
        contenedor_login_register.style.left = "0px";
        formulario_login.style.display = "none";
        caja_trasera_register.style.display = "none";
        caja_trasera_login.style.display = "block";
        caja_trasera_login.style.opacity = "1";
    }
}

function login() {

    if(window.innerWidth > 850){
        formulario_register.style.display = "none";
        contenedor_login_register.style.left = "10px";
        formulario_login.style.display = "block";
        caja_trasera_register.style.opacity = "1";
        caja_trasera_login.style.opacity = "0"
    }else{
        formulario_register.style.display = "none";
        contenedor_login_register.style.left = "0px";
        formulario_login.style.display = "block";
        caja_trasera_register.style.display = "block";
        caja_trasera_login.style.display = "none";
    }
}

function deleteReservation(reservationId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta reserva?')) {
        fetch(`eliminar_reserva.php?id=${reservationId}`, {
            method: 'POST',
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al eliminar la reserva');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Reserva eliminada correctamente');
                location.reload(); // Recargar la página para actualizar la lista
            } else {
                alert('No se pudo eliminar la reserva');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al eliminar la reserva');
        });
    }
}

function openUpdateModal(reservation) {
    document.getElementById('update-id').value = reservation.id;
    document.getElementById('update-nombre-usuario').value = reservation.nombre_usuario;
    document.getElementById('update-fecha-inicio').value = reservation.fecha_inicio;
    document.getElementById('update-fecha-final').value = reservation.fecha_final;
    document.getElementById('update-tipo-reservacion').value = reservation.tipo_reservacion;
    document.getElementById('update-descripcion').value = reservation.descripcion;

    document.getElementById('update-modal').style.display = 'block';
}

// Cerrar el modal cuando se haga clic fuera
window.onclick = function(event) {
    if (event.target === document.getElementById('update-modal')) {
        document.getElementById('update-modal').style.display = 'none';
    }
};