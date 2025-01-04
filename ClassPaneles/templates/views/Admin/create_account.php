<?php
include '../../php/admin_session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>
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
                <img src="../../assets/images/config.png" alt="Cerrar sesión" class="icons-image">
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
                    <a href="register_buldings.php">Añadir Edificios</a>
                    <a href="table_build.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <div class="container-form_register">
            <form action="../../php/registro_usuario_be.php" method="POST" enctype="multipart/form-data" class="formulario_register">
                <h2>Crear cuenta</h2>
                <input type="text" placeholder="Nombre Completo" name="nombre_completo" required>
                <input type="text" placeholder="Correo Electronico" name="correo" required>
                <input type="text" placeholder="Usuario" name="usuario" required>
                <input type="password" placeholder="Contraseña" name="contrasena" required>
                <label class="selection-rol" for="rol">Selecciona el Rol:</label>
                <select name="rol" id="rol" required>
                    <option value="admin">Administrador</option>
                    <option value="docente">Docente</option>
                </select>
                <label class="upload-image" for="imagen">Añadir imagen</label>
                <input type="file" name="imagen" id="imagen" accept="image/*">
                <button>Crear</button>
            </form>
        </div>
    </main>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>