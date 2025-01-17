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

// Add this at the very top of your PHP file, right after the session and connection includes
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['reserve_space'])) {
    $id_reservacion = $_POST['id'];  // ID de la reservación
    $id_usuario = $_POST['id_usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_final = $_POST['fecha_final'];
    $tipo_reservacion = $_POST['tipo_reservacion'];
    $descripcion = $_POST['descripcion'];
    $id_espacio = $_POST['id_espacio'];
    $estudiantes = $_POST['estudiantes'];

    // Inserción de la reservación
    $query = "INSERT INTO reservaciones (id_usuario, fecha_inicio, fecha_final, tipo_reservacion, descripcion, id_espacio) 
            VALUES ('$id_usuario', '$fecha_inicio', '$fecha_final', '$tipo_reservacion', '$descripcion', '$id_espacio')";
    if (!mysqli_query($conexion, $query)) {
        die("Error al insertar la reservación: " . mysqli_error($conexion));
    }

    $id_reservacion = mysqli_insert_id($conexion);

    foreach ($estudiantes as $id_estudiante) {
        $query_validar = "SELECT id FROM estudiantes WHERE id = '$id_estudiante'";
        $resultado_validar = mysqli_query($conexion, $query_validar);

        if (mysqli_num_rows($resultado_validar)) { // Solo insertar si el estudiante existe
            $query_estudiante = "INSERT INTO reservaciones_estudiantes (id_reservacion, id_estudiante) 
                    VALUES ('$id_reservacion', '$id_estudiante')";
            if (!mysqli_query($conexion, $query_estudiante)) {
                die("Error al insertar estudiante: " . mysqli_error($conexion));
            }
        }
    }

    echo "<script>alert('Reserva realizada con éxito.'); window.location.href='update_spaces_docente.php?id=" . $space_id . "';</script>";

    exit();
}


// Validar si el ID corresponde a un edificio existente
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información espacio</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
    <link rel="stylesheet" href="../../assets/css/style_teacher.css?v=1.0">
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
            
            <a href="../../php/config_docente.php" class="config">                    
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="docente_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>

            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">
                    Espacios<span>▼</span>
                </div>  
                <div class="submenu" id="submenu">
                    <a href="vista_buildings.php">Edificios</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>

        <div class="container-description-image" style="display: flex">
            <div class="image-container">
                <h1 class="title_build"><?php echo htmlspecialchars($id['codigo']); ?></h1>
                <img src="<?php echo htmlspecialchars($id['imagen']); ?>" alt="Edificio" class="profile-img-build">
                <button type="button" class="button-space" onclick="openModal()">Reservar</button>
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

    <div id="equipamientos-seleccionados">
    <form id="equipamiento-form" method="POST" enctype="multipart/form-data">
    <h3>Equipamientos Añadidos al Espacio</h3>
    <div class="grid-container">
        <input type="hidden" name="id" value="<?php echo $id['id']; ?>">
        <input type="hidden" name="equipment_spaces" value="true">
        <?php   
        $espacio_id = mysqli_real_escape_string($conexion, $_GET['id']);
        // Recuperar los equipamientos asignados al espacio
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
                    <div class="equipamiento-container">
                        <img src="' . htmlspecialchars($equipamiento['imagen']) . '" alt="' . htmlspecialchars($equipamiento['nombre']) . '" class="equipamiento-img_select">
                        <div class="equipamiento-info ' . strtolower(str_replace(' ', '-', $equipamiento['estado'])) . '">
                            <p>' . htmlspecialchars($equipamiento['nombre']) . '</p>
                            <p class="cantidad">Cantidad: ' . htmlspecialchars($equipamiento['cantidad']) . '</p>
                            <p>Estado: ' . htmlspecialchars($equipamiento['estado']) . '</p>
                        </div>
                    </div>
                </div>';
            }
        }
        ?>
    </div>
    </form>
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
                    <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($espacio_usuario['nombre_completo']); ?>">
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
            <div class="form-group">
                <button type="submit">Reservar espacio</button>
            </div>
        </form>
    </div>
    </div>
</main>
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
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</body>

</html>