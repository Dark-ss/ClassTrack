<?php

if (isset($_COOKIE['admin_session'])) {
    session_name("admin_session");
    session_start();
    session_unset();
    session_destroy();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Revisar si existe la sesión de docente
if (isset($_COOKIE['docente_session'])) {
    session_name("docente_session");
    session_start();
    session_unset();
    session_destroy();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
header("Location: ../index.php");
exit();
?>
