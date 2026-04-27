<?php
ini_set('MAX_EXECUTION_TIME', '-1');
header("Content-Type: application/json; charset=utf-8");

$uploaddir = "files/";
$fechaHoraActual = date("Ymd_His");

function guardarArchivo($archivo, $prefijo, $uploaddir, $fechaHoraActual) {
    // Validar que no haya error en la subida desde PHP
    if (!isset($archivo['error']) || is_array($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nuevoNombre = "{$prefijo}_{$fechaHoraActual}." . $extension;
    $rutaFinal = $uploaddir . $nuevoNombre;

    if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
        return $rutaFinal;
    } else {
        return false;
    }
}

$data = [];
$huboError = false;

// 1. Procesar Frontal si viene en la petición
if (isset($_FILES['img_frontal'])) {
    $rutaFrontal = guardarArchivo($_FILES['img_frontal'], 'img_frontal', $uploaddir, $fechaHoraActual);
    if ($rutaFrontal) {
        $data['img_frontal'] = $rutaFrontal;
    } else {
        $huboError = true;
    }
}

// 2. Procesar Posterior si viene en la petición
if (isset($_FILES['img_posterior'])) {
    $rutaPosterior = guardarArchivo($_FILES['img_posterior'], 'img_posterior', $uploaddir, $fechaHoraActual);
    if ($rutaPosterior) {
        $data['img_posterior'] = $rutaPosterior;
    } else {
        $huboError = true;
    }
}

// 3. Evaluar respuestas
if (empty($data) && !$huboError) {
    echo json_encode([
        "response" => "error",
        "error" => "No se recibió ninguna imagen (img_frontal o img_posterior)."
    ]);
} elseif (empty($data) && $huboError) {
    echo json_encode([
        "response" => "error",
        "error" => "Error al mover las imágenes al directorio destino."
    ]);
} else {
    // Éxito: Se subió al menos una imagen correctamente
    echo json_encode([
        "response" => "success",
        "data" => $data 
    ]);
}
?>