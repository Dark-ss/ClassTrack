<?php
include '../../php/docente_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); 
    exit();
}
include '../../php/conexion_be.php';

$id_usuario = $_SESSION['id_usuario'];

// Paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Total de reservas del usuario
$query_total = "SELECT COUNT(*) as total FROM mensajes WHERE id_remitente = '$id_usuario'";
$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error al obtener el total de solicitudes: " . mysqli_error($conexion));
}

$total_reservas = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reservas / $registros_por_pagina);

// Búsqueda de reservas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$query = "SELECT id, mensaje, fecha_registro, nivel_prioridad, tipo, respuesta 
        FROM mensajes 
        WHERE id_remitente = $id_usuario
        AND (fecha_registro LIKE '%$search%'
        OR nivel_prioridad LIKE '%$search%' 
        OR tipo LIKE '%$search%')
        ORDER BY fecha_registro DESC
        LIMIT $offset, $registros_por_pagina";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_mensaje = $_POST['id'];

    $query_eliminar = "DELETE FROM mensajes WHERE id = '$id_mensaje' AND id_remitente = '$id_usuario'";
    $resultado_eliminar = mysqli_query($conexion, $query_eliminar);

    if ($resultado_eliminar) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ver Mis Solicitudes</title>
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<div class="container-docente">
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
                        <li><a href="docente_dashboard.php"
                                class="<?php echo $currentFile == 'docente_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="vista_buildings.php"
                                class="<?php echo $currentFile == 'vista_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="table_disponibilidad.php"
                                class="<?php echo $currentFile == 'table_disponibilidad.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Disponibilidad
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de reservas</p>
                    <ul>
                        <li><a href="mis_reservas.php"
                                class="<?php echo $currentFile == 'mis_reservas.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Ayuda</p>
                    <ul>
                        <li><a href="suport.php"
                                class="<?php echo $currentFile == 'suport.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Soporte técnico
                            </a></li>
                    </ul>
                    <ul>
                        <li><a href="mis_solicitudes.php"
                                class="<?php echo $currentFile == 'mis_solicitudes.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis solicitudes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="../../php/config_docente.php"
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

        <main class="main-content-cuenta">
            <h1 class="title-table">Lista de Solicitudes</h1>
            <!-- Barra de búsqueda -->
            <div class="search-and-create">
                <form method="GET" action="mis_solicitudes.php" class="search-form">
                    <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    <input type="text" name="buscar" placeholder="Buscar solicitudes..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <div class="table-container">
    <table class="user-table">
        <thead>
            <tr>
                <th>Id</th>
                <th>Mensaje</th>
                <th>Prioridad</th>
                <th>Tipo</th>
                <th>Fecha Registro</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                <tr class="<?php echo (!is_null($fila['respuesta']) && $fila['respuesta'] !== '') ? 'respondida' : ''; ?>">
                    <td><?php echo htmlspecialchars($fila['id']); ?></td>
                    <td><?php echo htmlspecialchars($fila['mensaje']); ?></td>
                    <td><?php echo htmlspecialchars($fila['nivel_prioridad']); ?></td>
                    <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y h:i A', strtotime($fila['fecha_registro']))); ?></td>
                    <td>
                    <div class="dropdown">
                            <ion-icon name="ellipsis-horizontal-sharp" class="dropdown-toggle"></ion-icon>
                            <div class="dropdown-content">
                                <a href="#" class="update-button" 
                                    onclick="verSolicitud(
                                        '<?php echo $fila['id']; ?>', 
                                        '<?php echo htmlspecialchars($fila['mensaje']); ?>', 
                                        '<?php echo htmlspecialchars($fila['nivel_prioridad']); ?>', 
                                        '<?php echo htmlspecialchars($fila['tipo']); ?>', 
                                        '<?php echo htmlspecialchars(date('d/m/Y h:i A', strtotime($fila['fecha_registro']))); ?>', 
                                        '<?php echo htmlspecialchars($fila['respuesta'] ?? 'Sin respuesta aún'); ?>'
                                    );">
                                    <ion-icon name="create-outline"></ion-icon>
                                    Ver solicitud
                                </a>
                                <a href="#" class="delete-button" onclick="deleteReservation(<?php echo $fila['id']; ?>); return false;">
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
<!-- Modal para ver la solicitud -->
<div id="modalSolicitud" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2>Detalles de la Solicitud</h2>
        <p><strong>ID:</strong> <span id="modal-id"></span></p>
        <p><strong>Mensaje:</strong> <span id="modal-mensaje"></span></p>
        <p><strong>Prioridad:</strong> <span id="modal-prioridad"></span></p>
        <p><strong>Tipo:</strong> <span id="modal-tipo"></span></p>
        <p><strong>Fecha de Registro:</strong> <span id="modal-fecha"></span></p>
        <hr>
        <h3>Respuesta del Administrador</h3>
        <p id="modal-respuesta"></p>
    </div>
</div>
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
<style>
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 10% auto; padding: 20px; width: 50%; border-radius: 10px; text-align: center; }
    .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    .respondida { background-color: #d4edda; } /* Verde para solicitudes respondidas */
</style>
<script>
function deleteReservation(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta solicitud?')) {
        fetch('mis_solicitudes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Solicitued eliminada correctamente');
                location.reload(); // Recargar la página para actualizar la lista
            } else {
                alert('No se pudo eliminar la Solicitud: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al eliminar la Solicitud');
        });
    }
}

function verSolicitud(id, mensaje, prioridad, tipo, fecha, respuesta) {
        document.getElementById('modal-id').textContent = id;
        document.getElementById('modal-mensaje').textContent = mensaje;
        document.getElementById('modal-prioridad').textContent = prioridad;
        document.getElementById('modal-tipo').textContent = tipo;
        document.getElementById('modal-fecha').textContent = fecha;
        document.getElementById('modal-respuesta').textContent = respuesta;
        document.getElementById('modalSolicitud').style.display = 'block';
    }

    function cerrarModal() {
        document.getElementById('modalSolicitud').style.display = 'none';
    }

</script>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>

<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>