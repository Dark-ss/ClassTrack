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
$query_total = "SELECT COUNT(*) as total FROM reservaciones WHERE id_usuario = '$id_usuario'";
$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error al obtener el total de reservas: " . mysqli_error($conexion));
}

$total_reservas = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reservas / $registros_por_pagina);

// Obtener las reservas del usuario
$query = "SELECT r.id, r.fecha_inicio, r.fecha_final, r.tipo_reservacion, r.estado,e.codigo AS espacio
        FROM reservaciones r 
        JOIN espacios_academicos e ON r.id_espacio = e.id
        WHERE r.id_usuario = '$id_usuario'
        ORDER BY r.fecha_inicio DESC
        LIMIT $offset, $registros_por_pagina";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

// Búsqueda de reservas
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_reserva = $_POST['id'];

    $query_eliminar = "DELETE FROM reservaciones WHERE id = '$id_reserva' AND id_usuario = '$id_usuario'";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Mis Reservas</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
</head>

<body>
    <main>
    <div class="profile-container">
            <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
            <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
            <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
            <a href="../../php/cerrar_sesion.php" class="logout">
                <img src="../../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
            </a>
            <a href="../../php/config_docente.php" class="config">
                <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
            </a>
            <a href="docente_dashboard.php" class="home-admin">
                <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
            </a>
            <div class="menu-container" id="menu-container">
                <div class="menu-link" onclick="toggleDropdown()">Espacios<span>▼</span>
                </div>
                <div class="submenu" id="submenu">
                    <a href="vista_buildings.php">Edificios</a>
                    <a href="table_disponibilidad.php">Disponibilidad</a>
                    <a href="mis_reservas.php">Mis reservas</a>
                </div>
            </div>
        </div>

        <form method="GET" action="mis_reservas.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar reserva..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form><!--Corregir los estilos de paginación para poder realizar la busqueda-->

        <h1 class="title-table">Lista de Reservas</h1>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Tipo reservación</th>
                    <th>Espacio</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y h:i A', strtotime($fila['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y h:i A', strtotime($fila['fecha_final']))); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo_reservacion']); ?></td>
                        <td><?php echo htmlspecialchars($fila['espacio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td>
                            <a href="#" class="delete-button" onclick="deleteReservation(<?php echo $fila['id']; ?>); return false;">
                                <img src="../../assets/images/delete.png" alt="Eliminar" class="icons-image">
                            </a>
                            <a href="update_reservation.php?id=<?php echo $fila['id']; ?>" class="delete-button">
                                <img src="../../assets/images/update.png" alt="configuracion" class="icons-image">
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <div class="pagination">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?php echo $i; ?>" class="<?php echo $i === $pagina_actual ? 'active' : ''; ?>">
        <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
        <?php endif; ?>
    </div>
</main>

    <script>
function deleteReservation(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta reserva?')) {
        fetch('mis_reservas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reserva eliminada correctamente');
                location.reload(); // Recargar la página para actualizar la lista
            } else {
                alert('No se pudo eliminar la reserva: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al eliminar la reserva');
        });
    }
}

</script>
<script src="../../assets/js/button_update.js"></script>
<script src="../../assets/js/script_menu.js"></script>
</body>

</html>

<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
