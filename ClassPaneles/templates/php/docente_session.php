<?php
session_start(); // sesión abierta
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); // Redirige al login si no es docente
    exit();
}
include 'php/conexion_be.php';

// Obtener los datos del usuario
$correo = $_SESSION['usuario']; // Usamos el correo de la sesión para obtener los datos del usuario
$query = "SELECT imagen, nombre_completo, rol FROM usuarios WHERE correo='$correo'"; // Consulta obtener datos
$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario_data = mysqli_fetch_assoc($resultado);
    // condicional imagen 
    $imagen = $usuario_data['imagen'] ? "uploads/" . $usuario_data['imagen'] : "uploads/usuario.png";
    $nombre_completo = $usuario_data['nombre_completo'];
    $rol = $usuario_data['rol'];
} else {
    // redireccion a login 
    header("Location: ../templates/index.php");
    exit();
}
