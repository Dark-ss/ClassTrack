<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); // Redirige al login si no es admin
    exit();
}

include 'php/conexion_be.php';

// Obtener los datos del usuario (como la imagen)
$correo = $_SESSION['usuario']; // Usamos el correo de la sesión para obtener los datos del usuario
$query = "SELECT imagen, nombre_completo, rol FROM usuarios WHERE correo='$correo'"; // Consulta para obtener los datos
$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario_data = mysqli_fetch_assoc($resultado);
    // Si el usuario tiene imagen, se carga, sino se asigna una imagen predeterminada
    $imagen = $usuario_data['imagen'] ? "uploads/" . $usuario_data['imagen'] : "uploads/usuario.png";
    $nombre_completo = $usuario_data['nombre_completo'];
    $rol = $usuario_data['rol'];
} else {
    // Si no se encuentra al usuario, redirige al login
    header("Location: ../templates/index.php");
    exit();
}