<?php
require_once __DIR__ . '/../conexion.php';

function printTable($pdo, $table) {
    echo "🔹 Datos de la tabla: $table\n";
    try {
        $stmt = $pdo->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll();

        if (count($rows) === 0) {
            echo "⚠️ La tabla está vacía.\n\n";
            return;
        }

        foreach ($rows as $row) {
            echo implode(" | ", $row) . "\n";
        }
        echo "\n";
    } catch (PDOException $e) {
        echo "❌ Error al consultar $table: " . $e->getMessage() . "\n\n";
    }
}

$tables = [
    "Aspirante",
    "Socio",
    "Administrador",
    "UnidadSocio",
    "CuotaSocio",
    "UnidadHabit",
    "Obtiene",
    "Cuota",
    "Paga",
    "Asamblea",
    "JorComun",
    "Participa",
    "Vota",
    "HorasTrabajo",
    "Comprobantes",
];

foreach ($tables as $table) {
    printTable($pdo, $table);
}

echo "✅ TEST DB FINALIZADO.\n";
