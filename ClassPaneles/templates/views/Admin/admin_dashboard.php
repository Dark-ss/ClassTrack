<?php
include '../../php/admin_session.php';
include '../../php/conexion_be.php'; 

date_default_timezone_set('America/Bogota');
// Consulta para contar docentes registrados hoy
$queryEstadoPrevio = "SELECT total_docentes, total_estudiantes, tendencia_docentes, tendencia_estudiantes 
                    FROM estadisticas ORDER BY id DESC LIMIT 1";
$resultEstadoPrevio = mysqli_query($conexion, $queryEstadoPrevio);
$estadoPrevio = mysqli_fetch_assoc($resultEstadoPrevio);

$totalDocentesAnterior = $estadoPrevio ? $estadoPrevio['total_docentes'] : 0;
$totalEstudiantesAnterior = $estadoPrevio ? $estadoPrevio['total_estudiantes'] : 0;
$tendenciaDocentes = $estadoPrevio ? $estadoPrevio['tendencia_docentes'] : 'neutral';
$tendenciaEstudiantes = $estadoPrevio ? $estadoPrevio['tendencia_estudiantes'] : 'neutral';

// OBTENER DATOS ACTUALES EN TIEMPO REAL
$queryTotalDocentes = "SELECT COUNT(*) AS total_docentes FROM usuarios WHERE rol = 'docente'";
$resultTotalDocentes = mysqli_query($conexion, $queryTotalDocentes);
$totalDocentes = mysqli_fetch_assoc($resultTotalDocentes)['total_docentes'];

$queryTotalEstudiantes = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";
$resultTotalEstudiantes = mysqli_query($conexion, $queryTotalEstudiantes);
$totalEstudiantes = mysqli_fetch_assoc($resultTotalEstudiantes)['total_estudiantes'];

// DETERMINAR TENDENCIA
if ($totalDocentes > $totalDocentesAnterior) {
    $tendenciaDocentes = 'up';
} elseif ($totalDocentes < $totalDocentesAnterior) {
    $tendenciaDocentes = 'down';
}

if ($totalEstudiantes > $totalEstudiantesAnterior) {
    $tendenciaEstudiantes = 'up';
} elseif ($totalEstudiantes < $totalEstudiantesAnterior) {
    $tendenciaEstudiantes = 'down';
}

// ACTUALIZAR BASE DE DATOS SOLO SI HAY CAMBIO
if ($totalDocentes != $totalDocentesAnterior || $totalEstudiantes != $totalEstudiantesAnterior) {
    $insertQuery = "INSERT INTO estadisticas (total_docentes, total_estudiantes, tendencia_docentes, tendencia_estudiantes) 
                    VALUES ($totalDocentes, $totalEstudiantes, '$tendenciaDocentes', '$tendenciaEstudiantes')";
    mysqli_query($conexion, $insertQuery);
}

// FUNCIÓN PARA OBTENER EL ÍCONO DE TENDENCIA
function obtenerIcono($tendencia) {
    if ($tendencia == 'up') {
        return '<i class="ph ph-arrow-circle-up" style="color: green"></i>'; // Aumento
    } elseif ($tendencia == 'down') {
        return '<i class="ph ph-arrow-circle-down" style="color: red"></i>'; // Disminución
    } else {
        return '<i class="ph ph-arrow-right-circle" style="color: gray;"></i>'; // Sin cambios
    }
}

$iconoDocentes = obtenerIcono($tendenciaDocentes);
$iconoEstudiantes = obtenerIcono($tendenciaEstudiantes);

$fechaHoy = date('Y-m-d');

$queryMovimientos = "
(
    SELECT CAST(nombre_completo AS CHAR(100)) AS nombre_completo, 'docente' AS tipo, 'añadido' AS estado 
    FROM usuarios 
    WHERE rol = 'docente' AND DATE(fecha_registro) = '$fechaHoy'
)
UNION
(
    SELECT nombre_completo, 'estudiante' AS tipo, 'añadido' AS estado 
    FROM estudiantes 
    WHERE DATE(fecha_registro) = '$fechaHoy'
)
UNION
(
    SELECT CAST(nombre_completo AS CHAR(100)) AS nombre_completo, 'docente' AS tipo, 'removido' AS estado 
    FROM usuarios_eliminados
    WHERE DATE(fecha_eliminacion) = '$fechaHoy'
)
UNION
(
    SELECT nombre_completo, 'estudiante' AS tipo, 'removido' AS estado 
    FROM estudiantes_eliminados
    WHERE DATE(fecha_eliminacion) = '$fechaHoy'
)
ORDER BY tipo;";

//Guardar registros eliminados para consulta de estadistica
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $guardarEliminado = "INSERT INTO usuarios_eliminados (nombre_completo, rol) 
                        SELECT nombre_completo, rol FROM usuarios WHERE id = $id";
    if (!mysqli_query($conexion, $guardarEliminado)) {
        die("Error al registrar usuario eliminado: " . mysqli_error($conexion));
    }

    echo "Usuario registrado como eliminado.";
}

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $guardarEstudianteEliminado = "INSERT INTO estudiantes_eliminados (nombre_completo) 
                                SELECT nombre_completo FROM estudiantes WHERE id = $id";
    if (!mysqli_query($conexion, $guardarEstudianteEliminado)) {
        die("Error al registrar estudiante eliminado: " . mysqli_error($conexion));
    }

    echo "Estudiante registrado como eliminado.";
}

$resultMovimientos = mysqli_query($conexion, $queryMovimientos);
$movimientos = [];
if (!$resultMovimientos) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
while ($row = mysqli_fetch_assoc($resultMovimientos)) {
    $movimientos[] = $row;
}

$reservasPorMes = array_fill(0, 12, 0);

$queryReservas = "
    SELECT MONTH(registro_reserva) AS mes, COUNT(*) AS total_reservas 
    FROM reservaciones 
    WHERE YEAR(registro_reserva) = YEAR(CURDATE())
    GROUP BY MONTH(registro_reserva)
";

$resultReservas = mysqli_query($conexion, $queryReservas);

while ($fila = mysqli_fetch_assoc($resultReservas)) {
    $mes = (int)$fila['mes'] - 1; // Restar 1 para ajustar al índice del array
    $reservasPorMes[$mes] = (int)$fila['total_reservas'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../../assets/css/style_panel.css?v=1 ">
    <link rel="shortcut icon" href="../../assets/images/logo1.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>

<body>
    <div class="container">
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
                                <ion-icon name="school-outline"></ion-icon> Estudiantes
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de Espacios</p>
                    <ul>
                        <li><a href="./register_buldings.php"
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
        <main class="content">
            <p class="welcome-message">Hola, <?php echo htmlspecialchars($nombre_completo); ?>, ¡es bueno verte!</p>
            <p class="welcome-massage2">Accede a todas las herramientas para gestionar los espacios universitarios de
                manera eficiente.</p>
        <div class="content-stadistic">
            <div class="stats-admin">
                <div class="stat-card-admin">
                    <div class="stats-row">
                        <div class="stat-item">
                            <h3>Docentes registrados</h3>
                        </div>
                        <div class="stat-item">
                            <h3>Estudiantes registrados</h3>
                        </div>
                    </div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <p><?php echo $totalDocentes . ' ' . $iconoDocentes; ?></p>
                        </div>
                        <div class="stat-item">
                            <p><?php echo $totalEstudiantes . ' ' . $iconoEstudiantes; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Lista de cambios recientes -->
            <div class="list-container">
                <h3>Últimos cambios</h3>
                <?php if (!empty($movimientos)) : ?>
                    <?php foreach ($movimientos as $mov) : ?>
                        <div class="list-item">
                            <?php
                            $icon = ($mov['estado'] === 'añadido') ? '<span class="icon-added">✔</span>' : '<span class="icon-removed">✖</span>';
                            ?>
                            <?php echo $icon . ' ' . ucfirst($mov['tipo']) . ': ' . ucfirst($mov['nombre_completo']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No hay cambios recientes.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-big">
            <h3>Reservas por Mes (Último Año)</h3>
            <canvas id="chartReservas"></canvas>
        </div>
        </main>
    </div>
</body>
<script>
    var ctx2 = document.getElementById('chartReservas').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Reservas Totales',
                data: <?php echo json_encode(array_values($reservasPorMes)); ?>,
                borderColor: '#FF5733',
                backgroundColor: '#FFB399',
                fill: true,
                tension: 0.5,
                borderWidth: 1
            }]
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    console.log(<?php echo json_encode(array_values($reservasPorMes)); ?>);
</script>
<!-- Ionicons Script -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>

</html>