document.querySelectorAll('.openUpdateUserModal').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();

        const id = btn.dataset.id;

        fetch(`../../php/get_user.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('update_id').value = data.id;
                document.getElementById('update_nombre').value = data.nombre_completo;
                document.getElementById('update_correo').value = data.correo;
                document.getElementById('update_usuario').value = data.usuario;
                document.getElementById('update_rol').value = data.rol;
                document.getElementById('correo_original').value = data.correo;

                // actualizar ACTION del form con el ID
                document.getElementById('updateUserForm').action =
                    `update_user.php?id=${id}`;

                document.getElementById('updateUserModal').style.display = 'flex';
            });
    });
});

document.getElementById('closeUpdateModal').onclick = () => {
    document.getElementById('updateUserModal').style.display = 'none';
};