<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
</head>

<body>
    <h2>Recuperar Contraseña</h2>
    <form action="send_reset_link.php" method="POST">
        <input type="email" name="correo" placeholder="Ingresa tu correo" required>
        <button type="submit">Enviar enlace de recuperación</button>
    </form>
</body>

</html>