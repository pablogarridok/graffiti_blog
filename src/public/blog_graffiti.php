<?php
session_start();

// Si no hay sesión activa, redirige al login
//if (!isset($_SESSION['email'])) {
//    header('Location: Login.php'); 
//    exit;
//}

include 'includes/functions.php';

// recuperar errores y valores antiguos
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

// limpiar para que no se repitan al recargar
unset($_SESSION['errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/styles_sociograma.css">
    <script src="assets/validate.js" defer></script>
    <title>Blog Graffiti</title>
</head>

<body>


<div class="top-bar">
    <h1 class="sociograma-titulo">AQUI VA EL BLOG</h1>

    <?php if (!isset($_SESSION['email'])): ?>
        <form method="POST" action="Login.php">
            <button onclick="window.location.href='login.php'">Iniciar sesión</button>    
        </form>
    <?php else: ?>
        <form method="POST" action="Logout.php">
            <button type="submit">Cerrar sesión</button>
        </form>
    <?php endif; ?>


</div>





<?php include 'includes/footer.php'; ?>

</body>
</html>
