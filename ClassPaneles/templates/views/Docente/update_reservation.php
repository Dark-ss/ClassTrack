<?php
include '../../php/docente_session.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); 
    exit();
}
include '../../php/conexion_be.php';

$id_reservacion = isset($_GET['id']) ? $_GET['id'] : null;


$id_usuario = $_SESSION['id_usuario'];

// Obtener datos de la reserva
$query_reserva = "SELECT r.id, r.fecha_inicio, r.fecha_final, r.tipo_reservacion, r.descripcion, r.id_espacio, GROUP_CONCAT(re.id_estudiante) AS estudiantes
    FROM reservaciones r
    JOIN espacios_academicos e ON r.id_espacio = e.id
    JOIN reservaciones_estudiantes re ON r.id = re.id_reservacion
    WHERE r.id = '$id_reservacion' AND r.id_usuario = '$id_usuario'
    GROUP BY r.id
";
$resultado_reserva = mysqli_query($conexion, $query_reserva);

$reserva = mysqli_fetch_assoc($resultado_reserva);

// Obtener espacios académicos
$query_espacios = "SELECT id, codigo FROM espacios_academicos";
$resultado_espacios = mysqli_query($conexion, $query_espacios);

// Obtener lista de estudiantes
$query_estudiantes = "SELECT id, nombre_completo FROM estudiantes";
$resultado_estudiantes = mysqli_query($conexion, $query_estudiantes);

//procesar edicion reserva

if (isset($_POST['reservation_update'])) {
    $id_reservacion = $_POST['id_reservacion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_final = $_POST['fecha_final'];
    $tipo_reservacion = $_POST['tipo_reservacion'];
    $descripcion = $_POST['descripcion'];
    $id_espacio = $_POST['id_espacio'];
    $estudiantes = $_POST['estudiantes'];

    date_default_timezone_set('America/Bogota');
    $fecha_actual = date('Y-m-d H:i:s');
    // Validar fechas
    if (strtotime($fecha_inicio) < strtotime($fecha_actual)) {
        echo "<script>alert('La fecha no es válida.'); window.history.back();</script>";
        exit;
    }
    // Actualizar la reservación
    $query_update = "UPDATE reservaciones SET
    fecha_inicio = '$fecha_inicio', 
    fecha_final = '$fecha_final', 
    tipo_reservacion = '$tipo_reservacion', 
    descripcion = '$descripcion', 
    id_espacio = '$id_espacio'
    WHERE id = '$id_reservacion' AND id_usuario = '$id_usuario'
    ";
    if (!mysqli_query($conexion, $query_update)) {
        die("Error al actualizar la reservación: " . mysqli_error($conexion));
    }

    // Actualizar estudiantes
    $query_delete_estudiantes = "DELETE FROM reservaciones_estudiantes WHERE id_reservacion = '$id_reservacion'";
    mysqli_query($conexion, $query_delete_estudiantes);

    foreach ($estudiantes as $id_estudiante) {
        $query_insert_estudiante = "
            INSERT INTO reservaciones_estudiantes (id_reservacion, id_estudiante) 
            VALUES ('$id_reservacion', '$id_estudiante')
        ";
        if (!mysqli_query($conexion, $query_insert_estudiante)) {
            die("Error al actualizar estudiantes: " . mysqli_error($conexion));
        }
    }

    echo "<script>alert('Reserva actualizada con éxito.'); window.location.href='mis_reservas.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
    <link rel="stylesheet" href="../../assets/css/style_teacher.css?v=1.0">
</head>
<body>
    <div class="modal-content">
        <h2>Editar Reserva</h1>
        <form action="update_reservation.php" method="POST">
            <input type="hidden" name="reservation_update" value="true">
            <input type="hidden" name="id_reservacion" value="<?= $id_reservacion ?>">
            <div class="form-group-container">  
                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d\TH:i', strtotime($reserva['fecha_inicio'])); ?>" required disabled class="editable">
                </div>
                <div class="form-group">
                    <label for="fecha_final">Fecha Final:</label>
                    <input type="datetime-local" id="fecha_final" name="fecha_final" value="<?php echo date('Y-m-d\TH:i', strtotime($reserva['fecha_final'])); ?>" required disabled class="editable">
                </div>
            </div>
            <div class="form-group"> 
                <label for="tipo_reservacion">Tipo de Reservación:</label>
                <input type="text" id="tipo_reservacion" name="tipo_reservacion" value="<?php echo $reserva['tipo_reservacion']; ?>" required disabled class="editable">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" required disabled class="editable"><?php echo $reserva['descripcion']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="id_espacio">Espacio:</label>
                <select name="id_espacio" id="id_espacio" required disabled class="editable">
                    <?php while ($espacio = mysqli_fetch_assoc($resultado_espacios)) { ?>
                        <option value="<?php echo $espacio['id']; ?>" <?php echo $espacio['id'] == $reserva['id_espacio'] ? 'selected' : ''; ?>>
                            <?php echo $espacio['codigo']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="estudiantes">Añadir Estudiantes:</label>
                <input type="text" id="estudiantes" name="estudiantes" placeholder="Buscar estudiante..." autocomplete="off">
                <div id="student-list" style="margin-top: 8px;"></div>
                <div id="selected-students" style="margin-top: 8px;">
            <?php 
            $estudiantesSeleccionados = explode(',', $reserva['estudiantes']);
            foreach ($estudiantesSeleccionados as $estudianteId) {
                $query = "SELECT nombre_completo FROM estudiantes WHERE id = $estudianteId";
                $resultado = mysqli_query($conexion, $query);
                $estudiante = mysqli_fetch_assoc($resultado);
                if ($estudiante) {
            ?>
                <div style="display: flex; align-items: center; margin: 5px 0; padding: 5px; background-color: #e9ecef; border-radius: 4px;">
                    <span><?php echo $estudiante['nombre_completo']; ?></span>
                    <input type="hidden" name="estudiantes[]" value="<?php echo $estudianteId; ?>">
                    <button type="button" style="margin-left: 10px; cursor: pointer; border: none; background: none; color: red; font-weight: bold;" onclick="this.parentElement.remove();">×</button>
                </div>
            <?php 
                }
            } 
            ?>
            </div>
            <div class="buttons-form-container">
                <button type="button" id="edit-button-reservation" class="update-button" onclick="enableEditingReservation()">Actualizar</button>
                <button type="submit" id="save-button-reservation" class="save-button" style="display: none;">Guardar Cambios</button>
            </div>
        </form>
    </div> 

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    function eventQueryStudents() {
        const query = this.value.trim();
        const studentList = document.getElementById('student-list');
        const selectedStudents = document.getElementById('selected-students');

        if (query.length < 3) {
            studentList.innerHTML = '';
            return;
        }

        // Realizar búsqueda de estudiantes
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

    // Añadir función debounce
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

    // Evento de entrada con debounce
    document.getElementById('estudiantes').addEventListener('input', 
        debounce(eventQueryStudents, 300)
    );
});

    </script>
    <script src="../../assets/js/button_update.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>
</html>
