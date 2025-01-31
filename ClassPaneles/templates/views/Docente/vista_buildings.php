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

while ($row = mysqli_fetch_assoc($result)) {
    $edificios[] = $row;
}

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edificios</title>
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
            <a href="../../php/config_docente.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="docente_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>
            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Espacios<span>▼</span>
                </div>
                <div class="submenu" id="submenu">
                    <a href="vista_buildings.php">Edificios</a>
                    <a href="table_disponibilidad.php">Disponibilidad</a>
                </div>
            </div>
        </div>

        <a href="table_build_docente.php" class="Button-view_Table_build">Vista Tabla Edificios</a>

        <div class="container-edificios">
        <?php
        foreach ($edificios as $edificio) {
            ?>
            <div class="add-box">
                <h1 class="title_build"><?php echo htmlspecialchars($edificio['nombre']); ?></h1>
                <img src="../../assets/images/espacio_academico.png" alt="espacios" class="icons_space_count">
                <p class="info_build">Espacios: <?php echo htmlspecialchars($edificio['espacios_asociados']); ?></p>
                <a href="update_building_docente.php?id=<?php echo htmlspecialchars($edificio['id']); ?>">
                    <img src="<?php echo htmlspecialchars($edificio['imagen']); ?>" alt="Edificio" class="building-img">
                </a>
            </div>
        <?php
        }
        ?>
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