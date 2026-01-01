<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = intval($_POST['eliminar_id']);

    $delete_query = "DELETE FROM espacios_academicos WHERE id = $eliminar_id"; 
    if (mysqli_query($conexion, $delete_query)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo "<script>alert('Espacio eliminado con éxito.'); window.location.href='vista_spaces.php?edificio_id=" . intval($_GET['edificio_id']) . "';</script>";
        } else {
            echo "<script>alert('No se eliminó nada. Revisa si el ID existe en la tabla.');</script>";
        }
        exit;
    } else {
        echo "<script>alert('Error al eliminar el espacio: " . mysqli_error($conexion) . "');</script>";
    }
}

//formulario envio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
    $capacidad = mysqli_real_escape_string($conexion, $_POST['capacidad']);
    $tipo_espacio = mysqli_real_escape_string($conexion, $_POST['tipo_espacio']);
    $descripcion_general = mysqli_real_escape_string($conexion, $_POST['descripcion_general']);
    $building_id = mysqli_real_escape_string($conexion, $_POST['edificio_id']);

    $imagen = null;

    if ($imagen === null) {
        $imagen = "../../assets/images/default_building.png";
    }
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/espacio/";

        // Asegurarse de que el directorio exista
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $ruta_imagen = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        if (move_uploaded_file($ruta_temp, $ruta_imagen)) {
            $imagen = $ruta_imagen;
        } else {
            echo "<script>alert('Error al subir la imagen.');</script>";
        }
    }

    $query = "INSERT INTO espacios_academicos (codigo, capacidad, tipo_espacio, descripcion_general, imagen, edificio_id)
        VALUES ('$codigo', '$capacidad','$tipo_espacio', '$descripcion_general', '$imagen','$building_id')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Espacio registrado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al registrar el espacio: " . mysqli_error($conexion) . "');</script>";
    }
}

// Consultar espacios
$query = "SELECT id, codigo, imagen, edificio_id FROM espacios_academicos";
$result = mysqli_query($conexion, $query);
$espacios = [];

while ($row = mysqli_fetch_assoc($result)) {
    $espacios[] = $row;
}

//obteniendo id del edificio
$building_id = isset($_GET['edificio_id']) ? intval($_GET['edificio_id']):0;
// Validar si el ID corresponde a un edificio existente
$query = "SELECT nombre FROM edificios WHERE id = $building_id";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $edificio = mysqli_fetch_assoc($result);
}   else {
    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_buildings.php';</script>";
    exit;
}

if (isset($_GET['edificio_id'])) {
    $building_id = intval($_GET['edificio_id']);
    if ($building_id <= 0) {
        echo "<script>alert('ID de edificio no válido.'); window.location.href='vista_buildings.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ID de edificio no especificado.'); window.location.href='vista_buildings.php';</script>";
    exit;
}
//consulta edificio, separación de espacios
$edificio_id = isset($_GET['edificio_id']) ? intval($_GET['edificio_id']) : 0;

if ($edificio_id > 0) {
    $query_espacios = "SELECT * FROM espacios_academicos WHERE edificio_id = $edificio_id";
    $result = mysqli_query($conexion, $query_espacios);
    $espacios = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    echo "<p>No se especificó un edificio válido.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espacios</title>
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
</head>

<body>
<div class="container">
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
                                <ion-icon name="school-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="./register_buldings.php"
                                class="<?php echo $currentFile == 'register_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Añadir Edificios
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
        <main class="content">
            <div class="content-header">
                <h2>Gestion de Espacios</h2>
            </div>
            <div class="content_nav">
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Buscar espacio...">
                    <ion-icon name="search-outline"></ion-icon>
                    <select id="filter-type" class="filter-select">
                        <option value="">Todos los tipos</option>
                        <option value="espacio academico">Espacio academico</option>
                        <option value="sala computo">Sala Computo</option>
                        <option value="auditorio">Auditorio</option>
                    </select>
                </div>
                <div>
                    <div class="add-button-container">
                        <button class="add-button" onclick="openModal('modal-espacio')">
                            <ion-icon name="add-circle"></ion-icon>
                            Añadir Espacio
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="buildings-grid">
                <?php
                foreach ($espacios as $espacio) {
                    ?>
                    <div class="building-card">
                        <div class="image-container">
                            <a href="update_spaces.php?id=<?php echo htmlspecialchars($espacio['id']); ?>">
                                <img src="<?php echo htmlspecialchars($espacio['imagen']); ?>" alt="Espacio" class="building-image">
                            </a>
                        </div>
                        <div class="building-info">
                            <div class="building-header">
                            <i class="ti ti-building"></i>
                            <h3 class="building-name"><?php echo htmlspecialchars($espacio['codigo']); ?></h3>
                                <span
                                    class="role space-type <?php echo strtolower(str_replace(' ', '-', $espacio['tipo_espacio'] ?? 'desconocido')); ?>">
                                    <?php
                                        echo htmlspecialchars(($espacio['tipo_espacio'] ?? 'Desconocido') === 'Espacio Academico' ? 'academico' : $espacio['tipo_espacio']);
                                    ?>
                                </span>
                            <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este espacio?');">
                                <input type="hidden" name="eliminar_id" value="<?php echo $espacio['id']; ?>">
                                <button type="submit" style="background:red; color:white; border:none; border-radius:6px; ">Eliminar espacio</button>
                            </form>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </main>

        <!-- Modal Añadir Espacio -->
<div class="modal" id="modal-espacio" style="display:none;">
    <div class="modal-content">
        <div class="modal-header1">
            <h3>Añadir Nuevo Espacio</h3>
            <button class="close-button" onclick="closeModal()">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
            <!-- ocultos para la relación -->
            <input type="hidden" name="edificio_id" value="<?php echo htmlspecialchars($building_id); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="form-group">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" required>
                </div>
                <div class="form-group">
                    <label for="capacidad">Capacidad:</label>
                    <input type="number" id="capacidad" name="capacidad" min="0" required>
                </div>
                <div class="form-group">
                    <label for="tipo_espacio">Tipo:</label>
                    <select id="tipo_espacio" name="tipo_espacio" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="Espacio Academico">Espacio Académico</option>
                        <option value="Sala computo">Sala cómputo</option>
                        <option value="Auditorio">Auditorio</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="descripcion_general">Descripción General:</label>
                    <textarea id="descripcion_general" name="descripcion_general" class="description-register" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Edificio seleccionado:</label>
                    <input type="text" value="<?php echo htmlspecialchars($edificio['nombre']); ?>" readonly>
                </div>

            <div class="form-actions" style="display:flex; gap:.5rem; justify-content:flex-end; margin-top:1rem;">
                <button type="button" class="cancel-button" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="submit-button">Guardar</button>
            </div>
        </form>
    </div>
</div>
<script>
    function openModal(id) {
        document.getElementById('modal-espacio').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById('modal-espacio').style.display = 'none';
    }
</script>
        <script>
            document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase(); // Obtén el término de búsqueda en minúsculas
            const buildings = document.querySelectorAll('.building-card'); // Selecciona todas las tarjetas

            buildings.forEach(building => {
                const buildingName = building.querySelector('.building-name').textContent
                    .toLowerCase(); // Obtén el nombre del edificio
                if (buildingName.includes(searchTerm)) {
                    building.style.display = 'block'; // Muestra la tarjeta si coincide
                } else {
                    building.style.display = 'none'; // Oculta la tarjeta si no coincide
                }
            });
        });

        // Función para filtrar por tipo de edificio
        document.getElementById('filter-type').addEventListener('change', function() {
            const selectedType = this.value.toLowerCase(); // Obtén el tipo seleccionado en minúsculas
            const buildings = document.querySelectorAll('.building-card'); // Selecciona todas las tarjetas

            buildings.forEach(building => {
                const buildingType = building.querySelector('.space-type').textContent
                    .toLowerCase(); // Obtén el tipo del edificio
                if (selectedType === "" || buildingType.includes(selectedType)) {
                    building.style.display = 'block'; // Muestra la tarjeta si coincide
                } else {
                    building.style.display = 'none'; // Oculta la tarjeta si no coincide
                }
            });
        });

        // Función para filtrar por nombre (barra de búsqueda)
        document.getElementById('search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase(); // Obtén el término de búsqueda en minúsculas
            const buildings = document.querySelectorAll('.building-card'); // Selecciona todas las tarjetas

            buildings.forEach(building => {
                const buildingName = building.querySelector('.building-name').textContent
                    .toLowerCase(); // Obtén el nombre del edificio
                if (buildingName.includes(searchTerm)) {
                    building.style.display = 'block'; // Muestra la tarjeta si coincide
                } else {
                    building.style.display = 'none'; // Oculta la tarjeta si no coincide
                }
            });
        });

        let currentSearchTerm = '';
        let currentType = '';

        // Function to check if a building matches both filters
        function buildingMatchesFilters(building, searchTerm, type) {
            const buildingName = building.querySelector('.building-name').textContent.toLowerCase();
            const buildingType = building.querySelector('.space-type').textContent.toLowerCase();

            const matchesSearch = buildingName.includes(searchTerm);
            const matchesType = type === "" || buildingType.includes(type);

            return matchesSearch && matchesType;
        }

        // Function to apply both filters
        function applyFilters() {
            const buildings = document.querySelectorAll('.building-card');

            buildings.forEach(building => {
                if (buildingMatchesFilters(building, currentSearchTerm, currentType)) {
                    building.style.display = 'block';
                } else {
                    building.style.display = 'none';
                }
            });
        }

        // Event listener for search input
        document.getElementById('search-input').addEventListener('input', function() {
            currentSearchTerm = this.value.toLowerCase();
            applyFilters();
        });

        // Event listener for type filter
        document.getElementById('filter-type').addEventListener('change', function() {
            currentType = this.value.toLowerCase();
            applyFilters();
        });

        // Function to reset filters
        function resetFilters() {
            document.getElementById('search-input').value = '';
            document.getElementById('filter-type').value = '';
            currentSearchTerm = '';
            currentType = '';
            applyFilters();
        }
        </script>
        <script src="../../assets/js/script.js"></script>
        <script src="../../assets/js/script_menu.js"></script>
    </main>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>

</html>