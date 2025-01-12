<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!--Aqui se vincula la pagina de iconos de "Font Awesome"-->
    <title>Unispace</title>
    <link type="text/css" rel="stylesheet" href="ClassPaneles/templates/assets/css/style.css">
    <link rel="shortcut icon" href="ClassPaneles/templates/assets/images/logo1.png">
</head>

<body>
    <div class="fondo3">
        <main class="contenedor_formulario">
            <div class="form_img">
                <div class="ilustracion1"></div>
                <div class="formulario">
                    <div class="title">
                        <h1>Bienvenido</h1>
                    </div>
                    <div class="cont_form">
                        <form action="ClassPaneles/templates/php/login_be.php" method="post">
                            <div class="input_field">
                                <label for="email">Correo electrónico:</label>
                                <input autocomplete="off" autofocus type="email" id="email" name="email"
                                    placeholder="ejemplo@mail.com" required>
                            </div>
                            <div class="input_field">
                                <label for="password">Contraseña:</label>
                                <div class="input-container">
                                    <input type="password" id="password" name="password" placeholder="Contraseña"
                                        required>
                                    <i class="fa-solid fa-eye-slash login_eye_closed"></i>
                                </div>
                                <div class="link_change_password"><a href="ClassPaneles/templates/php/forgot_password.php">¿Olvidaste tu contraseña?</a></div>
                            </div>
                            <button type="submit">Iniciar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="ClassPaneles/templates/assets/js/main.js"></script> <!--Conexion a JavaScript-->
</body>

</html>
