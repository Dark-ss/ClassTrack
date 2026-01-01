<?php
include '../../php/admin_session.php'; 
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 7;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta total de registros (para paginación)
$query_total = "SELECT COUNT(*) as total
                FROM espacios_equipamiento";
$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error en query_total: " . mysqli_error($conexion) . "<br>SQL: " . $query_total);
}

$total_row = mysqli_fetch_assoc($resultado_total);
$total = $total_row['total'];
$total = $total_row ? $total_row['total'] : 0;
$total_paginas = $total > 0 ? ceil($total / $registros_por_pagina) : 1;


// Consulta principal
$query = "SELECT ee.id as relacion_id, ed.nombre as edificio ,ea.codigo as espacio, e.nombre as equipamiento, e.imagen, 
            ee.cantidad, ee.estado
            FROM espacios_equipamiento ee
            INNER JOIN espacios_academicos ea ON ee.espacio_id = ea.id
            INNER JOIN edificios ed ON ea.edificio_id = ed.id
            INNER JOIN equipamiento e ON ee.equipamiento_id = e.id
            WHERE e.nombre LIKE '%$search%' OR ed.nombre LIKE '%$search%' OR ee.id LIKE '%$search%'
            OR ea.codigo LIKE '%$search%' OR ee.estado LIKE '%$search%'
            ORDER BY ee.id DESC
            LIMIT $registros_por_pagina OFFSET $offset";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error en query principal: " . mysqli_error($conexion) . "<br>SQL: " . $query);
}

// Acción de eliminación
if (isset($_GET['eliminar'])) {
    $id_relacion = $_GET['eliminar'];
    $query_delete = "DELETE FROM espacios_equipamiento WHERE id = '$id_relacion'";
    mysqli_query($conexion, $query_delete);
    header("Location: table_equipament_spaces.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista Equipamientos Asociados</title>
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

    <main class="main-content-cuenta">
        <h1 class="title-table">Equipamientos en Espacios Académicos</h1>

        <!-- Barra de búsqueda -->
        <div class="search-and-create">
            <form method="GET" action="table_equipament_spaces.php" class="search-form">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="buscar" placeholder="Buscar espacio o equipamiento..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table class="user-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>EDIFICIO</th>
                    <th>ESPACIO</th>
                    <th>EQUIPAMIENTO</th>
                    <th>IMAGEN</th>
                    <th>CANTIDAD</th>
                    <th>ESTADO</th>
                    <th>ACCIÓN</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['relacion_id']); ?></td>
                        <td><?php echo htmlspecialchars($fila['edificio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['espacio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['equipamiento']); ?></td>
                        <td>
                            <img src="<?php echo $fila['imagen'] ? $fila['imagen'] : '../../uploads/usuario.png'; ?>" 
                                alt="Imagen" class="user-image">
                        </td>
                        <td><?php echo htmlspecialchars($fila['cantidad']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td>
                            <a href="?eliminar=<?php echo $fila['relacion_id']; ?>" 
                                class="delete-button"
                                onclick="return confirm('¿Seguro que deseas eliminar este equipamiento del espacio?');">
                                <ion-icon name="trash-outline"></ion-icon> Eliminar
                            </a>
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
</body>
</html>
<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>