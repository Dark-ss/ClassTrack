<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); // Redirige a no admin
    exit();
}
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 5;
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
                        <li><a href="create_account.php"
                                class="<?php echo $currentFile == 'create_account.php' ? 'active' : ''; ?>">
                                <ion-icon name="person-add-outline"></ion-icon> Crear Cuenta
                            </a></li>
                        <li><a href="vista_cuentas.php"
                                class="<?php echo $currentFile == 'vista_cuentas.php' ? 'active' : ''; ?>">
                                <ion-icon name="people-outline"></ion-icon> Cuentas
                            </a></li>
                        <li><a href="register_students.php"
                                class="<?php echo $currentFile == 'register_students.php' ? 'active' : ''; ?>">
                                <ion-icon name="school-outline"></ion-icon> Añadir Estudiantes
                            </a></li>
                        <li><a href="vista_students.php"
                                class="<?php echo $currentFile == 'vista_students.php' ? 'active' : ''; ?>">
                                <ion-icon name="reader-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="register_buildings.php"
                                class="<?php echo $currentFile == 'register_buildings.php' ? 'active' : ''; ?>">
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
                        <li><a href="reservar_espacio.php"
                                class="<?php echo $currentFile == 'reservar_espacio.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Reservar Espacio
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
                    <p class="user-email"> <?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content-cuenta">
            <h1 class="title-table">Lista de Usuarios</h1>

            <!-- Contenedor para la barra de búsqueda y la tabla -->
            <div class="table-container">
                <form method="GET" action="vista_cuentas.php" class="search-form">
                    <input type="text" name="buscar" placeholder="Buscar cuenta..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>

                <table class="user-table">
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
                            <td><img src="<?php echo $fila['imagen'] ? "../../uploads/" . $fila['imagen'] : "../../uploads/usuario.png"; ?>"
                                    alt="Imagen de Usuario" class="user-image"></td>
                            <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['usuario']); ?></td>
                            <td><span class="role"><?php echo htmlspecialchars($fila['rol']); ?></span></td>
                            <td>
                                <div class="dropdown">
                                    <ion-icon name="ellipsis-horizontal-sharp" class="dropdown-toggle"
                                        style="font-size: 24px; cursor: pointer;"></ion-icon>
                                    <div class="dropdown-content">
                                        <a href="?id=<?php echo $fila['id']; ?>" class="delete-button"
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                                        <a href="update_user.php?id=<?php echo $fila['id']; ?>"
                                            class="update-button">Actualizar</a>
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
        </main>

    </div>

    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>

</body>

</html>

<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
