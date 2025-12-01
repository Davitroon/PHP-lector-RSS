<?php

// Ruta a tu archivo SQLite
$dbFile = __DIR__ . '/periodicos.sqlite';

// Verificar que el archivo existe
if (!file_exists($dbFile)) {
    die("Error: La base de datos no se encuentra en $dbFile");
}

// Conectar a SQLite en modo solo lectura
try {
    $db = new SQLite3($dbFile, SQLITE3_OPEN_READONLY);
    // Opcional: habilitar UTF-8
    $db->exec("PRAGMA encoding = 'UTF-8';");
} catch (Exception $e) {
    die("Error al abrir la base de datos: " . $e->getMessage());
}
