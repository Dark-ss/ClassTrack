document.getElementById('edit-button').addEventListener('click', function () {
    // Habilitar los campos
    document.getElementById('nombre_completo').disabled = false;
    document.getElementById('correo').disabled = false;
    document.getElementById('identificacion').disabled = false;

    // Mostrar el botón "Guardar cambios"
    document.getElementById('save-button').style.display = 'inline-block';

    // Ocultar el botón "Actualizar"
    this.style.display = 'none';
});