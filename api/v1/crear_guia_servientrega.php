<?php
$url = 'https://181.39.87.158:8021/api/guiawebs/';

// Construye el JSON (no concatenes strings)
$payload = [
    "id_tipo_logistica"      => 1,
    "detalle_envio_1"        => "",
    "detalle_envio_2"        => "",
    "detalle_envio_3"        => "",
    "id_ciudad_origen"       => 1,
    "id_ciudad_destino"      => 42,

    "id_destinatario_ne_cl"  => "001dest",            // usa uno válido
    "razon_social_desti_ne"  => "pruebASDa de api s.a",
    "nombre_destinatario_ne" => "gustavo andres",
    "apellido_destinat_ne"   => "tecnologia matriz",  // ← CORREGIDO (sin ‘r’ extra)
    "direccion1_destinat_ne" => "panama 306 y thomas y martinez",
    "sector_destinat_ne"     => "",
    "telefono1_destinat_ne"  => "3732000 ext 4732",
    "telefono2_destinat_ne"  => "",
    "codigo_postal_dest_ne"  => "",

    "id_remitente_cl"        => "001remi",
    "razon_social_remite"    => "servientrega ecuador s.a",
    "nombre_remitente"       => "gustavo ",
    "apellido_remite"        => "villalba lopez",
    "direccion1_remite"      => "panama 306 y thomas y martinez",
    "sector_remite"          => "",
    "telefono1_remite"       => "123156",
    "telefono2_remite"       => "",
    "codigo_postal_remi"     => "",

    "id_producto"            => 2,
    "contenido"              => "laptop",
    "numero_piezas"          => 1,
    "valor_mercancia"        => 0,
    "valor_asegurado"        => 0,
    "largo"                  => 0,
    "ancho"                  => 0,
    "alto"                   => 0,
    "peso_fisico"            => 0.5,

    "login_creacion"         => "changethemove.sas",
    "password"               => "123456"
];

$json = json_encode($payload, JSON_UNESCAPED_UNICODE);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $json,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: PostmanRuntime/7.40.0',      // imita Postman
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'Expect:',                                // evita 100-continue
        'Content-Length: ' . strlen($json)
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_ENCODING       => '',
    CURLOPT_HEADER         => true,             // para separar headers/body
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,

    // ⚠️ Como usas HTTPS sobre IP, el cert no matchea el host → desactiva verificación SOLO en pruebas.
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,

    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
]);

curl_setopt($ch, CURLINFO_HEADER_OUT, true);

$raw   = curl_exec($ch);
$errno = curl_errno($ch);
$err   = curl_error($ch);
$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$hsz   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$reqH  = curl_getinfo($ch, CURLINFO_HEADER_OUT);
curl_close($ch);

if ($raw === false) {
    // → Aquí verás por qué devolvía bool(false)
    var_dump([
        'error'      => true,
        'msj'        => 'cURL error',
        'curl_errno' => $errno,
        'curl_error' => $err,
        'request'    => $reqH
    ]);
    exit;
}

$respHeaders = substr($raw, 0, $hsz);
$body        = substr($raw, $hsz);

$decoded = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    var_dump([
        'error'   => true,
        'msj'     => 'Respuesta no JSON del proveedor',
        'http'    => $code,
        'headers' => $respHeaders,
        'raw'     => $body,
        'request' => $reqH
    ]);
    exit;
}

var_dump($decoded);
