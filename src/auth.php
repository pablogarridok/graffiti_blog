<?php
declare(strict_types=1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ruta al archivo JSON de usuarios
define('USUARIOS_FILE', __DIR__ . '/storage/data.json');

/**
 * Cargar usuarios desde el archivo JSON
 */
function cargar_usuarios(): array {
    if (!file_exists(USUARIOS_FILE)) {
        $dir = dirname(USUARIOS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            USUARIOS_FILE,
            json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
    $data = json_decode(file_get_contents(USUARIOS_FILE), true);
    return is_array($data) ? $data : [];
}

/**
 * Guardar usuarios en el archivo JSON
 */
function guardar_usuarios(array $usuarios): void {
    $dir = dirname(USUARIOS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        USUARIOS_FILE,
        json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Verificar si un email ya existe
 */
function email_exists(string $email): bool {
    $usuarios = cargar_usuarios();
    $email = mb_strtolower(trim($email));
    
    foreach ($usuarios as $u) {
        if (mb_strtolower($u['email']) === $email) {
            return true;
        }
    }
    return false;
}

/**
 * Crear un nuevo usuario
 */
function create_user(string $nombre, string $email, string $password, string $rol = 'user'): bool {
    if (email_exists($email)) {
        return false;
    }
    
    $usuarios = cargar_usuarios();
    $email = mb_strtolower(trim($email));
    
    $usuarios[] = [
        'id'       => uniqid(),
        'nombre'   => $nombre,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol'      => $rol
    ];
    
    guardar_usuarios($usuarios);
    return true;
}

/**
 * Login de usuario
 */
function login(string $email, string $password): bool {
    $usuarios = cargar_usuarios();
    $email = mb_strtolower(trim($email));
    
    foreach ($usuarios as $u) {
        if (
            mb_strtolower($u['email']) === $email &&
            password_verify($password, $u['password'])
        ) {
            $_SESSION['email']  = $u['email'];
            $_SESSION['rol']    = strtolower($u['rol'] ?? 'user');
            $_SESSION['role']   = strtolower($u['rol'] ?? 'user'); // alias
            $_SESSION['nombre'] = $u['nombre'] ?? '';
            return true;
        }
    }
    return false;
}

/**
 * Logout de usuario
 */
function logout(): void {
    session_unset();
    session_destroy();
}

/**
 * Requiere login para acceder a la página
 */
function require_login(): void {
    if (!isset($_SESSION['email'])) {
        header("Location: Login.php");
        exit;
    }
}

/**
 * Obtener información del usuario logueado
 */
function me(): ?array {
    if (!isset($_SESSION['email'])) {
        return null;
    }
    
    return [
        'email'  => $_SESSION['email'],
        'rol'    => $_SESSION['rol'] ?? 'user',
        'nombre' => $_SESSION['nombre'] ?? ''
    ];
}