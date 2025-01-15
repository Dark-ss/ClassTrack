function enableEditingStudents() {
    document.getElementById('nombre_completo').disabled = false;
    document.getElementById('correo').disabled = false;
    document.getElementById('identificacion').disabled = false;
    document.getElementById('imagen').disabled = false;
    document.getElementById('save-button-students').style.display = 'inline-block';
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
    document.getElementById('imagen').disabled = false;
    document.getElementById('save-button-building').style.display = 'inline-block'; 
    event.target.style.display = 'none';
};
document.getElementById('edit-button-building').addEventListener('click', enableEditingBuilding);

function enableEditingDescription() {
    document.getElementById('descripcion').disabled = false;
    document.getElementById('save-button-description').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-description').addEventListener('click', enableEditingDescription);

function enableEditingSpace(){
        document.getElementById('codigo').disabled = false;
        document.getElementById('capacidad').disabled = false;
        document.getElementById('imagen').disabled = false;

        document.getElementById('save-button-Space').style.display = 'inline-block'; 
        event.target.style.display = 'none';
}
document.getElementById('edit-button-Space').addEventListener('click', enableEditingSpace);

function enableEditingDescriptionSpace() {
    document.getElementById('descripcion_general').disabled = false;
    document.getElementById('save-button-description_space').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-description_space').addEventListener('click', enableEditingDescriptionSpace);

function enableEditingUsers() {
    document.getElementById('nombre_completo').disabled = false;
    document.getElementById('correo').disabled = false;
    document.getElementById('usuario').disabled = false;
    document.getElementById('rol').disabled = false;
    document.getElementById('save-button-users').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-users').addEventListener('click', enableEditingUsers);

function enableEditingEquip() {
    document.getElementById('nombre').disabled = false;
    document.getElementById('codigo').disabled = false;
    document.getElementById('estado').disabled = false;
    document.getElementById('imagen').disabled = false;
    document.getElementById('save-button-equip').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-equip').addEventListener('click', enableEditingEquip);

function enableEditingDescriptionEquip() {
    document.getElementById('descripcion').disabled = false;
    document.getElementById('save-button-description_equip').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-description_space').addEventListener('click', enableEditingDescriptionEquip);

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('edit-button-building').addEventListener('click', enableEditingBuilding);
    document.getElementById('edit-button-students').addEventListener('click', enableEditingStudents);
    document.getElementById('edit-button-users').addEventListener('click', enableEditingUsers);
    document.getElementById('edit-button-description').addEventListener('click', enableEditingDescription);
    document.getElementById('estudiantes').addEventListener('input', eventQueryStudents);
});