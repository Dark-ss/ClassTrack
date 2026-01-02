<?php
include '../../php/conexion_be.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 4));
$dotenv->load();

if (isset($_POST['approve']) || isset($_POST['reject'])) {

    $id_reservacion = (int)$_POST['id'];
    $nuevo_estado = isset($_POST['approve']) ? 'aceptada' : 'rechazada';

    $query_info = "
        SELECT u.correo, u.notificaciones_email,
               r.id_espacio, r.fecha_inicio, r.fecha_final
        FROM reservaciones r
        JOIN usuarios u ON r.id_usuario = u.id
        WHERE r.id = ?
    ";

    $stmt = mysqli_prepare($conexion, $query_info);
    mysqli_stmt_bind_param($stmt, "i", $id_reservacion);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $reserva = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$reserva) {
        echo "<script>alert('No se encontró la reserva'); window.location.href='table_reservation.php';</script>";
        exit();
    }

    $correo_usuario = $reserva['correo'];
    $notificaciones_email = (int)$reserva['notificaciones_email'];
    $espacio_id = $reserva['id_espacio'];
    $fecha_inicio = $reserva['fecha_inicio'];
    $fecha_fin = $reserva['fecha_final'];

    // 🔹 Validar conflicto
    if ($nuevo_estado === 'aceptada') {
        $query_conflicto = "
            SELECT id FROM reservaciones
            WHERE id_espacio = ?
              AND fecha_inicio < ?
              AND fecha_final > ?
              AND estado = 'aceptada'
        ";

        $stmt_conflicto = mysqli_prepare($conexion, $query_conflicto);
        mysqli_stmt_bind_param($stmt_conflicto, "iss", $espacio_id, $fecha_fin, $fecha_inicio);
        mysqli_stmt_execute($stmt_conflicto);
        $resultado_conflicto = mysqli_stmt_get_result($stmt_conflicto);

        if (mysqli_num_rows($resultado_conflicto) > 0) {
            echo "<script>alert('Ya existe una reserva aprobada en ese horario'); window.location.href='table_reservation.php';</script>";
            exit();
        }

        mysqli_stmt_close($stmt_conflicto);
    }

    // 🔹 ACTUALIZAR SIEMPRE
    $query_update = "UPDATE reservaciones SET estado = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    mysqli_stmt_bind_param($stmt_update, "si", $nuevo_estado, $id_reservacion);

    if (!mysqli_stmt_execute($stmt_update)) {
        echo "Error al actualizar la reservación";
        exit();
    }

    mysqli_stmt_close($stmt_update);

    // 🔹 ENVIAR CORREO SOLO SI ESTÁ ACTIVO
    if ($notificaciones_email === 1) {

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($correo_usuario);

            $mail->isHTML(true);
            $mail->Subject = "Estado de tu reserva en ClassTrack";
            $mail->AddEmbeddedImage(__DIR__ . './../../assets/images/logo_correo.png','logoimg');

            $mail->Body = "
                <img src='cid:logoimg' style='width:300px'><br>
                <p>Tu reserva en el espacio <b>$espacio_id</b> fue <b>$nuevo_estado</b>.</p>
                <p>Inicio: $fecha_inicio</p>
                <p>Fin: $fecha_fin</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // No rompe el flujo
        }
    }

    // 🔹 REDIRECCIÓN FINAL
    echo "<script>
        alert('La reserva fue $nuevo_estado correctamente');
        window.location.href='table_reservation.php';
    </script>";
}
?>