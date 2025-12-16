<?php
session_start();

// Ruta correcta al JSON de usuarios
define('USUARIOS_LOGIN', __DIR__ . '/storage/data.json');

// Cargar usuarios desde JSON
function cargar_usuarios(): array {
    if (!file_exists(USUARIOS_LOGIN)) {
        file_put_contents(USUARIOS_LOGIN, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $data = json_decode(file_get_contents(USUARIOS_LOGIN), true);
    return is_array($data) ? $data : [];
}

// Guardar usuarios en JSON
function guardar_usuarios(array $usuarios): void {
    file_put_contents(USUARIOS_LOGIN, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Login
function login(string $email, string $password): bool {
    $usuarios = cargar_usuarios();
    $email = mb_strtolower(trim($email));
    foreach ($usuarios as $u) {
        if (mb_strtolower($u['email']) === $email && password_verify($password, $u['password'])) {
            $_SESSION['email']  = $u['email'];
            $_SESSION['role']   = strtolower($u['rol'] ?? 'user');
            $_SESSION['nombre'] = $u['nombre'] ?? '';
            return true;
        }
    }
    return false;
}

// Registrar usuario nuevo
function register_user(string $email, string $password, string $nombre, string $role = 'user'): bool {
    $usuarios = cargar_usuarios();
    $email = mb_strtolower(trim($email));
    foreach ($usuarios as $u) {
        if (mb_strtolower($u['email']) === $email) return false;
    }
    $usuarios[] = [
        'nombre'   => $nombre,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol'      => strtolower($role)
    ];
    guardar_usuarios($usuarios);
    return true;
}

// Logout
function logout(): void {
    session_destroy();
}

// Requiere login para acceder a la página
function require_login(): void {
    if (!isset($_SESSION['role'])) {
        header("Location: Login.php");
        exit;
    }
}

// Obtener info del usuario logueado
function me(): ?array {
    if (!isset($_SESSION['email'])) return null;
    return [
        'email' => $_SESSION['email'],
        'role'  => $_SESSION['role'],
        'nombre'=> $_SESSION['nombre'] ?? ''
    ];
}
