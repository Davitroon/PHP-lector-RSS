<?php

// Ruta a tu archivo SQLite
$dbFile = __DIR__ . '/database.sqlite';

// Conectar a SQLite
$db = new SQLite3($dbFile);

// Opcional: habilitar UTF-8
$db->exec("PRAGMA encoding = 'UTF-8';");