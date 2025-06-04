<?php
header('Content-Type: application/json'); // Asegura que la respuesta sea JSON
include '../../php/conexion_be.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mensaje_id = $_POST["mensaje_id"] ?? null;
    $respuesta = $_POST["respuesta"] ?? null;

    if ($mensaje_id && $respuesta) {
        $stmt = $conexion->prepare("UPDATE mensajes SET respuesta = ? WHERE id = ?");
        $stmt->bind_param("si", $respuesta, $mensaje_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Faltan datos"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}

$conexion->close();