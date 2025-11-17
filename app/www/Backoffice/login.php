<?php
session_start();
$login_error = '';
$reg_error = '';
$reg_success = '';

require_once __DIR__ . "/../Backend/conexion.php";

if (isset($_SESSION['usuario'])) {
    $login_error = "Ya hay sesión iniciada como " . $_SESSION['usuario'];
}

if (isset($_POST['login']) && !isset($_SESSION['usuario'])) {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $sql = "SELECT 'Administrador' AS tipo, IDAdmin AS id,
    CONCAT(NomA, ' ', ApeA) AS nombre,
    PassAd AS pass, CIadm
    FROM Administrador WHERE EmailAd = ?
    UNION
    SELECT 'Socio' AS tipo, CIsoc AS id,
           CONCAT(PnomS, ' ', PapeS) AS nombre,
           PassS AS pass, CIsoc AS CIadm
    FROM Socio WHERE EmailS = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      if ($pass === $row['pass']) {
          $_SESSION['usuario'] = $row['nombre'];
          $rol = strtolower($row['tipo']) === 'administrador' ? 'admin' : 'socio';
          $_SESSION['rol'] = $rol;
  
          if ($rol === 'socio') {
              $_SESSION['CIsoc'] = $row['id'];
          } else {
              $_SESSION['IDAdmin'] = $row['id'];
              $_SESSION['CIadm'] = $row['CIadm'];
          }
  
          header("Location: panel.php");
          exit;
        } else {
            $login_error = "Contraseña incorrecta";
        }
    } else {
        $login_error = "Usuario no encontrado";
    }
}

if (isset($_POST['registrar'])) {
    $ci = $_POST['ci'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email_reg'] ?? '';
    $pass = $_POST['password_reg'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (!$ci || !$nombre || !$apellido || !$email || !$pass || !$telefono) {
        $reg_error = "Por favor complete todos los campos";
    } else {
        $check = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi = ? OR EmailA = ?");
        $check->execute([$ci, $email]);

        if ($check->rowCount() > 0) {
            $reg_error = "CI o Email ya registrado";
        } else {
            $estado = 'Pendiente';
            $fecha_solicitud = date('Y-m-d');

            $insert = $pdo->prepare(
                "INSERT INTO Aspirante 
                (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli)
                 VALUES (?,?,?,?,?,?,?,?)"
            );

            if ($insert->execute([$ci, $nombre, $apellido, $email, $pass, $telefono, $estado, $fecha_solicitud])) {
                $reg_success = "Registro exitoso, espere aprobación del administrador";
            } else {
                $reg_error = "Error al registrar";
            }
        }
    }
}
if (isset($_SESSION['usuario']) || isset($_SESSION['ci_sess'])) {
    header("Location: panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login y Registro Cooperativa</title>
<link rel="stylesheet" href="../Backoffice/estilo2.css">
</head>
<body>

<header class="header-fixed">
  <div class="container">
    <div class="logo-container">
      <a href="../LandingPage/index.php">
        <img src="../LandingPage/imagenes/logo.jpeg" alt="Logo Cooperativa" class="logo circular-logo">
      </a>
    </div>
    <nav class="menu">
      <ul>
        <li><a href="../LandingPage/index.php">Inicio</a></li>
        <?php if(isset($_SESSION['usuario'])): ?>
          <li><a href="../Backend/logout.php" class="cerrar-sesion">Cerrar sesión</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<div class="auth-container full-white">
  <div class="auth-left">
    <div class="auth-box">
      <h2>Iniciar Sesión</h2>
      <?php if (isset($_SESSION['usuario'])): ?>
        <p class="error"><?= $login_error ?></p>
      <?php else: ?>
        <form method="post">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Contraseña" required>
          <button type="submit" name="login">Ingresar</button>
        </form>
        <?php if($login_error) echo "<p class='error'>$login_error</p>"; ?>
      <?php endif; ?>

      <hr style="margin:25px 0;">

      <h2>Registro de Aspirante</h2>
      <form method="post">
        <input type="text" name="ci" placeholder="CI" required>
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="email" name="email_reg" placeholder="Email" required>
        <input type="password" name="password_reg" placeholder="Contraseña" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <button type="submit" name="registrar">Registrar</button>
      </form>
      <?php 
      if($reg_error) echo "<p class='error'>$reg_error</p>";
      if($reg_success) echo "<p class='success'>$reg_success</p>";
      ?>
    </div>
  </div>

  <div class="auth-right">
    <img src="../LandingPage/imagenes/login.png" alt="Login" class="login-img">
  </div>
</div>

</body>
</html>
