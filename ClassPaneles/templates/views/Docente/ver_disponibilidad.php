<?php
// Conexión a la base de datos
require_once '../../php/conexion_be.php';
include '../../php/docente_session.php';

$id_espacio = $_GET['id_espacio'] ?? null;

if (!$id_espacio) {
    echo "<script>alert('Espacio no especificado.'); window.history.back();</script>";
    exit;
}

// Consultar las reservas para el espacio seleccionado
$query = "
    SELECT fecha_inicio, fecha_final, tipo_reservacion, descripcion 
    FROM reservaciones  
    WHERE id_espacio = '$id_espacio'
    ORDER BY fecha_inicio
";

$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error al consultar las reservas: " . mysqli_error($conexion));
}
$reservas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservas[] = $row;
}

// Hora de inicio y fin de las reservas
$hora_inicio = "08:00:00";
$hora_fin = "22:00:00";

// Obtener la fecha actual y el último día del año
$fecha_actual = date('Y-m-d');
$ultimo_dia_del_ano = date('Y-12-31');

if (empty($reservas)) {
    // Si no hay reservas
    $primer_dia_reserva = strtotime($fecha_actual);
    $ultimo_dia_reserva = strtotime($fecha_actual);
} else {
    $primer_dia_reserva = strtotime(min(array_column($reservas, 'fecha_inicio')));
    $ultimo_dia_reserva = strtotime(max(array_column($reservas, 'fecha_final')));
}

$reservas_formateadas = array_map(function($reserva) {
    return [
        'inicio' => $reserva['fecha_inicio'],
        'fin' => $reserva['fecha_final']
    ];
}, $reservas);

usort($reservas_formateadas, function ($a, $b) {
    return strtotime($a['inicio']) - strtotime($b['inicio']);
});

$bloques_libres_por_semana = [];

// Iterar por cada día desde la fecha actual hasta el final del año
$fecha_iteracion = strtotime($fecha_actual); 

while ($fecha_iteracion <= strtotime($ultimo_dia_del_ano)) {
    $fecha_dia = date('Y-m-d', $fecha_iteracion);
    $hora_actual = "$fecha_dia $hora_inicio";

    $reservas_dia = array_filter($reservas_formateadas, function($reserva) use ($fecha_dia) {
        return date('Y-m-d', strtotime($reserva['inicio'])) === $fecha_dia;
    });

    $disponibles = [];

    foreach ($reservas_dia as $reserva) {
        if (strtotime($hora_actual) < strtotime($reserva['inicio'])) {
            $disponibles[] = [
                'inicio' => $hora_actual,
                'fin' => $reserva['inicio']
            ];
        }
        $hora_actual = max($hora_actual, $reserva['fin']);
    }

    if (strtotime($hora_actual) < strtotime("$fecha_dia $hora_fin")) {
        $disponibles[] = [
            'inicio' => $hora_actual,
            'fin' => "$fecha_dia $hora_fin"
        ];
    }

    $bloques_libres_por_semana[] = [
        'fecha' => $fecha_dia,
        'bloques_libres' => $disponibles
    ];
    $fecha_iteracion = strtotime('+1 day', $fecha_iteracion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disponibilidad del Espacio</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.js"></script>
</head>
<body>
    <main>
        <h1>Disponibilidad del Espacio <?php echo htmlspecialchars($id_espacio); ?></h1>

        <div id="calendar"></div>

        <a href="update_spaces_docente.php?id=<?php echo urlencode($id_espacio); ?>">Volver</a>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        slotMinTime: '08:00:00',
        slotMaxTime: '22:00:00',
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
        },
        events: [
            <?php 
                // Mostrar reservas ocupadas
                foreach ($reservas_formateadas as $reserva) {
                    echo "{
                        title: 'Reservado',
                        start: '{$reserva['inicio']}',
                        end: '{$reserva['fin']}',
                        color: '#ff4d4d'  // Color para las reservas ocupadas
                    },";
                }

                // Mostrar bloques libres
                foreach ($bloques_libres_por_semana as $semana) {
                    foreach ($semana['bloques_libres'] as $bloque) {
                        echo "{
                            title: 'Disponible',
                            start: '{$bloque['inicio']}',
                            end: '{$bloque['fin']}',
                            color: '#2b7e1f'  // Color para los bloques libres
                        },";
                    }
                }
            ?>
        ],
        eventOverlap: false,
        eventClick: function(info) {
            alert('Bloque: ' + info.event.title + '\nInicio: ' + info.event.start + '\nFin: ' + info.event.end);
        },
        eventTimeFormat: { 
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridDay,timeGridWeek,dayGridMonth'
        }
    });

    calendar.render();
});
</script>
</body>
</html>
