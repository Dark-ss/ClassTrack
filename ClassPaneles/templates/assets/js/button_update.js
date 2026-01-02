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

function enableEditingReservation() {
    document.getElementById('fecha_inicio').disabled = false;
    document.getElementById('fecha_final').disabled = false;
    document.getElementById('tipo_reservacion').disabled = false;
    document.getElementById('descripcion').disabled = false;
    document.getElementById('id_espacio').disabled = false;
    document.getElementById('button-state-acept').disabled = true; 
    document.getElementById('estudiantes').disabled = false;
    document.getElementById('button-state-acept').disabled = false; 

    document.getElementById('save-button-reservation').style.display = 'inline-block';
    event.target.style.display = 'none';
};
document.getElementById('edit-button-reservation').addEventListener('click', enableEditingReservation);

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('edit-button-building').addEventListener('click', enableEditingBuilding);
    document.getElementById('edit-button-students').addEventListener('click', enableEditingStudents);
    document.getElementById('edit-button-users').addEventListener('click', enableEditingUsers);
    document.getElementById('edit-button-description').addEventListener('click', enableEditingDescription);
    document.getElementById('estudiantes').addEventListener('input', eventQueryStudents);
    document.getElementById('button-state-acept').addEventListener('click', enableEditingReservation);
    document.getElementById('estudiantes_search').addEventListener('click', enableEditingReservation);
});

// Función para mostrar/ocultar el dropdown
function toggleExportDropdown(event) {
    var dropdown = document.getElementById('exportDropdown');

    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
        dropdown.style.display = 'block';
        setTimeout(() => {
            dropdown.style.opacity = 1;
            dropdown.style.transform = 'translateY(0)';
        }, 10);
    } else {
        dropdown.style.opacity = 0;
        dropdown.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            dropdown.style.display = 'none';
        }, 300);
    }
}

function submitExportForm(format) {
    var form = document.getElementById('exportForm');
    var formData = new FormData(form);
    formData.set('format', format);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la exportación');
        }
        return response.blob();
    })
    .then(blob => {
        var filename = 'reservas.' + (format === 'excel' ? 'xls' : format);
        
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error al exportar los datos.');
    });
}

/* Cierra el menú si se hace clic fuera */
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (openDropdown.style.display === 'block') {
                openDropdown.style.opacity = 0;
                openDropdown.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    openDropdown.style.display = 'none';
                }, 300);
            }
        }
    }
};

/*CONFIGURACION NOTIFICACIONES*/ 
function enableEditingUsers() {
    document.querySelectorAll('#update-form input, #update-form select')
        .forEach(el => el.disabled = false);

    document.getElementById('edit-button-users').style.display = 'none';
    document.getElementById('save-button-users').style.display = 'inline-block';
}

