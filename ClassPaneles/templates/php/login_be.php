<?php
include 'conexion_be.php'; // Conexión a la base de datos

if (isset($_POST['correo']) && isset($_POST['contrasena'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $contrasena = hash('sha512', $contrasena);

    // Verifica el usuario en la base de datos
    $verificar_Login = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo' AND contrasena='$contrasena'");

    if (mysqli_num_rows($verificar_Login) > 0) { // Si se encuentra el usuario
        $usuario = mysqli_fetch_assoc($verificar_Login);

        if ($usuario['rol'] === 'admin') {
            session_name("admin_session");
        } elseif ($usuario['rol'] === 'docente') {
            session_name("docente_session");
        }

        session_start();
        session_regenerate_id(true);

        $_SESSION['usuario'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];

        // Redirige según el rol
        if ($usuario['rol'] === 'admin') {
            header("Location: ../views/Admin/admin_dashboard.php");
            exit();
        } elseif ($usuario['rol'] === 'docente') {
            header("Location: ../views/Docente/docente_dashboard.php");
            exit();
        }
    } else {
        echo '
            <script>
                alert("Los datos ingresados son incorrectos, inténtalo de nuevo");
                window.location = "../index.php"; // Redirige al login
            </script>
        ';
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}