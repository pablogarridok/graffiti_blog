<?php
session_start();
include 'includes/functions.php';

// procesar.php
// 1) Solo aceptamos POST; si alguien entra directo, le mandamos al formulario.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sociograma.php');
    exit;
}

$errors = [];
$old = $_POST; // guardamos los datos enviados
$respuestas = [];

// ====== Datos Información Personal ======
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW));
$clase = filter_input(INPUT_POST, 'clase', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT);
$fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_SPECIAL_CHARS);

// ====== Datos Preferencias de Trabajo ======
$preferenciaPositivaInput = trim(filter_input(INPUT_POST, 'preferenciaPositivaInput', FILTER_UNSAFE_RAW));
$motivoPositivo = trim(filter_input(INPUT_POST, 'motivoPositivo', FILTER_UNSAFE_RAW));
$color_favorito = filter_input(INPUT_POST, 'color_favorito', FILTER_SANITIZE_SPECIAL_CHARS);

// ====== Datos Habilidades ======
$rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_SPECIAL_CHARS);
$lenguaje = filter_input(INPUT_POST, 'lenguaje', FILTER_SANITIZE_SPECIAL_CHARS);
$temas = $_POST['temas'] ?? []; // array de checkboxes
$git = filter_input(INPUT_POST, 'git', FILTER_VALIDATE_INT);
$liderado = filter_input(INPUT_POST, 'liderado', FILTER_SANITIZE_SPECIAL_CHARS);

// ====== Datos Dinámica de Trabajo ======
$comunicacion = filter_input(INPUT_POST, 'comunicacion', FILTER_SANITIZE_SPECIAL_CHARS);
$frecuencia_reuniones = filter_input(INPUT_POST, 'frecuencia_reuniones', FILTER_SANITIZE_SPECIAL_CHARS);
$trabajo_equipo = filter_input(INPUT_POST, 'trabajo_equipo', FILTER_SANITIZE_SPECIAL_CHARS);
$conflictos = filter_input(INPUT_POST, 'conflictos', FILTER_SANITIZE_SPECIAL_CHARS);

// ====== Datos Organización y Bienestar ======
$gestion_tiempo = filter_input(INPUT_POST, 'gestion_tiempo', FILTER_SANITIZE_SPECIAL_CHARS);
$estres = filter_input(INPUT_POST, 'estres', FILTER_VALIDATE_INT);
$entorno = filter_input(INPUT_POST, 'entorno', FILTER_SANITIZE_SPECIAL_CHARS);
$bienestar = filter_input(INPUT_POST, 'bienestar', FILTER_SANITIZE_SPECIAL_CHARS);

// ====== Datos Reflexión y Organización ======
$hora_inicio = filter_input(INPUT_POST, 'hora_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
$comentarios = trim(filter_input(INPUT_POST, 'comentarios', FILTER_UNSAFE_RAW));

// ======================= VALIDACIONES =======================

if (strlen($nombre) < 2 || strlen($nombre) > 100) {
    $errors['nombre'] = 'El nombre debe tener entre 2 y 100 caracteres.';
}

if (!$clase) {
    $errors['clase'] = 'La clase es obligatoria.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'El email no es válido.';
}

if (!is_numeric($edad) || $edad < 0 || $edad > 120) {
    $errors['edad'] = 'La edad debe ser un número entre 0 y 120.';
}

if (!$fecha_nacimiento) {
    $errors['fecha_nacimiento'] = 'La fecha de nacimiento es obligatoria.';
}

if (!$preferenciaPositivaInput) {
    $errors['preferenciaPositivaInput'] = 'La preferencia positiva es obligatoria.';
}

if (!$motivoPositivo) {
    $errors['motivoPositivo'] = 'El motivo de la preferencia positiva es obligatorio.';
}

if (!$color_favorito) {
    $errors['color_favorito'] = 'El color favorito es obligatorio.';
}

if (!$rol) {
    $errors['rol'] = 'El rol es obligatorio.';
}

if (!$lenguaje) {
    $errors['lenguaje'] = 'El lenguaje de programación es obligatorio.';
}

if (empty($temas)) {
    $errors['temas'] = 'Selecciona al menos un tema que te gustaría aprender.';
}

if (!is_numeric($git) || $git < 0 || $git > 10) {
    $errors['git'] = 'El nivel de Git debe ser un número entre 0 y 10.';
}

if (!$liderado) {
    $errors['liderado'] = 'El liderazgo de proyectos es obligatorio.';
}

if (!$comunicacion) {
    $errors['comunicacion'] = 'La comunicación es obligatoria.';
}

if (!$frecuencia_reuniones) {
    $errors['frecuencia_reuniones'] = 'La frecuencia de reuniones es obligatoria.';
}

if (!$trabajo_equipo) {
    $errors['trabajo_equipo'] = 'El trabajo en equipo es obligatorio.';
}

if (!$conflictos) {
    $errors['conflictos'] = 'La gestión de conflictos es obligatoria.';
}

if (!$gestion_tiempo) {
    $errors['gestion_tiempo'] = 'La gestión del tiempo es obligatoria.';
}

if (!is_numeric($estres) || $estres < 1 || $estres > 5) {
    $errors['estres'] = 'El nivel de estrés debe ser un número entre 1 y 5.';
}

if (!$entorno) {
    $errors['entorno'] = 'El entorno de trabajo es obligatorio.';
}

if (!$bienestar) {
    $errors['bienestar'] = 'El bienestar es obligatorio.';
}

if (!$hora_inicio) {
    $errors['hora_inicio'] = 'La hora del día productiva es obligatoria.';
}

// Si hay errores, los guardamos en sesión y redirigimos al formulario
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header('Location: sociograma.php');
    exit;
}

//Si todo es correcto, se guardan los datos en el array de respuestas
$respuestas = [
    'nombre' => $nombre,
    'clase' => $clase,
    'email' => $email,
    'edad' => $edad,
    'fecha_nacimiento' => $fecha_nacimiento,
    'preferenciaPositivaInput' => $preferenciaPositivaInput,
    'motivoPositivo' => $motivoPositivo,
    'color_favorito' => $color_favorito,
    'rol' => $rol,
    'lenguaje' => $lenguaje,
    'temas' => $temas,
    'git' => $git,
    'liderado' => $liderado,
    'comunicacion' => $comunicacion,
    'frecuencia_reuniones' => $frecuencia_reuniones,
    'trabajo_equipo' => $trabajo_equipo,
    'conflictos' => $conflictos,
    'gestion_tiempo' => $gestion_tiempo,
    'estres' => $estres,
    'entorno' => $entorno,
    'bienestar' => $bienestar,
    'hora_inicio' => $hora_inicio,
    'comentarios' => $comentarios
];

$archivo = 'data_form/respuestas.json';

//guardar en el archivo JSON
     
if (append_json($archivo, $respuestas)) {
    echo "Formulario enviado correctamente.<br>";
    echo "<a href='sociograma.php'>Volver al formulario</a>";
} else {
    echo "Error al guardar los datos. Comprueba permisos del archivo 'respuestas.json'.";
}
