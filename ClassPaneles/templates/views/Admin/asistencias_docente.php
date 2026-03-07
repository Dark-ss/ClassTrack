<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php");
    exit();
}

include '../../php/conexion_be.php';

$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$search_reserva = isset($_GET['buscar']) ? $_GET['buscar'] : '';

/* TOTAL DE REGISTROS */
$query_total = "
    SELECT COUNT(*) as total
    FROM asistencia_reservas ar
    LEFT JOIN estudiantes e ON ar.id_estudiante = e.id
    LEFT JOIN reservaciones r ON ar.id_reservacion = r.id
    WHERE
    e.nombre_completo LIKE '%$search_reserva%' 
    OR r.descripcion LIKE '%$search_reserva%'
";

$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error al obtener asistencias: " . mysqli_error($conexion));
}

$total_reservas = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reservas / $registros_por_pagina);


/* CONSULTA PRINCIPAL */
$query = "
    SELECT 
        ar.id,
        ar.id_reservacion,
        ar.fecha_registro,
        ar.asistio,
        e.nombre_completo AS estudiante,
        r.descripcion,
        r.fecha_inicio,
        r.fecha_final
    FROM asistencia_reservas ar
    LEFT JOIN estudiantes e ON ar.id_estudiante = e.id
    LEFT JOIN reservaciones r ON ar.id_reservacion = r.id
    WHERE 
    e.nombre_completo LIKE '%$search_reserva%' 
    OR r.descripcion LIKE '%$search_reserva%'
    ORDER BY ar.fecha_registro DESC
    LIMIT $offset, $registros_por_pagina
";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error en consulta: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asistencias</title>
<link rel="stylesheet" href="../../assets/css/style_panel.css">
<link rel="shortcut icon" href="../../assets/images/logo2.png">
</head>

<body>

<div class="container">
    <!-- Sidebar (estructura igual a la de la tabla de usuarios de referencia) -->
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

<h1 class="title-table">Consulta de Asistencias</h1>

<div class="search-and-create">
<form method="GET" class="search-form">
<input type="text" name="buscar" placeholder="Buscar estudiante o reserva..."
value="<?php echo htmlspecialchars($search_reserva); ?>">
<button type="submit">Buscar</button>
</form>
</div>

<div class="table-container">
<table class="user-table">

<thead>
<tr>
<th>Id Reserva</th>
<th>Id</th>
<th>Estudiante</th>
<th>Descripción Reserva</th>
<th>Fecha Inicio</th>
<th>Fecha Fin</th>
<th>Asistió</th>
<th>Fecha Registro</th>
</tr>
</thead>

<tbody>

<?php while ($fila = mysqli_fetch_assoc($resultado)): ?>

<tr>
<td><?php echo $fila['id_reservacion']; ?></td>
<td><?php echo $fila['id']; ?></td>
<td><?php echo htmlspecialchars($fila['estudiante']); ?></td>
<td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
<td><?php echo $fila['fecha_inicio']; ?></td>
<td><?php echo $fila['fecha_final']; ?></td>
<td><?php echo $fila['asistio'] ? 'Sí' : 'No'; ?></td>
<td><?php echo $fila['fecha_registro']; ?></td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>"
                    class="pagination-button">Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>"
                    class="pagination-button <?php echo $i === $pagina_actual ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>"
                    class="pagination-button">Siguiente</a>
            <?php endif; ?>
        </div>

</main>
</div>

</body>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</html>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>