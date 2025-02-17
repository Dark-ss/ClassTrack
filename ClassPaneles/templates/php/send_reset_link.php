<?php
include 'conexion_be.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$correo = $_POST['correo'];

// Verificar si el correo existe en la base de datos
$correo = mysqli_real_escape_string($conexion, $_POST['correo']);
$verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo'");

if ($verificar_correo && mysqli_num_rows($verificar_correo) > 0) {
    // Generar un token de recuperación
    $token = bin2hex(random_bytes(50));
    $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Guardar el token en la base de datos
    $update_token = mysqli_query($conexion, "UPDATE usuarios SET reset_token='$token', reset_expira='$expira' WHERE correo='$correo'");

    // Configurar el enlace de recuperación
    $reset_link = "http://localhost/ClassTrack/ClassPaneles/templates/php/reset_password.php?token=$token";

    // Enviar correo con PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        // Configuración del servidor SMTP desde variables de entorno
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Configuración del remitente y destinatario
        $mail->setFrom($_ENV['SMTP_USER'], 'Plataforma ClassTrack');
        $mail->addAddress($correo);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña - ClassTrack';
        $mail->Body = "<p>Hola,</p>
                      <p>Para recuperar tu contraseña, haz clic en el siguiente enlace: <a href='$reset_link'>$reset_link</a></p>
                      <p>Si no solicitaste este cambio, ignora este correo.</p>
                      <p>¡Gracias!</p>";

        // Enviar el correo
        $mail->send();
        echo '<script>
                alert("Enlace de recuperación enviado a tu correo.");
                window.location = "../index.php";
              </script>';
    } catch (Exception $e) {
        echo '<script>
                alert("Error al enviar el correo: ' . $mail->ErrorInfo . '");
                window.location = "../index.php";
              </script>';
    }
} else {
    echo '<script>
            alert("El correo no está registrado.");
            window.location = "../index.php";
          </script>';
}

mysqli_close($conexion);