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

// Obtener las reservas
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

// Obtener la primera y última fecha de las reservas para calcular el rango completo
if (empty($reservas)) {
    // Si no hay reservas, asignamos un valor predeterminado para el rango
    $primer_dia_reserva = strtotime($fecha_actual);  // Fecha actual
    $ultimo_dia_reserva = strtotime($fecha_actual);  // Fecha actual
} else {
    // Si hay reservas, usamos min y max para obtener las fechas
    $primer_dia_reserva = strtotime(min(array_column($reservas, 'fecha_inicio')));
    $ultimo_dia_reserva = strtotime(max(array_column($reservas, 'fecha_final')));
}
// Calcular el inicio y el fin de la semana más temprana y más tardía en las reservas
$inicio_global = strtotime('mon', $primer_dia_reserva);
$fin_global = strtotime('next Saturday', $ultimo_dia_reserva);

// Formatear las reservas
$reservas_formateadas = array_map(function($reserva) {
    return [
        'inicio' => $reserva['fecha_inicio'],
        'fin' => $reserva['fecha_final']
    ];
}, $reservas);

// Ordenar reservas por hora de inicio
usort($reservas_formateadas, function ($a, $b) {
    return strtotime($a['inicio']) - strtotime($b['inicio']);
});

// Bloques libres por semana
$bloques_libres_por_semana = [];

// Iterar por cada semana en el rango global
for ($inicio_semana = strtotime($fecha_actual); $inicio_semana <= strtotime($ultimo_dia_del_ano); $inicio_semana = strtotime('+1 week', $inicio_semana)) {
    $fin_semana = strtotime('next Saturday', $inicio_semana);

    // Bloques libres para la semana actual
    $disponibles = [];

    // Iterar por cada día dentro de la semana
    for ($dia = $inicio_semana; $dia <= $fin_semana; $dia = strtotime('+1 day', $dia)) {
        $fecha_dia = date('Y-m-d', $dia);
        $hora_actual = "$fecha_dia $hora_inicio";

        // Filtrar reservas del día actual
        $reservas_dia = array_filter($reservas_formateadas, function($reserva) use ($fecha_dia) {
            return date('Y-m-d', strtotime($reserva['inicio'])) === $fecha_dia;
        });

        // Procesar las reservas del día
        foreach ($reservas_dia as $reserva) {
            if (strtotime($hora_actual) < strtotime($reserva['inicio'])) {
                // Agregar bloque disponible
                $disponibles[] = [
                    'inicio' => $hora_actual,
                    'fin' => $reserva['inicio']
                ];
            }
            // Actualizar la hora actual para verificar el siguiente bloque
            $hora_actual = max($hora_actual, $reserva['fin']);
        }

        // Verificar si hay un bloque libre al final del día
        if (strtotime($hora_actual) < strtotime("$fecha_dia $hora_fin")) {
            $disponibles[] = [
                'inicio' => $hora_actual,
                'fin' => "$fecha_dia $hora_fin"
            ];
        }
    }

    // Guardar los bloques libres de la semana actual
    $bloques_libres_por_semana[] = [
        'semana_inicio' => date('Y-m-d', $inicio_semana),
        'semana_fin' => date('Y-m-d', $fin_semana),
        'bloques_libres' => $disponibles
    ];
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

        <a href="update_spaces_docente.php?id_espacio=<?php echo urlencode($id_espacio); ?>">Volver a Reservar</a>
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
