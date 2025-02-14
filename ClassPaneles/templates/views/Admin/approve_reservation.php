<?php
include '../../php/conexion_be.php';

if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $id_reservacion = $_POST['id'];
    $nuevo_estado = isset($_POST['approve']) ? 'aceptada' : 'rechazada';

    if ($nuevo_estado == 'aceptada') {
        // Obtener la información de la reserva actual
        $query = "SELECT id_espacio, fecha_inicio, fecha_final FROM reservaciones WHERE id = '$id_reservacion'";
        $resultado = mysqli_query($conexion, $query);
        $reserva = mysqli_fetch_assoc($resultado);

        if ($reserva) {
            $espacio_id = $reserva['id_espacio'];
            $fecha_inicio = $reserva['fecha_inicio'];
            $fecha_fin = $reserva['fecha_final'];

            // Verificar si ya existe una reserva aprobada en el mismo espacio y horario
            $query_conflicto = "SELECT * FROM reservaciones 
                                WHERE id_espacio = '$espacio_id' 
                                AND fecha_inicio = '$fecha_inicio'
                                AND estado = 'aceptada'
                                AND (
                                    (fecha_inicio < '$fecha_fin' AND fecha_final > '$fecha_inicio')
                                )";
            $resultado_conflicto = mysqli_query($conexion, $query_conflicto);

            if (mysqli_num_rows($resultado_conflicto) > 0) {
                echo "<script>alert('Ya existe una reserva aprobada para este espacio en este horario.'); window.location.href = 'table_reservation.php';</script>";
                exit();
            }            
        }
    }

    // Si no hay conflictos, proceder con la actualización del estado
    $query_update = "UPDATE reservaciones SET estado = '$nuevo_estado' WHERE id = '$id_reservacion'";
    
    if (mysqli_query($conexion, $query_update)) {
        echo "Reservación " . ($nuevo_estado == 'aceptada' ? "aceptada" : "rechazada") . " exitosamente.";
        header("Location: table_reservation.php"); // Redirige al administrador para refrescar la tabla
        exit();
    } else {
        echo "Error al procesar la solicitud: " . mysqli_error($conexion);
    }
}
