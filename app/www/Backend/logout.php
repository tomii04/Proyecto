<?php
session_start();
session_unset();
session_destroy();

if (php_sapi_name() === 'cli' || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["success" => true, "mensaje" => "Sesión cerrada"]);
    exit;
}

header("Location: ../Backoffice/login.php");
exit;
?>