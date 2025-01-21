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

// Función para calcular bloques de disponibilidad
function calcularBloquesDisponibilidad($conexion, $idEspacio, $horasDia) {
    // Consultar reservas para el espacio en el día actual
    $hoy = date('Y-m-d');
    $queryReservas = "
        SELECT fecha_inicio, fecha_final
        FROM reservaciones
        WHERE id_espacio = $idEspacio AND DATE(fecha_inicio) = '$hoy'
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

    return implode(', ', $disponibilidad);
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
    <title>Espacios Académicos</title>
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
                </div>
            </div>
        </div>
        <form method="GET" action="table_disponibilidad.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar espacio..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <h1 class="title-table">Espacios Disponibilidad</h1>
        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Código</th>
                    <th>Tipo de Espacio</th>
                    <th>Edificio</th>
                    <th>Disponibilidad</th>
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
                    ['inicio' => '21:00', 'fin' => '22:00']
                ];

                while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <?php
                        // Calcular disponibilidad en bloques
                        $disponibilidad = calcularBloquesDisponibilidad($conexion, $fila['id'], $horarioDia);
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($fila['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($fila['codigo']); ?>" style="width: 100px; height: auto;">
                        </td>
                        <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo_espacio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['edificio_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($disponibilidad); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
//Cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>


