<?php
require_once '../../php/conexion_be.php';
include '../../php/docente_session.php';

$isAdmin = false;

// Validar ID
if (!isset($_GET['id'])) {
    echo "<script>alert('No se especificó un edificio válido.'); window.location.href='vista_buildings.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar edificio
$query = "SELECT * FROM edificios WHERE id='$id'";
$result = mysqli_query($conexion, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Edificio no encontrado.'); window.location.href='vista_buildings.php';</script>";
    exit;
}

$usuario = mysqli_fetch_assoc($result);

// Consultar espacios
$qEspacios = "SELECT id FROM espacios_academicos WHERE edificio_id='$id'";
$rEspacios = mysqli_query($conexion, $qEspacios);
$espacios_count = mysqli_num_rows($rEspacios);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Edificio</title>

    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/update_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">

    <style>
        body.docente .hidden-in-view-mode,
        body.docente .edit-button,
        body.docente .action-buttons,
        body.docente .change-image-btn,
        body.docente input,
        body.docente textarea,
        body.docente select {
            display: none !important;
        }
    </style>
</head>

<body class="docente">

<div class="container">

<!-- SIDEBAR DOCENTE -->
<aside class="sidebar">
    <div class="logo">
        <img src="../../assets/images/logo2.png" width="150">
    </div>

    <nav class="menu">
        <div class="menu-group">
            <p class="menu-title">Menú Principal</p>
            <ul>
                <li><a href="docente_dashboard.php"><ion-icon name="home-outline"></ion-icon> Inicio</a></li>
                <li><a href="vista_buildings.php" class="active"><ion-icon name="business-outline"></ion-icon> Edificios</a></li>
                <li><a href="table_disponibilidad.php"><ion-icon name="calendar-outline"></ion-icon> Disponibilidad</a></li>
            </ul>
        </div>

        <div class="menu-group">
            <p class="menu-title">Reservas</p>
            <ul>
                <li><a href="mis_reservas.php"><ion-icon name="book-outline"></ion-icon> Mis reservas</a></li>
            </ul>
        </div>

        <div class="menu-group">
            <p class="menu-title">Configuración</p>
            <ul>
                <li><a href="../../php/config_docente.php"><ion-icon name="settings-outline"></ion-icon> Ajustes</a></li>
                <li><a href="../../php/cerrar_sesion.php"><ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión</a></li>
            </ul>
        </div>
    </nav>

    <div class="divider"></div>

    <div class="profile">
        <img src="<?php echo $imagen; ?>" class="profile-img">
        <div>
            <p class="user-name"><?php echo htmlspecialchars($nombre_completo); ?></p>
            <p class="user-email"><?php echo htmlspecialchars($correo); ?></p>
        </div>
    </div>
</aside>

<div id="main-content">
    <div class="header-container">
        <div class="back-button-container">
            <button class="back-button" onclick="window.history.back()">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    <div class="rectangle">
        <div class="half">
            <div class="building-showcase">
                <div class="image-container">
                    <a href="vista_spaces_docente.php?edificio_id=<?php echo $id; ?>">
                        <img src="<?php echo htmlspecialchars($usuario['imagen']); ?>" class="building-image">
                    </a>
                </div>
            </div>
        </div>
        <div class="half">
            <div class="tabs-container">

                <h1 class="building-title">
                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                </h1>

                <div class="building-type-tag <?php 
                    echo strtolower(str_replace(' ', '-', $usuario['tipo'])); 
                ?>">
                    <?php echo htmlspecialchars($usuario['tipo']); ?>
                </div>

                <div class="tabs">
                    <button class="tab-button active" data-tab="informacion">
                        <i class="fa-solid fa-circle-info"></i> Información
                    </button>
                    <button class="tab-button" data-tab="detalles">
                        <i class="ti ti-list"></i> Detalles
                    </button>
                    <button class="tab-button" data-tab="ubicacion">
                        <i class="fa-solid fa-location-dot"></i> Ubicación
                    </button>
                </div>

                <!-- TAB INFO -->
                <div class="tab-content" id="informacion">
                    <p><?php echo htmlspecialchars($usuario['descripcion']); ?></p>

                    <div class="details-grid">

                        <div class="detail-item">
                            <i class="ti ti-users"></i>
                            <div>
                                <span class="detail-label">Capacidad</span>
                                <span class="detail-value"><?php echo $usuario['cupo']; ?></span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="ti ti-building-skyscraper"></i>
                            <div>
                                <span class="detail-label">Pisos</span>
                                <span class="detail-value"><?php echo $usuario['pisos']; ?></span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="ti ti-qrcode"></i>
                            <div>
                                <span class="detail-label">Código</span>
                                <span class="detail-value"><?php echo $usuario['codigo']; ?></span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="ti ti-door"></i>
                            <div>
                                <span class="detail-label">Salones</span>
                                <span class="detail-value"><?php echo $espacios_count; ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="tab-content" id="detalles" style="display:none">
                    <div class="details-list">

                        <div class="detail-item">
                            <span class="detail-label">Dirección</span>
                            <span class="detail-value"><?php echo htmlspecialchars($usuario['direccion']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Tipo</span>
                            <span class="detail-value"><?php echo htmlspecialchars($usuario['tipo']); ?></span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
</div>

<script src="../../assets/js/building_edit.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>

<script>
    // Bloquea JS de edición
    if (document.body.classList.contains('docente')) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
                document.getElementById(btn.dataset.tab).style.display = 'block';
            });
        });
    }
</script>

</body>
</html>