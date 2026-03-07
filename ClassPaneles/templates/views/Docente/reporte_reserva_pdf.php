<?php
include '../../php/conexion_be.php';
include '../../php/docente_session.php';

$id_reserva = $_GET['id'];
$id_usuario = $_SESSION['id_usuario'];

/* Datos reserva */
$query = "
SELECT r.*, e.codigo AS espacio
FROM reservaciones r
JOIN espacios_academicos e ON r.id_espacio = e.id
WHERE r.id='$id_reserva' AND r.id_usuario='$id_usuario'
";
$reserva = mysqli_fetch_assoc(mysqli_query($conexion,$query));

/* Asistencia */
$query_est = "
SELECT es.nombre_completo, re.asistio
FROM asistencia_reservas re
JOIN estudiantes es ON es.id=re.id_estudiante
WHERE re.id_reservacion='$id_reserva'
";
$resultEst = mysqli_query($conexion,$query_est);

/* TCPDF */
require '../../../templates/vendor/autoload.php';

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 10);

/* Logo y nombre app */
$logo = '../../../templates/assets/images/logo2.png';
$nombreAplicativo = "Unispace gestión de espacios";

date_default_timezone_set('America/Bogota');
$fechaReporte = date("d/m/Y h:i A");

$pdf->Image($logo, 10, 10, 30);
$pdf->SetY(12);
$pdf->SetFont('Helvetica','B',16);
$pdf->Cell(0,10,$nombreAplicativo,0,1,'C');

/* Título */
$pdf->Ln(10);
$pdf->SetFont('Helvetica','B',12);
$pdf->Cell(0,10,"Reporte de Reserva - $fechaReporte",0,1,'C');

/* Datos reserva */
$html = "
<b>Espacio:</b> {$reserva['espacio']}<br>
<b>Tipo:</b> {$reserva['tipo_reservacion']}<br>
<b>Fecha inicio:</b> {$reserva['fecha_inicio']}<br>
<b>Fecha final:</b> {$reserva['fecha_final']}<br>
<b>Estado:</b> {$reserva['estado']}<br>
<b>Descripción:</b> {$reserva['descripcion']}<br><br>

<h3>Asistencia</h3>

<table border='1' cellpadding='5'>
<tr style='background:#f2f2f2'>
<th>Estudiante</th>
<th>Asistió</th>
</tr>";

while($est=mysqli_fetch_assoc($resultEst)){
    $asistio = $est['asistio']==1 ? 'Sí' : 'No';
    $html .= "
    <tr>
        <td>{$est['nombre_completo']}</td>
        <td>$asistio</td>
    </tr>";
}

$html .= "</table>";

$pdf->writeHTML($html);

/* Descargar PDF */
$pdf->Output("reporte_reserva.pdf","D");
