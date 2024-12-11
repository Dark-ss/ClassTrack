<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo_original = mysqli_real_escape_string($conexion, $_POST['correo_original']);
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $identificacion = mysqli_real_escape_string($conexion, $_POST['identificacion']);

    $query_update = "UPDATE estudiantes SET
        nombre_completo='$nombre_completo',
        correo='$correo',
        identificacion='$identificacion',
        WHERE correo='$correo_original'";

    if (mysqli_query($conexion, $query_update)) {
        // Actualización exitosa: Refrescar datos de la sesión
        $_SESSION['usuario'] = $correo;
        echo "<script>
    alert('Información actualizada con éxito.');
</script>";
        header("Refresh:0"); // Recarga la página para reflejar los cambios
    } else {
        echo "<script>
    alert('Error al actualizar la información: " . mysqli_error($conexion) . "');
</script>";
    }
}
