<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php'; // Importa la conexión

// Consulta para obtener el total de usuarios
$queryUsuarios = "SELECT COUNT(*) AS total_usuarios FROM usuarios WHERE rol = 'docente'";
$resultUsuarios = mysqli_query($conexion, $queryUsuarios);
$totalUsuarios = mysqli_fetch_assoc($resultUsuarios)['total_usuarios'];

// Consulta para obtener el total de estudiantes
$queryEstudiantes = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";
$resultEstudiantes = mysqli_query($conexion, $queryEstudiantes);
$totalEstudiantes = mysqli_fetch_assoc($resultEstudiantes)['total_estudiantes'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
</head>

<body>
    <main>
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="../../php/cerrar_sesion.php" class="logout">
                <img src="../../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            
            <a href="../../php/config_docente.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="docente_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>

            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Espacios<span>▼</span>
                </div>  
                <div class="submenu" id="submenu">
                    <a href="vista_buildings.php">Edificios</a>
                    <a href="reservation.php">Salones</a>
                </div>
            </div>

        </div>

        <div class="dashboard">
            <div class="stat-card" id="totalUsers">
                <h3>Docentes registrados</h3>
                <p><?php echo $totalUsuarios; ?></p>
            </div>
            <div class="stat-card" id="totalStudents">
                <h3>Estudiantes registrados</h3>
                <p><?php echo $totalEstudiantes; ?></p>
            </div>
        </div>

    </main>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>