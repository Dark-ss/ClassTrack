<?php
include '../../php/conexion_be.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje_id = $_POST['mensaje_id'];
    $respuesta = $_POST['respuesta'];

    if (!empty($mensaje_id) && !empty($respuesta)) {
        // Actualizar la respuesta y el estado del mensaje
        $query = "UPDATE mensajes SET respuesta = ?, estado = 'Resuelto' WHERE id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("si", $respuesta, $mensaje_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al actualizar el mensaje"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}
?>
