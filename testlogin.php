<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=CooperativaS;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$login_error = '';
$reg_error = '';
$reg_success = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    $sql = "SELECT 'admin' AS tipo, IDAdmin AS id, Rol AS nombre, PassAd AS pass
            FROM Administrador WHERE EmailAd = ?
            UNION
            SELECT 'socio' AS tipo, CIsoc AS id, CONCAT(PnomS,' ',PapeS) AS nombre, PassS AS pass
            FROM Socio WHERE EmailS = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $email]);
    $row = $stmt->fetch();

    if ($row && $pass === $row['pass']) {  
        // Guardamos sesión según lo que espera cooperativa.php
        $_SESSION['usuario'] = $row['nombre'];
        $_SESSION['rol'] = $row['tipo'];   // 'admin' o 'socio'

        if($row['tipo'] === 'socio'){
            $_SESSION['CIsoc'] = $row['id'];  // CI del socio
        } else {
            $_SESSION['IDAdmin'] = $row['id']; // IDAdmin
        }

        header("Location: panelusuarios.php");
        exit;
    } else {
        $login_error = "Email o contraseña incorrectos";
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
        $reg_error = "Por favor complete todos los campos.";
    } else {
        $check = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi = ? OR EmailA = ?");
        $check->execute([$ci, $email]);

        if ($check->rowCount() > 0) {
            $reg_error = "CI o Email ya registrado";
        } else {
            $estado = 'Pendiente';
            $fecha_solicitud = date('Y-m-d');

            $insert = $pdo->prepare("INSERT INTO Aspirante (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$ci, $nombre, $apellido, $email, $pass, $telefono, $estado, $fecha_solicitud])) {
                $reg_success = "Registro exitoso, espere aprobación del administrador";
            } else {
                $reg_error = "Error al registrar";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login y Registro Cooperativa</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="auth-container">
  <div class="auth-left">
    <div class="auth-box">
      <h2>Iniciar Sesión</h2>
      <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit" name="login">Ingresar</button>
      </form>
      <?php if ($login_error) echo "<p style='color:red;'>$login_error</p>"; ?>

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
      if ($reg_error) echo "<p style='color:red;'>$reg_error</p>";
      if ($reg_success) echo "<p style='color:green;'>$reg_success</p>";
      ?>
    </div>
  </div>

  <div class="auth-right">
    <img src="imagenes/login.png" alt="Login" class="login-img">
  </div>
</div>
</body>
</html>
