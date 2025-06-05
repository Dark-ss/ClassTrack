<?php
include '../../php/conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reserva = intval($_POST['id_reserva'] ?? 0);
    $estudiantes = $_POST['estudiantes'] ?? [];

    if ($id_reserva === 0) {
        echo json_encode(['success' => false, 'message' => 'ID de reserva inválido']);
        exit;
    }

    $cadenaEstudiantes = implode(',', array_map('intval', $estudiantes));

    $query = "UPDATE reservas SET estudiantes = '$cadenaEstudiantes' WHERE id = $id_reserva";
    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
    }
}
?>
