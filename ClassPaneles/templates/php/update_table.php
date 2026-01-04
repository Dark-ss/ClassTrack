<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo_original = mysqli_real_escape_string($conexion, $_POST['correo_original']);
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol = mysqli_real_escape_string($conexion, $_POST['rol']);

    // 🔥 CLAVE
    $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;

    $query_update = "UPDATE usuarios SET
        nombre_completo='$nombre_completo',
        correo='$correo',
        usuario='$usuario',
        rol='$rol',
        notificaciones_email=$notificaciones_email
        WHERE correo='$correo_original'";

    if (mysqli_query($conexion, $query_update)) {

        if ($_SESSION['usuario'] === $correo_original) {
            $_SESSION['usuario'] = $correo;
        }

        echo "<script>alert('Información actualizada con éxito.');</script>;";
        header("Refresh:0");        
        exit;


    } else {
        echo "<script>alert('Error: " . mysqli_error($conexion) . "');</script>";
    }
}
?>