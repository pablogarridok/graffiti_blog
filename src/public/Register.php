<?php
require_once __DIR__ . '/../auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($nombre === '' || $email === '' || $password === '') {
        $error = 'Todos los campos son obligatorios';
    } elseif (email_exists($email)) {
        $error = 'Este email ya está registrado';
    } else {
        create_user($nombre, $email, $password); // role = user
        $success = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
    }
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registro</title>
<link rel="stylesheet" href="assets/css/styles_login.css">
</head>
<body>

<div class="login-container">
    <h1>Crear cuenta</h1>

    <?php if ($error): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success-message"><?= htmlspecialchars($success) ?></p>
        <a href="login.php">Ir al login</a>
    <?php else: ?>

    <form method="POST">
        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Contraseña</label>
        <input type="password" name="password" required>

        <button type="submit">Crear cuenta</button>
    </form>

    <?php endif; ?>
</div>

</body>
</html>
