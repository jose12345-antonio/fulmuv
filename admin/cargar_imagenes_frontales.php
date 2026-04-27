<?php
ini_set('MAX_EXECUTION_TIME', '-1');
header("Content-Type: application/json; charset=utf-8");

$uploaddir = "files/";
$fechaHoraActual = date("Ymd_His");

function guardarArchivo($archivo, $prefijo, $uploaddir, $fechaHoraActual) {
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreSinExtension = pathinfo($archivo['name'], PATHINFO_FILENAME);
    $nuevoNombre = "{$prefijo}_{$fechaHoraActual}." . $extension;
    $rutaFinal = $uploaddir . $nuevoNombre;

    if (move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
        return $rutaFinal;
    } else {
        return false;
    }
}

if (isset($_FILES['img_frontal']) && isset($_FILES['img_posterior'])) {
    $rutaFrontal = guardarArchivo($_FILES['img_frontal'], 'img_frontal', $uploaddir, $fechaHoraActual);
    $rutaPosterior = guardarArchivo($_FILES['img_posterior'], 'img_posterior', $uploaddir, $fechaHoraActual);

    if ($rutaFrontal && $rutaPosterior) {
        echo json_encode([
            "response" => "success",
            "data" => [
                "img_frontal" => $rutaFrontal,
                "img_posterior" => $rutaPosterior
            ]
        ]);
    } else {
        echo json_encode([
            "response" => "error",
            "error" => "Error al mover las imágenes al directorio destino."
        ]);
    }
} else {
    echo json_encode([
        "response" => "error",
        "error" => "No se recibieron ambas imágenes requeridas (img_frontal y img_posterior)."
    ]);
}