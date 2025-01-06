<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php';

// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un espacio válido.'); window.location.href='register_buldings.php';</script>";
    exit;
}

// Obtener y sanitizar el ID recibido
$equip_id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del equipamiento
$query_equip = "SELECT * FROM equipamiento WHERE id = '$equip_id'";
$resultado_equipamiento = mysqli_query($conexion, $query_equip);

if (mysqli_num_rows($resultado_equipamiento) > 0) {
    $id = mysqli_fetch_assoc($resultado_equipamiento);  // Aquí se almacena el array de datos del espacio  // Asignamos el ID del edificio del espacio
} else {
    echo "<script>alert('Espacio de edificio no encontrado.'); window.location.href='register_buldings.php';</script>";
    exit;
}

// Procesar formulario de actualización de descripción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se envió el formulario de descripción
    if (isset($_POST['update_description_space'])) {
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

        // Actualiza solo la descripción
        $query_update = "UPDATE equipamiento SET descripcion='$descripcion' WHERE id='$equip_id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='register_buldings.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la descripción: " . mysqli_error($conexion) . "');</script>";
        }
    }

    // Si se envió el formulario de actualización del espacio
    if (isset($_POST['update_equip'])) {
        $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
        // Condición para la descripción
        $descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($conexion, $_POST['descripcion']) : (isset($id['descripcion']) ? $id['descripcion'] : '');

        // Imagen
        $imagen = $id['imagen'];

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombre_imagen = $_FILES['imagen']['name'];
            $ruta_temp = $_FILES['imagen']['tmp_name'];
            $directorio_destino = "../../uploads/espacio/";

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
        $query_update = "UPDATE equipamiento SET
            codigo='$codigo',
            nombre='$nombre',
            descripcion='$descripcion',
            imagen='$imagen',
            estado='$estado'
            WHERE id='$equip_id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Equipamiento actualizado con éxito.'); window.location.href='equipment.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar el Equipamiento: " . mysqli_error($conexion) . "');</script>";
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Espacio</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
</head>
<body>
<main>
    <div class="profile-container">
        <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Foto de perfil" class="profile-img">
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
                <label for="descripcion" class="title_description">Descripción General</label>
                <textarea id="descripcion" name="descripcion" class="description-textarea" rows="10" cols="5" disabled><?php echo htmlspecialchars($id['descripcion']); ?></textarea>
            </div>
            <button type="button" id="edit-button-description_equip" class="update-button-description" onclick="enableEditingDescriptionEquip()">Actualizar</button>
            <button type="submit" id="save-button-description_equip" class="save-button-description" style="display: none;">Guardar Cambios</button>
        </form>
    </div>

    <div class="container-form_register_build">
        <h2>Información de espacio</h2>
        <form id="update-form-build_spaces" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_equip" value="true">
            <input type="hidden" name="id" value="<?php echo $id['id']; ?>">
            <div class="container-group-build">
                <div class="form-group-build">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($id['codigo']); ?>" disabled>
                </div>
                <div class="form-group-build">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($id['nombre']); ?>" disabled>
                </div>
            </div>

            <div class="container-group-build">
                <div class="form-group-build">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" disabled>
                        <option value="Disponible" <?php echo ($id['estado'] === 'Disponible') ? 'selected' : ''; ?>>Disponible</option>
                        <option value="En Mantenimiento" <?php echo ($id['estado'] === 'En Mantenimiento') ? 'selected' : ''; ?>>En Mantenimiento</option>
                        <option value="No Disponible" <?php echo ($id['estado'] === 'No Disponible') ? 'selected' : ''; ?>>No Disponible</option>
                    </select>
                </div>

                <div class="form-group-build">
                    <label for="imagen">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" disabled>
                </div>
            </div>

            <div class="buttons-form-container">
                <button type="button" id="edit-button-equip" class="update-button" onclick="enableEditingEquip()">Actualizar</button>
                <button type="submit" id="save-button-equip" class="save-button" style="display: none;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</main>

<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</body>

</html>