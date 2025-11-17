<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);

$usuario_sess = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? 'invitado';
$ci_sess = $_SESSION['CIsoc'] ?? null;

if (!$usuario_sess) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel de Usuarios - Cooperativa</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../Backoffice/estilo2.css">
</head>
<body>

<div class="panel-layout">

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../LandingPage/imagenes/logo.jpeg" alt="Logo Cooperativa" class="sidebar-logo">
            <div class="sidebar-user">
                <?= htmlentities($usuario_sess) ?><br>
                <span class="sidebar-role"><?= htmlentities($rol) ?></span>
            </div>
        </div>
        <div class="tabs" id="tabs"></div>
        <button onclick="window.location.href='../LandingPage/index.php'" class="volver-btn">Volver al inicio</button>
        <button id="theme-toggle" aria-label="Cambiar tema">🌙</button>
        <button id="logout-btn" class="logout-btn">Cerrar Sesión</button>
    </aside>

    <div class="panel-main">
        <div id="panel-content">
            <!--<h1>Panel de Usuarios</h1>
            <p>
                Usuario: <strong><?= htmlentities($usuario_sess) ?></strong> — Rol: <strong><?= htmlentities($rol) ?></strong>
            </p>-->

            <div id="panelAspirantes" style="display:none;"></div>
            <div id="panelSocios" style="display:none;"></div>
            <div id="panelUnidades" style="display:none;"></div>
            <div id="panelAsambleas" style="display:none;"></div>
            <div id="panelHoras" style="display:none;"></div>
            <div id="panelComprobantes" style="display:none;"></div>
            <div id="panelUnidad" style="display:none;"></div>
        </div>
    </div>

</div>

<script src="cooperativa.js?v=<?= time() ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.rol = <?= json_encode($rol) ?>;
    window.ci_sess = <?= json_encode($ci_sess) ?>;
    initPanelUsuarios(window.rol, window.ci_sess);
    document.getElementById('logout-btn').addEventListener('click', () => {
        window.location.href = '../Backend/logout.php';
    });
});
</script>

</body>
</html>