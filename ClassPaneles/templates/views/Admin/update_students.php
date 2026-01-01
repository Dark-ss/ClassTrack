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
$query_usuario = "SELECT * FROM estudiantes WHERE id = '$id'";
$resultado_estudiante = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_estudiante) == 0) {
    echo "<script>alert('Usuario no encontrado.'); window.location.href='vista_cuentas.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($resultado_estudiante);
$imagen_actual = $usuario['imagen']; // Guardar la imagen actual

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $usuario_id = mysqli_real_escape_string($conexion, $_POST['usuario']);

    $imagen_nueva = null;

    // Verificar si se subió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/estudiantes/";

        // Asegurar que el directorio exista
        if (!file_exists($directorio_destino)) {
            if (!mkdir($directorio_destino, 0777, true)) {
                die("<script>alert('Error: No se pudo crear el directorio de destino.');</script>");
            }
        }

        $ruta_imagen_nueva = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        // Mover el archivo y verificar si se guardó correctamente
        if (move_uploaded_file($ruta_temp, $ruta_imagen_nueva)) {
            // Verificar si el archivo realmente se movió
            if (file_exists($ruta_imagen_nueva)) {
                echo "<script>console.log('Imagen subida correctamente: $ruta_imagen_nueva');</script>";
            } else {
                die("<script>alert('Error: La imagen no se guardó en la carpeta de destino.');</script>");
            }

            // Eliminar la imagen anterior si existe
            if (!empty($imagen_actual) && file_exists($imagen_actual)) {
                unlink($imagen_actual);
            }
            $imagen_nueva = $ruta_imagen_nueva;
        } else {
            die("<script>alert('Error al subir la nueva imagen.');</script>");
        }
    } else {
        echo "<script>console.log('No se subió una nueva imagen. Se mantiene la anterior.');</script>";
    }

    // Construcción de la consulta SQL
    $query_update = "UPDATE estudiantes SET
        nombre_completo='$nombre_completo',
        correo='$correo',
        identificacion='$usuario_id'";

    // Solo actualizar la imagen si se subió una nueva
    if ($imagen_nueva) {
        $query_update .= ", imagen='$imagen_nueva'";
    }

    $query_update .= " WHERE id='$id'";

    if (mysqli_query($conexion, $query_update)) {
        echo "<script>alert('Usuario actualizado con éxito.'); window.location.href='vista_students.php';</script>";
    } else {
        die("<script>alert('Error al actualizar el usuario: " . mysqli_error($conexion) . "');</script>");
    }
}
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
            <a href="../../php/cerrar_sesion_admin.php" class="logout">
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
            <form id="update-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="correo_original" value="<?php echo  htmlspecialchars($usuario['correo']); ?>">
                <label for="nombre_completo">Nombre Completo:</label>
                <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" disabled>

                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled>

                <label for="usuario">identificacion:</label>
                <input type="text" id="identificacion" name="usuario" value="<?php echo htmlspecialchars($usuario['identificacion']); ?>" disabled>

                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" name="imagen" disabled>

                <button type="button" id="edit-button-students" class="update-button" onclick="enableEditingStudents()">Actualizar</button>
                <button type=" submit" id="save-button-students" class="save-button" style="display: none;">Guardar Cambios</button>
            </form>
        </div>
    </main>
</body>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>

</html>