<?php
include 'docente_session.php';
include 'conexion_be.php';

if (!isset($_POST['mensaje'])) {
    exit;
}

$mensajeOriginal = trim($_POST['mensaje']);
$mensaje = strtolower($mensajeOriginal);

/* ===============================
   1. SALUDO INICIAL
   =============================== */

$saludos = ['hola', 'buenas', 'buen día', 'buenos días', 'buenas tardes', 'buenas noches'];

foreach ($saludos as $saludo) {
    if ($mensaje === $saludo) {
        echo "👋 Hola, soy tu asistente virtual. 
        Puedo ayudarte con:
        • Consultar reservas de aulas
        • Reservas por año o fecha
        • Cómo reservar un aula";
        exit;
    }
}

/* ===============================
   2. DETECTAR INTENCIÓN
   =============================== */

/* Palabras para CONSULTAR datos */
$palabrasConsulta = [
    'cuantas', 'cuántas', 'cantidad', 'total',
    'hubo', 'hay', 'uso', 'usos'
];

/* Palabras para ACCIONES generales */
$palabrasAccion = [
    'reservar', 'reserva', 'cómo reservar', 'como reservar'
];

$esConsulta = false;
foreach ($palabrasConsulta as $palabra) {
    if (str_contains($mensaje, $palabra)) {
        $esConsulta = true;
        break;
    }
}

/* ===============================
   3. CONSULTA DE RESERVAS
   =============================== */

if ($esConsulta) {

    /* Extraer aula */
    if (!preg_match('/([a-zA-Z]+[- ]?\d+)/', $mensajeOriginal, $matches)) {
        echo "📍 Indica el código del aula. Ejemplo: ING-101.";
        exit;
    }

    $codigoAula = strtoupper(str_replace(' ', '-', $matches[1]));

    /* Extraer año */
    preg_match('/\b(20\d{2})\b/', $mensaje, $matchYear);
    $anio = $matchYear[1] ?? null;

    /* Extraer fecha */
    preg_match('/(\d{2}\/\d{2}\/\d{4})/', $mensaje, $matchFecha1);
    preg_match('/(\d{4}-\d{2}-\d{2})/', $mensaje, $matchFecha2);

    $fecha = null;
    if (!empty($matchFecha1)) {
        $fecha = DateTime::createFromFormat('d/m/Y', $matchFecha1[1])->format('Y-m-d');
    } elseif (!empty($matchFecha2)) {
        $fecha = $matchFecha2[1];
    }

    /* SQL */
    $sql = "
        SELECT COUNT(r.id) AS total
        FROM reservaciones r
        JOIN espacios_academicos a ON r.id_espacio = a.id
        JOIN edificios e ON a.edificio_id = e.id
        WHERE CONCAT(e.codigo, '-', a.codigo) = ?
    ";

    $params = [$codigoAula];
    $types  = "s";

    if ($anio) {
        $sql .= " AND YEAR(r.fecha_inicio) = ?";
        $params[] = $anio;
        $types .= "i";
    }

    if ($fecha) {
        $sql .= " AND DATE(r.fecha_inicio) = ?";
        $params[] = $fecha;
        $types .= "s";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    /* Respuesta natural */
    if ($fecha) {
        echo "📅 El aula $codigoAula tuvo {$row['total']} reservas el $fecha.";
    } elseif ($anio) {
        echo "📊 El aula $codigoAula tuvo {$row['total']} reservas en el año $anio.";
    } else {
        echo "📌 El aula $codigoAula tiene {$row['total']} reservas registradas.";
    }

    exit;
}

$palabrasHistorial = [
    'mi historial',
    'mis reservas',
    'historial',
    'reservas que hice',
    'cuantas reservas he hecho'
];

$esHistorial = false;
foreach ($palabrasHistorial as $palabra) {
    if (str_contains($mensaje, $palabra)) {
        $esHistorial = true;
        break;
    }
}

/* ===============================
   4. FAQ / RESPUESTAS PREDEFINIDAS
   =============================== */

$sqlFaq = "
    SELECT respuesta
    FROM chatbot_respuestas
    WHERE ? LIKE CONCAT('%', palabra_clave, '%')
    ORDER BY LENGTH(palabra_clave) DESC
    LIMIT 1
";

$stmtFaq = $conexion->prepare($sqlFaq);
$stmtFaq->bind_param("s", $mensaje);
$stmtFaq->execute();
$resultFaq = $stmtFaq->get_result();

if ($rowFaq = $resultFaq->fetch_assoc()) {
    echo $rowFaq['respuesta'];
    exit;
}

/* ===============================
   4. HISTORIAL POR USUARIO
   =============================== */


if ($esHistorial) {

    if (!isset($_SESSION['id_usuario'])) {
        echo "⚠️ Debes iniciar sesión para ver tu historial.";
        exit;
    }

    $idUsuario = $_SESSION['id_usuario'];

    /* Extraer año opcional */
    preg_match('/\b(20\d{2})\b/', $mensaje, $matchYear);
    $anio = $matchYear[1] ?? null;

    /* Extraer aula opcional */
    preg_match('/([a-zA-Z]+[- ]?\d+)/', $mensaje, $matchAula);
    $codigoAula = isset($matchAula[1])
        ? strtoupper(str_replace(' ', '-', $matchAula[1]))
        : null;

    /* SQL base */
    $sql = "
        SELECT 
            CONCAT(e.codigo, '-', a.codigo) AS aula,
            DATE(r.fecha_inicio) AS fecha,
            COUNT(r.id) AS total
        FROM reservaciones r
        JOIN espacios_academicos a ON r.id_espacio = a.id
        JOIN edificios e ON a.edificio_id = e.id
        WHERE r.id_usuario = ?
    ";

    $params = [$idUsuario];
    $types  = "i";

    if ($anio) {
        $sql .= " AND YEAR(r.fecha_inicio) = ?";
        $params[] = $anio;
        $types .= "i";
    }

    if ($codigoAula) {
        $sql .= " AND CONCAT(e.codigo, '-', a.codigo) = ?";
        $params[] = $codigoAula;
        $types .= "s";
    }

    $sql .= "
        GROUP BY aula, fecha
        ORDER BY fecha DESC
        LIMIT 5
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "📭 No tienes reservas registradas con esos filtros.";
        exit;
    }

    echo "📘 <b>Tu historial reciente:</b><br>";

    while ($row = $result->fetch_assoc()) {
        echo "• {$row['fecha']} — Aula {$row['aula']} ({$row['total']} reserva(s))<br>";
    }

    exit;
}


/* ===============================
   5. RESPUESTA POR DEFECTO
   =============================== */

echo "🤖 Puedo ayudarte con:
• Cuántas reservas hay en un aula
• Reservas por año o fecha
• Cómo reservar un aula";