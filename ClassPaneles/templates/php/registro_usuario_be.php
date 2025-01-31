<?php
    
    include 'conexion_be.php';//acceso a la conexión
    
    //Almacena valores de la DB
    $nombre_completo = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    //encriptar contraseña
    $contrasena = hash('sha512', $contrasena);
    $nombre_imagen = null;
    //Manejo imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $nombre_imagen = uniqid() . "_" . basename($imagen['name']); // Generar un nombre único para la imagen
        $ruta_destino = "../uploads/" . $nombre_imagen;

        // Validar y mover la imagen al directorio "uploads"
        if (!move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
            echo '
                <script>
                    alert("Hubo un problema al subir la imagen. Inténtalo nuevamente.");
                    window.location = "../views/Admin/vista_cuentas.php";
                </script>
            ';
            exit();
        }
    }
    //Insertar los datos en tabla
    $query = "INSERT INTO usuarios(nombre_completo, correo, usuario, contrasena, rol, imagen) 
            VALUES('$nombre_completo', '$correo', '$usuario', '$contrasena', '$rol', '$nombre_imagen')";

    //verificar correo repetidos
    $verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$correo'");

    if(mysqli_num_rows($verificar_correo) > 0){
        echo '
            <script>
                alert("El correo que acabas de ingresar ya esta registrado, intenta con otro nuevo");
                window.location = "../views/Admin/vista_cuentas.php";
            </script>
        ';
        exit();
    } 
    //verficar usuarios repetidos
    $verificar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE usuario='$usuario'");

    if (mysqli_num_rows($verificar_usuario) > 0) {
        echo '
            <script>
                alert("El usuario que acabas de ingresar ya esta en uso, intenta con otro nuevo");
                window.location = "../views/Admin/vista_cuentas.php";
            </script>
            ';
        exit();
    }

    //Ejecutar query
    $ejecutar = mysqli_query($conexion, $query);

    if($ejecutar){
        echo '
            <script>
                alert("El usuario ha sido registrado exitosamente");
                window.location = "../views/Admin/vista_cuentas.php";
            </script>
        ';
    }else{
        echo '
            <script>
                alert("El usuario no se pudo registrar, intentalo de nuevamente");
                window.location = "../views/Admin/vista_cuentas.php";
            </script>
        ';
    }

    mysqli_close($conexion);

?>
