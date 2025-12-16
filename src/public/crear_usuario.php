<?php
require_once __DIR__ . '/../auth.php';

$email = 'pablo@admin.com';
$password = '1234';
$nombre = 'pablo';
$role = 'admin'; 

if (register_user($email, $password, $nombre, $role)) {
    echo "Usuario creado correctamente.";
} else {
    echo "Ya existe un usuario con ese email.";
}
