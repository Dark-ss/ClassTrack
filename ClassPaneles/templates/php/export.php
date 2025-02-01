<?php
require_once '../../templates/vendor/autoload.php';
include 'admin_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../templates/index.php"); 
    exit();
}
include 'conexion_be.php';

if (isset($_POST['export']) && isset($_POST['format'])) {
    $format = $_POST['format'];
    
    $query = "SELECT r.id, usuarios.nombre_completo, r.fecha_inicio, 
            r.fecha_final, r.tipo_reservacion, 
            r.descripcion, espacios_academicos.codigo, r.estado
            FROM reservaciones r
            LEFT JOIN usuarios ON r.id_usuario = usuarios.id 
            LEFT JOIN espacios_academicos ON r.id_espacio = espacios_academicos.id";
    
    $result = mysqli_query($conexion, $query);
    
    if ($format === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=reservas.xls");
        
        echo "ID\tUsuario\tFecha Inicio\tFecha Fin\tTipo Reservación\tDescripción\tEspacio\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "{$row['id']}\t{$row['nombre_completo']}\t{$row['fecha_inicio']}\t{$row['fecha_final']}\t{$row['tipo_reservacion']}\t{$row['descripcion']}\t{$row['codigo']}\n";
        }
    } elseif ($format === 'pdf') {
        require '../../templates/vendor/autoload.php';
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 10);
    
        $logo = '../../templates/assets/images/logo2.png';
        $nombreAplicativo = "UniSpace";
        date_default_timezone_set('America/Bogota');
        $fechaReporte = date("d/m/Y h:i A");
    
        $pdf->Image($logo, 10, 10, 25);
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
                // Recorta las palabras al límite y agrega "..."
                $words = array_slice($words, 0, $wordLimit);
                $text = implode(' ', $words) . '...';
            }
            return $text;
        }
    
        $totalReservas = 0;
        $totalAprobadas = 0;
        $totalPendientes = 0;
        $totalRechazadas = 0;
        
        $html = '
        <table border="1" cellpadding="5">
            <tr style="background-color: #28a745; color: white; text-align: center; font-weight: bold;">
                <th>ID</th>
                <th>Usuario</th>
                <th style="width: 14%;">Fecha Inicio</th>
                <th style="width: 14%;">Fecha Fin</th>
                <th style="width: 11%;">Tipo</th>
                <th style="width: 15%;">Descripción</th>
                <th style="width: 8%;">Espacio</th>
                <th style="width: 15%;">Estado</th>
            </tr>';
    
        $counter = 0;  // Contador para los registros
        $rows = [];  // Almacenar todos los registros en un array para su manejo
    
        while ($row = mysqli_fetch_assoc($result)) {
            $descripcion_truncada = truncateDescription($row['descripcion']);
            $rows[] = "
            <tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre_completo']}</td>
                <td>{$row['fecha_inicio']}</td>
                <td>{$row['fecha_final']}</td>
                <td>{$row['tipo_reservacion']}</td>
                <td>{$descripcion_truncada}</td>
                <td>{$row['codigo']}</td>
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
    
            $counter++;  // Incrementar el contador
        }
    
        // Imprimir los primeros 12 registros
        $html .= implode('', array_slice($rows, 0, 12));  // Añadir los primeros 12
        $html .= '</table>';
        $pdf->writeHTML($html);
    
        // Si hay más de 12 registros, añadimos una nueva página para el resto
        if ($totalReservas > 12) {
            $pdf->AddPage();
            $html = '<table border="1" cellpadding="5">';
            $html .= '
            <tr style="background-color: #28a745; color: white; text-align: center; font-weight: bold;">
                <th>ID</th>
                <th>Usuario</th>
                <th style="width: 14%;">Fecha Inicio</th>
                <th style="width: 14%;">Fecha Fin</th>
                <th style="width: 11%;">Tipo</th>
                <th style="width: 15%;">Descripción</th>
                <th style="width: 8%;">Espacio</th>
                <th style="width: 15%;">Estado</th>
            </tr>';
            $html .= implode('', array_slice($rows, 12));  // Añadir el resto de los registros
            $html .= '</table>';
            $pdf->writeHTML($html);
        }
    
        // Imprimir los totales
        $html = '
        <h3 style="margin-top: 20px;">Totales</h3>
        <table border="1" cellpadding="5" style="width: 50%; margin: 0 auto; text-align: center;">
        <tr style="background-color: #28a745; color: white; font-weight: bold;">
            <th>Concepto</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>Total de reservas</td>
            <td>' . $totalReservas . '</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
            <td>Aprobadas</td>
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
    
        $pdf->writeHTML($html);
        $pdf->Output('reservas.pdf', 'D');
    } elseif ($format === 'png') {
        require '../../templates/vendor/autoload.php';
        $image = imagecreatetruecolor(800, 600);
        $bg = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 800, 600, $bg);
        
        $y = 20;
        imagestring($image, 5, 10, $y, "ID  Usuario  Fecha Inicio  Fecha Fin  Tipo  Descripción  Espacio", $text_color);
        $y += 20;
        while ($row = mysqli_fetch_assoc($result)) {
            imagestring($image, 3, 10, $y, "{$row['id']} {$row['nombre_completo']} {$row['fecha_inicio']} {$row['fecha_final']} {$row['tipo_reservacion']} {$row['descripcion']} {$row['codigo']}", $text_color);
            $y += 20;
        }
        
        header("Content-Type: image/png");
        imagepng($image);
        imagedestroy($image);
    }
    exit();
}