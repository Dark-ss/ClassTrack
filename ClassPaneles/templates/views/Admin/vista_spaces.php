<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';
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
    echo "<script>alert('Edificio no encontrado. ID: $building_id'); window.location.href='vista_edificios.php';</script>";
    exit;
}

if (isset($_GET['edificio_id'])) {
    $building_id = intval($_GET['edificio_id']);
    if ($building_id <= 0) {
        echo "<script>alert('ID de edificio no válido.'); window.location.href='vista_edificios.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ID de edificio no especificado.'); window.location.href='vista_edificios.php';</script>";
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
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css">
</head>

<body>
    <main class="build">
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="../../php/cerrar_sesion.php" class="logout">
                <img src="../../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="../../php/config.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="admin_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>

            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Cuenta<span>▼</span>
                </div>
                <div class="submenu" id="submenu">
                    <a href="create_account.php">Crear Cuenta</a>
                    <a href="vista_cuentas.php">cuentas </a>
                    <a href="register_students.php">Añadir Estudiantes</a>
                    <a href="vista_students.php">Estudiantes</a>
                </div>
            </div>

            <div class="menu-container_espacios" id="menu-container_espacios">
                <div class="menu-link" onclick="toggleDropdown_space()">Espacios<span>▼</span>
                </div>
                <div class="submenu" id="submenu_espacios">
                    <a href="register_buldings.php">Añadir Edificios</a>
                    <a href="vista_cuentas.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <a href="table_spaces.php" class="Button-view_Table_build">Vista Tabla Espacios</a>
        <div class="container-edificios">
        <?php
        foreach ($espacios as $espacio) {
            ?>
            <div class="add-box">
            <h1 class="title_build"><?php echo htmlspecialchars($espacio['codigo']); ?></h1>
            <a href="update_spaces.php?id=<?php echo htmlspecialchars($espacio['id']); ?>">
                <img src="<?php echo htmlspecialchars($espacio['imagen']); ?>" alt="Espacio" class="building-img">
            </a>
        </div>
        <?php
        }
        ?>
            <div class="add-box" onclick="openModal()">+</p>
            </div>
        </div>

        <div class=" modal" id="modal">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edificio_id" value="<?php echo htmlspecialchars($building_id); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <div class="form-group-container">
                        <div class="form-group">
                            <label for="codigo">Código:</label>
                            <input type="text" id="codigo" name="codigo" required>
                        </div>
                    </div>

                    <div class="form-group-container">
                        <div class="form-group">
                            <label for="capacidad">Capacidad:</label>
                            <input type="number" id="capacidad" name="capacidad" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipo_espacio">Tipo:</label>
                        <select id="tipo_espacio" name="tipo_espacio" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Sala computo">Sala computo</option>
                            <option value="Espacio Académico">Espacio Académico</option>
                            <option value="Auditorio">Auditorio</option>
                        </select>
                    </div>
                    <div class="form-group">
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

                    <div class="form-group">
                        <button type="submit">Añadir Espacio</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mostrar edificios existentes con su imagen y nombre en recuadros con "+" -->
        </main>
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
        <script src="../../assets/js/script.js"></script>
        <script src="../../assets/js/script_menu.js"></script>
    </main>
</body>

</html>