<?php
require_once 'php/conexion_be.php';
include 'php/docente_session.php';

// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un espacio válido.'); window.location.href='register_buldings.php';</script>";
    exit;
}

// Obtener y sanitizar el ID recibido
$space_id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del espacio
$query_espacio = "SELECT * FROM espacios_academicos WHERE id = '$space_id'";
$resultado_espacio = mysqli_query($conexion, $query_espacio);

if (mysqli_num_rows($resultado_espacio) > 0) {
    $id = mysqli_fetch_assoc($resultado_espacio);  // Aquí se almacena el array de datos del espacio
    $building_id = $id['edificio_id'];  // Asignamos el ID del edificio del espacio
} else {
    echo "<script>alert('Espacio de edificio no encontrado.'); window.location.href='register_buldings.php';</script>";
    exit;
}

// Procesar formulario de actualización de descripción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se envió el formulario de descripción
    if (isset($_POST['update_description_space'])) {
        $descripcion_general = mysqli_real_escape_string($conexion, $_POST['descripcion_general']);

        // Actualiza solo la descripción
        $query_update = "UPDATE espacios_academicos SET descripcion_general='$descripcion_general' WHERE id='$space_id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='register_buldings.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la descripción: " . mysqli_error($conexion) . "');</script>";
        }
    }

    // Si se envió el formulario de actualización del espacio
    if (isset($_POST['update_spaces'])) {
        $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
        $capacidad = mysqli_real_escape_string($conexion, $_POST['capacidad']);
        $building_id = mysqli_real_escape_string($conexion, $_POST['edificio_id']);

        // Condición para la descripción
        $descripcion_general = isset($_POST['descripcion_general']) ? mysqli_real_escape_string($conexion, $_POST['descripcion_general']) : (isset($id['descripcion_general']) ? $id['descripcion_general'] : '');

        // Imagen
        $imagen = $id['imagen'];

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
                $imagen = $id['imagen'];
            }
        }

        // Actualización de espacio
        $query_update = "UPDATE espacios_academicos SET
            codigo='$codigo',
            capacidad='$capacidad',
            descripcion_general='$descripcion_general',
            imagen='$imagen'
            WHERE id='$space_id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Espacio actualizado con éxito.'); window.location.href='register_buldings.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar el espacio: " . mysqli_error($conexion) . "');</script>";
        }
    }
}

// Consultar espacios
$query = "SELECT id, codigo, imagen, edificio_id FROM espacios_academicos";
$result = mysqli_query($conexion, $query);
$espacios = [];

while ($row = mysqli_fetch_assoc($result)) {
    $espacios[] = $row;
}

// Validar si el ID corresponde a un edificio existente
$query_edificio = "SELECT nombre FROM edificios WHERE id = '$building_id'";
$result_edificio = mysqli_query($conexion, $query_edificio);

if ($result_edificio && mysqli_num_rows($result_edificio) > 0) {
    $edificio = mysqli_fetch_assoc($result_edificio);
} else {
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
    <link rel="stylesheet" href="../templates/assets/css/style_teacher.css?v=1.0">
</head>
<body>
<main>
    <div class="profile-container">
        <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Foto de perfil" class="profile-img">
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
            <div class="menu-link" onclick="toggleDropdown()">Cuenta<span>▼</span></div>
            <div class="submenu" id="submenu">
                <a href="create_account.php">Crear Cuenta</a>
                <a href="vista_cuentas.php">cuentas </a>
                <a href="register_students.php">Añadir Estudiantes</a>
                <a href="vista_students.php">Estudiantes</a>
            </div>
        </div>
        <div class="menu-container_espacios" id="menu-container_espacios">
            <div class="menu-link" onclick="toggleDropdown_space()">Espacios<span>▼</span></div>
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
            <h1 class="title_build"><?php echo htmlspecialchars($id['codigo']); ?></h1>
            <img src="<?php echo htmlspecialchars($id['imagen']); ?>" alt="Edificio" class="profile-img-build">
        </div>

        <form method="POST" enctype="multipart/form-data" class="description-form">
            <input type="hidden" name="update_description_space" value="true">
            <div class="build-description">
                <label for="descripcion_general" class="title_description">Descripción General</label>
                <textarea id="descripcion_general" name="descripcion_general" class="description-textarea" rows="10" cols="5" disabled><?php echo htmlspecialchars($id['descripcion_general']); ?></textarea>
            </div>
        </form>
    </div>

    <div class="container-form_register_build">
        <h2>Información de espacio</h2>
        <form id="update-form-spaces_teacher" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_spaces" value="true">
            <input type="hidden" name="id" value="<?php echo $id['id']; ?>">
            <input type="hidden" name="edificio_id" value="<?php echo htmlspecialchars($building_id)?>">
            <div class="container-group-build">
                <div class="form-group-build">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($id['codigo']); ?>" disabled>
                </div>
                <div class="form-group-build">
                    <label for="capacidad">Capacidad:</label>
                    <input type="number" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($id['capacidad']); ?>" disabled>
                </div>
            </div>

            <div class="container-group-build">
                <div class="form-group-build">
                    <label>Edificio seleccionado:</label>
                    <input type="text" value="<?php echo htmlspecialchars($edificio['nombre']); ?>" disabled>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="assets/js/button_update.js"></script>
<script src="assets/js/script_menu.js"></script>
</body>

</html>