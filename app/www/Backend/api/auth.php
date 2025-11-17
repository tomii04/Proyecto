<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

require_once __DIR__ . '/../conexion.php';
global $pdo;

if ($method === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    $sql = "SELECT 'Administrador' AS tipo, IDAdmin AS id, Rol AS nombre, EmailAd AS email, PassAd AS pass
            FROM Administrador WHERE EmailAd=?
            UNION
            SELECT 'Socio', CIsoc, CONCAT(PnomS,' ',PapeS), EmailS, PassS
            FROM Socio WHERE EmailS=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && $pass === $user['pass']) {
        if($method !== 'CLI') session_start();
        $_SESSION['usuario'] = $user['nombre'];

        $rol = strtolower($user['tipo']) === 'administrador' ? 'admin' : 'socio';
        $_SESSION['rol'] = $rol;

        if($rol === 'socio') {
            $_SESSION['CIsoc'] = $user['id'];
        } else {
            $_SESSION['IDAdmin'] = $user['id'];
        }

        echo json_encode(["success"=>true]);
    } else {
        echo json_encode(["success"=>false,"error"=>"Credenciales incorrectas"]);
    }
    exit;
}

if ($method === 'POST' && isset($_POST['registrar'])) {
    $ci       = $_POST['ci'] ?? '';
    $nombre   = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email    = $_POST['email_reg'] ?? '';
    $pass     = $_POST['password_reg'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (!$ci || !$nombre || !$apellido || !$email || !$pass || !$telefono) {
        echo json_encode(["success"=>false,"error"=>"Faltan datos para el registro"]);
        exit;
    }

    $check = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi = ? OR EmailA = ?");
    $check->execute([$ci, $email]);
    if ($check->rowCount() > 0) {
        echo json_encode(["success"=>false,"error"=>"CI o Email ya registrado"]);
        exit;
    }

    $sql = "INSERT INTO Aspirante (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
    $stmt = $pdo->prepare($sql);
    if($stmt->execute([$ci, $nombre, $apellido, $email, $pass, $telefono])) {
        echo json_encode(["success"=>true,"mensaje"=>"Registro exitoso, espere aprobación"]);
    } else {
        echo json_encode(["success"=>false,"error"=>"Error al registrar"]);
    }
    exit;
}

if($method === 'CLI') {
    echo json_encode(["success"=>true,"mensaje"=>"auth.php listo para pruebas CLI"]);
    exit;
}

echo json_encode(["success"=>false,"error"=>"Método no soportado"]);
exit;
