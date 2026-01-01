<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php';
//formulario envio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
    $pisos = mysqli_real_escape_string($conexion, $_POST['pisos']);
    $cupo = mysqli_real_escape_string($conexion, $_POST['cupo']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
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

    $query = "INSERT INTO edificios (nombre, codigo, pisos, cupo, direccion, tipo, descripcion, imagen)
        VALUES ('$nombre', '$codigo', '$pisos', '$cupo', '$direccion', '$tipo', '$descripcion', '$imagen')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Edificio registrado con éxito.'); window.location.href='register_buldings.php';</script>";
    } else {
        echo "<script>alert('Error al registrar el edificio: " . mysqli_error($conexion) . "');</script>";
    }
}

// Consultar edificios
$query = "SELECT id, nombre, imagen FROM edificios";
$result = mysqli_query($conexion, $query);
$edificios = [];

$registros_por_pagina = 6;
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
    <title>Edificios</title>
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
        
        <main class="content">
            <div class="content-header">
                <h2>Gestion de Edificios</h2>
            </div>
            <div class="content_nav">
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Buscar edificio...">
                    <ion-icon name="search-outline"></ion-icon>

                    <select id="filter-type" class="filter-select">
                        <option value="">Todos los tipos</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Académico">Académico</option>
                        <option value="Auditorio">Auditorio</option>
                    </select>
                </div>
                <button class="button-ubi" id="abrirModal">Ver ubicaciones</button>
            </div>
            <div class="buildings-grid">
                <?php foreach ($edificios as $edificio): ?>
                <div class="building-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($edificio['imagen']); ?>" alt="Edificio"
                            class="building-image">
                        <a href="update_building_docente.php?id=<?php echo htmlspecialchars($edificio['id']); ?>"
                            class="edit-button">
                            <i class="ti ti-edit"></i>
                        </a>
                    </div>
                    <div class="building-info">
                        <div class="building-header">
                            <h3 class="building-name"><?php echo htmlspecialchars($edificio['nombre']); ?></h3>
                            <span
                                class="role building-type <?php echo strtolower(str_replace(' ', '-', $edificio['tipo'] ?? 'desconocido')); ?>">
                                <?php
                                    echo htmlspecialchars(($edificio['tipo'] ?? 'Desconocido') === 'Espacio Académico' ? 'Académico' : $edificio['tipo']);
                                ?>
                            </span>
                        </div>
                        <div class="space-count">
                            <i class="ti ti-building"></i>
                            <span>Espacios: <?php echo htmlspecialchars($edificio['espacios_asociados']); ?></span>
                        </div>
                        <div class="space-count">
                            <i class="ti ti-map-pin"></i>
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
                <div id="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; overflow-y: auto;">
                    <div style="background: #fff;margin: 1% auto;padding: 20px;width: 90%;max-width: 800px;border-radius: 10px;max-height: 90vh; overflow-y: auto;">
                        <span id="cerrarModal" style="float:right; cursor:pointer; font-weight:bold;">&times;</span>
                        <h3>Ubicaciones de los Edificios</h3>
                        <div id="mapa" style="height: 500px;"></div>
                    </div>
                </div>
            </div>
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
</div>
        </main>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
        <script>
            function openModal() {
                document.getElementById('modal').classList.add('active');
            }

            window.onclick = function(event) {
                const modal = document.getElementById('modal');
                if (event.target === modal) {
                    modal.classList.remove('active');
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
        <script>
            const modal = document.getElementById('modal');
            const abrirModalBtn = document.getElementById('abrirModal');
            const cerrarModalBtn = document.getElementById('cerrarModal');

            let mapa = null; // Para evitar duplicar el mapa

            // Función para abrir el modal y cargar el mapa
            abrirModalBtn.addEventListener('click', () => {
            modal.style.display = 'block';

            // Espera a que el modal se muestre antes de iniciar el mapa
            setTimeout(() => {
                if (!mapa) {
                // Inicializar mapa
                mapa = L.map('mapa').setView([4.147870, -73.634760], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapa);

                // Cargar edificios
                fetch('obtener_ubicaciones.php')
                    .then(response => response.json())
                    .then(data => {
                    data.forEach(edificio => {
                        if (edificio.latitud && edificio.longitud) {
                        L.marker([edificio.latitud, edificio.longitud])
                            .addTo(mapa)
                            .bindPopup(`<strong>${edificio.nombre}</strong><br>${edificio.direccion}`);
                        }
                    });
                    })
                    .catch(error => {
                    console.error('Error al cargar los edificios:', error);
                    });
                } else {
                // Si el mapa ya existe, solo forzar redibujo
                mapa.invalidateSize();
                }
            }, 200); // Delay para que el modal se vea primero
            });

            // Cerrar modal
            cerrarModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            });
        </script>

        <script src="../../assets/js/script.js"></script>
        <script src="../../assets/js/script_menu.js"></script>
    </main>
</div>
</body>

</html>