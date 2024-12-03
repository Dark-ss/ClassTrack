<?php
include 'conexion_be.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';


$correo = $_POST['correo'];

// Verificar si el correo existe en la base de datos
$verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo'");

if (isset($_POST['correo'])) {

    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo'");
    // Generar un token de recuperación
    $token = bin2hex(random_bytes(50)); // Genera un token aleatorio
    $expira = date("Y-m-d H:i:s", strtotime('+1 hour')); // Fecha de expiración de 1 hora

    // Guardar el token en la base de datos con el correo
    $update_token = mysqli_query($conexion, "UPDATE usuarios SET reset_token='$token', reset_expira='$expira' WHERE correo='$correo'");

    // Configurar el enlace de recuperación
    $reset_link = "http://localhost/ClassTrack/ClassPaneles/templates/php/reset_password.php?token=$token";

    // Usar PHPMailer para enviar el correo
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    try {
        // Configuración del servidor de correo
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'juancamilocalderon69@gmail.com';
        $mail->Password = 'zjdg rgdb viwc nqls';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar STARTTLS
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('juancamilocalderon69@gmail.com', 'Plataforma ClassTrack');
        $mail->addAddress($correo);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña plataforma ClassTrack';
        $mail->Body = $descripcion . "<br><img src='https://drive.google.com/uc?export=view&id=1dU3ND1kXnVhZLdah6iMpWGbyu3tggWcf' alt='Imagen de recuperación' /> <br><br> Hola, <br><br> Para recuperar tu contraseña, por favor haz clic en el siguiente enlace  <a href='$reset_link'>$reset_link</a>. <br> Si no solicitaste este cambio, por favor ignora este correo. <br><br> ¡Gracias!" ;

        // Enviar el correo
        $mail->send();
        echo '<script>
                alert("Enlace de recuperación enviado a tu correo.");
                window.location = "../index.php";
              </script>';
    } catch (Exception $e) {
          echo '<script>
            alert("Error al enviar el correo de recuperación. Mailer Error: ' . $mail->ErrorInfo . '");
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
