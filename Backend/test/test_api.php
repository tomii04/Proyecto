<?php
// =====================================
// 🧪 TEST AUTOMÁTICO DE API - Cooperativa
// =====================================

$baseUrl = "http://localhost/pdc3/Backend/api/";
$cookieFile = __DIR__ . "/cookie.txt";

// Elimina cookies anteriores para limpiar sesión
if (file_exists($cookieFile)) unlink($cookieFile);

function printLine($text, $ok = null) {
    if ($ok === true) echo "✅ $text\n";
    elseif ($ok === false) echo "❌ $text\n";
    else echo "🔹 $text\n";
}

printLine("Probando LOGIN en auth.php...");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$GLOBALS['baseUrl']}auth.php");
curl_setopt($ch, CURLOPT_POST, true);

$postData = http_build_query([
    'login' => 1,
    'email' => 'carlosyrami@gmail.com',
    'password' => 'password12'
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);

$response = curl_exec($ch);
echo "• Respuesta de auth.php:\n$response\n";

if (strpos($response, '"success":true') !== false) {
    printLine("Login exitoso.", true);
} else {
    printLine("Error en login, no se podrá continuar con endpoints protegidos.", false);
}

$endpoints = [
    "usuarios.php?accion=aspirantes"   => "Listar aspirantes",
    "usuarios.php?accion=socios"       => "Listar socios",
    "cooperativa.php?accion=unidades"  => "Listar unidades",
    "cooperativa.php?accion=comprobantes" => "Listar comprobantes",
    "cooperativa.php?accion=horas"     => "Listar horas de trabajo"
];

foreach ($endpoints as $url => $desc) {
    printLine("Probando $url...");
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

    $response = curl_exec($ch);
    echo "$response\n";

    if (strpos($response, 'error') !== false || !$response) {
        printLine("Fallo: sesión no válida o login incorrecto.", false);
    } else {
        printLine("$desc OK", true);
    }
}

printLine("Probando acceso al Backoffice...");

$backofficeUrl = "http://localhost/pdc3/Backoffice/login.php";
$resp = @file_get_contents($backofficeUrl);

if ($resp === false) {
    printLine("No se pudo acceder a $backofficeUrl", false);
} else {
    printLine("Backoffice accesible correctamente ($backofficeUrl)", true);
}

curl_close($ch);
printLine("🧪 TEST FINALIZADO 🧪");
