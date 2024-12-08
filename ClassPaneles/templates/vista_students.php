<?php
require_once 'php/conexion_be.php';
include 'php/admin_session.php'; // Verifica que el admin esté autenticado

$query = "SELECT * FROM estudiantes ORDER BY fecha_registro DESC";
$resultado = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estudiantes</title>
    <link rel="stylesheet" href="assets/css/style_paneles.css">
</head>

<body>
    <h1>Lista de Estudiantes</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Identificación</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['id']); ?></td>
                    <td>
                        <img src="<?php echo $fila['imagen'] ? $fila['imagen'] : 'uploads/usuario.png'; ?>" alt="Imagen de Estudiante" width="50">
                    </td>
                    <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                    <td><?php echo htmlspecialchars($fila['identificacion']); ?></td>
                    <td><?php echo htmlspecialchars($fila['fecha_registro']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>