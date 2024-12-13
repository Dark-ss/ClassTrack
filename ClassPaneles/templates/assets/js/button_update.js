function enableEditingStudents(event) {
    // Habilitar los campos
    document.getElementById('nombre_completo').disabled = false;
    document.getElementById('correo').disabled = false;
    document.getElementById('identificacion').disabled = false;
    // Mostrar el botón "Guardar cambios"
    document.getElementById('save-button-students').style.display = 'inline-block';
    // Ocultar el botón "Actualizar"
    event.target.style.display = 'none';
};
document.getElementById('edit-button-students').addEventListener('click', enableEditingStudents);

function enableEditingBuilding () {
    // Habilitar los campos
    document.getElementById('nombre').disabled = false;
    document.getElementById('codigo').disabled = false;
    document.getElementById('pisos').disabled = false;
    document.getElementById('cupo').disabled = false;
    document.getElementById('direccion').disabled = false;
    // Mostrar el botón "Guardar cambios"
    document.getElementById('save-button-building').style.display = 'inline-block';
    // Ocultar el botón "Actualizar"
    event.target.style.display = 'none';
};
document.getElementById('edit-button-building').addEventListener('click', enableEditingBuilding);

function enableEditingUsers() {
    // Habilitar los campos
    document.getElementById('nombre_completo').disabled = false;
    document.getElementById('correo').disabled = false;
    document.getElementById('usuario').disabled = false;
    document.getElementById('rol').disabled = false;
    // Mostrar el botón "Guardar cambios"
    document.getElementById('save-button-users').style.display = 'inline-block';
    // Ocultar el botón "Actualizar"
    event.target.style.display = 'none';
};
document.getElementById('edit-button-users').addEventListener('click', enableEditingUsers);
