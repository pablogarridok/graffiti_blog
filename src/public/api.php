<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function responder_json_exito(mixed $contenidoDatos = [], int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode(['ok' => true, 'data' => $contenidoDatos], JSON_UNESCAPED_UNICODE);
    exit;
}

function responder_json_error(string $mensajeError, int $codigoHttp = 400): void
{
    http_response_code($codigoHttp);
    echo json_encode(['ok' => false, 'error' => $mensajeError], JSON_UNESCAPED_UNICODE);
    exit;
}

$rutaArchivoDatosJson = __DIR__ .'/../storage/data.json';
if (!file_exists($rutaArchivoDatosJson)) {
    file_put_contents($rutaArchivoDatosJson, json_encode([]) . "\n");
}

$listaUsuarios = json_decode((string) file_get_contents($rutaArchivoDatosJson), true);
if (!is_array($listaUsuarios)) {
    $listaUsuarios = [];
}

$metodoHttpRecibido = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accionSolicitada = $_GET['action'] ?? $_POST['action'] ?? 'list';

// LISTAR
if ($metodoHttpRecibido === 'GET' && $accionSolicitada === 'list') {
    responder_json_exito($listaUsuarios);
}

// CREAR
if ($metodoHttpRecibido === 'POST' && $accionSolicitada === 'create') {
    $cuerpoBruto = (string) file_get_contents('php://input');
    $datosDecodificados = $cuerpoBruto !== '' ? (json_decode($cuerpoBruto, true) ?? []) : [];

    $nombre = trim((string) ($datosDecodificados['nombre'] ?? $_POST['nombre'] ?? ''));
    $email = trim((string) ($datosDecodificados['email'] ?? $_POST['email'] ?? ''));
    $password = trim((string) ($datosDecodificados['password'] ?? $_POST['password'] ?? ''));
    $rol = trim((string) ($datosDecodificados['role'] ?? $_POST['role'] ?? 'user'));

    $emailNormalizado = mb_strtolower($email);

    // Validaciones básicas
    if ($nombre === '' || $email === '' || $password === '' || $rol === '') {
        responder_json_error('Todos los campos (nombre, email, password, rol) son obligatorios.', 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder_json_error('El campo "email" no tiene un formato válido.', 422);
    }
    if (mb_strlen($nombre) > 60) {
        responder_json_error('El campo "nombre" excede los 60 caracteres.', 422);
    }
    if (mb_strlen($email) > 120) {
        responder_json_error('El campo "email" excede los 120 caracteres.', 422);
    }
    if (!in_array($rol, ['user','admin'], true)) {
        responder_json_error('El campo "rol" no es válido.', 422);
    }

    if (existeEmailDuplicado($listaUsuarios, $emailNormalizado)) {
        responder_json_error('Ya existe un usuario con ese email.', 409);
    }

    $listaUsuarios[] = [
        'nombre' => $nombre,
        'email' => $emailNormalizado,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol' => $rol,
    ];

    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );

    responder_json_exito($listaUsuarios, 201);
}

// ELIMINAR
if (($metodoHttpRecibido === 'POST' || $metodoHttpRecibido === 'DELETE') && $accionSolicitada === 'delete') {
    $indice = $_GET['index'] ?? null;
    if ($indice === null) {
        $cuerpoBruto = (string) file_get_contents('php://input');
        $datosDecodificados = $cuerpoBruto !== '' ? (json_decode($cuerpoBruto, true) ?? []) : [];
        $indice = $datosDecodificados['index'] ?? $_POST['index'] ?? null;
    }
    if ($indice === null) {
        responder_json_error('Falta el parámetro "index" para eliminar.', 422);
    }

    $indice = (int) $indice;
    if (!isset($listaUsuarios[$indice])) {
        responder_json_error('El índice indicado no existe.', 404);
    }

    unset($listaUsuarios[$indice]);
    $listaUsuarios = array_values($listaUsuarios);

    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );

    responder_json_exito($listaUsuarios);
}

// ACTUALIZAR
if ($metodoHttpRecibido === 'POST' && $accionSolicitada === 'update') {
    $cuerpoBruto = (string) file_get_contents('php://input');
    $datosDecodificados = $cuerpoBruto !== '' ? (json_decode($cuerpoBruto, true) ?? []) : [];

    $indice = (int) ($datosDecodificados['index'] ?? -1);
    if (!isset($listaUsuarios[$indice])) {
        responder_json_error('El índice indicado no existe.', 404);
    }

    $nombre = trim((string) ($datosDecodificados['nombre'] ?? ''));
    $email = trim((string) ($datosDecodificados['email'] ?? ''));
    $password = trim((string) ($datosDecodificados['password'] ?? ''));
    $rol = trim((string) ($datosDecodificados['role'] ?? ''));

    if ($nombre === '' || $email === '' || $rol === '') {
        responder_json_error('Los campos nombre, email y rol son obligatorios.', 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder_json_error('El campo "email" no tiene un formato válido.', 422);
    }

    $emailNormalizado = mb_strtolower($email);
    foreach ($listaUsuarios as $i => $u) {
        if ($i !== $indice && isset($u['email']) && mb_strtolower($u['email']) === $emailNormalizado) {
            responder_json_error('Ya existe un usuario con ese email.', 409);
        }
    }

    $listaUsuarios[$indice]['nombre'] = $nombre;
    $listaUsuarios[$indice]['email'] = $emailNormalizado;
    if ($password !== '') {
        $listaUsuarios[$indice]['password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $listaUsuarios[$indice]['rol'] = $rol;

    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );

    responder_json_exito($listaUsuarios);
}
responder_json_error('Acción no soportada. Use list | create | delete', 400);

function existeEmailDuplicado(array $usuarios, string $emailNormalizado): bool
{
    foreach ($usuarios as $u) {
        if (isset($u['email']) && is_string($u['email']) && mb_strtolower($u['email']) === $emailNormalizado) {
            return true;
        }
    }
    return false;
}
