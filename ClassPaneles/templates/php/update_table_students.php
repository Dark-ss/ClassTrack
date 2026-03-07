<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $identificacion = mysqli_real_escape_string($conexion, $_POST['identificacion']);

   $ruta = "../../uploads/estudiantes/";

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {

    $nombre_imagen = time() . "_" . basename($_FILES["imagen"]["name"]);
    $destino = $ruta . $nombre_imagen;

    if(move_uploaded_file($_FILES["imagen"]["tmp_name"], $destino)){
        echo "Imagen subida correctamente";
    } else {
        echo "Error al subir imagen";
    }
}

    if ($nombre_imagen) {

        $query_update = "UPDATE estudiantes SET
            nombre_completo='$nombre_completo',
            correo='$correo',
            identificacion='$identificacion',
            imagen='$nombre_imagen'
        WHERE id='$id'";

    } else {

        $query_update = "UPDATE estudiantes SET
            nombre_completo='$nombre_completo',
            correo='$correo',
            identificacion='$identificacion'
        WHERE id='$id'";
    }

    if (!mysqli_query($conexion, $query_update)) {
        die("Error al actualizar: " . mysqli_error($conexion));
    }
}
?>