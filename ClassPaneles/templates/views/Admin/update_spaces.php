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

//reportes equipamiento
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $espacio_equipamiento_id = isset($_POST['espacio_equipamiento_id']) ? (int) $_POST['espacio_equipamiento_id'] : 0;
    $espacio_id = isset($_POST['espacio_id']) ? (int) $_POST['espacio_id'] : 0;
    $nuevo_estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
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

    $sql = "INSERT INTO reportes_equipamiento (espacio_equipamiento_id, espacio_id, estado, descripcion) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiss", $real_espacio_equipamiento_id, $espacio_id, $nuevo_estado, $descripcion);

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
                <a href="equipment.php">Equipamientos</a>
                <a href="vista_students.php">Salones</a>
            </div>
        </div>
    </div>
    <div class="container-description-image" style="display: flex">
        <div class="image-container">
            <h1 class="title_build"><?php echo htmlspecialchars($id['codigo']); ?></h1>
            <img src="<?php echo htmlspecialchars($id['imagen']); ?>" alt="Espacio" class="profile-img-build">
            <button type="button" class="button-space" onclick="openModal()">Añadir equipamiento</button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="description-form" sytle="flex-direction: column">
            <input type="hidden" name="update_description_space" value="true">
            <div class="build-description">
                <label for="descripcion_general" class="title_description">Descripción General</label>
                <textarea id="descripcion_general" name="descripcion_general" class="description-textarea" rows="10" cols="5" disabled><?php echo htmlspecialchars($id['descripcion_general']); ?></textarea>
            </div>
            <button type="button" id="edit-button-description_space" class="update-button-description" onclick="enableEditingDescriptionSpace()">Actualizar</button>
            <button type="submit" id="save-button-description_space" class="save-button-description" style="display: none;">Guardar Cambios</button>
        </form>
    </div>

    <div class="container-form_register_build">
        <h2>Información de espacio</h2>
        <form id="update-form-build_spaces" method="POST" enctype="multipart/form-data">
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

                <div class="form-group-build">
                    <label for="imagen">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" disabled>
                </div>
            </div>

            <div class="buttons-form-container">
                <button type="button" id="edit-button-Space" class="update-button" onclick="enableEditingSpace()">Actualizar</button>
                <button type="submit" id="save-button-Space" class="save-button" style="display: none;">Guardar Cambios</button>
            </div>
        </form>
    </div>
    
    <div class="modal" id="modal">
    <div class="modal-content">
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

    <div id="equipamientos-seleccionados">
    <form id="equipamiento-form" method="POST" enctype="multipart/form-data">
        <h3>Equipamientos Añadidos al Espacio</h3>
        <div class="grid-container">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="equipment_spaces" value="true">
            
            <?php   
            $espacio_id = mysqli_real_escape_string($conexion, $_GET['id']);
            $query_show_equip = "SELECT e.id, e.nombre, e.imagen, ee.cantidad, ee.estado
                FROM equipamiento e
                JOIN espacios_equipamiento ee ON e.id = ee.equipamiento_id
                WHERE ee.espacio_id = '$espacio_id'";

            $resultado_equip = mysqli_query($conexion, $query_show_equip);

            if ($resultado_equip === false) {
                echo "Error en la consulta: " . mysqli_error($conexion);
            } else {
                while ($equipamiento = mysqli_fetch_assoc($resultado_equip)) {
                    echo '                
                    <div class="grid-item">
                        <div class="equipamiento-container" onclick="abrirModalReporte(\'' . htmlspecialchars($equipamiento['id'], ENT_QUOTES, 'UTF-8') . '\', \'' . htmlspecialchars($espacio_id, ENT_QUOTES, 'UTF-8') . '\')">
                            <img src="' . htmlspecialchars($equipamiento['imagen'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($equipamiento['nombre'], ENT_QUOTES, 'UTF-8') . '" class="equipamiento-img_select">
                            <div class="equipamiento-info ' . strtolower(str_replace(' ', '-', $equipamiento['estado'])) . '">
                                <p>' . htmlspecialchars($equipamiento['nombre'], ENT_QUOTES, 'UTF-8') . '</p>
                                <p class="cantidad">Cantidad: ' . htmlspecialchars($equipamiento['cantidad'], ENT_QUOTES, 'UTF-8') . '</p>
                                <p>Estado: ' . htmlspecialchars($equipamiento['estado'], ENT_QUOTES, 'UTF-8') . '</p>
                            </div>
                        </div>
                    </div>';                
                }
            }
            ?>
        </div>
    </form>
</div>

<!-- Modal -->
<div id="modalReporteEquipamiento" class="modal">   
    <div class="modal-content">
        <span class="close" onclick="cerrarModalReporte()">&times;</span>
        <h2>Reporte Equipamiento</h2>
        <form id="reporteEquipamientoForm" method="POST">
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

</main>

<!-- Script -->
<script>
    function openModal() {
            document.getElementById("modal").style.display = "block";
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

<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</body>
</html>