<?php
include 'conexion_be.php';

$token = $_POST['token'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_password === $confirm_password) {
    $new_password = hash('sha512', $new_password);

    // Actualizar la contraseña y eliminar el token
    $query = "UPDATE usuarios SET contrasena='$new_password', reset_token=NULL, reset_expira=NULL WHERE reset_token='$token'";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        echo '<script>
                alert("Contraseña restablecida con éxito.");
                window.location = "../index.php";
              </script>';
    } else {
        echo '<script>
                alert("Error al restablecer la contraseña.");
                window.location = "../index.php";
              </script>';
    }
} else {
    echo '<script>
            alert("Las contraseñas no coinciden.");
            window.location = "../reset_password.php?token=' . $token . '";
          </script>';
}

mysqli_close($conexion);
