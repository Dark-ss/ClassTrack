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

$queryAulasMasUsadas = "
    SELECT a.codigo AS codigo_espacio, e.codigo AS codigo_edificio, COUNT(r.id) AS total_usos
    FROM reservaciones r
    JOIN espacios_academicos a ON r.id_espacio = a.id
    JOIN edificios e ON a.edificio_id = e.id
    WHERE r.id_usuario = '$id_usuario'
    GROUP BY a.codigo, e.codigo
    ORDER BY total_usos DESC
    LIMIT 5";

$resultAulasMasUsadas = mysqli_query($conexion, $queryAulasMasUsadas);

if (!$resultAulasMasUsadas) {
    die("Error en la consulta de aulas más usadas: " . mysqli_error($conexion));
}

$aulasMasUsadas = [];
while ($row = mysqli_fetch_assoc($resultAulasMasUsadas)) {
    // Concatenamos el código del edificio con el código del espacio
    $codigoCompleto = $row['codigo_edificio'] . '-' . $row['codigo_espacio'];
    $aulasMasUsadas[$codigoCompleto] = $row['total_usos'];
}


$queryHorasAulas = "
    SELECT CONCAT(e.codigo, '-', a.codigo) AS codigo_completo, SUM(TIMESTAMPDIFF(HOUR, r.fecha_inicio, r.fecha_final)) AS horas_totales
    FROM reservaciones r
    JOIN espacios_academicos a ON r.id_espacio = a.id
    JOIN edificios e ON a.edificio_id = e.id
    WHERE r.id_usuario = '$id_usuario'
    GROUP BY codigo_completo
    ORDER BY horas_totales DESC
    LIMIT 5";

$resultHorasAulas = mysqli_query($conexion, $queryHorasAulas);
$horasAulas = [];
while ($row = mysqli_fetch_assoc($resultHorasAulas)) {
    $horasAulas[$row['codigo_completo']] = $row['horas_totales'];
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel Docente</title>
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
                        <li><a href="../../php/cerrar_session_docente.php"
                                class="<?php echo $currentFile == 'cerrar_session_docente.php' ? 'active' : ''; ?>">
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
            <div class="charts-container">       
                <div class="chart-big">
                    <h3>Estudiantes Registrados Por Mes (Último Año)</h3>
                    <canvas id="chartEstudiantes"></canvas>
                </div>

                <div class="chart-small">
                    <h3>Aulas Más Utilizadas</h3>
                    <canvas id="chartAulas"></canvas>
                </div>
                <div class="chart-small">
                    <h3>Horas Totales de Uso de Aulas</h3>
                    <canvas id="chartHoras"></canvas>
                </div>
            </div>
        </main>
        <!-- Chatbot -->
<!-- BOTÓN FLOTANTE -->
<div id="chatbot-float-btn">
    <img src="../../assets/images/chatbot.png" alt="Asistente">
</div>

<div id="chatbot-modal">
    <div class="chatbot-container">
        <div class="chatbot-header">
            <span>Asistente Virtual</span>
            <button id="chatbot-close">&times;</button>
        </div>

        <div id="chat-body">
            <div class="bot-msg">
                👋 Hola, soy tu asistente virtual.<br>
                Puedo ayudarte con reservas, aulas y fechas.
            </div>
        </div>

        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Escribe tu mensaje..." />
        </div>
    </div>
</div>
    </div>
    <script>
        var ctx1 = document.getElementById('chartEstudiantes').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Estudiantes Registrados',
                    data: <?php echo json_encode(array_values($estudiantesPorMes)); ?>,
                    borderColor: '#10646C',
                    backgroundColor: '#6FE3C6',
                    fill: true,
                    tension: 0.5,
                    borderWidth: 1 
                }]
            }
        });
        var ctx2 = document.getElementById('chartAulas').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($aulasMasUsadas)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($aulasMasUsadas)); ?>,
                    backgroundColor: ['#10646c', '#6F90E3', '#6FB5E3', '#6FE3C6', '#736FE3']
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'right'  // Mueve las etiquetas al lado derecho
                    }
                }
            }
        });

        var ctx3 = document.getElementById('chartHoras').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($horasAulas)); ?>,
                datasets: [{
                    label: 'Horas Totales',
                    data: <?php echo json_encode(array_values($horasAulas)); ?>,
                    backgroundColor: '#6FB5E3'
                }]
            }
        });
    </script>
    <script>
const btn = document.getElementById("chatbot-float-btn");
const modal = document.getElementById("chatbot-modal");
const closeBtn = document.getElementById("chatbot-close");
const input = document.getElementById("chat-input");
const body = document.getElementById("chat-body");

btn.onclick = () => modal.style.display = "block";
closeBtn.onclick = () => modal.style.display = "none";

modal.onclick = e => {
    if (e.target === modal) modal.style.display = "none";
};

input.addEventListener("keypress", function(e) {
    if (e.key === "Enter" && this.value.trim()) {
        const msg = this.value;
        this.value = "";

        body.innerHTML += `<div class="user-msg">${msg}</div>`;

        fetch("../../php/chatbot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "mensaje=" + encodeURIComponent(msg)
        })
        .then(r => r.text())
        .then(resp => {
            body.innerHTML += `<div class="bot-msg">${resp}</div>`;
            body.scrollTop = body.scrollHeight;
        });
    }
});
</script>



</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</html>