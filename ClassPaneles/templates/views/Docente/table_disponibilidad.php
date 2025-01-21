<?php
include '../../php/docente_session.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../templates/index.php"); 
    exit();
}
include '../../php/conexion_be.php';

//paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

//Calcular paginas 
$query_reserva = "SELECT COUNT(*) as total FROM reservaciones";
$resultado_reserva = mysqli_query($conexion, $query_reserva);
$total_reserva = mysqli_fetch_assoc($resultado_reserva)['total'];
$total_paginas = ceil($total_reserva / $registros_por_pagina);

$query_reservas = "SELECT * FROM reservaciones ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query_reservas);

//busqueda de espacios
$search_reserva = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta con JOIN Tabla edificios y espacios_academicos
$query = "
    SELECT r.fecha_inicio, r.fecha_final, r.tipo_reservacion, ea.codigo AS codigo_espacio 
    FROM reservaciones r
    JOIN espacios_academicos ea ON r.id_espacio = ea.id
    WHERE r.fecha_inicio LIKE '%$search_reserva%' OR r.fecha_final LIKE '%$search_reserva%' OR r.tipo_reservacion LIKE '%$search_reserva%' OR ea.codigo LIKE '%$search_reserva%'
    ORDER BY r.fecha_inicio DESC
    LIMIT $offset, $registros_por_pagina
";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id != $_SESSION['id']){ 
        $query_delete = "DELETE FROM reservaciones WHERE id = '$id'";
        mysqli_query($conexion, $query_delete);
        header("Location: table_spaces.php");
        exit();
    } else {
        echo "<script>alert('No puedes eliminarlo.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Lista Reservaciones</title>
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
                    <a href="table_disponibilidad.php">Reservaciones</a>
                </div>
            </div>
        </div>
        <form method="GET" action="table_disponibilidad.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar espacio..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <h1 class="title-table">Lista de reservaciones</h1>
        <table>
            <thead>
                <tr>
                    <th>Fecha inicio</th>
                    <th>Fecha final</th>
                    <th>Tipo</th>
                    <th>Espacio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['fecha_inicio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_final']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo_reservacion']); ?></td>
                        <td><?php echo htmlspecialchars($fila['codigo_espacio']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                    
        <!--Paginación-->
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>"
                    class="<?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search_reserva); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </main>
    <script src="../../assets/js/script.js"></script>
    <script src="../../assets/js/script_menu.js"></script>
</body>

</html>
<?php
// Liberar resultados y cerrar conexión
mysqli_free_result($resultado);
mysqli_close($conexion);
?>