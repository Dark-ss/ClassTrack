<?php
include '../../php/admin_session.php'; // Verifica que el admin esté autenticado

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); // Redirige a no admin
    exit();
}
include '../../php/conexion_be.php';

//paginación
$registros_por_pagina = 7;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

//Calcular paginas 
$query_total = "SELECT COUNT(*) as total FROM edificios";
$resultado_total = mysqli_query($conexion, $query_total);
$total_usuarios = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

$query = "SELECT * FROM edificios ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);

//busqueda de cuentas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$query = "SELECT id, imagen, nombre, codigo, pisos, cupo, direccion, tipo FROM 
        edificios WHERE nombre LIKE '%$search%' OR codigo LIKE '%$search%' OR 
        tipo LIKE '%$search%' 
        ORDER BY id DESC 
        LIMIT $registros_por_pagina OFFSET $offset";
$resultado = mysqli_query($conexion, $query);

$query_total = "SELECT COUNT(*) as total FROM edificios 
               WHERE nombre LIKE '%$search%' OR codigo LIKE '%$search%' OR tipo LIKE '%$search%'";

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id != $_SESSION['id']) {

        $query_delete_espacios = "DELETE FROM espacios_academicos WHERE edificio_id = '$id'";
        mysqli_query($conexion, $query_delete_espacios);

        $query_delete = "DELETE FROM edificios WHERE id = '$id'";
        mysqli_query($conexion, $query_delete);
        header("Location: table_build.php");
        exit();
    } else {
        echo "<script>alert('No puedes eliminarlo.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Lista Edificios</title>
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <h1 class="title-table">Lista de Edificios</h1>

            <!-- Barra de búsqueda -->
            <div class="search-and-create">
                <form method="GET" action="table_build.php" class="search-form">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="buscar" placeholder="Buscar edificio..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>

            <!-- Contenedor de la tabla -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>CÓDIGO</th>
                            <th>PISOS</th>
                            <th>CUPO</th>
                            <th>DIRECCIÓN</th>
                            <th>TIPO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['id']); ?></td>
                            <td>
                                <img src="<?php echo $fila['imagen'] ? $fila['imagen'] : '../../uploads/usuario.png'; ?>"
                                    alt="Imagen de Edificio" class="user-image">
                            </td>
                            <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['pisos']); ?></td>
                            <td><?php echo htmlspecialchars($fila['cupo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                            <td>
                                <div class="dropdown">
                                <i class="fa-solid fa-ellipsis dropdown-toggle"></i>
                                    <div class="dropdown-content">
                                        <a href="update_building.php?id=<?php echo $fila['id']; ?>"
                                            class="update-button">
                                            <ion-icon name="create-outline"></ion-icon>
                                            Actualizar
                                        </a>
                                        <?php
                                            $query_espacios = "SELECT COUNT(*) as total FROM espacios_academicos WHERE edificio_id = {$fila['id']}";
                                            $resultado_espacios = mysqli_query($conexion, $query_espacios);
                                            $total_espacios = mysqli_fetch_assoc($resultado_espacios)['total'];
                                            $mensaje_confirmacion = $total_espacios > 0
                                                ? "¿Estás seguro de que deseas eliminar este edificio? ¡Hay $total_espacios espacios asociados!"
                                                : "¿Estás seguro de que deseas eliminar este edificio?";
                                        ?>
                                        <a href="?id=<?php echo $fila['id']; ?>" class="delete-button"
                                            onclick="return confirm('<?php echo $mensaje_confirmacion; ?>');">
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
        </main>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>