<?php
require_once 'Turso.php';

try {
    // Iniciamos la conexión a Turso
    $db = new TursoConnection();

} catch (Exception $e) {
    die("Error al conectar con Turso: " . $e->getMessage());
}
