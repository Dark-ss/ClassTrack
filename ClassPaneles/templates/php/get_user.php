<?php
include 'admin_session.php';
include 'conexion_be.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no recibido']);
    exit;
}

$id = intval($_GET['id']);

$query = "SELECT id, nombre_completo, correo, usuario, rol, imagen
          FROM usuarios
          WHERE id = $id";

$result = mysqli_query($conexion, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Usuario no encontrado']);
}
