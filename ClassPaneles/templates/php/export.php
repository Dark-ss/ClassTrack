<?php
include 'admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); 
    exit();
}
include 'conexion_be.php';

if (isset($_POST['export']) && isset($_POST['format'])) {
    $format = $_POST['format'];
    
    $query = "SELECT r.*, us.nombre_completo, ea.codigo, e.codigo AS edificio_codigo
            FROM reservaciones r
            LEFT JOIN usuarios us ON r.id_usuario = us.id 
            LEFT JOIN espacios_academicos ea ON r.id_espacio = ea.id
            LEFT JOIN edificios e ON ea.edificio_id = e.id";

    $result = mysqli_query($conexion, $query);

    $consultaEstadisticas = "SELECT 
        COUNT(*) AS total_estudiantes, 
        AVG(total_por_reserva) AS promedio_por_reserva, 
        MAX(total_por_reserva) AS max_estudiantes
        FROM (
        SELECT id_reservacion, COUNT(id_estudiante) AS total_por_reserva
        FROM reservaciones_estudiantes
        GROUP BY id_reservacion
    ) AS subquery";

    $resultEstadisticas = mysqli_query($conexion, $consultaEstadisticas);
    $estadisticas = mysqli_fetch_assoc($resultEstadisticas);


    if ($format === 'excel') {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=reservas.xls");
    
        // Consulta para obtener las reservas y contar el número de estudiantes por reserva
        $queryReservations = "
            SELECT 
                r.id,
                us.nombre_completo, 
                r.fecha_inicio, 
                r.fecha_final,
                r.tipo_reservacion,
                r.descripcion,
                ea.codigo,
                e.codigo AS edificio_codigo,
                r.estado,
                COUNT(re.id_estudiante) AS total_estudiantes
            FROM reservaciones r
            LEFT JOIN reservaciones_estudiantes re ON r.id = re.id_reservacion
            LEFT JOIN usuarios us ON r.id_usuario = us.id
            LEFT JOIN espacios_academicos ea ON r.id_espacio = ea.id
            LEFT JOIN edificios e ON ea.edificio_id = e.id
            GROUP BY r.id, us.nombre_completo";
        $resultReservations = mysqli_query($conexion, $queryReservations);
        if (!$resultReservations) {
            die("Error en la consulta de Reservas: " . mysqli_error($conexion));
        }
    
        // Consulta para obtener el estado de las reservaciones
        $queryEstado = "
            SELECT 
                estado, 
                COUNT(*) AS total
            FROM reservaciones
            GROUP BY estado";
        $resultEstado = mysqli_query($conexion, $queryEstado);
        if (!$resultEstado) {
            die("Error en la consulta de Estado: " . mysqli_error($conexion));
        }
    
        echo "<html xmlns:o='urn:schemas-microsoft-com:office:office'
            xmlns:x='urn:schemas-microsoft-com:office:excel'
            xmlns='http://www.w3.org/TR/REC-html40'>
                <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                    <style>
                        " . file_get_contents("../assets/css/styles.php") . "
                        .header { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <table border='0' cellspacing='0' cellpadding='0' width='100%'>
                        <tr>
                            <!-- Columna para la tabla de Reservas -->
                            <td width='70%' valign='top'>
                                <table class='table' border='1' cellpadding='5' cellspacing='0' width='100%'>
                                    <tr>
                                        <th class='header'>ID</th>
                                        <th class='header'>Usuario</th>
                                        <th class='header'>Fecha Inicio</th>
                                        <th class='header'>Fecha Fin</th>
                                        <th class='header'>Tipo Reservación</th>
                                        <th class='header'>Descripción</th>
                                        <th class='header'>Espacio</th>
                                        <th class='header'>Edificio</th>
                                        <th class='header'>Estado</th>
                                        <th class='header'># Estudiantes</th>
                                    </tr>";
    
        while ($row = mysqli_fetch_assoc($resultReservations)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nombre_completo']}</td>
                    <td>{$row['fecha_inicio']}</td>
                    <td>{$row['fecha_final']}</td>
                    <td>{$row['tipo_reservacion']}</td>
                    <td style='word-wrap: break-word; white-space: normal; max-width: 200px;'>" . nl2br($row['descripcion']) . "</td>
                    <td>{$row['codigo']}</td>
                    <td>{$row['edificio_codigo']}</td>
                    <td>{$row['estado']}</td>
                    <td align='center'>{$row['total_estudiantes']}</td>
                </tr>";
        }
    
        echo "</table></td>";
    
        // Columna separadora
        echo "<td width='20'>&nbsp;</td>";
    
        // Columna para la tabla de Estado
        echo "<td valign='top'>
                <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>
                    <tr style='background-color: #ff8000; color: white; font-weight: bold;'>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>";
    
        while ($row = mysqli_fetch_assoc($resultEstado)) {
            echo "
                <tr>
                    <td>{$row['estado']}</td>
                    <td align='center'>{$row['total']}</td>
                </tr>";
        }
    
        echo "</table></td>
            </tr>
        </table>";
        echo "<div id='Sheet2' class='students-table'>
                <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>
                    <tr style='background-color: #007bff; color: white; font-weight: bold;'>
                        <th>Reservación ID</th>
                        <th>Nombre del Estudiante</th>
                    </tr>";
    
        // Consultar los estudiantes
        $queryMapping = "
            SELECT 
                re.id_reservacion,
                e.nombre_completo
            FROM reservaciones_estudiantes re
            LEFT JOIN estudiantes e ON re.id_estudiante = e.id
            ORDER BY re.id_reservacion";
        $resultMapping = mysqli_query($conexion, $queryMapping);
        if (!$resultMapping) {
            die("Error en la consulta de mapeo: " . mysqli_error($conexion));
        }
    
        while ($row = mysqli_fetch_assoc($resultMapping)) {
            echo "<tr>
                    <td>{$row['id_reservacion']}</td>
                    <td>{$row['nombre_completo']}</td>
                </tr>";
        }
    
        echo "</table></div>";
        echo "</body></html>";
    } elseif ($format === 'pdf') {
        require '../../templates/vendor/autoload.php';
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 10);
        $logo = '../../templates/assets/images/logo2.png';
        //$nombreAplicativo = "UniSpace";
        date_default_timezone_set('America/Bogota');
        $fechaReporte = date("d/m/Y h:i A");
    
        $pdf->Image($logo, 10, 10, 40);
        $pdf->SetXY(40, 15);
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->Cell(0, 10, $nombreAplicativo, 0, 1, 'L');
    
        $pdf->Ln(10);
    
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Reporte de Reservas - Generado el: $fechaReporte", 0, 1, 'C');
        
        $pdf->Ln(5);
    
        function truncateDescription($text, $wordLimit = 20) {
            $words = explode(' ', $text);
            if (count($words) > $wordLimit) {
                $words = array_slice($words, 0, $wordLimit);
                $text = implode(' ', $words) . '...';
            }
            return $text;
        }
    
        $totalReservas = 0;
        $totalAprobadas = 0;
        $totalPendientes = 0;
        $totalRechazadas = 0;
        
        //HTML de la tabla
        $html = '
        <table border="1" cellpadding="5">
            <tr style="background-color: #28a745; color: white; text-align: center; font-weight: bold;">
                <th style="width: 6%;">ID</th>
                <th>Usuario</th>
                <th style="width: 14%;">Fecha Inicio</th>
                <th style="width: 14%;">Fecha Fin</th>
                <th style="width: 11%;">Tipo</th>
                <th style="width: 15%;">Descripción</th>
                <th style="width: 10.5%;">Espacio</th>
                <th style="width: 10%;">Edificio</th>
                <th style="width: 13%;">Estado</th>
            </tr>';
        $counter = 0;
        $rows = [];
    
        // Guardamos todas las filas en un arreglo
        while ($row = mysqli_fetch_assoc($result)) {
            $descripcion_truncada = truncateDescription($row['descripcion']);
            $rows[] = "
            <tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre_completo']}</td>
                <td>{$row['fecha_inicio']}</td>
                <td>{$row['fecha_final']}</td>
                <td>{$row['tipo_reservacion']}</td>
                <td>" . mb_substr($row['descripcion'], 0, 40, 'UTF-8') . "...</td>
                <td>{$row['codigo']}</td>
                <td>{$row['edificio_codigo']}</td>
                <td>{$row['estado']}</td>
            </tr>";
            $totalReservas++;
            if ($row['estado'] === 'aceptada') {
                $totalAprobadas++;
            } elseif ($row['estado'] === 'pendiente') {
                $totalPendientes++;
            } elseif ($row['estado'] === 'rechazada') {
                $totalRechazadas++;
            }
    
            $counter++;
        }
        
        $html .= implode('', array_slice($rows, 0, 12));
        $html .= '</table>';
        
        $pdf->writeHTML($html);
        
        $remainingHeight = $pdf->getPageHeight() - $pdf->GetY();
        //if ($remainingHeight < 50 && count($rows) > 12) { 
        if (count($rows)> 12) { 
            $remainingHeight = $pdf->getPageHeight() - $pdf->GetY();

            if ($remainingHeight < 60) {
                $pdf->AddPage();
            }
        
            $html = '<table border="1" cellpadding="5">';
            $html .= '<tr style="background-color: #28a745; color: white; text-align: center; font-weight: bold;">
                    <th style="width: 6%;">ID</th>
                    <th>Usuario</th>
                    <th style="width: 14%;">Fecha Inicio</th>
                    <th style="width: 14%;">Fecha Fin</th>
                    <th style="width: 11%;">Tipo</th>
                    <th style="width: 15%;">Descripción</th>
                    <th style="width: 10.5%;">Espacio</th>
                    <th style="width: 10%;">Edificio</th>
                    <th style="width: 13%;">Estado</th>
                </tr>';
        }
        
        if (count($rows) > 12) {
            $html .= implode('', array_slice($rows, 12));
            $html .= '</table>';
            $pdf->writeHTML($html);
        }
        
        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 10, "Resumen de Reservas", 0, 1, 'C');
    
        // Resumen de Reservaciones y Estadísticas
        $xInicio = $pdf->GetX();
        $yInicio = $pdf->GetY();
    
        $htmlTotales = '
        <table border="1" cellpadding="5">
            <tr style="background-color: #28a745; color: white; font-weight: bold;">
                <th>Concepto</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>Total de reservas</td>
                <td>' . $totalReservas . '</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td>Aceptadas</td>
                <td>' . $totalAprobadas . '</td>
            </tr>
            <tr>
                <td>Pendientes</td>
                <td>' . $totalPendientes . '</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td>Rechazadas</td>
                <td>' . $totalRechazadas . '</td>
            </tr>
        </table>';
    
        $pdf->writeHTMLCell(90, '', $xInicio, $yInicio, $htmlTotales, 0, 0, false, true, 'L');
    
        // Establecer estadísticas
        $htmlEstadisticas = '
        <table border="1" cellpadding="5">
            <tr style="background-color: #007bff; color: white; font-weight: bold;">
                <th>Concepto</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Total de estudiantes registrados</td>
                <td>' . $estadisticas['total_estudiantes'] . '</td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td>Promedio de estudiantes por reserva</td>
                <td>' . round($estadisticas['promedio_por_reserva'], 2) . '</td>
            </tr>
            <tr>
                <td>Máximo de estudiantes en una reserva</td>
                <td>' . $estadisticas['max_estudiantes'] . '</td>
            </tr>
        </table>';

        $pdf->writeHTMLCell(90, '', $xInicio + 100, $yInicio, $htmlEstadisticas, 0, 1, false, true, 'R');
        if (ob_get_length()) {
            ob_end_clean();
        }
        $pdf->Output('reservas.pdf', 'D');
    } elseif ($format === 'png') {
        require '../../templates/vendor/autoload.php';

        $width = 1200;
        $height = 40 * (mysqli_num_rows($result) + 2);
        $image = imagecreatetruecolor($width, $height);
        $font = __DIR__ . '/fonts/arial.ttf';
        $bg = imagecolorallocate($image, 255, 255, 255); 
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $border_color = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        $col_widths = [50, 200, 180, 180, 130, 250, 100, 120]; 

        //imagestring($image, 5, 10, 10, utf8_decode("Reporte generado por: ") . utf8_decode($nombre_completo), $colorTexto);
    
        $headers = ["ID", "Usuario", "Fecha Inicio", "Fecha Fin", "Tipo", utf8_decode("Descripción"), "Espacio", "Estado"];
        $x = 10;
        $y = 10;
        $row_height = 30;
    
        for ($i = 0; $i < count($headers); $i++) {
            $col_width = $col_widths[$i];
            imagefilledrectangle($image, $x, $y, $x + $col_width, $y + $row_height, imagecolorallocate($image, 200, 200, 200)); // Fondo gris para el encabezado
            imagestring($image, 5, $x + 5, $y + 8, $headers[$i], $text_color);
            imagerectangle($image, $x, $y, $x + $col_width, $y + $row_height, $border_color);
            $x += $col_width;
        }
    
        $y += $row_height; // Avanzamos a la siguiente fila
    
        // Dibujar los datos de la tabla
        while ($row = mysqli_fetch_assoc($result)) {
            $x = 10;
            $datos = [
                utf8_decode($row['id']),
                utf8_decode($row['nombre_completo']),
                utf8_decode($row['fecha_inicio']),
                utf8_decode($row['fecha_final']),
                utf8_decode($row['tipo_reservacion']),
                utf8_decode(mb_substr($row['descripcion'], 0, 40, 'UTF-8') . "..."),
                utf8_decode($row['codigo']),
                utf8_decode($row['estado'])
            ];

            for ($i = 0; $i < count($datos); $i++) {
                $col_width = $col_widths[$i];
                imagefilledrectangle($image, $x, $y, $x + $col_width, $y + $row_height, $bg);
                imagestring($image, 4, $x + 5, $y + 8, $datos[$i], $text_color);
                imagerectangle($image, $x, $y, $x + $col_width, $y + $row_height, $border_color);
                $x += $col_width;
            }
            $y += $row_height;
        }
    
        // Enviar imagen como PNG
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
    }    
    exit();
}