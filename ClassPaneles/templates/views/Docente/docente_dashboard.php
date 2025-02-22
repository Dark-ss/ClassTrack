<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php';

$id_usuario = $_SESSION['id_usuario'];

$queryUsuarios = "SELECT COUNT(*) as total_reservas FROM reservaciones WHERE id_usuario = '$id_usuario'";
$resultUsuarios = mysqli_query($conexion, $queryUsuarios);
$totalReservas = mysqli_fetch_assoc($resultUsuarios)['total_reservas'];

// Consulta para obtener el total de estudiantes
$queryEstudiantes = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";    
$resultEstudiantes = mysqli_query($conexion, $queryEstudiantes);
$totalEstudiantes = mysqli_fetch_assoc($resultEstudiantes)['total_estudiantes'];

$mesActual = date('m');
$anioActual = date('Y');

// Consulta para obtener los estudiantes registrados por mes durante el último año
$queryEstudiantesPorMes = "
    SELECT MONTH(r.fecha_inicio) AS mes_registro, COUNT(re.id_estudiante) AS total_estudiantes
    FROM reservaciones r
    JOIN reservaciones_estudiantes re ON r.id = re.id_reservacion
    WHERE r.id_usuario = '$id_usuario' AND YEAR(r.fecha_inicio) = '$anioActual'
    GROUP BY MONTH(r.fecha_inicio)
    ORDER BY MONTH(r.fecha_inicio)
";


$resultEstudiantesPorMes = mysqli_query($conexion, $queryEstudiantesPorMes);
$estudiantesPorMes = [];

// Inicializamos los meses para asegurarnos de que cada uno esté representado
for ($i = 1; $i <= 12; $i++) {
    $estudiantesPorMes[$i] = 0;  // Inicializar en 0
}

while ($row = mysqli_fetch_assoc($resultEstudiantesPorMes)) {
    $estudiantesPorMes[$row['mes_registro']] = $row['total_estudiantes'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel Docente</title>
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
        <!-- Main Content -->
        <main class="content">
            <p class="welcome-message">Hola, <?php echo htmlspecialchars($nombre_completo); ?>, ¡es bueno verte!</p>
            <p class="welcome-massage2">Accede a todas las herramientas para gestionar los espacios universitarios de
                manera eficiente.</p>
            <div class="stats">
                <div class="stat-card">
                    <h3>Cantidad de reservas</h3>
                    <p><?php echo $totalReservas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Estudiantes en el Sistema</h3>
                    <p><?php echo $totalEstudiantes; ?></p>
                </div>
            </div>
            <div class="chart-container">
                <h3>Estudiantes Registrados Por Mes (Último Año)</h3>
                <canvas id="chartEstudiantes" width="400" height="200"></canvas>
                <script>
                    // Datos para el gráfico (deberías llenar estos valores dinámicamente con PHP)
                    var estudiantesPorMes = <?php echo json_encode(array_values($estudiantesPorMes)); ?>;
                    var labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

                    var ctx = document.getElementById('chartEstudiantes').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'line', // Tipo de gráfico ahora es de línea
                        data: {
                            labels: labels, // Etiquetas para los meses
                            datasets: [{
                                label: 'Estudiantes Registrados',
                                data: estudiantesPorMes, // Datos de los estudiantes por mes
                                borderColor: 'rgba(54, 162, 235, 1)', // Color de la línea
                                backgroundColor: 'rgba(54, 162, 235, 0.2)', // Color de fondo de la línea
                                fill: true, // Rellenar el área bajo la línea
                                tension: 0.4, // Para suavizar las líneas
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1 // Definir tamaño de los pasos en el eje Y
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return 'Estudiantes: ' + tooltipItem.raw;
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>
        </main>
    </div>
</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</html>