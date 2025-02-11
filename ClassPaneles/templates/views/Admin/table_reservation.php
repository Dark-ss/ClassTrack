<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); 
    exit();
}
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 4;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Total de reservas
$query_total = "SELECT COUNT(*) as total FROM reservaciones WHERE estado='pendiente'";
$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error al obtener el total de reservas: " . mysqli_error($conexion));
}

$total_reservas = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reservas / $registros_por_pagina);

// Consulta espacio código
$query_espacio = "SELECT codigo FROM espacios_academicos";
$result_espacio = mysqli_query($conexion, $query_espacio);

if ($result_espacio && mysqli_num_rows($result_espacio) > 0) {
    $espacio_reserva = mysqli_fetch_assoc($result_espacio);
} else {
    echo "<script>alert('Espacio no encontrado'); window.location.href='table_reservation.php';</script>";
    exit;
}

// Eliminar reserva (procesa peticiones POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_reserva = $_POST['id'];

    $query_eliminar = "DELETE FROM reservaciones WHERE id = '$id_reserva'";
    $resultado_eliminar = mysqli_query($conexion, $query_eliminar);

    if ($resultado_eliminar) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
    exit();
}

// Variable para saber en qué página nos encontramos (para el sidebar activo)
$currentFile = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ver Reservas</title>
<link rel="stylesheet" href="../../assets/css/style_panel.css?v=1">
<style>
    .conflict-row {
    background-color: #f8d7da;
    color: #721c24;
    }
    .no-conflict-row {
    background-color: rgb(219, 248, 215);
    color: green;
    }
    /* Si se requiere ajustar márgenes, paddings u otros detalles, se pueden agregar aquí */
</style>
</head>
<body>
<div class="container">
    <!-- Sidebar (estructura igual a la de la tabla de usuarios de referencia) -->
    <aside class="sidebar">
    <div class="logo">
        <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" width="150">
    </div>
    <nav class="menu">
        <div class="menu-group">
            <p class="menu-title">Menú Principal</p>
            <ul>
                <li>
                    <a href="admin_dashboard.php" class="<?php echo $currentFile == 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <ion-icon name="home-outline"></ion-icon> Inicio
                    </a>
                </li>
                <li>
                    <a href="vista_cuentas.php" class="<?php echo $currentFile == 'vista_cuentas.php' ? 'active' : ''; ?>">
                    <ion-icon name="people-outline"></ion-icon> Cuentas
                    </a>
                </li>
                <li>
                    <a href="register_students.php" class="<?php echo $currentFile == 'register_students.php' ? 'active' : ''; ?>">
                    <ion-icon name="school-outline"></ion-icon> Añadir Estudiantes
                    </a>
                </li>
                <li>
                    <a href="vista_students.php" class="<?php echo $currentFile == 'vista_students.php' ? 'active' : ''; ?>">
                    <ion-icon name="reader-outline"></ion-icon> Estudiantes
                    </a>
                </li>
            </ul>
        </div>
        <div class="menu-group">
            <p class="menu-title">Gestión de Espacios</p>
            <ul>
                <li>
                    <a href="register_buldings.php" class="<?php echo $currentFile == 'register_buldings.php' ? 'active' : ''; ?>">
                    <ion-icon name="business-outline"></ion-icon> Añadir Edificios
                    </a>
                </li>
                <li>
                    <a href="table_build.php" class="<?php echo $currentFile == 'table_build.php' ? 'active' : ''; ?>">
                    <ion-icon name="list-outline"></ion-icon> Edificios
                    </a>
                </li>
                <li>
                    <a href="equipment.php" class="<?php echo $currentFile == 'equipment.php' ? 'active' : ''; ?>">
                    <ion-icon name="construct-outline"></ion-icon> Equipamientos
                    </a>
                </li>
                <li>
                    <a href="table_reservation.php" class="<?php echo $currentFile == 'table_reservation.php' ? 'active' : ''; ?>">
                    <ion-icon name="calendar-outline"></ion-icon> Reservas
                    </a>
                </li>
            </ul>
        </div>
        <div class="menu-group">
            <p class="menu-title">Configuración</p>
            <ul>
                <li>
                    <a href="../../php/config.php" class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                    <ion-icon name="settings-outline"></ion-icon> Ajustes
                    </a>
                </li>
                <li>
                    <a href="../../php/cerrar_sesion.php" class="<?php echo $currentFile == 'cerrar_sesion.php' ? 'active' : ''; ?>">
                    <ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="divider"></div>
    <div class="profile">
        <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
        <div>
            <p class="user-name"><?php echo htmlspecialchars($nombre_completo); ?></p>
            <p class="user-email"><?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?></p>
        </div>
    </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content-cuenta">
    <h1 class="title-table">Lista de Reservas</h1>
    <div class="search-and-export">
        <form method="GET" action="table_reservation.php" class="search-form">
            <ion-icon name="search-outline" class="search-icon"></ion-icon>
            <input type="text" name="buscar" placeholder="Buscar reserva..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <div class="export-dropdown-container">
            <button id="exportButton" class="dropdown-btn" onclick="toggleExportDropdown(event)">Exportar ▼</button>
            <div id="exportDropdown" class="dropdown-content-reservation">
                <form id="exportForm" action="../../php/export.php" method="post">
                    <input type="hidden" name="export" value="1">
                    <p onclick="submitExportForm('excel')">Excel</p>
                    <p onclick="submitExportForm('pdf')">PDF</p>
                    <p onclick="submitExportForm('png')">Imagen (PNG)</p>
                </form>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USUARIO</th>
                    <th>FECHA INICIO</th>
                    <th>FECHA FIN</th>
                    <th>TIPO RESERVACIÓN</th>
                    <th>DESCRIPCIÓN</th>
                    <th>ESPACIO</th>
                    <th>EDIFICIO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query_pendientes = "SELECT r.*, us.nombre_completo, ea.codigo, e.nombre AS nombre_edificio
            FROM reservaciones r
            LEFT JOIN usuarios us ON r.id_usuario = us.id 
            LEFT JOIN espacios_academicos ea ON r.id_espacio = ea.id 
            LEFT JOIN edificios e ON ea.edificio_id = e.id
            WHERE r.estado = 'pendiente'";
            
            $search = isset($_GET['buscar']) ? $_GET['buscar'] : '';
            $query_pendientes .= " AND (r.id LIKE '%$search%' 
                OR us.nombre_completo LIKE '%$search%' 
                OR r.fecha_inicio LIKE '%$search%' 
                OR r.fecha_final LIKE '%$search%' 
                OR r.tipo_reservacion LIKE '%$search%'
                OR ea.codigo LIKE '%$search%')
                LIMIT $offset, $registros_por_pagina";
        
            $result_pendientes = mysqli_query($conexion, $query_pendientes);
        
            if (mysqli_num_rows($result_pendientes) > 0) {
                while ($row = mysqli_fetch_assoc($result_pendientes)) {
                    // Verificar conflicto de horario
                    $conflict_query = "SELECT COUNT(*) as conflictos 
                    FROM reservaciones 
                    WHERE id_espacio = '{$row['id_espacio']}'
                    AND id != '{$row['id']}'
                    AND (
                        ('{$row['fecha_inicio']}' BETWEEN fecha_inicio AND fecha_final) OR
                        ('{$row['fecha_final']}' BETWEEN fecha_inicio AND fecha_final) OR
                        (fecha_inicio BETWEEN '{$row['fecha_inicio']}' AND '{$row['fecha_final']}') OR
                        (fecha_final BETWEEN '{$row['fecha_inicio']}' AND '{$row['fecha_final']}')
                    ) AND NOT (
                    '{$row['fecha_inicio']}' = fecha_final OR 
                    '{$row['fecha_final']}' = fecha_inicio)";
        
                    $conflict_result = mysqli_query($conexion, $conflict_query);
                    if ($conflict_result) {
                        $conflict_data = mysqli_fetch_assoc($conflict_result);
                        if ($conflict_data['conflictos'] > 0) {
                            $row_class = 'conflict-row';
                        } else {
                            $row_class = 'no-conflict-row';
                        }
                    }
        
                    echo "<tr class='{$row_class}'>
                        <td>{$row['id']}</td>
                        <td>{$row['nombre_completo']}</td>
                        <td>{$row['fecha_inicio']}</td>
                        <td>{$row['fecha_final']}</td>
                        <td>{$row['tipo_reservacion']}</td>
                        <td>{$row['descripcion']}</td>
                        <td>{$row['codigo']}</td>
                        <td>{$row['nombre_edificio']}</td>
                        <td>
                            <div class='dropdown'>
                                <form method='POST' action='approve_reservation.php' class='btn-container' style='display:inline;'>
                                <ion-icon name='ellipsis-horizontal-sharp' class='dropdown-toggle'></ion-icon>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                <div class='dropdown-content'>
                                    <button type='submit' name='approve' class='btn-approve'>
                                    <ion-icon name='create-outline'></ion-icon>
                                    Aceptar
                                    </button>
                                    <button type='submit' name='reject' class='btn-reject'>
                                    <ion-icon name='trash-outline'></ion-icon>
                                    Rechazar
                                    </button>
                                </div>
                                </form>
                            <div>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No hay solicitudes pendientes.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>" class="pagination-button">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search); ?>" class="pagination-button <?php echo $i === $pagina_actual ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>" class="pagination-button">Siguiente</a>
        <?php endif; ?>
    </div>
    </main>
</div>

<script>
    function toggleExportDropdown(event) {
        event.stopPropagation();
        document.getElementById("exportDropdown").classList.toggle("show");
    }
    function submitExportForm(format) {
        document.getElementById("exportForm").submit();
    }
    // Cierra el dropdown si se hace clic fuera
    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-btn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>


