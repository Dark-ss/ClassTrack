<?php
require_once '../../php/conexion_be.php';
include '../../php/docente_session.php';
// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un edificio válido.'); window.location.href='vista_buildings.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar datos del usuario seleccionado
$query_usuario = "SELECT * FROM edificios WHERE id = '$id'";
$resultado_edificio = mysqli_query($conexion, $query_usuario);

if (mysqli_num_rows($resultado_edificio) == 0) {
    echo "<script>alert('Edificio no encontrado.'); window.location.href='vista_buildings.php';</script>";
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
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='vista_buildings.php';</script>";
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
        $directorio_destino = "uploads/edificio/";

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información Edificio</title>
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
    <link rel="stylesheet" href="../../assets/css/style_teacher.css?v=1.0">
</head>

<body>
    <aside class="sidebar">
            <div class="logo">
                <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" width="150" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="docente_dashboard.php"
                                class="<?php echo $currentFile == 'docente_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="vista_buildings.php"
                                class="<?php echo $currentFile == 'vista_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="table_disponibilidad.php"
                                class="<?php echo $currentFile == 'table_disponibilidad.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Disponibilidad
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de reservas</p>
                    <ul>
                        <li><a href="mis_reservas.php"
                                class="<?php echo $currentFile == 'mis_reservas.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="../../php/config_docente.php"
                                class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="../../php/cerrar_sesion.php"
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
                    <p class="user-email"> <?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
    </aside>

    <main class="content">

        <div class="container-description-image" style="display: flex">

            <div class="image-container">
                <h1 class="title_build"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                <img src="<?php echo  htmlspecialchars($usuario['imagen']); ?>" alt="Edificio" class="profile-img-build">
                <a href="vista_spaces_docente.php?edificio_id=<?php echo $id; ?>" class="btn-spaces">Ver Espacios</a>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="description-form" sytle="flex-direction: column">
                <input type="hidden" name="update_description" value="true">
                <div class="build-description">
                    <label for="descripcion" class="title_description">Descripción General</label>
                    <textarea id="descripcion" name="descripcion" class="description-textarea_docente" rows="10" cols="5" disabled><?php echo htmlspecialchars($usuario['descripcion']); ?></textarea>
                </div>
            </form>
        </div>
        <div class="modal-content-building">
            <h2>Información de edificio</h2>
            <form id="update-form-build_teacher" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_building" value="true">
                <div class="container-group-build">
                    <div class="form-group-build build_name_teacher">
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
                    <div class="form-group-build address">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>" disabled>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
<script src="../../assets/js/button_update.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</html>