<?php
require_once '../../php/conexion_be.php';
include '../../php/admin_session.php'; // Verifica que el admin esté autenticado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $identificacion = mysqli_real_escape_string($conexion, $_POST['identificacion']);

    // Manejo de la imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $directorio_destino = "../../uploads/estudiantes/";

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

    // Insertar estudiante
    $query = "INSERT INTO estudiantes (nombre_completo, correo, identificacion, imagen)
        VALUES ('$nombre_completo', '$correo', '$identificacion', '$imagen')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Estudiante registrado con éxito.'); window.location.href='vista_students.php';</script>";
    } else {
        echo "<script>alert('Error al registrar estudiante: " . mysqli_error($conexion) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
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
                    <a href="equipment.php">Equipamientos</a>
                    <a href="vista_students.php">Salones</a>
                </div>
            </div>
        </div>
        <h1 class="title-register_students">Registrar Estudiante</h1>
        <div class="container-form_register_students">
            <form method="POST" enctype="multipart/form-data" class="formulario_register">
                <input type="text" placeholder="Nombres y apellidos completos" id="nombre_completo" name="nombre_completo" required>

                <input type="email" placeholder="ej: ejemplo@gmail.com" id="correo" name="correo" required>

                <input type="text" placeholder="Cedula o documento de identidad" id="identificacion" name="identificacion" required>

                <input type="file" id="imagen" name="imagen" accept="image/*">

                <button type="submit">Registrar Estudiante</button>
            </form>
        </div>
    </main>
    <script src="../../assets/js/script_stats.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>