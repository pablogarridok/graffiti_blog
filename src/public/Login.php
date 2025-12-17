<?php
require_once __DIR__ . '/../auth.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emailForm = trim($_POST["usuario"] ?? '');
    $passForm  = trim($_POST["password"] ?? '');

    if ($emailForm === '' || $passForm === '') {
        $error = '';
    } elseif (login($emailForm, $passForm)) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: index_ajax.php");
        } else {
            header("Location: blog_graffiti.php");
        }
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/styles_login.css">
</head>
<body>




<div class="login-container">
    <h1>Iniciar Sesión</h1>

    <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="text" name="usuario" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Entrar</button>
    </form>
    <div class="register-link">
        <p>¿No tienes cuenta?</p>
    <a href="register.php">Crear cuenta</a>
</div>
</div>

</body>
</html>
