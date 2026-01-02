<?php
session_name("docente_session");
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); // Redirige
    exit();
}
include 'conexion_be.php';
include 'update_table.php';

// Obtener los datos del usuario
$correo = $_SESSION['usuario']; // Correo desde la sesión
/*$query = "SELECT imagen, nombre_completo, correo, usuario, rol FROM usuarios WHERE correo='$correo'";*/

$query = "SELECT imagen, nombre_completo, correo, usuario, rol, notificaciones_email 
          FROM usuarios 
          WHERE correo='$correo'";

$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario_data = mysqli_fetch_assoc($resultado);
    $notificaciones_email = $usuario_data['notificaciones_email'];
    $imagen = $usuario_data['imagen'] ? "../uploads/" . $usuario_data['imagen'] : "../uploads/usuario.png";
    $nombre_completo = $usuario_data['nombre_completo'];
    $correo = $usuario_data['correo'];
    $usuario = $usuario_data['usuario'];
    $rol = $usuario_data['rol'];
} else {
    // Si no se encuentra al usuario, redirige al login
    header("Location: ../templates/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../assets/images/logo2.png">
    <title>Configuración Docente</title>
</head>

<body>
<div class="container-docente">
<aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logo2.png" alt="Logo" class="logo-img" width="150" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="../views/Docente/docente_dashboard.php"
                                class="<?php echo $currentFile == '../views/Docente/docente_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="../views/Docente/vista_buildings.php"
                                class="<?php echo $currentFile == '../views/Docente/vista_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="../views/Docente/table_disponibilidad.php"
                                class="<?php echo $currentFile == '../views/Docente/table_disponibilidad.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Disponibilidad
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de reservas</p>
                    <ul>
                        <li><a href="../views/Docente/mis_reservas.php"
                                class="<?php echo $currentFile == '../views/Docente/mis_reservas.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Ayuda</p>
                    <ul>
                        <li><a href="../views/Docente/suport.php"
                                class="<?php echo $currentFile == '../views/Docente/suport.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Soporte técnico
                            </a></li>
                    </ul>
                    <ul>
                        <li><a href="../views/Docente/mis_solicitudes.php"
                                class="<?php echo $currentFile == '../views/Docente/mis_solicitudes.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis solicitudes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="config_docente.php"
                                class="<?php echo $currentFile == 'config_docente.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="cerrar_session_docente.php"
                                class="<?php echo $currentFile == 'cerrar_session_docente.php' ? 'active' : ''; ?>">
                                <ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión
                            </a></li>
                    </ul>
                </div>
            </nav>
            <div class="divider"></div>
            <div class="profile">
                <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
                <div>
                    <p class="user-name"><?php echo htmlspecialchars($nombre_completo); ?></p>
                    <p class="user-email"> <?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>
        
        <div class="container-form_register_config">
            <div class="container-form">
            <h2>Información de cuenta</h2>
            <form id="update-form" method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="correo_original" value="<?php echo  htmlspecialchars($correo); ?>">

                <div class="form-group-config">
                    <label for="nombre_completo">Nombre Completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($nombre_completo); ?>" disabled>
                </div>

                <div class="form-group-config">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" disabled>
                </div>

                <div class="form-group-config">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>" disabled>
                </div>
                    
                <div class="form-group-config">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" disabled>
                        <option value="admin" <?php if ($rol == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="docente" <?php if ($rol == 'docente') echo 'selected'; ?>>Docente</option>
                    </select>
                </div>
                <div class="form-group-config">
                    <label>Notificaciones por correo</label>

                    <label class="switch">
                        <input 
                            type="checkbox"
                            name="notificaciones_email"
                            id="notificaciones_email"
                            value="1"
                            <?= $notificaciones_email ? 'checked' : '' ?>
                        >
                        <span class="slider"></span>
                    </label>
                </div>

                <button type="button" id="edit-button-users" class="update-button-config" onclick="enableEditingUsers()">Actualizar</button>
                <button type=" submit" id="save-button-users" class="save-button" style="display: none;">Guardar Cambios</button>
            </form>
            </div>
        </div>
    </main>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="../assets/js/button_update.js"></script>
</body>
</html>