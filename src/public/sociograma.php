<?php
session_start();

// Si no hay sesión activa, redirige al login
if (!isset($_SESSION['email'])) {
    header('Location: Login.php'); 
    exit;
}

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
    <title>Formulario Sociométrico</title>
</head>

<body>


<div class="top-bar">
    <h1 class="sociograma-titulo">Formulario Sociométrico</h1>

    <div class="cerrar-sesion-container">
        <form method="POST" action="Logout.php">
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>
</div>

<form method="POST" action="process.php">
    <!-- resto del formulario -->

    <!-- Información Personal -->
    <fieldset>
        <legend>Información Personal</legend>

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Introduce tu nombre" value="<?= htmlspecialchars(old_field('nombre', $old)) ?>">
        <?= field_error('nombre', $errors) ?>
        <br>

        <label>Clase:</label>
        <div class="radio-group">
        <input type="radio" id="daw1" name="clase" value="daw1" <?= old_field('clase', $old) === 'daw1' ? 'checked' : '' ?> required> <label for="daw1">DAW1</label>
        <input type="radio" id="daw2" name="clase" value="daw2" <?= old_field('clase', $old) === 'daw2' ? 'checked' : '' ?> required> <label for="daw2">DAW2</label>
        </div><?= field_error('clase', $errors) ?>
        
        <br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="Introduce tu email" value="<?= htmlspecialchars(old_field('email', $old)) ?>">
        <?= field_error('email', $errors) ?>
        <br>

        <label for="edad">Edad:</label>
        <input type="number" id="edad" name="edad" min="16" max="115" required placeholder="Introduce tu edad" value="<?= htmlspecialchars(old_field('edad', $old)) ?>">
        <?= field_error('edad', $errors) ?>
        <br>

        <label for="fecha_nacimiento">Fecha de nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
               value="<?= htmlspecialchars(old_field('fecha_nacimiento', $old)) ?>" required>
        <?= field_error('fecha_nacimiento', $errors) ?>
    </fieldset>

    <!-- Preferencias de Trabajo -->
    <fieldset>
        <legend>Preferencias de Trabajo</legend>

        <label for="preferenciaPositivaInput">Persona preferida:</label>
        <input type="text" id="preferenciaPositivaInput" name="preferenciaPositivaInput" required maxlength="75" placeholder="Con quién te sientes más cómodo trabajando" value="<?= htmlspecialchars(old_field('preferenciaPositivaInput', $old)) ?>">
        <?= field_error('preferenciaPositivaInput', $errors) ?>
        <br>

        <label for="motivoPositivo">Motivo:</label>
        <input type="text" id="motivoPositivo" name="motivoPositivo" required maxlength="300" placeholder="Por qué?" value="<?= htmlspecialchars(old_field('motivoPositivo', $old)) ?>">
        <?= field_error('motivoPositivo', $errors) ?>
        <br>

        <label for="color_favorito">Color favorito:</label>
        <input type="color" id="color_favorito" name="color_favorito"
               value="<?= htmlspecialchars(old_field('color_favorito', $old) ?: '#000000') ?>"
               class="color-box" required>
        <?= field_error('color_favorito', $errors) ?>
        <br>

    </fieldset>

    <!-- Habilidades -->
    <fieldset>
        <legend>Habilidades</legend>

        <label>Rol habitual en proyectos:</label>
        <div class="radio-group">
        <input type="radio" id="frontend" name="rol" value="frontend" <?= old_field('rol', $old) === 'frontend' ? 'checked' : '' ?> required> <label for="frontend">Frontend</label>
        <input type="radio" id="backend" name="rol" value="backend" <?= old_field('rol', $old) === 'backend' ? 'checked' : '' ?> required> <label for="backend">Backend</label>
        <input type="radio" id="fullstack" name="rol" value="fullstack" <?= old_field('rol', $old) === 'fullstack' ? 'checked' : '' ?> required> <label for="fullstack">Fullstack</label>
        </div><?= field_error('rol', $errors) ?>
        <br>

        <label>Lenguaje que más dominas:</label>
        <div class="radio-group">
        <?php $lang = old_field('lenguaje', $old); ?>
        <input type="radio" id="php" name="lenguaje" value="php" <?= $lang === 'php' ? 'checked' : '' ?> required> <label for="php">PHP</label>
        <input type="radio" id="javascript" name="lenguaje" value="javascript" <?= $lang === 'javascript' ? 'checked' : '' ?> required> <label for="javascript">JavaScript</label>
        <input type="radio" id="python" name="lenguaje" value="python" <?= $lang === 'python' ? 'checked' : '' ?> required> <label for="python">Python</label>
        <input type="radio" id="java" name="lenguaje" value="java" <?= $lang === 'java' ? 'checked' : '' ?> required> <label for="java">Java</label>
        <input type="radio" id="otro" name="lenguaje" value="otro" <?= $lang === 'otro' ? 'checked' : '' ?> required> <label for="otro">Otro</label>
        </div><?= field_error('lenguaje', $errors) ?>
        <br>

        <label>Temas que te gustaría aprender:</label><br>
<?php $temas = (array)(old_field('temas', $old) ?? []); ?>
<div class="radio-group">
<input type="checkbox" id="seguridad" name="temas[]" value="seguridad" 
    <?= in_array('seguridad', $temas) ? 'checked' : '' ?>>
<label for="seguridad">Ciberseguridad</label>

<input type="checkbox" id="ia" name="temas[]" value="ia" 
    <?= in_array('ia', $temas) ? 'checked' : '' ?>>
<label for="ia">Inteligencia Artificial</label>

<input type="checkbox" id="ux" name="temas[]" value="ux" 
    <?= in_array('ux', $temas) ? 'checked' : '' ?>>
<label for="ux">Diseño UX</label>
</div>
<?= field_error('temas', $errors) ?>

        <label for="git">Nivel de dominio de Git:</label>
        <input type="range" id="git" name="git" min="0" max="10" step="1" value="<?= htmlspecialchars(old_field('git', $old)) ?>" required>
        <?= field_error('git', $errors) ?>
        <br>

        <label>Has liderado algún proyecto:</label>
        <div class="radio-group">
        <input type="radio" id="lideradoSi" name="liderado" value="lideradoSi" <?= old_field('liderado', $old) === 'lideradoSi' ? 'checked' : '' ?> required> <label for="lideradoSi">Sí</label>
        <input type="radio" id="lideradoNo" name="liderado" value="lideradoNo" <?= old_field('liderado', $old) === 'lideradoNo' ? 'checked' : '' ?> required> <label for="lideradoNo">No</label>
        </div><?= field_error('liderado', $errors) ?>
    </fieldset>

    <!-- Dinámica de Trabajo -->
    <fieldset>
        <legend>Dinámica de Trabajo</legend>

        <label>Tipo de comunicación que prefieres:</label>
        <div class="radio-group">
        <input type="radio" id="sincrona" name="comunicacion" value="sincrona" <?= old_field('comunicacion', $old) === 'sincrona' ? 'checked' : '' ?> required> <label for="sincrona">Síncrona</label>
        <input type="radio" id="asincrona" name="comunicacion" value="asincrona" <?= old_field('comunicacion', $old) === 'asincrona' ? 'checked' : '' ?> required> <label for="asincrona">Asíncrona</label>
        <input type="radio" id="mixta" name="comunicacion" value="mixta" <?= old_field('comunicacion', $old) === 'mixta' ? 'checked' : '' ?> required> <label for="mixta">Mixta</label>
        </div><?= field_error('comunicacion', $errors) ?>
        <br>

        <label for="frecuencia_reuniones">Frecuencia ideal de reuniones:</label>
        <select name="frecuencia_reuniones" required>
            <option value="">Selecciona</option>
            <option value="diaria" <?= old_field('frecuencia_reuniones', $old) === 'diaria' ? 'selected' : '' ?>>Diaria</option>
            <option value="semanal" <?= old_field('frecuencia_reuniones', $old) === 'semanal' ? 'selected' : '' ?>>Semanal</option>
            <option value="quincenal" <?= old_field('frecuencia_reuniones', $old) === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
        </select>
        <?= field_error('frecuencia_reuniones', $errors) ?>
        <br>

        <label>Prefieres trabajar en equipo o individual:</label>
        <div class="radio-group">
        <input type="radio" id="equipo" name="trabajo_equipo" value="equipo" <?= old_field('trabajo_equipo', $old) === 'equipo' ? 'checked' : '' ?> required> <label for="equipo">En equipo</label>
        <input type="radio" id="individual" name="trabajo_equipo" value="individual" <?= old_field('trabajo_equipo', $old) === 'individual' ? 'checked' : '' ?> required> <label for="individual">Individual</label>
        </div><?= field_error('trabajo_equipo', $errors) ?>
        <br>

        <label>Te consideras bueno gestionando conflictos:</label>
        <div class="radio-group">
        <input type="radio" id="conflictosSi" name="conflictos" value="conflictosSi" <?= old_field('conflictos', $old) === 'conflictosSi' ? 'checked' : '' ?> required> <label for="conflictosSi">Sí</label>
        <input type="radio" id="conflictosNo" name="conflictos" value="conflictosNo" <?= old_field('conflictos', $old) === 'conflictosNo' ? 'checked' : '' ?> required> <label for="conflictosNo">No</label>
        </div><?= field_error('conflictos', $errors) ?>
    </fieldset>

    <!-- Organización y Bienestar -->
    <fieldset>
        <legend>Organización y Bienestar</legend>

        <label for="gestion_tiempo">Nivel de gestión del tiempo:</label>
        <select id="gestion_tiempo" name="gestion_tiempo" required>
            <option value="">Selecciona</option>
            <option value="baja" <?= old_field('gestion_tiempo', $old) === 'baja' ? 'selected' : '' ?>>Baja</option>
            <option value="media" <?= old_field('gestion_tiempo', $old) === 'media' ? 'selected' : '' ?>>Media</option>
            <option value="alta" <?= old_field('gestion_tiempo', $old) === 'alta' ? 'selected' : '' ?>>Alta</option>
        </select>
        <?= field_error('gestion_tiempo', $errors) ?>
        <br>

        <label for="estres">Nivel de estrés en proyectos (1-5):</label>
        <input type="number" id="estres" name="estres" min="1" max="5" required value="<?= htmlspecialchars(old_field('estres', $old)) ?>">
        <?= field_error('estres', $errors) ?>
        <br>

        <label>Preferencia de entorno de trabajo:</label>
        <div class="radio-group">
        <input type="radio" id="entornoSilencio" name="entorno" value="silencio" <?= old_field('entorno', $old) === 'silencio' ? 'checked' : '' ?> required> <label for="entornoSilencio">Silencio</label>
        <input type="radio" id="entornoRuido" name="entorno" value="ruido_blanco" <?= old_field('entorno', $old) === 'ruido_blanco' ? 'checked' : '' ?> required> <label for="entornoRuido">Ruido blanco</label>
        <input type="radio" id="entornoMusica" name="entorno" value="musica" <?= old_field('entorno', $old) === 'musica' ? 'checked' : '' ?> required> <label for="entornoMusica">Música</label>
        </div><?= field_error('entorno', $errors) ?>
        <br>

        <label>Te sientes cómodo con la carga de trabajo actual:</label>
        <div class="radio-group">
        <input type="radio" id="bienestarSi" name="bienestar" value="bienestarSi" <?= old_field('bienestar', $old) === 'bienestarSi' ? 'checked' : '' ?> required> <label for="bienestarSi">Sí</label>
        <input type="radio" id="bienestarNo" name="bienestar" value="bienestarNo" <?= old_field('bienestar', $old) === 'bienestarNo' ? 'checked' : '' ?> required> <label for="bienestarNo">No</label>
        </div><?= field_error('bienestar', $errors) ?>
    </fieldset>

    <fieldset>
        <legend>Reflexión y Organización</legend>

        <label for="hora_inicio">Hora del día en la que te sientes más productivo:</label>
        <input type="time" id="hora_inicio" name="hora_inicio" 
               value="<?= htmlspecialchars(old_field('hora_inicio', $old)) ?>" required>
        <?= field_error('hora_inicio', $errors) ?>
        <br>

        <label for="comentarios">Comentarios adicionales (opcional):</label><br>
        <textarea id="comentarios" name="comentarios" rows="4" cols="50" placeholder="Añade tus observaciones o sugerencias..."><?= htmlspecialchars(old_field('comentarios', $old)) ?></textarea>
        <?= field_error('comentarios', $errors) ?>
    </fieldset>

    <br>
    <input type="submit" value="Enviar">

</form>



<?php include 'includes/footer.php'; ?>

</body>
</html>
