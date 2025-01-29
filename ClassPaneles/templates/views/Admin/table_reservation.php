<?php
include '../../php/admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); 
    exit();
}
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 5;
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

//consulta espacio codigo
$query_espacio = "SELECT codigo FROM espacios_academicos";
$result_espacio = mysqli_query($conexion, $query_espacio);

if ($result_espacio && mysqli_num_rows($result_espacio) > 0) {
    $espacio_reserva = mysqli_fetch_assoc($result_espacio);
}   else {
    echo "<script>alert('Espacio no encontrado'); window.location.href='table_reservation.php';</script>";
    exit;
}

// Eliminar reserva
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reservas</title>
    <link rel="stylesheet" href="../../assets/css/style_paneles.css">
</head>

<body>
    <main>
    <style>
        .conflict-row {
            background-color: #f8d7da;
            color: #721c24;
        }
        .no-conflict-row {
            background-color:rgb(219, 248, 215);
            color: green;
        }
    </style>

    <div class="profile-container">
        <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
        <h3 class="profile-name_user"><?php echo htmlspecialchars($nombre_completo); ?></h3>
        <h3 class="profile-name"><?php echo htmlspecialchars($rol); ?></h3>
        <a href="../../php/cerrar_sesion.php" class="logout">
            <img src="../../assets/images/cerrar-sesion.png" alt="Cerrar sesión" class="icons-image">
        </a>
        
        <a href="../../php/config.php" class="config">
            <img src="../../assets/images/config.png" alt="Configuracion" class="icons-image">
        </a>
        <a href="admin_dashboard.php" class="home-admin">
            <img src="../../assets/images/inicio.png" alt="inicio" class="icons-image">
        </a>

        <div class="menu-container" id="menu-container">
            <div class="menu-link" onclick="toggleDropdown()">Cuenta<span>▼</span>
            </div>
            <div class="submenu" id="submenu">
                <a href="create_account.php">Crear Cuenta</a>
                <a href="vista_cuentas.php">cuentas </a>
                <a href="register_students.php">Añadir Estudiantes</a>
                <a href="vista_students.php">Estudiantes</a>
            </div>
        </div>

        <div class="menu-container_espacios" id="menu-container_espacios">
            <div class="menu-link" onclick="toggleDropdown_space()">Espacios<span>▼</span>
            </div>
            <div class="submenu" id="submenu_espacios">
                <a href="register_buldings.php">Añadir Edificios</a>
                <a href="table_build.php">Edificios</a>
                <a href="equipment.php">Equipamientos</a>
                <a href="table_reservation.php">Reservas</a>
            </div>
        </div>
    </div>

    <form method="GET" action="table_reservation.php" class="search-form">
        <input type="text" name="buscar" placeholder="Buscar reserva..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
        <button type="submit">Buscar</button>
    </form>

    <h1 class="title-table">Lista de Reservas</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Tipo Reservación</th>
                <th>Descripcion</th>
                <th>Espacio</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query_pendientes = "SELECT reservaciones.*, usuarios.nombre_completo, espacios_academicos.codigo 
            FROM reservaciones 
            LEFT JOIN usuarios ON reservaciones.id_usuario = usuarios.id 
            LEFT JOIN espacios_academicos ON reservaciones.id_espacio = espacios_academicos.id 
            WHERE reservaciones.estado = 'pendiente'";

        $search = isset($_GET['buscar']) ? $_GET['buscar'] : '';
        $query_pendientes .= " AND (reservaciones.id LIKE '%$search%' 
            OR usuarios.nombre_completo LIKE '%$search%' 
            OR reservaciones.fecha_inicio LIKE '%$search%' 
            OR reservaciones.fecha_final LIKE '%$search%' 
            OR reservaciones.tipo_reservacion LIKE '%$search%') 
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
                    <td>
                        <form method='POST' action='approve_reservation.php' class='btn-container' style='display:inline;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <button type='submit' name='approve' class='btn-approve'>
                                <img src='../../assets/images/aceptar.png' alt='Aprobar' class='btn-icons'>
                            </button>
                            <button type='submit' name='reject' class='btn-reject'>
                                <img src='../../assets/images/rechazar.png' alt='Rechazar' class='btn-icons'>
                            </button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay solicitudes pendientes.</td></tr>";
        }
        ?>
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
            fetch('table_reservation.php', {
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

