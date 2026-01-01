<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';

// Formulario de envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
    $pisos = mysqli_real_escape_string($conexion, $_POST['pisos']);
    $cupo = mysqli_real_escape_string($conexion, $_POST['cupo']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $latitud = mysqli_real_escape_string($conexion, $_POST['latitud']);
    $longitud = mysqli_real_escape_string($conexion, $_POST['longitud']);
    $tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);


    $imagen = null;

    if ($imagen === null) {
        $imagen = "../../assets/images/default_building.png";
    }
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/edificio/";

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

    $query = "INSERT INTO edificios (nombre, codigo, pisos, cupo, direccion, tipo, descripcion, imagen, latitud, longitud)
        VALUES ('$nombre', '$codigo', '$pisos', '$cupo', '$direccion', '$tipo', '$descripcion', '$imagen', '$latitud', '$longitud')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Edificio registrado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al registrar el edificio: " . mysqli_error($conexion) . "');</script>";
    }
}
// Al inicio del archivo, después de la conexión
$registros_por_pagina = 6; // Puedes ajustar esto a 9 si prefieres
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Modificar la consulta para incluir LIMIT y OFFSET
$query_spaces_count = "
    SELECT 
        e.id,
        e.tipo,
        e.cupo,
        e.pisos,
        e.codigo,
        e.nombre, 
        e.imagen, 
        e.direccion,
        e.latitud,
        e.longitud,
        COUNT(s.id) AS espacios_asociados
    FROM 
        edificios e
    LEFT JOIN 
        espacios_academicos s 
    ON 
        e.id = s.edificio_id
    GROUP BY 
        e.id, e.nombre, e.imagen, e.direccion
    ORDER BY e.id DESC
    LIMIT $registros_por_pagina OFFSET $offset";

// Consulta para obtener el total de registros
$query_total = "
    SELECT COUNT(DISTINCT e.id) as total 
    FROM edificios e";
$result_total = mysqli_query($conexion, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_registros = $row_total['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
$result = mysqli_query($conexion, $query_spaces_count);
$edificios = [];

while ($row = mysqli_fetch_assoc($result)) {
    $edificios[] = $row;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Edificios</title>
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1">
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

        <!-- Main Content -->
        <main class="content">
            <div class="content-header">
                <h2>Gestión de Edificios</h2>
            </div>

            <div class="content_nav">
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Buscar edificio...">
                    <i class="fas fa-search search-icon"></i>

                    <!-- Menú desplegable para filtrar por tipo -->
                    <select id="filter-type" class="filter-select">
                        <option value="">Todos los tipos</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Académico">Académico</option>
                        <option value="Auditorio">Auditorio</option>
                    </select>
                </div>
                <div>
                    <div class="add-button-container">
                        <button class="add-button" onclick="openModal()">
                            <ion-icon name="add-circle"></ion-icon>
                            Añadir Edificio
                        </button>
                    </div>
                </div>
            </div>

            <div class="buildings-grid">
                <?php foreach ($edificios as $edificio): ?>
                <div class="building-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($edificio['imagen']); ?>" alt="Edificio"
                            class="building-image">
                        <a href="update_building.php?id=<?php echo htmlspecialchars($edificio['id']); ?>"
                            class="edit-button">
                            <i class="ti ti-edit"></i>
                        </a>
                    </div>
                    <div class="building-info">
                        <div class="building-header">
                            <h3 class="building-name"><?php echo htmlspecialchars($edificio['nombre']); ?></h3>
                            <!-- Identificador del tipo de edificio -->
                            <span
                                class="role building-type <?php echo strtolower(str_replace(' ', '-', $edificio['tipo'] ?? 'desconocido')); ?>">
                                <?php
                    // Cambiar "Espacio Académico" por "Académico"
                    echo htmlspecialchars(($edificio['tipo'] ?? 'Desconocido') === 'Espacio Académico' ? 'Académico' : $edificio['tipo']);
                    ?>
                            </span>
                        </div>
                        <!-- Nueva fila para código, pisos y cupo -->
                        <div class="space-count">
                            <i class="ti ti-building"></i> <!-- Reemplaza ion-icon name="business-outline" -->
                            <span>Espacios: <?php echo htmlspecialchars($edificio['espacios_asociados']); ?></span>
                        </div>
                        <div class="space-count">
                            <i class="ti ti-map-pin"></i> <!-- Reemplaza ion-icon name="location-outline" -->
                            <span><?php echo htmlspecialchars($edificio['direccion']); ?></span>
                        </div>

                        <hr>
                        <div class="building-details">
                            <div class="detail-item">
                                <i class="ti ti-barcode"></i> <!-- Reemplaza ion-icon name="barcode-outline" -->
                                <span class="detail-value"><?php echo htmlspecialchars($edificio['codigo']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="ti ti-stack-3"></i> <!-- Reemplaza ion-icon name="layers-outline" -->
                                <span class="detail-value"><?php echo htmlspecialchars($edificio['pisos']); ?></span>
                                <span>pisos</span>
                            </div>
                            <div class="detail-item">
                                <i class="ti ti-users"></i> <!-- Reemplaza ion-icon name="people-outline" -->
                                <span class="detail-value"><?php echo htmlspecialchars($edificio['cupo']); ?></span>
                                <span>cupos</span>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Paginación -->
            <div class="pagination">
                <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="pagination-button">Anterior</a>
            <?php endif; ?>

    <?php
    // Mostrar solo un rango de páginas si hay muchas
    $rango = 2;
    $inicio_rango = max(1, $pagina_actual - $rango);
    $fin_rango = min($total_paginas, $pagina_actual + $rango);

    if ($inicio_rango > 1) {
        echo '<a href="?pagina=1" class="pagination-button">1</a>';
        if ($inicio_rango > 2) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
    }

    for ($i = $inicio_rango; $i <= $fin_rango; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>"
                    class="pagination-button <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; 

    if ($fin_rango < $total_paginas) {
        if ($fin_rango < $total_paginas - 1) {
            echo '<span class="pagination-ellipsis">...</span>';
        }
        echo '<a href="?pagina=' . $total_paginas . '" class="pagination-button">' . $total_paginas . '</a>';
    }
    ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="pagination-button">Siguiente</a>
                <?php endif; ?>
            </div>
        </main>

        <!-- Modal para añadir edificio -->
        <div class="modal1" id="modal">
            <div class="modal-content1">
                <div class="modal-header1">
                    <h3>Añadir Nuevo Edificio</h3>
                    <button class="close-button" onclick="closeModal()">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre del edificio</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" required>
                    </div>
                    <div class="form-group">
                        <label for="pisos">Cantidad de pisos</label>
                        <input type="number" id="pisos" name="pisos" required>
                    </div>
                    <div class="form-group">
                        <label for="cupo">Cupo</label>
                        <input type="number" id="cupo" name="cupo" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="latitud">Latitud</label>
                        <input type="number" id="latitud" name="latitud" step="0.000001" min="-90" max="90" required>
                    </div>
                    <div class="form-group">
                        <label for="longitud">Longitud</label>
                        <input type="number" id="longitud" name="longitud" step="0.000001" min="-180" max="180" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Espacio Académico">Espacio Académico</option>
                            <option value="Auditorio">Auditorio</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="descripcion">Descripción General</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="imagen">Imagen</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="submit-button">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script>
    function openModal() {
        document.getElementById('modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modal');
        if (event.target === modal) {
            closeModal();
        }
    };
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
            const buildingType = building.querySelector('.building-type').textContent
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
        const buildingType = building.querySelector('.building-type').textContent.toLowerCase();

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
</body>

</html>