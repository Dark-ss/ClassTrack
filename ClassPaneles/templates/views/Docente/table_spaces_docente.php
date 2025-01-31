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
$query_total = "SELECT COUNT(*) as total FROM espacios_academicos";
$resultado_total = mysqli_query($conexion, $query_total);
$total_usuarios = mysqli_fetch_assoc($resultado_total)['total'];
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

$query = "SELECT * FROM espacios_academicos ORDER BY id DESC";
$resultado = mysqli_query($conexion, $query);

//busqueda de espacios
$search = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta con JOIN Tabla edificios y espacios_academicos
$query = "
    SELECT ea.id, ea.imagen, ea.codigo, ea.capacidad, ea.tipo_espacio, e.nombre AS edificio_nombre 
    FROM espacios_academicos ea
    JOIN edificios e ON ea.edificio_id = e.id
    WHERE ea.id LIKE '%$search%' OR ea.codigo LIKE '%$search%' OR ea.tipo_espacio LIKE '%$search%' OR e.nombre LIKE '%$search%'
    ORDER BY ea.id DESC
    LIMIT $offset, $registros_por_pagina
";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error al obtener los datos: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Lista Espacios</title>
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
        <form method="GET" action="table_spaces_docente.php" class="search-form">
            <input type="text" name="buscar" placeholder="Buscar espacio..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
        <h1 class="title-table">Lista de Espacios</h1>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Imagen</th>
                    <th>Codigo</th>
                    <th>Capacidad</th>
                    <th>Tipo</th>
                    <th>Edificio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id']); ?></td>
                        <td><img src="<?php echo $fila['imagen'] ? $fila['imagen'] : '../../uploads/usuario.png'; ?>" alt="Imagen de Espacio" width="50"></td>
                        <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['capacidad']); ?></td>
                        <td><?php echo htmlspecialchars($fila['tipo_espacio']); ?></td>
                        <td><?php echo htmlspecialchars($fila['edificio_nombre']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                    
        <!--Paginación-->
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo htmlspecialchars($search); ?>"
                    class="<?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&buscar=<?php echo htmlspecialchars($search); ?>">Siguiente</a>
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