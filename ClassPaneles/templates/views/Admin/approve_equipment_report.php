<?php
include '../../php/conexion_be.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_solicitud = $_POST['id'];

    // Paso 1: Obtener espacio_equipamiento_id y el estado de la solicitud
    $sql_get = "SELECT espacio_equipamiento_id, estado FROM solicitudes_reporte_docente WHERE id = ?";
    $stmt_get = $conexion->prepare($sql_get);
    $stmt_get->bind_param("i", $id_solicitud);
    $stmt_get->execute();
    $resultado = $stmt_get->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();
        $espacio_equipamiento_id = $row['espacio_equipamiento_id'];
        $estado_equipamiento = $row['estado']; // Ej: 'dañado', 'funcional', etc.

        // Paso 2: Aprobar la solicitud
        $query = "UPDATE solicitudes_reporte_docente SET estado_solicitud = 'aprobado' WHERE id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param('i', $id_solicitud);

        if ($stmt->execute()) {
            // Paso 3: Actualizar el estado en espacios_equipamiento
            $update_sql = "UPDATE espacios_equipamiento SET estado = ? WHERE id = ?";
            $stmt_update = $conexion->prepare($update_sql);
            $stmt_update->bind_param("si", $estado_equipamiento, $espacio_equipamiento_id);

            if ($stmt_update->execute()) {
                // Éxito total
                header("Location: table_equipment_reports.php");
                exit();
            } else {
                echo "Error al actualizar el estado del equipamiento: " . $stmt_update->error;
            }

            $stmt_update->close();
        } else {
            echo "Error al aprobar la solicitud: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: No se encontró la solicitud.";
    }

    $stmt_get->close();
}
?>

