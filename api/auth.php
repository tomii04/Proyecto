<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$host = "127.0.0.1";
$db   = "CooperativaS";
$user = "root";
$pass = "";
$charset = "utf8mb4";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_GET['login'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $pass = $data['password'] ?? '';

    $sql = "SELECT 'Administrador' AS tipo, IDAdmin AS id, Rol AS nombre, EmailAd AS email, PassAd AS pass 
            FROM Administrador WHERE EmailAd=?
            UNION
            SELECT 'Socio', CIsoc, CONCAT(PnomS,' ',PapeS), EmailS, PassS 
            FROM Socio WHERE EmailS=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if ($user && $pass === $user['pass']) {  
        unset($user['pass']);
        echo json_encode(["success" => true, "usuario" => $user]);
    } else {
        echo json_encode(["success" => false, "error" => "Credenciales incorrectas"]);
    }
    exit;
}

if ($method === 'POST' && isset($_GET['register'])) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['ci'], $data['nombre'], $data['apellido'], $data['email'], $data['password'], $data['telefono'])) {
        echo json_encode(["success" => false, "error" => "Faltan datos para el registro"]);
        exit;
    }

    $check = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi = ? OR EmailA = ?");
    $check->execute([$data['ci'], $data['email']]);
    if ($check->rowCount() > 0) {
        echo json_encode(["success" => false, "error" => "CI o Email ya registrado"]);
        exit;
    }

    $sql = "INSERT INTO Aspirante (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['ci'], $data['nombre'], $data['apellido'], $data['email'], $data['password'], $data['telefono']]);
    echo json_encode(["success" => true, "mensaje" => "Registro exitoso, espere aprobación"]);
    exit;
}

echo json_encode(["error" => "Método no soportado"]);
exit;
