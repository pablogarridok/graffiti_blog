<?php
// Cargar un archivo JSON y devolverlo como array
function load_json($path)
{
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Guardar un array en un archivo JSON (sobrescribe todo)
function save_json($path, $data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json) !== false;
}

// Guardar una nueva respuesta sin borrar las anteriores
function append_json($path, $newData)
{
    $existing = load_json($path);        
    $existing[] = $newData;              
    return save_json($path, $existing);  
}

//Rehidratacion
function old_field($name, $source = []) {
    if (isset($source[$name])) {
    return $source[$name]; // devolvemos lo que escribió el usuario
    }
    return ""; // si no había nada, devolvemos vacío
}   
function field_error($name, $errors = []){
    if (isset($errors[$name])) {
        return "<p style='color:red'>" . $errors[$name] . "</p>";
    }
    return ""; // si no hay error, no mostramos nada
}