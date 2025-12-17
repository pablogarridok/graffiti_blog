<?php
declare(strict_types=1);

// Determinar la ruta correcta de auth.php según desde dónde se llame
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
} elseif (file_exists(__DIR__ . '/../auth.php')) {
    require_once __DIR__ . '/../auth.php';
} else {
    die('Error: No se puede encontrar auth.php');
}

header('Content-Type: application/json; charset=utf-8');

/**
 * Envía una respuesta de éxito con envoltura homogénea.
 */
function responder_json_exito(mixed $contenidoDatos = [], int $codigoHttp = 200): void {
    http_response_code($codigoHttp);
    echo json_encode(
        ['ok' => true, 'data' => $contenidoDatos],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

/**
 * Envía una respuesta de error con envoltura homogénea.
 */
function responder_json_error(string $mensajeError, int $codigoHttp = 400): void {
    http_response_code($codigoHttp);
    echo json_encode(
        ['ok' => false, 'error' => $mensajeError],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// Obtener método HTTP y acción
$metodoHttpRecibido = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accionSolicitada = $_GET['action'] ?? $_POST['action'] ?? '';

// ========== ENDPOINTS DE AUTENTICACIÓN ==========

// LOGIN
if ($accionSolicitada === 'login' && $metodoHttpRecibido === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $email = trim($data['email'] ?? '');
    $pass  = trim($data['password'] ?? '');
    
    if ($email === '' || $pass === '') {
        responder_json_error('Campos incompletos', 422);
    }
    
    if (login($email, $pass)) {
        echo json_encode([
            'ok'   => true,
            'user' => me()
        ]);
    } else {
        responder_json_error('Credenciales inválidas', 401);
    }
    exit;
}

// REGISTER
if ($accionSolicitada === 'register' && $metodoHttpRecibido === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $nombre = trim($data['nombre'] ?? '');
    $email  = trim($data['email'] ?? '');
    $pass   = trim($data['password'] ?? '');
    $rol    = trim($data['rol'] ?? 'user');
    
    if ($nombre === '' || $email === '' || $pass === '') {
        responder_json_error('Campos incompletos', 422);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder_json_error('El email no tiene un formato válido', 422);
    }
    
    if (create_user($nombre, $email, $pass, $rol)) {
        responder_json_exito(['message' => 'Usuario creado correctamente'], 201);
    } else {
        responder_json_error('El usuario ya existe', 409);
    }
    exit;
}

// LOGOUT
if ($accionSolicitada === 'logout') {
    logout();
    echo json_encode(['ok' => true]);
    exit;
}

// ME (obtener usuario actual)
if ($accionSolicitada === 'me') {
    $user = me();
    if ($user) {
        echo json_encode(['ok' => true, 'user' => $user]);
    } else {
        responder_json_error('No autenticado', 401);
    }
    exit;
}

// ========== ENDPOINTS DE GESTIÓN DE USUARIOS (CRUD) ==========

// Cargar lista de usuarios
$listaUsuarios = cargar_usuarios();

// LISTAR usuarios: GET /api.php?action=list
if ($metodoHttpRecibido === 'GET' && $accionSolicitada === 'list') {
    responder_json_exito($listaUsuarios);
}

// CREAR usuario: POST /api.php?action=create
if ($metodoHttpRecibido === 'POST' && $accionSolicitada === 'create') {
    $cuerpoBruto = file_get_contents('php://input');
    $datosDecodificados = $cuerpoBruto !== '' ? (json_decode($cuerpoBruto, true) ?? []) : [];
    
    $nombreUsuarioNuevo = trim($datosDecodificados['nombre'] ?? $_POST['nombre'] ?? '');
    $correoUsuarioNuevo = trim($datosDecodificados['email'] ?? $_POST['email'] ?? '');
    $correoUsuarioNormalizado = mb_strtolower($correoUsuarioNuevo);
    $rolUsuarioNuevo = trim($datosDecodificados['rol'] ?? $_POST['rol'] ?? 'user');
    $passwordUsuarioNuevo = trim($datosDecodificados['password'] ?? $_POST['password'] ?? '');
    
    // Validaciones
    if ($nombreUsuarioNuevo === '' || $correoUsuarioNuevo === '') {
        responder_json_error('Los campos "nombre" y "email" son obligatorios.', 422);
    }
    
    if (!filter_var($correoUsuarioNuevo, FILTER_VALIDATE_EMAIL)) {
        responder_json_error('El campo "email" no tiene un formato válido.', 422);
    }
    
    if (mb_strlen($nombreUsuarioNuevo) > 60) {
        responder_json_error('El campo "nombre" excede los 60 caracteres.', 422);
    }
    
    if (mb_strlen($correoUsuarioNuevo) > 120) {
        responder_json_error('El campo "email" excede los 120 caracteres.', 422);
    }
    
    // Evitar duplicados
    if (email_exists($correoUsuarioNormalizado)) {
        responder_json_error('Ya existe un usuario con ese email.', 409);
    }
    
    if (mb_strlen($passwordUsuarioNuevo) > 255) {
        responder_json_error('El campo "password" excede los 255 caracteres.', 422);
    }
    
    // Crear usuario
    if (create_user($nombreUsuarioNuevo, $correoUsuarioNormalizado, $passwordUsuarioNuevo, $rolUsuarioNuevo)) {
        $listaUsuarios = cargar_usuarios(); // Recargar la lista
        responder_json_exito($listaUsuarios, 201);
    } else {
        responder_json_error('Error al crear el usuario', 500);
    }
}

// ELIMINAR usuario: POST /api.php?action=delete
if (($metodoHttpRecibido === 'POST' || $metodoHttpRecibido === 'DELETE') && $accionSolicitada === 'delete') {
    $indiceEnQuery = $_GET['index'] ?? null;
    
    if ($indiceEnQuery === null) {
        $cuerpoBruto = file_get_contents('php://input');
        if ($cuerpoBruto !== '') {
            $datosDecodificados = json_decode($cuerpoBruto, true) ?? [];
            $indiceEnQuery = $datosDecodificados['index'] ?? null;
        } else {
            $indiceEnQuery = $_POST['index'] ?? null;
        }
    }
    
    if ($indiceEnQuery === null) {
        responder_json_error('Falta el parámetro "index" para eliminar.', 422);
    }
    
    $indiceUsuarioAEliminar = (int) $indiceEnQuery;
    
    if (!isset($listaUsuarios[$indiceUsuarioAEliminar])) {
        responder_json_error('El índice indicado no existe.', 404);
    }
    
    // Eliminar y reindexar
    unset($listaUsuarios[$indiceUsuarioAEliminar]);
    $listaUsuarios = array_values($listaUsuarios);
    
    // Guardar
    guardar_usuarios($listaUsuarios);
    
    responder_json_exito($listaUsuarios);
}

// Acción no soportada
responder_json_error('Acción no soportada. Use: login, register, logout, me, list, create, delete', 400);