<?php
ini_set('MAX_EXECUTION_TIME', '-1');
header("Content-Type: application/json; charset=utf-8");

$uploaddir = "files/";

$respuesta = array(); // Array para almacenar el grupo de rutas y descripcion
$operacionExitosa = true; // Bandera para verificar si la operación fue exitosa
$rutasTmp = array(); //

if (isset($_FILES['archivos'])) {
    foreach ($_FILES['archivos']['tmp_name'] as $keyTmp => $tmp_name) {
        $ubicacionTemporal = $_FILES["archivos"]["tmp_name"][$keyTmp];
        $nombreArchivo = $_FILES['archivos']['name'][$keyTmp];

        // Obtener la extensión del archivo
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
        
        // Obtener el nombre del archivo sin la extensión
        $nombreSinExtension = pathinfo($nombreArchivo, PATHINFO_FILENAME);

        // Obtener la fecha y hora actual en el formato deseado
        $fechaHoraActual = date("Ymd_His");

        // Crear el nuevo nombre del archivo
        $nuevoNombre = sprintf("%s_%s.%s", $nombreSinExtension, $fechaHoraActual, $extension);

        $ruta = $uploaddir . $nuevoNombre; // Generar una ruta única
        $rutasTmp[] = array(
            'ruta' => $ruta,
        );
        if (move_uploaded_file($ubicacionTemporal, $ruta)) {
            if ($extension == "pdf") {
                $respuesta["archivos"][] = [
                    "archivo" => $ruta,
                    "tipo" => "ficha_tecnica"
                ];
            } else {
                $respuesta["archivos"][] = [
                    "archivo" => $ruta,
                    "tipo" => "imagen"
                ];
            }
        } else {
            // Manejo de errores si no se pudo mover la imagen
            $respuesta[] = array(
                'error' => 'Error al subir el archivo ' . $nombreArchivo,
            );
            $operacionExitosa = false;
        }
    }
    if ($operacionExitosa) {
        // Si la operación fue exitosa para todas las imágenes, se devuelve la respuesta
        echo json_encode(array("response" => "success", "data" => $respuesta));
    } else {
        foreach ($rutasTmp as $ruta) {
            unlink($ruta['ruta']);
        }
        // Si hubo algún error, se muestra un mensaje adecuado
        echo json_encode(array('error' => 'Ocurrió un error durante la operación.'));
    }
} else {
    echo json_encode(array('error' => 'No se recibieron archivos.'));
}
