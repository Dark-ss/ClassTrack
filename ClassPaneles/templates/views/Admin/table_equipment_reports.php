<?php
include '../../php/admin_session.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php");
    exit();
}
include '../../php/conexion_be.php';

$registros_por_pagina = 4;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$query_total = "SELECT COUNT(*) as total FROM reportes_equipamiento WHERE estado='pendiente'";
$resultado_total = mysqli_query($conexion, $query_total);
$total_reportes = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reportes / $registros_por_pagina);

$currentFile = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Equipamiento</title>
    <link rel="stylesheet" href="../../assets/css/style_panel.css"> 
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
</head>
<body>
<div class="container">
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
        <h1 class="title-table">Solicitudes de Reporte de Equipamiento</h1>

        <div class="search-and-export">
            <form method="GET" action="table_equipment_reports.php" class="search-form">
                <ion-icon name="search-outline" class="search-icon"></ion-icon>
                <input type="text" name="buscar" placeholder="Buscar reporte..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DOCENTE</th>
                        <th>EQUIPAMIENTO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ESPACIO</th>
                        <th>FECHA REPORTE</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $search = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, $_GET['buscar']) : '';

                /*$query = "SELECT re.*, us.nombre_completo, eq.nombre AS nombre_equipamiento, ea.codigo AS espacio
                    FROM reportes_equipamiento re
                    LEFT JOIN usuarios us ON re.id_usuario = us.id
                    LEFT JOIN espacios_equipamiento ee ON re.espacio_equipamiento_id = ee.equipamiento_id
                    LEFT JOIN equipamiento eq ON ee.equipamiento_id = eq.id
                    LEFT JOIN espacios_academicos ea ON re.espacio_id = ea.id
                    WHERE re.estado = 'Disponible'";*/

                    $query= "SELECT re.*, us.nombre_completo, eq.nombre AS nombre_equipamiento, ea.codigo AS espacio
                    FROM solicitudes_reporte_docente re
                    LEFT JOIN usuarios us ON re.id_usuario = us.id
                    LEFT JOIN espacios_equipamiento ee ON re.espacio_equipamiento_id = ee.id
                    LEFT JOIN equipamiento eq ON ee.equipamiento_id = eq.id
                    LEFT JOIN espacios_academicos ea ON ee.espacio_id = ea.id
                    WHERE re.estado_solicitud = 'pendiente'";

                if (!empty($search)) {
                    $query .= " AND (
                        re.id LIKE '%$search%' OR
                        us.nombre_completo LIKE '%$search%' OR
                        eq.nombre LIKE '%$search%' OR
                        re.descripcion LIKE '%$search%' OR
                        ea.codigo LIKE '%$search%'
                    )";
                }

                $query .= " LIMIT $offset, $registros_por_pagina";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    echo "<tr><td colspan='7'>❌ Error en la consulta: " . mysqli_error($conexion) . "</td></tr>";
} elseif (mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        echo "<tr>
            <td>{$row['id_usuario']}</td>
            <td>{$row['nombre_completo']}</td>
            <td>{$row['nombre_equipamiento']}</td>
            <td>{$row['descripcion']}</td>
            <td>{$row['espacio']}</td>
            <td>{$row['fecha_solicitud']}</td>
            <td>
                <div class='dropdown'>
                    <form method='POST' action='approve_equipment_report.php' class='btn-container' style='display:inline;'>
                        <ion-icon name='ellipsis-horizontal-sharp' class='dropdown-toggle'></ion-icon>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <div class='dropdown-content'>
                            <button type='submit' name='approve' class='btn-approve'>
                                <ion-icon name='checkmark-outline'></ion-icon>
                                Aceptar
                            </button>
                            <button type='button' class='btn-reject' onclick='mostrarMotivoRechazo({$row['id']})'>
                                <ion-icon name='close-outline'></ion-icon>
                                Rechazar
                            </button>
                        </div>
                    </form>
                </div>
            </td>
        </tr>";
    }
    } else {
        echo "<tr><td colspan='7'>No hay reportes pendientes.</td></tr>";
    }

                ?>
                </tbody>
            </table>
        </div>

        <div id="motivoModal" class="modal-reject">
            <div class="modal-content-reject">
                <h2>Motivo de Rechazo</h2>
                <textarea id="motivo-text" class="motivo_text" placeholder="Escriba el motivo del rechazo"></textarea>
                <div>
                    <button class="close-reject" onclick="closeModal()">Cancelar</button>
                    <button class="button-reject" onclick="rechazarReporte()">Confirmar Rechazo</button>
                </div>
            </div>
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
function mostrarMotivoRechazo(idReporte) {
    document.getElementById("motivo-text").dataset.reporteId = idReporte;
    document.getElementById("motivoModal").style.display = "block";
}

function closeModal() {
    document.getElementById("motivoModal").style.display = "none";
}

function rechazarReporte() {
    let motivoInput = document.getElementById("motivo-text");
    let idReporte = motivoInput.dataset.reporteId;
    let motivo = motivoInput.value.trim();

    if (motivo === "") {
        alert("Por favor, ingrese un motivo de rechazo.");
        return;
    }

    fetch("rechazar_reporte_equipamiento.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${encodeURIComponent(idReporte)}&motivo_rechazo=${encodeURIComponent(motivo)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Reporte rechazado correctamente.");
            window.location.reload();
        } else {
            alert("Error al rechazar el reporte: " + data.error);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Hubo un problema con la solicitud.");
    });

    closeModal();
}
</script>

<script src="../../assets/js/script_menu.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>