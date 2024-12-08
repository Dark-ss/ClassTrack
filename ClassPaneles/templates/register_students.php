<?php
require_once 'php/conexion_be.php';
include 'php/admin_session.php'; // Verifica que el admin esté autenticado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $identificacion = mysqli_real_escape_string($conexion, $_POST['identificacion']);

    // Manejo de la imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "uploads/estudiantes/";

        // Asegurarse de que el directorio exista
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $ruta_imagen = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        if (move_uploaded_file($ruta_temp, $ruta_imagen)) {
            $imagen = $ruta_imagen;
        } else {
            echo "<script>alert('Error al subir la imagen.');</script>";
        }
    }

    // Insertar estudiante
    $query = "INSERT INTO estudiantes (nombre_completo, correo, identificacion, imagen)
              VALUES ('$nombre_completo', '$correo', '$identificacion', '$imagen')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Estudiante registrado con éxito.'); window.location.href='vista_students.php';</script>";
    } else {
        echo "<script>alert('Error al registrar estudiante: " . mysqli_error($conexion) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante</title>
    <link rel="stylesheet" href="assets/css/style_paneles.css">
</head>

<body>
    <h1>Registrar Estudiante</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required>

        <label for="correo">Correo Electrónico:</label>
        <input type="email" id="correo" name="correo" required>

        <label for="identificacion">Identificación:</label>
        <input type="text" id="identificacion" name="identificacion" required>

        <label for="imagen">Imagen:</label>
        <input type="file" id="imagen" name="imagen" accept="image/*">

        <button type="submit">Registrar Estudiante</button>
    </form>
</body>

</html>