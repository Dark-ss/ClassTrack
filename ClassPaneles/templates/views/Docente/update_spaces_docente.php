<?php
require_once '../../php/conexion_be.php';
include '../../php/docente_session.php';

// Verificar si se recibió un ID válido
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un espacio válido.'); window.location.href='vista_spaces_docente.php';</script>";
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
    echo "<script>alert('Espacio de edificio no encontrado.'); window.location.href='vista_spaces_docente.php';</script>";
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
            echo "<script>alert('Descripción actualizada con éxito.'); window.location.href='vista_space
            s_docente.php';</script>";
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
            echo "<script>alert('Espacio actualizado con éxito.'); window.location.href='vista_spaces_docente.php';</script>";
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
$query = "SELECT nombre FROM edificios WHERE id = '$building_id'";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $edificio = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_spaces_docente.php';</script>";
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['reserve_space'])) {
    $id_reservacion = $_POST['id'];  
    $id_usuario = $_POST['id_usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_final = $_POST['fecha_final'];
    $tipo_reservacion = $_POST['tipo_reservacion'];
    $descripcion = $_POST['descripcion'];
    $id_espacio = $_POST['id_espacio'];
    $estudiantes = $_POST['estudiantes'];

    if (empty($estudiantes) || count($estudiantes) < 1) {
    echo "<script>alert('Debe añadir al menos un estudiante para realizar la reserva.'); window.history.back();</script>";
    exit;
    }

    date_default_timezone_set('America/Bogota');
    $fecha_actual = date('Y-m-d H:i:s');

    if (strtotime($fecha_inicio) < strtotime($fecha_actual)) {
        echo "<script>alert('La fecha no es la actual, no se puede hacer la reserva.'); window.history.back();</script>";
        exit;
    }

    // Verificar conflictos de reserva
    $query_verificar = "
    SELECT * 
    FROM reservaciones 
    WHERE id_espacio = '$id_espacio'
    AND estado = 'aceptada'
    AND (
        ('$fecha_inicio' BETWEEN fecha_inicio AND fecha_final) OR
        ('$fecha_final' BETWEEN fecha_inicio AND fecha_final) OR
        (fecha_inicio BETWEEN '$fecha_inicio' AND '$fecha_final') OR
        (fecha_final BETWEEN '$fecha_inicio' AND '$fecha_final')
    )AND NOT (
        '$fecha_inicio' = fecha_final OR '$fecha_final' = fecha_inicio
    )";

    $resultado_verificar = mysqli_query($conexion, $query_verificar);

    if (mysqli_num_rows($resultado_verificar) > 0) {
        echo "<script>alert('El espacio ya está reservado en el rango de fechas y horas especificado.'); window.history.back();</script>";
        exit;
    }

    // Insertar la reserva con estado "Pendiente"
    $query = "INSERT INTO reservaciones (id_usuario, fecha_inicio, fecha_final, tipo_reservacion, descripcion, id_espacio, estado) 
        VALUES ('$id_usuario', '$fecha_inicio', '$fecha_final', '$tipo_reservacion', '$descripcion', '$id_espacio', 'Pendiente')";
    if (!mysqli_query($conexion, $query)) {
        die("Error al insertar la reservación: " . mysqli_error($conexion));
    }

    $id_reservacion = mysqli_insert_id($conexion);

    // Insertar los estudiantes asociados a la reserva
    foreach ($estudiantes as $id_estudiante) {
        $query_validar = "SELECT id FROM estudiantes WHERE id = '$id_estudiante'";
        $resultado_validar = mysqli_query($conexion, $query_validar);

        if (mysqli_num_rows($resultado_validar)) { 
            $query_estudiante = "INSERT INTO reservaciones_estudiantes (id_reservacion, id_estudiante) 
                VALUES ('$id_reservacion', '$id_estudiante')";
            if (!mysqli_query($conexion, $query_estudiante)) {
                die("Error al insertar estudiante: " . mysqli_error($conexion));
            }
        }
    }

    echo "<script>alert('Solicitud de reserva enviada para aprobación.'); window.location.href='update_spaces_docente.php?id=" . $space_id . "';</script>";
    exit();
}

$query_reserva = "SELECT codigo FROM espacios_academicos WHERE id = $space_id";
$result_reserva = mysqli_query($conexion, $query_reserva);

if ($result_reserva && mysqli_num_rows($result_reserva) > 0) {
    $espacio_reserva = mysqli_fetch_assoc($result_reserva);
}   else {
    echo "<script>alert('Espacio no encontrado. ID: $space_id'); window.location.href='update_spaces_docente.php';</script>";
    exit;
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

    $sql = "INSERT INTO solicitudes_reporte_docente (id_usuario, espacio_equipamiento_id, espacio_id, estado, descripcion) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
    die("Error al preparar la sentencia: " . $conexion->error);
}
    $stmt->bind_param("iiiss", $id_usuario, $real_espacio_equipamiento_id, $espacio_id, $nuevo_estado, $descripcion);

    if ($stmt->execute()) {
        echo "Solicitud enviada correctamente.";
        header("Location: update_spaces_docente.php?id=" . urlencode($espacio_id) . "&reporte=enviado");
        exit();
    } else {
        die("Error en la ejecución de la consulta: " . $stmt->error);
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Espacio</title>

    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/update_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
</head>

<body class="docente">

<div class="container-docente">
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
                    <p class="menu-title">Ayuda</p>
                    <ul>
                        <li><a href="suport.php"
                                class="<?php echo $currentFile == 'suport.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Soporte técnico
                            </a></li>
                    </ul>
                    <ul>
                        <li><a href="mis_solicitudes.php"
                                class="<?php echo $currentFile == 'mis_solicitudes.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis solicitudes
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
                        <li><a href="../../php/cerrar_session_docente.php"
                                class="<?php echo $currentFile == 'cerrar_session_docente.php' ? 'active' : ''; ?>">
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
        <button class="back-button" onclick="window.history.back()">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </button>
    </div>
    <div class="rectangle">
        <div class="half">
            <div class="building-showcase">
                <div class="image-container space-image">
                    <img src="<?php echo htmlspecialchars($id['imagen']); ?>" class="building-image">
                    <div class="image-overlay-buttons">
                            <button type="button" class="button-space" onclick="openModal()">
                            <span class="icon-circle reservar">
                                <i class="ti ti-calendar-plus"></i>
                            </span>
                            <span>Reservar</span>
                        </button>
                            <a href="ver_disponibilidad.php?id_espacio=<?php echo $id['id']; ?>"class="button-avail"> <span class="icon-circle disponible">
                        <i class="ti ti-list-check"></i>
                            </span>
                            <span>Disponibilidad</span></a>
                    </div>

                </div>
            </div>
    </div>


    <div class="half">
        <div class="tabs-container">

        <h1 class="building-title">
            Espacio <?php echo htmlspecialchars($id['codigo']); ?>
        </h1>

        <div class="tabs">
            <button class="tab-button active" data-tab="info">
                <i class="fa-solid fa-circle-info"></i> Información
            </button>
            <button class="tab-button" data-tab="equipamiento">
                <i class="ti ti-tool"></i> Equipamiento
            </button>
        </div>


        <div class="tab-content" id="info">
            <div class="details-grid-descrip">
                <div class="detail-item-descript">
                    <p><?php echo htmlspecialchars($id['descripcion_general']); ?></p>
                </div>
            </div>
            <div class="details-grid">
                <div class="detail-item">
                    <i class="ti ti-users"></i>
                    <div>
                        <span class="detail-label">Capacidad</span>
                        <span class="detail-value"><?php echo $id['capacidad']; ?></span>
                    </div>
                </div>

                <div class="detail-item">
                    <i class="ti ti-building"></i>
                    <div>
                        <span class="detail-label">Edificio</span>
                        <span class="detail-value"><?php echo htmlspecialchars($edificio['nombre']); ?></span>
                    </div>
                </div>
            </div>
            <div class="details-grid">
                <div class="detail-item">
                <i class="ti ti-qrcode"></i>
                    <div>
                        <span class="detail-label">Codigo</span>
                        <span class="detail-value"><?php echo htmlspecialchars($id['codigo']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB EQUIPAMIENTO -->
        <div class="tab-content" id="equipamiento" style="display:none">

        <h3 class="section-title">Equipamientos del espacio</h3>

        <div class="grid-container equipamientos-grid">

        <?php
        $espacio_id = mysqli_real_escape_string($conexion, $_GET['id']);
        $query_show_equip = "
        SELECT e.id, e.nombre, e.imagen, ee.cantidad, ee.estado
        FROM equipamiento e
        JOIN espacios_equipamiento ee ON e.id = ee.equipamiento_id
        WHERE ee.espacio_id = '$espacio_id'
        ";

        $resultado_equip = mysqli_query($conexion, $query_show_equip);

        while ($equipamiento = mysqli_fetch_assoc($resultado_equip)) {
        ?>
        <div class="grid-item">
            <div class="equipamiento-container"
                onclick="abrirModalReporte(
                    '<?php echo $equipamiento['id']; ?>',
                    '<?php echo $espacio_id; ?>'
                )">

                <img src="<?php echo $equipamiento['imagen']; ?>"
                    class="equipamiento-img_select">

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
</div>

</div>
</div>
</div>
<div class="modal" id="modal">
    <div class="modal-content">
    <h2>Formulario de Reserva</h2>
        <form id="reserve-form" method="POST">
            <input type="hidden" name="reserve_space" value="true">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id['id']); ?>">
            <input type="hidden" name="id_espacio" value="<?php echo htmlspecialchars($space_id); ?>">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario) ?>">

            <div class="form-group-container">
                <div class="form-group">
                    <label for="id_usuario">Nombre Del Solicitante:</label>
                    <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($espacio_usuario['nombre_completo']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_inicio">Fecha y hora de inicio:</label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
                </div>
            </div>
            <div class="form-group-container">
                <div class="form-group">
                    <label for="fecha_final">Fecha y hora de fin:</label>
                    <input type="datetime-local" id="fecha_final" name="fecha_final" required>
                </div>

                <div class="form-group">
                <label for="tipo_reservacion">tipo de reservacion:</label>
                    <select id="tipo_reservacion" name="tipo_reservacion" required>
                        <option value="">Seleccione el tipo</option>
                        <option value="Clase">Clase</option>
                        <option value="Reunion">Reunion</option>
                        <option value="Evento">Evento</option>
                    </select>
                </div>
            </div>
            <div class="form-group-container">
                <div class="form-group">
                        <label for="descripcion">Descripción reserva:</label>
                        <textarea id="descripcion" name="descripcion" class="description-register" rows="4" required></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label for="estudiantes">Añadir Estudiantes:</label>
                <input type="text" id="estudiantes" name="estudiantes[]" placeholder="Buscar estudiante..." autocomplete="off">
                <ul id="student-list"></ul>
                <div id="selected-students"></div>
            </div>
            <div class="form-group-container">
                <div class="form-group">
                    <label>Espacio:</label>
                    <input type="number" value="<?php echo htmlspecialchars($espacio_reserva['codigo']); ?>">
                </div>
            </div>
                <button class="tab-button active-reserv" type="submit">Reservar espacio</button>
        </form>
    </div>
    </div>
<div id="modalReporteEquipamiento" class="modal">   
    <div class="modal-content">
        <span class="close" onclick="cerrarModalReporte()">&times;</span>
        <h2>Reporte Equipamiento</h2>
        <form id="reporteEquipamientoForm" method="POST">
            <input type="hidden" id="id_usuario" name="id_usuario">
            <input type="hidden" id="espacio_id" name="espacio_id">
            <input type="hidden" id="espacio_equipamiento_id" name="espacio_equipamiento_id">
            <label for="estado">Estado:</label>
            <div class="form-group-container">
                <div class="form-group">
                <select id="estado" name="estado">
                    <option value="Disponible">Disponible</option>
                    <option value="En Mantenimiento">En Mantenimiento</option>
                    <option value="No Disponible">No Disponible</option>
                </select>
                </div>
            </div>
            <div class="form-group-container">
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4"></textarea>
                </div>
            </div>
            <button class="tab-button active-reserv" type="submit">Guardar Reporte</button>
        </form>
    </div>
</div>
<script>
function openModal() {
    document.getElementById("modal").style.display = "block";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

window.onclick = function (event) {
    const modalReserva = document.getElementById("modal");
    const modalReporte = document.getElementById("modalReporteEquipamiento");

    if (event.target === modalReserva) {
        closeModal();
    }

    if (event.target === modalReporte) {
        modalReporte.style.display = "none";
    }
};
</script>

<script>
function abrirModalReporte(equipamiento_id, espacioId) {
    console.log("Abriendo modal para ID:", equipamiento_id);

    document.getElementById("espacio_equipamiento_id").value = equipamiento_id;
    document.getElementById("espacio_id").value = espacioId;

    document.getElementById("modalReporteEquipamiento").style.display = "block";
    
}
function cerrarModalReporte() {
    document.getElementById("modalReporteEquipamiento").style.display = "none";
}

window.onclick = function(event) {
    const modalReserva = document.getElementById("modal");
    const modalReporte = document.getElementById("modalReporteEquipamiento");

    if (event.target === modalReserva) {
        modalReserva.style.display = "none";
    }

    if (event.target === modalReporte) {
        modalReporte.style.display = "none";
    }
}
</script>

<script>
document.querySelectorAll('.tab-button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).style.display = 'block';
    });
});
</script>

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

document.addEventListener('DOMContentLoaded', function () {
    function eventQueryStudents() {
        const query = this.value.trim();
        const studentList = document.getElementById('student-list');
        const selectedStudents = document.getElementById('selected-students');

        if (query.length < 3) {
            studentList.innerHTML = '';
            return;
        }

        // Use the new endpoint
        fetch(`buscar_estudiantes.php?query=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                studentList.innerHTML = '';

        if (Array.isArray(data) && data.length > 0) {
            const ul = document.createElement('ul');
            ul.style.listStyle = 'none';
            ul.style.padding = '0';
            ul.style.margin = '0';
            ul.style.border = '1px solid #ddd';
            ul.style.borderRadius = '4px';
            ul.style.maxHeight = '200px';
            ul.style.overflowY = 'auto';

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = student.nombre_completo;
                li.dataset.id = student.id;
                li.style.padding = '8px';
                li.style.cursor = 'pointer';
                li.style.borderBottom = '1px solid #eee';

                li.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f0f0f0';
                });
                
                li.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
                
        li.addEventListener('click', function() {
        if (!selectedStudents) {
            console.error("El contenedor 'selected-students' no existe.");
            return;
        }

        const existingStudent = selectedStudents.querySelector(`input[value="${student.id}"]`);
        if (!existingStudent) {
            console.log("selectedStudents:", selectedStudents);
            agregarEstudianteSeleccionado(this);
            studentList.innerHTML = '';
            document.getElementById('estudiantes').value = '';
        }
        });

            ul.appendChild(li);
        });
                    
            studentList.appendChild(ul);
    } else {
            const noResults = document.createElement('p');
            noResults.textContent = 'No se encontraron estudiantes';
            noResults.style.padding = '8px';
            noResults.style.color = '#666';
            studentList.appendChild(noResults);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            studentList.innerHTML = '<p style="color: red; padding: 8px;">Error al buscar estudiantes</p>';
        });
    }

function agregarEstudianteSeleccionado(item) {
    const selectedStudents = document.getElementById('selected-students');

    if (!selectedStudents) {
        console.error("El contenedor 'selected-students' no está disponible en el DOM.");
        return;
    }

    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.margin = '5px 0';
    container.style.padding = '5px';
    container.style.backgroundColor = '#e9ecef';
    container.style.borderRadius = '4px';

    const span = document.createElement('span');
    span.textContent = item.textContent;

    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'estudiantes[]';
    inputHidden.value = item.dataset.id;

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.textContent = '×';
    removeButton.style.marginLeft = '10px';
    removeButton.style.cursor = 'pointer';
    removeButton.style.border = 'none';
    removeButton.style.background = 'none';
    removeButton.style.color = 'red';
    removeButton.style.fontWeight = 'bold';
    removeButton.onclick = function () {
        container.remove();
    };

    container.appendChild(span);
    container.appendChild(inputHidden);
    container.appendChild(removeButton);

    selectedStudents.appendChild(container);
}

    // Add debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add event listener with debounce
    document.getElementById('estudiantes').addEventListener('input', 
        debounce(eventQueryStudents, 300)
    );
});
</script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>