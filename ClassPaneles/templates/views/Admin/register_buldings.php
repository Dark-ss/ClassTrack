<?php
include '../../php/admin_session.php';
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

while ($row = mysqli_fetch_assoc($result)) {
    $edificios[] = $row;
}

// Consultar edificios junto con el conteo de espacios académicos
$query_spaces_count = "
    SELECT 
        e.id, 
        e.nombre, 
        e.imagen, 
        COUNT(s.id) AS espacios_asociados
    FROM 
        edificios e
    LEFT JOIN 
        espacios_academicos s 
    ON 
        e.id = s.edificio_id
    GROUP BY 
        e.id
";
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
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../../assets/images/logo1.png">
    <title>Registro de Edificios</title>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php
        $currentFile = basename($_SERVER['PHP_SELF']);
        ?>
        <aside class="sidebar">
            <div class="logo">
                <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" height="auto">
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
                        <li><a href="register_buldings.php"
                                class="<?php echo $currentFile == 'register_buldings.php' ? 'active' : ''; ?>">
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
                        <li><a href="./table_reservation.php"
                                class="<?php echo $currentFile == 'reservar_espacio.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Reservas
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
                    <p class="user-email"><?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <div class="content-header">
                <h2>Gestión de Edificios</h2>
                <button class="add-button" onclick="openModal()">
                    <ion-icon name="add-outline"></ion-icon>
                    Añadir Edificio
                </button>
            </div>

            <div class="buildings-grid">
                <?php foreach ($edificios as $edificio): ?>
                <div class="building-card">
                    <img src="<?php echo htmlspecialchars($edificio['imagen']); ?>" alt="Edificio"
                        class="building-image">
                    <div class="building-info">
                        <h3><?php echo htmlspecialchars($edificio['nombre']); ?></h3>
                        <div class="space-count">
                            <ion-icon name="business-outline"></ion-icon>
                            <span>Espacios: <?php echo htmlspecialchars($edificio['espacios_asociados']); ?></span>
                        </div>
                        <a href="update_building.php?id=<?php echo htmlspecialchars($edificio['id']); ?>"
                            class="edit-link">
                            <ion-icon name="create-outline"></ion-icon>
                            Editar
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

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
        </main>
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
</body>

</html>