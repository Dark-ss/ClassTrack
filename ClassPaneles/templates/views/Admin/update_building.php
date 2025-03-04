<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php';
// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un edificio válido.'); window.location.href='register_buldings.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del edificio
$query_usuario = "SELECT * FROM edificios WHERE id = '$id'";
$resultado_edificio = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_edificio) == 0) {
    echo "<script>alert('Edificio no encontrado.'); window.location.href='register_buldings.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($resultado_edificio);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se envió el formulario de descripción
    if (isset($_POST['update_description'])) {
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

        // Actualiza solo la descripción
        $query_update = "UPDATE edificios SET descripcion='$descripcion' WHERE id='$id'";
        if (mysqli_query($conexion, $query_update)) {
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='register_buldings.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar la descripción: " . mysqli_error($conexion) . "');</script>";
        }
    }
}
// Procesar formulario de actualización
if (isset($_POST['update_building'])) { //Solicitud HTTP
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']); //acceso a datos y envio
    $pisos = mysqli_real_escape_string($conexion, $_POST['pisos']); //'escape' para seguridad de la consulta SQL
    $cupo = mysqli_real_escape_string($conexion, $_POST['cupo']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $usuario_id = mysqli_real_escape_string($conexion, $_POST['usuario']);

    $descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($conexion, $_POST['descripcion']) : $usuario['descripcion'];

    $imagen = $usuario['imagen'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/edificio/";

        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $imagen = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        if (!move_uploaded_file($ruta_temp, $imagen)) {
            echo "<script>alert('Error al subir la imagen.');</script>";
            $imagen = $usuario['imagen'];
        }
    }

    $query_update = "UPDATE edificios SET
        nombre='$nombre',
        codigo='$usuario_id',
        pisos='$pisos',
        cupo='$cupo',
        direccion='$direccion',
        descripcion='$descripcion',
        imagen='$imagen'
        WHERE id='$id'";
    if (mysqli_query($conexion, $query_update)) {
        echo "<script>alert('Edificio actualizado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el edificio: " . mysqli_error($conexion) . "');</script>";
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
    <title>Actualizar Edificio</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
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
                    <a href="register_buldings.php">Añadir Edificios</a>
                    <a href="vista_cuentas.php">Edificios</a>
                    <a href="equipment.php">Equipamientos</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>

        <div class="container-description-image" style="display: flex">
            <div class="image-container">
                <h1 class="title_build"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                <img src="<?php echo  htmlspecialchars($usuario['imagen']); ?>" alt="Edificio" class="profile-img-build">
                <a href="vista_spaces.php?edificio_id=<?php echo $id; ?>" class="button-space">Ver Espacios</a>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="description-form" sytle="flex-direction: column">
                <input type="hidden" name="update_description" value="true">
                <div class="build-description">
                    <label for="descripcion" class="title_description">Descripción General</label>
                    <textarea id="descripcion" name="descripcion" class="description-textarea" rows="10" cols="5" disabled><?php echo htmlspecialchars($usuario['descripcion']); ?></textarea>
                </div>
                <button type="button" id="edit-button-description" class="update-button-description" onclick="enableEditingDescription()" >Actualizar</button>
                <button type="submit" id="save-button-description" class="save-button-description" style="display: none;">Guardar Cambios</button>
            </form>
        </div>
        <div class="container-form_register_build">
            <h2>Información de edificio</h2>
            <form id="update-form-build" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_building" value="true">
                <div class="container-group-build">
                    <div class="form-group-build">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>
                    </div>

                    <div class="form-group-build">
                        <label for="pisos">Cantidad de pisos:</label>
                        <input type="number" id="pisos" name="pisos" value="<?php echo htmlspecialchars($usuario['pisos']); ?>" disabled>
                    </div>
                </div>
                <div class="container-group-build">                
                    <div class="form-group-build">
                        <label for="usuario">Código:</label>
                        <input type="text" id="codigo" name="usuario" value="<?php echo htmlspecialchars($usuario['codigo']); ?>" disabled>
                    </div>

                    <div class="form-group-build">
                        <label for="cupo">Cupo:</label>
                        <input type="number" id="cupo" name="cupo" value="<?php echo htmlspecialchars($usuario['cupo']); ?>" disabled>
                    </div>
                </div>

                <div class="container-group-build">
                    <div class="form-group-build">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>" disabled>
                    </div>

                    <div class="form-group-build">
                        <label for="imagen">Imagen</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*" value="<?php echo htmlspecialchars($usuario['imagen']); ?>" disabled>
                    </div>
                </div>

                <button type="button" id="edit-button-building" class="update-button-build" onclick="enableEditingBuilding()" >Actualizar</button>
                <button type="submit" id="save-button-building" class="save-button-build" style="display: none;">Guardar Cambios</button>
            </form>
        </div>

    </main>
</body>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>

</html>