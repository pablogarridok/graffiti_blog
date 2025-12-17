<?php
// Pon este archivo en src/public/test_api.php
// Y accede a http://localhost:8080/test_api.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de API</h1>";

// 1. Verificar que auth.php se carga bien
echo "<h2>1. Cargando auth.php...</h2>";
require_once __DIR__ . '/../auth.php';
echo "✓ auth.php cargado correctamente<br>";

// 2. Verificar ruta del archivo de datos
echo "<h2>2. Archivo de datos</h2>";
echo "Ruta: " . USUARIOS_FILE . "<br>";
echo "¿Existe? " . (file_exists(USUARIOS_FILE) ? "SÍ" : "NO") . "<br>";

// 3. Intentar cargar usuarios
echo "<h2>3. Cargando usuarios...</h2>";
try {
    $usuarios = cargar_usuarios();
    echo "✓ Usuarios cargados: " . count($usuarios) . "<br>";
    echo "<pre>" . print_r($usuarios, true) . "</pre>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// 4. Test directo de la API
echo "<h2>4. Test de API (action=list)</h2>";
$_GET['action'] = 'list';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
include __DIR__ . '/../api.php';
$output = ob_get_clean();

echo "Respuesta de la API:<br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

$json = json_decode($output, true);
if ($json) {
    echo "<br>JSON parseado correctamente:<br>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<br>✗ Error al parsear JSON<br>";
}