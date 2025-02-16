<?php
include '../../php/conexion_be.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $id_reservacion = $_POST['id'];
    $nuevo_estado = isset($_POST['approve']) ? 'aceptada' : 'rechazada';

    // Obtener la información de la reserva y el correo del usuario
    $query_info = "SELECT u.correo, r.id_espacio, r.fecha_inicio, r.fecha_final 
                FROM reservaciones r
                JOIN usuarios u ON r.id_usuario = u.id
                WHERE r.id = ?";

    $stmt = mysqli_prepare($conexion, $query_info);
    mysqli_stmt_bind_param($stmt, "i", $id_reservacion);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $reserva = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($reserva) {
        $correo_usuario = $reserva['correo'];
        $espacio_id = $reserva['id_espacio'];
        $fecha_inicio = $reserva['fecha_inicio'];
        $fecha_fin = $reserva['fecha_final'];

        // Verificar si ya existe una reserva aprobada en el mismo espacio y horario
        if ($nuevo_estado == 'aceptada') {
            $query_conflicto = "SELECT id FROM reservaciones 
                                WHERE id_espacio = ? 
                                AND fecha_inicio < ? 
                                AND fecha_final > ?
                                AND estado = 'aceptada'";

            $stmt_conflicto = mysqli_prepare($conexion, $query_conflicto);
            mysqli_stmt_bind_param($stmt_conflicto, "iss", $espacio_id, $fecha_fin, $fecha_inicio);
            mysqli_stmt_execute($stmt_conflicto);
            $resultado_conflicto = mysqli_stmt_get_result($stmt_conflicto);

            if (mysqli_num_rows($resultado_conflicto) > 0) {
                echo "<script>alert('Ya existe una reserva aprobada para este espacio en este horario.'); window.location.href = 'table_reservation.php';</script>";
                exit();
            }
            mysqli_stmt_close($stmt_conflicto);
        }
        
        // Actualizar el estado de la reserva
        $query_update = "UPDATE reservaciones SET estado = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conexion, $query_update);
        mysqli_stmt_bind_param($stmt_update, "si", $nuevo_estado, $id_reservacion);

        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);

            // Enviar correo de notificación
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';

            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'juancamilocalderon69@gmail.com';
                $mail->Password = 'zjdg rgdb viwc nqls';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuración del remitente y destinatario
                $mail->setFrom('juancamilocalderon69@gmail.com', 'Plataforma ClassTrack');
                $mail->addAddress($correo_usuario);

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = "Estado de tu reserva en ClassTrack";
                $mail->Body = "<p>Hola,</p>
                            <p>Tu reserva en el espacio <b>$espacio_id</b> ha sido <b>$nuevo_estado</b>.</p>
                            <p>Fecha de inicio: <b>$fecha_inicio</b></p>
                            <p>Fecha de finalización: <b>$fecha_fin</b></p>
                            <p>Gracias por usar nuestra plataforma.</p>";

                // Enviar el correo
                $mail->send();

                echo "<script>alert('Reservación $nuevo_estado exitosamente. Se ha enviado una notificación al usuario.'); window.location.href = 'table_reservation.php';</script>";
                exit();

            } catch (Exception $e) {
                echo "<script>alert('La reservación fue $nuevo_estado, pero hubo un error al enviar el correo: " . $mail->ErrorInfo . "'); window.location.href = 'table_reservation.php';</script>";
                exit();
            }
        } else {
            echo "Error al actualizar la reservación: " . mysqli_error($conexion);
        }
    } else {
        echo "<script>alert('No se encontró la reserva.'); window.location.href = 'table_reservation.php';</script>";
    }
}
