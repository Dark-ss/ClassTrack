<?php
session_name("admin_session");
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php");
    exit();
}

include 'conexion_be.php';

$correo = $_SESSION['usuario'];
$query = "SELECT id, imagen, nombre_completo, rol FROM usuarios WHERE correo='$correo'";
$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario_data = mysqli_fetch_assoc($resultado);
    $_SESSION['id_usuario'] = $usuario_data['id'];//linea añadida para que funcione el reporte de equipamientos
    $imagen = $usuario_data['imagen'] ? "../../uploads/" . $usuario_data['imagen'] : "../../uploads/usuario.png";
    $nombre_completo = $usuario_data['nombre_completo'];
    $rol = $usuario_data['rol'];
} else {
    header("Location: ../templates/index.php");
    exit();
}