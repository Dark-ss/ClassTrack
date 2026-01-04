<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); // Redirige a no admin
    exit();
}
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 7;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Calcular paginas 
$query_total = "SELECT COUNT(*) as total FROM usuarios";
$resultado_total = mysqli_query($conexion, $query_total);
$total_usuarios = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

// Obtener todas las cuentas de la base de datos
$query = "SELECT id, imagen, nombre_completo, correo, usuario, rol FROM usuarios";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

// Búsqueda de cuentas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$query = "SELECT id, imagen, nombre_completo, correo, usuario, rol FROM usuarios WHERE nombre_completo 
        LIKE '%$search%' OR correo LIKE '%$search%' OR usuario LIKE '%$search%' LIMIT $registros_por_pagina OFFSET $offset";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

// Procesar la eliminación del usuario
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id != $_SESSION['id']) {
        $guardarEliminado = "INSERT INTO usuarios_eliminados (nombre_completo, rol)
                SELECT nombre_completo, rol FROM usuarios WHERE id = $id";
        if (!mysqli_query($conexion, $guardarEliminado)) {
        die("Error al registrar el usuario eliminado: " . mysqli_error($conexion));
        }

        $query_delete = "DELETE FROM usuarios WHERE id = '$id'";
        mysqli_query($conexion, $query_delete);
        header("Location: vista_cuentas.php");
        exit();
    } else {
        echo "<script>alert('No puedes eliminar tu propia cuenta.');</script>";
    }
}

include '../../php/update_table.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuarios</title>
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1 ">
    <link rel="shortcut icon" href="../../assets/images/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php
            // Obtiene el nombre del archivo actual
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

        <!-- Main Content -->
        <main class="main-content-cuenta">
            <h1 class="title-table">Lista de Usuarios</h1>

            <!-- Contenedor para la barra de búsqueda y el botón -->
            <div class="search-and-create">
                <form method="GET" action="vista_cuentas.php" class="search-form">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="buscar" placeholder="Buscar cuenta..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
                <a href="#" class="create-user-button" id="openCreateUserModal">
                    <ion-icon name="person-add-sharp"></ion-icon>
                    Crear Usuario
                </a>
            </div>

            <!-- Contenedor de la tabla -->
            <div class="table-container">
                <table class="user-table user-table-accounts">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO ELECTRÓNICO</th>
                            <th>USUARIO</th>
                            <th>ROL</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['id']); ?></td>
                            <td><img src="<?php echo $fila['imagen'] ? "../../uploads/" . $fila['imagen'] : "../../assets/images/photo.jpg"; ?>"
                                    class="user-image"></td>
                            <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                            <td class="email-use_count"><?php echo htmlspecialchars($fila['correo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
                            <td>
                                <span class="role <?php echo strtolower($fila['rol']); ?>-role">
                                    <?php echo htmlspecialchars($fila['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                <i class="fa-solid fa-ellipsis dropdown-toggle"></i>
                                    <div class="dropdown-content">
                                        <a href="#" class="update-button openUpdateUserModal" 
                                            data-id="<?php echo $fila['id']; ?>">
                                            <ion-icon name="create-outline"></ion-icon>
                                            Actualizar
                                            </a>
                                        <a href="?id=<?php echo $fila['id']; ?>" class="delete-button"
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                            <ion-icon name="trash-outline"></ion-icon>
                                            Eliminar
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="pagination">
                <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button <?php echo $i === $pagina_actual ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="pagination-button">Siguiente</a>
                <?php endif; ?>
            </div>

            <!-- Modal para crear usuario -->
            <div id="createUserModal" class="modal">
                <div class="modal-content">
                    <div class="title_modal">
                        <h2>Crear Nuevo Usuario</h2>
                    </div>
                    <form action="../../php/registro_usuario_be.php" method="POST" enctype="multipart/form-data"
                        class="formulario_register">
                        <div class="form-group">
                            <label for="nombre_completo">Nombre Completo</label>
                            <input autocomplete="off" type="text" id="nombre_completo" placeholder="Nombre Completo"
                                name="nombre_completo" required>
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input autocomplete="off" type="text" id="correo" placeholder="Correo Electrónico"
                                name="correo" required>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="usuario">Usuario</label>
                                <input autocomplete="off" type="text" id="usuario" placeholder="Usuario" name="usuario"
                                    required>
                            </div>
                            <div>
                                <label for="contrasena">Contraseña</label>
                                <input type="password" id="contrasena" placeholder="Contraseña" name="contrasena"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rol">Selecciona el Rol:</label>
                            <select name="rol" id="rol" required>
                                <option value="admin">Administrador</option>
                                <option value="docente">Docente</option>
                            </select>
                        </div>

                        <div class="profile-photo">
                            <div class="photo-circle">
                                <img src="../../assets/images/photo.jpg" alt="Foto de perfil" id="profileImage">
                            </div>
                            <a href="#" class="photo-upload-link" id="uploadPhotoBtn">Seleccionar foto</a>
                            <input type="file" id="photoInput" name="imagen" hidden accept="image/*">
                        </div>

                        <div class="modal-buttons">
                            <button type="button" class="cancel-button">Cancelar</button>
                            <button type="submit" class="submit-button">Crear</button>

                        </div>
                    </form>
                </div>
            </div>

<div id="updateUserModal" class="modal">
    <div class="modal-content">
        <div class="title_modal">
            <h2>Actualizar Usuario</h2>
        </div>

        <form id="updateUserForm" class="formulario_register" action="update_user.php?id=<?php echo $fila['id']; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="update_id">
            <input type="hidden" name="correo_original" id="correo_original">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre_completo" id="update_nombre" required>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" id="update_correo" required>
            </div>

            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" id="update_usuario" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol" id="update_rol">
                    <option value="admin">Administrador</option>
                    <option value="docente">Docente</option>
                </select>
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-button" id="closeUpdateModal">Cancelar</button>
                <button type="submit" class="submit-button">Guardar</button>
            </div>
        </form>
    </div>
</div>
</main>
    </div>
<script src="../../assets/js/script_modal.js"></script>
<script src="../../assets/js/script.js"></script>
<script src="../../assets/js/script_menu.js"></script>
<script src="../../assets/js/update_user_modal.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>


</body>
</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>