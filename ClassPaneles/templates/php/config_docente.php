<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); // Redirige
    exit();
}
include 'conexion_be.php';
include 'update_table.php';

// Obtener los datos del usuario
$correo = $_SESSION['usuario']; // Correo desde la sesión
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
    <title>Configuración</title>
    <link rel="stylesheet" href="../assets/css/style_paneles.css">
</head>

<body>
    <main>
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="cerrar_sesion.php" class="logout">
                <img src="../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="config_docente.php" class="config">
                <img src="../assets/images/config.png" alt="Configuración" class="incons-image">
            </a>
            <a href="../views/Docente/docente_dashboard.php" class="home-admin">
                <img src="../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>
            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">
                    Espacios<span>▼</span>
                </div>  
                <div class="submenu" id="submenu">
                    <a href="../views/Docente/vista_buildings.php">Edificios</a>
                    <a href="../views/Docente/table_disponibilidad.php">Disponibilidad</a>
                </div>
            </div>
        </div>
        <div class="container-form_register_config">
            <h2>Información de cuenta</h2>
            <form id="update-form" method="POST">
                <input type="hidden" name="correo_original" value="<?php echo  htmlspecialchars($correo); ?>">

                <label for="nombre_completo">Nombre Completo:</label>
                <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($nombre_completo); ?>" disabled>

                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" disabled>

                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario); ?>" disabled>

                <label for="rol">Rol:</label>
                <select id="rol" name="rol" disabled>
                    <option value="admin" <?php if ($rol == 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="docente" <?php if ($rol == 'docente') echo 'selected'; ?>>Docente</option>
                </select>

                <button type="button" id="edit-button-users" class="update-button" onclick="enableEditingUsers()">Actualizar</button>
                <button type=" submit" id="save-button-users" class="save-button" style="display: none;">Guardar Cambios</button>
            </form>
        </div>
    </main>
    <script src="../assets/js/button_update.js"></script>
    <script src="../assets/js/script_menu.js"></script>
</body>

</html>