<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once 'conexion_be.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idReserva = $_POST['id'] ?? null;
    $motivo = $_POST['motivo_rechazo'] ?? '';

    if (!$idReserva || empty($motivo)) {
        echo json_encode(["success" => false, "error" => "Faltan datos."]);
        exit;
    }

    $query = "UPDATE reservaciones SET estado = 'rechazada', motivo_rechazo = ? WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("si", $motivo, $idReserva);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error en la base de datos."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido."]);
}