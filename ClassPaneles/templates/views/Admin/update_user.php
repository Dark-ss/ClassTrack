<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php';

// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un usuario válido.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del usuario seleccionado
$query_usuario = "SELECT * FROM usuarios WHERE id = '$id'";
$resultado_usuario = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_usuario) == 0) {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($resultado_usuario);

// Procesar formulario de actualización
include '../../php/update_table.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Cuentas</title>
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
            <a href="../../php/config.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuración" class="incons-image">
            </a>
            <a href="admin_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>
            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Cuenta<span>▼</span>
                </div>
                <div class="submenu" id="submenu">
                    <a href="create_account.php">Crear Cuenta</a>
                    <a href="vista_cuentas.php">cuentas </a>
                    <a href="register_students.php">Añadir Estudiantes</a>
                    <a href="vista_students.php">Estudiantes</a>
                </div>
            </div>
            <div class="menu-container_espacios" id="menu-container_espacios">
                <div class="menu-link" onclick="toggleDropdown_space()">Espacios<span>▼</span>
                </div>
                <div class="submenu" id="submenu_espacios">
                    <a href="create_account.php">Añadir Edificios</a>
                    <a href="vista_cuentas.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <div class="container-form_register_config">
            <h2>Información de cuenta</h2>
            <form id="update-form" method="POST">
                <input type="hidden" name="correo_original" value="<?php echo  htmlspecialchars($usuario['correo']); ?>">
                <label for="nombre_completo">Nombre Completo:</label>
                <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" disabled>

                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled>

                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>

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
</body>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>

</html>