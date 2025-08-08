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
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
</head>
<body>
<div class="container">

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