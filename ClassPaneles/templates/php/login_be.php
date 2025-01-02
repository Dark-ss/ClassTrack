<?php
session_start(); // Mantiene la sesión activa
include 'conexion_be.php'; // Conexión a la base de datos

// Verifica si se recibieron los datos por POST
if (isset($_POST['correo']) && isset($_POST['contrasena'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $contrasena = hash('sha512', $contrasena); // Cifrado de la contraseña

    // Verifica el usuario en la base de datos
    $verificar_Login = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo' AND contrasena='$contrasena'");

    if (mysqli_num_rows($verificar_Login) > 0) { // Si se encuentra el usuario
        $usuario = mysqli_fetch_assoc($verificar_Login); // Obtén los datos del usuario
        $_SESSION['usuario'] = $usuario['correo']; // Guarda el correo en la sesión
        $_SESSION['rol'] = $usuario['rol']; // Guarda el rol en la sesión

        // Redirige según el rol
        if ($usuario['rol'] === 'admin') {
            header("Location: ../views/Admin/admin_dashboard.php"); // Página para el administrador
            exit;
        } elseif ($usuario['rol'] === 'docente') {
            header("Location: ../views/Docente/docente_dashboard.php"); // Página para el docente
            exit;
        }
    } else {
        // Si no se encuentra el usuario, muestra un mensaje de error
        echo '
            <script>
                alert("Los datos ingresados son incorrectos, inténtalo de nuevo");
                window.location = "../index.php"; // Redirige al login
            </script>
        ';
        exit;
    }
} else {
    // Si no se envían los datos, redirige al login
    header("Location: ../index.php");
    exit;
}
