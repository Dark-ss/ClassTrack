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
    $tipo = isset($_POST['tipo']) ? mysqli_real_escape_string($conexion, $_POST['tipo']) : $usuario['tipo'];

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
        tipo='$tipo',
        imagen='$imagen'
        WHERE id='$id'";
    if (mysqli_query($conexion, $query_update)) {
        echo "<script>alert('Edificio actualizado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el edificio: " . mysqli_error($conexion) . "');</script>";
    }
}

// Consultar espacios
$query = "SELECT id, codigo, imagen, edificio_id FROM espacios_academicos WHERE edificio_id = '$id'";
$result = mysqli_query($conexion, $query);
$espacios = [];
$espacios_count = mysqli_num_rows($result);

while ($row = mysqli_fetch_assoc($result)) {
    $espacios[] = $row;
}   

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Edificio</title>
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1 ">
    <link rel="stylesheet" href="../../assets/css/update_style.css?v=1 ">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php
        $currentFile = basename($_SERVER['PHP_SELF']);
        ?>
        <aside class="sidebar">
            <div class="logo">
                <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" width="150" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="admin_dashboard.php"
                                class="<?php echo $currentFile == 'admin_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="vista_cuentas.php"
                                class="<?php echo $currentFile == 'vista_cuentas.php' ? 'active' : ''; ?>">
                                <ion-icon name="people-outline"></ion-icon> Cuentas
                            </a></li>
                        <li><a href="vista_students.php"
                                class="<?php echo $currentFile == 'vista_students.php' ? 'active' : ''; ?>">
                                <ion-icon name="person-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="./register_buldings.php"
                                class="<?php echo $currentFile == 'register_buldings.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Añadir Edificios
                            </a></li>
                        <li><a href="table_build.php"
                                class="<?php echo $currentFile == 'table_build.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="equipment.php"
                                class="<?php echo $currentFile == 'equipment.php' ? 'active' : ''; ?>">
                                <ion-icon name="construct-outline"></ion-icon> Equipamientos
                            </a></li>
                        <li><a href="table_reservation.php"
                                class="<?php echo $currentFile == 'table_reservation.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Mensajeria</p>
                    <ul>
                        <li><a href="messages.php"
                                class="<?php echo $currentFile == 'messages.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Buzon ayuda
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="../../php/config.php"
                                class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="../../php/cerrar_sesion_admin.php"
                                class="<?php echo $currentFile == 'cerrar_sesion_admin.php' ? 'active' : ''; ?>">
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

        <div id="main-content">
            <div class="header-container">
                <div class="back-button-container">
                    <button class="back-button" onclick="window.history.back()">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </button>
                </div>
                <div class="edit-button-container">
                    <button id="edit-mode-btn" class="edit-button">
                        <i class="fa-solid fa-pen"></i> Modo Edición
                    </button>

                    <div class="action-buttons">
                        <button type="button" id="cancel-edit-btn" class="cancel-btn"><i class="fas fa-times"></i>
                            Cancelar</button>
                        <button type="submit" class="save-btn"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                    </div>
                </div>

            </div>

            <!-- Envuelve todo en un formulario para guardar los cambios -->
            <form id="edit-form" method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="update_building" value="1">

                <div class="rectangle">
                    <div class="half">
                        <div class="building-showcase">
                            <div class="image-container">
                                <a href="vista_spaces.php?edificio_id=<?php echo $id; ?>">
                                    <img src="<?php echo htmlspecialchars($usuario['imagen']); ?>" alt="Edificio" class="building-image">
                                </a>
                                <label for="imagen" class="change-image-btn">
                                    <i class="fa-solid fa-camera camera-icon"></i>
                                    <div class="change-image-text">Cambiar imagen</div>
                                    <div class="subtext">Haz clic para seleccionar</div>
                                </label>
                                <input type="file" id="imagen" name="imagen" class="file-input" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="half">
                        <div class="tabs-container">
                            <h1 class="building-title">
                                <span
                                    class="hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                                <input type="text" id="nombre" name="nombre"
                                    value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                    class="editable-field hidden-in-view-mode" required>
                            </h1>
                            <!-- Etiqueta de tipo para modo visualización -->
                            <div
                                class="building-type-tag <?php echo strtolower(str_replace(' ', '-', $usuario['tipo'])); ?> hidden-in-edit-mode">
                                <?php echo htmlspecialchars($usuario['tipo']); ?>
                            </div>
                            <!-- Select para cambiar el tipo en modo edición -->
                            <select id="tipo-header" name="tipo-header" class="type-selector hidden-in-view-mode"
                                data-sync-with="tipo">
                                <option value="">Seleccione un tipo</option>
                                <option value="Laboratorio"
                                    <?php echo ($usuario['tipo'] == 'Laboratorio') ? 'selected' : ''; ?>>Laboratorio
                                </option>
                                <option value="Espacio Académico"
                                    <?php echo ($usuario['tipo'] == 'Espacio Académico') ? 'selected' : ''; ?>>Espacio
                                    Académico</option>
                                <option value="Auditorio"
                                    <?php echo ($usuario['tipo'] == 'Auditorio') ? 'selected' : ''; ?>>Auditorio
                                </option>
                            </select>
                            <div class="tabs">
                                <button type="button" class="tab-button active" data-tab="informacion">
                                    <i class="fa-solid fa-circle-info"></i> Información
                                </button>
                                <button type="button" class="tab-button" data-tab="detalles">
                                    <i class="ti ti-list"></i> Detalles
                                </button>
                                <button type="button" class="tab-button" data-tab="ubicacion">
                                    <i class="fa-solid fa-location-dot"></i> Ubicación
                                </button>
                            </div>

                            <div class="tab-content" id="informacion">
                                <div class="details-grid-descrip">
                                    <div class="collapsible-content">
                                        <p class="hidden-in-edit-mode">
                                            <?php echo htmlspecialchars($usuario['descripcion']); ?></p>
                                        <textarea id="descripcion" name="descripcion"
                                            class="editable-textarea hidden-in-view-mode"><?php echo htmlspecialchars($usuario['descripcion']); ?></textarea>
                                    </div>
                                </div>

                                <div class="details-grid">
                                    <!-- Capacidad -->
                                    <div class="detail-item">
                                        <i class="ti ti-users" style="font-weight: bold;"></i>
                                        <div class="detail-text">
                                            <span class="detail-label">Capacidad</span>
                                            <span
                                                class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['cupo']); ?></span>
                                            <input type="number" id="cupo" name="cupo"
                                                value="<?php echo htmlspecialchars($usuario['cupo']); ?>"
                                                class="editable-field hidden-in-view-mode" required> <span
                                                class="hidden-in-view-mode"></span>
                                        </div>
                                    </div>

                                    <!-- Pisos -->
                                    <div class="detail-item">
                                        <i class="ti ti-building-skyscraper" style="font-weight: bold;"></i>
                                        <div class="detail-text">
                                            <span class="detail-label">Pisos</span>
                                            <span
                                                class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['pisos']); ?></span>
                                            <input type="number" id="pisos" name="pisos"
                                                value="<?php echo htmlspecialchars($usuario['pisos']); ?>"
                                                class="editable-field hidden-in-view-mode" required> <span
                                                class="hidden-in-view-mode"></span>
                                        </div>
                                    </div>

                                    <!-- Código -->
                                    <div class="detail-item">
                                        <i class="ti ti-qrcode" style="font-weight: bold;"></i>
                                        <div class="detail-text">
                                            <span class="detail-label">Código Edificio</span>
                                            <span
                                                class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['codigo']); ?></span>
                                            <input type="text" id="usuario" name="usuario"
                                                value="<?php echo htmlspecialchars($usuario['codigo']); ?>"
                                                class="editable-field hidden-in-view-mode" required>
                                        </div>
                                    </div>

                                    <!-- Salones -->
                                    <div class="detail-item">
                                        <i class="ti ti-door" style="font-weight: bold;"></i>
                                        <div class="detail-text">
                                            <span class="detail-label">Salones</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($espacios_count); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content" id="detalles" style="display: none;">
                                <div class="details-list">
                                    <div class="detail-item">
                                        <span class="detail-label">Pisos:</span>
                                        <span
                                            class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['pisos']); ?></span>
                                        <input type="number" id="pisos-detail" name="pisos-detail"
                                            value="<?php echo htmlspecialchars($usuario['pisos']); ?>"
                                            class="editable-field hidden-in-view-mode" data-sync-with="pisos">
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Cupo:</span>
                                        <span
                                            class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['cupo']); ?></span>
                                        <input type="number" id="cupo-detail" name="cupo-detail"
                                            value="<?php echo htmlspecialchars($usuario['cupo']); ?>"
                                            class="editable-field hidden-in-view-mode" data-sync-with="cupo">
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Dirección:</span>
                                        <span
                                            class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['direccion']); ?></span>
                                        <input type="text" id="direccion" name="direccion"
                                            value="<?php echo htmlspecialchars($usuario['direccion']); ?>"
                                            class="editable-field hidden-in-view-mode" required>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Tipo:</span>
                                        <span
                                            class="detail-value hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['tipo']); ?></span>
                                        <select id="tipo" name="tipo" class="editable-field hidden-in-view-mode"
                                            required>
                                            <option value="">Seleccione un tipo</option>
                                            <option value="Laboratorio"
                                                <?php echo ($usuario['tipo'] == 'Laboratorio') ? 'selected' : ''; ?>>
                                                Laboratorio</option>
                                            <option value="Espacio Académico"
                                                <?php echo ($usuario['tipo'] == 'Espacio Académico') ? 'selected' : ''; ?>>
                                                Espacio Académico</option>
                                            <option value="Auditorio"
                                                <?php echo ($usuario['tipo'] == 'Auditorio') ? 'selected' : ''; ?>>
                                                Auditorio</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content" id="ubicacion" style="display: none;">
                                <div class="location-info">
                                    <p>Dirección:
                                        <span
                                            class="hidden-in-edit-mode"><?php echo htmlspecialchars($usuario['direccion']); ?></span>
                                        <input type="text" id="direccion-map" name="direccion-map"
                                            value="<?php echo htmlspecialchars($usuario['direccion']); ?>"
                                            class="editable-field hidden-in-view-mode" data-sync-with="direccion">
                                    </p>
                                    <div class="map-placeholder">
                                        <!-- Mapa -->
                                        <div class="mock-map">
                                            <iframe
                                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.406936025233!2d-75.57055629999999!3d6.209937200000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e44282bb107cea9%3A0x4c9c14802efabc6b!2sCra.%2043A%20%2310%2C%20El%20Poblado%2C%20Medell%C3%ADn%2C%20El%20Poblado%2C%20Medell%C3%ADn%2C%20Antioquia!5e0!3m2!1ses-419!2sco!4v1744934469247!5m2!1ses-419!2sco"
                                                width="600" height="450" style="border:0;" allowfullscreen=""
                                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <script src="../../assets/js/building_edit.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>

</html>