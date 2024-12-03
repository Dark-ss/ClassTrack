<?php
include 'conexion_be.php';

$token = $_GET['token'];
$query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE reset_token='$token' AND reset_expira > NOW()");

if (mysqli_num_rows($query) > 0) {

?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Restablecer Contraseña</title>
    </head>

    <body>
        <h2>Restablecer Contraseña</h2>
        <form action="update_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="password" name="new_password" placeholder="Nueva Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            <button type="submit">Restablecer Contraseña</button>
        </form>
    </body>

    </html>
<?php
} else {
    echo '<script>
            alert("El enlace de recuperación es inválido o ha expirado.");
            window.location = "../index.php";
          </script>';
}

mysqli_close($conexion);
?>