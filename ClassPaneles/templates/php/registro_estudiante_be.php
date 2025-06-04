<?php
require_once 'conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $identificacion = mysqli_real_escape_string($conexion, $_POST['identificacion']);

    // Manejo de la imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../uploads/";
        
        $ruta_imagen = uniqid() . "_" . basename($nombre_imagen);
        
        if (move_uploaded_file($ruta_temp, $directorio_destino . $ruta_imagen)) {
            $imagen = $ruta_imagen;
        }
    }

    $query = "INSERT INTO estudiantes (nombre_completo, correo, identificacion, imagen)
              VALUES ('$nombre_completo', '$correo', '$identificacion', '$imagen')";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../views/Admin/vista_students.php");
        exit();
    } else {
        echo "<script>alert('Error al registrar estudiante'); window.location='../admin/templates/vista_students.php';</script>";
    }
}
?>
