<?php
include '../../php/docente_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php");
    exit();
}
include '../../php/conexion_be.php';

// Paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Total de reservas del usuario
$query_total = "SELECT COUNT(*) as total FROM espacios_academicos";
$resultado_total = mysqli_query($conexion, $query_total);

if (!$resultado_total) {
    die("Error al obtener el total de reservas: " . mysqli_error($conexion));
}

$total_reservas = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_reservas / $registros_por_pagina);

$search_reserva = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta para obtener espacios académicos
$query = "
    SELECT ea.id, ea.codigo, ea.tipo_espacio, ea.imagen, ed.nombre AS edificio_nombre
    FROM espacios_academicos ea
    JOIN edificios ed ON ea.edificio_id = ed.id
    WHERE ea.codigo LIKE '%$search_reserva%' 
    OR ea.tipo_espacio LIKE '%$search_reserva%' 
    OR ed.nombre LIKE '%$search_reserva%' 
    ORDER BY ed.nombre DESC
    LIMIT $offset, $registros_por_pagina
";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

ini_set('date.timezone', 'America/Bogota');

// Función para calcular bloques de disponibilidad
function calcularBloquesDisponibilidad($conexion, $idEspacio, $horasDia) {
    // Consultar reservas para el espacio en el día actual
    $hoy = date('Y-m-d');
    $queryReservas = "
        SELECT fecha_inicio, fecha_final
        FROM reservaciones
        WHERE id_espacio = $idEspacio AND DATE(fecha_inicio) = '$hoy'
        AND estado = 'aceptada'
    ";
    $resultadoReservas = mysqli_query($conexion, $queryReservas);

    // Convertir reservas en franjas ocupadas
    $horasOcupadas = [];
    while ($reserva = mysqli_fetch_assoc($resultadoReservas)) {
        $inicioReserva = strtotime($reserva['fecha_inicio']);
        $finReserva = strtotime($reserva['fecha_final']);

        foreach ($horasDia as $indice => $hora) {
            $inicioHora = strtotime("$hoy {$hora['inicio']}");
            $finHora = strtotime("$hoy {$hora['fin']}");

            if (($inicioHora >= $inicioReserva && $inicioHora < $finReserva) ||
                ($finHora > $inicioReserva && $finHora <= $finReserva)) {
                $horasOcupadas[] = $indice; // Guardar índice de la hora ocupada
            }
        }
    }

    // Crear bloques de disponibilidad
    $disponibilidad = [];
    $bloqueInicio = null;

    foreach ($horasDia as $indice => $hora) {
        if (!in_array($indice, $horasOcupadas)) {
            if ($bloqueInicio === null) {
                $bloqueInicio = $hora['inicio'];
            }
        } else {
            if ($bloqueInicio !== null) {
                $disponibilidad[] = formatearHora($bloqueInicio) . ' - ' . formatearHora($horasDia[$indice - 1]['fin']);
                $bloqueInicio = null;
            }
        }
    }

    // Agregar el último bloque si termina disponible
    if ($bloqueInicio !== null) {
        $disponibilidad[] = formatearHora($bloqueInicio) . ' - ' . formatearHora(end($horasDia)['fin']);
    }

    return implode(',<br>', $disponibilidad);
}

// Función para formatear hora a 12 horas (AM/PM)
function formatearHora($hora) {
    return date('h:i A', strtotime($hora));
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
    <title>Espacios Académicos</title>
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

    <main class="main-content-cuenta">
        <h1 class="title-table">Lista de disponibilidad</h1>
        <div class="search-and-create">
                <form method="GET" action="table_disponibilidad.php" class="search-form">
                    <ion-icon name="search-outline" class="search-icon"></ion-icon>
                    <input type="text" id="search-input" name="buscar" placeholder="Buscar edificio..."
                        value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
            </div>
        <div class="table-container">
            <table class="user-table user-table-avial">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Tipo de Espacio</th>
                        <th>Edificio</th>
                        <th style="width: 200px;">Disponibilidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Definir horario del día
                    $horarioDia = [
                        ['inicio' => '08:00', 'fin' => '09:00'],
                        ['inicio' => '09:00', 'fin' => '10:00'],
                        ['inicio' => '10:00', 'fin' => '11:00'],
                        ['inicio' => '11:00', 'fin' => '12:00'],
                        ['inicio' => '12:00', 'fin' => '13:00'],
                        ['inicio' => '13:00', 'fin' => '14:00'],
                        ['inicio' => '14:00', 'fin' => '15:00'],
                        ['inicio' => '15:00', 'fin' => '16:00'],
                        ['inicio' => '16:00', 'fin' => '17:00'],
                        ['inicio' => '17:00', 'fin' => '18:00'],
                        ['inicio' => '18:00', 'fin' => '19:00'],
                        ['inicio' => '19:00', 'fin' => '20:00'],
                        ['inicio' => '21:00', 'fin' => '22:00'],
                    ];

                    while ($fila = mysqli_fetch_assoc($resultado)): ?>
                        <?php
                            // Calcular disponibilidad en bloques
                            $disponibilidad = calcularBloquesDisponibilidad($conexion, $fila['id'], $horarioDia);
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($fila['codigo']); ?>" class="user-image">
                            </td>
                            <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($fila['tipo_espacio']); ?></td>
                            <td><?php echo htmlspecialchars($fila['edificio_nombre']); ?></td>
                            <td><?php echo nl2br($disponibilidad); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
//Cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>


