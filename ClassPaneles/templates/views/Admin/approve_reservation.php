<?php
include '../../php/conexion_be.php';

if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $id_reservacion = $_POST['id'];
    $nuevo_estado = isset($_POST['approve']) ? 'aceptada' : 'rechazada';

    $query = "UPDATE reservaciones SET estado = '$nuevo_estado' WHERE id = '$id_reservacion'";
    if (mysqli_query($conexion, $query)) {
        echo "Reservación " . ($nuevo_estado == 'aceptada' ? "aceptada" : "rechazada") . " exitosamente.";
        header("Location: table_reservation.php"); // Redirige al administrador para refrescar la tabla
    } else {
        echo "Error al procesar la solicitud: " . mysqli_error($conexion);
    }
}
