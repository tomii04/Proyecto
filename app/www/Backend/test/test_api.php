<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/usuarios.php';
require_once __DIR__ . '/../api/cooperativa.php';

function printLine($text, $ok = null) {
    if ($ok === true) echo "✅ $text\n";
    elseif ($ok === false) echo "❌ $text\n";
    else echo "🔹 $text\n";
}

printLine("Probando LOGIN...");

$email = 'carlosyrami@gmail.com';
$password = 'password12';

try {
    $loginSuccess = login($email, $password); 
    if ($loginSuccess) {
        printLine("Login exitoso para $email", true);
    } else {
        printLine("Login fallido para $email", false);
    }
} catch (Exception $e) {
    printLine("Error en login: " . $e->getMessage(), false);
}

$endpoints = [
    'usuarios_aspirantes' => function() { return listarAspirantes(); },
    'usuarios_socios'     => function() { return listarSocios(); },
    'cooperativa_unidades'=> function() { return listarUnidades(); },
    'cooperativa_comprobantes' => function() { return listarComprobantes(); },
    'cooperativa_horas'   => function() { return listarHorasTrabajo(); }
];

foreach ($endpoints as $name => $func) {
    printLine("Probando $name...");
    try {
        $result = $func();
        if (!empty($result)) {
            printLine("$name OK", true);
            print_r($result);
        } else {
            printLine("$name vacío o fallo", false);
        }
    } catch (Exception $e) {
        printLine("Error en $name: " . $e->getMessage(), false);
    }
}

printLine("🧪 TEST FINALIZADO 🧪");
