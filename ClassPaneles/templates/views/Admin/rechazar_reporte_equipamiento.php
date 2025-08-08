<?php
include '../../php/conexion_be.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['motivo_rechazo'])) {
    $id = $_POST['id'];
    $motivo = $_POST['motivo_rechazo'];

    $query = "UPDATE solicitudes_reporte_docente SET estado_solicitud = 'rechazado', motivo_rechazo = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'si', $motivo, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
?>
