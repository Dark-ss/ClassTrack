<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = intval($_POST['eliminar_id']);
    $delete_query = "DELETE FROM equipamiento WHERE id = $eliminar_id";

    if (mysqli_query($conexion, $delete_query)) {
        echo "<script>alert('Equipamiento eliminado con éxito.'); window.location.href='equipment.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error al eliminar el equipamiento: " . mysqli_error($conexion) . "');</script>";
    }
}
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
    <title>Registro de Edificios</title>
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1">
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
                                class="<?php echo $currentFile == 'register_buildings.php' ? 'active' : ''; ?>">
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
    <h2>Gestión de Equipamientos</h2>
    <div style="display: flex; gap: 10px;">
        <!-- Botón añadir -->
        <button class="add-button" onclick="openModal()">
            <ion-icon name="add-outline"></ion-icon>
            Añadir Equipamiento
        </button>

        <!-- Botón redirección a tabla de equipamiento espacios -->
        <a href="table_equipament_spaces.php" class="add-button" style="text-decoration: none; display: flex; align-items: center;">
            <ion-icon name="construct-outline"></ion-icon>
            Equipamientos Espacios
        </a>
    </div>
</div>     
    <div class="buildings-grid">
        <?php foreach ($equipamientos as $equipamiento): ?>
        <div class="building-card">
            <img src="<?php echo htmlspecialchars($equipamiento['imagen']); ?>" alt="Equipamiento" class="building-image">
            <div class="building-info">
                <h3><?php echo htmlspecialchars($equipamiento['nombre']); ?></h3>
            <div class="actions">
                <!-- Botón editar -->
                <a href="update_equipment.php?id=<?php echo htmlspecialchars($equipamiento['id']); ?>" class="edit-button">
                    <i class="ti ti-edit"></i>
                </a>

                <!-- Botón eliminar -->
                <form action="equipment.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este equipamiento?');">
                    <input type="hidden" name="eliminar_id" value="<?php echo htmlspecialchars($equipamiento['id']); ?>">
                    <button type="submit" class="delete-button-equip">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>
            </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

<div class="modal1" id="modal">
    <div class="modal-content1">
        <div class="modal-header1">
            <h3>Añadir Nuevo Equipamiento</h3>
            <button class="close-button" onclick="closeModal()">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>
        <form action="equipment.php" method="POST" enctype="multipart/form-data" class="form-grid">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" required>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="">Seleccione el estado</option>
                    <option value="Disponible">Disponible</option>
                    <option value="En Mantenimiento">En Mantenimiento</option>
                    <option value="No Disponible">No Disponible</option>
                </select>
            </div>
            <div class="form-group full-width">
                <label for="descripcion">Descripción</label>
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
<script src="../../assets/js/script.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</main>
</body>

</html>
