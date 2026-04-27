<?php
ini_set('MAX_EXECUTION_TIME', '-1');
header("Content-Type: application/json; charset=utf-8");

$uploaddir = "pagos_ordenes/";
$respuesta = array();
$operacionExitosa = true;
$rutasTmp = array();

if (isset($_FILES['archivos'])) {
    foreach ($_FILES['archivos']['tmp_name'] as $keyTmp => $tmp_name) {
        $ubicacionTemporal = $_FILES["archivos"]["tmp_name"][$keyTmp];
        $nombreArchivo = $_FILES['archivos']['name'][$keyTmp];

        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
        $nombreSinExtension = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $fechaHoraActual = date("Ymd_His");
        $nuevoNombre = sprintf("%s_%s.%s", $nombreSinExtension, $fechaHoraActual, $extension);

        $ruta = $uploaddir . $nuevoNombre;
        $rutasTmp[] = array('ruta' => $ruta);

        if (move_uploaded_file($ubicacionTemporal, $ruta)) {
            $respuesta["archivos"][] = [
                "archivo" => $ruta,
                "tipo" => strtolower($extension) === "pdf" ? "ficha_tecnica" : "imagen"
            ];
        } else {
            $respuesta[] = ['error' => 'Error al subir el archivo ' . $nombreArchivo];
            $operacionExitosa = false;
        }
    }

    if ($operacionExitosa) {
        echo json_encode(["response" => "success", "data" => $respuesta]);
    } else {
        foreach ($rutasTmp as $ruta) {
            if (file_exists($ruta['ruta'])) {
                unlink($ruta['ruta']);
            }
        }
        echo json_encode(['error' => 'Ocurrió un error durante la operación.']);
    }
} else {
    echo json_encode(['error' => 'No se recibieron archivos.']);
}
