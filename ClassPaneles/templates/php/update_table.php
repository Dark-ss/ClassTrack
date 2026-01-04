<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo_original = mysqli_real_escape_string($conexion, $_POST['correo_original']);
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol = mysqli_real_escape_string($conexion, $_POST['rol']);

    $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
    $imagen_sql = ""; // por defecto no actualiza imagen

    if (!empty($_FILES['imagen']['name'])) {

        $nombre_img = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_destino = "../../uploads/" . $nombre_img;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen_sql = ", imagen='$nombre_img'";
        }
    }
    $query_update = "UPDATE usuarios SET
        nombre_completo='$nombre_completo',
        correo='$correo',
        usuario='$usuario',
        rol='$rol',
        notificaciones_email=$notificaciones_email
        $imagen_sql
        WHERE correo='$correo_original'";

    if (mysqli_query($conexion, $query_update)) {

        if ($_SESSION['usuario'] === $correo_original) {
            $_SESSION['usuario'] = $correo;
        }

        echo "<script>alert('Información actualizada con éxito.');</script>";
        header("Refresh:0");
        exit;

    } else {
        echo "<script>alert('Error: " . mysqli_error($conexion) . "');</script>";
    }
}
?>