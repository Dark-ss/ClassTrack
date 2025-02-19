<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once 'conexion_be.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idReserva = $_POST['id'] ?? null;
    $motivo = $_POST['motivo_rechazo'] ?? '';

    if (!$idReserva || empty($motivo)) {
        echo json_encode(["success" => false, "error" => "Faltan datos."]);
        exit;
    }

    // Obtener la información de la reserva y el correo del usuario
    $query_info = "SELECT u.correo, r.id_espacio, r.fecha_inicio, r.fecha_final 
                FROM reservaciones r
                JOIN usuarios u ON r.id_usuario = u.id
                WHERE r.id = ?";
    $stmt = $conexion->prepare($query_info);
    $stmt->bind_param("i", $idReserva);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $reserva = $resultado->fetch_assoc();
    $stmt->close();

    if (!$reserva) {
        echo json_encode(["success" => false, "error" => "No se encontró la reserva."]);
        exit;
    }

    $correo_usuario = $reserva['correo'];
    $espacio_id = $reserva['id_espacio'];
    $fecha_inicio = $reserva['fecha_inicio'];
    $fecha_fin = $reserva['fecha_final'];

    // Actualizar el estado de la reserva y guardar el motivo del rechazo
    $query_update = "UPDATE reservaciones SET estado = 'rechazada', motivo_rechazo = ? WHERE id = ?";
    $stmt_update = $conexion->prepare($query_update);
    $stmt_update->bind_param("si", $motivo, $idReserva);

    if ($stmt_update->execute()) {
        $stmt_update->close();

        // Enviar correo de notificación al usuario
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            // Configuración del servidor SMTP con variables de entorno
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            // Configuración del remitente y destinatario
            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($correo_usuario);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = "Reserva rechazada en ClassTrack";
            $mail->AddEmbeddedImage(__DIR__ . './../assets/images/logo_correo.png','logoimg','logo_correo.png');
            $mail->Body = "
                        <img src='cid:logoimg' alt='ClassTrack Logo' style='width: 350px; height: 250px'>
                        <p>Hola,</p>
                        <p>Lamentamos informarte que tu reserva en el espacio <b>$espacio_id</b> ha sido <b>rechazada</b>.</p>
                        <p><b>Motivo del rechazo:</b> $motivo</p>
                        <p>Fecha de inicio: <b>$fecha_inicio</b></p>
                        <p>Fecha de finalización: <b>$fecha_fin</b></p>
                        <p>Para más información, comunícate con el administrador.</p>
                        <p>Gracias por usar nuestra plataforma.</p>";

            if ($mail->send()) {
                echo json_encode(["success" => true, "message" => "Reserva rechazada y notificación enviada."]);
                exit;
            }
            else {
                echo "<script>alert('Error al enviar el correo: " . $mail->ErrorInfo . "');</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('La reserva fue rechazada, pero hubo un error al enviar el correo: " . $mail->ErrorInfo . "'); window.location.href = 'table_reservation.php';</script>";
        }
    } else {
        echo json_encode(["success" => false, "error" => "Error en la base de datos."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido."]);
}

mysqli_close($conexion);
