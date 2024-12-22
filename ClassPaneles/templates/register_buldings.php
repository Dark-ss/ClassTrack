<?php
include 'php/admin_session.php';
include 'php/conexion_be.php';
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
        $imagen = "./assets/images/default_building.png";
    }
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "uploads/edificio/";

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <link rel="stylesheet" href="../templates/assets/css/style_paneles.css">
    <link rel="stylesheet" href="../templates/assets/css/style_building.css">
</head>

<body>
    <>
        <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="php/cerrar_sesion.php" class="logout">
                <img src="assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="php/config.php" class="config">
                <img src="assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="admin_dashboard.php" class="home-admin">
                <img src="assets/images/inicio.png" alt="inicio" class="icons-image">
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
                    <a href="create_account.php">Añadir Edificios</a>
                    <a href="vista_cuentas.php">Edificios</a>
                    <a href="register_students.php">Añadir Salones</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>

        <div class="container-edificios">
            <?php
            foreach ($edificios as $edificio) {
                echo '
                <div class="add-box">
                <a href="update_building.php?id=' . $edificio['id'] . '">
                    <img src="' . $edificio['imagen'] . '" alt="Edificio" class="building-img">
                </a>
                </div>';
            }
            ?>
            <div class="add-box" onclick="openModal()">+</p>
            </div>
        </div>
        <div class=" modal" id="modal">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">

                    <div class="form-group-container">
                        <div class="form-group">
                            <label for="nombre">Nombre del edificio:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="codigo">Código:</label>
                            <input type="text" id="codigo" name="codigo" required>
                        </div>
                    </div>

                    <div class="form-group-container">
                        <div class="form-group">
                            <label for="pisos">Cantidad de pisos:</label>
                            <input type="number" id="pisos" name="pisos" required>
                        </div>
                        <div class="form-group">
                            <label for="cupo">Cupo:</label>
                            <input type="number" id="cupo" name="cupo" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Espacio Académico">Espacio Académico</option>
                            <option value="Auditorio">Auditorio</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción General:</label>
                        <textarea id="descripcion" name="descripcion" class="description-register" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagen">Imagen:</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="form-group">
                        <button type="submit">Añadir Edificio</button>
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
        <script src="assets/js/script.js"></script>
        <script src="assets/js/script_menu.js"></script>
</body>

</html>