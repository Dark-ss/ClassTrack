<?php
header("Content-Type: application/json");

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include '../../php/conexion_be.php';

    $mensaje_id = isset($_POST["mensaje_id"]) ? intval($_POST["mensaje_id"]) : 0;

    if ($mensaje_id > 0) {
        $stmt = $conexion->prepare("DELETE FROM mensajes WHERE id = ?");
        $stmt->bind_param("i", $mensaje_id);
        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            $response["error"] = "Error al eliminar el mensaje.";
        }
        $stmt->close();
    } else {
        $response["error"] = "ID de mensaje inválido.";
    }

    $conexion->close();
}

echo json_encode($response);

