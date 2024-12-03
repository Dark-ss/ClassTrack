<?php
    include 'php/docente_session.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="../templates/assets/css/style_paneles.css">
</head>

<body>
    <div class="profile-container">
        <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
        <h3 class="profile-name"><?php echo htmlspecialchars($nombre_completo); ?></h3>
        <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
        <a href="php/cerrar_sesion.php" class="logout">
            <img src="assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
        </a>
    </div>
</body>

</html>