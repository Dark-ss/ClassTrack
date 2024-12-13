<?php
require_once 'php/conexion_be.php';
include 'php/admin_session.php';
// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un usuario válido.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del usuario seleccionado
$query_usuario = "SELECT * FROM edificios WHERE id = '$id'";
$resultado_edificio = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_edificio) == 0) {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($resultado_edificio);

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $pisos = mysqli_real_escape_string($conexion, $_POST['pisos']);
    $cupo = mysqli_real_escape_string($conexion, $_POST['cupo']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $usuario_id = mysqli_real_escape_string($conexion, $_POST['usuario']);

    $query_update = "UPDATE edificios SET
        nombre='$nombre',
        codigo='$usuario_id',
        pisos='$pisos',
        cupo='$cupo',
        direccion='$direccion'
        WHERE id='$id'";
    if (mysqli_query($conexion, $query_update)) {
        echo "<script>alert('Usuario actualizado con éxito.'); window.location.href='vista_students.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el edificio: " . mysqli_error($conexion) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Cuentas</title>
    <link rel="stylesheet" href="../templates/assets/css/style_paneles.css">
</head>

<body>
    <main>
        <div class="profile-container">
            <img src="<?php echo $imagen;?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="php/cerrar_sesion.php" class="logout">
                <img src="../templates/assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="php/config.php" class="config">
                <img src="./assets/images/config.png" alt="Configuración" class="incons-image">
            </a>
            <a href="admin_dashboard.php" class="home-admin">
                <img src="./assets/images/inicio.png" alt="inicio" class="icons-image">
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
                    <a href="vista_cuentas.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <div class="container-form_register_config">
            <h2>Información de edificio</h2>
            <form id="update-form" method="POST">
                <input type="hidden" name="codigo" value="<?php echo  htmlspecialchars($usuario['codigo']); ?>">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>

                <label for="usuario">Codigo:</label>
                <input type="text" id="codigo" name="usuario" value="<?php echo htmlspecialchars($usuario['codigo']); ?>" disabled>

                <label for="pisos">cantidad de pisos:</label>
                <input type="number" id="pisos" name="pisos" value="<?php echo htmlspecialchars($usuario['pisos']); ?>" disabled>

                <label for="cupo">cupo:</label>
                <input type="number" id="cupo" name="cupo" value="<?php echo htmlspecialchars($usuario['cupo']); ?>" disabled>

                <label for="direccion">direccion:</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>" disabled>

                <button type="button" id="edit-button-building" class="update-button" onclick="enableEditingBuilding()">Actualizar</button>
                <button type="submit" id="save-button-building" class="save-button" style="display: none;">Guardar Cambios</button>
            </form>
        </div>
    </main>
</body>
<script src="assets/js/button_update.js"></script>
<script src="assets/js/script_menu.js"></script>

</html>