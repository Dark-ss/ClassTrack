<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php");
    exit();
}
include 'conexion_be.php';
include 'update_table.php';

// Obtener los datos del usuario
$correo = $_SESSION['usuario'];
$query = "SELECT imagen, nombre_completo, correo, usuario, rol FROM usuarios WHERE correo='$correo'";
$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    $usuario_data = mysqli_fetch_assoc($resultado);
    $imagen = $usuario_data['imagen'] ? "../uploads/" . $usuario_data['imagen'] : "../uploads/usuario.png";
    $nombre_completo = $usuario_data['nombre_completo'];
    $correo = $usuario_data['correo'];
    $usuario = $usuario_data['usuario'];
    $rol = $usuario_data['rol'];
} else {
    header("Location: ../templates/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link rel="stylesheet" href="../assets/css/style_panel.css">
</head>

<body>
<div class="container">
        <!-- Sidebar -->
        <?php
        $currentFile = basename($_SERVER['PHP_SELF']);
        ?>
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/logo2.png" alt="Logo" class="logo-img" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="../views/Admin/admin_dashboard.php"
                                class="<?php echo $currentFile == '../views/Admin/admin_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="../views/Admin/vista_cuentas.php"
                                class="<?php echo $currentFile == '../views/Admin/vista_cuentas.php' ? 'active' : ''; ?>">
                                <ion-icon name="people-outline"></ion-icon> Cuentas
                            </a></li>
                        <li><a href="../views/Admin/vista_students.php"
                                class="<?php echo $currentFile == '../views/Admin/vista_students.php' ? 'active' : ''; ?>">
                                <ion-icon name="school-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="../views/Admin/register_buldings.php"
                                class="<?php echo $currentFile == '../views/Admin/register_buldings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Añadir Edificios
                            </a></li>
                        <li><a href="../views/Admin/table_build.php"
                                class="<?php echo $currentFile == '../views/Admin/table_build.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="../views/Admin/equipment.php"
                                class="<?php echo $currentFile == '../views/Admin/equipment.php' ? 'active' : ''; ?>">
                                <ion-icon name="construct-outline"></ion-icon> Equipamientos
                            </a></li>
                        <li><a href="../views/Admin/table_reservation.php"
                                class="<?php echo $currentFile == '../views/Admin/reservar_espacio.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="config.php"
                                class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="cerrar_sesion.php"
                                class="<?php echo $currentFile == 'cerrar_sesion.php' ? 'active' : ''; ?>">
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
                    <p class="user-email"><?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>
        <div class="container-form_register_config">
            <div class="container-form">
            <h2>Información de cuenta</h2>
            <form id="update-form" method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="correo_original" value="<?php echo  htmlspecialchars($correo); ?>">

                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($nombre_completo); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>" disabled>
                </div>
                    
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" disabled>
                        <option value="admin" <?php if ($rol == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="docente" <?php if ($rol == 'docente') echo 'selected'; ?>>Docente</option>
                    </select>
                </div>
                <button type="button" id="edit-button-users" class="update-button-config" onclick="enableEditingUsers()">Actualizar</button>
                <button type=" submit" id="save-button-users" class="save-button" style="display: none;">Guardar Cambios</button>
            </form>
            </div>
        </div>
    </main>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="../assets/js/button_update.js"></script>
    <script src="../assets/js/script_menu.js"></script>
</body>

</html>