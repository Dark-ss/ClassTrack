<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/style.css">
</head>

<body>
    <main>

        <div class="contenedor_todo">
            <!--Formularios de login y registro-->
            <div class="contenedor_login-register">
                <!--Login-->
                <div class="logo-container">
                    <img src="../templates/assets/images/logo-classtrack.png" alt="Logo de ClassTrack">
                </div>
                <form action="php/login_be.php" method="POST" class="formulario_login">
                    <h2>Iniciar Sesión</h2>
                    <input type="text" placeholder="Correo Electronico" name="correo" required>
                    <input type="password" placeholder="Contraseña" name="contrasena" required>
                    <button>Entrar</button>
                    <a href="./php/forgot_password.php">Recuperar contraseña</a>
                </form>
            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>
</body>

</html>