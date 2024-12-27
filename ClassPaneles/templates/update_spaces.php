<?php
require_once 'php/conexion_be.php';
include 'php/admin_session.php';
// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un espacio válido.'); window.location.href='register_buldings.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del espacio
$query_espacio = "SELECT * FROM espacios_academicos WHERE edificio_id = '$id'";
$resultado_espacio = mysqli_query($conexion, $query_espacio);

if (mysqli_num_rows($resultado_espacio) == 0) {
    echo "<script>alert('Espacio no encontrado.'); window.location.href='register_buldings.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($resultado_espacio);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se envió el formulario de descripción
    if (isset($_POST['update_description'])) {
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion_general']);

        // Actualiza solo la descripción
        $query_update = "UPDATE espacios_academicos SET descripcion_general='$descripcion_general' WHERE id='$id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='register_buldings.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la descripción: " . mysqli_error($conexion) . "');</script>";
        }
    }
}
// Procesar formulario de actualización
if (isset($_POST['update_spaces'])) { //Solicitud HTTP
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
    $capacidad = mysqli_real_escape_string($conexion, $_POST['capacidad']);
    $tipo_espacio = mysqli_real_escape_string($conexion, $_POST['tipo_espacio']);
    $building_id = mysqli_real_escape_string($conexion, $_POST['edificio_id']);

    $descripcion_general = isset($_POST['descripcion_general']) ? mysqli_real_escape_string($conexion, $_POST['descripcion_general']) : $usuario['descripcion_general'];

    $imagen = $usuario['imagen'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "uploads/espacio/";

        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $imagen = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        if (!move_uploaded_file($ruta_temp, $imagen)) {
            echo "<script>alert('Error al subir la imagen.');</script>";
            $imagen = $usuario['imagen'];
        }
    }

    $query_update = "UPDATE espacios_academicos SET
        codigo='$codigo',
        capacidad='$capacidad',
        tipo_espacio='$tipo_espacio',
        edificio_id='$building_id',
        descripcion_general='$descripcion_general'
        imagen='$imagen'
        WHERE id='$id'";
    if (mysqli_query($conexion, $query_update)) {
        echo "<script>alert('Espacio actualizado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el espacio: " . mysqli_error($conexion) . "');</script>";
    }
}

// Consultar espacios
$query = "SELECT id, codigo, imagen FROM espacios_academicos";
$result = mysqli_query($conexion, $query);
$espacios = [];

while ($row = mysqli_fetch_assoc($result)) {
    $espacios[] = $row;
} 

//obteniendo id del edificio
$building_id = isset($_GET['id']) ? intval($_GET['id']):0;
// Validar si el ID corresponde a un edificio existente
$query = "SELECT nombre FROM edificios WHERE id = $building_id";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $edificio = mysqli_fetch_assoc($result);
}   else {
    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_edificios.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Edificio</title>
    <link rel="stylesheet" href="../templates/assets/css/style_paneles.css">
    <link rel="stylesheet" href="../templates/assets/css/style_building.css?v=1.0">
</head>

<body>
    <main>
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
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

        <div class="container-description-image" style="display: flex">
            <div class="image-container">
                <h1 class="title_build"><?php echo htmlspecialchars($usuario['codigo']); ?></h1>
                <img src="<?php echo  htmlspecialchars($usuario['imagen']); ?>" alt="Edificio" class="profile-img-build">
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="description-form" sytle="flex-direction: column">
                <input type="hidden" name="update_description" value="true"><!--Campo oculto-->
                <div class="build-description">
                    <label for="descripcion_general" class="title_description">Descripción General</label>
                    <textarea id="descripcion" name="descripcion" class="description-textarea" rows="10" cols="5" disabled><?php echo htmlspecialchars($usuario['descripcion_general']); ?></textarea>
                </div>
                <button type="button" id="edit-button-description" class="update-button-description" onclick="enableEditingDescription()" >Actualizar</button>
                <button type="submit" id="save-button-description" class="save-button-description" style="display: none;">Guardar Cambios</button>
            </form>
        </div>
        <div class="container-form_register_build">
            <h2>Información de espacio</h2>
            <form id="update-form-build" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_spaces" value="true">
                <div class="container-group-build">
                    <div class="form-group-build">
                        <label for="usuario">Código:</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($usuario['codigo']); ?>" disabled>
                    </div>

                    <div class="form-group-build">
                        <label for="capacidad">Capacidad:</label>
                        <input type="number" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($usuario['capacidad']); ?>" disabled>
                    </div>
                </div>

                <div class="container-group-build">
                    <div class="form-group-build">
                        <label for="imagen">Imagen</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*" value="<?php echo htmlspecialchars($usuario['imagen']); ?>" disabled>
                    </div>

                    <div class="form-group-build">
                        <label>Edificio seleccionado:</label>
                        <input type="text" value="<?php echo htmlspecialchars($edificio['nombre']); ?>" disabled>
                    </div>
                </div>

                <button type="button" id="edit-button-building" class="update-button-build" onclick="enableEditingBuilding()" >Actualizar</button>
                <button type="submit" id="save-button-building" class="save-button-build" style="display: none;">Guardar Cambios</button>
            </form>
        </div>

    </main>
</body>
<script src="assets/js/button_update.js"></script>
<script src="assets/js/script_menu.js"></script>

</html>