<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);

$usuario_sess = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? $_SESSION['tipo'] ?? 'invitado';
$ci_sess = $_SESSION['ci'] ?? $_SESSION['CIsoc'] ?? null;

if(!$usuario_sess){
    header("Location: testlogin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel de Usuarios - Cooperativa</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<h1>Panel de Usuarios</h1>
<p>Usuario: <strong><?= htmlentities($usuario_sess) ?></strong> — Rol: <strong><?= htmlentities($rol) ?></strong></p>
<div class="tabs" id="tabs"></div>
<div id="panel-content">Cargando...</div>
<script src="cooperativa.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rol = <?= json_encode($rol) ?>;
    const ci_sess = <?= json_encode($ci_sess) ?>;
    console.log('Rol:', rol, 'CI sesión:', ci_sess);
    initPanelUsuarios(rol, ci_sess);
});
</script>
</body>
</html>
