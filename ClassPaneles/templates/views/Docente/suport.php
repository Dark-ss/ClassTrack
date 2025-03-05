<?php
include '../../php/docente_session.php';
include '../../php/conexion_be.php';

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_remitente = $_POST['id_remitente'];
    $id_destinatario = $_POST['id_destinatario'];
    $mensaje = trim($_POST['mensaje']);
    $nivel_prioridad = trim($_POST['nivel_prioridad']);
    $tipo = trim($_POST['tipo']);

    if (!empty($mensaje)) {
        $sql = "INSERT INTO mensajes (id_remitente, id_destinatario, mensaje, nivel_prioridad, tipo) 
        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iisss", $id_remitente, $id_destinatario, $mensaje,$nivel_prioridad, $tipo,);
        if ($stmt->execute()) {
            echo "Mensaje enviado con éxito";
            header("Location: suport.php?msg=success");
            exit();
        } else {
            echo "Error al enviar el mensaje";
        }
    } else {
        echo "El mensaje no puede estar vacío.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style_panel.css">
    <link rel="stylesheet" href="../../assets/css/style_building.css?v=1.0">
    <link rel="shortcut icon" href="../../assets/images/logo2.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel Docente</title>
</head>

<body>
<div class="container">
<?php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
        <aside class="sidebar">
            <div class="logo">
                <img src="../../assets/images/logo2.png" alt="Logo" class="logo-img" width="150" height="auto">
            </div>
            <nav class="menu">
                <div class="menu-group">
                    <p class="menu-title">Menú Principal</p>
                    <ul>
                        <li><a href="docente_dashboard.php"
                                class="<?php echo $currentFile == 'docente_dashboard.php' ? 'active' : ''; ?>">
                                <ion-icon name="home-outline"></ion-icon> Inicio
                            </a></li>
                        <li><a href="vista_buildings.php"
                                class="<?php echo $currentFile == 'vista_buildings.php' ? 'active' : ''; ?>">
                                <ion-icon name="business-outline"></ion-icon> Edificios
                            </a></li>
                        <li><a href="table_disponibilidad.php"
                                class="<?php echo $currentFile == 'table_disponibilidad.php' ? 'active' : ''; ?>">
                                <ion-icon name="list-outline"></ion-icon> Disponibilidad
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Gestión de reservas</p>
                    <ul>
                        <li><a href="mis_reservas.php"
                                class="<?php echo $currentFile == 'mis_reservas.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Mis reservas
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Ayuda</p>
                    <ul>
                        <li><a href="suport.php"
                                class="<?php echo $currentFile == 'suport.php' ? 'active' : ''; ?>">
                                <ion-icon name="calendar-outline"></ion-icon> Soporte técnico
                            </a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <p class="menu-title">Configuración</p>
                    <ul>
                        <li><a href="../../php/config_docente.php"
                                class="<?php echo $currentFile == 'config.php' ? 'active' : ''; ?>">
                                <ion-icon name="settings-outline"></ion-icon> Ajustes
                            </a></li>
                        <li><a href="../../php/cerrar_sesion.php"
                                class="<?php echo $currentFile == 'cerrar_sesion.php' ? 'active' : ''; ?>">
                                <ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión
                            </a></li>
                    </ul>
                </div>
            </nav>
            <div class="divider"></div>
            <div class="profile">
                <img src="<?php echo $imagen; ?>" alt="Foto de perfil" class="profile-img">
                <div>
                    <p class="user-name"><?php echo htmlspecialchars($nombre_completo); ?></p>
                    <p class="user-email"> <?php echo htmlspecialchars($correo); ?></p>
                </div>
            </div>
        </aside>

        <div class="modal-content">
    <h2>Enviar Queja o Comentario</h2>
    <form action="suport.php" method="POST">
        <input type="hidden" name="mensaje_update" value="true">
        <input type="hidden" name="id_remitente" value="<?php echo $_SESSION['id_usuario']; ?>">
        <input type="hidden" name="id_destinatario" value="15">
        <input type="hidden" name="fecha_registro" value="<?php echo date('Y-m-d H:i:s'); ?>">

        <div class="form-group-container">
            <div class="form-group">
                <label for="nivel_prioridad">Nivel de Prioridad:</label>
                <select id="nivel_prioridad" name="nivel_prioridad" required>
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo de Mensaje:</label>
                <select id="tipo" name="tipo" required>
                    <option value="Soporte">Soporte</option>
                    <option value="Desarrollo">Desarrollo</option>
                    <option value="Capacitación">Capacitación</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="mensaje">Descripción:</label>
            <textarea name="mensaje" id="mensaje" placeholder="Escribe tu mensaje aquí..." required></textarea>
        </div>

        <div class="buttons-form-container">
            <button type="submit" class="save-button-reservation">Enviar Mensaje</button>
        </div>
    </form>
</div>
</main>

</div>
</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</html>