<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_reserva = $_GET['id'] ?? $_POST['id_reserva'] ?? null;

if (!$id_reserva) {
    die("Reserva no válida.");
}

/* ===== PROCESAR FORMULARIO ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_reserva = $_POST['id_reserva'];

    /* ===== BOTÓN: NADIE ASISTIÓ ===== */
    if (isset($_POST['sin_asistencia'])) {

        mysqli_query($conexion,"
            DELETE FROM asistencia_reservas
            WHERE id_reservacion='$id_reserva'
        ");

        $queryEstudiantes = "
        SELECT id_estudiante
        FROM reservaciones_estudiantes
        WHERE id_reservacion='$id_reserva'
        ";
        $resEst = mysqli_query($conexion,$queryEstudiantes);

        while ($row = mysqli_fetch_assoc($resEst)) {
            $id_estudiante = $row['id_estudiante'];

            mysqli_query($conexion,"
                INSERT INTO asistencia_reservas
                (id_reservacion,id_estudiante,asistio)
                VALUES('$id_reserva','$id_estudiante',0)
            ");
        }

        mysqli_query($conexion,"
            UPDATE reservaciones
            SET estado='finalizado'
            WHERE id='$id_reserva'
            AND id_usuario='$id_usuario'
        ");

        header("Location: mis_reservas.php?msg=sin_asistencia");
        exit();
    }

    /* ===== GUARDAR ASISTENCIA NORMAL ===== */

    $asistentes = $_POST['asistencia'] ?? [];

    mysqli_query($conexion,"
        DELETE FROM asistencia_reservas
        WHERE id_reservacion='$id_reserva'
    ");

    $queryEstudiantes = "
    SELECT id_estudiante
    FROM reservaciones_estudiantes
    WHERE id_reservacion='$id_reserva'
    ";
    $resEst = mysqli_query($conexion,$queryEstudiantes);

    while ($row = mysqli_fetch_assoc($resEst)) {

        $id_estudiante = $row['id_estudiante'];

        $asistio = in_array($id_estudiante,$asistentes) ? 1 : 0;

        mysqli_query($conexion,"
            INSERT INTO asistencia_reservas
            (id_reservacion,id_estudiante,asistio)
            VALUES('$id_reserva','$id_estudiante','$asistio')
        ");
    }

    mysqli_query($conexion,"
        UPDATE reservaciones
        SET estado='finalizado'
        WHERE id='$id_reserva'
        AND id_usuario='$id_usuario'
    ");

    header("Location: mis_reservas.php?msg=asistencia_ok");
    exit();
}

/* ===== CONSULTAR RESERVA ===== */

$queryReserva = "
SELECT *
FROM reservaciones
WHERE id='$id_reserva'
AND id_usuario='$id_usuario'
";

$resultReserva = mysqli_query($conexion,$queryReserva);
$reserva = mysqli_fetch_assoc($resultReserva);

if (!$reserva) {
    die("Reserva no encontrada.");
}

/* Validar estado */
if ($reserva['estado'] !== 'asistencia') {
    die("La asistencia solo se puede tomar cuando la reserva está en estado asistencia.");
}

/* ===== ESTUDIANTES ===== */

$queryEstudiantes = "
SELECT e.id, e.nombre_completo
FROM reservaciones_estudiantes re
JOIN estudiantes e ON re.id_estudiante=e.id
WHERE re.id_reservacion='$id_reserva'
";

$resultEstudiantes = mysqli_query($conexion,$queryEstudiantes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tomar asistencia</title>
<link rel="stylesheet" href="../../assets/css/style_panel.css">
</head>

<body>

<div class="container-docente">
<main class="main-content-cuenta">

<h2 class="title-asis">Tomar asistencia</h2>

<form method="POST">

<input type="hidden" name="id_reserva" value="<?= $id_reserva ?>">

<table class="user-table-asis">
<thead>
<tr>
<th>Estudiante</th>
<th>Asistencia</th>
</tr>
</thead>

<tbody>
<?php while($est = mysqli_fetch_assoc($resultEstudiantes)): ?>
<tr>
<td><?= htmlspecialchars($est['nombre_completo']) ?></td>
<td>
<input type="checkbox" name="asistencia[]" value="<?= $est['id'] ?>">
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<br>
    <div class="btn-container-asistencia">
        <button type="submit" class="update-button-asistencia">
        Guardar asistencia
        </button>

        <button type="submit" class="btn-danger-asis"
                name="sin_asistencia"
                onclick="return confirm('¿Confirmas que ningún estudiante asistió?')"
                style="background:#dc3545;color:white;padding:10px;border:none;border-radius:5px;">
        Ningún estudiante asistió
        </button>
    </div>
</form>

</main>
</div>

</body>
</html>

