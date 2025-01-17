<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';
//formulario envio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = mysqli_real_escape_string($conexion, $_POST['codigo']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);

    $imagen_equip= null;

    if ($imagen_equip === null) {
        $imagen_equip = "../../assets/images/default_building.png";
    }
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/equipamiento/";

        // Asegurarse de que el directorio exista
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $ruta_imagen = $directorio_destino . uniqid() . "_" . basename($nombre_imagen);

        if (move_uploaded_file($ruta_temp, $ruta_imagen)) {
            $imagen_equip = $ruta_imagen;
        } else {
            echo "<script>alert('Error al subir la imagen.');</script>";
        }
    }

    $query = "INSERT INTO equipamiento (nombre, codigo, descripcion, imagen, estado)
        VALUES ('$nombre', '$codigo', '$descripcion', '$imagen_equip', '$estado')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Equipamiento registrado con éxito.'); window.location.href='equipment.php';</script>";
    } else {
        echo "<script>alert('Error al registrar el equipamiento: " . mysqli_error($conexion) . "');</script>";
    }
}

// Consultar edificios
$query = "SELECT id, codigo, nombre, imagen, estado FROM equipamiento";
$result = mysqli_query($conexion, $query);
$equipamientos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $equipamientos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipamiento</title>
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
                    <a href="table_build.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>

        <div class="container-edificios">
        <?php
        foreach ($equipamientos as $equipamiento) {
            ?>
            <div class="add-box">
                <h1 class="title_build"><?php echo htmlspecialchars($equipamiento['nombre']); ?></h1>
                <a href="update_equipment.php?id=<?php echo htmlspecialchars($equipamiento['id']); ?>">
                    <img src="<?php echo htmlspecialchars($equipamiento['imagen']); ?>" alt="Equipamiento" class="building-img">
                </a>
            </div>
        <?php
        }
        ?>
            <div class="add-box" onclick="openModal()">+</p>
            </div>
        </div>

        <div class="modal" id="modal">
            <div class="modal-content">
                <form action="equipment.php" method="POST" enctype="multipart/form-data">

                    <div class="form-group-container">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="codigo">Código:</label>
                            <input type="text" id="codigo" name="codigo" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select id="estado" name="estado" required>
                            <option value="">Seleccione el estado</option>
                            <option value="Disponible">Disponible</option>
                            <option value="En Mantenimiento">En Mantenimiento</option>
                            <option value="No Disponible">No Disponible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" class="description-register" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagen">Imagen:</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="form-group">
                        <button type="submit">Añadir Equipamiento</button>
                    </div>
                </form>
            </div>
        </div>

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