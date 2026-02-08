    <?php
    require_once '../../php/conexion_be.php';
    include '../../php/admin_session.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

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
        $building_id = $id['edificio_id']; 
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

            if ($imagen === null) {
                $imagen = "../../assets/images/default_building.png";
            }

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
    //equipamiento
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['equipment_spaces'])) {
            $space_equip_id = mysqli_real_escape_string($conexion, $_POST['id']);
            $cantidades = $_POST['cantidad'];
            $estados = $_POST['estado'];

            foreach ($cantidades as $equipamiento_id => $cantidad) {
                if ($cantidad > 0) {
                    $estado_equip = isset($estados[$equipamiento_id]) ? mysqli_real_escape_string($conexion, $estados[$equipamiento_id]) : 'No disponible';

                    $query_insert = "INSERT INTO espacios_equipamiento (espacio_id, equipamiento_id, cantidad, estado) 
                            VALUES ('$space_equip_id', '$equipamiento_id', '$cantidad', '$estado_equip')
                            ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad), estado = VALUES(estado)";
                    mysqli_query($conexion, $query_insert) or die("Error: " . mysqli_error($conexion));
                }
            }
        
            echo "<script>alert('Equipamientos añadidos con éxito.'); window.location.href='register_buldings.php';</script>";
        }
    }

    $id_usuario = $_SESSION['id_usuario'];
    // Validar si el ID corresponde a un edificio existente
    $query_usuario = "SELECT id, nombre_completo FROM usuarios WHERE id = $id_usuario";
    $result_usuario = mysqli_query($conexion, $query_usuario);

    if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
        $espacio_usuario = mysqli_fetch_assoc($result_usuario);
    }   else {
        echo "<script>alert('Usuario no encontrado. ID: $id_usuario'); window.location.href='update_spaces_docente.php';</script>";
        exit;
    }

    //reportes equipamiento
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_usuario = $_SESSION['id_usuario'];
        $espacio_equipamiento_id = isset($_POST['espacio_equipamiento_id']) ? (int) $_POST['espacio_equipamiento_id'] : 0;
        $espacio_id = isset($_POST['espacio_id']) ? (int) $_POST['espacio_id'] : 0;
        $nuevo_estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
        echo "id_usuario: $id_usuario <br>";
        echo "Valores recibidos para validación:<br>";
        echo "equipamiento_id: $espacio_equipamiento_id <br>";
        echo "espacio_id: $espacio_id <br>";


        $query_lookup = "SELECT id FROM espacios_equipamiento WHERE equipamiento_id = ? AND espacio_id = ?";
        $stmt_lookup = $conexion->prepare($query_lookup);
        $stmt_lookup->bind_param("ii", $espacio_equipamiento_id, $espacio_id);
        $stmt_lookup->execute();
        $stmt_lookup->bind_result($real_espacio_equipamiento_id);
        $stmt_lookup->fetch();
        $stmt_lookup->close();
        
        // Verificar si encontramos un resultado válido
        if (!$real_espacio_equipamiento_id) {
            die("Error: No se encontró una relación válida en espacios_equipamiento.");
        }

        $sql = "INSERT INTO reportes_equipamiento (id_usuario, espacio_equipamiento_id, espacio_id, estado, descripcion) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiiss", $id_usuario, $real_espacio_equipamiento_id, $espacio_id, $nuevo_estado, $descripcion);

        if ($stmt->execute()) {
            // 🔹 Paso 4: Actualizar el estado en espacios_equipamiento
            $update_sql = "UPDATE espacios_equipamiento SET estado = ? WHERE id = ?";
            $stmt_update = $conexion->prepare($update_sql);
            $stmt_update->bind_param("si", $nuevo_estado, $real_espacio_equipamiento_id);
            
            if ($stmt_update->execute()) {
                echo "Reporte guardado y estado actualizado correctamente.";
            } else {
                echo "Error al actualizar el estado en espacios_equipamiento: " . $stmt_update->error;
            }
        
            $stmt_update->close();
        
            // 🔹 Redireccionar para evitar reenvío de formulario
            header("Location: update_spaces.php?id=" . urlencode($espacio_id));
            exit();
        } else {
            die("Error en la ejecución de la consulta: " . $stmt->error);
        }

        $stmt->close();
        $conexion->close();
    }

    ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Espacio - Admin</title>

    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/update_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
</head>

<body class="admin">

<div class="container-docente">
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
                                    class="<?php echo $currentFile == 'register_buildings.php' ? 'active' : ''; ?>">
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
                            <li><a href="table_equipment_reports.php"
                                    class="<?php echo $currentFile == 'table_equipment_reports.php' ? 'active' : ''; ?>">
                                    <ion-icon name="calendar-outline"></ion-icon> Reportes equipamientos
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
<button type="button" id="cancel-edit-btn" class="cancel-btn">
<i class="fas fa-times"></i> Cancelar
</button>

<button type="submit" form="edit-form" class="save-btn">
<i class="fa-solid fa-floppy-disk"></i> Guardar
</button>
</div>
</div>

</div>
<form id="edit-form" method="POST" enctype="multipart/form-data">

<input type="hidden" name="update_spaces" value="1">
<input type="hidden" name="id" value="<?php echo $id['id']; ?>">

<div class="rectangle">
    <div class="half">
        <div class="image-container space-image">

            <img src="<?php echo htmlspecialchars($id['imagen']); ?>"
                class="building-image">

            <!-- BOTONES SOBRE IMAGEN -->
            <div class="image-overlay-buttons">

                <button type="button" class="button-space" onclick="openModal()">
                    
                    <span class="icon-circle reservar">
                        <i class="ti ti-calendar-plus"></i>
                    </span>
                    <span>Añadir equipamiento</span>
                </button>

                <a href="disponibilidad_spaces.php?id_espacio=<?php echo $id['id']; ?>"
                    class="button-avail">
                    <span class="icon-circle disponible">
                        <i class="ti ti-list-check"></i>
                    </span>
                    <span>Disponibilidad</span>
                </a>

            </div>

            <!-- CAMBIAR IMAGEN (SOLO EDICIÓN) -->
            <label for="imagen" class="change-image-btn">
                                    <i class="fa-solid fa-camera camera-icon"></i>
                                    <div class="change-image-text">Cambiar imagen</div>
                                    <div class="subtext">Haz clic para seleccionar</div>
                                </label>
                                <input type="file" id="imagen" name="imagen" class="file-input" accept="image/*">

        </div>
    </div>

<div class="half">

<h1 class="building-title">
<span class="hidden-in-edit-mode">
    Espacio <?php echo htmlspecialchars($id['codigo']); ?>
</span>

<!-- MODO EDICIÓN -->
<input type="text" name="codigo"
value="<?php echo htmlspecialchars($id['codigo']); ?>"
class="editable-field hidden-in-view-mode">

</h1>

<div class="tabs">
<button type="button" class="tab-button active"
data-tab="info">
<i class="fa-solid fa-circle-info"></i> Información
</button>

<button type="button" class="tab-button"
data-tab="equipamiento">
<i class="ti ti-tool"></i> Equipamiento
</button>
</div>

<!-- TAB INFORMACIÓN -->
<div class="tab-content" id="info">

    <div class="detail-item-descript-spaces">
    <div class="details-grid-descrip">
                 <div class="collapsible-content">
                    <p class="hidden-in-edit-mode">
                        <?php echo htmlspecialchars($id['descripcion_general']); ?></p>
                    <textarea name="descripcion_general"
                    class="editable-textarea hidden-in-view-mode">
                    <?php echo htmlspecialchars($id['descripcion_general']); ?>
                    </textarea>
                </div>
        </div>
    </div>

    <div class="details-grid">
            <div class="detail-item">
                <i class="ti ti-users"></i>
            <div>
            <span class="detail-label">Capacidad</span>

            <span class="detail-value hidden-in-edit-mode">
            <?php echo $id['capacidad']; ?>
            </span>

            <input type="number" name="capacidad"
            value="<?php echo $id['capacidad']; ?>"
            class="editable-field hidden-in-view-mode">
            </div>
            </div>
                <div class="detail-item">
                    <i class="ti ti-building"></i>
                <div>
            <span class="detail-label">Edificio</span>
            <span class="detail-value">
            <?php echo htmlspecialchars($edificio['nombre']); ?>
            </span>
        </div>
    </div>
    <div class="detail-item">
            <i class="ti ti-hash"></i>
            <div>
                <span class="detail-label">Código</span>

                <span class="detail-value hidden-in-edit-mode">
                    <?php echo htmlspecialchars($id['codigo']); ?>
                </span>

                <input type="text" name="codigo"
                value="<?php echo htmlspecialchars($id['codigo']); ?>"
                class="editable-field hidden-in-view-mode">
            </div>
        </div>

</div>

</div>

<!-- TAB EQUIPAMIENTO (SIN CAMBIOS) -->
<div class="tab-content" id="equipamiento" style="display:none">

<h3 class="section-title">Equipamientos del espacio</h3>

<div class="grid-container equipamientos-grid">

<?php
$espacio_id = mysqli_real_escape_string($conexion, $_GET['id']);

$query_show_equip = "
SELECT e.id, e.nombre, e.imagen, ee.cantidad, ee.estado
FROM equipamiento e
JOIN espacios_equipamiento ee
ON e.id = ee.equipamiento_id
WHERE ee.espacio_id = '$espacio_id'
";

$resultado_equip = mysqli_query($conexion, $query_show_equip);

while ($equipamiento = mysqli_fetch_assoc($resultado_equip)) {
?>

<div class="grid-item">
<div class="equipamiento-container"  onclick="abrirModalReporte('<?php echo $equipamiento['id']; ?>','<?php echo $espacio_id; ?>')">

<img src="<?php echo $equipamiento['imagen']; ?>"class="equipamiento-img_select">

<div class="equipamiento-info
                                <?php echo strtolower(str_replace(' ', '-', $equipamiento['estado'])); ?>">

                                <p><?php echo $equipamiento['nombre']; ?></p>
                                <p class="cantidad">Cantidad: <?php echo $equipamiento['cantidad']; ?></p>
                                <p>Estado: <?php echo $equipamiento['estado']; ?></p>
                            </div>

</div>
</div>

<?php } ?>

</div>
</div>

</div>
</form>

</div>
</div>
    <div class="modal" id="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Selecciona el equipamiento</h2>
            <form id="equipamiento-form" method="POST" enctype="multipart/form-data">
                <div class="equipamiento-grid">
                    <input type="hidden" name="id" value="<?php echo $id['id']; ?>">
                    <input type="hidden" name="equipment_spaces" value="true">
                    <?php
                    $espacio_id = mysqli_real_escape_string($conexion, $_GET['id']);
                    $query_equipamientos = "SELECT e.id, e.nombre, e.imagen
                    FROM equipamiento e";

                    $resultado_equipamientos = mysqli_query($conexion, $query_equipamientos);
                    
                    while ($equipamiento = mysqli_fetch_assoc($resultado_equipamientos)) {
                        echo '
                        <div class="equipamiento-item">
                        <img src="' . htmlspecialchars($equipamiento['imagen']) . '" alt="' . htmlspecialchars($equipamiento['nombre']) . '" class="equipamiento-img">
                        <p>' . htmlspecialchars($equipamiento['nombre']) . '</p>
                        <input type="number" name="cantidad[' . htmlspecialchars($equipamiento['id']) . ']" min="0" placeholder="Cantidad">
                        <label for="estado">Estado:</label>
                            <select id="estado" name="estado[' .htmlspecialchars($equipamiento['id']). ']"> 
                                <option value="">Seleccione el estado</option>
                                <option value="Disponible" ' . (($equipamiento['id'] === 'Disponible') ? 'selected' : '') . '>Disponible</option>
                                <option value="En Mantenimiento" ' . (($equipamiento['id'] === 'En Mantenimiento') ? 'selected' : '') . '>En Mantenimiento</option>
                                <option value="No Disponible" ' . (($equipamiento['id'] === 'No Disponible') ? 'selected' : '') . '>No Disponible</option>
                            </select>
                    </div>';
                    }
                    ?>
                </div>
                <button type="submit" class="modal-button">Añadir</button>
            </form>
        </div>
        </div>
<!-- Modal -->
    <div id="modalReporteEquipamiento" class="modal">   
        <div class="modal-content">
            <span class="close" onclick="cerrarModalReporte()">&times;</span>
            <h2>Reporte Equipamiento</h2>
            <form id="reporteEquipamientoForm" method="POST">
                <input type="hidden" id="id_usuario" name="id_usuario">
                <input type="hidden" id="espacio_id" name="espacio_id">
                <input type="hidden" id="espacio_equipamiento_id" name="espacio_equipamiento_id">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado">
                    <option value="Disponible">Disponible</option>
                    <option value="En Mantenimiento">En Mantenimiento</option>
                    <option value="No Disponible">No Disponible</option>
                </select>
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                <button type="submit">Guardar Reporte</button>
            </form>
        </div>
    </div>

<!-- JS DE EDICIÓN (EL MISMO DE EDIFICIOS) -->
<script src="../../assets/js/building_edit.js"></script>

<script>
document.querySelectorAll('.tab-button').forEach(btn=>{
btn.addEventListener('click',()=>{
document.querySelectorAll('.tab-button')
.forEach(b=>b.classList.remove('active'));

document.querySelectorAll('.tab-content')
.forEach(c=>c.style.display='none');

btn.classList.add('active');
document.getElementById(btn.dataset.tab).style.display='block';
});
});
</script>
<script>
        function openModal() {
                document.getElementById("modal").style.display = "block";
            }
        function cerrarModal(){
            document.getElementById("modal").style.display = "none";
        }
            // Cerrar el modal cuando se haga clic fuera del modal
        window.onclick = function(event) {
                if (event.target === document.getElementById("modal")) {
                    document.getElementById("modal").style.display = "none";
                }
        }
        function abrirModalReporte(equipamiento_id, espacioId) {
            console.log("Abriendo modal para ID:", equipamiento_id); 
            document.getElementById("espacio_equipamiento_id").value = equipamiento_id;
            document.getElementById("espacio_id").value = espacioId;
            document.getElementById("modalReporteEquipamiento").style.display = "block";
            document.getElementById("modal-reject").style.display = "block";
        }

        function cerrarModalReporte() {
            document.getElementById("modalReporteEquipamiento").style.display = "none";
        }

        // Cerrar el modal cuando se haga clic fuera del contenido
        window.onclick = function(event) {
            let modal = document.getElementById("modalReporteEquipamiento");
            if (event.target === modal) {
                cerrarModalReporte();
            }
        }
    </script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>
