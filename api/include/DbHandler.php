<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class DbHandler
{
    private $conn;
    private $publicCreatorActiveCache = [];

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once 'PassHash.php';


        require_once 'PHPMailer/src/Exception.php';
        require_once 'PHPMailer/src/PHPMailer.php';
        require_once 'PHPMailer/src/SMTP.php';
        date_default_timezone_set('America/Guayaquil');
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
        $this->conn->set_charset('utf8mb4');
        $this->conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private function nuveiEnv()
    {
        $env = strtolower(trim((string) NUVEI_ENV));
        return $env === 'prod' ? 'prod' : 'int';
    }

    private function nuveiBaseUrl()
    {
        return $this->nuveiEnv() === 'prod'
            ? 'https://secure.safecharge.com/ppp/api/v1/'
            : 'https://ppp-test.nuvei.com/ppp/api/v1/';
    }

    public function nuveiIsConfigured()
    {
        return NUVEI_MERCHANT_ID !== ''
            && NUVEI_MERCHANT_SITE_ID !== ''
            && NUVEI_SECRET_KEY !== '';
    }

    public function nuveiPublicConfig()
    {
        return [
            "configured" => $this->nuveiIsConfigured(),
            "env" => $this->nuveiEnv(),
            "merchantId" => NUVEI_MERCHANT_ID,
            "merchantSiteId" => NUVEI_MERCHANT_SITE_ID
        ];
    }

    private function nuveiTimestamp()
    {
        return date('YmdHis');
    }

    private function ensureEmpresaBillingColumns()
    {
        $columns = [
            "telefono_facturacion" => "ALTER TABLE empresas ADD COLUMN telefono_facturacion VARCHAR(50) NULL DEFAULT NULL AFTER direccion_facturacion",
            "correo_facturacion" => "ALTER TABLE empresas ADD COLUMN correo_facturacion VARCHAR(180) NULL DEFAULT NULL AFTER telefono_facturacion"
        ];

        foreach ($columns as $column => $sql) {
            $safeColumn = $this->conn->real_escape_string($column);
            $exists = $this->conn->query("SHOW COLUMNS FROM empresas LIKE '{$safeColumn}'");
            if ($exists && $exists->num_rows > 0) {
                $exists->close();
                continue;
            }
            if ($exists) {
                $exists->close();
            }
            if (!$this->conn->query($sql)) {
                return [
                    "error" => true,
                    "column" => $column,
                    "sql_error" => $this->conn->error,
                    "sql" => $sql
                ];
            }
        }

        return ["error" => false];
    }

    private function ensureFacturaBillingColumns()
    {
        $columns = [
            "telefono_facturacion" => "ALTER TABLE facturas ADD COLUMN telefono_facturacion VARCHAR(50) NULL DEFAULT NULL AFTER direccion",
            "correo_facturacion" => "ALTER TABLE facturas ADD COLUMN correo_facturacion VARCHAR(180) NULL DEFAULT NULL AFTER telefono_facturacion"
        ];

        foreach ($columns as $column => $sql) {
            $safeColumn = $this->conn->real_escape_string($column);
            $exists = $this->conn->query("SHOW COLUMNS FROM facturas LIKE '{$safeColumn}'");
            if ($exists && $exists->num_rows > 0) {
                $exists->close();
                continue;
            }
            if ($exists) {
                $exists->close();
            }
            if (!$this->conn->query($sql)) {
                return [
                    "error" => true,
                    "column" => $column,
                    "sql_error" => $this->conn->error,
                    "sql" => $sql
                ];
            }
        }

        return ["error" => false];
    }

    private function tableHasColumn($table, $column)
    {
        $safeTable = $this->conn->real_escape_string($table);
        $safeColumn = $this->conn->real_escape_string($column);
        $result = $this->conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
        if (!$result) {
            return false;
        }
        $exists = $result->num_rows > 0;
        $result->close();
        return $exists;
    }

    private function updateEmpresaBillingData($id_empresa, array $facturacion)
    {
        $updates = [];
        $params = [];
        $types = '';

        $map = [
            'razon_social' => 'razon_social',
            'tipo_identificacion' => 'tipo_identificacion',
            'cedula_ruc' => 'cedula_ruc',
            'direccion_facturacion' => 'direccion_facturacion',
            'telefono_facturacion' => 'telefono_facturacion',
            'correo_facturacion' => 'correo_facturacion'
        ];

        foreach ($map as $column => $inputKey) {
            if (!array_key_exists($inputKey, $facturacion)) {
                continue;
            }
            if (!$this->tableHasColumn('empresas', $column)) {
                continue;
            }

            $value = trim((string)($facturacion[$inputKey] ?? ''));
            if ($value === '') {
                continue;
            }

            $updates[] = "{$column} = ?";
            $params[] = $value;
            $types .= 's';
        }

        if (empty($updates)) {
            return true;
        }

        $sql = "UPDATE empresas SET " . implode(', ', $updates) . " WHERE id_empresa = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("No se pudo preparar la actualizacion de facturacion: " . $this->conn->error);
        }

        $types .= 'i';
        $params[] = (int)$id_empresa;
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $errorStmt = $stmt->error;
        $stmt->close();

        if (!$ok) {
            throw new Exception("No se pudo actualizar la facturacion de la empresa: " . $errorStmt);
        }

        return true;
    }

    private function contificoRequestJson($method, $url, $payload = null)
    {
        $curl = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Authorization: ELPAz3khSjp7kh4Dqnu9kjK7D4R7WEC8bBD2k2yXcrU'
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers
        ]);

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);

        if ($raw === false) {
            return [
                "error" => true,
                "http_code" => $httpCode,
                "msg" => "No fue posible conectar con Contifico: " . ($curlError ?: ("cURL error " . $curlErrno))
            ];
        }

        $decoded = json_decode($raw, true);
        return [
            "error" => false,
            "http_code" => $httpCode,
            "raw" => $raw,
            "data" => $decoded
        ];
    }

    private function syncPersonaContificoCliente(array $facturacion)
    {
        $identificacion = trim((string)($facturacion['cedula_ruc'] ?? ''));
        if ($identificacion === '') {
            return [
                "error" => true,
                "msg" => "No se pudo sincronizar la persona en Contifico: falta identificacion."
            ];
        }

        $tipoId = strtolower(trim((string)($facturacion['tipo_identificacion'] ?? 'cedula')));
        $tipoPersona = $tipoId === 'ruc' ? 'J' : ($tipoId === 'pasaporte' ? 'P' : 'N');
        $personaPayload = [
            "tipo" => $tipoPersona,
            "personaasociada_id" => null,
            "nombre_comercial" => trim((string)($facturacion['razon_social'] ?? '')),
            "telefonos" => trim((string)($facturacion['telefono_facturacion'] ?? '')),
            "ruc" => $tipoId === 'ruc' ? $identificacion : "",
            "razon_social" => trim((string)($facturacion['razon_social'] ?? '')),
            "direccion" => trim((string)($facturacion['direccion_facturacion'] ?? '')),
            "es_extranjero" => false,
            "porcentaje_descuento" => "0",
            "es_cliente" => true,
            "es_empleado" => false,
            "email" => trim((string)($facturacion['correo_facturacion'] ?? '')),
            "cedula" => $tipoId === 'ruc' ? "" : $identificacion,
            "placa" => "",
            "es_vendedor" => false,
            "es_proveedor" => false,
            "adicional1_cliente" => null,
            "adicional2_cliente" => null,
            "adicional3_cliente" => null,
            "adicional4_cliente" => null,
            "adicional1_proveedor" => null,
            "adicional2_proveedor" => null,
            "adicional3_proveedor" => null,
            "adicional4_proveedor" => null
        ];

        $lookupUrl = 'https://api.contifico.com/sistema/api/v1/persona/?identificacion=' . rawurlencode($identificacion);
        $lookup = $this->contificoRequestJson('GET', $lookupUrl);
        if (!empty($lookup['error'])) {
            return $lookup;
        }

        $personaActual = null;
        if (($lookup['http_code'] >= 200 && $lookup['http_code'] < 300) && is_array($lookup['data']) && !empty($lookup['data'][0])) {
            $personaActual = $lookup['data'][0];
        }

        $posToken = '58799fc1-67a9-4ef9-b3dc-1add00a8288c';
        $debugPersona = [
            "tipo_identificacion_recibido" => $tipoId,
            "cedula_ruc_recibido" => $identificacion,
            "tipo_persona_enviado" => $tipoPersona,
            "ruc_enviado" => $personaPayload["ruc"],
            "cedula_enviada" => $personaPayload["cedula"],
            "modo_sync" => "create"
        ];
        if ($personaActual && !empty($personaActual['id'])) {
            $tipoActual = strtoupper(trim((string)($personaActual['tipo'] ?? '')));
            $rucActual = trim((string)($personaActual['ruc'] ?? ''));
            $cedulaActual = trim((string)($personaActual['cedula'] ?? ''));
            $hayConflictoNaturalAJuridica = $tipoId === 'ruc'
                && $tipoActual !== ''
                && $tipoActual !== 'J'
                && ($cedulaActual !== '' || $rucActual === '');

            $debugPersona["persona_actual_id"] = $personaActual['id'];
            $debugPersona["persona_actual_tipo"] = $tipoActual;
            $debugPersona["persona_actual_ruc"] = $rucActual;
            $debugPersona["persona_actual_cedula"] = $cedulaActual;

            if (!$hayConflictoNaturalAJuridica) {
                $personaPayload['id'] = $personaActual['id'];
                $updateUrl = 'https://api.contifico.com/sistema/api/v1/persona/?pos=' . rawurlencode($posToken);
                $debugPersona["modo_sync"] = "update";
                $updated = $this->contificoRequestJson('PUT', $updateUrl, $personaPayload);
                if (!empty($updated['error']) || $updated['http_code'] < 200 || $updated['http_code'] >= 300) {
                    return [
                        "error" => true,
                        "msg" => "Contifico no permitio actualizar la persona del cliente.",
                        "http_code" => $updated['http_code'] ?? null,
                        "raw" => $updated['raw'] ?? null,
                        "debug" => $debugPersona
                    ];
                }
                return [
                    "error" => false,
                    "persona_id" => $personaActual['id'],
                    "debug" => $debugPersona
                ];
            }

            $debugPersona["modo_sync"] = "update_contact_conflict_ruc";
            $personaPayloadContacto = $personaPayload;
            $personaPayloadContacto['id'] = $personaActual['id'];
            $personaPayloadContacto['tipo'] = $tipoActual !== '' ? $tipoActual : 'N';
            $personaPayloadContacto['ruc'] = $rucActual;
            $personaPayloadContacto['cedula'] = $cedulaActual;

            $updateUrl = 'https://api.contifico.com/sistema/api/v1/persona/?pos=' . rawurlencode($posToken);
            $updated = $this->contificoRequestJson('PUT', $updateUrl, $personaPayloadContacto);
            if (empty($updated['error']) && $updated['http_code'] >= 200 && $updated['http_code'] < 300) {
                $debugPersona["modo_sync"] = "update_contact_conflict_ruc_ok";
                return [
                    "error" => false,
                    "persona_id" => $personaActual['id'],
                    "debug" => $debugPersona,
                    "warning" => "La persona ya existia en Contifico con otra identidad; se actualizaron solo los datos de contacto."
                ];
            }

            $debugPersona["modo_sync"] = "create_conflict_ruc";
        }

        $createUrl = 'https://api.contifico.com/sistema/api/v1/persona/?pos=' . rawurlencode($posToken);
        $created = $this->contificoRequestJson('POST', $createUrl, $personaPayload);
        if (!empty($created['error']) || $created['http_code'] < 200 || $created['http_code'] >= 300) {
            $raw = (string)($created['raw'] ?? '');
            $httpCode = (int)($created['http_code'] ?? 0);
            $personaYaExiste = $httpCode === 409
                || stripos($raw, 'Persona ya existe') !== false
                || stripos($raw, 'ya existe') !== false;

            if ($personaYaExiste) {
                $debugPersona["modo_sync"] = "skip_existing_persona";
                return [
                    "error" => false,
                    "persona_id" => $personaActual['id'] ?? null,
                    "debug" => $debugPersona,
                    "warning" => "La persona ya existia en Contifico; se continua con la emision del documento usando los datos enviados en la factura."
                ];
            }

            return [
                "error" => true,
                "msg" => "Contifico no permitio crear la persona del cliente.",
                "http_code" => $created['http_code'] ?? null,
                "raw" => $created['raw'] ?? null,
                "debug" => $debugPersona
            ];
        }

        return [
            "error" => false,
            "persona_id" => $created['data']['id'] ?? null,
            "debug" => $debugPersona
        ];
    }

    private function nuveiHash(array $parts)
    {
        return hash('sha256', implode('', $parts));
    }

    private function nuveiPost($endpoint, array $payload)
    {
        if (!$this->nuveiIsConfigured()) {
            return [
                "status" => "ERROR",
                "reason" => "La configuración de Nuvei no está completa en el servidor."
            ];
        }

        $ch = curl_init($this->nuveiBaseUrl() . ltrim($endpoint, '/'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $result === null) {
            return [
                "status" => "ERROR",
                "reason" => $curlError !== '' ? $curlError : 'No se recibió respuesta desde Nuvei.'
            ];
        }

        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "status" => "ERROR",
                "reason" => 'La respuesta de Nuvei no se pudo interpretar.',
                "raw" => $result
            ];
        }

        return $decoded;
    }

    public function nuveiOpenOrder($amount, $currency, $clientUniqueId, $clientRequestId, $userTokenId = '', $billing = [])
    {
        $timeStamp = $this->nuveiTimestamp();
        $amountFormatted = number_format((float)$amount, 2, '.', '');

        $payload = [
            "merchantId" => NUVEI_MERCHANT_ID,
            "merchantSiteId" => NUVEI_MERCHANT_SITE_ID,
            "clientUniqueId" => (string)$clientUniqueId,
            "clientRequestId" => (string)$clientRequestId,
            "currency" => (string)$currency,
            "amount" => $amountFormatted,
            "timeStamp" => $timeStamp,
            "checksum" => $this->nuveiHash([
                NUVEI_MERCHANT_ID,
                NUVEI_MERCHANT_SITE_ID,
                (string)$clientRequestId,
                $amountFormatted,
                (string)$currency,
                $timeStamp,
                NUVEI_SECRET_KEY
            ])
        ];

        if ($userTokenId !== '') {
            $payload["userTokenId"] = (string)$userTokenId;
        }

        if (!empty($billing["email"])) {
            $payload["billingAddress"] = [
                "email" => (string)$billing["email"],
                "country" => (string)($billing["country"] ?? 'EC')
            ];
        }

        if (!empty($billing["firstName"]) || !empty($billing["lastName"])) {
            if (!isset($payload["billingAddress"])) {
                $payload["billingAddress"] = [];
            }
            if (!empty($billing["firstName"])) {
                $payload["billingAddress"]["firstName"] = (string)$billing["firstName"];
            }
            if (!empty($billing["lastName"])) {
                $payload["billingAddress"]["lastName"] = (string)$billing["lastName"];
            }
        }

        return $this->nuveiPost('openOrder.do', $payload);
    }

    public function nuveiGetPaymentStatus($sessionToken)
    {
        return $this->nuveiPost('getPaymentStatus.do', [
            "sessionToken" => (string)$sessionToken
        ]);
    }

    /**
     *ADMINISTRADORES
     */


    /*TRAER CLAVE DEL ADMIN*/
    public function adminlogin($usuario, $clave)
    {

        $stmt = $this->conn->prepare("SELECT pass FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result(); //Devuelve un objeto de resultados almacenado en buffer o false si ocurrió un error.
        if ($stmt->num_rows > 0) { //Obtiene el número de filas de un resultado
            $stmt->fetch();
            $stmt->close();

            if (PassHash::check_password($password_hash, $clave)) {
                return 2;
            } else {
                return 1;
            }
        } else {
            $stmt->close();
            return 0;
        }
    }
    /*TRAER CLAVE DEL ADMIN*/


    /*TRAER CLAVE DEL CLIENTE*/
    public function clientelogin($usuario, $clave)
    {

        $stmt = $this->conn->prepare("SELECT password FROM clientes WHERE correo = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result(); //Devuelve un objeto de resultados almacenado en buffer o false si ocurrió un error.
        if ($stmt->num_rows > 0) { //Obtiene el número de filas de un resultado
            $stmt->fetch();
            $stmt->close();

            if (PassHash::check_password($password_hash, $clave)) {
                return 2;
            } else {
                return 1;
            }
        } else {
            $stmt->close();
            return 0;
        }
    }
    /*TRAER CLAVE DEL CLIENTE*/


    /* RESETEAR PASS CORREO */
    public function resetearPass($id_usuario)
    {

        $correo_contenedor = $this->getContenedor();

        $stmt = $this->conn->prepare("SELECT * FROM usuarios where id_usuario = ?");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {

            $code = $this->generateRandomString();
            $password_hash = PassHash::hash($code);

            $stm = $this->conn->prepare("UPDATE usuarios SET pass = ? WHERE correo = ?");
            $stm->bind_param("ss", $password_hash, $row["correo"]);
            $resul = $stm->execute();
            $stm->close();

            $mail = new PHPMailer();
            $mail->IsSMTP(); // enable SMTP
            $mail->SMTPAuth = true; // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->Username = 'bonsaidev@bonsai.com.ec';
            $mail->Password = 'ykdvtvcizzgjyfhy';
            $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

            $mail->IsHTML(true);
            $mail->Subject = utf8_decode('Notification');
            $mail->AddAddress($row["correo"]);

            $mail->Body = utf8_decode('
            <!DOCTYPE html>
            <html lang="en">
                <body style="margin:0px;">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
                    <center>
                        <div style="background:#00686f; padding:10px;"><img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" width="200"/></div>
                        <p style="font-size: xx-large; margin-bottom: 0px;"><b>Contraseña reseteada</b></p>
                        <p style="font-size: xx-large;"><b> Hola ' . $row["nombres"] . ' </b></p>
                    </center>
                    <div style=" text-align:center; ">
                        <div style="align-items: center;justify-content: center; text-align: center;">
                            <p style="font-size: medium;">Este es tu usuario:
                            <strong style="margin-left: 5px;">' . $row["nombre_usuario"] . '</strong></p>
                        </div>
                        <div style="align-items: center;justify-content: center; text-align: center;" >
                            <p style="font-size: medium;">Esta es tu contraseña:
                            <strong style="margin-left: 5px;">' . $code . '</strong></p>
                        </div>
                    </div>
                    <center>
                        <a href="https://fulmuv.com/empresa/login.php" class="btn btn-primary" style="font-size: 12px;background-color: blue;border: none;color: white;text-align: center;text-decoration: none;display: inline-block; padding: 6px; border-radius: 5px;">Acceder Fulmuv</a>
                    </center>
                </body>
            </html>
            ');
            return $mail->send();
        } else {
            return false;
        }
    }

    public function enviarCorreoNuevoAnuncioFulmuv($data)
    {


        $correo_contenedor = $this->getContenedor(); // tu método actual
        $empresa   = trim($data['nombre_empresa'] ?? '');
        $titular   = trim($data['titular'] ?? '');
        $correo    = trim($data['correo'] ?? '');
        $telefono  = trim($data['telefono'] ?? '');
        $motivo    = trim($data['motivo'] ?? '');
        $comentario = trim($data['comentario'] ?? '');

        // (Opcional) sanitize simple para HTML
        $empresa_safe   = htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8');
        $titular_safe   = htmlspecialchars($titular, ENT_QUOTES, 'UTF-8');
        $correo_safe    = htmlspecialchars($correo, ENT_QUOTES, 'UTF-8');
        $telefono_safe  = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
        $motivo_safe    = htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8');

        // Comentario puede ir con saltos de línea
        $comentario_safe = $comentario !== ''
            ? nl2br(htmlspecialchars($comentario, ENT_QUOTES, 'UTF-8'))
            : '<span style="color:#00686f;">(Sin comentario)</span>';

        // Fecha Ecuador (Guayaquil)
        $fecha = date('Y-m-d H:i:s');

        // ✅ Configura correos destino (admin / soporte)
        // Puedes cambiarlo por el correo real de tu equipo
        $destinatarioAdmin = "jacarrasco@bonsai.com.ec"; // <-- CAMBIA AQUÍ

        // PHPMailer
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;

        // ⚠️ Recomendación: NO hardcodear credenciales aquí.
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");
        $mail->IsHTML(true);

        $mail->Subject = utf8_decode("Nuevo registro recibido: " . $motivo_safe);
        $mail->AddAddress($destinatarioAdmin);

        // (Opcional) CC al correo del interesado
        // $mail->AddCC($correo);

        // Cuerpo del correo (HTML bonito)
        $mail->Body = utf8_decode('
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Nuevo registro - FulMuv</title>
    </head>

    <body style="margin:0; padding:0; background:#
    ; font-family:Arial, Helvetica, sans-serif;">
      <div style="max-width:720px; margin:0 auto; padding:22px;">
        
        <!-- Header -->
        <div style="background:#00686f; padding:16px; border-radius:14px 14px 0 0; text-align:center;">
          <img src="https://fulmuv.com/admin/' . ($correo_contenedor["imagen"] ?? "") . '" width="170" style="max-width:100%; height:auto;" alt="FulMuv" />
          <div style="color:#fff; font-size:18px; font-weight:700; margin-top:10px;">
            Notificación de nuevo registro
          </div>
          <div style="color:rgba(255,255,255,.75); font-size:13px; margin-top:4px;">
            Se recibió una nueva solicitud desde el formulario de contacto.
          </div>
        </div>

        <!-- Card -->
        <div style="background:#ffffff; border-radius:0 0 14px 14px; padding:18px 18px 6px; box-shadow:0 12px 30px rgba(15,23,42,.08);">

          <!-- Badge Motivo -->
          <div style="margin-bottom:14px;">
            <span style="display:inline-block; background:#00686f; color:#fff; padding:8px 12px; border-radius:999px; font-size:13px; font-weight:700;">
              Motivo: ' . $motivo_safe . '
            </span>
            <span style="display:inline-block; margin-left:10px; color:#6b7280; font-size:12px;">
              Fecha: ' . $fecha . '
            </span>
          </div>

          <h2 style="margin:0 0 12px; font-size:18px; color:#111827;">
            Detalles del registro
          </h2>

          <!-- Tabla de datos -->
          <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr>
              <td style="padding:10px; background:#f9fafb; width:38%; color:#374151; border:1px solid #e5e7eb;"><b>Nombre de Empresa</b></td>
              <td style="padding:10px; border:1px solid #e5e7eb; color:#111827;">' . $empresa_safe . '</td>
            </tr>
            <tr>
              <td style="padding:10px; background:#f9fafb; color:#374151; border:1px solid #e5e7eb;"><b>Titular</b></td>
              <td style="padding:10px; border:1px solid #e5e7eb; color:#111827;">' . $titular_safe . '</td>
            </tr>
            <tr>
              <td style="padding:10px; background:#f9fafb; color:#374151; border:1px solid #e5e7eb;"><b>Correo</b></td>
              <td style="padding:10px; border:1px solid #e5e7eb; color:#111827;">
                <a href="mailto:' . $correo_safe . '" style="color:#2563eb; text-decoration:none;">' . $correo_safe . '</a>
              </td>
            </tr>
            <tr>
              <td style="padding:10px; background:#f9fafb; color:#374151; border:1px solid #e5e7eb;"><b>Teléfono</b></td>
              <td style="padding:10px; border:1px solid #e5e7eb; color:#111827;">
                <a href="tel:' . $telefono_safe . '" style="color:#2563eb; text-decoration:none;">' . $telefono_safe . '</a>
              </td>
            </tr>
            <tr>
              <td style="padding:10px; background:#f9fafb; color:#374151; border:1px solid #e5e7eb;"><b>Comentario</b></td>
              <td style="padding:10px; border:1px solid #e5e7eb; color:#111827; line-height:1.5;">
                ' . $comentario_safe . '
              </td>
            </tr>
          </table>

          <!-- CTA -->
          <div style="margin:16px 0 10px; text-align:center;">
            <a href="https://fulmuv.com/admin/login.php" 
               style="display:inline-block; background:#00686f; color:#fff; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:700; font-size:13px;">
              Revisar en el panel
            </a>
          </div>

          <div style="text-align:center; color:#9ca3af; font-size:11px; padding:12px 0 6px;">
            © ' . date("Y") . ' FulMuv. Todos los derechos reservados.
          </div>

        </div>
      </div>
    </body>
    </html>
    ');

        return $mail->send();
    }

    public function enviarCorreoConfirmacionContactoFulmuv($data)
    {
        $correo_contenedor = $this->getContenedor();

        $empresa   = $data['nombre_empresa'];
        $titular   = $data['titular'];
        $correo    = $data['correo'];
        $telefono  = $data['telefono'];
        $motivo    = $data['motivo'];
        $comentario = $data['comentario'];

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;

        // ⚠️ Recomendación: NO hardcodear credenciales aquí.
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");
        $mail->AddAddress($correo, $titular);

        $mail->IsHTML(true);
        $mail->Subject = utf8_decode("Hemos recibido tu solicitud - FULMUV");

        $mail->Body = utf8_decode('
    <!DOCTYPE html>
    <html>
    <body style="margin:0; background:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
    
    <div style="max-width:650px; margin:auto; padding:20px;">
        
        <!-- HEADER -->
        <div style="background:#f3f4f6; padding:18px; text-align:center; border-radius:12px 12px 0 0;">
            <img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" width="180">
        </div>

        <!-- CONTENIDO -->
        <div style="background:#ffffff; padding:24px; border-radius:0 0 12px 12px; box-shadow:0 10px 25px rgba(0,0,0,0.08);">

            <h2 style="color:#111827; margin-top:0;">
                ¡Gracias por contactarte con el equipo de FULMUV!
            </h2>

            <p style="color:#374151; font-size:15px;">
                Estimado/a <b>' . $titular . '</b>,
            </p>

            <p style="color:#374151; font-size:15px;">
                Hemos recibido correctamente tu solicitud relacionada con 
                <b>' . $motivo . '</b>.
            </p>

            <p style="color:#374151; font-size:15px;">
                En FULMUV trabajamos para impulsar el crecimiento de negocios dentro de la 
                plataforma de especialidad vehicular del país, por lo que uno de nuestros 
                asesores se pondrá en contacto contigo en el menor tiempo posible para:
            </p>

            <ul style="color:#374151; font-size:14px;">
                <li>Brindarte información detallada sobre nuestros servicios.</li>
                <li>Acompañarte en el proceso de publicación y promoción.</li>
                <li>Resolver cualquier duda que tengas.</li>
            </ul>

            <hr style="border:none; border-top:1px solid #e5e7eb; margin:20px 0;">

            <h3 style="color:#111827;">Datos registrados</h3>

            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:8px; background:#f9fafb;"><b>Empresa:</b></td>
                    <td style="padding:8px;">' . $empresa . '</td>
                </tr>
                <tr>
                    <td style="padding:8px; background:#f9fafb;"><b>Contacto:</b></td>
                    <td style="padding:8px;">' . $titular . '</td>
                </tr>
                <tr>
                    <td style="padding:8px; background:#f9fafb;"><b>Teléfono:</b></td>
                    <td style="padding:8px;">' . $telefono . '</td>
                </tr>
                <tr>
                    <td style="padding:8px; background:#f9fafb;"><b>Correo:</b></td>
                    <td style="padding:8px;">' . $correo . '</td>
                </tr>
                <tr>
                    <td style="padding:8px; background:#f9fafb;"><b>Comentario:</b></td>
                    <td style="padding:8px;">' . $comentario . '</td>
                </tr>
            </table>

            <div style="text-align:center; margin-top:25px;">
                <a href="https://fulmuv.com" 
                   style="background:#111827; color:white; padding:12px 18px; 
                          text-decoration:none; border-radius:8px; font-weight:bold;">
                    Visitar FULMUV
                </a>
            </div>

            <p style="margin-top:30px; color:#6b7280; font-size:13px;">
                Si tienes alguna consulta adicional, puedes responder directamente a este correo 
                y nuestro equipo te atenderá con gusto.
            </p>

            <p style="color:#f3f4f6; font-weight:bold;">
                Atentamente,<br>
                Equipo FULMUV
            </p>

        </div>

        <div style="text-align:center; font-size:11px; color:#9ca3af; margin-top:10px;">
            © ' . date("Y") . ' FULMUV. Todos los derechos reservados.
        </div>

    </div>

    </body>
    </html>
    ');

        return $mail->send();
    }

    public function resetearPassCliente($id_usuario)
    {

        $correo_contenedor = $this->getContenedor();

        $stmt = $this->conn->prepare("SELECT * FROM clientes where id_cliente = ?");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {

            $code = $this->generateRandomString();
            $password_hash = PassHash::hash($code);

            $stm = $this->conn->prepare("UPDATE clientes SET password = ?, id_cliente = ? WHERE correo = ?");
            $stm->bind_param("sss", $password_hash, $id_usuario, $row["correo"]);
            $resul = $stm->execute();
            $stm->close();

            $mail = new PHPMailer();
            $mail->IsSMTP(); // enable SMTP
            $mail->SMTPAuth = true; // authentication enabled
            $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->Username = 'bonsaidev@bonsai.com.ec';
            $mail->Password = 'ykdvtvcizzgjyfhy';
            $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

            $mail->IsHTML(true);
            $mail->Subject = utf8_decode('Notification');
            $mail->AddAddress($row["correo"]);

            $mail->Body = utf8_decode('
            <!DOCTYPE html>
            <html lang="en">
                <body style="margin:0px;">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
                    <center>
                        <div style="background:#00686f; padding:10px;"><img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" width="200"/></div>
                        <p style="font-size: xx-large; margin-bottom: 0px;"><b>Contraseña reseteada</b></p>
                        <p style="font-size: xx-large;"><b> Hola ' . $row["nombres"] . ' </b></p>
                    </center>
                    <div style=" text-align:center; ">
                        <div style="align-items: center;justify-content: center; text-align: center;">
                            <p style="font-size: medium;">Este es tu usuario:
                            <strong style="margin-left: 5px;">' . $row["nombre_usuario"] . '</strong></p>
                        </div>
                        <div style="align-items: center;justify-content: center; text-align: center;" >
                            <p style="font-size: medium;">Esta es tu contraseña:
                            <strong style="margin-left: 5px;">' . $code . '</strong></p>
                        </div>
                    </div>
                    <center>
                        <a href="https://fulmuv.com/login.php" class="btn btn-primary" style="font-size: 12px;background-color: blue;border: none;color: white;text-align: center;text-decoration: none;display: inline-block; padding: 6px; border-radius: 5px;">Acceder Fulmuv</a>
                    </center>
                </body>
            </html>
            ');
            return $mail->send();
        } else {
            return false;
        }
    }

    public function updatePasswordCliente($id_usuario, $password)
    {
        // 1) Verificar si existe y está activo
        $stmt = $this->conn->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'A'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return RECORD_DOES_NOT_EXIST;
        }

        // 2) Hashear la nueva contraseña
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // 3) Actualizar password (ajusta el nombre del campo si es diferente)
        $stmt2 = $this->conn->prepare("UPDATE clientes SET password = ? WHERE id_cliente = ? AND estado = 'A'");
        $stmt2->bind_param("si", $hash, $id_usuario);

        $ok = $stmt2->execute();
        $stmt2->close();

        return $ok ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function getClienteById($id_cliente)
    {
        $stmt = $this->conn->prepare("
        SELECT *
        FROM clientes
        WHERE id_cliente = ? AND estado = 'A'
        LIMIT 1
    ");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? $data : null;
    }

    public function updateDatosCliente($id_cliente, $nombres, $cedula, $telefono, $correo)
    {
        // 1) Verificar que exista el cliente activo
        $stmt = $this->conn->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'A' LIMIT 1");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->fetch_assoc();
        $stmt->close();

        if (!$exists) return "NOT_FOUND";

        // 2) Verificar correo duplicado en otro cliente (si aplica)
        $stmt2 = $this->conn->prepare("SELECT id_cliente FROM clientes WHERE correo = ? AND id_cliente <> ? AND estado = 'A' LIMIT 1");
        $stmt2->bind_param("si", $correo, $id_cliente);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $dup = $res2->fetch_assoc();
        $stmt2->close();

        if ($dup) return "EXISTS_EMAIL";

        // 3) Actualizar datos
        $stmt3 = $this->conn->prepare("
        UPDATE clientes
        SET nombres = ?, cedula = ?, telefono = ?, correo = ?
        WHERE id_cliente = ? AND estado = 'A'
    ");
        $stmt3->bind_param("ssssi", $nombres, $cedula, $telefono, $correo, $id_cliente);
        $ok = $stmt3->execute();
        $stmt3->close();

        return $ok;
    }


    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /* RESETEAR PASS CORREO */


    public function ifExistsEmail($email, $username)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM usuarios where (correo = ? OR nombre_usuario = ?) AND estado = 'A'");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $response[] = $row;
            }
            return $response;
        } else {
            return false;
        }
    }


    public function ifExistsClienteEmail($email)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM clientes where correo = ? AND estado = 'A'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $response[] = $row;
            }
            return $response;
        } else {
            return false;
        }
    }

    /*TRAER CLAVE DEL CLIENTE*/
    public function adminloginCliente($usuario, $clave)
    {
        $stmt = $this->conn->prepare("SELECT clave FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result(); //Devuelve un objeto de resultados almacenado en buffer o false si ocurrió un error.
        if ($stmt->num_rows > 0) { //Obtiene el número de filas de un resultado
            $stmt->fetch();
            $stmt->close();

            if (PassHash::check_password($password_hash, $clave)) {
                return 2;
            } else {
                return 1;
            }
        } else {
            $stmt->close();
            return 0;
        }
    }
    /*TRAER CLAVE DEL CLIENTE*/


    public function getAdminByUsuario($usuario)
    {
        $stmt = $this->conn->prepare("SELECT u.id_usuario, u.nombre_usuario, u.rol_id, u.created_at, u.nombres, u.correo, u.imagen, r.rol, u.id AS id_empresa from usuarios u INNER JOIN rol r ON r.id_rol = u.rol_id where u.nombre_usuario = ? OR u.correo = ?");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["nombre_usuario"] =  $row['nombre_usuario'];
            $row["nombre_rol_user"] =  $row["rol"];


            unset($row["rol"]);
            return $row;
        } else return RECORD_DOES_NOT_EXIST;
    }

    public function getClienteByUsuario($usuario)
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes where correo = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["correo"] =  $row["correo"];

            return $row;
        } else return RECORD_DOES_NOT_EXIST;
    }


    public function getClienteByIdCliente($id_cliente)
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes where id_cliente = ? and estado ='A'");
        $stmt->bind_param("s", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        } else return RECORD_DOES_NOT_EXIST;
    }



    /* PERMISOS */
    public function getPermisosByUser($id_principal)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT p.id_permisos, p.permiso, p.valor, p.levels, p.created_at, p.estado, p.id_rol, u.id AS id_empresa FROM usuarios u, rol r, permisos p WHERE u.id_usuario = ? AND u.rol_id = r.id_rol AND p.id_rol =r.id_rol;");
        $stmt->bind_param("s", $id_principal);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $row["membresia"] = !empty($row["id_empresa"]) ? $this->getMembresiaByEmpresa($row["id_empresa"]) : null;
            $response[] = $row;
        }
        return $response;
    }
    /* PERMISOS */

    public function getUsuarios($id_usuario, $id_empresa)
    {
        $user_permisos = $this->getPermisosByUser($id_usuario);
        $user_permisos = array_filter($user_permisos, function ($permiso) {
            return $permiso['permiso'] == 'Usuarios';
        });
        $user_permisos = array_values($user_permisos)[0];
        $response = array();

        if ($user_permisos["valor"] == "true") {
            switch ($user_permisos["levels"]) {
                case 'Fulmuv':
                    $stmt = $this->conn->prepare("SELECT u.id_usuario,
                    u.nombre_usuario,
                    u.rol_id,
                    r.rol,
                    u.created_at,
                    u.estado,
                    u.nombres,
                    u.correo,
                    u.imagen,
                    u.id,
                    CASE 
                        WHEN u.rol_id = 1 THEN 'Owner'
                        WHEN u.rol_id = 2 THEN 'Empresa'
                        WHEN u.rol_id = 3 THEN 'Sucursal'
                    END AS nivel,
                    COALESCE(e.nombre, e2.nombre, '') AS nombre_empresa
                    FROM usuarios u
                    JOIN rol r ON u.rol_id = r.id_rol
                    LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
                    LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3
                    LEFT JOIN empresas e2 ON s.id_empresa = e2.id_empresa AND u.rol_id = 3
                    WHERE u.estado = 'A';");
                    break;
                case 'Empresa':
                    $stmt = $this->conn->prepare("SELECT u.id_usuario, u.nombre_usuario, u.rol_id, r.rol, u.created_at, u.estado, u.nombres,
                       u.correo, u.imagen, u.id, 'Empresa' AS nivel, e.nombre AS nombre_empresa
                    FROM usuarios u
                    JOIN rol r ON u.rol_id = r.id_rol
                    JOIN empresas e ON u.id = e.id_empresa
                    WHERE u.estado = 'A'  AND u.rol_id = 2 AND e.id_empresa = ?
                    UNION ALL
                    SELECT u.id_usuario, u.nombre_usuario, u.rol_id, r.rol, u.created_at, u.estado, u.nombres,
                        u.correo, u.imagen, u.id, 'Sucursal' AS nivel, e.nombre AS nombre_empresa
                    FROM usuarios u
                    JOIN rol r ON u.rol_id = r.id_rol
                    JOIN sucursales s ON u.id = s.id_sucursal
                    JOIN empresas e ON s.id_empresa = e.id_empresa
                    WHERE u.estado = 'A' AND u.rol_id = 3 AND s.id_empresa = ?;");
                    $stmt->bind_param("ss", $id_empresa, $id_empresa);

                    break;
                case 'Sucursal':
                    $stmt = $this->conn->prepare("SELECT u.id_usuario, u.nombre_usuario, u.rol_id, r.rol, u.created_at, u.estado, u.nombres,
                       u.correo, u.imagen, u.id, 'Sucursal' AS nivel, e.nombre AS nombre_empresa
                    FROM usuarios u
                    JOIN rol r ON u.rol_id = r.id_rol
                    JOIN sucursales s ON u.id = s.id_sucursal
                    JOIN empresas e ON s.id_empresa = e.id_empresa
                    WHERE u.estado = 'A' AND u.rol_id = 3 AND s.id_sucursal = ?;");
                    $stmt->bind_param("s", $id_empresa);
                    break;
                default:
                    break;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $response[] = $row;
            }
        }
        return $response;



        $stmt = $this->conn->prepare("SELECT 
        u.id_usuario,
        u.nombre_usuario,
        u.rol_id,
        r.rol,
        u.created_at,
        u.estado,
        u.nombres,
        u.correo,
        u.imagen,
        u.id,
        CASE 
            WHEN u.rol_id = 2 THEN 'Empresa'
            WHEN u.rol_id = 3 THEN 'Sucursal'
            WHEN u.rol_id = 4 THEN 'Área'
            ELSE 'Owner'
        END AS nivel,
        COALESCE(e.nombre, e2.nombre, e3.nombre, '') AS nombre_empresa
        FROM usuarios u
        JOIN rol r ON u.rol_id = r.id_rol 
        LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
        LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3
        LEFT JOIN empresas e2 ON s.id_empresa = e2.id_empresa AND u.rol_id = 3
        LEFT JOIN areas a ON u.id = a.id_area AND u.rol_id = 4
        LEFT JOIN sucursales s2 ON a.id_sucursal = s2.id_sucursal AND u.rol_id = 4
        LEFT JOIN empresas e3 ON s2.id_empresa = e3.id_empresa AND u.rol_id = 4
        WHERE u.estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return
            $response;
    }
    /*  public function getUsuarios()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT 
        u.id_usuario,
        u.nombre_usuario,
        u.rol_id,
        r.rol,
        u.created_at,
        u.estado,
        u.nombres,
        u.correo,
        u.imagen,
        u.id,
        CASE 
            WHEN u.rol_id = 1 THEN 'Owner'
            WHEN u.rol_id = 2 THEN 'Empresa'
            WHEN u.rol_id = 3 THEN 'Sucursal'
            WHEN u.rol_id = 4 THEN 'Área'
        END AS nivel,
        COALESCE(e.nombre, s.nombre, a.nombre, 'Owner') AS nombre_nivel -- Para los owners, el nombre_nivel será 'Owner'
        FROM  usuarios u
        JOIN rol r ON u.rol_id = r.id_rol
        LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
        LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3 
        LEFT JOIN areas a ON u.id = a.id_area AND u.rol_id = 4
        LEFT JOIN sucursales s2 ON a.id_sucursal = s2.id_sucursal AND u.rol_id = 4
        WHERE u.estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa") <= 0) {
                continue;
            }
            $response[] = $row;
        }
        return
            $response;
    } */

    public function getUsuarioById($id_usuario)
    {
        $stmt = $this->conn->prepare("SELECT 
        u.id_usuario,
        u.nombre_usuario,
        u.rol_id,
        r.rol,
        u.created_at,
        u.estado,
        u.nombres,
        u.correo,
        u.imagen,
        u.id,
        CASE 
            WHEN u.rol_id = 1 THEN 'Owner'
            WHEN u.rol_id = 2 THEN 'Empresa'
            WHEN u.rol_id = 3 THEN 'Sucursal'
            WHEN u.rol_id = 4 THEN 'Área'
        END AS nivel,
        COALESCE(e.nombre, s.nombre, a.nombre, 'Owner') AS nombre_nivel -- Para los owners, el nombre_nivel será 'Owner'
        FROM  usuarios u
        JOIN rol r ON u.rol_id = r.id_rol
        LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
        LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3 
        LEFT JOIN areas a ON u.id = a.id_area AND u.rol_id = 4
        LEFT JOIN sucursales s2 ON a.id_sucursal = s2.id_sucursal AND u.rol_id = 4
        WHERE u.estado = 'A' AND u.id_usuario = ?;");
        $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function createUsuario($nombre_usuario, $pass, $rol_id, $nombres, $correo, $imagen, $id = "")
    {
        if (!$this->isUsuarioExists($nombre_usuario)) {
            // $password_hash = PassHash::hash($pass);
            //$password_hash = "$2a$10$40c5301977319f32809bfO0O1LxgipOC.xr26Q8yOvmRqr2no7d0y";
            // $password_hash = "$2a$10$098b72b21a867db4f9e62OIQxuhOQSSNa1.mIUlY/zhtGXLTY2PXq";
            $code = $this->generateRandomString();
            $password_hash = PassHash::hash($code);

            $stmt = $this->conn->prepare("INSERT INTO usuarios(nombre_usuario, pass, rol_id, nombres, correo, imagen, id) values(?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $nombre_usuario, $password_hash, $rol_id, $nombres, $correo, $imagen, $id);
            $this->conn->begin_transaction();
            $this->conn->begin_transaction();
            $this->conn->begin_transaction();
            $result = $stmt->execute();
            $empresaStmtError = $stmt->error;
            $empresaStmtError = $stmt->error;
            $empresaStmtError = $stmt->error;
            $stmt->close();
            if ($result) {
                $this->notificaNuevoCliente($correo, $nombres, $code);
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isUsuarioExists($nombre_usuario)
    {
        $stmt = $this->conn->prepare("SELECT nombre_usuario, rol_id, id 
        FROM usuarios 
        WHERE nombre_usuario=? AND estado = 'A'");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateUsuario($id_usuario, $nombre_usuario, $rol_id, $nombres, $correo, $imagen, $id)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET nombre_usuario=?, rol_id=?, nombres=?, correo=?, imagen=?, id=?   WHERE id_usuario = ?");
        $stmt->bind_param("sssssss", $nombre_usuario, $rol_id, $nombres, $correo, $imagen, $id, $id_usuario);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function darsebajaFULMUV($id_empresa, $modo)
    {
        $fecha_baja = date('Y-m-d H:i:s');

        try {
            $this->conn->begin_transaction();

            // 1) EMPRESAS
            $stmt1 = $this->conn->prepare("
            UPDATE empresas
            SET estado = 'E'
            WHERE id_empresa = ?
        ");
            $stmt1->bind_param("s", $id_empresa);
            if (!$stmt1->execute()) {
                throw new Exception("Error al actualizar empresas: " . $stmt1->error);
            }
            $stmt1->close();

            // 2) USUARIOS (todos los usuarios de esa empresa)
            $stmt2 = $this->conn->prepare("
            UPDATE usuarios
            SET estado = 'E', fecha_baja = ?, modo_baja = ?
            WHERE id = ?
        ");
            $stmt2->bind_param("sss", $fecha_baja, $modo, $id_empresa);
            if (!$stmt2->execute()) {
                throw new Exception("Error al actualizar usuarios: " . $stmt2->error);
            }
            $stmt2->close();

            // 3) MEMBRESIAS_EMPRESAS (la(s) membresía(s) de esa empresa)
            $stmt3 = $this->conn->prepare("
            UPDATE membresias_empresas
            SET estado = 'E'
            WHERE id_empresa = ?
        ");
            $stmt3->bind_param("s", $id_empresa);
            if (!$stmt3->execute()) {
                throw new Exception("Error al actualizar membresias_empresas: " . $stmt3->error);
            }
            $stmt3->close();

            $this->conn->commit();
            return RECORD_CREATED_SUCCESSFULLY;
        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage(); // o RECORD_CREATION_FAILED si no quieres detallar
        }
    }


    public function deleteUsuario($id_usuario)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET estado = 'E' WHERE id_usuario = ?");
        $stmt->bind_param("s", $id_usuario);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function updatePass($id_usuario, $pass)
    {
        $password_hash = PassHash::hash($pass);
        $stmt = $this->conn->prepare("UPDATE usuarios SET pass=? WHERE id_usuario = ?");
        $stmt->bind_param("ss", $password_hash, $id_usuario);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            $usuario = $this->getUsuarioById($id_usuario);
            if (is_array($usuario) && !empty($usuario["correo"])) {
                $this->notificaActualizacionPasswordUsuario(
                    $usuario["correo"],
                    $usuario["nombres"] ?? $usuario["nombre_usuario"] ?? 'Usuario',
                    $usuario["nombre_usuario"] ?? '',
                    $pass
                );
            }
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    private function rollbackEmpresaCheckout($empresa_id = 0, $id_usuario = 0, $removePagos = false)
    {
        $empresa_id = (int)$empresa_id;
        $id_usuario = (int)$id_usuario;

        if ($removePagos && $empresa_id > 0) {
            $stmtPagos = $this->conn->prepare("UPDATE pagos_recurrentes SET estado = 'E', es_default = 'N' WHERE id_empresa = ?");
            if ($stmtPagos) {
                $stmtPagos->bind_param("i", $empresa_id);
                $stmtPagos->execute();
                $stmtPagos->close();
            }
        }

        if ($id_usuario > 0) {
            $this->deleteUsuario($id_usuario);
        }

        if ($empresa_id > 0) {
            $this->deleteEmpresa($empresa_id);
        }
    }

    public function notificaActualizacionPasswordUsuario($correo, $nombre, $nombreUsuario, $passwordPlano)
    {
        $correo_contenedor = $this->getContenedor();
        $logo = "https://fulmuv.com/admin/" . ltrim((string)($correo_contenedor["imagen"] ?? ''), "/");
        $nombreSeguro = htmlspecialchars((string)$nombre, ENT_QUOTES, "UTF-8");
        $passwordSeguro = htmlspecialchars((string)$passwordPlano, ENT_QUOTES, "UTF-8");

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FULMUV");
        $mail->IsHTML(true);
        $mail->Subject = utf8_decode('Tu contraseña de FULMUV fue actualizada');
        $mail->AddAddress($correo);

        $mail->Body = utf8_decode('
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <title>Contraseña actualizada</title>
            </head>
            <body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef2f7;padding:28px 0;">
                    <tr>
                        <td align="center" style="padding:0 12px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 18px 45px rgba(15,23,42,.10);">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#004e60 0%,#0f766e 100%);padding:28px 24px 22px;text-align:center;">
                                        <img src="' . $logo . '" alt="FULMUV" style="max-width:180px;width:100%;height:auto;display:block;margin:0 auto 14px;">
                                        <div style="display:inline-block;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);color:#ffffff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:8px 14px;border-radius:999px;">
                                            Seguridad de cuenta
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:34px 28px 26px;">
                                        <div style="font-size:28px;font-weight:800;line-height:1.2;color:#0f172a;margin-bottom:8px;">
                                            Tu contraseña fue actualizada
                                        </div>
                                        <div style="font-size:15px;line-height:1.7;color:#475569;margin-bottom:18px;">
                                            Hola <strong>' . $nombreSeguro . '</strong>, registramos correctamente un cambio de contraseña en tu cuenta de FULMUV.
                                        </div>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);border:1px solid #e2e8f0;border-radius:18px;margin:20px 0 18px;">
                                            <tr>
                                                <td style="padding:20px 20px 12px;">
                                                    <div style="font-size:13px;color:#64748b;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:14px;">
                                                        Tu nueva clave de acceso
                                                    </div>
                                                    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:22px 18px;text-align:center;">
                                                        <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;">
                                                            <span style="display:inline-block;width:52px;border-top:2px dashed #94a3b8;"></span>
                                                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#0f766e;"></span>
                                                            <span style="display:inline-block;width:52px;border-top:2px dashed #94a3b8;"></span>
                                                        </div>
                                                        <div style="font-size:28px;font-weight:800;letter-spacing:.08em;color:#0f172a;word-break:break-word;line-height:1.35;">
                                                            ' . $passwordSeguro . '
                                                        </div>
                                                        <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-top:14px;">
                                                            <span style="display:inline-block;width:52px;border-top:2px dashed #94a3b8;"></span>
                                                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#0f766e;"></span>
                                                            <span style="display:inline-block;width:52px;border-top:2px dashed #94a3b8;"></span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="background:#ecfeff;border:1px solid #a5f3fc;border-radius:16px;padding:15px 16px;margin-bottom:22px;">
                                            <div style="font-size:14px;font-weight:700;color:#155e75;margin-bottom:6px;">Recomendación de seguridad</div>
                                            <div style="font-size:14px;line-height:1.6;color:#0f172a;">
                                                Si no realizaste este cambio, te recomendamos actualizar tu contraseña nuevamente y contactar al equipo de soporte lo antes posible.
                                            </div>
                                        </div>

                                        <div style="text-align:center;margin-bottom:6px;">
                                            <a href="https://fulmuv.com/empresa/login.php" style="display:inline-block;background:#004e60;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 22px;border-radius:12px;">
                                                Ingresar a FULMUV
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 24px;border-top:1px solid #e5e7eb;background:#fafafa;">
                                        <div style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.7;">
                                            Este correo fue enviado automáticamente por FULMUV.<br>
                                            © ' . date('Y') . ' FULMUV. Todos los derechos reservados.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ');

        return $mail->send();
    }

    /* UPDATE IMAGEN */
    public function updateImagenUser($id_usuario, $imagen)
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET imagen=? WHERE id_usuario=?;");
        $stmt->bind_param("ss", $imagen, $id_usuario);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }
    /* UPDATE IMAGEN */

    /* ROLES */
    public function getRoles()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM rol
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getRolById($id_rol, $detalle = false)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM rol
        WHERE estado = 'A' AND id_rol = ?");
        $stmt->bind_param("s", $id_rol);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            /* if ($detalle) {
                $row["sucursales"] = $this->getSucursalesByEmpresa($row["id_empresa"]);
                $row["usuarios"] = [];
            } */
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getPermisos($rol)
    {
        $stmt = $this->conn->prepare("SELECT * FROM permisos where id_rol = ?");
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    /* VALIDA ROLE */
    public function validaRole()
    {

        $stmt = $this->conn->prepare("SELECT r.rol FROM rol r WHERE r.rol = 'Owner' AND r.estado = 'A';");
        //$stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $respuesta = $stmt->get_result();
        while ($row = $respuesta->fetch_array(MYSQLI_ASSOC)) {
            $response["rol"] = $row["rol"];
            //$response[] = $res;
        }
        return $response;
    }
    /* VALIDA ROLE */

    /* CREAR ROLE */
    public function createRole($nameRole)
    {

        $stmt = $this->conn->prepare("INSERT INTO rol (rol, created_at, estado) VALUES(?, CURRENT_TIMESTAMP, 'A');");
        $stmt->bind_param("s", $nameRole);

        $result = $stmt->execute();
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            //return RECORD_CREATED_SUCCESSFULLY;
            return $ultimo_id;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }
    /* CREAR ROLE */

    /* CREAR PERMISO POR ID_ROLE */
    public function createPermisos($id_role, $name, $valor, $level)
    {

        $stmt = $this->conn->prepare("INSERT INTO permisos (permiso, valor, levels, created_at, estado, id_rol)
    VALUES(?, ?, ?, CURRENT_TIMESTAMP, 'A', ?);");
        $stmt->bind_param("sssi", $name, $valor, $level, $id_role);

        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
            //return $ultimo_id;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }
    /* CREAR PERMISO POR ID_ROLE */

    /* ACTUALIZA PERMISO */
    public function actualizaPermiso($nameRole, $id_role, $valor)
    {

        $stmt = $this->conn->prepare("UPDATE permisos SET " . $nameRole . " = ? WHERE id_permisos=?;");

        $stmt->bind_param("si", $valor, $id_role);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }
    /* ROLES */

    /* EMPRESAS */

    public function getUsuariosByEmpresa($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT 
    u.id_usuario,
    u.nombre_usuario,
    u.rol_id,
    r.rol,
    u.created_at,
    u.estado,
    u.nombres,
    u.correo,
    u.imagen,
    u.id,
    CASE 
        WHEN u.rol_id = 2 THEN 'Empresa'
        WHEN u.rol_id = 3 THEN 'Sucursal'
        WHEN u.rol_id = 4 THEN 'Área'
    END AS nivel,
    COALESCE(e.nombre, s.nombre, a.nombre) AS nombre_nivel
FROM usuarios u
JOIN rol r ON u.rol_id = r.id_rol
LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3 
LEFT JOIN areas a ON u.id = a.id_area AND u.rol_id = 4
LEFT JOIN sucursales s2 ON a.id_sucursal = s2.id_sucursal AND u.rol_id = 4
WHERE 
    (u.rol_id = 2 AND u.id = ? AND u.estado = 'A')
    OR (u.rol_id = 3 AND s.id_empresa = ? AND u.estado = 'A')
    OR (u.rol_id = 4 AND s2.id_empresa = ? AND u.estado = 'A');
");
        /* $stmt = $this->conn->prepare("SELECT 
        u.id_usuario,
        u.nombre_usuario,
        u.rol_id,
        r.rol,
        u.created_at,
        u.estado,
        u.nombres,
        u.correo,
        u.imagen,
        u.id,
        CASE 
            WHEN u.rol_id = 2 THEN 'Empresa'
            WHEN u.rol_id = 3 THEN 'Sucursal'
            WHEN u.rol_id = 4 THEN 'Área'
        END AS nivel,
        COALESCE(e.nombre, s.nombre, a.nombre) AS nombre_nivel
        FROM usuarios u
        JOIN rol r ON u.rol_id = r.id_rol
        LEFT JOIN empresas e ON u.id = e.id_empresa AND u.rol_id = 2
        LEFT JOIN sucursales s ON u.id = s.id_sucursal AND u.rol_id = 3 
        LEFT JOIN areas a ON u.id = a.id_area AND u.rol_id = 4
        LEFT JOIN sucursales s2 ON a.id_sucursal = s2.id_sucursal AND u.rol_id = 4
        WHERE 
            u.rol_id = 2 AND u.id = ? AND u.estado = 'A'
            OR u.rol_id = 3 AND s.id_empresa = ?
            OR u.rol_id = 4 AND s2.id_empresa = ?;"); */
        $stmt->bind_param("sss", $id_empresa,  $id_empresa,  $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getEmpresas()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT e.*
        FROM empresas e
        WHERE e.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["membresia"] = $this->getMembresiaByEmpresa($row["id_empresa"]);
            $response[] = $row;
        }
        return $response;
    }

    public function getEmpresasByMembresiaTodos()
    {
        $response = array();

        $stmt = $this->conn->prepare("
        SELECT DISTINCT e.*
        FROM empresas e
        INNER JOIN membresias_empresas me ON me.id_empresa = e.id_empresa AND me.estado = 'A'
        INNER JOIN membresias m ON m.id_membresia = me.id_membresia AND m.tipo = 'todos'
        WHERE e.estado = 'A'
          AND me.fecha_inicio <= CURDATE()
          AND me.fecha_fin >= CURDATE()
          AND LOWER(m.nombre) NOT IN ('basicmuv','onemuv')
    ");

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $row["membresia"] = $this->getMembresiaByEmpresa($row["id_empresa"]);
            $response[] = $row;
        }

        return $response;
    }

    public function getMembresiaByEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT m.nombre, m.tipo, m.numero, me.fecha_inicio, me.fecha_fin,
            CASE
                WHEN me.estado = 'A' AND me.fecha_fin >= CURDATE() THEN 'ACTIVA'
                WHEN me.fecha_fin < CURDATE() THEN 'VENCIDA'
                ELSE 'OTRO'
            END AS estado_membresia
            FROM membresias_empresas me
            INNER JOIN membresias m
            ON m.id_membresia = me.id_membresia
            WHERE me.estado = 'A' AND me.id_empresa = ?");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    public function validaMembresiaEmpresaLogin($id_empresa)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                m.id_membresia,
                m.nombre,
                m.tipo,
                m.costo,
                m.numero,
                me.fecha_inicio,
                me.fecha_fin,
                me.estado,
                CASE
                    WHEN me.estado = 'A' AND me.fecha_fin >= CURDATE() THEN 'ACTIVA'
                    WHEN me.fecha_fin < CURDATE() THEN 'VENCIDA'
                    ELSE 'OTRO'
                END AS estado_membresia
            FROM membresias_empresas me
            INNER JOIN membresias m ON m.id_membresia = me.id_membresia
            WHERE me.id_empresa = ?
            ORDER BY me.fecha_fin DESC
            LIMIT 1
        ");

        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row; // Devuelve información + estado_membresia
        }

        // Si no encuentra nada, nunca ha tenido membresía
        return null;
    }

    // public function getEmpresaById($id_empresa, $detalle = false)
    // {
    //     $stmt = $this->conn->prepare("SELECT e.*
    //     FROM empresas e
    //     WHERE e.estado = 'A' AND e.id_empresa = ?");
    //     $stmt->bind_param("s", $id_empresa);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     if ($row = $result->fetch_assoc()) {
    //         $row["sucursales"] = $this->getSucursalesByEmpresa($row["id_empresa"], true);
    //         $row["usuarios"] = $this->getUsuariosByEmpresa($row["id_empresa"]);
    //         $row["archivos"] = $this->filesEmpresa($row["id_empresa"]);
    //         $row["membresia"] = $this->getMembresiaByEmpresa($row["id_empresa"]);
    //         $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);
    //         return $row;
    //     }
    //     return RECORD_DOES_NOT_EXIST;
    // }

    public function getEmpresaById($id, $detalle = false)
    {
        // =========================
        // 1) Buscar como EMPRESA
        // =========================
        $stmt = $this->conn->prepare("
        SELECT e.*
        FROM empresas e
        WHERE e.id_empresa = ?
        ORDER BY e.id_empresa DESC
        LIMIT 1
    ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $row["tipo"] = "empresa";

            // Detalle completo (lo que ya tenías)
            $row["sucursales"]   = $this->getSucursalesByEmpresa((int)$row["id_empresa"], true);
            $row["usuarios"]     = $this->getUsuariosByEmpresa((int)$row["id_empresa"]);
            $row["archivos"]     = $this->filesEmpresa((int)$row["id_empresa"]);
            $row["membresia"]    = $this->getMembresiaByEmpresa((int)$row["id_empresa"]);
            $row["verificacion"] = $this->getVerificacionCuentaEmpresa((int)$row["id_empresa"]);

            return $row;
        }

        // =========================
        // 2) Si NO existe empresa, buscar como SUCURSAL
        // =========================
        $stmt2 = $this->conn->prepare("
        SELECT s.*
        FROM sucursales s
        WHERE s.estado = 'A' AND s.id_sucursal = ?
        LIMIT 1
    ");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($row2 = $result2->fetch_assoc()) {
            $row2["tipo"] = "sucursal";

            // (Opcional recomendado) Adjuntar la empresa dueña de la sucursal
            $stmt3 = $this->conn->prepare("
            SELECT e.*
            FROM empresas e
            WHERE e.estado = 'A' AND e.id_empresa = ?
            LIMIT 1
        ");
            $idEmpresa = (int)$row2["id_empresa"];
            $stmt3->bind_param("i", $idEmpresa);
            $stmt3->execute();
            $row2["empresa"] = $stmt3->get_result()->fetch_assoc() ?: null;
            $row2["img_path"] = !empty($row2["imagen"]) ? $row2["imagen"] : ($row2["empresa"]["img_path"] ?? null);
            $row2["archivos"] = $this->filesEmpresa($idEmpresa);
            $row2["membresia"] = $this->getMembresiaByEmpresa($idEmpresa);
            $row2["verificacion"] = $this->getVerificacionCuentaEmpresa($idEmpresa);

            if (empty($row2["descripcion"]) && !empty($row2["empresa"]["descripcion"])) {
                $row2["descripcion"] = $row2["empresa"]["descripcion"];
            }
            if (empty($row2["tiempo_anos"]) && isset($row2["empresa"]["tiempo_anos"])) {
                $row2["tiempo_anos"] = $row2["empresa"]["tiempo_anos"];
            }
            if (empty($row2["tiempo_meses"]) && isset($row2["empresa"]["tiempo_meses"])) {
                $row2["tiempo_meses"] = $row2["empresa"]["tiempo_meses"];
            }

            return $row2;
        }

        return RECORD_DOES_NOT_EXIST;
    }


    public function getEmpresaById2($id, $detalle = false, $tipo = 'empresa')
    {
        // ==========================
        //  Caso: viene por SUCURSAL
        // ==========================
        if ($tipo === 'sucursal') {

            // 1) Buscar la sucursal
            $stmt = $this->conn->prepare("
            SELECT s.*
            FROM sucursales s
            WHERE s.estado = 'A' AND s.id_sucursal = ?
        ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if (!$sucursal = $result->fetch_assoc()) {
                $stmt->close();
                return RECORD_DOES_NOT_EXIST;
            }
            $stmt->close();

            // 2) Con el id_empresa de la sucursal, buscar SOLO img_path
            $id_empresa = $sucursal['id_empresa'];

            $stmt2 = $this->conn->prepare("
            SELECT img_path
            FROM empresas
            WHERE estado = 'A' AND id_empresa = ?
        ");
            $stmt2->bind_param("i", $id_empresa);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            $img_path = null;
            if ($empresa = $result2->fetch_assoc()) {
                $img_path = $empresa['img_path'];
            }
            $stmt2->close();

            // 3) Devolver datos de la sucursal + img_path de la empresa
            $sucursal['img_path'] = $img_path;

            return $sucursal;
        }

        // ================================
        //  Caso por defecto: TIPO EMPRESA
        // ================================
        $stmt = $this->conn->prepare("
        SELECT e.*
        FROM empresas e
        WHERE e.estado = 'A' AND e.id_empresa = ?
    ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $row["sucursales"]   = $this->getSucursalesByEmpresa($row["id_empresa"], true);
            $row["usuarios"]     = $this->getUsuariosByEmpresa($row["id_empresa"]);
            $row["archivos"]     = $this->filesEmpresa($row["id_empresa"]);
            $row["membresia"]    = $this->getMembresiaByEmpresa($row["id_empresa"]);
            $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);
            return $row;
        }

        return RECORD_DOES_NOT_EXIST;
    }



    public function createEmpresa($nombre, $direccion, $tipo_establecimiento, $razon_social, $latitud, $longitud)
    {
        if (!$this->isEmpresaExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO empresas (nombre, direccion, tipo_establecimiento, razon_social, latitud, longitud) VALUES(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nombre, $direccion, $tipo_establecimiento, $razon_social, $latitud, $longitud);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    // public function createEmpresaExtendida(
    //     $nombre,
    //     $direccion,
    //     $whatsapp_contacto,
    //     $telefono_contacto,
    //     $username,
    //     $email,
    //     $password,
    //     $nombre_titular,
    //     $tipo_local,
    //     $categorias_referencia,
    //     $provincia,
    //     $canton,
    //     $calle_principal,
    //     $calle_secundaria,
    //     $bien_inmueble,
    //     $razon_social,
    //     $celular,
    //     $tipo_identificacion,
    //     $cedula_ruc,
    //     $latitud,
    //     $longitud,
    //     $sucursales,
    //     $direccion_facturacion,
    //     $id_membresia = null,
    //     $pago_valor = null,
    //     $tipo = 'empresa',
    //     $transaction_id = '',
    //     $authorization_code = '',
    //     $recurrente = 'N',
    //     $payment_date = '',
    //     $valor_membresia = null,
    //     $promo_resumen = null,
    //     $token = '',
    //     $transaction_reference = '',
    //     $tipo_pago_checkout = 0,
    //     $meses_checkout = 0
    // ) {
    //     $nombreUsuarioLogin = $username;
    //     $empresa_id = 0;
    //     $id_usuario_creado = 0;
    //     $resultUser = false;
    //     $empresaExiste = $this->isEmpresaExists($nombre);
    //     $usuarioExiste = $this->isUsuarioExists($username);
    //     if ($empresaExiste) {
    //         $resp["error"] = RECORD_ALREADY_EXISTED;
    //         $resp["msg"]  = "La empresa ya existe. Intente con otra.";
    //         return $resp;
    //     }

    //     if ($usuarioExiste) {
    //         $resp["error"] = RECORD_ALREADY_EXISTED;
    //         $resp["msg"]  = "El nombre de usuario ya existe. Intente con otro.";
    //         return $resp;
    //     }

    //     $categorias_referencia = json_encode($categorias_referencia);
    //     $stmt = $this->conn->prepare("
    //         INSERT INTO empresas (
    //             nombre, direccion, whatsapp_contacto, nombre_titular, 
    //             tipo_tienda, categorias_referencia, correo, provincia, canton, calle_principal, calle_secundaria, bien_inmueble,
    //             razon_social, telefono_contacto, tipo_identificacion, cedula_ruc, latitud, longitud, sucursales, estado, direccion_facturacion
    //         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'E', ?)
    //     ");
    //     $stmt->bind_param(
    //         "ssssssssssssssssssss",
    //         $nombre,
    //         $direccion,
    //         $whatsapp_contacto,
    //         $nombre_titular,
    //         $tipo_local,
    //         $categorias_referencia,
    //         $email,
    //         $provincia,
    //         $canton,
    //         $calle_principal,
    //         $calle_secundaria,
    //         $bien_inmueble,
    //         $razon_social,
    //         $telefono_contacto,
    //         $tipo_identificacion,
    //         $cedula_ruc,
    //         $latitud,
    //         $longitud,
    //         $sucursales,
    //         $direccion_facturacion
    //     );
    //     $result = $stmt->execute();
    //     // Obtener el ID de la empresa recién creada (si lo necesitas para relacionar)
    //     $empresa_id = $this->conn->insert_id;
    //     $stmt->close();

    //     if ($result) {
    //         // Insertar usuario
    //         $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    //         $stmtUser = $this->conn->prepare("
    //             INSERT INTO usuarios (nombre_usuario, pass, rol_id, correo, id, estado)
    //             VALUES (?, ?, 2, ?, ?, 'E')
    //         ");
    //         $stmtUser->bind_param("ssss", $username, $hashedPassword, $email, $empresa_id);
    //         $resultUser = $stmtUser->execute();

    //         $id_usuario_creado = $stmtUser->insert_id;
    //         $stmtUser->close();



    //         if ($resultUser) {
    //             //this->notificaNuevoCliente($email, $nombre, $password);
    //             //enviar correo
    //         }
    //     }

    //     if (!$result || !$resultUser) {
    //         return [
    //             "error" => true,
    //             "msg" => "No se pudo crear la empresa o el usuario asociado.",
    //             "response" => RECORD_CREATION_FAILED
    //         ];
    //     }

    //     $checkoutConToken = !empty($token)
    //         && !empty($id_membresia)
    //         && $pago_valor !== null
    //         && $valor_membresia !== null;

    //     if ($checkoutConToken) {
    //         $resultadoToken = $this->webstoreCreateRecurrente($token, $transaction_reference, $id_usuario_creado, $empresa_id);
    //         if ($resultadoToken !== RECORD_CREATED_SUCCESSFULLY) {
    //             $this->deleteRecord("usuarios", "id_usuario", $id_usuario_creado);
    //             $this->deleteRecord("empresas", "id_empresa", $empresa_id);
    //             return [
    //                 "error" => true,
    //                 "msg" => "No se pudo registrar el token de pago para la empresa.",
    //                 "response" => RECORD_CREATION_FAILED
    //             ];
    //         }

    //         $resultadoDebito = $this->debitToken(
    //             $token,
    //             $id_usuario_creado,
    //             $id_membresia,
    //             $empresa_id,
    //             $pago_valor,
    //             $tipo_pago_checkout,
    //             $meses_checkout
    //         );

    //         $debitoExitoso = false;
    //         $mensajeDebito = "Ocurrió un error al realizar el débito.";

    //         if (is_array($resultadoDebito) && isset($resultadoDebito["transaction"])) {
    //             $trx = $resultadoDebito["transaction"];
    //             if (strtolower($trx["status"] ?? '') === 'success') {
    //                 $debitoExitoso = true;
    //                 $transaction_id = $trx["id"] ?? $transaction_id;
    //                 $authorization_code = $trx["authorization_code"] ?? $authorization_code;
    //                 $payment_date = $trx["payment_date"] ?? $payment_date;
    //                 $recurrente = 'Y';
    //                 $pago_valor = $trx["amount"] ?? $pago_valor;
    //             } else {
    //                 $mensajeDebito = "Ocurrió un error al realizar el débito.\n Estado de la transacción: "
    //                     . ($trx["current_status"] ?? 'desconocido')
    //                     . ".\n Respuesta del carrier: "
    //                     . ($trx["carrier_code"] ?? 'sin carrier_code')
    //                     . ".\n Mensaje Nuvei: "
    //                     . ($trx["message"] ?? 'sin detalle');
    //             }
    //         } elseif (is_array($resultadoDebito) && isset($resultadoDebito["error"])) {
    //             $errorDebito = $resultadoDebito["error"];
    //             $mensajeDebito = "Ocurrió un error al realizar el débito.\n Tipo de error: "
    //                 . ($errorDebito["type"] ?? 'desconocido')
    //                 . ".\n Mensaje Nuvei: "
    //                 . ($errorDebito["help"] ?? 'sin detalle');
    //         } elseif (is_array($resultadoDebito)) {
    //             $mensajeDebito = "Ocurrió un error al realizar la transacción: " . json_encode($resultadoDebito, JSON_UNESCAPED_UNICODE);
    //         }

    //         if (!$debitoExitoso) {
    //             $this->deleteRecord("pagos_recurrentes", "id_empresa", $empresa_id);
    //             $this->deleteRecord("usuarios", "id_usuario", $id_usuario_creado);
    //             $this->deleteRecord("empresas", "id_empresa", $empresa_id);
    //             return [
    //                 "error" => true,
    //                 "msg" => $mensajeDebito,
    //                 "response" => RECORD_CREATION_FAILED
    //             ];
    //         }
    //     }

    //     $debeRegistrarMembresia = !empty($id_membresia)
    //         && $pago_valor !== null
    //         && $valor_membresia !== null;

    //     if ($debeRegistrarMembresia) {
    //         $resultadoMembresia = $this->membresiasUpdate(
    //             $empresa_id,
    //             $id_membresia,
    //             $id_usuario_creado,
    //             $pago_valor,
    //             $tipo,
    //             $transaction_id,
    //             $authorization_code,
    //             $recurrente,
    //             $payment_date,
    //             $valor_membresia,
    //             $nombreUsuarioLogin,
    //             $password,
    //             $promo_resumen
    //         );

    //         if (is_array($resultadoMembresia) && !empty($resultadoMembresia["error"])) {
    //             $resultadoMembresia["id_empresa"] = $empresa_id;
    //             $resultadoMembresia["id_usuario"] = $id_usuario_creado;
    //             $resultadoMembresia["response"] = RECORD_CREATION_FAILED;
    //             return $resultadoMembresia;
    //         }

    //         if ($resultadoMembresia !== RECORD_CREATED_SUCCESSFULLY) {
    //             return [
    //                 "error" => true,
    //                 "msg" => "La empresa fue creada, pero no se pudo registrar la membresía/factura.",
    //                 "id_empresa" => $empresa_id,
    //                 "id_usuario" => $id_usuario_creado,
    //                 "response" => RECORD_CREATION_FAILED
    //             ];
    //         }
    //     }

    //     return ["id_empresa" => $empresa_id, "id_usuario" => $id_usuario_creado, "response" => RECORD_CREATED_SUCCESSFULLY];
    // }

    public function createEmpresaExtendida(
        $nombre,
        $direccion,
        $whatsapp_contacto,
        $telefono_contacto,
        $username,
        $email,
        $password,
        $nombre_titular,
        $tipo_local,
        $categorias_referencia,
        $provincia,
        $canton,
        $calle_principal,
        $calle_secundaria,
        $bien_inmueble,
        $razon_social,
        $celular,
        $tipo_identificacion,
        $cedula_ruc,
        $latitud,
        $longitud,
        $sucursales,
        $direccion_facturacion,
        $telefono_facturacion = null,
        $correo_facturacion = null,
        $id_membresia = null,
        $pago_valor = null,
        $tipo = 'empresa',
        $transaction_id = '',
        $authorization_code = '',
        $recurrente = 'N',
        $payment_date = '',
        $valor_membresia = null,
        $promo_resumen = null,
        $token = '',
        $transaction_reference = '',
        $tipo_pago_checkout = 0,
        $meses_checkout = 0,
        $ultimos_digitos = null,
        $marca = null,
        $exp_year = null,
        $exp_month = null,
        $holder_name = null,
        $es_default = null,
        $gateway_uid = null,
        $nuvei_user_payment_option_id = null,
        $nuvei_user_token_id = null,
        $nuvei_session_token = null
    ) {
        try {
            $nombreUsuarioLogin = $username;
            $empresa_id = 0;
            $id_usuario_creado = 0;
            $resultUser = false;
            $empresaExiste = $this->isEmpresaExists($nombre);
            $usuarioExiste = $this->isUsuarioExists($username);
            if ($empresaExiste) {
                $resp["error"] = RECORD_ALREADY_EXISTED;
                $resp["msg"]  = "La empresa ya existe. Intente con otra.";
                return $resp;
            }

            if ($usuarioExiste) {
                $resp["error"] = RECORD_ALREADY_EXISTED;
                $resp["msg"]  = "El nombre de usuario ya existe. Intente con otro.";
                return $resp;
            }

            $ensureBillingColumns = $this->ensureEmpresaBillingColumns();
            $telefono_facturacion = trim((string)($telefono_facturacion ?? ''));
            if ($telefono_facturacion === '') {
                $telefono_facturacion = trim((string)$celular);
            }
            $correo_facturacion = trim((string)($correo_facturacion ?? ''));
            if ($correo_facturacion === '') {
                $correo_facturacion = trim((string)$email);
            }
            $facturacionPayload = [
                "razon_social" => trim((string)$razon_social),
                "tipo_identificacion" => trim((string)$tipo_identificacion),
                "cedula_ruc" => trim((string)$cedula_ruc),
                "direccion_facturacion" => trim((string)$direccion_facturacion),
                "telefono_facturacion" => $telefono_facturacion,
                "correo_facturacion" => $correo_facturacion
            ];

            $categorias_referencia = json_encode($categorias_referencia);
            $hasTelefonoFacturacion = $this->tableHasColumn('empresas', 'telefono_facturacion');
            $hasCorreoFacturacion = $this->tableHasColumn('empresas', 'correo_facturacion');

            $empresaColumns = [
                'nombre',
                'direccion',
                'whatsapp_contacto',
                'nombre_titular',
                'tipo_tienda',
                'categorias_referencia',
                'correo',
                'provincia',
                'canton',
                'calle_principal',
                'calle_secundaria',
                'bien_inmueble',
                'razon_social',
                'telefono_contacto',
                'tipo_identificacion',
                'cedula_ruc',
                'latitud',
                'longitud',
                'sucursales',
                'estado',
                'direccion_facturacion'
            ];
            $empresaValues = array_fill(0, 19, '?');
            $empresaValues[] = "'E'";
            $empresaValues[] = '?';
            $empresaParams = [
                $nombre,
                $direccion,
                $whatsapp_contacto,
                $nombre_titular,
                $tipo_local,
                $categorias_referencia,
                $email,
                $provincia,
                $canton,
                $calle_principal,
                $calle_secundaria,
                $bien_inmueble,
                $razon_social,
                $telefono_contacto,
                $tipo_identificacion,
                $cedula_ruc,
                $latitud,
                $longitud,
                $sucursales,
                $direccion_facturacion
            ];

            if ($hasTelefonoFacturacion) {
                $empresaColumns[] = 'telefono_facturacion';
                $empresaValues[] = '?';
                $empresaParams[] = $telefono_facturacion;
            }

            if ($hasCorreoFacturacion) {
                $empresaColumns[] = 'correo_facturacion';
                $empresaValues[] = '?';
                $empresaParams[] = $correo_facturacion;
            }

            $sqlEmpresa = "INSERT INTO empresas (" . implode(', ', $empresaColumns) . ") VALUES (" . implode(', ', $empresaValues) . ")";
            $stmt = $this->conn->prepare($sqlEmpresa);
            if (!$stmt) {
                return [
                    "error" => true,
                    "msg" => "No se pudo preparar el registro de la empresa.",
                    "sql_error" => $this->conn->error,
                    "sql" => preg_replace('/\s+/', ' ', trim($sqlEmpresa)),
                    "response" => RECORD_CREATION_FAILED
                ];
            }
            $stmt->bind_param(str_repeat('s', count($empresaParams)), ...$empresaParams);
            $this->conn->begin_transaction();
            $result = $stmt->execute();
            $empresaStmtError = $stmt->error;
            // Obtener el ID de la empresa recién creada (si lo necesitas para relacionar)
            $empresa_id = $this->conn->insert_id;
            $stmt->close();

            if (!$result) {
                $this->conn->rollback();
                return [
                    "error" => true,
                    "msg" => "No se pudo crear la empresa.",
                    "sql_error" => $empresaStmtError !== '' ? $empresaStmtError : $this->conn->error,
                    "sql" => preg_replace('/\s+/', ' ', trim($sqlEmpresa)),
                    "response" => RECORD_CREATION_FAILED
                ];
            }

            if ($result) {
                // Insertar usuario
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $sqlUsuario = "
                INSERT INTO usuarios (nombre_usuario, pass, rol_id, correo, id, estado)
                VALUES (?, ?, 2, ?, ?, 'E')
            ";
                $stmtUser = $this->conn->prepare($sqlUsuario);
                if (!$stmtUser) {
                    $this->conn->rollback();
                    return [
                        "error" => true,
                        "msg" => "No se pudo preparar el usuario asociado a la empresa.",
                        "sql_error" => $this->conn->error,
                        "sql" => preg_replace('/\s+/', ' ', trim($sqlUsuario)),
                        "id_empresa" => $empresa_id,
                        "response" => RECORD_CREATION_FAILED
                    ];
                }
                $stmtUser->bind_param("ssss", $username, $hashedPassword, $email, $empresa_id);
                $resultUser = $stmtUser->execute();
                $usuarioStmtError = $stmtUser->error;

                $id_usuario_creado = $stmtUser->insert_id;
                $stmtUser->close();



                if ($resultUser) {
                    //this->notificaNuevoCliente($email, $nombre, $password);
                    //enviar correo
                }
            }

            if (!$result || !$resultUser) {
                $this->conn->rollback();
                return [
                    "error" => true,
                    "msg" => !$result ? "No se pudo crear la empresa." : "No se pudo crear el usuario asociado a la empresa.",
                    "sql_error" => !$result ? ($empresaStmtError !== '' ? $empresaStmtError : $this->conn->error) : ($usuarioStmtError !== '' ? $usuarioStmtError : $this->conn->error),
                    "sql" => !$result ? preg_replace('/\s+/', ' ', trim($sqlEmpresa)) : preg_replace('/\s+/', ' ', trim($sqlUsuario)),
                    "id_empresa" => $empresa_id ?: null,
                    "response" => RECORD_CREATION_FAILED
                ];
            }


            $checkoutConToken = !empty($token)
                && !empty($id_membresia)
                && $pago_valor !== null
                && $valor_membresia !== null;

            $checkoutConNuvei = !$checkoutConToken
                && !empty($transaction_id)
                && !empty($nuvei_session_token)
                && !empty($id_membresia)
                && $pago_valor !== null
                && $valor_membresia !== null;

            if ($checkoutConToken) {
                $resultadoToken = $this->webstoreCreateRecurrente(
                    $token,
                    $transaction_reference,
                    $id_usuario_creado,
                    $empresa_id,
                    $ultimos_digitos,
                    $marca,
                    $exp_year,
                    $exp_month,
                    $es_default,
                    $gateway_uid,
                    $holder_name
                );
                if ($resultadoToken !== RECORD_CREATED_SUCCESSFULLY) {
                    $this->rollbackEmpresaCheckout($empresa_id, $id_usuario_creado, false);
                    return [
                        "error" => true,
                        "msg" => "No se pudo registrar el token de pago para la empresa.",
                        "response" => RECORD_CREATION_FAILED
                    ];
                }

                $resultadoDebito = $this->debitToken(
                    $token,
                    $id_usuario_creado,
                    $id_membresia,
                    $empresa_id,
                    $pago_valor,
                    $tipo_pago_checkout,
                    $meses_checkout,
                    true
                );

                $debitoExitoso = false;
                $mensajeDebito = "Ocurrió un error al realizar el débito.";

                if (is_array($resultadoDebito) && isset($resultadoDebito["transaction"])) {
                    $trx = $resultadoDebito["transaction"];
                    if (strtolower($trx["status"] ?? '') === 'success') {
                        $debitoExitoso = true;
                        $transaction_id = $trx["id"] ?? $transaction_id;
                        $authorization_code = $trx["authorization_code"] ?? $authorization_code;
                        $payment_date = $trx["payment_date"] ?? $payment_date;
                        $recurrente = 'Y';
                        $pago_valor = $trx["amount"] ?? $pago_valor;
                    } else {
                        $mensajeDebito = "Ocurrió un error al realizar el débito.\n Estado de la transacción: "
                            . ($trx["current_status"] ?? 'desconocido')
                            . ".\n Respuesta del carrier: "
                            . ($trx["carrier_code"] ?? 'sin carrier_code')
                            . ".\n Mensaje Nuvei: "
                            . ($trx["message"] ?? 'sin detalle');
                    }
                } elseif (is_array($resultadoDebito) && isset($resultadoDebito["error"])) {
                    $errorDebito = $resultadoDebito["error"];
                    $detalleError = $errorDebito["description"] ?? $errorDebito["help"] ?? $errorDebito["message"] ?? (is_string($resultadoDebito["error"]) ? $resultadoDebito["error"] : null) ?? 'sin detalle';
                    $mensajeDebito = "Ocurrió un error al realizar el débito.\n Tipo de error: "
                        . ($errorDebito["type"] ?? 'desconocido')
                        . ".\n Mensaje Nuvei: "
                        . $detalleError;
                } elseif (is_array($resultadoDebito)) {
                    $mensajeDebito = "Ocurrió un error al realizar la transacción: " . json_encode($resultadoDebito, JSON_UNESCAPED_UNICODE);
                }

                if (!$debitoExitoso) {
                    $this->rollbackEmpresaCheckout($empresa_id, $id_usuario_creado, true);
                    return [
                        "error" => true,
                        "msg" => $mensajeDebito,
                        "response" => RECORD_CREATION_FAILED
                    ];
                }
            }

            if ($checkoutConNuvei && !empty($nuvei_user_payment_option_id)) {
                $tokenNuvei = 'NUVEI_UPO:' . $nuvei_user_payment_option_id . ':' . (string)$nuvei_user_token_id;
                $resultadoTokenNuvei = $this->webstoreCreateRecurrente(
                    $tokenNuvei,
                    $transaction_id,
                    $id_usuario_creado,
                    $empresa_id,
                    $ultimos_digitos,
                    $marca,
                    $exp_year,
                    $exp_month,
                    $es_default,
                    $gateway_uid,
                    $holder_name
                );

                if ($resultadoTokenNuvei !== RECORD_CREATED_SUCCESSFULLY) {
                    $this->rollbackEmpresaCheckout($empresa_id, $id_usuario_creado, false);
                    return [
                        "error" => true,
                        "msg" => "El pago fue aprobado en Nuvei, pero no se pudo guardar la referencia recurrente.",
                        "response" => RECORD_CREATION_FAILED
                    ];
                }

                if (empty($payment_date)) {
                    $payment_date = date('Y-m-d H:i:s');
                }
                if (empty($recurrente)) {
                    $recurrente = 'Y';
                }
            }

            $debeRegistrarMembresia = !empty($id_membresia)
                && $pago_valor !== null
                && $valor_membresia !== null;

            if ($debeRegistrarMembresia) {
                $resultadoMembresia = $this->membresiasUpdate(
                    $empresa_id,
                    $id_membresia,
                    $id_usuario_creado,
                    $pago_valor,
                    $tipo,
                    $transaction_id,
                    $authorization_code,
                    $recurrente,
                    $payment_date,
                    $valor_membresia,
                    $nombreUsuarioLogin,
                    $password,
                    $promo_resumen,
                    $facturacionPayload
                );

                if (is_array($resultadoMembresia) && !empty($resultadoMembresia["error"])) {
                    $resultadoMembresia["id_empresa"] = $empresa_id;
                    $resultadoMembresia["id_usuario"] = $id_usuario_creado;
                    $resultadoMembresia["response"] = RECORD_CREATION_FAILED;
                    return $resultadoMembresia;
                }

                if (is_array($resultadoMembresia) && empty($resultadoMembresia["error"])) {
                    $this->conn->commit();
                    return [
                        "error" => false,
                        "msg" => $resultadoMembresia["msg"] ?? "La empresa ha sido creada con exito.",
                        "id_empresa" => $empresa_id,
                        "id_usuario" => $id_usuario_creado,
                        "factura" => $resultadoMembresia["datos"]["factura"] ?? null,
                        "datos" => $resultadoMembresia["datos"] ?? null,
                        "response" => RECORD_CREATED_SUCCESSFULLY
                    ];
                }

                if ($resultadoMembresia !== RECORD_CREATED_SUCCESSFULLY) {
                    return [
                        "error" => true,
                        "msg" => "La empresa fue creada, pero no se pudo registrar la membresía/factura.",
                        "id_empresa" => $empresa_id,
                        "id_usuario" => $id_usuario_creado,
                        "response" => RECORD_CREATION_FAILED
                    ];
                }
            }

            $this->conn->commit();
            return ["id_empresa" => $empresa_id, "id_usuario" => $id_usuario_creado, "response" => RECORD_CREATED_SUCCESSFULLY];
        } catch (\Throwable $e) {
            try {
                $this->conn->rollback();
            } catch (\Throwable $rollbackError) {
            }
            error_log('createEmpresaExtendida error: ' . $e->getMessage());
            return [
                "error" => true,
                "msg" => "createEmpresaExtendida: " . $e->getMessage(),
                "response" => RECORD_CREATION_FAILED
            ];
        }
    }




    public function getMembresias()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM membresias
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function createMembresia($nombre, $tipo, $numero, $costo, $dias_permitidos)
    {
        if (!$this->isMembresiaExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO membresias (nombre, tipo, numero, costo, dias_permitidos) VALUES(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $tipo, $numero, $costo, $dias_permitidos);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isMembresiaExists($nombre)
    {
        $stmt = $this->conn->prepare("SELECT nombre FROM membresias WHERE nombre = ? AND estado = 'A'");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    public function getMembresiaById($id_membresia, $detalle = false)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM membresias
        WHERE estado = 'A' AND id_membresia = ?");
        $stmt->bind_param("s", $id_membresia);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($detalle) {
                // $row["sucursales"] = $this->getSucursalesByEmpresa($row["id_empresa"], true);
                // $row["usuarios"] = $this->getUsuariosByEmpresa($row["id_empresa"]);
            }
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function updateMembresia($id_membresia, $nombre, $tipo, $numero, $costo, $dias_permitidos)
    {
        $stmt = $this->conn->prepare("UPDATE membresias SET nombre = ?, tipo = ?, numero = ?, costo = ?, dias_permitidos = ? WHERE id_membresia = ?");
        $stmt->bind_param("ssssss", $nombre, $tipo, $numero, $costo, $dias_permitidos, $id_membresia);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateEmpresaUbicacion($id_empresa, $latitud, $longitud, $direccion, $tipo)
    {
        // Decidir a qué tabla actualizar según el tipo
        if ($tipo === 'sucursal') {
            // Aquí $id_empresa realmente es el id_sucursal
            $sql = "UPDATE sucursales 
                SET latitud = ?, longitud = ?, direccion = ?
                WHERE id_sucursal = ?";
        } else {
            // Por defecto: empresa
            $sql = "UPDATE empresas 
                SET latitud = ?, longitud = ?, direccion = ?
                WHERE id_empresa = ?";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $latitud, $longitud, $direccion, $id_empresa);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateEmpresa(
        $id_empresa,
        $nombre = null,
        $nombre_titular = null,
        $provincia = null,
        $canton = null,
        $calle_principal = null,
        $calle_secundaria = null,
        $bien_inmueble = null,
        $whatsapp_contacto = null,
        $telefono_contacto = null,
        $correo = null,
        $img_path = null,
        $tipo_user = null,
        $red_tiktok = null,
        $red_instagram = null,
        $red_youtube = null,
        $red_facebook = null,
        $red_linkedln = null,
        $red_web = null,
        $descripcion = null
    ) {

        // 🔹 SI ES SUCURSAL → actualizar tabla sucursales
        if ($tipo_user === 'sucursal') {

            if (is_null($img_path)) {
                return RECORD_CREATION_FAILED;
            }

            $sql = "UPDATE sucursales SET imagen = ? WHERE id_sucursal = ?";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) return RECORD_CREATION_FAILED;

            $stmt->bind_param("ss", $img_path, $id_empresa);
            $ok = $stmt->execute();
            $stmt->close();

            return $ok ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
        }

        // 🔹 CASO NORMAL → actualizar empresas
        if (!is_null($descripcion)) {
            $cantidadCaracteres = mb_strlen(preg_replace('/\s+/u', '', trim((string)$descripcion)), 'UTF-8');
            if ($cantidadCaracteres > 500) {
                return RECORD_CREATION_FAILED;
            }
        }

        $fields = [];
        $params = [];
        $types  = "";

        if (!is_null($nombre)) {
            $fields[] = "nombre = ?";
            $params[] = $nombre;
            $types .= "s";
        }
        if (!is_null($nombre_titular)) {
            $fields[] = "nombre_titular = ?";
            $params[] = $nombre_titular;
            $types .= "s";
        }
        if (!is_null($provincia)) {
            $fields[] = "provincia = ?";
            $params[] = $provincia;
            $types .= "s";
        }
        if (!is_null($canton)) {
            $fields[] = "canton = ?";
            $params[] = $canton;
            $types .= "s";
        }
        if (!is_null($calle_principal)) {
            $fields[] = "calle_principal = ?";
            $params[] = $calle_principal;
            $types .= "s";
        }
        if (!is_null($calle_secundaria)) {
            $fields[] = "calle_secundaria = ?";
            $params[] = $calle_secundaria;
            $types .= "s";
        }
        if (!is_null($bien_inmueble)) {
            $fields[] = "bien_inmueble = ?";
            $params[] = $bien_inmueble;
            $types .= "s";
        }
        if (!is_null($whatsapp_contacto)) {
            $fields[] = "whatsapp_contacto = ?";
            $params[] = $whatsapp_contacto;
            $types .= "s";
        }
        if (!is_null($telefono_contacto)) {
            $fields[] = "telefono_contacto = ?";
            $params[] = $telefono_contacto;
            $types .= "s";
        }
        if (!is_null($correo)) {
            $fields[] = "correo = ?";
            $params[] = $correo;
            $types .= "s";
        }
        if (!is_null($img_path)) {
            $fields[] = "img_path = ?";
            $params[] = $img_path;
            $types .= "s";
        }
        if (!is_null($red_tiktok)) {
            $fields[] = "red_tiktok = ?";
            $params[] = $red_tiktok;
            $types .= "s";
        }
        if (!is_null($red_instagram)) {
            $fields[] = "red_instagram = ?";
            $params[] = $red_instagram;
            $types .= "s";
        }
        if (!is_null($red_youtube)) {
            $fields[] = "red_youtube = ?";
            $params[] = $red_youtube;
            $types .= "s";
        }
        if (!is_null($red_facebook)) {
            $fields[] = "red_facebook = ?";
            $params[] = $red_facebook;
            $types .= "s";
        }
        if (!is_null($red_linkedln)) {
            $fields[] = "red_linkedln = ?";
            $params[] = $red_linkedln;
            $types .= "s";
        }
        if (!is_null($red_web)) {
            $fields[] = "red_web = ?";
            $params[] = $red_web;
            $types .= "s";
        }
        if (!is_null($descripcion)) {
            $fields[] = "descripcion = ?";
            $params[] = $descripcion;
            $types .= "s";
        }

        if (count($fields) === 0) {
            return RECORD_CREATION_FAILED;
        }

        $params[] = $id_empresa;
        $types   .= "s";

        $sql = "UPDATE empresas SET " . implode(", ", $fields) . " WHERE id_empresa = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) return RECORD_CREATION_FAILED;

        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }


    // public function membresiasUpdate($id_empresa, $id_membresia, $id_usuario, $pago_valor, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $valor_membresia, $username = '', $password = '', $promo_tipo = '', $codigo_agente = '', $con_sucursales = 'N', $cobro_programado_monto = 0, $cobro_programado_dias = 0, $dias_extra_promocion = 0)
    // {

    //     // //var_dump($id_empresa, $id_membresia, $jsonArray, $id_usuario, $pago_valor, $tipo);
    //     // $verifica = $this->validaMembresia($id_empresa);
    //     // if (!$verifica) {
    //     $memb = $this->getMembresiaById($id_membresia);

    //     $fecha_actual = new DateTime();
    //     $fecha_inicio = $fecha_actual->format('Y-m-d H:i:s'); // Fecha y hora actual en formato Y-m-d H:i:s
    //     $dias_sumar = intval($memb["dias_permitidos"]);
    //     $mes_prueba = 'N';
    //     $promo_tipo = strtolower(trim((string)$promo_tipo));
    //     $codigo_agente = trim((string)$codigo_agente);
    //     $cobro_programado_monto = (float)$cobro_programado_monto;
    //     $cobro_programado_dias = (int)$cobro_programado_dias;
    //     $dias_extra_promocion = (int)$dias_extra_promocion;

    //     if ($promo_tipo === 'fulmuv' && $cobro_programado_monto > 0 && $cobro_programado_dias > 0) {
    //         $dias_sumar = 30;
    //         $mes_prueba = 'Y';
    //     } elseif ($promo_tipo === 'general_anual' && $dias_extra_promocion > 0) {
    //         $dias_sumar += $dias_extra_promocion;
    //     } elseif ($pago_valor == 1) { // compatibilidad con promos antiguas
    //         $dias_sumar = 30;
    //         $mes_prueba = 'Y';
    //     }
    //     $fecha_actual->modify("+$dias_sumar days");
    //     $fecha_fin = $fecha_actual->format('Y-m-d H:i:s'); // Fecha y hora con los días sumados
    //     // $limite_articulos = ""; // Asigna un valor si corresponde
    //     $limite_articulos = $memb["numero"];
    //     $membresia_nombre = $memb["nombre"];

    //     $stmt = $this->conn->prepare("INSERT INTO membresias_empresas (id_empresa, id_membresia, fecha_inicio, fecha_fin, limite_articulos, mes_prueba, valor_membresia) VALUES(?, ?, ?, ?, ?, ?, ?)");
    //     $stmt->bind_param("sssssss", $id_empresa, $id_membresia, $fecha_inicio, $fecha_fin, $limite_articulos, $mes_prueba, $valor_membresia);
    //     $result = $stmt->execute();
    //     $ultimo_id = $stmt->insert_id;
    //     $stmt->close();
    //     if ($result) {
    //         //activa empresa
    //         $stmt2 = $this->conn->prepare("UPDATE empresas SET estado = 'A' WHERE id_empresa = ?;");
    //         $stmt2->bind_param("s", $id_empresa);
    //         $result2 = $stmt2->execute();
    //         $stmt2->close();
    //         //activa usuario
    //         $stmt3 = $this->conn->prepare("UPDATE usuarios SET estado = 'A' WHERE id_usuario = ?;");
    //         $stmt3->bind_param("s", $id_usuario);
    //         $result3 = $stmt3->execute();
    //         $stmt3->close();
    //         $this->createPagoTransaccion($id_usuario, $id_membresia, $pago_valor, $id_empresa, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $ultimo_id);

    //         if ($promo_tipo === 'fulmuv' && $cobro_programado_monto > 0 && $cobro_programado_dias > 0) {
    //             $fechaCobro = (new DateTime($fecha_inicio))->modify('+30 days')->format('Y-m-d H:i:s');
    //             $this->crearCobroProgramadoMembresia($id_empresa, $id_usuario, $id_membresia, $ultimo_id, $codigo_agente, $promo_tipo, $cobro_programado_monto, $cobro_programado_dias, $fechaCobro, $con_sucursales);
    //         }

    //         $this->generarFacturaEmpresa($id_empresa, $pago_valor, $membresia_nombre, $dias_sumar);
    //         $this->notificaCompra($authorization_code, $transaction_id, $id_empresa, $pago_valor, $membresia_nombre, $dias_sumar);

    //         if (!empty($password)) {
    //             $empresa = $this->getEmpresaById($id_empresa);
    //             $nombreTitular = $empresa["nombre_titular"] ?? $empresa["nombre"] ?? $username;
    //             $this->notificaNuevoCliente($empresa["correo"] ?? '', $nombreTitular, $password, $username);
    //         }
    //         return RECORD_CREATED_SUCCESSFULLY;
    //     } else {
    //         return RECORD_CREATION_FAILED;
    //     }
    //     // } else {
    //     //     return RECORD_ALREADY_EXISTED;
    //     // }
    // }

    public function membresiasUpdate($id_empresa, $id_membresia, $id_usuario, $pago_valor, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $valor_membresia, $username = '', $password = '', $promo_resumen = null, $facturacion = [])
    {

        // //var_dump($id_empresa, $id_membresia, $jsonArray, $id_usuario, $pago_valor, $tipo);
        // $verifica = $this->validaMembresia($id_empresa);
        // if (!$verifica) {
        $memb = $this->getMembresiaById($id_membresia);

        $fecha_actual = new DateTime();
        $fecha_inicio = $fecha_actual->format('Y-m-d H:i:s'); // Fecha y hora actual en formato Y-m-d H:i:s
        $dias_sumar = 0;
        if ($pago_valor == 1) { //mes gratuito
            $dias_sumar = 30;
            $mes_prueba = 'Y';
        } else if ($pago_valor == 237 || $pago_valor == 297) { //mes gratuito con valor normal
            $dias_sumar = 30 + intval($memb["dias_permitidos"]);
            $mes_prueba = 'N';
        } else { //valores normal
            $dias_sumar = intval($memb["dias_permitidos"]);
            $mes_prueba = 'N';
        }
        $fecha_actual->modify("+$dias_sumar days");
        $fecha_fin = $fecha_actual->format('Y-m-d H:i:s'); // Fecha y hora con los días sumados
        // $limite_articulos = ""; // Asigna un valor si corresponde
        $limite_articulos = $memb["numero"];
        $membresia_nombre = $memb["nombre"];

        $stmt = $this->conn->prepare("INSERT INTO membresias_empresas (id_empresa, id_membresia, fecha_inicio, fecha_fin, limite_articulos, mes_prueba, valor_membresia) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_empresa, $id_membresia, $fecha_inicio, $fecha_fin, $limite_articulos, $mes_prueba, $valor_membresia);
        $result = $stmt->execute();
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            $responseOk = [
                "error" => false,
                "msg" => "Membresia registrada correctamente.",
                "datos" => [
                    "factura" => [
                        "emitida" => false,
                        "source" => "factura",
                        "msg" => "Factura pendiente de procesar."
                    ]
                ]
            ];
            //activa empresa
            $stmt2 = $this->conn->prepare("UPDATE empresas SET estado = 'A' WHERE id_empresa = ?;");
            $stmt2->bind_param("s", $id_empresa);
            $result2 = $stmt2->execute();
            $stmt2->close();
            //activa usuario
            $stmt3 = $this->conn->prepare("UPDATE usuarios SET estado = 'A' WHERE id_usuario = ?;");
            $stmt3->bind_param("s", $id_usuario);
            $result3 = $stmt3->execute();
            $stmt3->close();
            $this->createPagoTransaccion($id_usuario, $id_membresia, $pago_valor, $id_empresa, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $ultimo_id, $promo_resumen);
            $resultadoFactura = $this->generarFacturaEmpresa(
                $id_empresa,
                $pago_valor,
                $membresia_nombre,
                $dias_sumar,
                is_array($facturacion) ? $facturacion : []
            );
            if (is_array($resultadoFactura) && empty($resultadoFactura["error"])) {
                $responseOk["datos"]["factura"] = [
                    "emitida" => true,
                    "source" => $resultadoFactura["source"] ?? "factura",
                    "msg" => $resultadoFactura["msg"] ?? "Factura generada correctamente.",
                    "documento" => $resultadoFactura["documento"] ?? null,
                    "id_factura_contifico" => $resultadoFactura["id_factura_contifico"] ?? null,
                    "debug" => $resultadoFactura["debug"] ?? null
                ];
                $responseOk["msg"] = "Membresia registrada correctamente y factura generada.";
            } elseif (is_array($resultadoFactura)) {
                $responseOk["datos"]["factura"] = [
                    "emitida" => false,
                    "source" => $resultadoFactura["source"] ?? "contifico",
                    "msg" => $resultadoFactura["msg"] ?? "No se pudo generar la factura en Contifico.",
                    "debug" => $resultadoFactura["debug"] ?? null
                ];
                $responseOk["msg"] = "Membresia registrada correctamente, pero hubo un error al generar la factura.";
            }
            //$this->notificaCompra($authorization_code, $transaction_id, $id_empresa, $pago_valor, $membresia_nombre, $dias_sumar);

            if (!empty($password)) {
                $empresa = $this->getEmpresaById($id_empresa);
                $nombreTitular = $empresa["nombre_titular"] ?? $empresa["nombre"] ?? $username;
                $this->notificaNuevoCliente($empresa["correo"] ?? '', $nombreTitular, $password, $username);
            }
            return $responseOk;
        } else {
            return RECORD_CREATION_FAILED;
        }
        // } else {
        //     return RECORD_ALREADY_EXISTED;
        // }
    }
    public function validaMembresia($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM membresias_empresas WHERE id_empresa = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function isEmpresaExists($nombre)
    {
        $stmt = $this->conn->prepare("SELECT nombre FROM empresas WHERE nombre = ? AND estado = 'A'");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("UPDATE empresas SET estado = 'E' WHERE id_empresa = ?");
        $stmt->bind_param("s", $id_empresa);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    /* Tipos Establecimientos */
    public function getTiposEstablecimientos()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM establecimientos
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTipoEstablecimientoById($id_tipo)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM establecimientos
        WHERE estado = 'A' AND id_establecimiento = ?");
        $stmt->bind_param("s", $id_tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getBannerById($id_banner)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM banner
        WHERE estado = 'A' AND id_banner = ?");
        $stmt->bind_param("s", $id_banner);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function createTipoEstablecimiento($nombre)
    {
        if (!$this->isTipoEstablecimientoExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO establecimientos (descripcion) VALUES(?)");
            $stmt->bind_param("s", $nombre);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isTipoEstablecimientoExists($nombre)
    {
        $stmt = $this->conn->prepare("SELECT * FROM establecimientos WHERE descripcion = ? AND estado = 'A'");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function deleteTipoEstablecimiento($id_tipo)
    {
        $stmt = $this->conn->prepare("UPDATE establecimientos SET estado = 'E' WHERE id_establecimiento = ?");
        $stmt->bind_param("s", $id_tipo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }
    /* Tipos Establecimientos */


    /* SUCURSALES */
    public function getSucursales($id_usuario, $id_empresa)
    {
        $user_permisos = $this->getPermisosByUser($id_usuario);
        $user_permisos = array_filter($user_permisos, function ($permiso) {
            return $permiso['permiso'] == 'Ordenes';
        });
        $user_permisos = array_values($user_permisos)[0];
        $response = array();

        if ($user_permisos["valor"] == "true") {
            switch ($user_permisos["levels"]) {
                case 'Fulmuv':
                    $stmt = $this->conn->prepare("SELECT s.*, e.nombre AS empresa
                    FROM sucursales s
                    INNER JOIN empresas e ON e.id_empresa = s.id_empresa
                    WHERE s.estado = 'A'");
                    // $stmt->execute();
                    // $result = $stmt->get_result();
                    // while ($row = $result->fetch_assoc()) {
                    //     $row["areas"] = $this->getAreasBySucursal($row["id_sucursal"]);
                    //     $response[] = $row;
                    // }
                    break;
                case 'Empresa':
                    $stmt = $this->conn->prepare("SELECT s.*, e.nombre AS empresa
                    FROM sucursales s
                    INNER JOIN empresas e ON e.id_empresa = s.id_empresa
                    WHERE s.estado = 'A' AND s.id_empresa = ?");
                    $stmt->bind_param("s", $id_empresa);
                    break;

                case 'Sucursal':
                    $stmt = $this->conn->prepare("SELECT 
                        o.*, 
                        e.nombre AS empresa, 
                        s.nombre AS sucursal, 
                        CASE 
                            WHEN o.id_area = 'Todas' THEN 'Todas' 
                            ELSE a.nombre 
                        END AS area
                    FROM ordenes_empresas o
                    INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                    INNER JOIN sucursales s ON s.id_sucursal = o.id_sucursal
                    LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                    WHERE o.estado = 'A' AND o.id_sucursal = ?
                    ORDER BY o.created_at DESC;");
                    $stmt->bind_param("s", $id_empresa);
                    break;
                // case 'Vendedor':
                //     $stmt = $this->conn->prepare("SELECT 
                //         o.*, 
                //         e.nombre AS empresa, 
                //         CASE 
                //             WHEN o.id_area = 'Todas' THEN 'Todas' 
                //             ELSE a.nombre 
                //         END AS area
                //     FROM ordenes_empresas o
                //     INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                //     LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                //     WHERE o.estado = 'A' AND o.creation_user = ? ORDER BY o.created_at DESC;");
                //     $stmt->bind_param("s", $id_usuario);
                //     break;

                default:
                    break;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $date = new DateTime($row["created_at"]);
                $row["created_at"] = $date->format('M d, Y, g:i a');
                unset($row["productos"]);
                $response[] = $row;
            }
        }
        return $response;
    }

    public function getSucursalesByEmpresa($id_empresa,  $detalle = false)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT s.*, e.nombre AS empresa
        FROM sucursales s
        INNER JOIN empresas e ON e.id_empresa = s.id_empresa
        WHERE s.estado = 'A' AND s.id_empresa = ?");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($detalle) {
                $row["areas"] = $this->getAreasBySucursal($row["id_sucursal"]);
            }
            $response[] = $row;
        }
        return $response;
    }

    public function getSucursalesAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT s.*
        FROM sucursales s
        WHERE s.estado = 'A'");
        // $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {


            $response[] = $row;
        }
        return $response;
    }
    public function getSucursalById($id_sucursal)
    {
        $stmt = $this->conn->prepare("SELECT s.*, u.nombre_usuario
        FROM sucursales s
        LEFT JOIN usuarios u ON u.id = s.id_sucursal AND u.rol_id = 3 AND u.estado = 'A'
        WHERE s.estado = 'A' AND s.id_sucursal = ?");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["areas"] = $this->getAreasBySucursal($id_sucursal);
            $row["empresa"] = $this->getEmpresaById($row["id_empresa"]);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    /*public function createSucursal($id_empresa, $nombre, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble)
    {
        // 1) Verificar plan Fulmuv activo
        if (!$this->tienePlanFulmuvActivo((int)$id_empresa)) {
            return ACCESS_DENIED;
        }

        // 2) Verificar si el nombre ya existe
        if ($this->isSucursalExists($nombre, $id_empresa)) {
            return RECORD_ALREADY_EXISTED;
        }

        // 3) Contar sucursales activas de la empresa
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total 
                                    FROM sucursales 
                                    WHERE id_empresa=? AND estado='A'");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $totalSucursales = (int)$result['total'];

        // 4) Si ya tiene 1 o más, no crear gratis
        if ($totalSucursales >= 1) {
            return [
                "opciones" => $this->obtenerTarjetas($id_empresa),
                "msg" => REQUIRED_PAYMENT
            ];
        }

        // 5) Crear la primera sucursal gratis
        $stmt = $this->conn->prepare("INSERT INTO sucursales (id_empresa, nombre, provincia, canton, telefono_contacto, whatsapp_contacto, calle_principal, calle_secundaria, bien_inmueble, estado) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?,'A')");
        $stmt->bind_param("sssssssss", $id_empresa, $nombre, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }*/

    /*public function createSucursal($id_empresa,$nombre,$provincia,$canton,$telefono_contacto,$whatsapp_contacto,$calle_principal,$calle_secundaria,$bien_inmueble) {
        $id_empresa = (int)$id_empresa;

        // 1) Estado del plan
        $status = $this->getFulmuvStatus($id_empresa);

        // Debe ser FulMuv activo
        if (!$status['has_active'] || !$status['is_fulmuv']) {
            return ACCESS_DENIED;
        }

        // 2) Nombre duplicado
        if ($this->isSucursalExists($nombre, $id_empresa)) {
            return RECORD_ALREADY_EXISTED;
        }

        // 3) Si NO es anual, solo permitir 1 gratis (si ya tiene 1 o más => pago)
        if (!$status['is_annual']) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS total
                FROM sucursales
                WHERE id_empresa = ? AND estado = 'A'
            ");
            $stmt->bind_param("i", $id_empresa);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $totalSucursales = (int)$result['total'];

            if ($totalSucursales >= 1) {
                return ACCESS_DENIED;
            }
        }
        // 4) Insertar (anual = ilimitadas, no anual = primera gratis)
        $stmt = $this->conn->prepare("
            INSERT INTO sucursales
                (id_empresa, nombre, provincia, canton, telefono_contacto, whatsapp_contacto, calle_principal, calle_secundaria, bien_inmueble, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')
        ");
        // i + 8 s
        $stmt->bind_param(
            "issssssss",
            $id_empresa,
            $nombre,
            $provincia,
            $canton,
            $telefono_contacto,
            $whatsapp_contacto,
            $calle_principal,
            $calle_secundaria,
            $bien_inmueble
        );
        $ok = $stmt->execute();
        $stmt->close();

        return $ok ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }*/

    public function createSucursal($id_empresa, $nombre, $username, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble, $latitud, $longitud, $correo)
    {
        $id_empresa = (int)$id_empresa;
        $username = preg_replace('/\s+/', '', trim((string)$username));

        if ($username === '') {
            return RECORD_CREATION_FAILED;
        }

        // 1) Verificar que la empresa tenga plan FulMuv activo
        $status = $this->getFulmuvStatus($id_empresa);

        if (!$status['has_active'] || !$status['is_fulmuv']) {
            // no tiene FulMuv activo
            return ACCESS_DENIED;
        }

        // 2) Nombre duplicado
        if ($this->isSucursalExists($nombre, $id_empresa)) {
            return RECORD_ALREADY_EXISTED;
        }

        if ($this->isUsuarioExists($username)) {
            return RECORD_ALREADY_EXISTED;
        }

        // 3) ¿Esta empresa tiene sucursales ilimitadas habilitadas?
        $ilimitado = $this->empresaPermiteSucursalesIlimitadas($id_empresa);

        if (!$ilimitado) {
            // NO tiene flag 'Y' -> solo puede tener 1 sucursal activa
            /*$stmt = $this->conn->prepare("
                SELECT COUNT(*) AS total
                FROM sucursales
                WHERE id_empresa = ? AND estado = 'A'
            ");
            $stmt->bind_param("i", $id_empresa);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $totalSucursales = (int)$result['total'];

            if ($totalSucursales >= 1) {
                // ya tiene la gratis -> bloquear más
                return ACCESS_DENIED;
                // si en lugar de ACCESS_DENIED quieres mandar opciones de upsell:
                // return [
                //     "opciones" => $this->obtenerTarjetas($id_empresa),
                //     "msg" => REQUIRED_PAYMENT
                // ];
            }*/
            return ACCESS_DENIED;
        }

        // 4) Insertar sucursal nueva
        $stmt = $this->conn->prepare("
            INSERT INTO sucursales
                (id_empresa, nombre, provincia, canton, telefono_contacto, whatsapp_contacto, calle_principal, calle_secundaria, bien_inmueble, estado, latitud, longitud, correo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'A',?,?,?)
        ");

        $stmt->bind_param(
            "isssssssssss",
            $id_empresa,
            $nombre,
            $provincia,
            $canton,
            $telefono_contacto,
            $whatsapp_contacto,
            $calle_principal,
            $calle_secundaria,
            $bien_inmueble,
            $latitud,
            $longitud,
            $correo
        );

        $ok = $stmt->execute();
        $id_sucursal = $stmt->insert_id;
        $stmt->close();

        if ($ok && $id_sucursal > 0) {
            $passwordPlano = $this->generateRandomString(10);
            $passwordHash = PassHash::hash($passwordPlano);

            $stmtUser = $this->conn->prepare("
                INSERT INTO usuarios (nombre_usuario, pass, rol_id, nombres, correo, id, estado)
                VALUES (?, ?, 3, ?, ?, ?, 'A')
            ");

            if ($stmtUser) {
                $stmtUser->bind_param(
                    "ssssi",
                    $username,
                    $passwordHash,
                    $nombre,
                    $correo,
                    $id_sucursal
                );
                $resultUser = $stmtUser->execute();
                $stmtUser->close();

                if ($resultUser && !empty($correo)) {
                    $this->notificaNuevoCliente($correo, $nombre, $passwordPlano, $username, 'Usuario');
                }
            }
        }

        return $ok ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }


    public function getFulmuvStatus($id_empresa)
    {
        $sql = "
            SELECT 
                m.nombre AS plan
            FROM membresias_empresas me
            INNER JOIN membresias m ON m.id_membresia = me.id_membresia
            WHERE me.id_empresa = ?
            AND me.estado = 'A'
            AND m.estado  = 'A'
            AND NOW() BETWEEN me.fecha_inicio AND me.fecha_fin
            ORDER BY me.fecha_fin DESC
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return [
                'has_active' => false,
                'is_fulmuv'  => false
            ];
        }

        $plan = (string)$row['plan'];

        // normalizar 'FulMuv', 'FULMUV', 'Fúl Múv', etc.
        $norm = function (string $s): string {
            $s = mb_strtolower($s, 'UTF-8');
            $x = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
            return trim($x !== false ? $x : $s);
        };

        $isFulmuv = ($norm($plan) === 'fulmuv');

        return [
            'has_active' => true,
            'is_fulmuv'  => $isFulmuv
        ];
    }

    public function empresaPermiteSucursalesIlimitadas($id_empresa)
    {
        $stmt = $this->conn->prepare("
            SELECT sucursales
            FROM empresas
            WHERE id_empresa = ? AND estado = 'A'
            LIMIT 1
        ");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }

        // en tu schema 'sucursales' es varchar(1) y usas 'Y' / 'N'
        return strtoupper(trim($row['sucursales'])) === 'Y';
    }



    public function createSucursalPago($id_empresa, $nombre, $username, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble, $token, $monto, $correo = '')
    {
        $username = preg_replace('/\s+/', '', trim((string)$username));
        if ($username === '') {
            return RECORD_CREATION_FAILED;
        }
        if ($this->isUsuarioExists($username)) {
            return RECORD_ALREADY_EXISTED;
        }

        $stmt = $this->conn->prepare("INSERT INTO sucursales (id_empresa, nombre, provincia, canton, telefono_contacto, whatsapp_contacto, calle_principal, calle_secundaria, bien_inmueble, estado) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?,'A')");
        $stmt->bind_param("sssssssss", $id_empresa, $nombre, $provincia, $canton, $telefono_contacto, $whatsapp_contacto, $calle_principal, $calle_secundaria, $bien_inmueble);
        $result = $stmt->execute();
        $id_sucursal = $stmt->insert_id;
        $stmt->close();

        if ($result) {
            if ($id_sucursal > 0) {
                $passwordPlano = $this->generateRandomString(10);
                $passwordHash = PassHash::hash($passwordPlano);

                $stmtUser = $this->conn->prepare("
                    INSERT INTO usuarios (nombre_usuario, pass, rol_id, nombres, correo, id, estado)
                    VALUES (?, ?, 3, ?, ?, ?, 'A')
                ");

                if ($stmtUser) {
                    $stmtUser->bind_param(
                        "ssssi",
                        $username,
                        $passwordHash,
                        $nombre,
                        $correo,
                        $id_sucursal
                    );
                    $resultUser = $stmtUser->execute();
                    $stmtUser->close();

                    if ($resultUser && !empty($correo)) {
                        $this->notificaNuevoCliente($correo, $nombre, $passwordPlano, $username, 'Usuario');
                    }
                }
            }
            $res = $this->debitToken($token, 1, 1, $id_empresa, $monto);
            //$this->notificaCompra($res["authorization_code"], $res["id"], $id_empresa, $res["amount"]);
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }


    public function isSucursalExists($nombre, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT nombre, id_empresa 
        FROM sucursales 
        WHERE nombre=? AND id_empresa=? AND estado = 'A'");
        $stmt->bind_param("ss", $nombre, $id_empresa);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateSucursal(
        $id_sucursal,
        $nombre,
        $username,
        $provincia,
        $canton,
        $telefono_contacto,
        $whatsapp_contacto,
        $calle_principal,
        $calle_secundaria,
        $bien_inmueble,
        $latitud,
        $longitud,
        $correo
    ) {
        $id_sucursal = (int)$id_sucursal;
        $username = preg_replace('/\s+/', '', trim((string)$username));

        if ($username === '') {
            return RECORD_UPDATED_FAILED;
        }

        $stmtExists = $this->conn->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE nombre_usuario = ? AND estado = 'A' AND NOT (id = ? AND rol_id = 3)
            LIMIT 1
        ");
        if ($stmtExists) {
            $stmtExists->bind_param("si", $username, $id_sucursal);
            $stmtExists->execute();
            $exists = $stmtExists->get_result()->fetch_assoc();
            $stmtExists->close();
            if ($exists) {
                return RECORD_ALREADY_EXISTED;
            }
        }

        $stmt = $this->conn->prepare("
        UPDATE sucursales
        SET
            nombre = ?,
            provincia = ?,
            canton = ?,
            calle_principal = ?,
            calle_secundaria = ?,
            bien_inmueble = ?,
            whatsapp_contacto = ?,
            telefono_contacto = ?,
            latitud = ?,
            longitud = ?,
            correo = ?
        WHERE id_sucursal = ?
    ");

        if (!$stmt) {
            return RECORD_CREATION_FAILED; // o RECORD_UPDATE_FAILED si lo tienes
        }

        // 11 strings + 1 int (id_sucursal)
        $stmt->bind_param(
            "sssssssssssi",
            $nombre,
            $provincia,
            $canton,
            $calle_principal,
            $calle_secundaria,
            $bien_inmueble,
            $whatsapp_contacto,
            $telefono_contacto,
            $latitud,
            $longitud,
            $correo,
            $id_sucursal
        );

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $stmtUser = $this->conn->prepare("
                UPDATE usuarios
                SET nombre_usuario = ?, correo = ?, nombres = ?
                WHERE id = ? AND rol_id = 3
            ");
            if ($stmtUser) {
                $stmtUser->bind_param("sssi", $username, $correo, $nombre, $id_sucursal);
                $stmtUser->execute();
                $stmtUser->close();
            }
        }

        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
        // recomendado:
        // return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATE_FAILED;
    }

    public function deleteSucursal($id_sucursal)
    {
        $stmt = $this->conn->prepare("UPDATE sucursales SET estado = 'E' WHERE id_sucursal = ?");
        $stmt->bind_param("s", $id_sucursal);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }
    /* SUCURSALES */

    /* AREAS */
    public function getAreas()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT a.*, s.nombre AS sucursal
        FROM areas a
        INNER JOIN sucursales s ON s.id_sucursal = a.id_sucursal
        WHERE a.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getAreasBySucursal($id_sucursal)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM areas
        WHERE estado = 'A' AND id_sucursal = ?");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getAreaById($id_area)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM areas
        WHERE estado = 'A' AND id_area = ?");
        $stmt->bind_param("s", $id_area);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function createArea($id_sucursal, $nombre)
    {
        if (!$this->isAreaExists($nombre, $id_sucursal)) {
            $stmt = $this->conn->prepare("INSERT INTO areas (id_sucursal, nombre) VALUES(?, ?)");
            $stmt->bind_param("ss", $id_sucursal, $nombre);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isAreaExists($nombre, $id_sucursal)
    {
        $stmt = $this->conn->prepare("SELECT nombre, id_sucursal 
        FROM areas 
        WHERE nombre=? AND id_sucursal=? AND estado = 'A'");
        $stmt->bind_param("ss", $nombre, $id_sucursal);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateArea($id_area, $nombre)
    {
        $stmt = $this->conn->prepare("UPDATE areas SET nombre=? WHERE id_area = ?");
        $stmt->bind_param("ss", $nombre, $id_area);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function deleteArea($id_area)
    {
        $stmt = $this->conn->prepare("UPDATE areas SET estado = 'E' WHERE id_area = ?");
        $stmt->bind_param("s", $id_area);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    /* AREAS */

    /* EMPRESAS */


    /* CATALOGOS */

    public function getCatalogos($id_principal)
    {
        $permisos = $this->getPermisosByUser($id_principal);
        foreach ($permisos as $permiso) {
            if ($permiso["permiso"] == "Catalogos") {
                $nivel = $permiso["levels"];
            }
        }

        if ($nivel == "Empresa") {
            $user = $this->getUsuarioById($id_principal);
            $response = array();
            $stmt = $this->conn->prepare("SELECT c.*, s.nombre AS sucursal, e.nombre AS empresa
                FROM catalogos c
                INNER JOIN sucursales s ON s.id_sucursal = c.id_sucursal
                INNER JOIN empresas e ON e.id_empresa = s.id_empresa
                WHERE s.id_empresa = ? AND c.estado = 'A';");
            $stmt->bind_param("s", $user["id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                unset($row["productos"]);
                $response[] = $row;
            }
            return $response;
        } else if ($nivel == "Vendedor") {
            $response = array();
            $stmt = $this->conn->prepare("SELECT c.*, s.nombre AS sucursal, e.nombre AS empresa
            FROM catalogos c
            INNER JOIN sucursales s ON s.id_sucursal = c.id_sucursal
            INNER JOIN empresas e ON e.id_empresa = s.id_empresa
            WHERE c.creation_user = ? AND c.tipo = 'V' AND c.estado = 'A'");
            $stmt->bind_param("s", $id_principal);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                unset($row["productos"]);
                $response[] = $row;
            }
            return $response;
        } else {
            $response = array();
            $stmt = $this->conn->prepare("SELECT c.*, s.nombre AS sucursal, e.nombre AS empresa
            FROM catalogos c
            INNER JOIN sucursales s ON s.id_sucursal = c.id_sucursal
            INNER JOIN empresas e ON e.id_empresa = s.id_empresa
            WHERE c.estado = 'A'");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                unset($row["productos"]);
                $response[] = $row;
            }
            return $response;
        }
    }

    public function createCatalogo($nombre, $descripcion, $id_sucursal, $productos, $creation_user)
    {
        $permisos = $this->getPermisosByUser($creation_user);
        foreach ($permisos as $permiso) {
            if ($permiso["permiso"] == "Catalogos") {
                $nivel = $permiso["levels"];
            }
        }

        if ($nivel == "Vendedor") {
            if (!$this->isCatalogoExists($nombre, $id_sucursal)) {
                $default_empresa = 'true'; // Asignar valor a una variable
                // Convertir a JSON
                $jsonCatalogo = json_encode($productos);
                $stmt = $this->conn->prepare("INSERT INTO catalogos (id_sucursal, nombre, descripcion, productos, creation_user, default_empresa, tipo) VALUES(?, ?, ?, ?, ?, ?, 'V')");
                $stmt->bind_param("ssssss", $id_sucursal, $nombre, $descripcion, $jsonCatalogo, $creation_user, $default_empresa);
                $result = $stmt->execute();
                $stmt->close();
                if ($result) {
                    return RECORD_CREATED_SUCCESSFULLY;
                } else {
                    return RECORD_CREATION_FAILED;
                }
            }
        } else {
            if (!$this->isCatalogoExists($nombre, $id_sucursal)) {
                $default_empresa = 'true'; // Asignar valor a una variable
                // Convertir a JSON
                $jsonCatalogo = json_encode($productos);
                $stmt = $this->conn->prepare("INSERT INTO catalogos (id_sucursal, nombre, descripcion, productos, creation_user, default_empresa) VALUES(?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $id_sucursal, $nombre, $descripcion, $jsonCatalogo, $creation_user, $default_empresa);
                $result = $stmt->execute();
                $stmt->close();
                if ($result) {
                    return RECORD_CREATED_SUCCESSFULLY;
                } else {
                    return RECORD_CREATION_FAILED;
                }
            } else {
                return RECORD_ALREADY_EXISTED;
            }
        }
    }
    public function isCatalogoExists($nombre, $id_sucursal)
    {
        $stmt = $this->conn->prepare("SELECT nombre, id_sucursal FROM catalogos WHERE nombre = ? AND id_sucursal=? AND estado = 'A'");
        $stmt->bind_param("ss", $nombre, $id_sucursal);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getCatalogoById($id_catalogo, $detalle = true)
    {
        $stmt = $this->conn->prepare("SELECT c.*, s.nombre AS sucursal, e.nombre AS empresa, e.id_empresa
        FROM catalogos c
        INNER JOIN sucursales s ON s.id_sucursal = c.id_sucursal
        INNER JOIN empresas e ON e.id_empresa = s.id_empresa
        WHERE c.estado = 'A'AND id_catalogo = ?");
        $stmt->bind_param("s", $id_catalogo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($detalle) {
                // 
                $row["productos"] = json_decode($row['productos'], true);
                foreach ($row["productos"] as $key => &$pro) {
                    $producto = $this->getProductoById($pro["id_producto"]);
                    if ($producto != RECORD_DOES_NOT_EXIST && $producto["estado"] == "A") {
                        // Eliminar detalles no deseados del producto
                        unset($producto["id_producto"]);
                        unset($producto["precio_referencia"]);
                        unset($producto["ficha_tecnica"]);
                        unset($producto["created_at"]);
                        unset($producto["updated_at"]);
                        // Unir el array del producto con sus detalles
                        $pro = array_merge($pro, $producto);
                    } else {
                        // Quitar el producto del array si no existe
                        unset($row["productos"][$key]);
                    }
                }
                $row["membresia"] = $this->getMembresiaByEmpresa($row['id_empresa']);
            } else {
                unset($row["productos"]);
            }
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getProductosCatalogoByIdSucursal($id_sucursal, $ids_productos)
    {
        $stmt = $this->conn->prepare("SELECT c.productos
        FROM catalogos c
        WHERE c.estado = 'A' AND id_sucursal = ?");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["productos"] = json_decode($row['productos'], true);

            foreach ($row["productos"] as $key => &$pro) {

                if (in_array($pro["id_producto"], $ids_productos)) {

                    $producto = $this->getProductoById($pro["id_producto"]);
                    if ($producto != RECORD_DOES_NOT_EXIST && $producto["estado"] == "A") {
                        // Eliminar detalles no deseados del producto
                        unset($producto["precio_referencia"]);
                        unset($producto["created_at"]);
                        unset($producto["updated_at"]);
                        unset($producto["descripcion"]);
                        unset($producto["codigo"]);
                        unset($producto["categoria"]);
                        unset($producto["ficha_tecnica"]);
                        unset($producto["nombre_categoria"]);
                        unset($producto["nombre_sub_categoria"]);
                        unset($producto["sub_categoria"]);
                        unset($producto["tags"]);
                        // Unir el array del producto con sus detalles
                        $pro = array_merge($pro, $producto);
                    } else {
                        // Quitar el producto del array si no existe
                        unset($row["productos"][$key]);
                    }
                }
            }
            return $row["productos"];
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getProductosGeneral($id_catalogo, $ids_productos)
    {
        $stmt = $this->conn->prepare("SELECT c.productos
        FROM catalogos c
        WHERE c.estado = 'A' AND id_catalogo = ?");
        $stmt->bind_param("s", $id_catalogo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["productos"] = json_decode($row['productos'], true);

            foreach ($row["productos"] as $key => &$pro) {

                if (in_array($pro["id_producto"], $ids_productos)) {

                    $producto = $this->getProductoById($pro["id_producto"]);
                    if ($producto != RECORD_DOES_NOT_EXIST && $producto["estado"] == "A") {
                        // Eliminar detalles no deseados del producto
                        unset($producto["precio_referencia"]);
                        unset($producto["created_at"]);
                        unset($producto["updated_at"]);
                        unset($producto["descripcion"]);
                        unset($producto["codigo"]);
                        unset($producto["categoria"]);
                        unset($producto["ficha_tecnica"]);
                        unset($producto["nombre_categoria"]);
                        unset($producto["nombre_sub_categoria"]);
                        unset($producto["sub_categoria"]);
                        unset($producto["tags"]);
                        // Unir el array del producto con sus detalles
                        $pro = array_merge($pro, $producto);
                    } else {
                        // Quitar el producto del array si no existe
                        unset($row["productos"][$key]);
                    }
                }
            }
            return $row["productos"];
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getCatalogoByIdSucursal($id_sucursal)
    {
        $stmt = $this->conn->prepare("SELECT c.*
        FROM catalogos c
        WHERE c.estado = 'A' AND id_sucursal = ? AND c.tipo = 'G';");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["productos"] = json_decode($row['productos'], true);

            foreach ($row["productos"] as $key => &$pro) {
                $producto = $this->getProductoById($pro["id_producto"]);
                if ($producto != RECORD_DOES_NOT_EXIST) {
                    // Eliminar detalles no deseados del producto
                    unset($producto["precio_referencia"]);
                    unset($producto["created_at"]);
                    unset($producto["updated_at"]);
                    // Unir el array del producto con sus detalles
                    $pro = array_merge($pro, $producto);
                } else {
                    // Quitar el producto del array si no existe
                    unset($row["productos"][$key]);
                }
            }
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getCatalogoByIdSucursalVendedores($id_sucursal)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT c.*
        FROM catalogos c
        WHERE c.estado = 'A' AND c.id_sucursal = ? AND c.tipo = 'V';");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {

                $row["productos"] = json_decode($row['productos'], true);

                foreach ($row["productos"] as $key => &$pro) {
                    $producto = $this->getProductoById($pro["id_producto"]);
                    if ($producto != RECORD_DOES_NOT_EXIST) {
                        // Eliminar detalles no deseados del producto
                        unset($producto["precio_referencia"]);
                        unset($producto["created_at"]);
                        unset($producto["updated_at"]);
                        // Unir el array del producto con sus detalles
                        $pro = array_merge($pro, $producto);
                    } else {
                        // Quitar el producto del array si no existe
                        unset($row["productos"][$key]);
                    }
                }

                $response[] = $row;
            }
            return $response;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function updateCatalogo($id_catalogo, $descripcion, $productos)
    {
        $jsonCatalogo = json_encode($productos);
        $stmt = $this->conn->prepare("UPDATE catalogos SET descripcion=?, productos=? WHERE id_catalogo = ?");
        $stmt->bind_param("sss", $descripcion, $jsonCatalogo, $id_catalogo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function deleteCatalogo($id_catalogo)
    {
        $stmt = $this->conn->prepare("UPDATE catalogos SET estado = 'E' WHERE id_catalogo = ?");
        $stmt->bind_param("s", $id_catalogo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    /* public function getProductosByCatalogo($id_catalogo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT p.*, c.nombre AS nombre_categoria, s.nombre AS nombre_sub_categoria, cp.precio
        FROM catalogo_productos cp
        INNER JOIN productos p ON p.id_producto = cp.id_producto
        INNER JOIN categorias c ON c.id_categoria = p.categoria
        INNER JOIN sub_categorias s ON s.id_sub_categoria = p.sub_categoria
        WHERE cp.estado = 'A' AND id_catalogo = ?");
        $stmt->bind_param("s", $id_catalogo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            unset($row["precio_referencia"]);
            $response[] = $row;
        }
        return $response;
    } */

    /* CATALOGOS */

    /* PRODUCTOS */

    // ✅ Normaliza ids recibidos como array PHP, JSON string o "1,2,3"
    public function normalizeIds($ids): array
    {
        if (!$ids) return [];

        if (is_string($ids)) {
            $ids = json_decode($ids, true);
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
        }

        if (!is_array($ids)) return [];

        // Limpia y solo deja números positivos
        return array_values(
            array_filter(array_map('intval', $ids), fn($v) => $v > 0)
        );
    }


    // ✅ Bind dinámico para N parámetros
    private function bindMany(\mysqli_stmt $stmt, array $vals): void
    {
        if (empty($vals)) return;
        $types = str_repeat('i', count($vals)); // ids numéricos
        $stmt->bind_param($types, ...$vals);
    }

    // ✅ Trae categorías por arreglo de IDs
    public function getCategoriaByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_categoria AS id, nombre, tipo
        FROM categorias
        WHERE estado = 'A' AND id_categoria IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[(int)$r['id']] = $r; // ['id'=>..., 'nombre'=>...]
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($out[$id])) {
                $ordered[] = $out[$id];
            }
        }
        return $ordered;
    }

    public function getCategoriaServicioByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_categoria AS id, nombre, tipo
        FROM categorias
        WHERE estado = 'A' AND tipo = 'servicio' AND id_categoria IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }


    // ✅ Trae subcategorías por arreglo de IDs
    public function getSubCategoriaByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_sub_categoria AS id, nombre
        FROM sub_categorias
        WHERE estado = 'A' AND id_sub_categoria IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    // ✅ Trae MARCAS por arreglo de IDs
    public function getMarcaByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_marca AS id, nombre
        FROM marcas
        WHERE estado = 'A' AND id_marca IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getModeloByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
       SELECT id_modelos_autos AS id, nombre, id_marca as id_marc
        FROM modelos_autos
        WHERE estado = 'A' AND id_modelos_autos IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getTipoAutoByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_tipo_auto AS id, nombre
        FROM tipos_auto
        WHERE estado = 'A' AND id_tipo_auto IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getTipoTraccionByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_tipo_traccion AS id, nombre
        FROM tipo_traccion
        WHERE estado = 'A' AND id_tipo_traccion IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }


    // ✅ Tu método principal, enviando el array/JSON tal cual llega desde productos
    public function getProductos($id_empresa, $tipo)
    {
        $response = [];
        $stmt = $this->conn->prepare("
            SELECT p.*
            FROM productos p
            WHERE p.estado = 'A' AND p.id_empresa = ? AND p.tipo_creador = ?
        ");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("ss", $id_empresa, $tipo);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Nota: p.categoria y p.sub_categoria están guardados como JSON (e.g. ["1","3"])
            $row['archivos']      = $this->getArchivosByProductos($row['id_producto']);
            $row['categorias']    = $this->getCategoriaByArray($row['categoria']);       // 👈 le paso el JSON/array
            $row['subcategorias'] = $this->getSubCategoriaByArray($row['sub_categoria']); // 👈 idem

            if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "producto") {
                $response[] = $row;
            }

            // $response[] = $row;
        }
        return $response;
    }

    public function getProductosFiltro($id_empresa, $tipo, $consulta)
    {
        $response = [];
        if ($consulta == "0") {
            $consulta = '%';
        } else {
            $consulta = '%' . $consulta . '%';
        }
        $stmt = $this->conn->prepare("
            SELECT p.*
            FROM productos p
            WHERE p.estado = 'A' AND p.id_empresa = ? AND p.tipo_creador = ? AND p.titulo_producto LIKE ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("sss", $id_empresa, $tipo, $consulta);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Nota: p.categoria y p.sub_categoria están guardados como JSON (e.g. ["1","3"])
            $row['archivos']      = $this->getArchivosByProductos($row['id_producto']);
            $row['categorias']    = $this->getCategoriaByArray($row['categoria']);       // 👈 le paso el JSON/array
            $row['subcategorias'] = $this->getSubCategoriaByArray($row['sub_categoria']); // 👈 idem

            if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "producto") {
                $response[] = $row;
            }

            // $response[] = $row;
        }
        return $response;
    }

    public function getProductosAllTipo($id_empresa, $tipo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT p.*, c.nombre AS nombre_categoria, s.nombre AS nombre_sub_categoria
        FROM productos p
        INNER JOIN categorias c ON c.id_categoria = p.categoria
        INNER JOIN sub_categorias s ON s.id_sub_categoria = p.sub_categoria
        WHERE p.estado = 'A' AND c.tipo = ? AND p.id_empresa = ?;");
        $stmt->bind_param("ss", $tipo, $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);

            $response[] = $row;
        }
        return $response;
    }

    public function getServicios($id_empresa, $tipo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT p.*
        FROM productos p
        WHERE p.estado = 'A' AND p.id_empresa = ? AND p.tipo_creador = ?;");
        $stmt->bind_param("ss", $id_empresa, $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            $row['categorias']    = $this->getCategoriaByArray($row['categoria']);       // 👈 le paso el JSON/array
            if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "servicio") {
                $response[] = $row;
            }
            // $response[] = $row;
        }
        return $response;
    }

    public function getProductosBulk($ids_productos)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT p.id_producto, p.nombre, p.img_path
        FROM productos p
        WHERE p.estado = 'A' AND p.id_producto IN ($ids_productos)");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    // public function createProducto($nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $archivos, $id_empresa, $atributos, $img_frontal, $img_posterior, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $peso, $titulo_producto, $marca_producto, $iva, $negociable, $tipo_creador, $emergencia_24_7, $emergencia_carretera, $emergencia_domicilio, $referencias)
    // {
    //     if (!$this->isProductoExists($titulo_producto, $codigo, $id_empresa)) {
    //         $jsonAtributos = json_encode($atributos);
    //         $categoria = json_encode($categoria);
    //         $sub_categoria = json_encode($sub_categoria);
    //         $tipo_vehiculo = json_encode($tipo_vehiculo);
    //         $marca = json_encode($marca);
    //         $traccion = json_encode($traccion);
    //         $modelo = json_encode($modelo);
    //         var_dump($referencias);
    //         $stmt = $this->conn->prepare("INSERT INTO productos (nombre, descripcion, codigo, categoria, sub_categoria, tags, precio_referencia, id_empresa, detalle_producto, img_frontal, img_posterior, descuento, tipo_auto, id_modelo, id_marca, tipo_traccion, peso, titulo_producto, marca_producto, iva, negociable, tipo_creador, emergencia_24_7, emergencia_carretera, emergencia_domicilio, referencias) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmt->bind_param("sssssssssssdssssdssiisiiis", $nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $id_empresa, $jsonAtributos, $img_frontal, $img_posterior, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $peso, $titulo_producto, $marca_producto, $iva, $negociable, $tipo_creador, $emergencia_24_7, $emergencia_carretera, $emergencia_domicilio, $referencias);
    //         $result = $stmt->execute();
    //         if (!$result) {
    //             // Error al ejecutar
    //             echo "Error en execute(): " . $stmt->error;
    //         }

    //         $ultimo_id = $stmt->insert_id;
    //         $stmt->close();
    //         if ($result) {
    //             // Insertar archivos si existen
    //             if (!empty($archivos) && is_array($archivos)) {
    //                 $stmtArchivos = $this->conn->prepare("INSERT INTO archivos_productos (id_producto, archivo, tipo) VALUES (?, ?, ?)");
    //                 foreach ($archivos["archivos"] as $archivoData) {
    //                     $rutaArchivo = $archivoData['archivo'];
    //                     $tipoArchivo = $archivoData['tipo'];
    //                     $stmtArchivos->bind_param("iss", $ultimo_id, $rutaArchivo, $tipoArchivo);
    //                     $stmtArchivos->execute();
    //                 }
    //                 $stmtArchivos->close();
    //             }
    //             return RECORD_CREATED_SUCCESSFULLY;
    //         } else {
    //             return RECORD_CREATION_FAILED;
    //         }
    //     } else {
    //         return RECORD_ALREADY_EXISTED;
    //     }
    // }

    public function createProducto(
        $nombre,
        $descripcion,
        $codigo,
        $categoria,
        $sub_categoria,
        $tags,
        $precio_referencia,
        $archivos,
        $id_empresa,
        $atributos,
        $img_frontal,
        $img_posterior,
        $descuento,
        $tipo_vehiculo,
        $modelo,
        $marca,
        $traccion,
        $peso,
        $titulo_producto,
        $marca_producto,
        $iva,
        $negociable,
        $tipo_creador,
        $emergencia_24_7,
        $emergencia_carretera,
        $emergencia_domicilio,
        $referencias,
        $estado,
        $tipo_producto,
        $funcionamiento_motor
    ) {
        if (!$this->isProductoExists($titulo_producto, $codigo, $id_empresa)) {

            $jsonAtributos  = json_encode($atributos, JSON_UNESCAPED_UNICODE);
            $categoria      = json_encode($categoria, JSON_UNESCAPED_UNICODE);
            $sub_categoria  = json_encode($sub_categoria, JSON_UNESCAPED_UNICODE);
            $tipo_vehiculo  = json_encode($tipo_vehiculo, JSON_UNESCAPED_UNICODE);
            $marca          = json_encode($marca, JSON_UNESCAPED_UNICODE);
            $traccion       = json_encode($traccion, JSON_UNESCAPED_UNICODE);
            $modelo         = json_encode($modelo, JSON_UNESCAPED_UNICODE);
            $funcionamiento_motor         = json_encode($funcionamiento_motor, JSON_UNESCAPED_UNICODE);


            if (!is_array($referencias)) {
                // por si llega string o null
                $referencias = $referencias ? [$referencias] : [];
            }
            $jsonReferencias = json_encode($referencias, JSON_UNESCAPED_UNICODE);

            $stmt = $this->conn->prepare("
            INSERT INTO productos (
                nombre, descripcion, codigo, categoria, sub_categoria, tags,
                precio_referencia, id_empresa, detalle_producto, img_frontal, img_posterior,
                descuento, tipo_auto, id_modelo, id_marca, tipo_traccion, peso,
                titulo_producto, marca_producto, iva, negociable, tipo_creador,
                emergencia_24_7, emergencia_carretera, emergencia_domicilio, referencias, estado, tipo_producto, funcionamiento_motor
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

            $stmt->bind_param(
                "sssssssssssdssssdssiisiiissss",
                $nombre,
                $descripcion,
                $codigo,
                $categoria,
                $sub_categoria,
                $tags,
                $precio_referencia,
                $id_empresa,
                $jsonAtributos,
                $img_frontal,
                $img_posterior,
                $descuento,
                $tipo_vehiculo,
                $modelo,
                $marca,
                $traccion,
                $peso,
                $titulo_producto,
                $marca_producto,
                $iva,
                $negociable,
                $tipo_creador,
                $emergencia_24_7,
                $emergencia_carretera,
                $emergencia_domicilio,
                $jsonReferencias,
                $estado,
                $tipo_producto,
                $funcionamiento_motor
            );

            $result = $stmt->execute();
            if (!$result) {
                echo "Error en execute(): " . $stmt->error;
            }

            $ultimo_id = $stmt->insert_id;
            $stmt->close();

            if ($result) {
                if (!empty($archivos) && is_array($archivos)) {
                    $stmtArchivos = $this->conn->prepare("
                    INSERT INTO archivos_productos (id_producto, archivo, tipo)
                    VALUES (?, ?, ?)
                ");
                    foreach ($archivos["archivos"] as $archivoData) {
                        $rutaArchivo = $archivoData['archivo'];
                        $tipoArchivo = $archivoData['tipo'];
                        $stmtArchivos->bind_param("sss", $ultimo_id, $rutaArchivo, $tipoArchivo);
                        $stmtArchivos->execute();
                    }
                    $stmtArchivos->close();
                }
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }


    public function editProducto(
        $nombre,
        $descripcion,
        $codigo,
        $categoria,
        $sub_categoria,
        $tags,
        $precio_referencia,
        $archivos,
        $id_empresa,
        $atributos,
        $img_frontal,
        $img_posterior,
        $descuento,
        $tipo_vehiculo,
        $modelo,
        $marca,
        $traccion,
        $peso,
        $titulo_producto,
        $marca_producto,
        $iva,
        $negociable,
        $tipo_creador,
        $emergencia_24_7,
        $emergencia_carretera,
        $emergencia_domicilio,
        $referencias,
        $imagenFrontalEdit,
        $imagenPosteriorEdit,
        $funcionamiento_motor,
        $id_producto
    ) {
        // ======= ENCODEO IGUAL QUE ANTES =======
        $jsonAtributos  = json_encode($atributos, JSON_UNESCAPED_UNICODE);
        $categoria      = json_encode($categoria, JSON_UNESCAPED_UNICODE);
        $sub_categoria  = json_encode($sub_categoria, JSON_UNESCAPED_UNICODE);
        $tipo_vehiculo  = json_encode($tipo_vehiculo, JSON_UNESCAPED_UNICODE);
        $marca          = json_encode($marca, JSON_UNESCAPED_UNICODE);
        $traccion       = json_encode($traccion, JSON_UNESCAPED_UNICODE);
        $modelo         = json_encode($modelo, JSON_UNESCAPED_UNICODE);
        $funcionamiento_motor         = json_encode($funcionamiento_motor, JSON_UNESCAPED_UNICODE);

        if (!is_array($referencias)) {
            $referencias = $referencias ? [$referencias] : [];
        }
        $jsonReferencias = json_encode($referencias, JSON_UNESCAPED_UNICODE);

        // ======= ARMAR SQL DINÁMICO (SIN IMÁGENES) =======
        $sql = "
        UPDATE productos SET
            nombre = ?,
            descripcion = ?,
            codigo = ?,
            categoria = ?,
            sub_categoria = ?,
            tags = ?,
            precio_referencia = ?,
            id_empresa = ?,
            detalle_producto = ?,
            descuento = ?,
            tipo_auto = ?,
            id_modelo = ?,
            id_marca = ?,
            tipo_traccion = ?,
            peso = ?,
            titulo_producto = ?,
            marca_producto = ?,
            iva = ?,
            negociable = ?,
            tipo_creador = ?,
            emergencia_24_7 = ?,
            emergencia_carretera = ?,
            emergencia_domicilio = ?,
            referencias = ?,
            funcionamiento_motor = ?
    ";

        // tipos base (sin img_frontal / img_posterior)
        // nombre s
        // descripcion s
        // codigo s
        // categoria s
        // sub_categoria s
        // tags s
        // precio_referencia s
        // id_empresa s
        // detalle_producto s
        // descuento d
        // tipo_auto s
        // id_modelo s
        // id_marca s
        // tipo_traccion s
        // peso d
        // titulo_producto s
        // marca_producto s
        // iva i
        // negociable i
        // tipo_creador s
        // emergencia_24_7 i
        // emergencia_carretera i
        // emergencia_domicilio i
        // referencias s
        $types  = "sssssssssdssssdssiisiiiss";

        // parámetros base
        $params = [
            &$nombre,
            &$descripcion,
            &$codigo,
            &$categoria,
            &$sub_categoria,
            &$tags,
            &$precio_referencia,
            &$id_empresa,
            &$jsonAtributos,
            &$descuento,
            &$tipo_vehiculo,
            &$modelo,
            &$marca,
            &$traccion,
            &$peso,
            &$titulo_producto,
            &$marca_producto,
            &$iva,
            &$negociable,
            &$tipo_creador,
            &$emergencia_24_7,
            &$emergencia_carretera,
            &$emergencia_domicilio,
            &$jsonReferencias,
            &$funcionamiento_motor
        ];

        // ======= CAMPOS DE IMAGEN SEGÚN LOS FLAGS =======
        // imagenFrontalEdit = 1  => actualizar img_frontal
        if ((int)$imagenFrontalEdit === 1) {
            $sql   .= ", img_frontal = ?";
            $types .= "s";
            $params[] = &$img_frontal;
        }

        // imagenPosteriorEdit = 1 => actualizar img_posterior
        if ((int)$imagenPosteriorEdit === 1) {
            $sql   .= ", img_posterior = ?";
            $types .= "s";
            $params[] = &$img_posterior;
        }

        // ======= WHERE =======
        $sql   .= " WHERE id_producto = ?";
        $types .= "i";
        $params[] = &$id_producto;

        // ======= PREPARE & BIND DINÁMICO =======
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // error al preparar
            return RECORD_CREATION_FAILED;
        }

        // bind_param con número variable de parámetros
        array_unshift($params, $types); // primer parámetro es la cadena de tipos
        call_user_func_array([$stmt, 'bind_param'], $params);

        $result = $stmt->execute();
        if (!$result) {
            // Puedes loguear si quieres:
            // error_log("Error editProducto: " . $stmt->error);
            $stmt->close();
            return RECORD_CREATION_FAILED;
        }

        $stmt->close();

        // ======= ARCHIVOS DE GALERÍA (AGREGAR NUEVOS) =======
        if (!empty($archivos) && is_array($archivos)) {
            $stmtArchivos = $this->conn->prepare("
            INSERT INTO archivos_productos (id_producto, archivo, tipo)
            VALUES (?, ?, ?)
        ");
            if ($stmtArchivos) {
                foreach ($archivos["archivos"] as $archivoData) {
                    $rutaArchivo = $archivoData['archivo'];
                    $tipoArchivo = $archivoData['tipo'];
                    $stmtArchivos->bind_param("sss", $id_producto, $rutaArchivo, $tipoArchivo);
                    $stmtArchivos->execute();
                }
                $stmtArchivos->close();
            }
        }

        return RECORD_CREATED_SUCCESSFULLY; // o algún RECORD_UPDATED_SUCCESSFULLY si lo tienes definido
    }


    public function createServicio($nombre, $descripcion, $categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $atributos)
    {
        if (!$this->isServicioExists($nombre, $id_empresa)) {
            $jsonAtributos = json_encode($atributos);
            $stmt = $this->conn->prepare("INSERT INTO servicios (nombre, descripcion, categoria, tags, precio_referencia, img_path, ficha_tecnica, id_empresa, detalle_servicio) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $nombre, $descripcion, $categoria, $tags, $precio_referencia, $img_path, $ficha_tecnica, $id_empresa, $jsonAtributos);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function updateProducto($id_producto, $nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $detalle_producto, $peso)
    {
        $jsonAtributos = json_encode($detalle_producto);
        $stmt = $this->conn->prepare("UPDATE productos SET nombre=?, descripcion=?, codigo=?, categoria=?, sub_categoria=?, tags=?, precio_referencia=?, detalle_producto=?, peso = ? WHERE id_producto = ?");
        $stmt->bind_param("ssssssssds", $nombre, $descripcion, $codigo, $categoria, $sub_categoria, $tags, $precio_referencia, $jsonAtributos, $peso, $id_producto);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    public function updateProductoAtributo($id_producto, $detalle_producto)
    {
        $jsonAtributos = json_encode($detalle_producto);
        $stmt = $this->conn->prepare("UPDATE productos SET detalle_producto=? WHERE id_producto = ?");
        $stmt->bind_param("ss", $jsonAtributos, $id_producto);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    public function getMembresiaBySucursal($id_sucursal)
    {
        $stmt = $this->conn->prepare("SELECT m.*
        FROM sucursales s
        INNER JOIN empresas e ON s.id_empresa = e.id_empresa
        INNER JOIN membresias_empresas me ON me.id_empresa = e.id_empresa
        INNER JOIN membresias m ON m.id_membresia = me.id_membresia
        WHERE s.id_sucursal = ? AND s.estado = 'A' and e.estado = 'A' and me.estado = 'A';");
        $stmt->bind_param("s", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["estado_membresia"] = 'ACTIVA';
            return $row;
        }
    }

    public function isProductoExists($nombre, $codigo, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT titulo_producto, codigo FROM productos WHERE titulo_producto = ? AND id_empresa = ? AND codigo = ? AND estado = 'A'");
        $stmt->bind_param("sss", $nombre, $id_empresa, $codigo);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function isServicioExists($nombre, $id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT nombre FROM servicios WHERE nombre = ? AND id_empresa = ? AND estado = 'A'");
        $stmt->bind_param("ss", $nombre, $id_empresa);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getProductoById($id_producto)
    {
        $stmt = $this->conn->prepare("SELECT p.*
        FROM productos p
        WHERE p.id_producto = ? and estado = 'A'");
        $stmt->bind_param("s", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["id_categoria"] = $row["categoria"];
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            $row["empresa"] = $this->getEmpresaById($row["id_empresa"]);
            $row["categorias"] = $this->getCategoriaByArray($row['categoria']);
            $row["subcategorias"] = $this->getSubCategoriaByArray($row['sub_categoria']);
            $row["categoria"] = $row["categorias"];
            $row["marca"] = $this->getMarcaByArray($row["id_marca"]);
            $row["modelo"] = $this->getModeloByArray($row["id_modelo"]);
            $row["tipo_autoo"] = $this->getTipoAutoByArray($row["tipo_auto"]);
            $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
            $row["motor"] = $this->getFuncionamientoMotorByArray($row["funcionamiento_motor"]);
            $row["marca_productos"] = $this->getMarcaproductoById($row["marca_producto"]);
            $row["modelo_producto"] = $this->getModeloByArray($row["id_modelo"]);

            $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);


            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }


    public function getArchivosByProductos($id_producto)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM archivos_productos
        WHERE id_producto = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    public function getMarcaproductoById($id_marca_producto)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM marcas_productos
        WHERE id_marca_producto = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_marca_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getArchivosByEmpresa($id_producto)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM 
        WHERE id_producto = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function filesEmpresa($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM archivos_empresa
        WHERE id_empresa = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function fileEmpresaById($id_archivo)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM archivos_empresa
        WHERE id_archivo_empresa = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_archivo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    public function getArchivosByVehiculos($id_vehiculo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM archivos_vehiculos
        WHERE id_vehiculo = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_vehiculo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getArchivosByEmpleos($id_empleo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM archivos_empleos
        WHERE id_empleo = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_empleo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getServicioById($id_servicio)
    {
        $stmt = $this->conn->prepare("SELECT p.*, c.nombre AS nombre_categoria
        FROM productos p
        INNER JOIN categorias c ON c.id_categoria = p.categoria
        WHERE p.id_producto = ?");
        $stmt->bind_param("s", $id_servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["id_categoria"] = $row["categoria"];
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            $row["empresa"] = $this->getEmpresaById($row["id_empresa"]);
            $row["categoria"] = $this->getCategoriaByIdEmpresa($row["id_empresa"]);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function deleteProducto($id_producto)
    {
        $stmt = $this->conn->prepare("UPDATE productos SET estado = 'E' WHERE id_producto = ?");
        $stmt->bind_param("s", $id_producto);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    /* PRODUCTOS */


    /* CATEGORIAS */
    public function getCategorias($tipo, $id_empresa = null, $tipo_usuario = 'empresa')
    {
        $response = array();
        $tipo = (string)$tipo;
        $sql = "SELECT * FROM categorias c WHERE tipo = ? AND c.estado = 'A'";
        $params = [$tipo];
        $types = "s";

        if (!empty($id_empresa)) {
            $membresia = $this->getMembresiaActivaPorContexto((int)$id_empresa, (string)$tipo_usuario);
            $plan = $this->normalizarNombreMembresia($membresia['nombre'] ?? '');

            if ($plan === 'basicmuv') {
                if ($tipo === 'producto') {
                    return [];
                }

                if ($tipo === 'servicio') {
                    $permitidas = $this->getCategoriasPermitidasBasicMuv();
                    $placeholders = implode(',', array_fill(0, count($permitidas), '?'));
                    $sql .= " AND c.nombre IN ($placeholders)";
                    $params = array_merge($params, $permitidas);
                    $types .= str_repeat("s", count($permitidas));
                }
            }
        }

        $stmt = $this->conn->prepare($sql);
        $this->bindDynamicParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $row["sub_categorias"] = $this->getSubCategoriaByCategoria($row["id_categoria"]);


            $response[] = $row;
        }
        return $response;
    }

    private function bindDynamicParams($stmt, $types, $params)
    {
        if (empty($params)) {
            return;
        }

        $bindNames[] = $types;
        foreach ($params as $key => $value) {
            $bindNames[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindNames);
    }

    public function getCategoriasByPrincipal($id_categoria_principal)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM categorias c
        WHERE JSON_CONTAINS(c.categoria_principal, ?, '$') AND c.estado = 'A';");
        $json_categoria = json_encode((string)$id_categoria_principal);
        $stmt->bind_param("s", $json_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $row["sub_categorias"] = $this->getSubCategoriaByCategoria($row["id_categoria"]);


            $response[] = $row;
        }
        return $response;
    }

    public function getCategoriasAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM categorias c
        WHERE c.estado = 'A'");
        // $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $row["sub_categorias"] = $this->getSubCategoriaByCategoria($row["id_categoria"]);
            $response[] = $row;
        }
        return $response;
    }

    public function getCategoriasByCategoriaPrincipal($categoria_principal_id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE estado = 'A' AND JSON_CONTAINS(categoria_principal, JSON_QUOTE(?))");
        $stmt->bind_param("s", $categoria_principal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    public function getCategoriasPrincipalesAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM categorias_principales c
        WHERE c.estado = 'A'");
        // $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["total_producto_categoria"] = $this->getTotalProductosByCategoria($row["id_categoria_principal"]);
            $row["categorias_secundarias"] = $this->getCategoriasByPrincipal($row["id_categoria_principal"]);
            $response[] = $row;
        }
        return $response;
    }

    public function createCategoria($nombre)
    {
        if (!$this->isCategoriaExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO categorias (nombre) VALUES(?)");
            $stmt->bind_param("s", $nombre);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function createCategoriaPrincipal($nombre, $imagen)
    {
        if (!$this->isCategoriaExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO categorias_principales (nombre, imagen) VALUES(?,?)");
            $stmt->bind_param("ss", $nombre, $imagen);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isCategoriaExists($nombre)
    {
        $stmt = $this->conn->prepare("SELECT nombre FROM categorias WHERE nombre = ? AND estado = 'A'");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getCategoriaById($id_categoria)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM categorias c
        WHERE c.estado = 'A' AND id_categoria = ?");
        $stmt->bind_param("s", $id_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["sub_categorias"] = $this->getSubCategoriaByCategoria($id_categoria);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getCategoriaPrincipalById($id_categoria_principal)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM categorias_principales c
        WHERE c.estado = 'A' AND id_categoria_principal = ?");
        $stmt->bind_param("s", $id_categoria_principal);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row["total_producto_categoria"] = $this->getTotalProductosByCategoria($row["id_categoria_principal"]);
            $row["categorias_secundarias"] = $this->getCategoriasByPrincipal($row["id_categoria_principal"]);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    // public function getProductosByCategoria($id_categoria, $id_empresa)
    // {
    //     $ids = $this->normalizeIds($id_categoria);
    //     if (empty($ids)) return [];

    //     // Construye OR con JSON_CONTAINS(categoria, JSON_QUOTE(CAST(? AS CHAR)))
    //     $orParts = [];
    //     foreach ($ids as $_) {
    //         $orParts[] = "JSON_CONTAINS(categoria, JSON_QUOTE(CAST(? AS CHAR)))";
    //     }
    //     $orSql = implode(' OR ', $orParts);

    //     $sql = "
    //     SELECT *
    //     FROM productos
    //     WHERE estado = 'A'
    //       AND id_empresa = ?
    //       AND ($orSql)
    // ";

    //     $stmt = $this->conn->prepare($sql);

    //     // Tipos: N ids (int) + id_empresa (int). Si id_empresa es string, usa 's'.
    //     $types = str_repeat('i', count($ids)) . 'i';
    //     $params = array_merge($ids, [(int)$id_empresa]);

    //     $stmt->bind_param($types, ...$params);
    //     $stmt->execute();

    //     $result = $stmt->get_result();
    //     $response = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $response[] = $row;
    //     }
    //     return $response;
    // }

    public function getProductosByCategoria($id_categoria, $id_empresa)
    {
        // Normalizar a array
        if (!is_array($id_categoria)) {
            $id_categoria = json_decode($id_categoria, true);
        }
        if (!is_array($id_categoria)) {
            $id_categoria = [$id_categoria];
        }
        $id_categoria = array_filter(array_map('intval', $id_categoria));

        if (empty($id_categoria)) {
            return [];
        }

        $productos = []; // <== acumulador

        foreach ($id_categoria as $id) {
            $sql = "
                SELECT *
                FROM productos
                WHERE estado = 'A'
                AND id_empresa = ?
                AND JSON_CONTAINS(categoria, JSON_QUOTE(CAST(? AS CHAR)), '$')
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $id_empresa, $id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;   // <== acumula aquí
            }

            $stmt->close();
        }

        return $productos;
    }

    public function getVehiculosByCategoria($id_modelo, $id_empresa)
    {
        $vehiculos = [];

        // Validamos que tengamos los datos mínimos
        if (!$id_modelo || !$id_empresa) {
            return [];
        }

        // Si id_modelo es un array, tomamos el primer valor o adaptamos la lógica.
        // Asumiendo que buscas por un modelo específico relacionado al vehículo actual:
        $modelo_final = is_array($id_modelo) ? $id_modelo[0] : $id_modelo;

        $sql = "SELECT * FROM vehiculos 
            WHERE estado = 'A' 
            AND id_empresa = ? 
            AND id_modelo = ?";

        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $id_empresa, $modelo_final);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                // Aquí puedes añadir lógica para procesar campos JSON si los tienes
                $vehiculos[] = $row;
            }
            $stmt->close();
        }

        return $vehiculos;
    }


    public function updateCategoria($id_categoria, $atributos)
    {
        $jsonAtributos = json_encode($atributos);
        $stmt = $this->conn->prepare("UPDATE categorias SET atributos = ? WHERE id_categoria = ?");
        $stmt->bind_param("ss", $jsonAtributos, $id_categoria);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    public function updateCategoriaAll($id_categoria, $nombre, $tipo, $imagen, $categoria_principal)
    {
        $categoria_principal = json_encode($categoria_principal);
        $stmt = $this->conn->prepare("UPDATE categorias SET nombre = ?, tipo = ?, imagen = ?, categoria_principal = ? WHERE id_categoria = ?");
        $stmt->bind_param("sssss", $nombre, $tipo, $imagen, $categoria_principal, $id_categoria);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function updateCategoriaPrincipal($id_categoria, $nombre, $imagen)
    {
        $stmt = $this->conn->prepare("UPDATE categorias_principales SET nombre = ?, imagen = ? WHERE id_categoria_principal = ?");
        $stmt->bind_param("sss", $nombre, $imagen, $id_categoria);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function deleteCategoria($id_categoria)
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET estado = 'E' WHERE id_categoria = ?");
        $stmt->bind_param("s", $id_categoria);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getSubCategorias()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT s.*, c.nombre AS categoria
        FROM sub_categorias s
        INNER JOIN categorias c
        ON s.id_categoria = c.id_categoria
        WHERE s.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getSubCategoriaByCategoria($id_categoria)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM sub_categorias s
        WHERE s.estado = 'A' AND s.id_categoria=?");
        $stmt->bind_param("s", $id_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function createSubCategoria($id_categoria, $nombre)
    {
        if (!$this->isSubCategoriaExists($nombre, $id_categoria)) {
            $stmt = $this->conn->prepare("INSERT INTO sub_categorias (id_categoria, nombre) VALUES(?, ?)");
            $stmt->bind_param("ss", $id_categoria, $nombre);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isSubCategoriaExists($nombre, $id_categoria)
    {
        $stmt = $this->conn->prepare("SELECT nombre, id_categoria 
        FROM sub_categorias 
        WHERE nombre=? AND id_categoria=? AND estado = 'A'");
        $stmt->bind_param("ss", $nombre, $id_categoria);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getSubCategoriaById($id_sub_categoria)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM sub_categorias s
        WHERE s.estado = 'A' AND id_sub_categoria = ?");
        $stmt->bind_param("s", $id_sub_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function deleteSubCategoria($id_sub_categoria)
    {
        $stmt = $this->conn->prepare("UPDATE sub_categorias SET estado = 'E' WHERE id_sub_categoria = ?");
        $stmt->bind_param("s", $id_sub_categoria);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function updateSubCategoria($id_sub_categoria, $nombre, $id_categoria)
    {
        $stmt = $this->conn->prepare("UPDATE sub_categorias SET nombre = ?, id_categoria = ? WHERE id_sub_categoria = ? AND estado = 'A'");
        $stmt->bind_param("sss", $nombre, $id_categoria, $id_sub_categoria);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }
    /* CATEGORIAS */

    /* NOMBRES PRODUCTOS */
    public function createNombreProducto($nombre, $categoria, $sub_categoria)
    {
        $stmt = $this->conn->prepare("INSERT INTO nombres_productos (nombre, categoria, sub_categoria) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $categoria, $sub_categoria);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function updateNombreProducto($id, $nombre, $categoria, $sub_categoria)
    {
        $stmt = $this->conn->prepare("UPDATE nombres_productos SET nombre = ?, categoria = ?, sub_categoria = ? WHERE id_nombre_producto = ? AND estado = 'A'");
        $stmt->bind_param("ssss", $nombre, $categoria, $sub_categoria, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function deleteNombreProducto($id)
    {
        $stmt = $this->conn->prepare("UPDATE nombres_productos SET estado = 'E' WHERE id_nombre_producto = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function getNombresProductos()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT n.*, c.nombre AS categoria, s.nombre AS sub_categoria
        FROM nombres_productos n
        LEFT JOIN sub_categorias s
        ON n.sub_categoria = s.id_sub_categoria
        LEFT JOIN categorias c
        ON n.categoria = c.id_categoria
        WHERE n.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getNombreProductoById($id_nombre_producto)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM nombres_productos 
        WHERE id_nombre_producto = ?;");
        $stmt->bind_param("s", $id_nombre_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row['atributos'] = $this->getAtributosCategoriaCompleto($row['categoria']);
            return $row;
        }
    }
    /* NOMBRES PRODUCTOS */




    /* NOMBRES SERVICIOS */
    public function createNombreServicio($nombre, $categoria, $referencia)
    {
        $stmt = $this->conn->prepare("INSERT INTO nombres_servicios (nombre, categoria, referencia) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $categoria, $referencia);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function updateNombreServicio($id, $nombre, $categoria, $referencia)
    {
        $stmt = $this->conn->prepare("UPDATE nombres_servicios SET nombre = ?, categoria = ?, referencia = ? WHERE id_nombre_servicio = ? AND estado = 'A'");
        $stmt->bind_param("ssss", $nombre, $categoria, $referencia, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function deleteNombreServicio($id)
    {
        $stmt = $this->conn->prepare("UPDATE nombres_servicios SET estado = 'E' WHERE id_nombre_servicio = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function getNombresServicios()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT n.*, c.nombre AS categoria
        FROM nombres_servicios n
        INNER JOIN categorias c
        ON n.categoria = c.id_categoria
        WHERE n.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getNombreServicioById($id_nombre_servicio)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM nombres_servicios 
        WHERE id_nombre_servicio = ?;");
        $stmt->bind_param("s", $id_nombre_servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row['atributos'] = $this->getAtributosCategoriaCompleto($row['categoria']);
            return $row;
        }
    }
    /* NOMBRES PRODUCTOS */

    /* TIPO TRACCION */
    public function getTipoTraccion()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipo_traccion
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTipoTraccionById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipo_traccion
        WHERE estado = 'A' and id_tipo_traccion = '$id'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }
    /* TIPO TRACCION */

    /* MARCAS */
    public function createMarca($nombre, $referencia)
    {
        $stmt = $this->conn->prepare("INSERT INTO marcas (nombre, referencia) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $referencia);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function updateMarca($id, $nombre, $referencia)
    {
        $stmt = $this->conn->prepare("UPDATE marcas SET nombre = ?, referencia = ? WHERE id_marca = ? AND estado = 'A'");
        $stmt->bind_param("sss", $nombre, $referencia, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function deleteMarca($id)
    {
        $stmt = $this->conn->prepare("UPDATE marcas SET estado = 'E' WHERE id_marca = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function getMarcas()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM marcas
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }
    /* MARCAS */

    public function getMarcaById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM marcas
        WHERE estado = 'A' and id_marca = '$id'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    /* FUNCIONAMIENTO MOTOR */
    public function getFuncionamientoMotor()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM funcionamiento_motor
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getFuncionamientoMotorById($ids)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM funcionamiento_motor
        WHERE estado = 'A' and id_funcionamiento_motor = '$ids'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getFuncionamientoMotorByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
        SELECT id_funcionamiento_motor, nombre
        FROM funcionamiento_motor
        WHERE estado = 'A' AND id_funcionamiento_motor IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $response = [];
        while ($row = $res->fetch_assoc()) {
            $response[] = $row;
        }

        return $response;
    }
    /* FUNCIONAMIENTO MOTOR */

    /* REFERENCIAS */
    // public function getReferencias()
    // {
    //     $response = array();
    //     $stmt = $this->conn->prepare("SELECT DISTINCT referencia
    //         FROM modelos_autos;");
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     while ($row = $result->fetch_assoc()) {
    //         $response[] = $row;
    //     }
    //     return $response;
    // }

    public function getReferencias($asCsv = false)
    {
        // 1) Misma consulta que tienes
        $stmt = $this->conn->prepare("
        SELECT DISTINCT referencia
        FROM modelos_autos
        WHERE referencia IS NOT NULL AND referencia <> ''
    ");
        $stmt->execute();
        $res = $stmt->get_result();

        // 2) Split por coma y deduplicación (insensible a mayúsculas, conserva el casing del primero)
        $set = []; // clave en mayúsculas para deduplicar
        while ($row = $res->fetch_assoc()) {
            foreach (explode(',', $row['referencia']) as $token) {
                $t = trim($token);
                if ($t === '') continue;
                $key = mb_strtoupper($t, 'UTF-8');
                if (!isset($set[$key])) $set[$key] = $t;
            }
        }

        // 3) Orden natural (ignora mayúsculas/minúsculas)
        $refs = array_values($set);
        usort($refs, 'strnatcasecmp');

        // 4) Salida: CSV o array
        return $asCsv ? implode(',', $refs) : $refs;
    }
    /* REFERENCIAS */

    // public function getModelosByReferencia($referencia)
    // {
    //     $response = array();
    //     $stmt = $this->conn->prepare("SELECT *
    //     FROM modelos_autos
    //     WHERE referencia = ? AND estado = 'A'");
    //     $stmt->bind_param("s", $referencia);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     while ($row = $result->fetch_assoc()) {
    //         $response[] = $row;
    //     }
    //     return $response;
    // }

    // public function getModelosByReferencia($referencia)
    // {
    //     $response = [];
    //     $ref = str_replace(' ', '', trim($referencia)); // sin espacios

    //     $sql = "SELECT *
    //         FROM modelos_autos
    //         WHERE estado = 'A'
    //           AND FIND_IN_SET(?, REPLACE(referencia,' ','')) > 0";

    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("s", $ref);
    //     $stmt->execute();

    //     $result = $stmt->get_result();
    //     while ($row = $result->fetch_assoc()) $response[] = $row;
    //     return $response;
    // }

    public function getModelosByReferencia($referencia)
    {
        $response = [];

        // "ATVS,BICICLETAS" -> ["ATVS", "BICICLETAS"]
        $refClean = str_replace(' ', '', trim($referencia));
        $refs = array_filter(explode(',', $refClean));

        if (empty($refs)) {
            return $response;
        }

        // Construir condiciones: FIND_IN_SET(?, REPLACE(referencia,' ','')) > 0 OR ...
        $conds  = [];
        $types  = '';
        $params = [];

        foreach ($refs as $r) {
            $conds[]  = "FIND_IN_SET(?, UPPER(REPLACE(referencia,' ',''))) > 0";
            $types   .= 's';
            $params[] = $r;
        }

        $sql = "SELECT *
            FROM modelos_autos
            WHERE estado = 'A'
              AND (" . implode(' OR ', $conds) . ")";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return $response;
        }

        $this->bindDynamicParams($stmt, $types, $params);
        $stmt->execute();

        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }

        return $response;
    }

    public function getCatalogoVehiculoByReferencia($entity, $referencia)
    {
        $response = [];
        $map = [
            'modelos_autos' => [
                'table' => 'modelos_autos',
                'pk' => 'id_modelos_autos',
            ],
            'marcas' => [
                'table' => 'marcas',
                'pk' => 'id_marca',
            ],
            'tipos_auto' => [
                'table' => 'tipos_auto',
                'pk' => 'id_tipo_auto',
            ],
            'tipo_traccion' => [
                'table' => 'tipo_traccion',
                'pk' => 'id_tipo_traccion',
            ],
            'funcionamiento_motor' => [
                'table' => 'funcionamiento_motor',
                'pk' => 'id_funcionamiento_motor',
            ],
        ];

        if (!isset($map[$entity])) {
            return $response;
        }

        $refClean = str_replace(' ', '', strtoupper(trim((string)$referencia)));
        $refs = array_filter(array_map('trim', explode(',', $refClean)));
        if (empty($refs)) {
            return $response;
        }

        $table = $map[$entity]['table'];
        $pk = $map[$entity]['pk'];

        if ($entity === 'modelos_autos') {
            $conds = [];
            $types = '';
            $params = [];
            foreach ($refs as $ref) {
                $conds[] = "FIND_IN_SET(?, UPPER(REPLACE(referencia,' ',''))) > 0";
                $types .= 's';
                $params[] = $ref;
            }

            $sql = "SELECT *
                FROM modelos_autos
                WHERE estado = 'A'
                  AND referencia IS NOT NULL
                  AND referencia <> ''
                  AND (" . implode(' OR ', $conds) . ")
                ORDER BY nombre ASC";
        } else {
            $modelColumn = [
                'marcas' => 'id_marca',
                'tipos_auto' => 'id_tipo_auto',
                'tipo_traccion' => 'id_tipo_traccion',
                'funcionamiento_motor' => 'id_funcionamiento_motor',
            ][$entity] ?? '';

            if ($modelColumn === '') {
                return $response;
            }

            $condsModelo = [];
            $condsTabla = [];
            $types = '';
            $params = [];
            foreach ($refs as $ref) {
                $condsModelo[] = "FIND_IN_SET(?, UPPER(REPLACE(mo.referencia,' ',''))) > 0";
                $types .= 's';
                $params[] = $ref;
            }
            foreach ($refs as $ref) {
                $condsTabla[] = "FIND_IN_SET(?, UPPER(REPLACE(t.referencia,' ',''))) > 0";
                $types .= 's';
                $params[] = $ref;
            }

            $sql = "SELECT DISTINCT t.*
                FROM {$table} t
                LEFT JOIN modelos_autos mo
                    ON mo.estado = 'A'
                   AND mo.{$modelColumn} = t.{$pk}
                   AND mo.referencia IS NOT NULL
                   AND mo.referencia <> ''
                   AND (" . implode(' OR ', $condsModelo) . ")
                WHERE t.estado = 'A'
                  AND (
                    mo.id_modelos_autos IS NOT NULL
                    OR (
                        t.referencia IS NOT NULL
                        AND t.referencia <> ''
                        AND (" . implode(' OR ', $condsTabla) . ")
                    )
                  )
                ORDER BY t.nombre ASC";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return $response;
        }

        $this->bindDynamicParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row[$pk] ?? null;
            $response[] = $row;
        }
        $stmt->close();

        return $response;
    }

    public function getModelosByReferenciaYMarca($referencia, $id_marca = null, $id_tipo_auto = null)
    {
        $response = [];

        $refClean = str_replace(' ', '', strtoupper(trim((string)$referencia)));
        $refs = array_filter(array_map('trim', explode(',', $refClean)));

        if (in_array('COMERCIALES', $refs, true)) {
            foreach (['CAMIONESYPESADOS', 'CAMIONES', 'PESADOS'] as $refExtra) {
                if (!in_array($refExtra, $refs, true)) {
                    $refs[] = $refExtra;
                }
            }
        }

        if (empty($refs)) {
            return $response;
        }

        $conds = [];
        $types = '';
        $params = [];

        foreach ($refs as $r) {
            $conds[] = "FIND_IN_SET(?, UPPER(REPLACE(referencia,' ',''))) > 0";
            $types .= 's';
            $params[] = $r;
        }

        $sql = "SELECT *
            FROM modelos_autos
            WHERE estado = 'A'
              AND (" . implode(' OR ', $conds) . ")";

        if (!empty($id_marca)) {
            $sql .= " AND id_marca = ?";
            $types .= 'i';
            $params[] = (int)$id_marca;
        }

        if (!empty($id_tipo_auto)) {
            $sql .= " AND id_tipo_auto = ?";
            $types .= 'i';
            $params[] = (int)$id_tipo_auto;
        }

        $sql .= " ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return $response;
        }

        $this->bindDynamicParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }

        return $response;
    }



    public function getModeloProductoById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM modelos_autos
        WHERE id_modelo = ? AND estado = 'A'");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    /* MODELOS AUTOS*/
    public function createModeloAuto($nombre, $id_marca, $referencia)
    {
        $stmt = $this->conn->prepare("INSERT INTO modelos_autos (nombre, id_marca, referencia) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $id_marca, $referencia);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function updateModeloAuto($id, $nombre, $id_marca, $referencia)
    {
        $stmt = $this->conn->prepare("UPDATE modelos_autos SET nombre = ?, id_marca = ?, referencia = ? WHERE id_modelos_autos = ? AND estado = 'A'");
        $stmt->bind_param("ssss", $nombre, $id_marca, $referencia, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function deleteModeloAuto($id)
    {
        $stmt = $this->conn->prepare("UPDATE modelos_autos SET estado = 'E' WHERE id_modelos_autos = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function getModelosAutos()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM modelos_autos
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }
    /* MODELOS AUTOS*/

    public function getModeloById($id_modelo)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM modelos_autos
        WHERE id_modelos_autos = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_modelo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    /**
     * Rellena los campos NULL/0 de un registro en modelos_autos.
     * Solo actualiza columnas que estén en NULL o 0 — nunca sobreescribe datos existentes.
     */
    public function enrichModeloAuto(int $id, array $data): array
    {
        if ($id <= 0) {
            return ['error' => true, 'msg' => 'ID inválido'];
        }

        $allowed = [
            'id_tipo_auto'            => 'i',
            'id_tipo_traccion'        => 'i',
            'id_funcionamiento_motor' => 'i',
        ];

        $sets  = [];
        $types = '';
        $vals  = [];

        foreach ($allowed as $col => $type) {
            if (isset($data[$col]) && $data[$col] !== '' && $data[$col] !== null) {
                $v = intval($data[$col]);
                if ($v > 0) {
                    // Solo rellena si el campo es NULL o 0 (preserva datos ya registrados)
                    $sets[]  = "$col = IF($col IS NULL OR $col = 0, ?, $col)";
                    $types  .= $type;
                    $vals[]  = $v;
                }
            }
        }

        if (empty($sets)) {
            return ['error' => false, 'msg' => 'Sin cambios'];
        }

        $types .= 'i';
        $vals[] = $id;

        $sql  = 'UPDATE modelos_autos SET ' . implode(', ', $sets) . ' WHERE id_modelos_autos = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$vals);
        $ok   = $stmt->execute();
        $stmt->close();

        return ['error' => !$ok, 'msg' => $ok ? 'OK' : 'Error al actualizar'];
    }

    public function getTiposAuto()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipos_auto
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    public function getTiposAutoById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipos_auto
        WHERE estado = 'A' and id_tipo_auto = '$id'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    /* ATRIBUTOS */
    public function createAtributo($nombre, $tipo_dato, $opciones)
    {
        $opcionesJson = ($tipo_dato === 'OPCIONES' && !empty($opciones)) ? $opciones : null;
        $stmt = $this->conn->prepare("INSERT INTO atributos (nombre, tipo_dato, opciones) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $tipo_dato, $opcionesJson);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function updateAtributoFull($id, $nombre, $tipo_dato, $opciones)
    {
        $opcionesJson = ($tipo_dato === 'OPCIONES' && !empty($opciones)) ? $opciones : null;
        $stmt = $this->conn->prepare("UPDATE atributos SET nombre = ?, tipo_dato = ?, opciones = ? WHERE id_atributo = ? AND estado = 'A'");
        $stmt->bind_param("ssss", $nombre, $tipo_dato, $opcionesJson, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function deleteAtributo($id)
    {
        $stmt = $this->conn->prepare("UPDATE atributos SET estado = 'E' WHERE id_atributo = ?");
        $stmt->bind_param("s", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    public function getAtributos()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM atributos
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getAtributoById($id_atributo)
    {
        // $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM atributos
        WHERE id_atributo = ?;");
        $stmt->bind_param("s", $id_atributo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // $response[] = $row;
            return $row;
        }
        // return $response;
    }

    public function getAtributosCategoria($id_categoria)
    {
        // $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM categorias
        WHERE id_categoria = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // $response[] = $row;
            return $row;
        }
        // return $response;
    }

    public function getAtributosCategoriaCompleto($id_categoria)
    {
        // Paso 1: obtener los atributos asociados a la categoría
        $stmt = $this->conn->prepare("SELECT atributos FROM categorias WHERE id_categoria = ? AND estado = 'A'");
        $stmt->bind_param("s", $id_categoria);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $atributos_ids = json_decode($row['atributos']); // si viene como JSON

            if (!is_array($atributos_ids)) {
                $atributos_ids = explode(',', trim($row['atributos'], '[]')); // fallback si viene como string
            }

            // Paso 2: preparar la consulta dinámica con IN
            $placeholders = implode(',', array_fill(0, count($atributos_ids), '?'));
            $types = str_repeat('i', count($atributos_ids)); // todos los atributos son int

            $sql = "SELECT id_atributo, nombre, tipo_dato, opciones FROM atributos 
                    WHERE id_atributo IN ($placeholders) AND estado = 'A'";
            $stmt2 = $this->conn->prepare($sql);
            $stmt2->bind_param($types, ...$atributos_ids);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            $atributos = [];
            while ($atributo = $result2->fetch_assoc()) {
                // Decodificar las opciones si existen
                if ($atributo['tipo_dato'] === 'OPCIONES' && !empty($atributo['opciones'])) {
                    $atributo['opciones'] = json_decode($atributo['opciones']);
                } else {
                    unset($atributo['opciones']); // eliminar campo si no aplica
                }

                $atributos[] = $atributo;
            }

            return $atributos;
        }

        return [];
    }

    public function updateAtributo($id_atributo, $opciones)
    {
        $jsonOpciones = json_encode($opciones);
        $stmt = $this->conn->prepare("UPDATE atributos SET opciones = ? WHERE id_atributo = ?;");
        $stmt->bind_param("ss", $jsonOpciones, $id_atributo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    /* ATRIBUTOS */

    /* ORDENES */

    public function createOrden($id_sucursal, $area, $productos, $total, $id_usuario)
    {

        $sucursal = $this->getSucursalById($id_sucursal);
        if ($sucursal != RECORD_DOES_NOT_EXIST) {
            $jsonProductos = json_encode($productos);
            $fecha = date('Y-m-d H:i:s');
            $stmt = $this->conn->prepare("INSERT INTO ordenes_empresas(id_empresa, id_sucursal, id_area, productos, total, created_at, updated_at, creation_user) values(?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssss", $sucursal["id_empresa"], $id_sucursal, $area, $jsonProductos, $total, $fecha, $fecha, $id_usuario);
            $result = $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            if ($result) {
                $accion = "Orden #" . $id . " creada.";
                $this->createOrdenNota($id, $id_usuario, $accion, "shopping-cart", 'E');
                $this->notificaEvento('orden_creada', null, null, $id);

                $permisos = $this->getPermisosByUser($id_usuario);
                foreach ($permisos as $permiso) {
                    if ($permiso["permiso"] == "Ordenes") {
                        $nivel = $permiso["levels"];
                    }
                }

                if ($nivel == "Vendedor") {
                    $this->updateEstadoOrden([$id], 'aprobada', $id_usuario);
                    $this->createOrdenIso([$id], $id_usuario);
                }

                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function createOrdenNota($id_orden, $id_usuario, $accion, $tipo, $tipo_nota)
    {
        date_default_timezone_set('America/Guayaquil');
        $fecha =  date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("INSERT INTO ordenes_notas (id_orden, id_usuario, accion, created_at, updated_at, tipo, tipo_nota) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_orden, $id_usuario, $accion, $fecha, $fecha, $tipo, $tipo_nota);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }


    // public function confirmarVentaOrdenFulmuv($id_usuario, $raw)
    // {
    //     date_default_timezone_set('America/Guayaquil');
    //     $fecha = date('Y-m-d H:i:s');

    //     // Carga de datos de la orden y cliente
    //     $ordenes = $this->getOrdenesByIdOrden($raw['id_ordenes'])[0] ?? null;

    //     $cliente = $this->getClienteByIdCliente($ordenes['id_cliente']);

    //     // --- Construcción de datos para la guía ---
    //     // Contenido: nombres de productos separados por coma
    //     $contenido = '';
    //     if (!empty($raw['productos']) && is_array($raw['productos'])) {
    //         $contenido = implode(', ', array_map(function ($p) {
    //             return trim((string)($p['nombre'] ?? $p['tags'] ?? ''));
    //         }, $raw['productos']));
    //     }

    //     $numero_piezas   = max(1, (int)count($raw['productos'] ?? []));
    //     $valor_mercancia = (float)($raw['total_pagado'] ?? 0);     // ajusta si tu “valor mercancia” es otro campo
    //     $valor_asegurado = 0.0;                                    // si tu flujo requiere asegurar, cambia aquí
    //     $peso_fisico     = (float)($raw['total_peso'] ?? 0);

    //     $razon_social     = (string)($ordenes['facturacion']['razon_social'] ?? '');
    //     $nombre_cliente   = (string)($cliente['nombres'] ?? '');
    //     $direccion        = (string)($ordenes['facturacion']['direccion'] ?? '');
    //     $sector_destino   = (string)($ordenes['facturacion']['sector'] ?? ''); // si no tienes, deja vacío
    //     $telefono_destino = (string)($cliente['telefono'] ?? '');
    //     $id_trayecto = (int)($raw['id_trayecto'] ?? '');
    //     $latitud = "-1.782382167170598";
    //     $longitud = "-79.68265123087815";

    //     // 1) Crear guía en Servientrega
    //     $resGuia = $this->crearGuiaGrupoEntrega(
    //         $razon_social,
    //         $nombre_cliente,
    //         $direccion,
    //         $sector_destino,
    //         $telefono_destino,
    //         $contenido,
    //         $numero_piezas,
    //         $valor_mercancia,
    //         $valor_asegurado,
    //         $peso_fisico,
    //         $latitud,
    //         $longitud
    //     );

    //     var_dump($resGuia);
    //     // Validar respuesta del proveedor
    //     // if (!is_array($resGuia) || empty($resGuia['id'])) {
    //     //     // Puedes loguear $resGuia para diagnóstico
    //     //     return [
    //     //         'error' => true,
    //     //         'msg'   => 'No se pudo crear la guía en Servientrega',
    //     //         'raw'   => $resGuia
    //     //     ];
    //     // }

    //     // $idGuia = (string)$resGuia['id'];
    //     // $idOrdenEmpresa = (int)$raw['id_orden_empresa'];

    //     // // 2) Registrar ordenes_servientrega + 3) actualizar estado_venta = 2
    //     // $this->conn->begin_transaction();
    //     // try {
    //     //     // Insert en ordenes_servientrega
    //     //     $stmt = $this->conn->prepare(
    //     //         "INSERT INTO ordenes_servientrega (id_orden_empresa, id_guia) VALUES (?, ?)"
    //     //     );
    //     //     $stmt->bind_param("is", $idOrdenEmpresa, $idGuia);
    //     //     if (!$stmt->execute()) {
    //     //         throw new Exception('No se pudo insertar ordenes_servientrega');
    //     //     }
    //     //     $stmt->close();

    //     //     // Update estado_venta = 2 en ordenes_empresas
    //     //     $nuevoEstadoVenta = 3;
    //     //     $stmt2 = $this->conn->prepare(
    //     //         "UPDATE ordenes_empresas SET estado_venta = ? WHERE id_orden = ?"
    //     //     );
    //     //     $stmt2->bind_param("ii", $nuevoEstadoVenta, $idOrdenEmpresa);
    //     //     if (!$stmt2->execute()) {
    //     //         throw new Exception('No se pudo actualizar estado_venta');
    //     //     }
    //     //     $stmt2->close();

    //     //     // (Opcional) Registrar nota de auditoría
    //     //     $id_orden = $ordenes['id_orden'] ?? null; // si quieres apuntar a la orden “macro”
    //     //     if ($id_orden) {
    //     //         $accion    = "Guía Servientrega {$idGuia} creada y estado_venta actualizado a 2";
    //     //         $tipo      = "VENTA";
    //     //         $tipo_nota = "CONFIRMACION_ENVIO";

    //     //         $stmt3 = $this->conn->prepare(
    //     //             "INSERT INTO ordenes_notas (id_orden, id_usuario, accion, created_at, updated_at, tipo, tipo_nota)
    //     //          VALUES(?, ?, ?, ?, ?, ?, ?)"
    //     //         );
    //     //         $stmt3->bind_param("iisssss", $id_orden, $id_usuario, $accion, $fecha, $fecha, $tipo, $tipo_nota);
    //     //         if (!$stmt3->execute()) {
    //     //             // No rompemos la operación por una nota, pero puedes lanzar excepción si lo prefieres
    //     //         }
    //     //         $stmt3->close();
    //     //     }

    //     //     $this->conn->commit();

    //     //     return [
    //     //         'error'   => false,
    //     //         'msg'     => 'Guía creada, orden registrada en Servientrega y estado actualizado.',
    //     //         'id_guia' => $idGuia
    //     //     ];
    //     // } catch (Throwable $e) {
    //     //     $this->conn->rollback();
    //     //     return [
    //     //         'error' => true,
    //     //         'msg'   => 'Transacción fallida: ' . $e->getMessage(),
    //     //         'guia'  => $idGuia
    //     //     ];
    //     // }
    // }
    // public function confirmarVentaOrdenFulmuv($id_usuario, $raw)
    // {
    //     date_default_timezone_set('America/Guayaquil');
    //     $fecha = date('Y-m-d H:i:s');

    //     // =========================
    //     // 1) Cargar datos de la orden
    //     // =========================
    //     $ordenes = $this->getOrdenesByIdOrden($raw['id_ordenes'])[0] ?? null;
    //     if (!$ordenes) {
    //         return ['error' => true, 'msg' => 'No se encontró la orden.'];
    //     }

    //     $cliente = $this->getClienteByIdCliente($ordenes['id_cliente']);
    //     if (!$cliente) {
    //         return ['error' => true, 'msg' => 'No se encontró el cliente.'];
    //     }

    //     // =========================
    //     // 2) Armar datos para guía
    //     // =========================
    //     $contenido = '';
    //     if (!empty($raw['productos']) && is_array($raw['productos'])) {
    //         $contenido = implode(', ', array_map(function ($p) {
    //             return trim((string)($p['nombre'] ?? $p['tags'] ?? ''));
    //         }, $raw['productos']));
    //     }

    //     $numero_piezas   = max(1, (int)count($raw['productos'] ?? []));
    //     $valor_mercancia = (float)($raw['total_pagado'] ?? 0);
    //     $valor_asegurado = (float)($raw['valor_asegurado'] ?? 0);
    //     $peso_fisico     = (float)($raw['total_peso'] ?? 0);

    //     $razon_social     = (string)($ordenes['facturacion']['razon_social'] ?? ($raw['razon_social'] ?? ''));
    //     $nombre_cliente   = (string)($cliente['nombres'] ?? ($raw['nombre_cliente'] ?? ''));
    //     $direccion        = (string)($ordenes['facturacion']['direccion'] ?? ($raw['direccion'] ?? ''));
    //     $sector_destino   = (string)($raw['sector_destino'] ?? '');
    //     $telefono_destino = (string)($cliente['telefono'] ?? ($raw['telefono_destino'] ?? ''));

    //     $latitud  = (string)($raw['latitud'] ?? '');
    //     $longitud = (string)($raw['longitud'] ?? '');

    //     $idOrdenEmpresa = (int)($raw['id_orden_empresa'] ?? 0);
    //     if ($idOrdenEmpresa <= 0) {
    //         return ['error' => true, 'msg' => 'Falta id_orden_empresa en raw.'];
    //     }

    //     // =========================
    //     // 3) Crear guía (MASTER + HIJA)
    //     // =========================
    //     $resGuia = $this->crearGuiaGrupoEntrega(
    //         $razon_social,
    //         $nombre_cliente,
    //         $direccion,
    //         $sector_destino,
    //         $telefono_destino,
    //         $contenido,
    //         $numero_piezas,
    //         $valor_mercancia,
    //         $valor_asegurado,
    //         $peso_fisico,
    //         $latitud,
    //         $longitud
    //     );

    //     if (!is_array($resGuia) || !empty($resGuia['error'])) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'No se pudo crear la guía (MASTER/HIJA).',
    //             'raw'   => $resGuia
    //         ];
    //     }

    //     $guiaMasterId = $resGuia['guiaMasterId'] ?? null;
    //     $barcode      = $resGuia['hija']['data']  ?? null;
    //     $labelUrl     = $resGuia['hija']['label'] ?? null;
    //     $codeApi      = $resGuia['hija']['code']  ?? null;

    //     if (empty($guiaMasterId) || empty($barcode) || empty($labelUrl)) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'La guía se creó pero faltan campos clave (guiaMasterId / barcode / label).',
    //             'raw'   => $resGuia
    //         ];
    //     }

    //     // =========================
    //     // 4) Guardar en BD + actualizar estado_venta
    //     // =========================
    //     $this->conn->begin_transaction();

    //     try {

    //         // A) INSERT ordenes_servientrega
    //         // 👇 usa "url" (según tu tabla). Si tu columna es otra, cámbiala aquí.
    //         $sqlInsert = "
    //         INSERT INTO ordenes_servientrega (id_orden_empresa, id_guia, url_grupoentrega, code, estado)
    //         VALUES (?, ?, ?, ?, ?)
    //     ";

    //         $stmt = $this->conn->prepare($sqlInsert);
    //         if (!$stmt) {
    //             throw new Exception("Prepare INSERT falló: " . $this->conn->error);
    //         }

    //         $estado = 'A';
    //         $idGuiaDB = (string)$guiaMasterId; // MASTER
    //         $codeDB   = (string)$barcode;      // HIJA barcode

    //         $stmt->bind_param("issss", $idOrdenEmpresa, $idGuiaDB, $labelUrl, $codeDB, $estado);

    //         if (!$stmt->execute()) {
    //             $err = $stmt->error;
    //             $stmt->close();
    //             throw new Exception("Execute INSERT falló: " . $err);
    //         }

    //         $idOrdenServientrega = $this->conn->insert_id;
    //         $affectedInsert      = $stmt->affected_rows;
    //         $stmt->close();

    //         if ($affectedInsert <= 0) {
    //             throw new Exception("INSERT no afectó filas. insert_id={$idOrdenServientrega}");
    //         }

    //         // B) UPDATE estado_venta
    //         $nuevoEstadoVenta = 3;
    //         $sqlUpdate = "UPDATE ordenes_empresas SET estado_venta = ? WHERE id_orden = ?";

    //         $stmt2 = $this->conn->prepare($sqlUpdate);
    //         if (!$stmt2) {
    //             throw new Exception("Prepare UPDATE falló: " . $this->conn->error);
    //         }

    //         $stmt2->bind_param("ii", $nuevoEstadoVenta, $idOrdenEmpresa);

    //         if (!$stmt2->execute()) {
    //             $err = $stmt2->error;
    //             $stmt2->close();
    //             throw new Exception("Execute UPDATE falló: " . $err);
    //         }

    //         $affectedUpdate = $stmt2->affected_rows;
    //         $stmt2->close();

    //         // Si no actualiza, validamos si existe o si ya estaba en 3
    //         if ($affectedUpdate === 0) {
    //             $chk = $this->conn->prepare("SELECT id_orden_empresa, estado_venta FROM ordenes_empresas WHERE id_orden = ?");
    //             if (!$chk) {
    //                 throw new Exception("Prepare SELECT validación falló: " . $this->conn->error);
    //             }

    //             $chk->bind_param("i", $idOrdenEmpresa);
    //             $chk->execute();
    //             $r = $chk->get_result()->fetch_assoc();
    //             $chk->close();

    //             if (!$r) {
    //                 throw new Exception("UPDATE no afectó filas: NO existe id_orden_empresa={$idOrdenEmpresa} en ordenes_empresas.");
    //             } else {
    //                 throw new Exception("UPDATE no afectó filas: el registro existe pero estado_venta ya era {$r['estado_venta']} (o mismo valor).");
    //             }
    //         }

    //         // C) COMMIT
    //         if (!$this->conn->commit()) {
    //             throw new Exception("Commit falló: " . $this->conn->error);
    //         }

    //         return [
    //             'error' => false,
    //             'msg'   => 'Guía registrada y estado_venta actualizado.',
    //             'debug' => [
    //                 'idOrdenEmpresa'   => $idOrdenEmpresa,
    //                 'guiaMasterId'     => $guiaMasterId,
    //                 'barcode'          => $barcode,
    //                 'labelUrl'         => $labelUrl,
    //                 'insert_id'        => $idOrdenServientrega,
    //                 'affected_insert'  => $affectedInsert,
    //                 'affected_update'  => $affectedUpdate,
    //             ],
    //             'servientrega' => $resGuia
    //         ];
    //     } catch (Throwable $e) {
    //         $this->conn->rollback();
    //         return [
    //             'error' => true,
    //             'msg'   => 'Transacción fallida: ' . $e->getMessage(),
    //             'debug' => [
    //                 'idOrdenEmpresa' => $idOrdenEmpresa,
    //                 'guiaMasterId'   => $guiaMasterId,
    //                 'barcode'        => $barcode,
    //                 'labelUrl'       => $labelUrl,
    //                 'sqlInsert'      => $sqlInsert ?? null,
    //                 'sqlUpdate'      => $sqlUpdate ?? null,
    //                 'db_error'       => $this->conn->error ?? null,
    //             ],
    //             'servientrega' => $resGuia
    //         ];
    //     }
    // }


    // public function confirmarVentaOrdenFulmuv($id_usuario, $raw)
    // {
    //     date_default_timezone_set('America/Guayaquil');
    //     $fecha = date('Y-m-d H:i:s');

    //     // =========================
    //     // 1) Cargar datos de la orden
    //     // =========================
    //     $ordenes = $this->getOrdenesByIdOrden($raw['id_ordenes'])[0] ?? null;
    //     if (!$ordenes) {
    //         return ['error' => true, 'msg' => 'No se encontró la orden.'];
    //     }

    //     $cliente = $this->getClienteByIdCliente($ordenes['id_cliente']);
    //     if (!$cliente) {
    //         return ['error' => true, 'msg' => 'No se encontró el cliente.'];
    //     }

    //     // =========================
    //     // 2) Armar datos para guía
    //     // =========================
    //     $contenido = '';
    //     if (!empty($raw['productos']) && is_array($raw['productos'])) {
    //         $contenido = implode(', ', array_map(function ($p) {
    //             return trim((string)($p['nombre'] ?? $p['tags'] ?? ''));
    //         }, $raw['productos']));
    //     }

    //     $numero_piezas   = max(1, (int)count($raw['productos'] ?? []));
    //     $valor_mercancia = (float)($raw['total_pagado'] ?? 0);
    //     $valor_asegurado = (float)($raw['valor_asegurado'] ?? 0);
    //     $peso_fisico     = (float)($raw['total_peso'] ?? 0);

    //     $razon_social     = (string)($ordenes['facturacion']['razon_social'] ?? ($raw['razon_social'] ?? ''));
    //     $nombre_cliente   = (string)($cliente['nombres'] ?? ($raw['nombre_cliente'] ?? ''));
    //     $direccion        = (string)($ordenes['facturacion']['direccion'] ?? ($raw['direccion'] ?? ''));
    //     $sector_destino   = (string)($raw['sector_destino'] ?? '');
    //     $telefono_destino = (string)($cliente['telefono'] ?? ($raw['telefono_destino'] ?? ''));

    //     $latitud  = (string)($raw['latitud'] ?? '');
    //     $longitud = (string)($raw['longitud'] ?? '');

    //     $idOrdenEmpresa = (int)($raw['id_orden_empresa'] ?? 0);
    //     if ($idOrdenEmpresa <= 0) {
    //         return ['error' => true, 'msg' => 'Falta id_orden_empresa en raw.'];
    //     }

    //     // =========================
    //     // 3) Crear guía (MASTER + HIJA)
    //     // =========================
    //     $resGuia = $this->crearGuiaGrupoEntrega(
    //         $razon_social,
    //         $nombre_cliente,
    //         $direccion,
    //         $sector_destino,
    //         $telefono_destino,
    //         $contenido,
    //         $numero_piezas,
    //         $valor_mercancia,
    //         $valor_asegurado,
    //         $peso_fisico,
    //         $latitud,
    //         $longitud,
    //         $idOrdenEmpresa
    //     );

    //     if (!is_array($resGuia) || !empty($resGuia['error'])) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'No se pudo crear la guía (MASTER/HIJA).',
    //             'raw'   => $resGuia
    //         ];
    //     }

    //     $guiaMasterId = $resGuia['guiaMasterId'] ?? null;
    //     $barcode      = $resGuia['hija']['data']  ?? null;
    //     $labelUrl     = $resGuia['hija']['label'] ?? null;
    //     $codeApi      = $resGuia['hija']['code']  ?? null;

    //     if (empty($guiaMasterId) || empty($barcode) || empty($labelUrl)) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'La guía se creó pero faltan campos clave (guiaMasterId / barcode / label).',
    //             'raw'   => $resGuia
    //         ];
    //     }

    //     // =========================
    //     // 4) Guardar en BD + actualizar estado_venta
    //     // =========================
    //     $this->conn->begin_transaction();

    //     try {

    //         // A) INSERT ordenes_servientrega
    //         // 👇 usa "url" (según tu tabla). Si tu columna es otra, cámbiala aquí.
    //         $sqlInsert = "
    //         INSERT INTO ordenes_servientrega (id_orden_empresa, id_guia, url_grupoentrega, code, estado)
    //         VALUES (?, ?, ?, ?, ?)
    //     ";

    //         $stmt = $this->conn->prepare($sqlInsert);
    //         if (!$stmt) {
    //             throw new Exception("Prepare INSERT falló: " . $this->conn->error);
    //         }

    //         $estado = 'A';
    //         $idGuiaDB = (string)$guiaMasterId; // MASTER
    //         $codeDB   = (string)$barcode;      // HIJA barcode

    //         $stmt->bind_param("issss", $idOrdenEmpresa, $idGuiaDB, $labelUrl, $codeDB, $estado);

    //         if (!$stmt->execute()) {
    //             $err = $stmt->error;
    //             $stmt->close();
    //             throw new Exception("Execute INSERT falló: " . $err);
    //         }

    //         $idOrdenServientrega = $this->conn->insert_id;
    //         $affectedInsert      = $stmt->affected_rows;
    //         $stmt->close();

    //         if ($affectedInsert <= 0) {
    //             throw new Exception("INSERT no afectó filas. insert_id={$idOrdenServientrega}");
    //         }

    //         // B) UPDATE estado_venta
    //         $nuevoEstadoVenta = 3;
    //         $sqlUpdate = "UPDATE ordenes_empresas SET estado_venta = ? WHERE id_orden = ?";

    //         $stmt2 = $this->conn->prepare($sqlUpdate);
    //         if (!$stmt2) {
    //             throw new Exception("Prepare UPDATE falló: " . $this->conn->error);
    //         }

    //         $stmt2->bind_param("ii", $nuevoEstadoVenta, $idOrdenEmpresa);

    //         if (!$stmt2->execute()) {
    //             $err = $stmt2->error;
    //             $stmt2->close();
    //             throw new Exception("Execute UPDATE falló: " . $err);
    //         }

    //         $affectedUpdate = $stmt2->affected_rows;
    //         $stmt2->close();

    //         // Si no actualiza, validamos si existe o si ya estaba en 3
    //         if ($affectedUpdate === 0) {
    //             $chk = $this->conn->prepare("SELECT id_orden_empresa, estado_venta FROM ordenes_empresas WHERE id_orden = ?");
    //             if (!$chk) {
    //                 throw new Exception("Prepare SELECT validación falló: " . $this->conn->error);
    //             }

    //             $chk->bind_param("i", $idOrdenEmpresa);
    //             $chk->execute();
    //             $r = $chk->get_result()->fetch_assoc();
    //             $chk->close();

    //             if (!$r) {
    //                 throw new Exception("UPDATE no afectó filas: NO existe id_orden_empresa={$idOrdenEmpresa} en ordenes_empresas.");
    //             } else {
    //                 throw new Exception("UPDATE no afectó filas: el registro existe pero estado_venta ya era {$r['estado_venta']} (o mismo valor).");
    //             }
    //         }

    //         // C) COMMIT
    //         if (!$this->conn->commit()) {
    //             throw new Exception("Commit falló: " . $this->conn->error);
    //         }

    //         return [
    //             'error' => false,
    //             'msg'   => 'Guía registrada y estado_venta actualizado.',
    //             'debug' => [
    //                 'idOrdenEmpresa'   => $idOrdenEmpresa,
    //                 'guiaMasterId'     => $guiaMasterId,
    //                 'barcode'          => $barcode,
    //                 'labelUrl'         => $labelUrl,
    //                 'insert_id'        => $idOrdenServientrega,
    //                 'affected_insert'  => $affectedInsert,
    //                 'affected_update'  => $affectedUpdate,
    //             ],
    //             'servientrega' => $resGuia
    //         ];
    //     } catch (Throwable $e) {
    //         $this->conn->rollback();
    //         return [
    //             'error' => true,
    //             'msg'   => 'Transacción fallida: ' . $e->getMessage(),
    //             'debug' => [
    //                 'idOrdenEmpresa' => $idOrdenEmpresa,
    //                 'guiaMasterId'   => $guiaMasterId,
    //                 'barcode'        => $barcode,
    //                 'labelUrl'       => $labelUrl,
    //                 'sqlInsert'      => $sqlInsert ?? null,
    //                 'sqlUpdate'      => $sqlUpdate ?? null,
    //                 'db_error'       => $this->conn->error ?? null,
    //             ],
    //             'servientrega' => $resGuia
    //         ];
    //     }
    // }


    public function confirmarVentaOrdenFulmuv($id_usuario, $raw)
    {
        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        // =========================
        // 1) Cargar datos de la orden
        // =========================
        $idOrden = (int)($raw['id_ordenes'] ?? 0);
        if ($idOrden <= 0) {
            return ['error' => true, 'msg' => 'Falta id_ordenes en raw.'];
        }

        $ordenes = $this->getOrdenesByIdOrden($idOrden)[0] ?? null;
        if (!$ordenes) {
            return ['error' => true, 'msg' => 'No se encontró la orden.'];
        }

        $cliente = $this->getClienteByIdCliente($ordenes['id_cliente']);
        if (!$cliente) {
            return ['error' => true, 'msg' => 'No se encontró el cliente.'];
        }

        // =========================
        // 2) Armar datos para guía
        // =========================
        $productos = (!empty($raw['productos']) && is_array($raw['productos'])) ? $raw['productos'] : [];

        $contenido = '';
        if (!empty($productos)) {
            $contenido = implode(', ', array_map(function ($p) {
                return trim((string)($p['nombre'] ?? $p['tags'] ?? ''));
            }, $productos));
            $contenido = trim($contenido);
        }

        $numero_piezas   = max(1, (int)count($productos));
        $valor_mercancia = (float)($raw['total_pagado'] ?? 0);
        $valor_asegurado = (float)($raw['valor_asegurado'] ?? 0);
        $peso_fisico     = (float)($raw['total_peso'] ?? 0);

        // OJO: acá ajusta a TU estructura real (facturacion y domicilio en tu orden)
        $fact = is_array($ordenes['facturacion'] ?? null) ? $ordenes['facturacion'] : [];
        $dom  = is_array($ordenes['domicilio'] ?? null)   ? $ordenes['domicilio']   : [];

        $razon_social     = (string)($fact['razon_social'] ?? ($raw['razon_social'] ?? ''));
        $nombre_cliente   = (string)($cliente['nombres'] ?? ($raw['nombre_cliente'] ?? ''));
        $direccion        = (string)($dom['direccion_exacta'] ?? $fact['direccion'] ?? ($raw['direccion'] ?? ''));
        $sector_destino   = (string)($dom['sector'] ?? ($raw['sector_destino'] ?? ''));
        $telefono_destino = (string)($cliente['telefono'] ?? ($raw['telefono_destino'] ?? ''));

        $latitud  = (string)($raw['latitud'] ?? ($dom['latitud'] ?? ''));
        $longitud = (string)($raw['longitud'] ?? ($dom['longitud'] ?? ''));

        $idOrdenEmpresa = (int)($raw['id_orden_empresa'] ?? 0);
        if ($idOrdenEmpresa <= 0) {
            return ['error' => true, 'msg' => 'Falta id_orden_empresa en raw.'];
        }

        // =========================
        // 3) Crear guía (MASTER + HIJA)
        // =========================
        $resGuia = $this->crearGuiaGrupoEntrega(
            $razon_social,
            $nombre_cliente,
            $direccion,
            $sector_destino,
            $telefono_destino,
            $contenido,
            $numero_piezas,
            $valor_mercancia,
            $valor_asegurado,
            $peso_fisico,
            $latitud,
            $longitud,
            $idOrdenEmpresa
        );

        if (!is_array($resGuia) || !empty($resGuia['error'])) {
            return [
                'error' => true,
                'msg'   => 'No se pudo crear la guía (MASTER/HIJA).',
                'raw'   => $resGuia
            ];
        }

        // ✅ OJO: tu crearGuiaGrupoEntrega (el que te dejé) retorna:
        // guiaMasterId, barcode, labelUrl, barcodePickup

        $this->enviarGraciasCompra($idOrdenEmpresa);
        $guiaMasterId = $resGuia['guiaMasterId'] ?? null;
        $barcode      = $resGuia['barcode'] ?? null;        // HIJA (entrega)
        $labelUrl     = $resGuia['labelUrl'] ?? null;       // URL etiqueta (entrega)
        $barcodePickup = $resGuia['barcodePickup'] ?? null;  // pickup si existe

        if (empty($guiaMasterId) || empty($barcode) || empty($labelUrl)) {
            return [
                'error' => true,
                'msg'   => 'La guía se creó pero faltan campos clave (guiaMasterId / barcode / labelUrl).',
                'raw'   => $resGuia
            ];
        }

        // =========================
        // 4) Guardar en BD + actualizar estado_venta
        // =========================
        $this->conn->begin_transaction();

        try {

            // A) INSERT ordenes_servientrega
            // Ajusta nombres exactos de columnas según tu tabla.
            $sqlInsert = "
            INSERT INTO ordenes_servientrega
                (id_orden_empresa, id_guia, url_grupoentrega, code)
            VALUES
                (?, ?, ?, ?)
        ";

            $stmt = $this->conn->prepare($sqlInsert);
            if (!$stmt) {
                throw new Exception("Prepare INSERT falló: " . $this->conn->error);
            }

            $estado  = 'A';
            $idGuiaDB = (string)$guiaMasterId;  // MASTER
            $codeDB   = (string)$barcode;       // HIJA entrega


            // barcode_pickup puede ser null
            $pickupDB = !empty($barcodePickup) ? (string)$barcodePickup : null;

            // i s s s s s s
            $stmt->bind_param(
                "isss",
                $idOrdenEmpresa,
                $idGuiaDB,
                $labelUrl,
                $codeDB
            );

            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new Exception("Execute INSERT falló: " . $err);
            }

            $idOrdenServientrega = (int)$this->conn->insert_id;
            $affectedInsert      = (int)$stmt->affected_rows;
            $stmt->close();

            if ($affectedInsert <= 0) {
                throw new Exception("INSERT no afectó filas. insert_id={$idOrdenServientrega}");
            }

            // B) UPDATE estado_venta
            // ✅ CAMBIO: tu PK es id_orden_empresa (según lo que vienes usando)
            $nuevoEstadoVenta = 3;

            $sqlUpdate = "UPDATE ordenes_empresas SET estado_venta = ? WHERE id_orden = ?";
            $stmt2 = $this->conn->prepare($sqlUpdate);
            if (!$stmt2) {
                throw new Exception("Prepare UPDATE falló: " . $this->conn->error);
            }

            // i s i
            $stmt2->bind_param("ii", $nuevoEstadoVenta, $idOrdenEmpresa);

            if (!$stmt2->execute()) {
                $err = $stmt2->error;
                $stmt2->close();
                throw new Exception("Execute UPDATE falló: " . $err);
            }

            $affectedUpdate = (int)$stmt2->affected_rows;
            $stmt2->close();

            if ($affectedUpdate === 0) {
                // Validación: existe o ya estaba en 3
                $chk = $this->conn->prepare("SELECT id_orden, estado_venta FROM ordenes_empresas WHERE id_orden = ?");
                if (!$chk) {
                    throw new Exception("Prepare SELECT validación falló: " . $this->conn->error);
                }
                $chk->bind_param("i", $idOrdenEmpresa);
                $chk->execute();
                $r = $chk->get_result()->fetch_assoc();
                $chk->close();

                if (!$r) {
                    throw new Exception("UPDATE no afectó filas: NO existe id_orden_empresa={$idOrdenEmpresa} en ordenes_empresas.");
                }
                throw new Exception("UPDATE no afectó filas: el registro existe pero estado_venta ya era {$r['estado_venta']} (o mismo valor).");
            }

            // C) COMMIT
            if (!$this->conn->commit()) {
                throw new Exception("Commit falló: " . $this->conn->error);
            }

            //$this->generarFacturaDomicilio($fact, $idOrdenEmpresa);

            return [
                'error' => false,
                'msg'   => 'Guía registrada y estado_venta actualizado.',
                'debug' => [
                    'idOrdenEmpresa'   => $idOrdenEmpresa,
                    'guiaMasterId'     => $guiaMasterId,
                    'barcode_entrega'  => $barcode,
                    'labelUrl'         => $labelUrl,
                    'barcode_pickup'   => $barcodePickup,
                    'insert_id'        => $idOrdenServientrega,
                    'affected_insert'  => $affectedInsert,
                    'affected_update'  => $affectedUpdate,
                ],
                'servientrega' => $resGuia
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return [
                'error' => true,
                'msg'   => 'Transacción fallida: ' . $e->getMessage(),
                'debug' => [
                    'idOrdenEmpresa' => $idOrdenEmpresa,
                    'guiaMasterId'   => $guiaMasterId,
                    'barcode'        => $barcode,
                    'labelUrl'       => $labelUrl,
                    'sqlInsert'      => $sqlInsert ?? null,
                    'sqlUpdate'      => $sqlUpdate ?? null,
                    'db_error'       => $this->conn->error ?? null,
                ],
                'servientrega' => $resGuia
            ];
        }
    }

    public function generarFacturaDomicilio($fact, $id_orden_empresa)
    {
        $producto_id = "EMaxy7qpI1R68b5G"; //domicilio
        $producto_nombre = "Gestión Logística - Envío a domicilio.";
        $producto_descripcion = "Servicio de coordinación logística y envío a domicilio mediante operador logístico aliado.";

        $orden = $this->getOrdenEmpresaByIdEmpresa($id_orden_empresa);
        $valor = $orden[0]["total_envio"];
        $id_empresa = $orden[0]["id_empresa"];

        // Configurar zona horaria de Ecuador
        date_default_timezone_set('America/Guayaquil');

        // Generar la fecha actual en formato dd/mm/YYYY
        $fecha_emision = date("d/m/Y");

        // Inicializamos valores
        $ruc = null;
        $cedula = null;

        // Verificamos el tipo de identificación
        if ($fact["tipo_identificacion"] === "ruc") {
            $ruc = $fact["numero_identificacion"];
        } elseif ($fact["tipo_identificacion"] === "cedula") {
            $cedula = $fact["numero_identificacion"];
        }

        // IVA incluido en $valor
        $porcentajeIva = 15; // 15%
        $total_con_iva = round((float)$valor, 2);

        // extraer base e IVA desde el total
        $base = round($total_con_iva / (1 + $porcentajeIva / 100), 2);

        // calcular IVA como diferencia para cuadrar exactamente con 2 decimales
        $iva = round($total_con_iva - $base, 2);

        // si por redondeo queda un centavo suelto, lo ajustamos en la base
        if (round($base + $iva, 2) !== $total_con_iva) {
            $base = round($total_con_iva - $iva, 2);
        }

        //numero factura
        $query = "SELECT numero_factura 
          FROM facturas 
          ORDER BY id_factura DESC 
          LIMIT 1";
        $result = $this->conn->query($query);
        $lastFactura = $result->fetch_assoc();

        /*if ($lastFactura && !empty($lastFactura['numero_factura'])) {
            $ultimoNumero = $lastFactura['numero_factura'];
        }

        // Separar por guiones
        list($establecimiento, $punto, $secuencial) = explode("-", $ultimoNumero);

        // Aumentar el secuencial
        $nuevoSecuencial = str_pad(((int)$secuencial + 1), 9, "0", STR_PAD_LEFT);

        // Construir el nuevo número de factura
        $numero_factura = $establecimiento . "-" . $punto . "-" . $nuevoSecuencial;*/

        $ultimoNumero = $lastFactura['numero_factura'] ?? null;

        if (!$ultimoNumero) {
            // valor por defecto (ajústalo a tu establecimiento/punto)
            $ultimoNumero = "001-007-000000000";
        }

        $parts = explode("-", $ultimoNumero);
        if (count($parts) !== 3) {
            $parts = ["001", "007", "000000000"]; // fallback seguro
        }

        [$establecimiento, $punto, $secuencial] = $parts;

        $nuevoSecuencial = str_pad(((int)$secuencial + 1), 9, "0", STR_PAD_LEFT);
        $numero_factura  = "{$establecimiento}-{$punto}-{$nuevoSecuencial}";

        $data = [
            "pos" => "58799fc1-67a9-4ef9-b3dc-1add00a8288c",
            "fecha_emision" => $fecha_emision,
            "tipo_documento" => "FAC",
            "documento" => $numero_factura,
            "estado" => "P",
            "autorizacion" => "",
            "electronico" => true,
            "caja_id" => null,
            "cliente" => [
                "ruc" => $ruc,
                "cedula" => $cedula,
                "razon_social" => $fact["razon_social"],
                "telefonos" => $fact["telefono"],
                "direccion" => $fact["direccion"],
                "tipo" => "N",
                "email" => $fact["correo"],
                "es_extranjero" => false
            ],
            "vendedor" => null,
            "descripcion" => $producto_descripcion,
            "subtotal_0" => 0.00,
            "subtotal_12" => $base,     // base gravada
            "iva" => $iva,              // 15% de $valor
            "ice" => 0.00,
            "servicio" => 0.00,
            "total" => $total_con_iva,          // base + IVA
            "detalles" => [[
                "producto_id" => $producto_id,
                "producto_nombre" => $producto_nombre,
                "cantidad" => "1.0",
                "precio" => $base,        // precio sin IVA
                "porcentaje_descuento" => "0.0",
                "porcentaje_iva" => $porcentajeIva,
                "porcentaje_ice" => null,
                "valor_ice" => "0.0",
                "base_cero" => "0.0",
                "base_gravable" => $base, // coincide con subtotal_12
                "base_no_gravable" => "0.0"
            ]]
        ];

        //var_dump($data);


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/documento/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ELPAz3khSjp7kh4Dqnu9kjK7D4R7WEC8bBD2k2yXcrU'
            ),
        ));

        $respons = curl_exec($curl);
        $respons = json_decode($respons, true);
        //var_dump($respons);
        curl_close($curl);


        $stmt = $this->conn->prepare("INSERT INTO facturas( id_factura_contifico, id_cliente, numero_factura, descripcion, tipo) values(?,?,?,'domicilio','E')");
        $stmt->bind_param("sss",  $respons["id"], $id_empresa, $respons["documento"]);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }


    public function getOrdenes($id_usuario, $id_empresa)
    {
        $user_permisos = $this->getPermisosByUser($id_usuario);
        $user_permisos = array_filter($user_permisos, function ($permiso) {
            return $permiso['permiso'] == 'Ordenes';
        });
        $user_permisos = array_values($user_permisos)[0];
        $response = array();

        if ($user_permisos["valor"] == "true") {
            switch ($user_permisos["levels"]) {
                case 'Fulmuv':
                    $stmt = $this->conn->prepare("SELECT 
                        o.*, 
                        e.nombre AS empresa, 
                        cl.nombres AS cliente_nombres,
                        cl.apellidos AS cliente_apellidos,
                        cl.cedula AS cliente_cedula,
                        cl.telefono AS cliente_telefono,
                        cl.correo AS cliente_correo,
                        CASE 
                            WHEN o.id_area = 'Todas' THEN 'Todas' 
                            ELSE a.nombre 
                        END AS area
                    FROM ordenes_empresas o
                    INNER JOIN ordenes ord ON ord.id_orden = o.id_ordenes
                    INNER JOIN clientes cl ON cl.id_cliente = ord.id_cliente
                    INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                    LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                    WHERE o.estado = 'A' AND o.orden_estado = 'aprobada' ORDER BY o.created_at DESC;");
                    break;

                case 'Empresa':
                    $stmt = $this->conn->prepare("SELECT 
                        o.*, 
                        e.nombre AS empresa,  
                        cl.nombres AS cliente_nombres,
                        cl.apellidos AS cliente_apellidos,
                        cl.cedula AS cliente_cedula,
                        cl.telefono AS cliente_telefono,
                        cl.correo AS cliente_correo,
                        CASE 
                            WHEN o.id_area = 'Todas' THEN 'Todas' 
                            ELSE a.nombre 
                        END AS area
                    FROM ordenes_empresas o
                    INNER JOIN ordenes ord ON ord.id_orden = o.id_ordenes
                    INNER JOIN clientes cl ON cl.id_cliente = ord.id_cliente
                    INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                    LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                    WHERE o.estado = 'A' AND o.id_empresa = ?
                    ORDER BY o.created_at DESC;");
                    $stmt->bind_param("s", $id_empresa);
                    break;

                case 'Sucursal':

                    $stmt = $this->conn->prepare("SELECT 
                        o.*, 
                        e.nombre AS empresa, 
                        s.nombre AS sucursal, 
                        cl.nombres AS cliente_nombres,
                        cl.apellidos AS cliente_apellidos,
                        cl.cedula AS cliente_cedula,
                        cl.telefono AS cliente_telefono,
                        cl.correo AS cliente_correo,
                        CASE 
                            WHEN o.id_area = 'Todas' THEN 'Todas' 
                            ELSE a.nombre 
                        END AS area
                    FROM ordenes_empresas o
                    INNER JOIN ordenes ord ON ord.id_orden = o.id_ordenes
                    INNER JOIN clientes cl ON cl.id_cliente = ord.id_cliente
                    INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                    INNER JOIN sucursales s ON s.id_sucursal = o.id_sucursal
                    LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                    WHERE o.estado = 'A' AND o.id_sucursal = ?
                    ORDER BY o.created_at DESC;");
                    $stmt->bind_param("s", $id_empresa);
                    break;
                case 'Vendedor':
                    $stmt = $this->conn->prepare("SELECT 
                        o.*, 
                        e.nombre AS empresa, 
                        cl.nombres AS cliente_nombres,
                        cl.apellidos AS cliente_apellidos,
                        cl.cedula AS cliente_cedula,
                        cl.telefono AS cliente_telefono,
                        cl.correo AS cliente_correo,
                        CASE 
                            WHEN o.id_area = 'Todas' THEN 'Todas' 
                            ELSE a.nombre 
                        END AS area
                    FROM ordenes_empresas o
                    INNER JOIN ordenes ord ON ord.id_orden = o.id_ordenes
                    INNER JOIN clientes cl ON cl.id_cliente = ord.id_cliente
                    INNER JOIN empresas e ON e.id_empresa = o.id_empresa
                    LEFT JOIN areas a ON a.id_area = o.id_area AND o.id_area != 'Todas'
                    WHERE o.estado = 'A' AND o.creation_user = ? ORDER BY o.created_at DESC;");
                    $stmt->bind_param("s", $id_usuario);
                    break;

                default:
                    break;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $date = new DateTime($row["created_at"]);
                $row["created_at"] = $date->format('M d, Y, g:i a');
                unset($row["productos"]);
                $row["guia"] = $this->getObtenerGuiaServientrega($row["id_orden"]);
                $response[] = $row;
            }
        }
        return $response;
    }

    public function getOrdenById($id_orden, $detalle = true)
    {

        $stmt = $this->conn->prepare("SELECT 
        oe.*, 
        cl.nombres,
        cl.cedula,
        cl.correo,
        o.numero_orden,
        o.subtotal,
        o.iva,
        o.numero_orden,
        o.total AS totalOrden,
        e.nombre AS empresa
        
        FROM ordenes o
        INNER JOIN ordenes_empresas oe ON oe.id_ordenes = o.id_orden
        INNER JOIN clientes cl ON cl.id_cliente = o.id_cliente
        INNER JOIN empresas e ON e.id_empresa = oe.id_empresa
        WHERE o.estado = 'A' and oe.estado = 'A' and cl.estado = 'A' and e.estado = 'A' AND oe.id_orden = ?");
        $stmt->bind_param("s", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $date = new DateTime($row["created_at"]);
            $row["created_at"] = $date->format('M d, Y, g:i a');
            $row["productos"] = json_decode($row['productos'], true);
            $row["pagos"] = $this->getOrdenPago($id_orden);

            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getNotasOrden($id_orden, $tipo_nota)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT o.*, u.nombres AS usuario, u.imagen
        FROM ordenes_notas o
        INNER JOIN usuarios u ON u.id_usuario = o.id_usuario
        WHERE  o.id_orden = ? AND o.estado = 'A' AND o.tipo_nota = ?");
        $stmt->bind_param("ss", $id_orden, $tipo_nota);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row["created_at"]);
            $row["created_at"] = $date->format('M d, Y, g:i a');
            $response[] = $row;
        }
        return $response;
    }

    public function getNotas($id_empresa, $tiempo)
    {
        $response = array();

        // Define las variables para manejar la fecha
        $today = new DateTime();
        $startDate = $today->format('Y-m-d H:i:s'); // Fecha y hora actual

        switch ($tiempo) {
            case "D":
                // Obtener el comienzo del día actual
                $startDate = $today->format('Y-m-d 00:00:00');
                break;
            case "S":
                // Obtener el comienzo de la semana (7 días atrás)
                $startDate = $today->modify('-7 days')->format('Y-m-d H:i:s');
                break;
            case "M":
                // Obtener el comienzo del mes (30 días atrás)
                $startDate = $today->modify('-30 days')->format('Y-m-d H:i:s');
                break;
            default:
                // Si no se reconoce el tiempo, devolver todas las notas
                $startDate = '2024-01-01 00:00:00'; // Fecha muy antigua para devolver todos los resultados
        }

        // Prepara la consulta SQL con la fecha de inicio calculada
        $stmt = $this->conn->prepare("SELECT on2.*, u.nombres AS usuario 
        FROM ordenes_notas on2
        INNER JOIN ordenes_empresas oe ON oe.id_orden = on2.id_orden
        INNER JOIN usuarios u ON u.id_usuario = on2.id_usuario
        WHERE oe.id_empresa = ? AND on2.created_at >= ? AND on2.tipo_nota = 'E'
        ORDER BY on2.created_at DESC");

        $stmt->bind_param("ss", $id_empresa, $startDate);

        // Ejecuta la consulta y procesa los resultados
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row["created_at"]);
            $row["created_at"] = $date->format('M d, Y, g:i a');
            $row["tiempo"] = $this->tiempoTranscurrido($row["created_at"]);
            $response[] = $row;
        }

        return $response;
    }

    private function tiempoTranscurrido($fecha)
    {
        $fecha = new DateTime($fecha);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($fecha);

        if ($diferencia->y > 0) {
            return "Hace " . $diferencia->y . " años";
        } elseif ($diferencia->m > 0) {
            return "Hace " . $diferencia->m . " meses";
        } elseif ($diferencia->d > 0) {
            if ($diferencia->d == 1) {
                return "Hace " . $diferencia->d . " día";
            } else {
                return "Hace " . $diferencia->d . " días";
            }
        } elseif ($diferencia->h > 0) {
            return "Hace " . $diferencia->h . "h";
        } elseif ($diferencia->i > 0) {
            return "Hace " . $diferencia->i . "m";
        } elseif ($diferencia->s > 0) {
            return "Hace " . $diferencia->s . "s";
        } else {
            return "Justo ahora";
        }
    }

    public function deleteOrden($id_ordenes, $id_usuario)
    {
        foreach ($id_ordenes as $id_orden) {
            $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET estado = 'E', orden_estado = 'eliminada' WHERE id_orden = ?");
            $stmt->bind_param("s", $id_orden);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                $accion = "Orden #" . $id_orden . " eliminada.";
                $this->createOrdenNota($id_orden, $id_usuario,  $accion, "trash-alt", 'E');
            } else {
                return  "Ocurrió un error al eliminar la orden " . $id_orden;
            }
        }
        return RECORD_UPDATED_SUCCESSFULLY;
    }

    public function updateEstadoOrden($id_ordenes, $orden_estado, $id_usuario)
    {
        foreach ($id_ordenes as $id_orden) {
            $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET orden_estado = ? WHERE id_orden = ?");
            $stmt->bind_param("ss", $orden_estado, $id_orden);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                switch ($orden_estado) {
                    case 'aprobada':
                        $tipo = "check";
                        break;
                    case 'procesada':
                        $tipo = "store-alt";
                        break;
                    case 'enviada':
                        $tipo = "truck";
                        break;
                    case 'completada':
                        $tipo = "check-circle";
                        break;
                    default:
                        $tipo = "";
                        break;
                }
                $accion = "Orden #" . $id_orden . " " . $orden_estado . ".";
                $this->createOrdenNota($id_orden, $id_usuario,  $accion, $tipo, 'E');
                //$this->createOrdenNota($id_orden, $id_usuario,  $accion, $tipo, 'E');
                $this->notificaEvento('orden_' . $orden_estado, null, null, $id_orden);
            } else {
                return  "Ocurrió un error al actualizar el estado de la orden " . $id_orden;
            }
        }
        return RECORD_UPDATED_SUCCESSFULLY;
    }

    public function updateEstadoVerificacion($id_verificacion, $estado, $motivo)
    {
        // 1) Obtener id_empresa por id_verificacion
        $stmt = $this->conn->prepare("SELECT id_empresa FROM verificacion_empresa WHERE id_verificacion = ?");
        $stmt->bind_param("i", $id_verificacion);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return RECORD_UPDATED_FAILED;
        }

        $id_empresa = (int)$row["id_empresa"];

        // 2) Setear campos según estado
        $obs = "";
        $rechazo = "0";

        if ((int)$estado === 0) {
            $obs = $motivo ?? "";
            $rechazo = "1";

            $stmt = $this->conn->prepare("
            UPDATE verificacion_empresa
            SET verificado = ?, observacion_verificacion = ?, rechazo_verificacion_empresa = ?
            WHERE id_verificacion = ?
        ");
            $stmt->bind_param("issi", $estado, $obs, $rechazo, $id_verificacion);
        } else { // estado = 1
            $obs = "";
            $rechazo = "0";

            $stmt = $this->conn->prepare("
            UPDATE verificacion_empresa
            SET verificado = ?, observacion_verificacion = ?, rechazo_verificacion_empresa = ?
            WHERE id_verificacion = ?
        ");
            $stmt->bind_param("issi", $estado, $obs, $rechazo, $id_verificacion);
        }

        $result = $stmt->execute();
        $stmt->close();

        if (!$result) {
            return RECORD_UPDATED_FAILED;
        }

        // 3) Enviar correos según estado (no detiene el update si falla el correo)
        try {
            if ((int)$estado === 0) {
                $this->correoVerificacionRechazada($id_empresa, $obs);
            } else {
                $this->correoVerificacionAprobada($id_empresa);
            }
        } catch (\Throwable $e) {
            // opcional: log error
            // error_log($e->getMessage());
        }

        return RECORD_UPDATED_SUCCESSFULLY;
    }



    // ISO

    public function getOrdenesIso($id_usuario)
    {
        $user_permisos = $this->getPermisosByUser($id_usuario);
        $user_permisos = array_filter($user_permisos, function ($permiso) {
            return $permiso['permiso'] == 'Ordenes';
        });
        $user_permisos = array_values($user_permisos)[0];
        $response = array();

        if ($user_permisos["valor"] == "true") {
            switch ($user_permisos["levels"]) {
                case 'Fulmuv':
                    $stmt = $this->conn->prepare("SELECT o.*
                    FROM ordenes_iso o
                    
                    WHERE o.estado = 'A' ORDER BY o.created_at DESC;");
                    break;
                default:
                    break;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $date = new DateTime($row["created_at"]);
                $row['ordenes'] =  $this->getOrdenById($row["id_orden_empresa"]);
                $row['grupo_entrega'] =  $this->getObtenerGuiaServientrega($row["id_orden_empresa"]);
                // $ordenes = json_decode($row['ordenes'], true);
                // $row['ordenes'] = [];
                // foreach ($ordenes as $orden) {
                //     $row['ordenes'][] =  $this->getOrdenById($orden, false);
                // }

                $row["created_at"] = $date->format('M d, Y, g:i a');
                // unset($row["productos"]);
                $response[] = $row;
            }
        }
        return $response;
    }

    public function getOrdenIsoById($id_orden_iso, $detalle = true)
    {
        $stmt = $this->conn->prepare("SELECT o.*
                    FROM ordenes_iso o
                    WHERE o.estado = 'A' AND o.id_orden_iso = ?;");
        $stmt->bind_param("s", $id_orden_iso);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row['ordenes'] =  $this->getOrdenById($row["id_orden_empresa"]);
            $date = new DateTime($row["created_at"]);
            // $ordenes = json_decode($row['ordenes'], true);
            // if ($detalle) {
            //     // foreach ($ordenes as $orden) {
            //     $row['ordenes'] =  $this->getOrdenById($row["id_orden_empresa"]);
            //     // }
            // }
            $row["created_at"] = $date->format('M d, Y, g:i a');
            $response[] = $row;
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getOrdenIsoByIdEmpresa($id_orden_empresa, $detalle = true)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT o.*
                    FROM ordenes_iso o
                    WHERE o.estado = 'A' AND o.id_orden_empresa = ?;");
        $stmt->bind_param("s", $id_orden_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    // public function createOrdenIso($id_ordenes, $id_usuario)
    // {
    //     $total = 0;
    //     foreach ($id_ordenes as $id_orden) {
    //         $orden = $this->getOrdenById($id_orden);
    //         if ($orden != RECORD_DOES_NOT_EXIST) {
    //             $total += $orden["total"];
    //         } else {
    //             return  "Ocurrió un error al obtener los detalles de la orden " . $id_orden;
    //         }
    //     }

    //     $procesar = $this->updateEstadoOrden($id_ordenes, "procesada", $id_usuario);

    //     if (strpos($procesar, "error")) {
    //         return $procesar;
    //     } else if ($procesar == RECORD_UPDATED_SUCCESSFULLY) {

    //         $jsonOrdenes = json_encode($id_ordenes);
    //         $fecha = date('Y-m-d H:i:s');
    //         $stmt = $this->conn->prepare("INSERT INTO ordenes_iso(ordenes, total, created_at, updated_at) values(?,?,?,?)");
    //         $stmt->bind_param("ssss", $jsonOrdenes, $total, $fecha, $fecha);
    //         $result = $stmt->execute();
    //         $id = $stmt->insert_id;
    //         $stmt->close();
    //         if ($result) {
    //             $accion = "Orden Fulmuv #" . $id . " creada.";
    //             $this->createOrdenNota($id, $id_usuario, $accion, "store-alt", 'I');
    //             return RECORD_CREATED_SUCCESSFULLY;
    //         } else {
    //             return RECORD_CREATION_FAILED;
    //         }
    //     } else {
    //         return "Ocurrió un error, intente despues.";
    //     }
    // }

    public function createOrdenIso($id_ordenes, $id_usuario)
    {
        // Permite recibir un solo id o un array de ids
        if (!is_array($id_ordenes)) $id_ordenes = [$id_ordenes];

        $creadas = [];
        $errores = [];

        foreach ($id_ordenes as $id_orden) {

            // 1) Traer la orden
            $orden = $this->getOrdenById($id_orden);
            if ($orden == RECORD_DOES_NOT_EXIST) {
                $errores[] = "La orden $id_orden no existe.";
                continue;
            }

            // 2) Calcular el peso total desde el JSON productos
            //    Soporta llaves comunes: peso / weight, cantidad / qty, y opcional peso_total
            $pesoTotal = 0.0;
            // $productos = json_decode($orden['productos'] ?? '[]', true);
            $productos = $orden['productos'];
            if (is_array($productos)) {

                foreach ($productos as $p) {
                    $cantidad = isset($p['cantidad']) ? (float)$p['cantidad']
                        : (isset($p['qty']) ? (float)$p['qty'] : 1.0);

                    $pesoUnit = isset($p['peso'])   ? (float)$p['peso']
                        : (isset($p['weight']) ? (float)$p['weight'] : 0.0);

                    // Si ya viene "peso_total" lo usamos; si no, calculamos (peso * cantidad)
                    $pesoItem = isset($p['peso']) ? (float)$p['peso'] : ($pesoUnit * $cantidad);

                    $pesoTotal += $pesoItem;
                }
            }

            // 3) Actualizar estado de la orden a "procesada"
            $proc = $this->updateEstadoOrden([$id_orden], "procesada", $id_usuario);
            if (strpos((string)$proc, "error") !== false) {
                $errores[] = "Error al procesar orden $id_orden: $proc";
                continue;
            }

            $total = $this->calcularTotalEnvio($orden["id_trayecto"], $pesoTotal);

            // 4) Insertar registro unitario en ordenes_iso
            $fecha = date('Y-m-d H:i:s');
            $stmt = $this->conn->prepare(
                "INSERT INTO ordenes_iso (id_orden_empresa, total, total_peso, created_at, updated_at, id_trayecto)
             VALUES (?,?,?,?,?,?)"
            );

            $stmt->bind_param("sddssi", $id_orden, $total, $pesoTotal, $fecha, $fecha, $orden["id_trayecto"]);
            $ok = $stmt->execute();
            $id_iso = $stmt->insert_id;
            $stmt->close();

            if ($ok) {

                $this->updateOrdenIso($id_orden, $total);
                // Nota/bitácora opcional
                $this->createOrdenNota(
                    $id_iso,
                    $id_usuario,
                    "Orden FulMuv #$id_iso creada para la orden empresa #$id_orden. Total: $total | Peso: $pesoTotal",
                    "store-alt",
                    'I'
                );
                $creadas[] = $id_iso;
            } else {
                $errores[] = "No se pudo crear el registro ISO para la orden $id_orden.";
            }
        }

        // Respuesta
        if (!empty($errores)) {
            return [
                "status"  => "partial",
                "creadas" => $creadas,
                "errores" => $errores
            ];
        }
        return RECORD_CREATED_SUCCESSFULLY;
    }

    // Calcula el total de envío según la tabla `trayecto`
    public function calcularTotalEnvio($id_trayecto, $total_peso)
    {
        // Sanitizar/normalizar peso
        $peso = max(0.01, (float)$total_peso);

        $stmt = $this->conn->prepare(
            "SELECT valor, adicional 
            FROM trayecto 
            WHERE id_trayecto = ? AND estado = 'A' 
            LIMIT 1"
        );
        $stmt->bind_param("i", $id_trayecto);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            // No existe o está inactivo
            return null; // o 0.0 si prefieres no cortar el flujo
        }

        $base      = (float)$row['valor'];
        $adicional = (float)$row['adicional'];

        // Base cubre hasta 1 kg. Sobrepeso se cobra por kg adicional (ceil).
        if ($adicional > 0) {
            $kgsAdicionales = max(0, ceil($peso - 1.0));
            $total = $base + ($adicional * $kgsAdicionales);
        } else {
            $total = $base; // documentos (sin adicional)
        }

        // Redondear a 2 decimales
        return round($total, 2);
    }


    public function updateEstadoOrdenIso($id_ordenes_iso, $orden_estado, $id_usuario)
    {
        foreach ($id_ordenes_iso as $id_orden_iso) {

            $orden_iso = $this->getOrdenIsoById($id_orden_iso, false);
            if ($orden_iso != RECORD_DOES_NOT_EXIST) {

                $ordenes = json_decode($orden_iso['ordenes'], true);
                $actualizar = $this->updateEstadoOrden($ordenes, $orden_estado, $id_usuario);

                if (strpos($actualizar, "error")) {
                    return $actualizar;
                } else if ($actualizar == RECORD_UPDATED_SUCCESSFULLY) {

                    $stmt = $this->conn->prepare("UPDATE ordenes_iso SET orden_estado = ? WHERE id_orden_iso = ?");
                    $stmt->bind_param("ss", $orden_estado, $id_orden_iso);
                    $result = $stmt->execute();
                    $stmt->close();
                    if ($result) {
                        switch ($orden_estado) {
                            case 'enviada':
                                $tipo = "truck";
                                break;
                            case 'completada':
                                $tipo = "check-circle";
                                break;
                            default:
                                $tipo = "";
                                break;
                        }
                        $accion = "Orden Fulmuv #" . $id_orden_iso . " " . $orden_estado . ".";
                        $this->createOrdenNota($id_orden_iso, $id_usuario,  $accion, $tipo, 'I');
                    } else {
                        return  "Ocurrió un error al actualizar el estado de la orden Fulmuv " . $id_orden_iso;
                    }
                } else {
                    return "Ocurrió un error, intente despues.";
                }
            } else {
                return  "Ocurrió un error al obtener los detalles de la orden " . $id_orden_iso;
            }
        }
        return RECORD_UPDATED_SUCCESSFULLY;
    }


    /* ORDENES */


    /* E-mails */

    public function getEmails()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM correo_plantilla WHERE estado = 'A' ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $response[] = $row;
        }
        return $response;
    }

    public function creatEmail($titulo, $cuerpo, $descripcion, $id_contenedor = 1)
    {
        $stmt = $this->conn->prepare("INSERT INTO correo_plantilla(titulo, cuerpo, descripcion, id_contenedor) VALUES(?, ?, ?, ?);");
        $stmt->bind_param("ssss", $titulo, $cuerpo, $descripcion, $id_contenedor);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateOrdenIso($id_orden, $total)
    {
        $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET estado_venta = 2 WHERE id_orden = ?");
        $stmt->bind_param("s", $id_orden);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateEstadoVenta($id_orden, $estado_venta)
    {
        $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET estado_venta = ? WHERE id_orden = ? AND estado = 'A'");
        $stmt->bind_param("is", $estado_venta, $id_orden);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($result && $affected > 0) {
            return RECORD_UPDATED_SUCCESSFULLY;
        }
        if ($result && $affected === 0) {
            return "error: No se actualizó el estado de venta. Verifique la orden.";
        }
        return RECORD_UPDATED_FAILED;
    }

    public function updateEmail($id_correo, $titulo, $cuerpo, $descripcion)
    {
        $stmt = $this->conn->prepare("UPDATE correo_plantilla SET titulo = ?, cuerpo = ?, descripcion = ? WHERE id_correo = ?;");
        $stmt->bind_param("ssss", $titulo, $cuerpo, $descripcion, $id_correo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function deleteEmail($id_correo)
    {
        $stmt = $this->conn->prepare("UPDATE correo_plantilla SET estado = 'E' WHERE id_correo = ?");
        $stmt->bind_param("s", $id_correo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getContenedor()
    {
        $stmt = $this->conn->prepare("SELECT * FROM contenedor WHERE estado = 'A'");
        // $stmt->bind_param("s", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    public function updateContenedor($color, $imagen, $id_contenedor = 1)
    {
        $stmt = $this->conn->prepare("UPDATE contenedor SET imagen=?, color=? WHERE id_contenedor = ?;");
        $stmt->bind_param("sss", $imagen, $color, $id_contenedor);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getCorreosControl()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM correo_control where estado = 'A' ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function updateCorreoControl($id_correo_control, $id_correo_plantilla)
    {
        $stmt = $this->conn->prepare("UPDATE correo_control SET id_correo_plantilla = ? WHERE id_correo_control = ?");
        $stmt->bind_param("ss", $id_correo_plantilla, $id_correo_control);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getCorreoControlByName($name)
    {
        $stmt = $this->conn->prepare("SELECT cp.* FROM correo_control cc, correo_plantilla cp WHERE cc.nombre = ? AND cc.estado = 'A' AND cc.id_correo_plantilla = cp.id_correo;");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            return $row;
        }
    }

    public function getCorreosDefault()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT c.*, u.correo FROM correos_default c INNER JOIN usuarios u ON u.id_usuario = c.id_usuario WHERE c.estado = 'A' ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function createCorreoDefault($id_user)
    {
        $stmt = $this->conn->prepare("INSERT INTO correos_default(id_usuario) VALUES(?);");
        $stmt->bind_param("s", $id_user);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateCorreoDefault($id_correo_default, $id_usuario)
    {
        $stmt = $this->conn->prepare("UPDATE correos_default SET id_usuario = ? WHERE id_correo_default = ?");
        $stmt->bind_param("ss", $id_usuario, $id_correo_default);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    public function notificaEvento($correoControl, $body = "", $email = null, $id_orden)
    {

        $correo_contenedor = $this->getContenedor();
        $correo_control = $this->getCorreoControlByName($correoControl);
        $cliente = $this->getClienteByIdOrden($id_orden);

        $mail = new PHPMailer();
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "Fulmuv");

        $mail->IsHTML(true);
        $mail->Subject = utf8_decode($correo_control["titulo"]);

        $correos_default = $this->getCorreosDefault();
        if ($correos_default) {
            for ($i = 0; $i < count($correos_default); $i = $i + 1) {
                $mail->AddBCC($correos_default[$i]["correo"]);
            }
        }

        if ($email != null) {
            $mail->AddAddress($email);
        }

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail->Body = utf8_decode('
        <!-- EMAIL WRAPPER -->
        <!doctype html>
        <html lang="es">
        <head>
        <meta charset="utf-8">
        <title>Notificación</title>
        <meta name="x-apple-disable-message-reformatting">
        <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
        </head>
        <body style="margin:0;padding:0;background:#f2f4f7;">
        <center style="width:100%;background:#f2f4f7;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:600px;margin:0 auto;">
            <tr>
                <td style="padding:24px 16px 8px 16px;text-align:center;">
                <!-- Logo: usa UNA de estas dos opciones -->
                <!-- Opción A: URL pública -->
                <img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" width="160" height="auto" alt="Logo" style="display:block;margin:0 auto 8px auto;border:0;">
                <!-- Opción B (si usas CID): <img src="cid:logo_cid" width="160" alt="Logo" style="display:block;margin:0 auto 8px auto;border:0;"> -->
                </td>
            </tr>

            <!-- Encabezado -->
            <tr>
                <td style="padding:0 16px 16px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;">
                    <tr>
                    <td style="padding:20px 24px;text-align:left;">
                        <h1 style="margin:0 0 6px 0;font:700 20px/1.2 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">
                        Ordenes FULMUV
                        </h1>
                        <p style="margin:0;font:14px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#475467;">
                        Ha recibido una nueva actualización
                        </p>
                    </td>
                    </tr>
                </table>
                </td>
            </tr>

            <tr>
                <td style="padding:0 16px 16px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;">
                    <tr>
                    <td style="padding:16px 20px;">
                        <table role="presentation" width="100%">
                        <tr>
                            <td style="font:600 16px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#101828;">
                            Orden #' . $id_orden . '
                            </td>
                            <td align="right" style="font:12px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#667085;">
                            ' . $fecha . '
                            </td>
                        </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:8px">
                        <tr>
                            <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                            <strong>Cliente:</strong> ' . $cliente["nombres"] . '
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#344054;">
                            ' . $body . '
                            </td>
                        </tr>
                        </table>

                        <!-- Botones -->
                        <div style="margin-top:12px;">
                        <a href="https://fulmuv.com/empresa/orden_detalle.php?id_orden=' . $id_orden . '" style="display:inline-block;background:#0ea5e9;color:#ffffff;text-decoration:none;border-radius:8px;padding:10px 16px;font:600 14px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;margin-right:8px;">Ver orden</a>
                        </div>
                    </td>
                    </tr>
                </table>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding:8px 16px 32px 16px;text-align:center;">
                <div style="font:12px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:#98a2b3;">
                    © 2025 FULMUV
                </div>
                </td>
            </tr>
            </table>
        </center>
        </body>
        </html>

        ');

        /*$mail->Body = utf8_decode('
        <!DOCTYPE html>
        <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width,initial-scale=1.0">
                <style>
                </style>
            </head>
            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
                <table style="width: 100%; margin: 0 auto; background-color: #ffffff; padding: 10px; border-radius: 10px;">
                    <tr>
                        <td style="text-align: center; background: ' . $correo_contenedor["color"] . '; padding: 10px;">
                            <img width="150" src="http://18.191.120.236/fulmuv/admin/' . $correo_contenedor["imagen"] . '" />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <h3 style="color: #333333;">' . $correo_control["cuerpo"] . '</h3>
                        </td>
                    </tr>
                    <tr>
                        ' . $body . '
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <p style="font-size: medium;"><strong>' . $correo_contenedor["footer"] . '</strong></p>
                        </td>
                    </tr>
                </table>
            </body>
        </html>');*/
        return $mail->send();
    }

    public function getClienteByIdOrden($id_orden)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM clientes c
            INNER JOIN ordenes o ON o.id_cliente = c.id_cliente
            INNER JOIN ordenes_empresas oe ON oe.id_ordenes = o.id_orden
            WHERE oe.id_orden = ?;");
        $stmt->bind_param("s", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                return $row;
            }
        } else {
            return false;
        }
    }

    private function getUltimaFacturaEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("
            SELECT numero_factura, id_factura_contifico, created_at
            FROM facturas
            WHERE id_cliente = ? AND descripcion = 'membresia' AND tipo = 'E'
            ORDER BY id_factura DESC
            LIMIT 1
        ");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function notificaCompra($codigo_autorizacion, $id_transaccion, $id_empresa, $valor, $membresia_nombre = '', $dias = null)
    {
        $empresa = $this->getEmpresaById($id_empresa);
        $correo_contenedor = $this->getContenedor();
        $factura = $this->getUltimaFacturaEmpresa($id_empresa);

        $nombrePlan = trim((string)$membresia_nombre);
        if ($nombrePlan === '') {
            $nombrePlan = 'Membresía FULMUV';
        }

        $periodoPlan = '';
        if ((int)$dias === 30) {
            $periodoPlan = 'Mensual';
        } elseif ((int)$dias === 180) {
            $periodoPlan = 'Semestral';
        } elseif ((int)$dias === 365) {
            $periodoPlan = 'Anual';
        }

        $monto = number_format((float)$valor, 2, '.', ',');
        $numeroFactura = $factura["numero_factura"] ?? 'En proceso de emisión';
        $titular = $empresa["nombre_titular"] ?? $empresa["nombre"] ?? 'Cliente FULMUV';
        $nombreEmpresa = $empresa["nombre"] ?? 'Tu empresa';
        $correoEmpresa = $empresa["correo"] ?? '';
        $provinciaEmpresa = $empresa["provincia"] ?? '';
        $cantonEmpresa = $empresa["canton"] ?? '';
        $tipoEstablecimiento = $empresa["tipo_tienda"] ?? ($empresa["tipo_establecimiento"] ?? 'Empresa');
        $logo = "https://fulmuv.com/admin/" . ltrim((string)($correo_contenedor["imagen"] ?? ''), "/");
        $titularSeguro = htmlspecialchars((string)$titular, ENT_QUOTES, "UTF-8");
        $nombreEmpresaSeguro = htmlspecialchars((string)$nombreEmpresa, ENT_QUOTES, "UTF-8");
        $nombrePlanSeguro = htmlspecialchars((string)$nombrePlan, ENT_QUOTES, "UTF-8");
        $periodoPlanSeguro = htmlspecialchars((string)$periodoPlan, ENT_QUOTES, "UTF-8");
        $numeroFacturaSeguro = htmlspecialchars((string)$numeroFactura, ENT_QUOTES, "UTF-8");
        $codigoAutorizacionSeguro = htmlspecialchars((string)$codigo_autorizacion, ENT_QUOTES, "UTF-8");
        $idTransaccionSeguro = htmlspecialchars((string)$id_transaccion, ENT_QUOTES, "UTF-8");
        $correoEmpresaSeguro = htmlspecialchars((string)$correoEmpresa, ENT_QUOTES, "UTF-8");
        $tipoEstablecimientoSeguro = htmlspecialchars((string)$tipoEstablecimiento, ENT_QUOTES, "UTF-8");
        $ubicacionEmpresa = trim($provinciaEmpresa . (!empty($cantonEmpresa) ? ' - ' . $cantonEmpresa : ''));
        $ubicacionEmpresaSeguro = htmlspecialchars((string)$ubicacionEmpresa, ENT_QUOTES, "UTF-8");
        $mensajePlan = !empty($periodoPlan)
            ? 'Tu plan <strong>' . $nombrePlanSeguro . '</strong> quedó habilitado en modalidad <strong>' . $periodoPlanSeguro . '</strong>.'
            : 'Tu plan <strong>' . $nombrePlanSeguro . '</strong> quedó habilitado correctamente.';
        $mensajeEmpresa = 'La cuenta de <strong>' . $nombreEmpresaSeguro . '</strong> ya se encuentra validada para continuar con la activación y gestión dentro de FULMUV.';

        $mail = new PHPMailer();
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "Fulmuv");

        $mail->IsHTML(true);
        $mail->Subject = utf8_decode('Pago exitoso FULMUV');

        $correos_default = $this->getCorreosDefault();
        if ($correos_default) {
            for ($i = 0; $i < count($correos_default); $i = $i + 1) {
                $mail->AddBCC($correos_default[$i]["correo"]);
            }
        }

        if ($empresa["correo"] != null) {
            $mail->AddAddress($empresa["correo"]);
        }

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail->Body = utf8_decode('
        <!doctype html>
        <html lang="es">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="x-apple-disable-message-reformatting">
        <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
        <title>Pago exitoso FULMUV</title>
        </head>
        <body style="margin:0;padding:0;background:#edf2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#edf2f7;padding:32px 12px;">
                <tr>
                    <td align="center">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;">
                            <tr>
                                <td style="padding-bottom:14px;text-align:center;">
                                    <img src="' . $logo . '" width="176" alt="FULMUV" style="display:block;margin:0 auto;height:auto;border:0;">
                                </td>
                            </tr>
                            <tr>
                                <td style="background:linear-gradient(135deg,#004e60 0%,#0f766e 55%,#13b5a6 100%);border-radius:26px 26px 0 0;padding:34px 30px 26px 30px;text-align:left;">
                                    <div style="display:inline-block;background:rgba(255,255,255,0.14);color:#ecfeff;font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;padding:8px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.12);">
                                        Comprobante de pago
                                    </div>
                                    <h1 style="margin:18px 0 10px 0;font-size:31px;line-height:1.14;color:#ffffff;">
                                        Recibimos tu pago correctamente
                                    </h1>
                                    <p style="margin:0;font-size:15px;line-height:1.75;color:#d9f7f3;">
                                        Hola ' . $titularSeguro . ', confirmamos el pago de <strong>$' . $monto . '</strong> asociado a <strong>' . $nombreEmpresaSeguro . '</strong>. Tu facturación fue procesada y el plan ya quedó registrado dentro de FULMUV.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#ffffff;border:1px solid #dbe4ea;border-top:none;border-radius:0 0 26px 26px;padding:28px 24px 26px 24px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:18px;">
                                        <tr>
                                            <td style="padding:18px 18px;background:linear-gradient(180deg,#f8fbfc 0%,#f1f8fa 100%);border:1px solid #dceaf0;border-radius:18px;">
                                                <div style="font-size:12px;color:#5b6b79;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Resumen principal</div>
                                                <div style="font-size:24px;color:#0f172a;font-weight:800;margin-bottom:6px;">$' . $monto . '</div>
                                                <div style="font-size:14px;color:#47606f;line-height:1.7;">' . $mensajePlan . '</div>
                                            </td>
                                        </tr>
                                    </table>

                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0 10px;">
                                        <tr>
                                            <td style="width:50%;padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Empresa</div>
                                                <div style="font-size:17px;font-weight:700;color:#0f172a;line-height:1.5;">' . $nombreEmpresaSeguro . '</div>
                                            </td>
                                            <td style="width:50%;padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Titular / contacto</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;line-height:1.5;">' . $titularSeguro . '</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Plan contratado</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;">' . $nombrePlanSeguro . (!empty($periodoPlanSeguro) ? ' · ' . $periodoPlanSeguro : '') . '</div>
                                            </td>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Fecha de confirmación</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;">' . $fecha . '</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Factura emitida</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;">' . $numeroFacturaSeguro . '</div>
                                            </td>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Tipo de establecimiento</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;">' . $tipoEstablecimientoSeguro . '</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Autorización</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;word-break:break-word;">' . $codigoAutorizacionSeguro . '</div>
                                            </td>
                                            <td style="padding:14px 16px;background:#f8fafc;border:1px solid #e7edf3;border-radius:14px;">
                                                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Transacción</div>
                                                <div style="font-size:16px;font-weight:700;color:#0f172a;word-break:break-word;">' . $idTransaccionSeguro . '</div>
                                            </td>
                                        </tr>
                                    </table>

                                    <div style="margin-top:22px;padding:18px;border-radius:18px;background:#fff7ed;border:1px solid #fed7aa;">
                                        <div style="font-size:14px;font-weight:700;line-height:1.6;color:#9a3412;margin-bottom:6px;">
                                            Información de la empresa pagadora
                                        </div>
                                        <div style="font-size:14px;line-height:1.8;color:#7c2d12;">
                                            ' . $mensajeEmpresa . '
                                            ' . (!empty($correoEmpresaSeguro) ? '<br>Correo de contacto: <strong>' . $correoEmpresaSeguro . '</strong>' : '') . '
                                            ' . (!empty($ubicacionEmpresaSeguro) ? '<br>Ubicación registrada: <strong>' . $ubicacionEmpresaSeguro . '</strong>' : '') . '
                                        </div>
                                    </div>

                                    <div style="margin-top:18px;padding:18px;border-radius:18px;background:#f4fbf9;border:1px solid #d9f0eb;">
                                        <div style="font-size:14px;font-weight:700;line-height:1.6;color:#21535b;margin-bottom:6px;">
                                            Siguiente paso
                                        </div>
                                        <div style="font-size:14px;line-height:1.75;color:#21535b;">
                                            En los próximos instantes recibirás también tu correo de acceso con las credenciales definidas durante el registro. Ya puedes comenzar a completar tu perfil, publicar contenido y administrar tu empresa desde el panel.
                                        </div>
                                    </div>

                                    <div style="text-align:center;padding-top:24px;">
                                        <a href="https://fulmuv.com/empresa/login.php" style="display:inline-block;background:#004e60;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 24px;border-radius:12px;">
                                            Ingresar a mi panel
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 10px 0 10px;text-align:center;font-size:12px;line-height:1.6;color:#7b8a97;">
                                    © ' . date("Y") . ' FULMUV. Gracias por confiar en nuestra plataforma para impulsar a ' . $nombreEmpresaSeguro . '.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ');
        return $mail->send();
    }


    /* E-mails */

    /* Dashboard */
    public function getTotalEmpresas()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_empresas FROM empresas WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row["total_empresas"];
        }
    }

    public function getTotalOrdenes()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_ordenes FROM ordenes_empresas;");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row["total_ordenes"];
        }
    }

    public function getTotalUsuarios()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total_usuarios FROM usuarios WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row["total_usuarios"];
        }
    }

    public function getTotalByEmpresas()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT e.nombre, COUNT(*) AS total
            FROM ordenes_empresas o
            INNER JOIN sucursales s 
            ON s.id_sucursal = o.id_sucursal 
            INNER JOIN empresas e
            ON e.id_empresa = s.id_empresa 
            GROUP BY s.id_empresa ;");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTotalOrdenesByEstado()
    {
        $response = array();

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'enviada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["enviadas"] = $row["total"];
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'creada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["creadas"] = $row["total"];
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'aprobada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["aprobadas"] = $row["total"];
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'completada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["completadas"] = $row["total"];
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'eliminada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["eliminadas"] = $row["total"];
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total
            FROM ordenes_empresas 
            WHERE orden_estado = 'procesada';");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["procesadas"] = $row["total"];
        }

        return $response;
    }

    public function getTotalOrdenesByHistory($startDate, $endDate)
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        $response = array();

        // Crear un intervalo de 1 mes
        $intervalo = new DateInterval('P1M');
        $periodo = new DatePeriod($startDate, $intervalo, $endDate);

        $meses = [];
        foreach ($periodo as $fecha) {
            $month = $fecha->format('m');
            $year = $fecha->format('Y');
            $nombre_mes = $fecha->format('F');

            $stmt = $this->conn->prepare("SELECT 
                    COUNT(CASE WHEN accion LIKE '%creada.%' THEN 1 END) AS total_creada,
                    COUNT(CASE WHEN accion LIKE '%enviada.%' THEN 1 END) AS total_enviada,
                    COUNT(CASE WHEN accion LIKE '%completada.%' THEN 1 END) AS total_completada,
                    COUNT(CASE WHEN accion LIKE '%procesada.%' THEN 1 END) AS total_procesada,
                    COUNT(CASE WHEN accion LIKE '%eliminada.%' THEN 1 END) AS total_eliminada,
                    COUNT(CASE WHEN accion LIKE '%aprobada.%' THEN 1 END) AS total_aprobada
                FROM 
                    ordenes_notas
                WHERE 
                    tipo_nota = 'E' 
                    AND MONTH(created_at) = ?
                    AND YEAR(created_at) = ?;");
            $stmt->bind_param("ss", $month, $year);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response[$nombre_mes] = $row;
            }
        }
        return $response;
    }
    /* Dashboard */

    public function getCorreoById($id_correo)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM correo_plantilla
        WHERE estado = 'A' AND id_correo = ?");
        $stmt->bind_param("s", $id_correo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            /* if ($detalle) {
                $row["sucursales"] = $this->getSucursalesByEmpresa($row["id_empresa"]);
                $row["usuarios"] = [];
            } */
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    // public function cargarExcel($data)
    // {
    //     $response = [];

    //     foreach ($data as $marca) {
    //         $marca = trim($marca);

    //         if ($marca == "") {
    //             continue;
    //         }

    //         // Verificar si ya existe la marca
    //         $stmt = $this->conn->prepare("SELECT id_marca FROM marcas WHERE nombre = ?");
    //         $stmt->bind_param("s", $marca);
    //         $stmt->execute();
    //         $result = $stmt->get_result();

    //         if ($result->num_rows == 0) {
    //             // Insertar marca nueva
    //             $stmtInsert = $this->conn->prepare("INSERT INTO marcas (nombre, estado) VALUES (?, 'A')");
    //             $stmtInsert->bind_param("s", $marca);
    //             if ($stmtInsert->execute()) {
    //                 $response[] = "Marca insertada: " . $marca;
    //             } else {
    //                 $response[] = "Error al insertar: " . $marca;
    //             }
    //         } else {
    //             $response[] = "Marca ya existe: " . $marca;
    //         }
    //     }

    //     return $response;
    // }

    // public function cargarExcel($data)
    // {
    //     $response = [];

    //     foreach ($data as $row) {
    //         $marca = trim($row['marca']);
    //         $modelo = trim($row['modelo']);
    //         $tipo = trim($row['tipo'] ?? '');
    //         $motor = trim($row['motor'] ?? '');
    //         //$traccion = trim($row['traccion'] ?? '');

    //         if ($modelo == "") {
    //             continue;
    //         }

    //         // Buscar o Insertar MARCA
    //         $id_marca = $this->buscarOInsertar('marcas', 'id_marca', 'nombre', $marca);

    //         // Buscar o Insertar TIPO AUTO
    //         $id_tipo = $this->buscarOInsertar('tipos_auto', 'id_tipo_auto', 'nombre', $tipo);

    //         // Buscar o Insertar MOTOR
    //         $id_motor = $this->buscarOInsertar('funcionamiento_motor', 'id_funcionamiento_motor', 'nombre', $motor);

    //         // Buscar o Insertar TRACCION
    //         //$id_traccion = $this->buscarOInsertar('tipo_traccion', 'id_tipo_traccion', 'nombre', $traccion);

    //         // Verificar si el modelo ya existe para esa marca
    //         $stmt = $this->conn->prepare("SELECT id_modelos_autos FROM modelos_autos WHERE nombre = ? AND id_marca = ?");
    //         $stmt->bind_param("si", $modelo, $id_marca);
    //         $stmt->execute();
    //         $result = $stmt->get_result();

    //         if ($result->num_rows == 0) {
    //             // Insertar Modelo
    //             $stmtInsert = $this->conn->prepare("INSERT INTO modelos_autos(nombre, id_marca, id_tipo_auto, id_funcionamiento_motor, referencia, estado) VALUES (?,?,?,?,'AÉREOS', 'A')");
    //             $stmtInsert->bind_param("siii", $modelo, $id_marca, $id_tipo, $id_motor);

    //             if ($stmtInsert->execute()) {
    //                 $response[] = "Modelo insertado: $marca - $modelo";
    //             } else {
    //                 $response[] = "Error al insertar modelo: $marca - $modelo";
    //             }
    //         } else {
    //             $response[] = "Modelo ya existe: $marca - $modelo";
    //         }
    //     }

    //     return $response;
    // }

    public function cargarExcel($data)
    {
        $response = [];
        foreach ($data as $row) {
            $stmtInsert = $this->conn->prepare("INSERT INTO rutas (provincia_origen,canton_origen,provincia_destino,canton_destino,trayecto,aplica_domicilio,zona_peligrosa,agencia_1,direccion_1,agencia_2,direccion_2) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmtInsert->bind_param("sssssssssss", $row["provincia_origen"], $row["canton_origen"], $row["provincia_destino"], $row["canton_destino"], $row["trayecto"], $row["aplica_domicilio"], $row["zona_peligrosa"], $row["agencia_1"], $row["direccion_1"], $row["agencia_2"], $row["direccion_2"]);
            if ($stmtInsert->execute()) {
                $response[] = "Ruta insertada: " . $row["provincia_origen"] . "-" . $row["canton_origen"] . "-" . $row["provincia_destino"] . "-" . $row["canton_destino"] . "-" . $row["trayecto"];
            } else {
                $response[] = "Error al insertar ruta: " . $row["provincia_origen"] . "-" . $row["canton_origen"] . "-" . $row["provincia_destino"] . "-" . $row["canton_destino"] . "-" . $row["trayecto"];
            }
        }
        return $response;
    }

    private function buscarOInsertar($tabla, $campo_id, $campo_nombre, $valor)
    {
        if ($valor == "") return null;

        $stmt = $this->conn->prepare("SELECT $campo_id FROM $tabla WHERE $campo_nombre COLLATE utf8mb4_general_ci = ?");
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row[$campo_id];
        }

        $stmtInsert = $this->conn->prepare("INSERT INTO $tabla($campo_nombre, referencia, estado) VALUES (?, 'AÉREOS', 'A')");
        $stmtInsert->bind_param("s", $valor);
        $stmtInsert->execute();

        return $stmtInsert->insert_id;
    }

    /*public function cargarExcel($data){
        foreach($data as $item) {
            $nombre = trim($item['nombre']);
            $tipo_dato = trim($item['tipo_dato']);
    
            // Verificar que no exista (normalizado)
            $stmt = $this->conn->prepare("SELECT id_atributo FROM atributos WHERE UPPER(TRIM(nombre)) = UPPER(TRIM(?))");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if($result->num_rows == 0){
                // Insertar
                $insert = $this->conn->prepare("INSERT INTO atributos(nombre, tipo_dato) VALUES (?, ?)");
                $insert->bind_param("ss", $nombre, $tipo_dato);
                $insert->execute();
            }
        }
    }*/

    // public function cargarExcel($data){
    //     $response = [];

    //     foreach ($data as $item) {
    //         $categoriaNombre = trim($item["categoria"]);
    //         $subcategoriaNombre = trim($item["subcategoria"]);
    //         $productoNombre = trim($item["producto"]);

    //         // Evitar registros vacíos
    //         if (empty($categoriaNombre) || empty($subcategoriaNombre) || empty($productoNombre)) {
    //             continue;
    //         }

    //         // Insertar o buscar categoría
    //         $stmt = $this->conn->prepare("SELECT id_categoria FROM categorias WHERE nombre COLLATE utf8mb4_general_ci = ?");
    //         $stmt->bind_param("s", $categoriaNombre);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //         if ($row = $result->fetch_assoc()) {
    //             $id_categoria = $row["id_categoria"];
    //         } else {
    //             $stmtInsert = $this->conn->prepare("INSERT INTO categorias(nombre, estado) VALUES (?, 'A')");
    //             $stmtInsert->bind_param("s", $categoriaNombre);
    //             $stmtInsert->execute();
    //             $id_categoria = $stmtInsert->insert_id;
    //         }

    //         // Insertar o buscar subcategoría dentro de la categoría
    //         $stmt = $this->conn->prepare("SELECT id_sub_categoria FROM sub_categorias WHERE nombre COLLATE utf8mb4_general_ci = ? AND id_categoria = ?");
    //         $stmt->bind_param("si", $subcategoriaNombre, $id_categoria);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //         if ($row = $result->fetch_assoc()) {
    //             $id_sub_categoria = $row["id_sub_categoria"];
    //         } else {
    //             $stmtInsert = $this->conn->prepare("INSERT INTO sub_categorias(id_categoria, nombre, estado) VALUES (?, ?, 'A')");
    //             $stmtInsert->bind_param("is", $id_categoria, $subcategoriaNombre);
    //             $stmtInsert->execute();
    //             $id_sub_categoria = $stmtInsert->insert_id;
    //         }

    //         // Verificar si el producto ya existe
    //         $stmt = $this->conn->prepare("SELECT id_nombre_producto FROM nombres_productos WHERE nombre COLLATE utf8mb4_general_ci = ? AND categoria = ? AND sub_categoria = ?");
    //         $stmt->bind_param("sii", $productoNombre, $id_categoria, $id_sub_categoria);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //         if ($result->num_rows == 0) {
    //             $stmtInsert = $this->conn->prepare("
    //                 INSERT INTO nombres_productos (nombre, categoria, sub_categoria, estado) 
    //                 VALUES (?, ?, ?, 'A')
    //             ");
    //             $stmtInsert->bind_param("sii", $productoNombre, $id_categoria, $id_sub_categoria);
    //             if ($stmtInsert->execute()) {
    //                 $response[] = "Producto registrado: $productoNombre";
    //             } else {
    //                 $response[] = "Error al registrar: $productoNombre";
    //             }
    //         } else {
    //             $response[] = "Producto duplicado: $productoNombre";
    //         }
    //     }
    //     return $response;
    // }

    // public function cargarExcel($data)
    // {
    //     foreach ($data as $bloque) {
    //         $referencia = $bloque['referencia'];
    //         $atributos = $bloque['atributos'];

    //         $ids_atributos = [];

    //         foreach ($atributos as $atributo) {
    //             $nombre = trim($atributo['limpio']);
    //             $tipo_dato = strtoupper(trim($atributo['tipo_dato']));

    //             // Verificar si ya existe el atributo
    //             $stmt = $this->conn->prepare("SELECT id_atributo FROM atributos WHERE LOWER(nombre) = LOWER(?) AND estado = 'A' LIMIT 1");
    //             $stmt->bind_param("s", $nombre);
    //             $stmt->execute();
    //             $result = $stmt->get_result();

    //             if ($row = $result->fetch_assoc()) {
    //                 $ids_atributos[] = (int)$row['id_atributo'];
    //             } else {
    //                 // Insertar nuevo atributo
    //                 $stmtInsert = $this->conn->prepare("
    //                     INSERT INTO atributos (nombre, tipo_dato, estado, created_at, updated_at)
    //                     VALUES (?, ?, 'A', NOW(), NOW())
    //                 ");
    //                 $stmtInsert->bind_param("ss", $nombre, $tipo_dato);
    //                 $stmtInsert->execute();

    //                 // Obtener el id insertado
    //                 $nuevoId = $stmtInsert->insert_id;
    //                 $ids_atributos[] = $nuevoId;
    //             }
    //         }

    //         // Convertimos el array de IDs a JSON
    //         $jsonAtributos = json_encode($ids_atributos);

    //         // Actualizar todas las categorías con esa referencia
    //         $stmtUpdate = $this->conn->prepare("UPDATE categorias SET atributos = ? WHERE referencia = ?");
    //         $stmtUpdate->bind_param("ss", $jsonAtributos, $referencia);
    //         $stmtUpdate->execute();
    //     }

    //     return ["status" => "ok", "mensaje" => "Atributos actualizados e insertados correctamente."];
    // }


    /*public function cargarExcel($data){
        $response = [];
        foreach ($data as $item) {
            $categoriaNombre = trim($item["categoria"]);
            $servicios = $item["servicios"];

            if (empty($categoriaNombre) || !is_array($servicios)) {
                continue;
            }

            // Insertar la categoría si no existe
            $stmtCategoria = $this->conn->prepare("SELECT id_categoria FROM categorias WHERE nombre = ? AND tipo = 'servicio'");
            $stmtCategoria->bind_param("s", $categoriaNombre);
            $stmtCategoria->execute();
            $stmtCategoria->store_result();

            if ($stmtCategoria->num_rows > 0) {
                $stmtCategoria->bind_result($id_categoria);
                $stmtCategoria->fetch();
            } else {
                $tipo = "servicio";
                $referencia = strtoupper($categoriaNombre);
                $stmtInsert = $this->conn->prepare("INSERT INTO categorias (nombre, referencia, tipo, estado) VALUES (?, ?, ?, 'A')");
                $stmtInsert->bind_param("sss", $categoriaNombre, $referencia, $tipo);
                $stmtInsert->execute();
                $id_categoria = $stmtInsert->insert_id;
                $stmtInsert->close();
            }
            $stmtCategoria->close();

            // Insertar los servicios asociados a la categoría
            foreach ($servicios as $nombreServicio) {
                $nombreServicio = trim($nombreServicio);
                if (empty($nombreServicio)) continue;

                // Verifica si ya existe el servicio
                $stmtVerif = $this->conn->prepare("SELECT id_nombre_servicio FROM nombres_servicios WHERE nombre = ? AND categoria = ?");
                $stmtVerif->bind_param("si", $nombreServicio, $id_categoria);
                $stmtVerif->execute();
                $stmtVerif->store_result();

                if ($stmtVerif->num_rows === 0) {
                    $stmtInsertServ = $this->conn->prepare("INSERT INTO nombres_servicios (nombre, categoria, estado) VALUES (?, ?, 'A')");
                    $stmtInsertServ->bind_param("si", $nombreServicio, $id_categoria);
                    $stmtInsertServ->execute();
                    $stmtInsertServ->close();
                }

                $stmtVerif->close();
            }

            $response[] = ["categoria" => $categoriaNombre, "id" => $id_categoria, "total_servicios" => count($servicios)];
        }
        return $response;
    }*/

    public function deleteFileProducto($id_archivo)
    {
        $stmt = $this->conn->prepare("UPDATE archivos_productos SET estado = 'E' WHERE id_archivo_producto = ?");
        $stmt->bind_param("s", $id_archivo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function deleteFileEmpresa($id_archivo)
    {
        $stmt = $this->conn->prepare("UPDATE archivos_empresa SET estado = 'E' WHERE id_archivo_empresa = ?");
        $stmt->bind_param("s", $id_archivo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function deleteFileVehiculo($id_archivo)
    {
        $stmt = $this->conn->prepare("UPDATE archivos_vehiculos SET estado = 'E' WHERE id_archivo_vehiculo = ?");
        $stmt->bind_param("s", $id_archivo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function createFileProducto($id_producto, $archivo, $tipo)
    {
        $stmt = $this->conn->prepare("INSERT INTO archivos_productos (id_producto, archivo, tipo) VALUES(?, ?, ?);");
        $stmt->bind_param("sss", $id_producto, $archivo, $tipo);
        $result = $stmt->execute();
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            return $ultimo_id;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function createFileEmpresa($id_empresa, $archivo, $tipo, $titulo, $descripcion)
    {
        $stmt = $this->conn->prepare("INSERT INTO archivos_empresa (id_empresa, archivo, tipo, titulo, descripcion) VALUES(?, ?, ?, ?, ?);");
        $stmt->bind_param("sssss", $id_empresa, $archivo, $tipo, $titulo, $descripcion);
        $result = $stmt->execute();
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            return $ultimo_id;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateFileEmpresa($id_archivo_empresa, $titulo, $descripcion, $archivo = null)
    {
        $archivo = is_null($archivo) ? null : trim((string)$archivo);

        if ($archivo !== null && $archivo !== '') {
            $stmt = $this->conn->prepare("UPDATE archivos_empresa SET titulo = ?, descripcion = ?, archivo = ? WHERE id_archivo_empresa = ?;");
            $stmt->bind_param("sssi", $titulo, $descripcion, $archivo, $id_archivo_empresa);
        } else {
            $stmt = $this->conn->prepare("UPDATE archivos_empresa SET titulo = ?, descripcion = ? WHERE id_archivo_empresa = ?;");
            $stmt->bind_param("ssi", $titulo, $descripcion, $id_archivo_empresa);
        }
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    // public function getEmpresasTotalProductos()
    // {
    //     $response = array();
    //     $stmt = $this->conn->prepare(" SELECT DISTINCT e.*
    //     FROM empresas e
    //     INNER JOIN membresias_empresas me ON me.id_empresa = e.id_empresa AND me.estado = 'A'
    //     INNER JOIN membresias m ON m.id_membresia = me.id_membresia AND m.estado = 'A'
    //     WHERE e.estado = 'A'
    //       AND me.fecha_inicio <= NOW()
    //       AND me.fecha_fin >= NOW()");
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     while ($row = $result->fetch_assoc()) {
    //         $idEmpresa = (int)$row["id_empresa"];
    //         $membresia = $this->getMembresiaByEmpresa($idEmpresa);

    //         if (!$this->empresaTieneMembresiaFulmuvActiva($membresia)) {
    //             continue;
    //         }

    //         $empresaPayload = $this->buildProveedorEmpresaPayload($row, $membresia);
    //         $response[] = $empresaPayload;

    //         foreach ($empresaPayload["lista_sucursales"] as $sucursal) {
    //             $response[] = $this->buildProveedorSucursalPayload($sucursal, $empresaPayload);
    //         }
    //     }
    //     return $response;
    // }

    public function getEmpresasTotalProductos()
    {
        $response = array();
        $stmt = $this->conn->prepare(" SELECT DISTINCT e.*
        FROM empresas e
        INNER JOIN membresias_empresas me ON me.id_empresa = e.id_empresa AND me.estado = 'A'
        INNER JOIN membresias m ON m.id_membresia = me.id_membresia AND m.estado = 'A'
        WHERE e.estado = 'A'
          AND me.fecha_inicio <= NOW()
          AND me.fecha_fin >= NOW()");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $idEmpresa = (int)$row["id_empresa"];
            $membresia = $this->getMembresiaByEmpresa($idEmpresa);

            if ($this->getPublicEmpresaIdForCreator($idEmpresa, 'empresa') <= 0) {
                continue;
            }

            $empresaPayload = $this->buildProveedorEmpresaPayload($row, $membresia);
            $response[] = $empresaPayload;

            foreach ($empresaPayload["lista_sucursales"] as $sucursal) {
                $response[] = $this->buildProveedorSucursalPayload($sucursal, $empresaPayload);
            }
        }
        return $response;
    }

    private function empresaTieneMembresiaFulmuvActiva($membresia)
    {
        if (empty($membresia) || !is_array($membresia)) {
            return false;
        }

        $estado = strtoupper((string)($membresia["estado_membresia"] ?? ""));
        if ($estado !== "ACTIVA") {
            return false;
        }

        return $this->normalizarNombreMembresia($membresia["nombre"] ?? "") === "fulmuv";
    }

    private function getPublicEmpresaIdForCreator($idCreador, $tipoCreador = 'empresa')
    {
        $idCreador = (int)$idCreador;
        $tipoCreador = strtolower(trim((string)$tipoCreador));
        if ($idCreador <= 0) {
            return 0;
        }

        $cacheKey = $tipoCreador . ':' . $idCreador;
        if (array_key_exists($cacheKey, $this->publicCreatorActiveCache)) {
            return $this->publicCreatorActiveCache[$cacheKey];
        }

        if ($tipoCreador === 'sucursal') {
            $stmt = $this->conn->prepare("
                SELECT e.id_empresa
                FROM sucursales s
                INNER JOIN empresas e ON e.id_empresa = s.id_empresa
                WHERE s.id_sucursal = ?
                  AND s.estado = 'A'
                  AND e.estado = 'A'
                LIMIT 1
            ");
        } else {
            $stmt = $this->conn->prepare("
                SELECT e.id_empresa
                FROM empresas e
                WHERE e.id_empresa = ?
                  AND e.estado = 'A'
                LIMIT 1
            ");
        }

        if (!$stmt) {
            $this->publicCreatorActiveCache[$cacheKey] = 0;
            return 0;
        }

        $stmt->bind_param("i", $idCreador);
        $stmt->execute();
        $empresa = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $idEmpresa = (int)($empresa["id_empresa"] ?? 0);
        if ($idEmpresa <= 0 || !$this->empresaTieneMembresiaPublicaActiva($idEmpresa)) {
            $this->publicCreatorActiveCache[$cacheKey] = 0;
            return 0;
        }

        $this->publicCreatorActiveCache[$cacheKey] = $idEmpresa;
        return $idEmpresa;
    }

    private function empresaTieneMembresiaPublicaActiva($idEmpresa)
    {
        $idEmpresa = (int)$idEmpresa;
        if ($idEmpresa <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare("
            SELECT 1
            FROM membresias_empresas me
            INNER JOIN membresias m ON m.id_membresia = me.id_membresia
            WHERE me.id_empresa = ?
              AND me.estado = 'A'
              AND m.estado = 'A'
              AND (me.fecha_inicio IS NULL OR me.fecha_inicio <= NOW())
              AND (me.fecha_fin IS NULL OR me.fecha_fin >= NOW())
            LIMIT 1
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $idEmpresa);
        $stmt->execute();
        $hasMembership = (bool)$stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $hasMembership;
    }

    private function buildProveedorEmpresaPayload(array $empresa, $membresia = null)
    {
        $idEmpresa = (int)$empresa["id_empresa"];
        $empresa["tipo_registro"] = "empresa";
        $empresa["tipo"] = "empresa";
        $empresa["id_ruta"] = $idEmpresa;
        $empresa["id_principal_empresa"] = $idEmpresa;
        $empresa["nombre_empresa_padre"] = $empresa["nombre"] ?? "";
        $empresa["membresia"] = $membresia ?: $this->getMembresiaByEmpresa($idEmpresa);
        $empresa["verificacion"] = $this->getVerificacionCuentaEmpresa($idEmpresa);
        $empresa["total_productos"] = $this->getTotalProductosbyIdEmpresa($idEmpresa);
        $empresa["categorias_principales"] = $this->getCategoriasPrincipalesDisponiblesByEmpresa($idEmpresa);
        $empresa["categorias"] = $this->getCategoriasByEmpresa($idEmpresa);
        $empresa["lista_sucursales"] = $this->getSucursalesByEmpresa($idEmpresa);
        $empresa["total_sucursales"] = count($empresa["lista_sucursales"]);

        return $empresa;
    }

    private function getCategoriasPrincipalesDisponiblesByEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT cp.id_categoria_principal, cp.nombre
            FROM productos p
            INNER JOIN categorias c
                ON c.estado = 'A'
               AND JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
            INNER JOIN categorias_principales cp
                ON cp.estado = 'A'
               AND JSON_CONTAINS(c.categoria_principal, JSON_QUOTE(CAST(cp.id_categoria_principal AS CHAR)), '$')
            WHERE p.id_empresa = ?
              AND p.estado = 'A'
            ORDER BY cp.nombre ASC
        ");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        $stmt->close();

        return $categorias;
    }
    private function buildProveedorSucursalPayload(array $sucursal, array $empresaPayload)
    {
        $payload = $sucursal;
        $idSucursal = (int)($sucursal["id_sucursal"] ?? 0);
        $payload["tipo_registro"] = "sucursal";
        $payload["tipo"] = "sucursal";
        $payload["id_ruta"] = $idSucursal;
        $payload["id_principal_empresa"] = (int)$empresaPayload["id_empresa"];
        $payload["nombre_empresa_padre"] = $empresaPayload["nombre"] ?? "";
        $payload["img_path"] = !empty($sucursal["imagen"]) ? $sucursal["imagen"] : ($empresaPayload["img_path"] ?? "");
        $payload["membresia"] = $empresaPayload["membresia"] ?? null;
        $payload["verificacion"] = $empresaPayload["verificacion"] ?? [];
        $payload["categorias_referencia"] = $empresaPayload["categorias_referencia"] ?? null;
        $payload["categorias"] = $empresaPayload["categorias"] ?? [];
        $payload["total_productos"] = $this->getTotalProductosByIdSucursal($idSucursal);
        $payload["lista_sucursales"] = [];
        $payload["total_sucursales"] = $empresaPayload["total_sucursales"] ?? 0;
        $payload["tiempo_anos"] = $empresaPayload["tiempo_anos"] ?? ($sucursal["tiempo_anos"] ?? 0);
        $payload["tiempo_meses"] = $empresaPayload["tiempo_meses"] ?? ($sucursal["tiempo_meses"] ?? 0);

        return $payload;
    }

    private function getProductoIdsBySucursalVendedor($id_sucursal)
    {
        $id_sucursal = (int)$id_sucursal;
        if ($id_sucursal <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT productos
            FROM catalogos
            WHERE estado = 'A' AND id_sucursal = ? AND tipo = 'V'
        ");
        $stmt->bind_param("i", $id_sucursal);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $productos = json_decode($row["productos"] ?? "[]", true);
            if (!is_array($productos)) {
                continue;
            }

            foreach ($productos as $producto) {
                $idProducto = (int)($producto["id_producto"] ?? 0);
                if ($idProducto > 0) {
                    $ids[$idProducto] = $idProducto;
                }
            }
        }
        $stmt->close();

        return array_values($ids);
    }

    private function getTotalProductosByIdSucursal($id_sucursal)
    {
        return count($this->getProductoIdsBySucursalVendedor($id_sucursal));
    }

    public function getCategoriasByEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("
        SELECT DISTINCT c.id_categoria, c.nombre 
        FROM productos p 
        INNER JOIN categorias c ON p.categoria = c.id_categoria 
        WHERE p.id_empresa = ? AND p.estado = 'A'
    ");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        return $categorias;
    }

    public function getVerificacionCuentaEmpresa($id_empresa)
    {
        $stmt = $this->conn->prepare("SELECT * FROM verificacion_empresa ve WHERE ve.estado = 'A' and ve.id_empresa = ?");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        $verificacion = [];
        while ($row = $result->fetch_assoc()) {
            $verificacion[] = $row;
        }
        return $verificacion;
    }

    public function getTotalProductosbyIdEmpresa($id_empresa)
    {
        // Contar total de productos asociados a esta empresa
        $stmt2 = $this->conn->prepare("SELECT COUNT(*) as total_productos FROM productos WHERE id_empresa = ? and estado = 0");
        $stmt2->bind_param("i", $id_empresa);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $total_productos = $result2->fetch_assoc()["total_productos"];

        return (int)$total_productos;
    }

    public function getTotalEmpresa()
    {
        // Contar total de productos asociados a esta empresa
        $stmt2 = $this->conn->prepare("SELECT COUNT(*) as total_empresa FROM empresas WHERE estado = 0");
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $total_empresa = $result2->fetch_assoc()["total_empresa"];

        return (int)$total_empresa;
    }


    // public function buscarProductosYCategorias(string $q, string $categoria)
    // {
    //     $resp = [
    //         'error'            => false,
    //         'products'         => [],
    //         'categories'       => [],
    //         'randomCategories' => [],
    //         'vehicles'         => [],   // <- NUEVO: resultados de vehículos
    //     ];

    //     // -----------------------------
    //     // Helpers para LIKE y tokens
    //     // -----------------------------
    //     $qTrim  = trim($q);
    //     $qLike  = '%' . $qTrim . '%';

    //     // Tokens por coma para "tags" y búsquedas multi-frase (e.g. "aceite, chevrolet")
    //     $tokens = array_values(
    //         array_filter(
    //             array_map(fn($s) => trim($s), explode(',', $qTrim)),
    //             fn($s) => $s !== ''
    //         )
    //     );

    //     // ======================================================
    //     // 1) PRODUCTOS: nombre / titulo_producto / marca_producto / tags
    //     // ======================================================
    //     $sqlProd = "SELECT p.*
    //             FROM productos p
    //             WHERE p.estado = 'A' ";

    //     $condsProd = [];
    //     $params    = [];
    //     $types     = '';

    //     // Campos principales
    //     $condsProd[] = "(p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ?)";
    //     array_push($params, $qLike, $qLike, $qLike);
    //     $types .= 'sss';

    //     // Tags por tokens (OR entre tokens)
    //     if (!empty($tokens)) {
    //         $sub   = [];
    //         foreach ($tokens as $tk) {
    //             $sub[]   = "p.tags LIKE ?";
    //             $params[] = '%' . $tk . '%';
    //             $types   .= 's';
    //         }
    //         // (p.tags LIKE ? OR p.tags LIKE ? ...)
    //         $condsProd[] = '(' . implode(' OR ', $sub) . ')';
    //     } else {
    //         // si no hay tokens (no hay coma), busca también por tags con el q completo
    //         $condsProd[] = "p.tags LIKE ?";
    //         $params[]    = $qLike;
    //         $types      .= 's';
    //     }

    //     // Filtro por categoría, si aplica
    //     if ($categoria !== "all") {
    //         $condsProd[] = "p.categoria = ?";
    //         $params[]    = $categoria;
    //         $types      .= 's';
    //     }

    //     // Unimos condiciones
    //     if (!empty($condsProd)) {
    //         $sqlProd .= " AND (" . implode(' OR ', $condsProd) . ")";
    //     }

    //     // Orden (opcional)
    //     $sqlProd .= " ORDER BY p.created_at DESC";

    //     $stmt = $this->conn->prepare($sqlProd);
    //     if (!$stmt) {
    //         return ['error' => true, 'message' => 'Error al preparar consulta de productos'];
    //     }
    //     $stmt->bind_param($types, ...$params);
    //     $stmt->execute();
    //     $rs = $stmt->get_result();
    //     while ($row = $rs->fetch_assoc()) {
    //         $resp['products'][] = $row;
    //     }
    //     $stmt->close();

    //     // ======================================================
    //     // 2) CATEGORÍAS relacionadas (si hubo productos)
    //     // ======================================================
    //     if (!empty($resp['products'])) {
    //         $sqlCat   = "SELECT DISTINCT c.id_categoria, c.nombre
    //                  FROM categorias c
    //                  INNER JOIN productos p ON p.categoria = c.id_categoria
    //                  WHERE c.estado='A' AND p.estado='A' ";

    //         $catTypes = '';
    //         $catPars  = [];

    //         // mismas reglas de texto principal
    //         $sqlCat .= " AND (p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //         array_push($catPars, $qLike, $qLike, $qLike, $qLike);
    //         $catTypes .= 'ssss';

    //         if ($categoria !== "all") {
    //             $sqlCat   .= " AND p.categoria = ?";
    //             $catPars[] = $categoria;
    //             $catTypes .= 's';
    //         }

    //         $stmt2 = $this->conn->prepare($sqlCat);
    //         if ($stmt2) {
    //             $stmt2->bind_param($catTypes, ...$catPars);
    //             $stmt2->execute();
    //             $r2 = $stmt2->get_result();
    //             while ($c = $r2->fetch_assoc()) {
    //                 $resp['categories'][] = $c;
    //             }
    //             $stmt2->close();
    //         }
    //     } else {
    //         // Si NO hubo productos, devuelve 10 categorías aleatorias
    //         $sqlRand = "SELECT id_categoria, nombre
    //                 FROM categorias
    //                 WHERE estado='A'
    //                 ORDER BY RAND()
    //                 LIMIT 10";
    //         if ($stmt3 = $this->conn->prepare($sqlRand)) {
    //             $stmt3->execute();
    //             $r3 = $stmt3->get_result();
    //             while ($rc = $r3->fetch_assoc()) {
    //                 $resp['randomCategories'][] = $rc;
    //             }
    //             $stmt3->close();
    //         }
    //     }

    //     // ======================================================
    //     // 3) VEHÍCULOS: por marca (marcas.nombre / marcas.referencia) y modelo (modelos_autos.nombre)
    //     //     - Si tu tabla de modelos se llama distinto, ajusta los nombres.
    //     // ======================================================
    //     $sqlVeh = "SELECT v.*,
    //                   m.nombre  AS marca_nombre,
    //                   m.referencia AS marca_referencia,
    //                   mo.nombre AS modelo_nombre
    //            FROM vehiculos v
    //            INNER JOIN marcas m ON m.id_marca = v.id_marca
    //            INNER JOIN modelos_autos mo ON mo.id_modelos_autos = v.id_modelo
    //            WHERE v.estado IS NULL OR v.estado = 'A' ";

    //     $vehConds = [];
    //     $vehPars  = [];
    //     $vehTypes = '';

    //     // Buscar por marca y modelo con el texto completo
    //     $vehConds[] = "(m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ?)";
    //     array_push($vehPars, $qLike, $qLike, $qLike);
    //     $vehTypes .= 'sss';

    //     // Tokens por coma también aplican aquí (OR)
    //     if (!empty($tokens)) {
    //         $sub = [];
    //         foreach ($tokens as $tk) {
    //             $sub[]     = "(m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ?)";
    //             $likeToken = '%' . $tk . '%';
    //             array_push($vehPars, $likeToken, $likeToken, $likeToken);
    //             $vehTypes .= 'sss';
    //         }
    //         $vehConds[] = '(' . implode(' OR ', $sub) . ')';
    //     }

    //     if (!empty($vehConds)) {
    //         $sqlVeh .= " AND (" . implode(' OR ', $vehConds) . ")";
    //     }

    //     // Orden opcional
    //     $sqlVeh .= " ORDER BY v.id_vehiculo DESC";

    //     if ($stmtV = $this->conn->prepare($sqlVeh)) {
    //         $stmtV->bind_param($vehTypes, ...$vehPars);
    //         $stmtV->execute();
    //         $rv = $stmtV->get_result();
    //         while ($v = $rv->fetch_assoc()) {
    //             $resp['vehicles'][] = $v;
    //         }
    //         $stmtV->close();
    //     }

    //     return $resp;
    // }

    // public function buscarProductosYCategorias(string $q, string $categoria)
    // {
    //     $resp = [
    //         'error'            => false,
    //         'products'         => [],
    //         // categorías agrupadas por tipo
    //         'categories'       => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         // categorías aleatorias cuando no hay productos
    //         'randomCategories' => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         'vehicles'         => [],   // resultados de vehículos
    //     ];

    //     // -----------------------------
    //     // Helpers para LIKE y tokens
    //     // -----------------------------
    //     $qTrim  = trim($q);
    //     $qLike  = '%' . $qTrim . '%';

    //     // Tokens por coma para "tags" y búsquedas multi-frase (e.g. "aceite, chevrolet")
    //     $tokens = array_values(
    //         array_filter(
    //             array_map(fn($s) => trim($s), explode(',', $qTrim)),
    //             fn($s) => $s !== ''
    //         )
    //     );

    //     // ======================================================
    //     // 1) PRODUCTOS: nombre / titulo_producto / marca_producto / tags
    //     // ======================================================
    //     $sqlProd = "SELECT p.*
    //             FROM productos p
    //             WHERE p.estado = 'A' ";

    //     $condsProd = [];
    //     $params    = [];
    //     $types     = '';

    //     // Campos principales
    //     $condsProd[] = "(p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ?)";
    //     array_push($params, $qLike, $qLike, $qLike);
    //     $types .= 'sss';

    //     // Tags por tokens (OR entre tokens)
    //     if (!empty($tokens)) {
    //         $sub = [];
    //         foreach ($tokens as $tk) {
    //             $sub[]   = "p.tags LIKE ?";
    //             $params[] = '%' . $tk . '%';
    //             $types   .= 's';
    //         }
    //         // (p.tags LIKE ? OR p.tags LIKE ? ...)
    //         $condsProd[] = '(' . implode(' OR ', $sub) . ')';
    //     } else {
    //         // si no hay tokens (no hay coma), busca también por tags con el q completo
    //         $condsProd[] = "p.tags LIKE ?";
    //         $params[]    = $qLike;
    //         $types      .= 's';
    //     }

    //     // Filtro por categoría, si aplica
    //     if ($categoria !== "all") {
    //         $condsProd[] = "p.categoria = ?";
    //         $params[]    = $categoria;
    //         $types      .= 's';
    //     }

    //     // Unimos condiciones
    //     if (!empty($condsProd)) {
    //         $sqlProd .= " AND (" . implode(' OR ', $condsProd) . ")";
    //     }

    //     // Orden (opcional)
    //     $sqlProd .= " ORDER BY p.created_at DESC";

    //     $stmt = $this->conn->prepare($sqlProd);
    //     if (!$stmt) {
    //         return ['error' => true, 'message' => 'Error al preparar consulta de productos'];
    //     }
    //     $stmt->bind_param($types, ...$params);
    //     $stmt->execute();
    //     $rs = $stmt->get_result();
    //     while ($row = $rs->fetch_assoc()) {
    //         $resp['products'][] = $row;
    //     }
    //     $stmt->close();

    //     // ======================================================
    //     // 2) CATEGORÍAS relacionadas (si hubo productos)
    //     //    -> separadas en productos/servicios según c.tipo
    //     // ======================================================
    //     if (!empty($resp['products'])) {
    //         $sqlCat   = "SELECT DISTINCT c.id_categoria, c.nombre, c.tipo
    //                  FROM categorias c
    //                  INNER JOIN productos p ON p.categoria = c.id_categoria
    //                  WHERE c.estado='A' AND p.estado='A' ";

    //         $catTypes = '';
    //         $catPars  = [];

    //         // mismas reglas de texto principal
    //         $sqlCat .= " AND (p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //         array_push($catPars, $qLike, $qLike, $qLike, $qLike);
    //         $catTypes .= 'ssss';

    //         if ($categoria !== "all") {
    //             $sqlCat   .= " AND p.categoria = ?";
    //             $catPars[] = $categoria;
    //             $catTypes .= 's';
    //         }

    //         $stmt2 = $this->conn->prepare($sqlCat);
    //         if ($stmt2) {
    //             $stmt2->bind_param($catTypes, ...$catPars);
    //             $stmt2->execute();
    //             $r2 = $stmt2->get_result();
    //             while ($c = $r2->fetch_assoc()) {
    //                 // agrupamos por tipo
    //                 if (isset($c['tipo']) && $c['tipo'] === 'servicio') {
    //                     $resp['categories']['servicios'][] = $c;
    //                 } else {
    //                     // por defecto lo tratamos como producto
    //                     $resp['categories']['productos'][] = $c;
    //                 }
    //             }
    //             $stmt2->close();
    //         }
    //     } else {
    //         // ==================================================
    //         // Si NO hubo productos, devuelve 10 categorías aleatorias
    //         // -> también separadas en productos/servicios
    //         // ==================================================
    //         $sqlRand = "SELECT id_categoria, nombre, tipo
    //                 FROM categorias
    //                 WHERE estado='A'
    //                 ORDER BY RAND()
    //                 LIMIT 10";
    //         if ($stmt3 = $this->conn->prepare($sqlRand)) {
    //             $stmt3->execute();
    //             $r3 = $stmt3->get_result();
    //             while ($rc = $r3->fetch_assoc()) {
    //                 if (isset($rc['tipo']) && $rc['tipo'] === 'servicio') {
    //                     $resp['randomCategories']['servicios'][] = $rc;
    //                 } else {
    //                     $resp['randomCategories']['productos'][] = $rc;
    //                 }
    //             }
    //             $stmt3->close();
    //         }
    //     }

    //     // ======================================================
    //     // 3) VEHÍCULOS:
    //     //    - marca (marcas.nombre / marcas.referencia)
    //     //    - modelo (modelos_autos.nombre)
    //     //    - tags de la tabla vehiculos (v.tags)
    //     // ======================================================
    //     $sqlVeh = "SELECT v.*,
    //                   m.nombre     AS marca_nombre,
    //                   m.referencia AS marca_referencia,
    //                   mo.nombre    AS modelo_nombre
    //            FROM vehiculos v
    //            INNER JOIN marcas m ON m.id_marca = v.id_marca
    //            INNER JOIN modelos_autos mo ON mo.id_modelos_autos = v.id_modelo
    //            WHERE (v.estado IS NULL OR v.estado = 'A') ";

    //     $vehConds = [];
    //     $vehPars  = [];
    //     $vehTypes = '';

    //     // Buscar por marca, modelo y tags con el texto completo
    //     $vehConds[] = "(m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ? OR v.tags LIKE ?)";
    //     array_push($vehPars, $qLike, $qLike, $qLike, $qLike);
    //     $vehTypes .= 'ssss';

    //     // Tokens por coma también aplican aquí (OR)
    //     if (!empty($tokens)) {
    //         $sub = [];
    //         foreach ($tokens as $tk) {
    //             $sub[] = "(m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ? OR v.tags LIKE ?)";
    //             $likeToken = '%' . $tk . '%';
    //             array_push($vehPars, $likeToken, $likeToken, $likeToken, $likeToken);
    //             $vehTypes .= 'ssss';
    //         }
    //         $vehConds[] = '(' . implode(' OR ', $sub) . ')';
    //     }

    //     if (!empty($vehConds)) {
    //         $sqlVeh .= " AND (" . implode(' OR ', $vehConds) . ")";
    //     }

    //     // Orden opcional
    //     $sqlVeh .= " ORDER BY v.id_vehiculo DESC";

    //     if ($stmtV = $this->conn->prepare($sqlVeh)) {
    //         $stmtV->bind_param($vehTypes, ...$vehPars);
    //         $stmtV->execute();
    //         $rv = $stmtV->get_result();
    //         while ($v = $rv->fetch_assoc()) {
    //             $resp['vehicles'][] = $v;
    //         }
    //         $stmtV->close();
    //     }

    //     return $resp;
    // }

    // public function buscarProductosYCategorias(string $q, string $categoria)
    // {
    //     $resp = [
    //         'error'            => false,
    //         'products'         => [],
    //         // categorías agrupadas por tipo
    //         'categories'       => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         // categorías aleatorias cuando no hay productos
    //         'randomCategories' => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         'vehicles'         => [],   // resultados de vehículos
    //     ];

    //     // -----------------------------
    //     // Helpers para LIKE y tokens
    //     // -----------------------------
    //     $qTrim  = trim($q);

    //     // 🔹 Normalizamos para palabras: quitamos comas, punto y coma, etc.
    //     $qPalabras = str_replace([',', ';'], ' ', $qTrim);
    //     $words = array_values(
    //         array_filter(
    //             preg_split('/\s+/', $qPalabras),
    //             fn($s) => $s !== ''
    //         )
    //     );

    //     // Tokens por coma para compatibilidad (si luego quieres usarlos para algo extra)
    //     $tokens = array_values(
    //         array_filter(
    //             array_map(fn($s) => trim($s), explode(',', $qTrim)),
    //             fn($s) => $s !== ''
    //         )
    //     );

    //     // ======================================================
    //     // 1) PRODUCTOS: nombre / titulo_producto / marca_producto / tags
    //     // ======================================================
    //     $sqlProd = "SELECT p.*
    //         FROM productos p
    //         WHERE p.estado = 'A' ";

    //     $params    = [];
    //     $types     = '';

    //     // 🔹 Búsqueda por PALABRAS (en cualquier orden):
    //     // Cada palabra debe estar en nombre/título/marca/tags (AND entre palabras)
    //     if (!empty($words)) {
    //         $wordConds = [];
    //         foreach ($words as $w) {
    //             $likeW = '%' . $w . '%';
    //             $wordConds[] = "(p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //             array_push($params, $likeW, $likeW, $likeW, $likeW);
    //             $types .= 'ssss';
    //         }
    //         // (condPal1 AND condPal2 AND ...)
    //         $sqlProd .= " AND (" . implode(' AND ', $wordConds) . ")";
    //     }

    //     // 🔹 Filtro por categoría, si aplica
    //     if ($categoria !== "all") {
    //         $sqlProd .= " AND p.categoria = ?";
    //         $params[] = $categoria;
    //         $types   .= 's';
    //     }

    //     // Orden (opcional)
    //     $sqlProd .= " ORDER BY p.created_at DESC";

    //     $stmt = $this->conn->prepare($sqlProd);
    //     if (!$stmt) {
    //         return ['error' => true, 'message' => 'Error al preparar consulta de productos'];
    //     }

    //     if ($types !== '') {
    //         $stmt->bind_param($types, ...$params);
    //     }
    //     $stmt->execute();
    //     $rs = $stmt->get_result();
    //     while ($row = $rs->fetch_assoc()) {
    //         $resp['products'][] = $row;
    //     }
    //     $stmt->close();

    //     // ======================================================
    //     // 2) CATEGORÍAS relacionadas (si hubo productos)
    //     //    -> separadas en productos/servicios según c.tipo
    //     // ======================================================
    //     if (!empty($resp['products'])) {
    //         $sqlCat   = "SELECT DISTINCT c.id_categoria, c.nombre, c.tipo
    //              FROM categorias c
    //              INNER JOIN productos p ON p.categoria = c.id_categoria
    //              WHERE c.estado='A' AND p.estado='A' ";

    //         $catTypes = '';
    //         $catPars  = [];

    //         if (!empty($words)) {
    //             $catWordConds = [];
    //             foreach ($words as $w) {
    //                 $likeW = '%' . $w . '%';
    //                 $catWordConds[] = "(p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //                 array_push($catPars, $likeW, $likeW, $likeW, $likeW);
    //                 $catTypes .= 'ssss';
    //             }
    //             $sqlCat .= " AND (" . implode(' AND ', $catWordConds) . ")";
    //         }

    //         if ($categoria !== "all") {
    //             $sqlCat   .= " AND p.categoria = ?";
    //             $catPars[] = $categoria;
    //             $catTypes .= 's';
    //         }

    //         $stmt2 = $this->conn->prepare($sqlCat);
    //         if ($stmt2) {
    //             if ($catTypes !== '') {
    //                 $stmt2->bind_param($catTypes, ...$catPars);
    //             }
    //             $stmt2->execute();
    //             $r2 = $stmt2->get_result();
    //             while ($c = $r2->fetch_assoc()) {
    //                 // agrupamos por tipo
    //                 if (isset($c['tipo']) && $c['tipo'] === 'servicio') {
    //                     $resp['categories']['servicios'][] = $c;
    //                 } else {
    //                     // por defecto lo tratamos como producto
    //                     $resp['categories']['productos'][] = $c;
    //                 }
    //             }
    //             $stmt2->close();
    //         }
    //     } else {
    //         // ==================================================
    //         // Si NO hubo productos, devuelve 10 categorías aleatorias
    //         // -> también separadas en productos/servicios
    //         // ==================================================
    //         $sqlRand = "SELECT id_categoria, nombre, tipo
    //             FROM categorias
    //             WHERE estado='A'
    //             ORDER BY RAND()
    //             LIMIT 10";
    //         if ($stmt3 = $this->conn->prepare($sqlRand)) {
    //             $stmt3->execute();
    //             $r3 = $stmt3->get_result();
    //             while ($rc = $r3->fetch_assoc()) {
    //                 if (isset($rc['tipo']) && $rc['tipo'] === 'servicio') {
    //                     $resp['randomCategories']['servicios'][] = $rc;
    //                 } else {
    //                     $resp['randomCategories']['productos'][] = $rc;
    //                 }
    //             }
    //             $stmt3->close();
    //         }
    //     }

    //     // ======================================================
    //     // 3) VEHÍCULOS:
    //     //    - marca (marcas.nombre / marcas.referencia)
    //     //    - modelo (modelos_autos.nombre)
    //     //    - tags de la tabla vehiculos (v.tags)
    //     // ======================================================
    //     $sqlVeh = "SELECT v.*,
    //               m.nombre     AS marca_nombre,
    //               m.referencia AS marca_referencia,
    //               mo.nombre    AS modelo_nombre
    //        FROM vehiculos v
    //        INNER JOIN marcas m ON m.id_marca = v.id_marca
    //        INNER JOIN modelos_autos mo ON mo.id_modelos_autos = v.id_modelo
    //        WHERE (v.estado IS NULL OR v.estado = 'A') ";

    //     $vehPars  = [];
    //     $vehTypes = '';

    //     if (!empty($words)) {
    //         $vehWordConds = [];
    //         foreach ($words as $w) {
    //             $likeW = '%' . $w . '%';
    //             $vehWordConds[] = "(m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ? OR v.tags LIKE ?)";
    //             array_push($vehPars, $likeW, $likeW, $likeW, $likeW);
    //             $vehTypes .= 'ssss';
    //         }
    //         $sqlVeh .= " AND (" . implode(' AND ', $vehWordConds) . ")";
    //     }

    //     // Orden opcional
    //     $sqlVeh .= " ORDER BY v.id_vehiculo DESC";

    //     if ($stmtV = $this->conn->prepare($sqlVeh)) {
    //         if ($vehTypes !== '') {
    //             $stmtV->bind_param($vehTypes, ...$vehPars);
    //         }
    //         $stmtV->execute();
    //         $rv = $stmtV->get_result();
    //         while ($v = $rv->fetch_assoc()) {
    //             $resp['vehicles'][] = $v;
    //         }
    //         $stmtV->close();
    //     }

    //     return $resp;
    // }

    // public function buscarProductosYCategorias(string $q, string $categoria)
    // {
    //     $resp = [
    //         'error'            => false,
    //         'products'         => [],
    //         'categories'       => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         'randomCategories' => [
    //             'productos' => [],
    //             'servicios' => [],
    //         ],
    //         'vehicles'         => [],
    //     ];

    //     $qTrim = trim($q);
    //     if (empty($qTrim)) return $resp;

    //     // 🔹 1. LISTA DE STOPWORDS (Palabras que el buscador ignorará)
    //     $stopwords = [
    //         'para',
    //         'de',
    //         'del',
    //         'la',
    //         'el',
    //         'en',
    //         'y',
    //         'a',
    //         'con',
    //         'un',
    //         'una',
    //         'los',
    //         'las',
    //         'por',
    //         'lo',
    //         'su',
    //         'al',
    //         'es',
    //         'son',
    //         'si'
    //     ];

    //     // 🔹 2. NORMALIZACIÓN INTELIGENTE
    //     // Pasamos a minúsculas y limpiamos caracteres especiales
    //     $qPalabras = str_replace([',', ';', '.', '-', '_', '/'], ' ', mb_strtolower($qTrim));
    //     $rawWords = array_values(array_filter(preg_split('/\s+/', $qPalabras)));

    //     // Filtramos para quedarnos con palabras con significado real
    //     $words = array_values(array_filter($rawWords, function ($w) use ($stopwords) {
    //         return !in_array($w, $stopwords) && mb_strlen($w) > 1;
    //     }));

    //     // Si la búsqueda queda vacía tras filtrar (ej. "de para"), usamos los términos originales
    //     if (empty($words)) $words = $rawWords;

    //     // ======================================================
    //     // 1) PRODUCTOS: nombre / titulo_producto / marca_producto / tags
    //     // ======================================================
    //     $sqlProd = "SELECT p.* FROM productos p WHERE p.estado = 'A' ";
    //     $paramsProd = [];
    //     $typesProd = '';

    //     foreach ($words as $w) {
    //         $likeW = '%' . $w . '%';
    //         $sqlProd .= " AND (p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //         array_push($paramsProd, $likeW, $likeW, $likeW, $likeW);
    //         $typesProd .= 'ssss';
    //     }

    //     if ($categoria !== "all") {
    //         $sqlProd .= " AND p.categoria = ?";
    //         $paramsProd[] = $categoria;
    //         $typesProd .= 's';
    //     }

    //     $sqlProd .= " ORDER BY p.created_at DESC";

    //     $stmt = $this->conn->prepare($sqlProd);
    //     if ($stmt) {
    //         if (!empty($paramsProd)) $stmt->bind_param($typesProd, ...$paramsProd);
    //         $stmt->execute();
    //         $resp['products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    //         $stmt->close();
    //     }

    //     // ======================================================
    //     // 2) CATEGORÍAS relacionadas (si hubo productos)
    //     // ======================================================
    //     if (!empty($resp['products'])) {
    //         $sqlCat = "SELECT DISTINCT c.id_categoria, c.nombre, c.tipo
    //                FROM categorias c
    //                INNER JOIN productos p ON p.categoria = c.id_categoria
    //                WHERE c.estado='A' AND p.estado='A' ";

    //         $catTypes = '';
    //         $catPars  = [];

    //         foreach ($words as $w) {
    //             $likeW = '%' . $w . '%';
    //             $sqlCat .= " AND (p.nombre LIKE ? OR p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
    //             array_push($catPars, $likeW, $likeW, $likeW, $likeW);
    //             $catTypes .= 'ssss';
    //         }

    //         if ($categoria !== "all") {
    //             $sqlCat .= " AND p.categoria = ?";
    //             $catPars[] = $categoria;
    //             $catTypes .= 's';
    //         }

    //         $stmt2 = $this->conn->prepare($sqlCat);
    //         if ($stmt2) {
    //             if ($catTypes !== '') $stmt2->bind_param($catTypes, ...$catPars);
    //             $stmt2->execute();
    //             $r2 = $stmt2->get_result();
    //             while ($c = $r2->fetch_assoc()) {
    //                 if (isset($c['tipo']) && $c['tipo'] === 'servicio') {
    //                     $resp['categories']['servicios'][] = $c;
    //                 } else {
    //                     $resp['categories']['productos'][] = $c;
    //                 }
    //             }
    //             $stmt2->close();
    //         }
    //     } else {
    //         // Categorías aleatorias si no hay resultados directos
    //         $sqlRand = "SELECT id_categoria, nombre, tipo FROM categorias WHERE estado='A' ORDER BY RAND() LIMIT 10";
    //         if ($stmt3 = $this->conn->prepare($sqlRand)) {
    //             $stmt3->execute();
    //             $r3 = $stmt3->get_result();
    //             while ($rc = $r3->fetch_assoc()) {
    //                 if (isset($rc['tipo']) && $rc['tipo'] === 'servicio') {
    //                     $resp['randomCategories']['servicios'][] = $rc;
    //                 } else {
    //                     $resp['randomCategories']['productos'][] = $rc;
    //                 }
    //             }
    //             $stmt3->close();
    //         }
    //     }

    //     // ======================================================
    //     // 3) VEHÍCULOS: Búsqueda cruzada en marcas, modelos y tags
    //     // ======================================================
    //     $sqlVeh = "SELECT v.*, m.nombre AS marca_nombre, m.referencia AS marca_referencia, mo.nombre AS modelo_nombre
    //            FROM vehiculos v
    //            INNER JOIN marcas m ON m.id_marca = v.id_marca
    //            INNER JOIN modelos_autos mo ON mo.id_modelos_autos = v.id_modelo
    //            WHERE (v.estado IS NULL OR v.estado = 'A') ";

    //     $vehPars  = [];
    //     $vehTypes = '';

    //     foreach ($words as $w) {
    //         $likeW = '%' . $w . '%';
    //         // Buscamos la palabra en marca, modelo, tags y año
    //         $sqlVeh .= " AND (m.nombre LIKE ? OR m.referencia LIKE ? OR mo.nombre LIKE ? OR v.tags LIKE ? OR v.anio LIKE ?)";
    //         array_push($vehPars, $likeW, $likeW, $likeW, $likeW, $likeW);
    //         $vehTypes .= 'sssss';
    //     }

    //     $sqlVeh .= " ORDER BY v.id_vehiculo DESC";

    //     if ($stmtV = $this->conn->prepare($sqlVeh)) {
    //         if ($vehTypes !== '') $stmtV->bind_param($vehTypes, ...$vehPars);
    //         $stmtV->execute();
    //         $resp['vehicles'] = $stmtV->get_result()->fetch_all(MYSQLI_ASSOC);
    //         $stmtV->close();
    //     }

    //     return $resp;
    // }

    public function buscarProductosYCategorias(string $q, string $categoria)
    {
        $resp = [
            'error'            => false,
            'products'         => [],
            'services'         => [],
            'vehicles'         => [],
            'events'           => [],
            'jobs'             => [],
            'categories'       => [
                'productos' => [],
                'servicios' => [],
            ],
            'randomCategories' => [
                'productos' => [],
                'servicios' => [],
            ],
        ];

        $qTrim = trim($q);
        if (empty($qTrim)) return $resp;

        // 🔹 1. LISTA DE STOPWORDS (Ignora conectores para búsqueda inteligente)
        $stopwords = [
            'para',
            'de',
            'del',
            'la',
            'el',
            'en',
            'y',
            'a',
            'con',
            'un',
            'una',
            'los',
            'las',
            'por',
            'lo',
            'su',
            'al',
            'es',
            'son',
            'si',
            'como'
        ];

        // 🔹 2. NORMALIZACIÓN
        $qPalabras = str_replace([',', ';', '.', '-', '_', '/', '\\'], ' ', mb_strtolower($qTrim));
        $rawWords = array_values(array_filter(preg_split('/\s+/', $qPalabras)));

        $words = array_values(array_filter($rawWords, function ($w) use ($stopwords) {
            return !in_array($w, $stopwords) && mb_strlen($w) > 1;
        }));

        if (empty($words)) $words = $rawWords;

        // ======================================================
        // 1) PRODUCTOS: Búsqueda flexible y combinada
        // ======================================================
        $sqlProd = "SELECT p.* FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            INNER JOIN membresias_empresas me ON me.id_empresa = em.id_empresa
                AND me.estado = 'A'
                AND me.fecha_inicio <= CURDATE()
                AND me.fecha_fin >= CURDATE()
            WHERE p.estado = 'A' ";
        $paramsProd = [];
        $typesProd = '';

        foreach ($words as $w) {
            $likeW = '%' . $w . '%';
            // Buscamos la palabra en opciones individuales O en la combinación de campos
            $sqlProd .= " AND (
            p.titulo_producto LIKE ? 
            OR p.marca_producto LIKE ? 
            OR p.tags LIKE ? 
            OR CONCAT_WS(' ', p.titulo_producto, p.marca_producto, p.tags) LIKE ?
        )";
            array_push($paramsProd, $likeW, $likeW, $likeW, $likeW);
            $typesProd .= 'ssss';
        }

        if ($categoria !== "all") {
            $sqlProd .= " AND p.categoria = ?";
            $paramsProd[] = $categoria;
            $typesProd .= 's';
        }

        $sqlProd .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sqlProd);
        if ($stmt) {
            if (!empty($paramsProd)) $stmt->bind_param($typesProd, ...$paramsProd);
            $stmt->execute();
            $resp['products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        // ======================================================
        // 2) SERVICIOS
        // ======================================================
        $sqlServ = "SELECT p.* FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            INNER JOIN membresias_empresas me ON me.id_empresa = em.id_empresa
                AND me.estado = 'A'
                AND me.fecha_inicio <= CURDATE()
                AND me.fecha_fin >= CURDATE()
            WHERE p.estado = 'A' ";
        $paramsServ = [];
        $typesServ = '';

        foreach ($words as $w) {
            $likeW = '%' . $w . '%';
            $sqlServ .= " AND (
            p.titulo_producto LIKE ? 
            OR p.marca_producto LIKE ? 
            OR p.tags LIKE ? 
            OR CONCAT_WS(' ', p.titulo_producto, p.marca_producto, p.tags) LIKE ?
        )";
            array_push($paramsServ, $likeW, $likeW, $likeW, $likeW);
            $typesServ .= 'ssss';
        }

        if ($categoria !== "all") {
            $sqlServ .= " AND p.categoria = ?";
            $paramsServ[] = $categoria;
            $typesServ .= 's';
        }

        $sqlServ .= " AND EXISTS (
            SELECT 1
            FROM categorias c
            WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
            AND c.estado = 'A' AND c.tipo = 'servicio'
        )";

        $sqlServ .= " ORDER BY p.created_at DESC";

        $stmtServ = $this->conn->prepare($sqlServ);
        if ($stmtServ) {
            if (!empty($paramsServ)) $stmtServ->bind_param($typesServ, ...$paramsServ);
            $stmtServ->execute();
            $resp['services'] = $stmtServ->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtServ->close();
        }

        // ======================================================
        // 3) CATEGORÍAS (Relacionadas por relevancia de palabras)
        // ======================================================
        if (!empty($resp['products'])) {
            $sqlCat = "SELECT DISTINCT c.id_categoria, c.nombre, c.tipo
                   FROM categorias c
                   INNER JOIN productos p ON p.categoria = c.id_categoria
                   INNER JOIN empresas em ON em.id_empresa = p.id_empresa
                   INNER JOIN membresias_empresas me ON me.id_empresa = em.id_empresa
                        AND me.estado = 'A'
                        AND me.fecha_inicio <= CURDATE()
                        AND me.fecha_fin >= CURDATE()
                   WHERE c.estado='A' AND p.estado='A' ";

            $catTypes = '';
            $catPars  = [];

            foreach ($words as $w) {
                $likeW = '%' . $w . '%';
                $sqlCat .= " AND (p.titulo_producto LIKE ? OR p.marca_producto LIKE ? OR p.tags LIKE ?)";
                array_push($catPars, $likeW, $likeW, $likeW);
                $catTypes .= 'sss';
            }

            if ($categoria !== "all") {
                $sqlCat .= " AND p.categoria = ?";
                $catPars[] = $categoria;
                $catTypes .= 's';
            }

            $stmt2 = $this->conn->prepare($sqlCat);
            if ($stmt2) {
                if ($catTypes !== '') $stmt2->bind_param($catTypes, ...$catPars);
                $stmt2->execute();
                $r2 = $stmt2->get_result();
                while ($c = $r2->fetch_assoc()) {
                    if (($c['tipo'] ?? '') === 'servicio') {
                        $resp['categories']['servicios'][] = $c;
                    } else {
                        $resp['categories']['productos'][] = $c;
                    }
                }
                $stmt2->close();
            }
        } else {
            $sqlRand = "SELECT id_categoria, nombre, tipo FROM categorias WHERE estado='A' ORDER BY RAND() LIMIT 10";
            if ($stmt3 = $this->conn->prepare($sqlRand)) {
                $stmt3->execute();
                $r3 = $stmt3->get_result();
                while ($rc = $r3->fetch_assoc()) {
                    if (($rc['tipo'] ?? '') === 'servicio') {
                        $resp['randomCategories']['servicios'][] = $rc;
                    } else {
                        $resp['randomCategories']['productos'][] = $rc;
                    }
                }
                $stmt3->close();
            }
        }

        // ======================================================
        // 4) VEHÍCULOS: Marca + Modelo + Tags
        // ======================================================
        $sqlVeh = "SELECT v.*, m.nombre AS marca_nombre, mo.nombre AS modelo_nombre
               FROM vehiculos v
               INNER JOIN marcas m ON m.id_marca = v.id_marca
               INNER JOIN modelos_autos mo ON mo.id_modelos_autos = v.id_modelo
               INNER JOIN empresas em ON em.id_empresa = v.id_empresa
               INNER JOIN membresias_empresas me ON me.id_empresa = em.id_empresa
                    AND me.estado = 'A'
                    AND me.fecha_inicio <= CURDATE()
                    AND me.fecha_fin >= CURDATE()
               WHERE (v.estado IS NULL OR v.estado = 'A') ";

        $vehPars  = [];
        $vehTypes = '';

        foreach ($words as $w) {
            $likeW = '%' . $w . '%';
            // Búsqueda en campos individuales y en el conjunto de la información del vehículo
            $sqlVeh .= " AND (
            m.nombre LIKE ? 
            OR mo.nombre LIKE ? 
            OR v.tags LIKE ? 
            OR CONCAT_WS(' ', m.nombre, mo.nombre, v.tags) LIKE ?
        )";
            array_push($vehPars, $likeW, $likeW, $likeW, $likeW);
            $vehTypes .= 'ssss';
        }

        $sqlVeh .= " ORDER BY v.id_vehiculo DESC";

        if ($stmtV = $this->conn->prepare($sqlVeh)) {
            if ($vehTypes !== '') $stmtV->bind_param($vehTypes, ...$vehPars);
            $stmtV->execute();
            $resp['vehicles'] = $stmtV->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtV->close();
        }

        // ======================================================
        // 5) EVENTOS
        // ======================================================
        $sqlEv = "SELECT e.* FROM eventos e
            INNER JOIN empresas em ON em.id_empresa = e.id_empresa
            INNER JOIN membresias_empresas me ON me.id_empresa = em.id_empresa
                AND me.estado = 'A'
                AND me.fecha_inicio <= CURDATE()
                AND me.fecha_fin >= CURDATE()
            WHERE e.estado = 'A'
            AND e.fecha_hora_fin IS NOT NULL
            AND e.fecha_hora_fin >= NOW() ";
        $paramsEv = [];
        $typesEv = '';

        foreach ($words as $w) {
            $likeW = '%' . $w . '%';
            $sqlEv .= " AND (
            e.titulo LIKE ? 
            OR e.descripcion LIKE ? 
            OR e.organizador LIKE ? 
            OR CONCAT_WS(' ', e.titulo, e.descripcion, e.organizador) LIKE ?
        )";
            array_push($paramsEv, $likeW, $likeW, $likeW, $likeW);
            $typesEv .= 'ssss';
        }

        $sqlEv .= " ORDER BY e.id_evento DESC";

        $stmtEv = $this->conn->prepare($sqlEv);
        if ($stmtEv) {
            if (!empty($paramsEv)) $stmtEv->bind_param($typesEv, ...$paramsEv);
            $stmtEv->execute();
            $resp['events'] = $stmtEv->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtEv->close();
        }

        // ======================================================
        // 6) EMPLEOS
        // ======================================================
        $sqlJob = "SELECT em.* FROM empleos em
            INNER JOIN empresas e ON e.id_empresa = em.id_empresa
            INNER JOIN membresias_empresas me ON me.id_empresa = e.id_empresa
                AND me.estado = 'A'
                AND me.fecha_inicio <= CURDATE()
                AND me.fecha_fin >= CURDATE()
            WHERE em.estado = 'A'
            AND em.fecha_inicio IS NOT NULL
            AND em.fecha_fin IS NOT NULL
            AND CURDATE() BETWEEN em.fecha_inicio AND em.fecha_fin ";
        $paramsJob = [];
        $typesJob = '';

        foreach ($words as $w) {
            $likeW = '%' . $w . '%';
            $sqlJob .= " AND (
            em.titulo LIKE ? 
            OR em.descripcion LIKE ? 
            OR em.tags LIKE ? 
            OR CONCAT_WS(' ', em.titulo, em.descripcion, em.tags) LIKE ?
        )";
            array_push($paramsJob, $likeW, $likeW, $likeW, $likeW);
            $typesJob .= 'ssss';
        }

        $sqlJob .= " ORDER BY em.id_empleo DESC";

        $stmtJob = $this->conn->prepare($sqlJob);
        if ($stmtJob) {
            if (!empty($paramsJob)) $stmtJob->bind_param($typesJob, ...$paramsJob);
            $stmtJob->execute();
            $resp['jobs'] = $stmtJob->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtJob->close();
        }

        return $resp;
    }




    // public function buscarProductosYCategorias($q, $categoria)
    // {
    //     $resp = [
    //         'error' => false,
    //         'products' => [],
    //         'categories' => [],
    //         'randomCategories' => []
    //     ];

    //     // ---------- Productos ----------
    //     $sqlProd = "SELECT *
    //             FROM productos p
    //             WHERE p.estado = 'A' AND p.nombre LIKE ?";
    //     $typesProd = "s";
    //     $paramsProd = ["%{$q}%"];

    //     if ($categoria !== "all") {
    //         $sqlProd .= " AND p.categoria = ?";
    //         $typesProd .= "s";
    //         $paramsProd[] = $categoria;
    //     }

    //     $stmt = $this->conn->prepare($sqlProd);
    //     if (!$stmt) return ['error' => true, 'message' => 'Error al preparar consulta de productos'];
    //     $stmt->bind_param($typesProd, ...$paramsProd);
    //     $stmt->execute();
    //     $result = $stmt->get_result();

    //     while ($row = $result->fetch_assoc()) {
    //         $resp['products'][] = $row;
    //     }
    //     $stmt->close();

    //     // ---------- Categorías relacionadas (de los productos hallados) ----------
    //     if (!empty($resp['data']['products'])) {
    //         $sqlCat = "SELECT DISTINCT c.id_categoria, c.nombre
    //                FROM categorias c
    //                INNER JOIN productos p ON p.categoria = c.id_categoria
    //                WHERE c.estado = 'A' AND p.estado='A' AND p.nombre LIKE ?";
    //         $typesCat = "s";
    //         $paramsCat = ["%{$q}%"];

    //         if ($categoria !== "all") {
    //             $sqlCat .= " AND p.categoria = ?";
    //             $typesCat .= "s";
    //             $paramsCat[] = $categoria;
    //         }

    //         $stmt2 = $this->conn->prepare($sqlCat);
    //         if ($stmt2) {
    //             $stmt2->bind_param($typesCat, ...$paramsCat);
    //             $stmt2->execute();
    //             $r2 = $stmt2->get_result();
    //             while ($c = $r2->fetch_assoc()) {
    //                 $resp['categories'][] = $c;
    //             }
    //             $stmt2->close();
    //         }
    //     } else {
    //         // ---------- Fallback: 10 categorías aleatorias si NO hay productos ----------
    //         $sqlRand = "SELECT id_categoria, nombre
    //                 FROM categorias
    //                 WHERE estado='A'
    //                 ORDER BY RAND()
    //                 LIMIT 10";
    //         $stmt3 = $this->conn->prepare($sqlRand);
    //         if ($stmt3) {
    //             $stmt3->execute();
    //             $r3 = $stmt3->get_result();
    //             while ($rc = $r3->fetch_assoc()) {
    //                 $resp['randomCategories'][] = $rc;
    //             }
    //             $stmt3->close();
    //         }
    //     }

    //     return $resp;
    // }


    public function getProductosByIdEmpresa($id_empresa)
    {
        $contextoEmpresa = $this->getEmpresaById((int)$id_empresa);
        if ($contextoEmpresa === RECORD_DOES_NOT_EXIST || !is_array($contextoEmpresa)) {
            return [];
        }

        if ($this->getPublicEmpresaIdForCreator(
            (int)(($contextoEmpresa["tipo"] ?? "empresa") === "sucursal" ? ($contextoEmpresa["id_sucursal"] ?? $id_empresa) : ($contextoEmpresa["id_empresa"] ?? $id_empresa)),
            $contextoEmpresa["tipo"] ?? "empresa"
        ) <= 0) {
            return [];
        }

        $idEmpresaConsulta = (int)(($contextoEmpresa["tipo"] ?? "empresa") === "sucursal"
            ? ($contextoEmpresa["id_empresa"] ?? 0)
            : ($contextoEmpresa["id_empresa"] ?? $id_empresa));

        $productos = [];
        $vistos = [];

        $esSucursal = (($contextoEmpresa["tipo"] ?? "empresa") === "sucursal");
        $idsSucursal = $esSucursal
            ? $this->getProductoIdsBySucursalVendedor((int)($contextoEmpresa["id_sucursal"] ?? $id_empresa))
            : [];

        if ($esSucursal && empty($idsSucursal)) {
            return [];
        }

        $sql = "SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A'
            AND p.id_empresa = ?
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A'))";

        if ($esSucursal) {
            $sql .= " AND p.id_producto IN (" . implode(',', array_fill(0, count($idsSucursal), '?')) . ")";
        }

        $stmt = $this->conn->prepare($sql);
        if ($esSucursal) {
            $types = 'i' . str_repeat('i', count($idsSucursal));
            $params = array_merge([$idEmpresaConsulta], $idsSucursal);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("i", $idEmpresaConsulta);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            if (!isset($vistos[$row['id_producto']])) {
                if ($esSucursal) {
                    $row["nombre_empresa"] = $contextoEmpresa["nombre"] ?? $row["nombre_empresa"];
                    $row["imagen_empresa"] = $contextoEmpresa["img_path"] ?? $row["imagen_empresa"];
                    $row["latitud"] = $contextoEmpresa["latitud"] ?? $row["latitud"];
                    $row["longitud"] = $contextoEmpresa["longitud"] ?? $row["longitud"];
                    $row["provincia"] = $contextoEmpresa["provincia"] ?? $row["provincia"];
                    $row["canton"] = $contextoEmpresa["canton"] ?? $row["canton"];
                }

                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"]  = $this->getVerificacionCuentaEmpresa($idEmpresaConsulta);
                $row["membresia"]     = $this->getMembresiaByEmpresa($idEmpresaConsulta);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }
        return $productos;
    }

    public function getVehiculosByIdEmpresa($id_empresa)
    {
        $vehiculos = [];
        $contextoEmpresa = $this->getEmpresaById((int)$id_empresa);
        if ($contextoEmpresa === RECORD_DOES_NOT_EXIST || !is_array($contextoEmpresa)) {
            return $vehiculos;
        }

        if ($this->getPublicEmpresaIdForCreator(
            (int)(($contextoEmpresa["tipo"] ?? "empresa") === "sucursal" ? ($contextoEmpresa["id_sucursal"] ?? $id_empresa) : ($contextoEmpresa["id_empresa"] ?? $id_empresa)),
            $contextoEmpresa["tipo"] ?? "empresa"
        ) <= 0) {
            return $vehiculos;
        }

        $idEmpresaConsulta = (int)(($contextoEmpresa["tipo"] ?? "empresa") === "sucursal"
            ? ($contextoEmpresa["id_empresa"] ?? 0)
            : ($contextoEmpresa["id_empresa"] ?? $id_empresa));

        $stmt = $this->conn->prepare("SELECT v.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia AS provincia_empresa, em.canton AS canton_empresa
            FROM vehiculos v
            INNER JOIN empresas em ON em.id_empresa = v.id_empresa
            WHERE v.estado = 'A' AND v.id_empresa = ?");

        if (!$stmt) {
            return $vehiculos;
        }

        $stmt->bind_param("i", $idEmpresaConsulta);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if (($contextoEmpresa["tipo"] ?? "empresa") === "sucursal") {
                $row["nombre_empresa"] = $contextoEmpresa["nombre"] ?? $row["nombre_empresa"];
                $row["imagen_empresa"] = $contextoEmpresa["img_path"] ?? $row["imagen_empresa"];
                $row["latitud"] = $contextoEmpresa["latitud"] ?? $row["latitud"];
                $row["longitud"] = $contextoEmpresa["longitud"] ?? $row["longitud"];
                $row["provincia_empresa"] = $contextoEmpresa["provincia"] ?? $row["provincia_empresa"];
                $row["canton_empresa"] = $contextoEmpresa["canton"] ?? $row["canton_empresa"];
            }

            $marcaId = (int)($row["id_marca"] ?? 0);
            $modeloId = (int)($row["id_modelo"] ?? 0);
            $marcaNombre = $row["marca_nombre"] ?? "";
            $modeloNombre = $row["modelo_nombre"] ?? "";

            if ($marcaId > 0 && method_exists($this, 'getMarcaById')) {
                $marca = $this->getMarcaById($marcaId);
                if (is_array($marca)) {
                    $marcaNombre = $marca["nombre"] ?? $marcaNombre;
                }
            }

            if ($modeloId > 0 && method_exists($this, 'getModeloById')) {
                $modelo = $this->getModeloById($modeloId);
                if (is_array($modelo)) {
                    $modeloNombre = $modelo["nombre"] ?? $modeloNombre;
                }
            }

            $row["tipo_item"] = "vehiculo";
            $row["id_item"] = $row["id_vehiculo"];
            $row["nombre"] = trim($marcaNombre . " " . $modeloNombre);
            $row["marca_nombre"] = $marcaNombre;
            $row["modelo_nombre"] = $modeloNombre;
            $row["marca"] = $marcaId > 0 ? [[
                "id" => $marcaId,
                "id_marca" => $marcaId,
                "nombre" => $marcaNombre,
            ]] : [];
            $row["modelo"] = $modeloId > 0 ? [[
                "id" => $modeloId,
                "id_modelo" => $modeloId,
                "id_modelos_autos" => $modeloId,
                "nombre" => $modeloNombre,
            ]] : [];
            $row["archivos"] = $this->getArchivosByVehiculos($row["id_vehiculo"]);
            $row["verificacion"] = $this->getVerificacionCuentaEmpresa($idEmpresaConsulta);
            $row["membresia"] = $this->getMembresiaByEmpresa($idEmpresaConsulta);

            $vehiculos[] = $row;
        }

        $stmt->close();
        return $vehiculos;
    }
    public function getProductosByCategorias(array $ids)
    {
        $productos = [];
        $vistos = [];

        foreach ($ids as $id) {
            $sql = "
            SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A'
            AND JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(? AS CHAR)), '$')
            -- todas las categorías del producto deben ser tipo 'producto' y activas
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A' OR c.tipo <> 'producto')
            )
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
                if ($publicEmpresaId <= 0) {
                    continue;
                }

                if (!isset($vistos[$row['id_producto']])) {
                    // Enriquecidos (ya seguros)
                    $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                    $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                    $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                    $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                    $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
                    $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                    $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                    $row["membresia"]    = $this->getMembresiaByEmpresa($publicEmpresaId);

                    $productos[] = $row;
                    $vistos[$row['id_producto']] = true;
                }
            }
            $stmt->close();
        }

        return $productos;
    }

    public function getServiciosAll()
    {
        $productos = [];
        $vistos = [];
        $sql = "
            SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A'
            -- todas las categorías del producto deben ser tipo 'producto' y activas
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A' OR c.tipo <> 'servicio')
            )
            ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaServicioByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]    = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }


        return $productos;
    }

    public function getServiciosEmergenciaAll()
    {
        $productos = [];
        $vistos = [];
        $sql = "
            SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A' AND (p.emergencia_24_7 = 1)
            -- todas las categorías del producto deben ser tipo 'producto' y activas
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A' OR c.tipo <> 'servicio')
                    
            )
            ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaServicioByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]    = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }


        return $productos;
    }

    public function getServiciosASearchll($search)
    {
        $productos = [];
        $vistos = [];
        $sql = "
            SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A'
            -- todas las categorías del producto deben ser tipo 'producto' y activas
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A' OR c.tipo <> 'servicio' OR c.nombre LIKE '%$search%' OR p.titulo_producto LIKE '%$search%')

            )
            ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaServicioByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]    = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }


        return $productos;
    }

    public function getProductosASearchll($search)
    {
        $productos = [];
        $vistos = [];
        $sql = "
            SELECT p.*, em.nombre AS nombre_empresa, em.tipo_tienda, em.img_path AS imagen_empresa,
                em.latitud, em.longitud, em.provincia, em.canton
            FROM productos p
            INNER JOIN empresas em ON em.id_empresa = p.id_empresa
            WHERE p.estado = 'A'
            -- todas las categorías del producto deben ser tipo 'producto' y activas
            AND NOT EXISTS (
                    SELECT 1
                    FROM categorias c
                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                    AND (c.estado <> 'A' OR c.tipo <> 'producto' OR c.nombre LIKE '%$search%' OR p.titulo_producto LIKE '%$search%')

            )
            ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaServicioByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]    = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }


        return $productos;
    }




    public function getCategoriaByIdEmpresa($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT DISTINCT p.categoria AS id_categoria, p.sub_categoria AS id_subcategoria
        FROM productos p
        WHERE p.estado = 'A' and p.id_empresa = '$id_empresa'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["categorias"] = $this->getCategoriaById($row["id_categoria"]);
            $row["total_producto_categoria"] = $this->getTotalProductosByCategoria($row["id_categoria"]);
            $row["total_producto_sub_categoria"] = $this->getTotalProductosBySubCategoria($row["id_subcategoria"]);
            $row["subcategorias"] = $this->getSubCategoriaById($row["id_subcategoria"]);
            $response[] = $row;
        }
        return $response;
    }

    public function getTotalProductosByCategoria($id_categoria)
    {
        // Contar total de productos asociados a esta empresa
        $stmt2 = $this->conn->prepare("SELECT COUNT(*) as total_productos FROM productos WHERE categoria = '$id_categoria'");
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $total_productos = $result2->fetch_assoc()["total_productos"];

        return (int)$total_productos;;
    }

    public function getTotalProductosBySubCategoria($id_sub_categoria)
    {
        // Contar total de productos asociados a esta empresa
        $stmt2 = $this->conn->prepare("SELECT COUNT(*) as total_productos FROM productos WHERE sub_categoria = '$id_sub_categoria'");
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $total_productos = $result2->fetch_assoc()["total_productos"];

        return (int)$total_productos;;
    }



    public function createCat($nombre, $tipo, $imagen, $categoria_principal)
    {
        if (!$this->isCategoriaExists($nombre)) {
            $categoria_principal = json_encode($categoria_principal);
            $stmt = $this->conn->prepare("INSERT INTO categorias(nombre, tipo, imagen, categoria_principal) values(?,?,?,?)");
            $stmt->bind_param("ssss", $nombre, $tipo, $imagen, $categoria_principal);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }


    public function getBannerAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM banner b
        WHERE b.estado = 'A'");
        // $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }

    public function createBanner($imagen, $imagen_tablet, $imagen_movil, $url)
    {
        $stmt = $this->conn->prepare("INSERT INTO banner(imagen, imagen_tablet, imagen_movil, url) values(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $imagen, $imagen_tablet, $imagen_movil, $url);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateBanner($id, $imagen = null, $imagen_tablet = null, $imagen_movil = null, $url = '')
    {
        $campos = array();
        $tipos = "";
        $parametros = array();

        if ($imagen !== null) {
            $campos[] = "imagen = ?";
            $tipos .= "s";
            $parametros[] = $imagen;
        }

        if ($imagen_tablet !== null) {
            $campos[] = "imagen_tablet = ?";
            $tipos .= "s";
            $parametros[] = $imagen_tablet;
        }

        if ($imagen_movil !== null) {
            $campos[] = "imagen_movil = ?";
            $tipos .= "s";
            $parametros[] = $imagen_movil;
        }

        $campos[] = "url = ?";
        $tipos .= "s";
        $parametros[] = $url;

        $tipos .= "i";
        $parametros[] = $id;

        $sql = "UPDATE banner SET " . implode(", ", $campos) . " WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($tipos, ...$parametros);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function deleteBanner($id)
    {
        $stmt = $this->conn->prepare("UPDATE banner SET estado = 'E' WHERE id_banner = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }


    public function getProductosAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM productos p
        WHERE p.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            $row["categorias"] = $this->getCategoriaByArray($row["categoria"]);

            // Verifica que tenga al menos una categoría y que la primera sea tipo 'producto'
            if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "producto") {
                $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]   = $this->getMembresiaByEmpresa($publicEmpresaId);
                $response[] = $row;
            }
        }

        return $response;
    }


    public function getPublicidadAll()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM publicidad p
        WHERE p.estado = 'A'");
        // $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }


    public function getPublicidadAllAdmin()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM publicidad p
        WHERE p.estado = 'A'");
        // $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }


    public function getPublicidadAllAdminById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM publicidad p
        WHERE p.estado = 'A' AND id_publicidad = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }


    public function createPublicidad($imagen, $imagen_tablet, $imagen_movil, $url, $posicion)
    {
        $stmt = $this->conn->prepare("INSERT INTO publicidad(imagen, imagen_tablet, imagen_movil, url, posicion) values(?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $imagen, $imagen_tablet, $imagen_movil, $url, $posicion);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updatePublicidad($id, $imagen = null, $imagen_tablet = null, $imagen_movil = null, $url = '', $posicion = '')
    {
        $campos = array();
        $tipos = "";
        $parametros = array();

        if ($imagen !== null) {
            $campos[] = "imagen = ?";
            $tipos .= "s";
            $parametros[] = $imagen;
        }

        if ($imagen_tablet !== null) {
            $campos[] = "imagen_tablet = ?";
            $tipos .= "s";
            $parametros[] = $imagen_tablet;
        }

        if ($imagen_movil !== null) {
            $campos[] = "imagen_movil = ?";
            $tipos .= "s";
            $parametros[] = $imagen_movil;
        }

        $campos[] = "url = ?";
        $tipos .= "s";
        $parametros[] = $url;

        $campos[] = "posicion = ?";
        $tipos .= "s";
        $parametros[] = $posicion;

        $tipos .= "i";
        $parametros[] = $id;

        $sql = "UPDATE publicidad SET " . implode(", ", $campos) . " WHERE id_publicidad = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($tipos, ...$parametros);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function deletePublicidad($id)
    {
        $stmt = $this->conn->prepare("UPDATE publicidad SET estado = 'E' WHERE id_publicidad = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getEventosAll(): array
    {
        // --- Helpers internos ---
        $parseSubtipoIds = function (?string $raw): array {
            if (!$raw) return [];

            $raw = trim($raw);

            // 1) intento directo
            $try = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                return self::intsFromArray($try);
            }

            // 2) quitar backslashes y volver a intentar
            $raw2 = str_replace('\\', '', $raw);
            $try = json_decode($raw2, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                return self::intsFromArray($try);
            }

            // 3) si viene sin corchetes, los agregamos y reintentamos
            if ($raw2 !== '' && $raw2[0] !== '[') {
                $raw3 = '[' . trim($raw2, '"\'') . ']';
                $try = json_decode($raw3, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                    return self::intsFromArray($try);
                }
            }

            // 4) último recurso: separar por coma
            $raw4 = trim($raw2, "[] \t\n\r\0\x0B\"'");
            if ($raw4 === '') return [];
            $parts = preg_split('/\s*,\s*/', $raw4);
            return self::intsFromArray($parts ?: []);
        };

        // --- 1) Traer eventos activos ---
        $eventos = [];
        $stmt = $this->conn->prepare("
        SELECT *
        FROM eventos
        WHERE estado = 'A'
    ");
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            if ($this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa") <= 0) {
                continue;
            }
            $eventos[] = $row;
        }
        $stmt->close();

        if (!$eventos) return [];

        // --- 2) Recolectar todos los IDs de subtipos para una sola consulta ---
        $allIds = [];
        foreach ($eventos as $ev) {
            foreach ($parseSubtipoIds($ev['subtipo_evento'] ?? null) as $id) {
                $allIds[$id] = true;
            }
        }
        $allIds = array_keys($allIds);

        // --- 3) Mapear id -> nombre desde subtipo_eventos ---
        $map = [];
        if ($allIds) {
            $in = implode(',', array_map('intval', $allIds));
            $sql = "SELECT id_subtipo_eventos AS id, nombre 
                FROM subtipo_eventos 
                WHERE id_subtipo_eventos IN ($in)";
            if ($rs = $this->conn->query($sql)) {
                while ($r = $rs->fetch_assoc()) {
                    $map[(int)$r['id']] = $r['nombre'];
                }
            }
        }

        // --- 4) Adjuntar subtipos legibles a cada evento ---
        foreach ($eventos as &$ev) {
            $ids = $parseSubtipoIds($ev['subtipo_evento'] ?? null);
            $ev['subtipos'] = array_map(function ($id) use ($map) {
                return [
                    'id'     => $id,
                    'nombre' => $map[$id] ?? ("Subtipo {$id}")
                ];
            }, $ids);
            if (!empty($ev["id_empresa"])) {
                $publicEmpresaId = $this->getPublicEmpresaIdForCreator($ev["id_empresa"], $ev["tipo_creador"] ?? "empresa");
                $ev["membresia"] = $this->getMembresiaByEmpresa($publicEmpresaId);
            }
        }
        unset($ev);

        return $eventos;
    }

    /**
     * Convierte un arreglo mixto en ints únicos y ordenados.
     */
    private static function intsFromArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $v) {
            // por si viene "16", 16, o "id:16"
            if (is_string($v)) $v = preg_replace('/\D+/', '', $v);
            $id = (int)$v;
            if ($id > 0) $out[$id] = true;
        }
        $ids = array_keys($out);
        sort($ids, SORT_NUMERIC);
        return $ids;
    }


    // public function getEventosById($id_evento)
    // {
    //     $response = array();
    //     $stmt = $this->conn->prepare("SELECT *
    //     FROM eventos p
    //     WHERE p.estado = 'A' and id_evento = ?");
    //     $stmt->bind_param("i", $id_evento);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     while ($row = $result->fetch_assoc()) {

    //         $response[] = $row;
    //     }
    //     return $response;
    // }

    public function getEventosById(int $id_evento): array
    {
        // --- Helper local para parsear subtipo_evento, idéntico a getEventosAll() ---
        $parseSubtipoIdsFromRaw = function (?string $raw): array {
            if (!$raw) return [];

            $raw = trim($raw);

            // 1) intento directo
            $try = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                return self::intsFromArray($try);
            }

            // 2) quitar backslashes y volver a intentar
            $raw2 = str_replace('\\', '', $raw);
            $try = json_decode($raw2, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                return self::intsFromArray($try);
            }

            // 3) si viene sin corchetes, reintentar
            if ($raw2 !== '' && $raw2[0] !== '[') {
                $raw3 = '[' . trim($raw2, '"\'') . ']';
                $try = json_decode($raw3, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($try)) {
                    return self::intsFromArray($try);
                }
            }

            // 4) último recurso: split por coma
            $raw4 = trim($raw2, "[] \t\n\r\0\x0B\"'");
            if ($raw4 === '') return [];
            $parts = preg_split('/\s*,\s*/', $raw4);
            return self::intsFromArray($parts ?: []);
        };

        // --- 1) Traer el evento activo ---
        $stmt = $this->conn->prepare("
        SELECT *
        FROM eventos
        WHERE estado = 'A' AND id_evento = ?
        LIMIT 1
    ");
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        $res = $stmt->get_result();
        $ev = $res->fetch_assoc();
        $stmt->close();

        if (!$ev) {
            return []; // no existe o está inactivo
        }

        if ($this->getPublicEmpresaIdForCreator($ev["id_empresa"], $ev["tipo_creador"] ?? "empresa") <= 0) {
            return [];
        }

        // --- 2) Subtipos: parsear IDs y mapear a nombres (igual que getEventosAll) ---
        $ids = $parseSubtipoIdsFromRaw($ev['subtipo_evento'] ?? null);

        $map = [];
        if ($ids) {
            $in = implode(',', array_map('intval', $ids));
            $sql = "SELECT id_subtipo_eventos AS id, nombre
                FROM subtipo_eventos
                WHERE id_subtipo_eventos IN ($in)";
            if ($rs = $this->conn->query($sql)) {
                while ($r = $rs->fetch_assoc()) {
                    $map[(int)$r['id']] = $r['nombre'];
                }
            }
        }

        $ev['subtipos'] = array_map(function ($id) use ($map) {
            return [
                'id'     => $id,
                'nombre' => $map[$id] ?? ("Subtipo {$id}")
            ];
        }, $ids);

        // --- 3) Galería del evento (solo activas) ---
        $galeria = [];
        $stmtG = $this->conn->prepare("
        SELECT id_galeria, imagen, estado, created_at, updated_at
        FROM galerias_eventos
        WHERE estado = 'A' AND id_evento = ?
        ORDER BY id_galeria ASC
    ");
        $stmtG->bind_param("i", $id_evento);
        $stmtG->execute();
        $resG = $stmtG->get_result();
        while ($rowG = $resG->fetch_assoc()) {
            $galeria[] = [
                'id_galeria' => (int)$rowG['id_galeria'],
                'imagen'     => $rowG['imagen'],
                'estado'     => $rowG['estado'],
                'created_at' => $rowG['created_at'],
                'updated_at' => $rowG['updated_at'],
            ];
        }
        $stmtG->close();

        $ev['galeria'] = $galeria;

        // --- 4) Enriquecer con datos del creador (empresa o sucursal) ---
        $tipoCreador = strtolower(trim((string)($ev['tipo_creador'] ?? 'empresa')));
        $idCreador   = (int)($ev['id_empresa'] ?? 0);

        if ($idCreador > 0) {
            if ($tipoCreador === 'sucursal') {
                $stmtS = $this->conn->prepare("
                    SELECT
                        s.id_sucursal,
                        s.id_empresa AS empresa_padre_id,
                        s.nombre,
                        s.latitud,
                        s.longitud,
                        s.direccion,
                        s.provincia,
                        s.canton,
                        s.correo,
                        s.telefono_contacto,
                        s.whatsapp_contacto,
                        e.nombre AS empresa_nombre,
                        e.red_tiktok,
                        e.red_instagram,
                        e.red_youtube,
                        e.red_facebook,
                        e.red_linkedln,
                        e.red_web,
                        e.descripcion AS empresa_descripcion
                    FROM sucursales s
                    LEFT JOIN empresas e ON e.id_empresa = s.id_empresa
                    WHERE s.id_sucursal = ?
                    LIMIT 1
                ");
                if ($stmtS) {
                    $stmtS->bind_param("i", $idCreador);
                    $stmtS->execute();
                    $sucursal = $stmtS->get_result()->fetch_assoc();
                    $stmtS->close();

                    if ($sucursal) {
                        $ev['latitud'] = $sucursal['latitud'] ?? ($ev['latitud'] ?? null);
                        $ev['longitud'] = $sucursal['longitud'] ?? ($ev['longitud'] ?? null);
                        $ev['direccion'] = $ev['direccion'] ?: ($sucursal['direccion'] ?? '');
                        $ev['provincia'] = $ev['provincia'] ?: ($sucursal['provincia'] ?? '');
                        $ev['canton'] = $ev['canton'] ?: ($sucursal['canton'] ?? '');
                        $ev['organizador'] = $ev['organizador'] ?: ($sucursal['nombre'] ?? ($sucursal['empresa_nombre'] ?? ''));
                        $ev['empresa_nombre'] = $sucursal['empresa_nombre'] ?? ($sucursal['nombre'] ?? '');
                        $ev['empresa_descripcion'] = $sucursal['empresa_descripcion'] ?? '';
                        $ev['red_tiktok'] = $sucursal['red_tiktok'] ?? '';
                        $ev['red_instagram'] = $sucursal['red_instagram'] ?? '';
                        $ev['red_youtube'] = $sucursal['red_youtube'] ?? '';
                        $ev['red_facebook'] = $sucursal['red_facebook'] ?? '';
                        $ev['red_linkedln'] = $sucursal['red_linkedln'] ?? '';
                        $ev['red_web'] = $sucursal['red_web'] ?? '';

                        if (empty($ev['telefono']) && !empty($sucursal['telefono_contacto'])) {
                            $ev['telefono'] = $sucursal['telefono_contacto'];
                        }
                        if (empty($ev['telefono']) && !empty($sucursal['whatsapp_contacto'])) {
                            $ev['telefono'] = $sucursal['whatsapp_contacto'];
                        }
                        if (empty($ev['correo']) && !empty($sucursal['correo'])) {
                            $ev['correo'] = $sucursal['correo'];
                        }
                    }
                }
            } else {
                $stmtE = $this->conn->prepare("
                    SELECT
                        id_empresa,
                        nombre,
                        descripcion,
                        latitud,
                        longitud,
                        direccion,
                        provincia,
                        canton,
                        correo,
                        telefono_contacto,
                        whatsapp_contacto,
                        red_tiktok,
                        red_instagram,
                        red_youtube,
                        red_facebook,
                        red_linkedln,
                        red_web
                    FROM empresas
                    WHERE id_empresa = ?
                    LIMIT 1
                ");
                if ($stmtE) {
                    $stmtE->bind_param("i", $idCreador);
                    $stmtE->execute();
                    $empresa = $stmtE->get_result()->fetch_assoc();
                    $stmtE->close();

                    if ($empresa) {
                        $ev['latitud'] = $empresa['latitud'] ?? ($ev['latitud'] ?? null);
                        $ev['longitud'] = $empresa['longitud'] ?? ($ev['longitud'] ?? null);
                        $ev['direccion'] = $ev['direccion'] ?: ($empresa['direccion'] ?? '');
                        $ev['provincia'] = $ev['provincia'] ?: ($empresa['provincia'] ?? '');
                        $ev['canton'] = $ev['canton'] ?: ($empresa['canton'] ?? '');
                        $ev['organizador'] = $ev['organizador'] ?: ($empresa['nombre'] ?? '');
                        $ev['empresa_nombre'] = $empresa['nombre'] ?? '';
                        $ev['empresa_descripcion'] = $empresa['descripcion'] ?? '';
                        $ev['red_tiktok'] = $empresa['red_tiktok'] ?? '';
                        $ev['red_instagram'] = $empresa['red_instagram'] ?? '';
                        $ev['red_youtube'] = $empresa['red_youtube'] ?? '';
                        $ev['red_facebook'] = $empresa['red_facebook'] ?? '';
                        $ev['red_linkedln'] = $empresa['red_linkedln'] ?? '';
                        $ev['red_web'] = $empresa['red_web'] ?? '';

                        if (empty($ev['telefono']) && !empty($empresa['telefono_contacto'])) {
                            $ev['telefono'] = $empresa['telefono_contacto'];
                        }
                        if (empty($ev['telefono']) && !empty($empresa['whatsapp_contacto'])) {
                            $ev['telefono'] = $empresa['whatsapp_contacto'];
                        }
                        if (empty($ev['correo']) && !empty($empresa['correo'])) {
                            $ev['correo'] = $empresa['correo'];
                        }
                    }
                }
            }
        }

        return $ev;
    }



    public function createEvento($p)
    {
        // Limpieza básica
        $id_empresa       = intval($p['id_empresa'] ?? 0);

        $titulo           = $this->conn->real_escape_string($p['titulo'] ?? '');
        $descripcion      = $this->conn->real_escape_string($p['descripcion'] ?? '');
        $organizador      = $this->conn->real_escape_string($p['organizador'] ?? '');
        $enlace           = $this->conn->real_escape_string($p['enlace'] ?? '');

        $fecha_inicio     = $this->conn->real_escape_string($p['fecha_hora_inicio'] ?? '');
        $fecha_fin        = $this->conn->real_escape_string($p['fecha_hora_fin'] ?? '');

        $tipo_evento      = $this->conn->real_escape_string($p['tipo_evento'] ?? '');
        $subtipo_evento   = $this->conn->real_escape_string($p['subtipo_evento'] ?? '[]'); // JSON de ids

        $direccion        = $this->conn->real_escape_string($p['direccion'] ?? '');
        $provincia        = $this->conn->real_escape_string($p['provincia'] ?? '');
        $canton           = $this->conn->real_escape_string($p['canton'] ?? '');
        $modalidad        = $this->conn->real_escape_string($p['modalidad'] ?? '');

        $tipo_entrada     = $this->conn->real_escape_string($p['tipo_entrada'] ?? '');
        $precio_secciones = $this->conn->real_escape_string($p['precio_secciones'] ?? ''); // JSON como string
        $enlace_compra    = $this->conn->real_escape_string($p['enlace_compra'] ?? '');

        $imagen           = $this->conn->real_escape_string($p['imagen'] ?? '');
        $portada_evento   = $this->conn->real_escape_string($p['portada_evento'] ?? '');

        $nombre_contacto  = $this->conn->real_escape_string($p['nombre_contacto'] ?? '');
        $telefono         = $this->conn->real_escape_string($p['telefono'] ?? '');
        $correo           = $this->conn->real_escape_string($p['correo'] ?? '');
        $latitud          = $this->conn->real_escape_string($p['latitud'] ?? '');
        $longitud         = $this->conn->real_escape_string($p['longitud'] ?? '');

        $estado           = $this->conn->real_escape_string($p['estado'] ?? 'A');
        $tipo_creador     = $this->conn->real_escape_string($p['tipo_user'] ?? 'empresa');

        // PREPARED
        $sql = "INSERT INTO eventos
            (id_empresa, titulo, descripcion, organizador, imagen, fecha_hora_inicio, fecha_hora_fin,
             estado, tipo_evento, subtipo_evento, enlace, direccion, provincia, canton, modalidad,
             tipo_entrada, precio_secciones, enlace_compra, portada_evento, nombre_contacto, telefono, correo, latitud, longitud, tipo_creador)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $types = "i" . str_repeat("s", 24);
        $stmt->bind_param(
            $types,
            $id_empresa,
            $titulo,
            $descripcion,
            $organizador,
            $imagen,
            $fecha_inicio,
            $fecha_fin,
            $estado,
            $tipo_evento,
            $subtipo_evento,
            $enlace,
            $direccion,
            $provincia,
            $canton,
            $modalidad,
            $tipo_entrada,
            $precio_secciones,
            $enlace_compra,
            $portada_evento,
            $nombre_contacto,
            $telefono,
            $correo,
            $latitud,
            $longitud,
            $tipo_creador
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function addGaleriaEvento($id_evento, $urls = [])
    {
        if (!$urls || !is_array($urls)) return 0;

        $sql = "INSERT INTO galerias_eventos (imagen, id_evento, estado) VALUES (?, ?, 'A')";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return 0;

        $insertados = 0;
        foreach ($urls as $u) {
            $img = $this->conn->real_escape_string($u);
            $stmt->bind_param("si", $img, $id_evento);
            if ($stmt->execute()) $insertados++;
        }
        return $insertados;
    }

    public function updateEvento($p)
    {
        $id = intval($p['id_evento'] ?? ($p['id'] ?? 0));
        $titulo = $p['titulo'] ?? '';
        $descripcion = $p['descripcion'] ?? '';
        $organizador = $p['organizador'] ?? '';
        $enlace = $p['enlace'] ?? '';
        $tipo_evento = $p['tipo_evento'] ?? ($p['tipo'] ?? '');
        $subtipo_evento = $p['subtipo_evento'] ?? '[]';
        $direccion = $p['direccion'] ?? '';
        $provincia = $p['provincia'] ?? '';
        $canton = $p['canton'] ?? '';
        $modalidad = $p['modalidad'] ?? '';
        $tipo_entrada = $p['tipo_entrada'] ?? '';
        $precio_secciones = $p['precio_secciones'] ?? '';
        $enlace_compra = $p['enlace_compra'] ?? '';
        $imagen = $p['imagen'] ?? '';
        $portada_evento = $p['portada_evento'] ?? $imagen;
        $nombre_contacto = $p['nombre_contacto'] ?? '';
        $telefono = $p['telefono'] ?? '';
        $correo = $p['correo'] ?? '';
        $fecha_hora_inicio = $p['fecha_hora_inicio'] ?? '';
        $fecha_hora_fin = $p['fecha_hora_fin'] ?? '';
        $latitud = $p['latitud'] ?? null;
        $longitud = $p['longitud'] ?? null;

        $stmt = $this->conn->prepare("UPDATE eventos SET titulo = ?, descripcion = ?, organizador = ?, imagen = ?, fecha_hora_inicio = ?, fecha_hora_fin = ?, tipo_evento = ?, subtipo_evento = ?, enlace = ?, direccion = ?, provincia = ?, canton = ?, modalidad = ?, tipo_entrada = ?, precio_secciones = ?, enlace_compra = ?, portada_evento = ?, nombre_contacto = ?, telefono = ?, correo = ?, latitud = ?, longitud = ? WHERE id_evento = ?");
        $stmt->bind_param("ssssssssssssssssssssssi", $titulo, $descripcion, $organizador, $imagen, $fecha_hora_inicio, $fecha_hora_fin, $tipo_evento, $subtipo_evento, $enlace, $direccion, $provincia, $canton, $modalidad, $tipo_entrada, $precio_secciones, $enlace_compra, $portada_evento, $nombre_contacto, $telefono, $correo, $latitud, $longitud, $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function deleteEvento($id)
    {
        $stmt = $this->conn->prepare("UPDATE evento SET estado = 'E' WHERE id_evento = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getEventoById($id_evento)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM eventos
        WHERE estado = 'A' AND id_evento = ?");
        $stmt->bind_param("s", $id_evento);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getEventoEmpresaById($id_empresa, $tipo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM eventos
        WHERE estado = 'A' AND id_empresa = ? AND tipo_creador = ?;");
        $stmt->bind_param("ss", $id_empresa, $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }


    public function resetPassword($email)
    {
        $stmt = $this->conn->prepare("SELECT id_usuario, correo FROM usuarios where correo =? AND estado = 'A'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $reset_user = $this->resetearPass($row["id_usuario"]);
            if ($reset_user) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_DOES_NOT_EXIST;
        }
    }


    public function resetPasswordCliente2($email)
    {
        $stmt = $this->conn->prepare("SELECT id_cliente, correo FROM clientes where correo =? AND estado = 'A'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $reset_user = $this->resetearPassCliente($row["id_cliente"]);
            if ($reset_user) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_DOES_NOT_EXIST;
        }
    }

    public function getClienteIdPorCorreo(string $correo): int
    {
        $sql  = "SELECT 1 FROM clientes WHERE correo = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();              // permite leer num_rows

        $exists = ($stmt->num_rows > 0) ? 1 : 0;

        $stmt->free_result();
        $stmt->close();

        return $exists; // 1 si existe, 0 si no
    }


    public function insertOrden(array $data, int $idCliente)
    {
        // Normaliza modo y coords: si pickup => NULL
        $envio  = (int)($data['envio_domicilio'] ?? 0);
        $lat    = $envio ? ($data['latitud']  !== '' ? $data['latitud']  : null) : null;
        $lng    = $envio ? ($data['longitud'] !== '' ? $data['longitud'] : null) : null;
        $retiro = !$envio ? ($data['nombre_retiro'] ?? null) : null;
        $agencia_cercana = (isset($data['agencia_cercana']) && $data['agencia_cercana'] !== '')
            ? trim($data['agencia_cercana'])
            : null;

        $sql = "INSERT INTO ordenes
                (numero_orden, subtotal, iva, total, id_cliente, envio_domicilio, latitud, longitud, nombre_retiro, agencia_cercana)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssisssss",
            $data['numero_orden'],
            $data['subtotal'],
            $data['iva'],
            $data['total'],
            $idCliente,
            $envio,
            $lat,
            $lng,
            $retiro,
            $agencia_cercana,
        );
        $stmt->execute();
        $stmt->close();
        return (int)$this->conn->insert_id;
    }


    public function insertOrdenesEmpresas(int $idOrden, array $ordenesEmpresas, array $datos_domicilio, $aplica_domicilio): void
    {
        $provincia = $datos_domicilio["provincia"] ?? '';
        $canton    = $datos_domicilio["canton"] ?? '';
        $sector    = $datos_domicilio["sector"] ?? '';

        // Si ya tienes las columnas extra, déjalas. Si no, quítalas del INSERT.
        $sql = "INSERT INTO ordenes_empresas (
                id_empresa, productos, total, id_ordenes, envio_domicilio, id_trayecto, id_ruta,
                total_envio, peso_total, valor_base_trayecto, valor_adicional_kg
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);

        foreach ($ordenesEmpresas as $oe) {
            $idEmpresa = (int)($oe['id_empresa'] ?? 0);
            if ($idEmpresa <= 0) {
                continue;
            }

            $empresa = $this->getEmpresaById($idEmpresa);
            if (!$empresa) {
                continue;
            }

            $envio_domicilio = (int)($oe['envio_domicilio'] ?? 0);

            $id_trayecto = 0;
            $id_ruta = 0;
            $valor_trayecto = 0.0;
            $adicional_trayecto = 0.0;
            $total_envio = 0.0;

            // Productos de esta empresa
            $productos = $oe['productos'] ?? [];
            // Sumar peso total = sum(peso * cantidad)
            $pesoTotal = 0.0;
            foreach ($productos as $p) {
                $pesoItem = isset($p['peso']) ? (float)$p['peso'] : 0.0;
                $cant     = isset($p['cantidad']) ? (float)$p['cantidad'] : 1.0;
                $pesoTotal += $pesoItem * $cant;
            }

            // 🔹 Redondeo final
            $pesoTotal = round($pesoTotal, 0, PHP_ROUND_HALF_UP);

            if ($envio_domicilio === 0) {
                // Ruta origen (empresa) -> destino (cliente)
                $obtenerRuta = $this->getObtenerRutaOrigenDestino(
                    $empresa["provincia"],
                    $empresa["canton"],
                    $provincia,
                    $canton,
                    $sector,
                    $aplica_domicilio
                );
                if (!$obtenerRuta || empty($obtenerRuta[0])) {
                    continue;
                }

                // Tarifa del trayecto
                $trayecto = $this->getTrayectoByTrayecto($obtenerRuta[0]["tipo_cobertura"]);
                if (!$trayecto || empty($trayecto[0])) {
                    continue;
                }

                $id_trayecto         = (int)$trayecto[0]["id_trayecto"];
                $id_ruta             = (int)$obtenerRuta[0]["id_ruta"];
                $valor_trayecto      = (float)$trayecto[0]["valor"];     // base por kg hasta 2 kg (según tu regla)
                $adicional_trayecto  = (float)$trayecto[0]["adicional"]; // adicional por kg sobre 2 kg

                // Cálculo del envío según tu regla
                if ($pesoTotal <= 2.0) {
                    $total_envio = $valor_trayecto * $pesoTotal;
                } else {
                    $exceso = $pesoTotal - 2.0;
                    $total_envio = ($valor_trayecto) + ($adicional_trayecto * $exceso);
                }
                $total_envio = $total_envio * 1.1;
                $total_envio = round($total_envio, 2);
            }

            // Totales de la empresa (ya lo traes en $oe['total'])
            $totalEmp = (float)($oe['total'] ?? 0);
            $json     = json_encode($productos, JSON_UNESCAPED_UNICODE);


            // i s d i i i i d d d d  => 11 params
            $stmt->bind_param(
                "isdiiiidddd",
                $idEmpresa,          // i
                $json,               // s
                $totalEmp,           // d
                $idOrden,            // i
                $envio_domicilio,    // i
                $id_trayecto,        // i
                $id_ruta,            // i
                $total_envio,        // d
                $pesoTotal,          // d
                $valor_trayecto,     // d
                $adicional_trayecto  // d
            );
            $stmt->execute();
        }
        $stmt->close();
    }

    // public function insertOrdenesEmpresas(int $idOrden, array $ordenesEmpresas, array $datos_domicilio, $aplica_domicilio): void
    // {
    //     $provincia = $datos_domicilio["provincia"] ?? '';
    //     $canton    = $datos_domicilio["canton"] ?? '';
    //     $sector    = $datos_domicilio["sector"] ?? '';

    //     $sql = "INSERT INTO ordenes_empresas (
    //             id_empresa, productos, total, id_ordenes, id_trayecto, id_ruta,
    //             total_envio, peso_total, valor_base_trayecto, valor_adicional_kg
    //         ) VALUES (?,?,?,?,?,?,?,?,?,?)";

    //     $stmt = $this->conn->prepare($sql);

    //     if (!$stmt) {
    //         var_dump("❌ PREPARE ERROR:", $this->conn->error, $sql);
    //         return;
    //     }

    //     foreach ($ordenesEmpresas as $oe) {
    //         $idEmpresa = (int)($oe['id_empresa'] ?? 0);
    //         if ($idEmpresa <= 0) {
    //             var_dump("⚠️ id_empresa inválido:", $oe);
    //             continue;
    //         }

    //         $empresa = $this->getEmpresaById($idEmpresa);
    //         if (!$empresa) {
    //             var_dump("⚠️ Empresa no encontrada:", $idEmpresa);
    //             continue;
    //         }

    //         $obtenerRuta = $this->getObtenerRutaOrigenDestino(
    //             $empresa["provincia"],
    //             $empresa["canton"],
    //             $provincia,
    //             $canton,
    //             $sector,
    //             $aplica_domicilio
    //         );

    //         if (!$obtenerRuta || empty($obtenerRuta[0])) {
    //             var_dump("⚠️ No se encontró ruta para empresa:", $idEmpresa, "origen:", $empresa["provincia"], $empresa["canton"], "destino:", $provincia, $canton, $sector, "aplica:", $aplica_domicilio);
    //             continue;
    //         }

    //         // OJO: aquí asegúrate qué campo trae el nombre del trayecto/cobertura
    //         $nombreTrayecto = $obtenerRuta[0]["tipo_cobertura"] ?? '';
    //         if ($nombreTrayecto === '') {
    //             var_dump("⚠️ Ruta sin campo trayecto:", $obtenerRuta[0]);
    //             continue;
    //         }

    //         $trayecto = $this->getTrayectoByTrayecto($nombreTrayecto);
    //         if (!$trayecto || empty($trayecto[0])) {
    //             var_dump("⚠️ No se encontró trayecto en tabla trayecto para:", $nombreTrayecto);
    //             continue;
    //         }

    //         $id_trayecto        = (int)($trayecto[0]["id_trayecto"] ?? 0);
    //         $id_ruta            = (int)($obtenerRuta[0]["id_ruta"] ?? 0);
    //         $valor_trayecto     = (float)($trayecto[0]["valor"] ?? 0);
    //         $adicional_trayecto = (float)($trayecto[0]["adicional"] ?? 0);

    //         $productos = $oe['productos'] ?? [];
    //         $pesoTotal = 0.0;

    //         foreach ($productos as $p) {
    //             $pesoItem = isset($p['peso']) ? (float)$p['peso'] : 0.0;
    //             $cant     = isset($p['cantidad']) ? (float)$p['cantidad'] : 1.0;
    //             $pesoTotal += $pesoItem * $cant;
    //         }

    //         // Calculo (según tu regla)
    //         if ($pesoTotal <= 2.0) {
    //             $total_envio = $valor_trayecto * $pesoTotal;
    //         } else {
    //             $exceso = $pesoTotal - 2.0;
    //             $total_envio = ($valor_trayecto * 2.0) + ($adicional_trayecto * $exceso);
    //         }
    //         $total_envio = round((float)$total_envio, 2);

    //         $totalEmp = (float)($oe['total'] ?? 0);
    //         $json     = json_encode($productos, JSON_UNESCAPED_UNICODE);

    //         // ✅ TIPOS CORRECTOS:
    //         // id_empresa (i)
    //         // productos (s)
    //         // total (d)
    //         // id_ordenes (i)
    //         // id_trayecto (i)
    //         // id_ruta (i)
    //         // total_envio (d)
    //         // peso_total (d)
    //         // valor_base_trayecto (d)
    //         // valor_adicional_kg (d)
    //         $stmt->bind_param(
    //             "isdiiddddd",
    //             $idEmpresa,
    //             $json,
    //             $totalEmp,
    //             $idOrden,
    //             $id_trayecto,
    //             $id_ruta,
    //             $total_envio,
    //             $pesoTotal,
    //             $valor_trayecto,
    //             $adicional_trayecto
    //         );
    //     }

    //     $stmt->close();
    // }


    public function getObtenerRutaOrigenDestino($provinciaOrigen, $cantonOrigen, $provinciaDestino, $cantonDestino, $parroquiaDestino, $aplica_domicilio)
    {
        $rows = [];

        $provD = trim((string)$provinciaDestino);
        $canD  = trim((string)$cantonDestino);
        $parD  = trim((string)$parroquiaDestino);

        if ($provD === '' || $canD === '') return [];

        // LIKE
        $likeProv = "%{$provD}%";
        $likeCan  = "%{$canD}%";
        $likePar  = "%{$parD}%";

        // Si aplica_domicilio = 'Y' => quiero SOLO dentro de cobertura (fuera_cobertura = '0')
        // Si aplica_domicilio = 'N' => puedo devolver cualquiera
        $whereCobertura = "";
        $types = "sss";
        $params = [$likeProv, $likeCan, $likePar];



        $sql = "
        SELECT rg.*
        FROM ruta_grupo rg
        WHERE rg.estado = 'A'
          AND rg.provincia LIKE ?
          AND rg.canton LIKE ?
          " . ($parD !== '' ? " AND rg.parroquia LIKE ? " : "") . "
        ORDER BY rg.parroquia ASC
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            var_dump("❌ PREPARE ERROR:", $this->conn->error, $sql);
            return [];
        }

        if ($parD !== '') {
            // s s s
            $stmt->bind_param("sss", $likeProv, $likeCan, $likePar);
        } else {
            // s s (si no filtras por parroquia)
            $stmt->bind_param("ss", $likeProv, $likeCan);
        }

        if (!$stmt->execute()) {
            var_dump("❌ EXECUTE ERROR:", $stmt->error);
            $stmt->close();
            return [];
        }

        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();

        return $rows;
    }


    function getTrayectoByTrayecto($trayecto)
    {
        $guias = array();
        $stmt = $this->conn->prepare("SELECT * FROM trayecto WHERE nombre LIKE '%$trayecto%' and estado = 'A' and tipo = 'mercancia_premier'");
        // $stmt->bind_param("s", $trayecto);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($guia = $result->fetch_assoc()) {
            $guias[] = $guia;
        }

        return $guias;
    }

    function getTrayectoBy($trayecto)
    {
        $guias = array();
        $stmt = $this->conn->prepare("SELECT * FROM trayecto WHERE id_trayecto = '$trayecto' and estado = 'A' and tipo = 'mercancia_premier'");
        // $stmt->bind_param("s", $trayecto);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($guia = $result->fetch_assoc()) {
            $guias[] = $guia;
        }

        return $guias;
    }

    function getRutaById($id_ruta)
    {
        $guias = array();
        $stmt = $this->conn->prepare("SELECT * FROM ruta_grupo WHERE id_ruta = '$id_ruta' and estado = 'A'");
        // $stmt->bind_param("s", $trayecto);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($guia = $result->fetch_assoc()) {
            $guias[] = $guia;
        }

        return $guias;
    }
    public function insertDatosFacturacion(int $idOrden, array $fact, int $idCliente = 0): void
    {
        $sql = "INSERT INTO datos_facturacion
                (tipo_identificacion, razon_social, numero_identificacion, correo, telefono, direccion, forma_pago, id_orden, id_cliente)
                VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssii",
            $fact['tipo_identificacion'],
            $fact['razon_social'],
            $fact['numero_identificacion'],
            $fact['correo'],
            $fact['telefono'],
            $fact['direccion'],
            $fact['forma_pago'],
            $idOrden,
            $idCliente
        );
        $stmt->execute();
        $stmt->close();
    }

    public function insertDatosDomicilio(int $idOrden, array $envio): void
    {
        $sql = "INSERT INTO datos_domicilio
                (nombres, cedula, telefono, direccion_exacta, punto_referencial, provincia, canton, parroquia,
                 codigo_postal, horario_entrega, observaciones, id_orden, latitud, longitud, parroquia_id, canton_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssissss",
            $envio['nombres'],
            $envio['cedula'],
            $envio['telefono'],
            $envio['direccion_exacta'],
            $envio['punto_referencial'],
            $envio['provincia'],
            $envio['canton'],
            $envio['sector'],
            $envio['codigo_postal'],
            $envio['horario_entrega'],
            $envio['observaciones'],
            $idOrden,
            $envio['latitud'],
            $envio['longitud'],
            $envio['sector_id'],
            $envio['canton_id']

        );
        $stmt->execute();
        $stmt->close();
    }

    public function crearCliente(string $correo, string $passwordPlano, string $nombres, string $cedula, string $telefono): int
    {
        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $sql = "INSERT INTO clientes (correo, password, nombres, cedula, telefono)
                VALUES (?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $correo, $hash, $nombres, $cedula, $telefono);
        $stmt->execute();
        $stmt->close();
        return (int)$this->conn->insert_id;
    }

    public function createOrdenCompleta(array $data)
    {
        // 1) Resolver id_cliente desde sesión (enviado por el frontend)
        $idCliente = (int)($data['id_cliente'] ?? 0);

        if ($idCliente <= 0) {
            return ['success' => false, 'msg' => 'Se requiere sesión activa para generar una orden.'];
        }

        // 2) Orden
        $idOrden = $this->insertOrden($data, $idCliente);

        // 3) Ordenes-Empresas
        $this->insertOrdenesEmpresas($idOrden, $data['ordenes_empresas'], $data["datos_domicilio"], $data["aplica_domicilio"]);

        // 4) Facturación (con id_cliente)
        $this->insertDatosFacturacion($idOrden, $data['datos_facturacion'], $idCliente);

        // 5) Domicilio
        $this->insertDatosDomicilio($idOrden, $data['datos_domicilio']);

        return [
            'success'      => true,
            'id_orden'     => $idOrden,
            'numero_orden' => $data['numero_orden'],
            'id_cliente'   => $idCliente
        ];
    }

    // public function createOrdenCompleta($data)
    // {
    //     var_dump($data);
    //     // Hace que cualquier error de mysqli lance una excepción (mucho más fácil de depurar)
    //     mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    //     $conn = $this->conn;
    //     $conn->begin_transaction();

    //     // Helper para registrar dónde estamos si algo falla
    //     $step = 'inicio';

    //     try {
    //         // ---------- 0) Alta / Reuso del cliente ----------
    //         $step = 'check/insert cliente';

    //         // Acepta ambos nombres desde el front
    //         $correo  = $data['correo_inicio']  ?? $data['correo_sesion'] ?? null;
    //         $plainPw = $data['password_inicio'] ?? $data['password']     ?? null;
    //         if (!$correo || !$plainPw) {
    //             throw new Exception("Faltan credenciales (correo o password).");
    //         }
    //         $password = password_hash($plainPw, PASSWORD_DEFAULT);

    //         // ¿Existe ya?
    //         $sql = "SELECT id_cliente FROM clientes WHERE correo = ? LIMIT 1";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bind_param("s", $correo);
    //         $stmt->execute();
    //         $stmt->bind_result($id_cliente_existente);
    //         if ($stmt->fetch()) {
    //             // Reusar cliente existente en vez de abortar
    //             $id_usuario = $id_cliente_existente;
    //             $stmt->close();
    //         } else {
    //             $stmt->close();

    //             $sql = "INSERT INTO clientes (correo, password, nombres, cedula, telefono)
    //                 VALUES (?,?,?,?,?)";
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bind_param(
    //                 "sssss",
    //                 $correo,
    //                 $password,
    //                 $data["datos_facturacion"]["razon_social"],
    //                 $data["datos_facturacion"]["numero_identificacion"],
    //                 $data["datos_facturacion"]["telefono"]
    //             );
    //             $stmt->execute();
    //             $id_usuario = $conn->insert_id;
    //             $stmt->close();
    //         }

    //         // ---------- 1) Insert en ORDENES ----------
    //         $step = 'insert orden';

    //         // Normaliza modo y coords (NULL si pickup)
    //         $envio_domicilio = (int)($data['envio_domicilio'] ?? 0);
    //         $lat = $envio_domicilio ? ($data['latitud']  !== '' ? $data['latitud']  : null) : null;
    //         $lng = $envio_domicilio ? ($data['longitud'] !== '' ? $data['longitud'] : null) : null;
    //         $nombre_retiro = !$envio_domicilio ? ($data['nombre_retiro'] ?? null) : null;

    //         $sql = "INSERT INTO ordenes
    //             (numero_orden, subtotal, iva, total, id_cliente, envio_domicilio, latitud, longitud, nombre_retiro)
    //             VALUES (?,?,?,?,?,?,?,?,?)";
    //         $stmt = $conn->prepare($sql);
    //         // s d d d i d d s  -> usamos sdddi dds, pero lat/lng pueden venir nulos; 's' acepta NULL sin problema
    //         $stmt->bind_param(
    //             "sdddiisss",
    //             $data["numero_orden"],
    //             (float)$data["subtotal"],
    //             (float)$data["iva"],
    //             (float)$data["total"],
    //             $id_usuario,
    //             $envio_domicilio,
    //             $lat,
    //             $lng,
    //             $nombre_retiro
    //         );
    //         $stmt->execute();
    //         $id_orden = $conn->insert_id;
    //         $stmt->close();

    //         // ---------- 2) Insert en ORDENES_EMPRESAS ----------
    //         $step = 'insert ordenes_empresas';

    //         foreach ($data["ordenes_empresas"] as $oe) {
    //             $id_empresa     = (int)$oe["id_empresa"];
    //             $productos_json = json_encode($oe["productos"], JSON_UNESCAPED_UNICODE);
    //             $total_empresa  = (float)$oe["total"];

    //             $sql = "INSERT INTO ordenes_empresas (id_empresa, productos, total, id_ordenes)
    //                 VALUES (?,?,?,?)";
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bind_param("isdi", $id_empresa, $productos_json, $total_empresa, $id_orden);
    //             $stmt->execute();
    //             $stmt->close();
    //         }

    //         // ---------- 3) Insert en DATOS_DOMICILIO (solo si envío) ----------
    //         if ($envio_domicilio === 1) {
    //             $step = 'insert datos_domicilio';
    //             $e = $data["datos_domicilio"];

    //             $sql = "INSERT INTO datos_domicilio
    //                 (nombres, cedula, telefono, direccion_exacta, punto_referencial, provincia, canton, parroquia,
    //                  codigo_postal, horario_entrega, observaciones, id_orden)
    //                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bind_param(
    //                 "sssssssssssi",
    //                 $e["nombres"],
    //                 $e["cedula"],
    //                 $e["telefono"],
    //                 $e["direccion_exacta"],
    //                 $e["punto_referencial"],
    //                 $e["provincia"],
    //                 $e["canton"],
    //                 $e["parroquia"],
    //                 $e["codigo_postal"],
    //                 $e["horario_entrega"],
    //                 $e["observaciones"],
    //                 $id_orden
    //             );
    //             $stmt->execute();
    //             $stmt->close();
    //         }

    //         // ---------- 4) Insert en DATOS_FACTURACION ----------
    //         $step = 'insert datos_facturacion';
    //         $f = $data["datos_facturacion"];

    //         $sql = "INSERT INTO datos_facturacion
    //             (tipo_identificacion, razon_social, numero_identificacion, correo, telefono, direccion, forma_pago, id_orden)
    //             VALUES (?,?,?,?,?,?,?,?)";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bind_param(
    //             "sssssssi",
    //             $f["tipo_identificacion"],
    //             $f["razon_social"],
    //             $f["numero_identificacion"],
    //             $f["correo"],
    //             $f["telefono"],
    //             $f["direccion"],
    //             $f["forma_pago"],
    //             $id_orden
    //         );
    //         $stmt->execute();
    //         $stmt->close();

    //         // Si llegamos aquí, todo OK
    //         $conn->commit();
    //         return ["success" => true, "numero_orden" => $data["numero_orden"]];
    //     } catch (\mysqli_sql_exception $e) {
    //         // Errores nativos de MySQLi (prep/exec/bind) con contexto del paso
    //         $conn->rollback();
    //         error_log("[SQL][$step] {$e->getMessage()}");
    //         return ["success" => false, "msg" => "SQL error en '$step': " . $e->getMessage()];
    //     } catch (\Throwable $e) {
    //         // Cualquier otro error PHP
    //         $conn->rollback();
    //         error_log("[APP][$step] {$e->getMessage()}");
    //         return ["success" => false, "msg" => "APP error en '$step': " . $e->getMessage()];
    //     }
    // }


    // public function createOrdenCompleta($data)
    // {
    //     $conn = $this->conn;
    //     $conn->begin_transaction();

    //     try {
    //         // 0. Insertar en tabla clientes (si no existe ya)
    //         $correo_sesion = $data["correo_inicio"];
    //         $password = password_hash($data["password_inicio"], PASSWORD_DEFAULT); // Encriptar la contraseña

    //         // Verificar si ya existe el cliente con ese correo
    //         $stmtCheck = $conn->prepare("SELECT id_cliente FROM clientes WHERE correo = ?");
    //         $stmtCheck->bind_param("s", $correo_sesion);
    //         $stmtCheck->execute();
    //         $stmtCheck->store_result();

    //         if ($stmtCheck->num_rows > 0) {
    //             // Cliente ya existe
    //             $stmtCheck->close();
    //             $conn->rollback();
    //             return ["success" => false, "msg" => "RECORD_ALREADY_EXISTED"];
    //         }

    //         $stmtCheck->close();

    //         // Insertar nuevo cliente
    //         $stmtInsertCliente = $conn->prepare("INSERT INTO clientes (correo, password, nombres, cedula, telefono) VALUES (?, ?, ?, ?, ?)");
    //         $stmtInsertCliente->bind_param("sssss", $correo_sesion, $password, $data["datos_facturacion"]["razon_social"], $data["datos_facturacion"]["numero_identificacion"], $data["datos_facturacion"]["telefono"]);
    //         $stmtInsertCliente->execute();
    //         $stmtInsertCliente->close();
    //         $id_usuario = $conn->insert_id;

    //         var_dump($id_usuario);
    //         // 1. Insertar en tabla ordenes
    //         $stmt = $conn->prepare("INSERT INTO ordenes (numero_orden, subtotal, iva, total, id_cliente, envio_domicilio, latitud, longitud, nombre_retiro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmt->bind_param("sssssssss", $data["numero_orden"], $data["subtotal"], $data["iva"], $data["total"], $id_usuario, $data["envio_domicilio"], $data["latitud"], $data["longitud"], $data["nombre_retiro"]);
    //         $stmt->execute();
    //         $id_orden = $conn->insert_id;
    //         $stmt->close();

    //         // 2. Insertar en ordenes_empresas
    //         foreach ($data["ordenes_empresas"] as $ordenEmpresa) {
    //             $id_empresa = $ordenEmpresa["id_empresa"];
    //             $productos_json = json_encode($ordenEmpresa["productos"]);
    //             $total_empresa = $ordenEmpresa["total"];

    //             $stmt2 = $conn->prepare("INSERT INTO ordenes_empresas (id_empresa, productos, total, id_ordenes) VALUES (?, ?, ?, ?)");
    //             $stmt2->bind_param("isdi", $id_empresa, $productos_json, $total_empresa, $id_orden);
    //             $stmt2->execute();
    //             $stmt2->close();
    //         }


    //         if ($data["envio_domicilio"] == 1 || $data["envio_domicilio"] == "1") {
    //             // 3. Insertar en datos_domicilio
    //             $envio = $data["datos_domicilio"];
    //             $stmt3 = $conn->prepare("INSERT INTO datos_domicilio 
    //     (nombres, cedula, telefono, direccion_exacta, punto_referencial, provincia, canton, parroquia, codigo_postal, horario_entrega, observaciones, id_orden) 
    //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //             $stmt3->bind_param(
    //                 "sssssssssssi",
    //                 $envio["nombres"],
    //                 $envio["cedula"],
    //                 $envio["telefono"],
    //                 $envio["direccion_exacta"],
    //                 $envio["punto_referencial"],
    //                 $envio["provincia"],
    //                 $envio["canton"],
    //                 $envio["parroquia"],
    //                 $envio["codigo_postal"],
    //                 $envio["horario_entrega"],
    //                 $envio["observaciones"],
    //                 $id_orden
    //             );
    //             $stmt3->execute();
    //             $stmt3->close();
    //         }

    //         // 4. Insertar en datos_facturacion
    //         $fact = $data["datos_facturacion"];
    //         $stmt4 = $conn->prepare("INSERT INTO datos_facturacion 
    //     (tipo_identificacion, razon_social, numero_identificacion, correo, telefono, direccion, forma_pago, id_orden) 
    //     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmt4->bind_param(
    //             "sssssssi",
    //             $fact["tipo_identificacion"],
    //             $fact["razon_social"],
    //             $fact["numero_identificacion"],
    //             $fact["correo"],
    //             $fact["telefono"],
    //             $fact["direccion"],
    //             $fact["forma_pago"],
    //             $id_orden
    //         );
    //         $stmt4->execute();
    //         $stmt4->close();

    //         $conn->commit();

    //         return ["success" => true, "numero_orden" => $data["numero_orden"]];
    //     } catch (Exception $e) {
    //         $conn->rollback();
    //         error_log("Error al insertar orden: " . $e->getMessage());
    //         return ["success" => false];
    //     }
    // }



    function getOrdenesSeguimiento($numero_orden)
    {
        $ordenes = array();
        $stmt = $this->conn->prepare("SELECT * FROM ordenes WHERE numero_orden = ?");
        $stmt->bind_param("s", $numero_orden);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($orden = $result->fetch_assoc()) {
            $orden_id = $orden['id_orden'];
            $orden['empresas'] = $this->getOrdenesEmpresas($orden_id);
            $orden['facturacion'] = $this->getDatosFacturacion($orden_id);
            $orden['domicilio'] = $this->getDatosDomicilio($orden_id);
            $ordenes[] = $orden;
        }

        return $ordenes;
    }

    public function getReportesInteracciones($id_empresa, $desde = null, $hasta = null, $tipo = null)
    {
        $response = [
            "kpis" => ["total_interacciones" => 0, "sesiones" => 0, "productos" => 0, "eventos" => 0],
            "por_tipo" => [],
            "top_productos" => [],
            "detalle" => []
        ];

        // Verificar si la tabla existe
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS c 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto_interacciones'");
        $stmt->execute();
        $hasTable = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (empty($hasTable) || (int)$hasTable["c"] === 0) {
            return $response;
        }

        // Verificar si existe columna created_at
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS c 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'producto_interacciones' 
              AND COLUMN_NAME = 'created_at'");
        $stmt->execute();
        $hasCreated = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $hasCreatedAt = !empty($hasCreated) && (int)$hasCreated["c"] > 0;

        $filters = " WHERE id_empresa = ? ";
        $filtersAlias = " WHERE i.id_empresa = ? ";
        $params = [$id_empresa];
        $types = "i";
        if ($hasCreatedAt && $desde && $hasta) {
            $filters .= " AND created_at BETWEEN ? AND ? ";
            $filtersAlias .= " AND i.created_at BETWEEN ? AND ? ";
            $params[] = $desde . " 00:00:00";
            $params[] = $hasta . " 23:59:59";
            $types .= "ss";
        }
        if ($tipo && $tipo !== "todos") {
            $filters .= " AND tipo = ? ";
            $filtersAlias .= " AND i.tipo = ? ";
            $params[] = $tipo;
            $types .= "s";
        }

        // KPIs
        $sql = "SELECT 
            COALESCE(SUM(cantidad),0) AS total_interacciones,
            COALESCE(COUNT(DISTINCT session_key),0) AS sesiones,
            COALESCE(COUNT(DISTINCT CONCAT(tipo,'-',id_producto)),0) AS productos,
            COALESCE(COUNT(*),0) AS eventos
            FROM producto_interacciones $filters";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $response["kpis"] = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Por tipo de evento
        $sql = "SELECT tipo_evento, COALESCE(SUM(cantidad),0) AS total
            FROM producto_interacciones $filters
            GROUP BY tipo_evento
            ORDER BY total DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $response["por_tipo"][] = $row;
        }
        $stmt->close();

        // Top productos
        $sql = "SELECT i.tipo, i.id_producto,
            CASE 
                WHEN i.tipo = 'empresa' THEN COALESCE(emp.nombre, CONCAT('Empresa #', i.id_producto))
                WHEN i.tipo = 'evento' THEN COALESCE(ev.titulo, CONCAT('Evento #', i.id_producto))
                WHEN i.tipo = 'vehiculo' THEN CONCAT('Vehículo #', i.id_producto)
                WHEN i.tipo = 'servicio' THEN COALESCE(p.titulo_producto, p.nombre, CONCAT('Servicio #', i.id_producto))
                ELSE COALESCE(p.nombre, p.titulo_producto, CONCAT('Producto #', i.id_producto))
            END AS nombre,
            COALESCE(SUM(i.cantidad),0) AS total
            FROM producto_interacciones i
            LEFT JOIN productos p ON p.id_producto = i.id_producto
            LEFT JOIN eventos ev ON ev.id_evento = i.id_producto
            LEFT JOIN empresas emp ON emp.id_empresa = i.id_producto
            $filtersAlias
            GROUP BY i.tipo, i.id_producto
            ORDER BY total DESC
            LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $response["top_productos"][] = $row;
        }
        $stmt->close();

        // Detalle
        $fechaSelect = $hasCreatedAt ? "DATE_FORMAT(i.created_at, '%Y-%m-%d %H:%i') AS fecha" : "'' AS fecha";
        $sql = "SELECT 
            CASE 
                WHEN i.tipo = 'empresa' THEN COALESCE(emp.nombre, CONCAT('Empresa #', i.id_producto))
                WHEN i.tipo = 'evento' THEN COALESCE(ev.titulo, CONCAT('Evento #', i.id_producto))
                WHEN i.tipo = 'vehiculo' THEN CONCAT('Vehículo #', i.id_producto)
                WHEN i.tipo = 'servicio' THEN COALESCE(p.titulo_producto, p.nombre, CONCAT('Servicio #', i.id_producto))
                ELSE COALESCE(p.nombre, p.titulo_producto, CONCAT('Producto #', i.id_producto))
            END AS producto,
            i.tipo,
            i.tipo_evento,
            $fechaSelect
            FROM producto_interacciones i
            LEFT JOIN productos p ON p.id_producto = i.id_producto
            LEFT JOIN eventos ev ON ev.id_evento = i.id_producto
            LEFT JOIN empresas emp ON emp.id_empresa = i.id_producto
            $filtersAlias
            ORDER BY i.id_interaccion DESC
            LIMIT 200";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $response["detalle"][] = $row;
        }
        $stmt->close();

        return $response;
    }


    function getOrdenesByIdOrden($id_orden)
    {
        $ordenes = array();
        $stmt = $this->conn->prepare("SELECT * FROM ordenes WHERE id_orden = ? and estado = 'A'");
        $stmt->bind_param("s", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($orden = $result->fetch_assoc()) {
            $orden_id = $orden['id_orden'];
            $orden['empresas'] = $this->getOrdenesEmpresas($orden_id);
            $orden['facturacion'] = $this->getDatosFacturacion($orden_id);
            $orden['domicilio'] = $this->getDatosDomicilio($orden_id);
            $ordenes[] = $orden;
        }

        return $ordenes;
    }


    function getOrdenesEmpresas($id_orden)
    {
        $empresas = array();
        $stmt = $this->conn->prepare("SELECT * FROM ordenes_empresas WHERE id_ordenes = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($empresa = $result->fetch_assoc()) {
            $empresa["datos_empresa"] = $this->getEmpresaById($empresa["id_empresa"]);
            $empresa["pagos"] = $this->getOrdenPago($empresa["id_orden"]);
            $empresa["trayecto"] = $this->getTrayectoBy($empresa["id_trayecto"]);
            $empresa["ruta"] = $this->getRutaById($empresa["id_ruta"]);
            $empresa["orden_iso"] = $this->getOrdenIsoByIdEmpresa($empresa["id_orden"]);
            $empresa["guia"] = $this->getObtenerGuiaServientrega($empresa["id_orden"]);
            $empresas[] = $empresa;
        }

        return $empresas;
    }

    function getOrdenEmpresaByIdEmpresa($id_orden)
    {
        $empresas = array();
        $stmt = $this->conn->prepare("SELECT * FROM ordenes_empresas WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($empresa = $result->fetch_assoc()) {
            $empresa["datos_empresa"] = $this->getEmpresaById($empresa["id_empresa"]);
            $empresa["pagos"] = $this->getOrdenPago($empresa["id_orden"]);
            $empresa["trayecto"] = $this->getTrayectoBy($empresa["id_trayecto"]);
            $empresa["ruta"] = $this->getRutaById($empresa["id_ruta"]);
            $empresa["orden_iso"] = $this->getOrdenIsoByIdEmpresa($empresa["id_orden"]);
            $empresa["guia"] = $this->getObtenerGuiaServientrega($empresa["id_orden"]);
            $empresa["domicilio"] = $this->getDatosDomicilio($empresa["id_ordenes"]);
            $empresa["facturacion"] = $this->getDatosFacturacion($empresa["id_ordenes"]);
            $empresas[] = $empresa;
        }

        return $empresas;
    }

    function getObtenerGuiaServientrega($id_orden_empresa)
    {
        $guias = array();
        $stmt = $this->conn->prepare("SELECT * FROM ordenes_servientrega WHERE id_orden_empresa = ? and estado = 'A'");
        $stmt->bind_param("i", $id_orden_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($guia = $result->fetch_assoc()) {
            $guias[] = $guia;
        }

        return $guias;
    }

    function getDatosFacturacion($id_orden)
    {
        $stmt = $this->conn->prepare("SELECT * FROM datos_facturacion WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Solo 1 registro
    }

    function getUltimaFacturacionCliente($id_cliente)
    {
        $stmt = $this->conn->prepare(
            "SELECT df.tipo_identificacion, df.razon_social, df.numero_identificacion,
                    df.correo, df.telefono, df.direccion, df.forma_pago
             FROM datos_facturacion df
             INNER JOIN ordenes o ON o.id_orden = df.id_orden
             WHERE o.id_cliente = ?
             ORDER BY df.id_datos_facturacion DESC
             LIMIT 1"
        );
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function getDatosDomicilio($id_orden)
    {
        $stmt = $this->conn->prepare("SELECT * FROM datos_domicilio WHERE id_orden = ? and estado = 'A'");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Solo 1 registro
    }

    function getRutaByidRuta($id_ruta)
    {
        $stmt = $this->conn->prepare("SELECT * FROM ruta_grupo WHERE id_ruta = ? and estado = 'A'");
        $stmt->bind_param("i", $id_ruta);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Solo 1 registro
    }

    /*public function validarMembresiaProductos($id_empresa)
    {

        // 1. Obtener membresía activa de la empresa
        $stmt = $this->conn->prepare("
            SELECT me.limite_articulos, me.fecha_fin
            FROM membresias_empresas me
            INNER JOIN membresias m ON me.id_membresia = m.id_membresia
            WHERE me.id_empresa = ? AND me.estado = 'A' AND m.estado = 'A'
            AND CURDATE() <= me.fecha_fin
            ORDER BY me.fecha_fin DESC LIMIT 1
        ");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $membresia = $result->fetch_assoc();

        if (!$membresia) {
            return ['error' => true, 'msg' => 'No tiene membresía activa'];
        }

        // 2. Contar productos actuales de la empresa
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total FROM productos WHERE id_empresa = ? AND estado = 'A'
        ");
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $productos_actuales = $row['total'];
        $limite = $membresia['limite_articulos'];

        if ($limite !== 'ilimitado' && $productos_actuales >= (int)$limite) {
            return ['error' => true, 'msg' => "Has alcanzado el límite de $limite productos/servicios permitidos."];
        } else {
            return ['error' => false, 'msg' => 'Membresía válida y límite no superado'];
        }
    }*/

    private function normalizarNombreMembresia($nombre)
    {
        $nombre = trim((string)$nombre);
        if ($nombre === '') {
            return '';
        }

        $lower = function_exists('mb_strtolower')
            ? mb_strtolower($nombre, 'UTF-8')
            : strtolower($nombre);

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $lower);
        $base = $ascii !== false ? $ascii : $lower;
        $base = preg_replace('/[^a-z0-9]+/', '', $base);

        return trim((string)$base);
    }

    private function getCategoriasPermitidasBasicMuv()
    {
        return [
            'BasicMuv: 1 Lavadora Básica',
            'BasicMuv: 1 Vulcanizadora Básica'
        ];
    }

    private function getMembresiaActivaPorContexto($id_empresa, $tipo)
    {
        if ($tipo === "sucursal") {
            $sql = "
                SELECT me.id_empresa, me.limite_articulos, me.fecha_fin, m.nombre
                FROM membresias_empresas me
                INNER JOIN membresias m ON m.id_membresia = me.id_membresia
                INNER JOIN sucursales s ON s.id_empresa = me.id_empresa
                WHERE s.id_sucursal = ?
                  AND me.estado = 'A'
                  AND m.estado = 'A'
                  AND NOW() <= me.fecha_fin
                ORDER BY me.fecha_fin DESC
                LIMIT 1
            ";
        } else {
            $sql = "
                SELECT me.id_empresa, me.limite_articulos, me.fecha_fin, m.nombre
                FROM membresias_empresas me
                INNER JOIN membresias m ON m.id_membresia = me.id_membresia
                WHERE me.id_empresa = ?
                  AND me.estado = 'A'
                  AND m.estado = 'A'
                  AND NOW() <= me.fecha_fin
                ORDER BY me.fecha_fin DESC
                LIMIT 1
            ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $membresia = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $membresia ?: null;
    }

    private function contarProductosPorTipoCategoria($id_empresa, $tipoCategoria, $idExcluir = 0)
    {
        $response = 0;
        $sql = "SELECT id_producto, categoria, tipo_producto FROM productos WHERE id_empresa = ? AND estado = 'A'";

        if ($idExcluir > 0) {
            $sql .= " AND id_producto <> ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $id_empresa, $idExcluir);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_empresa);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $tipoProducto = strtolower(trim((string)($row['tipo_producto'] ?? '')));
            if ($tipoProducto === strtolower($tipoCategoria)) {
                $response++;
                continue;
            }

            $categorias = $this->getCategoriaByArray($row['categoria'] ?? []);
            if (!empty($categorias) && strtolower((string)$categorias[0]['tipo']) === strtolower($tipoCategoria)) {
                $response++;
            }
        }

        $stmt->close();
        return $response;
    }

    private function contarRegistrosPorModulo($id_empresa, $modulo, $idExcluir = 0)
    {
        $modulo = strtolower(trim((string)$modulo));

        if ($modulo === 'producto') {
            return $this->contarProductosPorTipoCategoria($id_empresa, 'producto', $idExcluir);
        }

        if ($modulo === 'servicio') {
            return $this->contarProductosPorTipoCategoria($id_empresa, 'servicio', $idExcluir);
        }

        $map = [
            'vehiculo' => ['tabla' => 'vehiculos', 'pk' => 'id_vehiculo'],
            'evento'   => ['tabla' => 'eventos', 'pk' => 'id_evento'],
            'empleo'   => ['tabla' => 'empleos', 'pk' => 'id_empleo'],
        ];

        if (!isset($map[$modulo])) {
            return 0;
        }

        $tabla = $map[$modulo]['tabla'];
        $pk = $map[$modulo]['pk'];
        $sql = "SELECT COUNT(*) AS total FROM {$tabla} WHERE id_empresa = ? AND estado = 'A'";

        if ($idExcluir > 0) {
            $sql .= " AND {$pk} <> ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $id_empresa, $idExcluir);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_empresa);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }

    private function categoriaPermitidaParaBasicMuv($categoriaId)
    {
        $ids = $this->normalizeIds($categoriaId);
        if (empty($ids)) {
            return false;
        }

        $categorias = $this->getCategoriaByArray($ids);
        if (empty($categorias)) {
            return false;
        }

        $permitidas = $this->getCategoriasPermitidasBasicMuv();
        foreach ($categorias as $categoria) {
            if (
                strtolower((string)($categoria['tipo'] ?? '')) !== 'servicio' ||
                !in_array((string)($categoria['nombre'] ?? ''), $permitidas, true)
            ) {
                return false;
            }
        }

        return true;
    }

    public function validarMembresiaProductos($id_empresa, $tipo, $modulo = 'producto', $id_registro = 0, $categoria_id = null, $incluye_galeria = false)
    {
        $membresia = $this->getMembresiaActivaPorContexto((int)$id_empresa, (string)$tipo);
        if (!$membresia) {
            return ['error' => true, 'msg' => 'No tiene membresía activa'];
        }

        $empresaIdReal = (int)$membresia['id_empresa'];
        $nombrePlan = (string)($membresia['nombre'] ?? '');
        $planNormalizado = $this->normalizarNombreMembresia($nombrePlan);
        $modulo = strtolower(trim((string)$modulo));
        $id_registro = (int)$id_registro;
        $incluye_galeria = filter_var($incluye_galeria, FILTER_VALIDATE_BOOLEAN);

        if ($planNormalizado === 'fulmuv') {
            return ['error' => false, 'msg' => 'Membresía FULMUV válida'];
        }

        if ($planNormalizado === 'onemuv') {
            if ($modulo === 'empleo') {
                return ['error' => true, 'msg' => 'Tu plan OneMuv no permite registrar empleos.'];
            }

            if ($modulo === 'galeria' || ($modulo === 'evento' && $incluye_galeria)) {
                return ['error' => true, 'msg' => 'Tu plan ONEMUV no permite agregar más de 1 evento.'];
            }

            $permitidos = ['producto', 'servicio', 'vehiculo', 'evento'];
            if (!in_array($modulo, $permitidos, true)) {
                return ['error' => true, 'msg' => 'Tu plan OneMuv no permite registrar este módulo.'];
            }

            $total = $this->contarRegistrosPorModulo($empresaIdReal, $modulo, $id_registro);
            if ($total >= 1) {
                $labels = [
                    'producto' => 'un producto',
                    'servicio' => 'un servicio',
                    'vehiculo' => 'un vehículo',
                    'evento' => 'un evento',
                ];
                $label = $labels[$modulo] ?? 'un registro';
                return ['error' => true, 'msg' => "Tu plan OneMuv solo permite registrar {$label}."];
            }

            return ['error' => false, 'msg' => 'Membresía OneMuv válida'];
        }

        if ($planNormalizado === 'basicmuv') {
            if ($modulo !== 'servicio') {
                return [
                    'error' => true,
                    'msg' => 'Tu plan BasicMuv solo permite registrar servicios en las categorías BasicMuv habilitadas.'
                ];
            }

            if (!$this->categoriaPermitidaParaBasicMuv($categoria_id)) {
                $categoriasTexto = implode(' y ', $this->getCategoriasPermitidasBasicMuv());
                return [
                    'error' => true,
                    'msg' => "Tu plan BasicMuv solo permite servicios en las categorías {$categoriasTexto}."
                ];
            }

            return ['error' => false, 'msg' => 'Membresía BasicMuv válida'];
        }

        $limiteRaw = trim((string)$membresia['limite_articulos']);
        if (strcasecmp($limiteRaw, 'ilimitado') === 0) {
            return ['error' => false, 'msg' => 'Membresía válida (ilimitada)'];
        }

        $limite = (int)$limiteRaw;
        $total = $this->contarRegistrosPorModulo($empresaIdReal, $modulo, $id_registro);
        if ($limite > 0 && $total >= $limite) {
            return ['error' => true, 'msg' => "Has alcanzado el límite de {$limite} registros permitidos para {$modulo}."];
        }

        return ['error' => false, 'msg' => 'Membresía válida y límite no superado'];
    }


    public function init_reference($id_membresia, $id_empresa, $id_usuario, $valor)
    {
        //$cliente = $this->getSocioById($id_socio);
        //$membresia = $this->getMembershipById($id_membresia);

        $empresa = $this->getEmpresaById($id_empresa);

        // datos para enviar en al api
        $amount = number_format((float) $valor, 2, '.', '');
        $description =  $empresa["nombre"];

        $dev_reference =  "Pago de Nuvei Membresía";
        $email =  $empresa["correo"];


        $data = array(
            'locale' => "es",
            'order' =>  array(
                "amount" => $amount,
                "description" => $description,
                "tax_percentage" => 0,
                "vat" => 0,
                "dev_reference" => $dev_reference,
                "installments_type" => 0
            ),
            'user' =>  array(
                "id" => $id_usuario,
                "email" => $email
            ),
        );

        $date = new DateTime();
        $unix_timestamp = $date->getTimestamp();
        $uniq_token_string = server_app_key . $unix_timestamp;
        $uniq_token_hash = hash('sha256', $uniq_token_string);
        $auth_token = base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

        $ch = curl_init(paymentezURL . "transaction/init_reference/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Auth-Token: ' . $auth_token
        ]);


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    private function ensureWalletColumnsPagosRecurrentes()
    {
        $requiredColumns = [
            "ultimos_digitos" => "ALTER TABLE pagos_recurrentes ADD COLUMN ultimos_digitos VARCHAR(10) NULL DEFAULT NULL AFTER transaction_reference",
            "marca" => "ALTER TABLE pagos_recurrentes ADD COLUMN marca VARCHAR(50) NULL DEFAULT NULL AFTER ultimos_digitos",
            "exp_year" => "ALTER TABLE pagos_recurrentes ADD COLUMN exp_year VARCHAR(10) NULL DEFAULT NULL AFTER marca",
            "exp_month" => "ALTER TABLE pagos_recurrentes ADD COLUMN exp_month VARCHAR(10) NULL DEFAULT NULL AFTER exp_year",
            "es_default" => "ALTER TABLE pagos_recurrentes ADD COLUMN es_default CHAR(1) NOT NULL DEFAULT 'N' AFTER exp_month",
            "gateway_uid" => "ALTER TABLE pagos_recurrentes ADD COLUMN gateway_uid VARCHAR(120) NULL DEFAULT NULL AFTER es_default",
            "holder_name" => "ALTER TABLE pagos_recurrentes ADD COLUMN holder_name VARCHAR(180) NULL DEFAULT NULL AFTER gateway_uid"
        ];

        foreach ($requiredColumns as $column => $sql) {
            $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
            $exists = $this->conn->query("SHOW COLUMNS FROM pagos_recurrentes LIKE '{$safeColumn}'");
            if ($exists && $exists->num_rows > 0) {
                continue;
            }
            $this->conn->query($sql);
        }
    }

    private function getEmpresaGatewayUserIds($id_empresa)
    {
        $empresa = $this->getEmpresaById((int)$id_empresa);
        $ids = [];

        $this->ensureWalletColumnsPagosRecurrentes();

        $stmt = $this->conn->prepare("SELECT DISTINCT gateway_uid
                                      FROM pagos_recurrentes
                                      WHERE id_empresa = ? AND gateway_uid IS NOT NULL AND gateway_uid <> ''");
        if ($stmt) {
            $stmt->bind_param("s", $id_empresa);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $gatewayUid = trim((string)($row['gateway_uid'] ?? ''));
                if ($gatewayUid !== '') {
                    $ids[] = $gatewayUid;
                }
            }
            $stmt->close();
        }

        if (is_array($empresa)) {
            $correo = trim(strtolower((string)($empresa["correo"] ?? '')));
            if ($correo !== '') {
                $ids[] = $correo;
            }
        }

        $ids[] = (string)$id_empresa;

        return array_values(array_unique(array_filter($ids, function ($value) {
            return trim((string)$value) !== '';
        })));
    }

    private function getPrimaryGatewayUserId($id_empresa)
    {
        $ids = $this->getEmpresaGatewayUserIds($id_empresa);
        return $ids[0] ?? (string)$id_empresa;
    }

    private function paymentezAuthToken()
    {
        $date = new DateTime();
        $unix_timestamp = $date->getTimestamp();
        $uniq_token_string = server_app_key . $unix_timestamp;
        $uniq_token_hash = hash('sha256', $uniq_token_string);
        return base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);
    }

    private function paymentezRequest($method, $endpoint, $payload = null, $baseUrl = null)
    {
        $base = rtrim((string)($baseUrl ?: paymentezURL), '/') . '/';
        $ch = curl_init($base . ltrim($endpoint, '/'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Auth-Token: ' . $this->paymentezAuthToken()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $upperMethod = strtoupper((string)$method);
        if ($upperMethod !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $upperMethod);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        }

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $result === null) {
            return [
                "error" => true,
                "msg" => $error !== '' ? $error : 'No se recibió respuesta de Paymentez.'
            ];
        }

        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "error" => true,
                "msg" => 'La respuesta de Paymentez no se pudo interpretar.',
                "raw" => $result,
                "http_code" => $httpCode
            ];
        }

        if ($httpCode >= 400) {
            $decoded["error"] = $decoded["error"] ?? [
                "type" => "HTTP_ERROR",
                "description" => "HTTP " . $httpCode
            ];
        }

        $decoded["http_code"] = $httpCode;
        return $decoded;
    }

    private function isWalletTokenExpired($expMonth, $expYear)
    {
        if ($expMonth === null || $expYear === null || $expMonth === '' || $expYear === '') {
            return false;
        }

        $month = (int)$expMonth;
        $year = (int)$expYear;
        if ($year < 100) {
            $year += 2000;
        }
        if ($month < 1 || $month > 12 || $year < 2000) {
            return false;
        }

        $expiresAt = DateTime::createFromFormat('Y-n-j H:i:s', $year . '-' . $month . '-1 00:00:00');
        if (!$expiresAt) {
            return false;
        }
        $expiresAt->modify('+1 month');

        return new DateTime() >= $expiresAt;
    }

    private function normalizeWalletCardStatus($rawStatus)
    {
        $status = trim(strtolower((string)$rawStatus));

        if ($status === '' || $status === 'local') {
            return $status;
        }

        if (strpos($status, 'valid') !== false) {
            return 'valid';
        }

        if (
            strpos($status, 'review') !== false ||
            strpos($status, 'pending') !== false ||
            strpos($status, 'process') !== false
        ) {
            return 'review';
        }

        if (
            strpos($status, 'reject') !== false ||
            strpos($status, 'invalid') !== false ||
            strpos($status, 'deny') !== false
        ) {
            return 'rejected';
        }

        return $status;
    }

    private function isWalletCardChargeable($card)
    {
        $status = $this->normalizeWalletCardStatus($card['status'] ?? '');
        $isExpired = !empty($card['is_expired']);

        return in_array($status, ['valid', 'local'], true) && !$isExpired;
    }

    private function getPagoRecurrenteLocalByToken($id_empresa, $token)
    {
        $stmt = $this->conn->prepare("SELECT token, id_usuario, es_default, created_at
                                      FROM pagos_recurrentes
                                      WHERE id_empresa = ? AND token = ? AND estado = 'A'
                                      ORDER BY es_default DESC, created_at DESC
                                      LIMIT 1");
        $stmt->bind_param("ss", $id_empresa, $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function getChargeableWalletTarjetasByEmpresa($id_empresa)
    {
        $cards = $this->getWalletTarjetasByEmpresa($id_empresa);
        $chargeable = [];

        foreach ($cards as $card) {
            if ($this->isWalletCardChargeable($card)) {
                $chargeable[] = $card;
            }
        }

        return array_values($chargeable);
    }

    public function getPreferredChargeableTokenByEmpresa($id_empresa, $preferredToken = null)
    {
        $cards = $this->getChargeableWalletTarjetasByEmpresa($id_empresa);
        if (empty($cards)) {
            return null;
        }

        $selectedCard = null;
        $preferredToken = trim((string)$preferredToken);
        if ($preferredToken !== '') {
            foreach ($cards as $card) {
                if (trim((string)($card['token'] ?? '')) === $preferredToken) {
                    $selectedCard = $card;
                    break;
                }
            }

            if ($selectedCard === null) {
                return [
                    "error" => true,
                    "msg" => "La tarjeta seleccionada no esta disponible para cobro."
                ];
            }
        }

        if ($selectedCard === null) {
            $selectedCard = $cards[0];
        }

        $token = trim((string)($selectedCard['token'] ?? ''));
        if ($token === '') {
            return null;
        }

        $localRow = $this->getPagoRecurrenteLocalByToken($id_empresa, $token);

        return [
            "error" => false,
            "token" => $token,
            "id_usuario" => (int)($localRow['id_usuario'] ?? 0),
            "es_default" => $localRow['es_default'] ?? ($selectedCard['es_default'] ?? 'N'),
            "status" => $selectedCard['status'] ?? 'valid'
        ];
    }

    // public function obtenerTarjetas($id_empresa)
    // {
    //     $date = new DateTime();
    //     $unix_timestamp = $date->getTimestamp();
    //     $uniq_token_string = server_app_key . $unix_timestamp;
    //     $uniq_token_hash = hash('sha256', $uniq_token_string);
    //     $auth_token = base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

    //     $ch = curl_init(paymentezURL . "card/list?uid=" . $id_empresa);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //         'Content-Type: application/json',
    //         'Auth-Token: ' . $auth_token
    //     ]);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    //     $result = curl_exec($ch);
    //     curl_close($ch);
    //     return json_decode($result, true);
    // }


    public function obtenerTarjetas($id_empresa)
    {
        $allCards = [];
        $debugWallet = isset($_GET['wallet_debug']) && (string)$_GET['wallet_debug'] === '1';
        $stgBaseUrl = 'https://ccapi.paymentez.com/v2/';
        $defaultBaseUrl = rtrim((string)paymentezURL, '/') . '/';
        $debugPayload = [];

        foreach ($this->getEmpresaGatewayUserIds($id_empresa) as $gatewayUserId) {
            $result = $this->paymentezRequest('GET', "card/list?uid=" . urlencode($gatewayUserId), null, $stgBaseUrl);

            if (
                (empty($result['cards']) || !is_array($result['cards'])) &&
                rtrim($stgBaseUrl, '/') !== rtrim($defaultBaseUrl, '/')
            ) {
                $fallbackResult = $this->paymentezRequest('GET', "card/list?uid=" . urlencode($gatewayUserId));
                if (!empty($fallbackResult['cards']) && is_array($fallbackResult['cards'])) {
                    $result = $fallbackResult;
                }
                if ($debugWallet) {
                    $debugPayload[] = [
                        'gateway_uid' => $gatewayUserId,
                        'stg_result' => $result,
                        'fallback_result' => $fallbackResult
                    ];
                }
            } elseif ($debugWallet) {
                $debugPayload[] = [
                    'gateway_uid' => $gatewayUserId,
                    'stg_result' => $result
                ];
            }

            if (!empty($result['cards']) && is_array($result['cards'])) {
                foreach ($result['cards'] as $card) {
                    $token = trim((string)($card['token'] ?? ''));
                    if ($token === '') {
                        continue;
                    }
                    $card['gateway_uid'] = $gatewayUserId;
                    $allCards[$token] = $card;
                }
            }
        }

        if ($debugWallet) {
            header('Content-Type: text/plain; charset=utf-8');
            var_dump([
                'id_empresa' => $id_empresa,
                'gateway_user_ids' => $this->getEmpresaGatewayUserIds($id_empresa),
                'list_debug' => $debugPayload,
                'mapped_cards' => array_values($allCards)
            ]);
            exit;
        }

        return [
            "cards" => array_values($allCards),
            "result_size" => count($allCards)
        ];
    }

    public function addWalletTarjetaPaymentez(
        $id_empresa,
        $id_usuario,
        $gateway_uid,
        $email,
        $holder_name,
        $number,
        $expiry_month,
        $expiry_year,
        $cvc,
        $type = '',
        $phone = '',
        $ip_address = '',
        $fiscal_number = '',
        $session_id = ''
    ) {
        $gatewayUid = trim((string)$gateway_uid);
        $email = trim(strtolower((string)$email));
        $holderName = trim((string)$holder_name);
        $number = preg_replace('/\D/', '', (string)$number);
        $expiryMonth = (int)$expiry_month;
        $expiryYear = (int)$expiry_year;
        $cvc = preg_replace('/\D/', '', (string)$cvc);
        $type = trim(strtolower((string)$type));

        if ($gatewayUid === '') {
            $gatewayUid = $this->getPrimaryGatewayUserId($id_empresa);
        }

        if ($gatewayUid === '' || $email === '' || $holderName === '' || $number === '' || $expiryMonth <= 0 || $expiryYear <= 0 || $cvc === '') {
            return [
                "error" => true,
                "msg" => "Faltan datos obligatorios para card/add."
            ];
        }

        $payload = [
            "user" => [
                "id" => $gatewayUid,
                "email" => $email
            ],
            "card" => [
                "number" => $number,
                "holder_name" => $holderName,
                "expiry_month" => $expiryMonth,
                "expiry_year" => $expiryYear,
                "cvc" => $cvc
            ]
        ];

        if ($type !== '') {
            $payload["card"]["type"] = $type;
        }
        if (trim((string)$phone) !== '') {
            $payload["user"]["phone"] = trim((string)$phone);
        }
        if (trim((string)$ip_address) !== '') {
            $payload["user"]["ip_address"] = trim((string)$ip_address);
        }
        if (trim((string)$fiscal_number) !== '') {
            $payload["user"]["fiscal_number"] = trim((string)$fiscal_number);
        }
        if (trim((string)$session_id) !== '') {
            $payload["session_id"] = trim((string)$session_id);
        }

        $stgBaseUrl = 'https://ccapi.paymentez.com/v2/';
        $defaultBaseUrl = rtrim((string)paymentezURL, '/') . '/';
        $responses = [
            $this->paymentezRequest('POST', 'card/add', $payload, $stgBaseUrl)
        ];

        if (rtrim($stgBaseUrl, '/') !== rtrim($defaultBaseUrl, '/')) {
            $responses[] = $this->paymentezRequest('POST', 'card/add', $payload);
        }

        $gatewayResponse = null;
        foreach ($responses as $candidate) {
            if (!empty($candidate['card']) || empty($candidate['error'])) {
                $gatewayResponse = $candidate;
                break;
            }
            $gatewayResponse = $candidate;
        }

        if (!is_array($gatewayResponse) || empty($gatewayResponse['card'])) {
            return [
                "error" => true,
                "msg" => $gatewayResponse['error']['description'] ?? $gatewayResponse['error']['type'] ?? $gatewayResponse['msg'] ?? 'Paymentez no devolvio una tarjeta valida.',
                "gateway_response" => $gatewayResponse
            ];
        }

        $card = $gatewayResponse['card'];
        $token = trim((string)($card['token'] ?? ''));
        if ($token === '') {
            return [
                "error" => true,
                "msg" => "Paymentez no devolvio card.token.",
                "card" => $card,
                "gateway_response" => $gatewayResponse
            ];
        }

        $saveLocal = $this->webstoreCreateRecurrente(
            $token,
            $card['transaction_reference'] ?? '',
            $id_usuario,
            $id_empresa,
            $card['number'] ?? substr($number, -4),
            $card['type'] ?? $type,
            $card['expiry_year'] ?? $expiryYear,
            $card['expiry_month'] ?? $expiryMonth,
            null,
            $gatewayUid,
            $card['holder_name'] ?? $holderName
        );

        if ($saveLocal !== RECORD_CREATED_SUCCESSFULLY) {
            return [
                "error" => true,
                "msg" => "La tarjeta fue agregada en Paymentez, pero no se pudo guardar en pagos_recurrentes.",
                "card" => $card,
                "gateway_response" => $gatewayResponse
            ];
        }

        return [
            "error" => false,
            "msg" => "Tarjeta agregada correctamente.",
            "card" => $card,
            "saved_local" => true
        ];
    }
    public function createPagoTransaccion($id_usuario, $id_membresia, $pago_valor, $id_empresa, $tipo, $transaction_id, $authorization_code, $recurrente, $payment_date, $id_membresia_empresa, $promo_resumen = null)
    {

        // Preparar el insert
        $stmt = $this->conn->prepare("
        INSERT INTO pagos_transaccion(id_transaccion, codigo_autorizacion, pago_valor, id_usuario, id_membresia, id_empresa, tipo, recurrente, fecha_transaccion, id_membresia_empresa) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("ssssssssss", $transaction_id, $authorization_code, $pago_valor, $id_usuario, $id_membresia, $id_empresa, $tipo, $recurrente, $payment_date, $id_membresia_empresa);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function createAgente($nombre, $correo, $codigo)
    {
        if (!$this->isAgenteExists($nombre)) {
            $stmt = $this->conn->prepare("INSERT INTO agentes (nombre, correo, codigo) VALUES(?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $correo, $codigo);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function isAgenteExists($nombre)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM agentes 
        WHERE nombre=? AND estado = 'A'");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateAgente($id_agente, $nombre, $correo, $codigo)
    {
        $stmt = $this->conn->prepare("UPDATE agentes SET nombre = ?, correo = ?, codigo = ? WHERE id_agente = ?");
        $stmt->bind_param("ssss", $nombre, $correo, $codigo, $id_agente);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function getAgentes()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM agentes
        WHERE estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getAgenteById($id_agente)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM agentes
        WHERE estado = 'A' AND id_agente = ?");
        $stmt->bind_param("s", $id_agente);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function reembolsar($id_transaccion)
    {
        $info = $this->obtenerInfoTransaccionNuvei($id_transaccion);
        if (!empty($info['error'])) {
            return [
                "status" => "failure",
                "detail" => $info['msg'] ?? 'No se pudo consultar la transaccion en Nuvei.'
            ];
        }

        $trx = $info['transaction'] ?? [];
        $statusActual = strtolower((string)($trx['status'] ?? ''));
        $statusDetailActual = (string)($trx['status_detail'] ?? '');
        $mensajeActual = (string)($trx['message'] ?? '');

        $refundables = ['success', 'approved'];
        if (!in_array($statusActual, $refundables, true)) {
            return [
                "status" => "failure",
                "detail" => "Invalid Status",
                "transaction" => [
                    "id" => $id_transaccion,
                    "status" => $statusActual,
                    "status_detail" => $statusDetailActual,
                    "message" => $mensajeActual
                ]
            ];
        }

        $data = array(
            'transaction' =>  array(
                "id" => $id_transaccion,
            ),
        );

        $date = new DateTime();
        $unix_timestamp = $date->getTimestamp();
        $uniq_token_string = server_app_key . $unix_timestamp;
        $uniq_token_hash = hash('sha256', $uniq_token_string);
        $auth_token = base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

        $ch = curl_init(paymentezURL . "transaction/refund/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Auth-Token: ' . $auth_token
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);
        $refund = json_decode($result, true);

        // var_dump($refund);
        if (array_key_exists("status", $refund)) {

            if ($refund['status'] == 'success') {

                /*$detalleTransaccion = $this->transaccionDetalles($id_transaccion);
                $placa = $detalleTransaccion["transaction"]["dev_reference"];
                $detalleMembresia = $this->detalleMembresia($placa);

                if ($this->deleteRecord("subscripciones", "id_subscripcion", $detalleMembresia["id_subscripcion"])) {
                    // edit

                    if ($this->deleteRecord("ventas", "id_venta", $detalleMembresia["id_venta"])) {
                        return "success_update_all"; //se hizo el reembolso y se actualizó la suscripcion y la venta
                    }
                    return "success_update"; //se hizo el reembolsono y se actualizó la suscripcion pero no la venta
                    // edit
                } else {
                    return "success"; //se hizo el reembolsono se pero no se actualizó la suscripcion
                }*/
                return "success";
            } else {
                return $refund; //no se pudo hacer el reembolso
            }
        }
        return "error";
    }

    public function obtenerInfoTransaccionNuvei($id_transaccion)
    {
        $date = new DateTime();
        $unix_timestamp = $date->getTimestamp();
        $uniq_token_string = server_app_key . $unix_timestamp;
        $uniq_token_hash = hash('sha256', $uniq_token_string);
        $auth_token = base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

        $ch = curl_init(paymentezURL . "transaction/" . rawurlencode((string)$id_transaccion) . "/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Auth-Token: ' . $auth_token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            return [
                "error" => true,
                "msg" => "No se pudo consultar Nuvei: " . ($curlError ?: 'Error desconocido')
            ];
        }

        $decoded = json_decode($result, true);
        if (!is_array($decoded)) {
            return [
                "error" => true,
                "msg" => "Nuvei devolvio una respuesta invalida. HTTP {$httpCode}"
            ];
        }

        if ($httpCode >= 400) {
            $msg = $decoded['error']['type'] ?? $decoded['error']['description'] ?? $decoded['detail'] ?? 'Error consultando la transaccion';
            return [
                "error" => true,
                "msg" => $msg,
                "raw" => $decoded
            ];
        }

        return $decoded;
    }

    public function getPagosRefundAdmin()
    {
        $response = [];
        $sql = "SELECT 
                    pt.id_pagos_transaccion,
                    pt.id_transaccion,
                    pt.codigo_autorizacion,
                    pt.pago_valor,
                    pt.fecha_transaccion,
                    pt.estado AS estado_pago,
                    pt.id_empresa,
                    pt.id_membresia,
                    pt.id_membresia_empresa,
                    e.nombre AS empresa,
                    e.correo,
                    m.nombre AS membresia,
                    me.estado AS estado_membresia,
                    me.valor_membresia,
                    f.id_factura,
                    f.id_factura_contifico,
                    f.numero_factura,
                    f.estado AS estado_factura
                FROM pagos_transaccion pt
                INNER JOIN empresas e ON e.id_empresa = pt.id_empresa
                LEFT JOIN membresias m ON m.id_membresia = pt.id_membresia
                LEFT JOIN membresias_empresas me ON me.id_membresia_empresa = pt.id_membresia_empresa
                LEFT JOIN facturas f 
                    ON f.id_factura = (
                        SELECT fx.id_factura
                        FROM facturas fx
                        WHERE fx.id_cliente = pt.id_empresa
                          AND fx.descripcion = 'membresia'
                          AND fx.tipo = 'E'
                          AND fx.estado = 'A'
                        ORDER BY fx.id_factura DESC
                        LIMIT 1
                    )
                WHERE pt.tipo = 'empresa'
                ORDER BY pt.fecha_transaccion DESC, pt.id_pagos_transaccion DESC";

        $result = $this->conn->query($sql);
        if (!$result) {
            return RECORD_DOES_NOT_EXIST;
        }

        while ($row = $result->fetch_assoc()) {
            $row['valor_pagado'] = (float)($row['pago_valor'] ?? 0);
            $row['valor_reembolso'] = (float)($row['pago_valor'] ?? 0);
            $response[] = $row;
        }

        return $response;
    }

    private function getPagoRefundDetalle($idPago)
    {
        $sql = "SELECT 
                    pt.*,
                    e.nombre AS empresa_nombre,
                    e.correo AS empresa_correo,
                    m.nombre AS membresia_nombre,
                    me.estado AS estado_membresia,
                    me.valor_membresia,
                    f.id_factura,
                    f.id_factura_contifico,
                    f.numero_factura,
                    f.estado AS estado_factura
                FROM pagos_transaccion pt
                INNER JOIN empresas e ON e.id_empresa = pt.id_empresa
                LEFT JOIN membresias m ON m.id_membresia = pt.id_membresia
                LEFT JOIN membresias_empresas me ON me.id_membresia_empresa = pt.id_membresia_empresa
                LEFT JOIN facturas f 
                    ON f.id_factura = (
                        SELECT fx.id_factura
                        FROM facturas fx
                        WHERE fx.id_cliente = pt.id_empresa
                          AND fx.descripcion = 'membresia'
                          AND fx.tipo = 'E'
                          AND fx.estado = 'A'
                        ORDER BY fx.id_factura DESC
                        LIMIT 1
                    )
                WHERE pt.id_pagos_transaccion = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $idPago);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    private function eliminarFacturaContifico($idFacturaContifico)
    {
        $idFacturaContifico = trim((string)$idFacturaContifico);
        if ($idFacturaContifico === '') {
            return [
                "error" => false,
                "msg" => "No habia factura de Contifico vinculada."
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/documento/' . rawurlencode($idFacturaContifico) . '/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ELPAz3khSjp7kh4Dqnu9kjK7D4R7WEC8bBD2k2yXcrU'
            ),
        ));

        $respuestaCruda = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($respuestaCruda === false) {
            return [
                "error" => true,
                "msg" => "No fue posible conectar con Contifico: " . ($curlError ?: 'Error desconocido')
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                "error" => false,
                "msg" => "Factura eliminada/anulada en Contifico."
            ];
        }

        $decoded = json_decode($respuestaCruda, true);
        $msg = is_array($decoded)
            ? ($decoded['mensaje'] ?? $decoded['message'] ?? $decoded['detail'] ?? json_encode($decoded, JSON_UNESCAPED_UNICODE))
            : substr((string)$respuestaCruda, 0, 250);

        return [
            "error" => true,
            "msg" => "Contifico devolvio HTTP {$httpCode}: {$msg}"
        ];
    }

    private function marcarFacturaEliminadaLocal($idFactura)
    {
        if (!$idFactura) {
            return true;
        }

        $stmt = $this->conn->prepare("UPDATE facturas SET estado = 'E' WHERE id_factura = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idFactura);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function correoReembolsoMembresia(int $idEmpresa, float $valorReembolso, string $membresiaNombre = '')
    {
        $empresa = $this->getEmpresaById($idEmpresa);
        $correo_contenedor = $this->getContenedor();

        if (!$empresa || empty($empresa["correo"])) {
            return false;
        }

        $titular = $empresa["nombre_titular"] ?? $empresa["nombre"] ?? 'Cliente FULMUV';
        $nombreEmpresa = $empresa["nombre"] ?? 'Tu empresa';
        $correoEmpresa = $empresa["correo"];
        $plan = trim($membresiaNombre) !== '' ? $membresiaNombre : 'Membresía FULMUV';
        $monto = number_format((float)$valorReembolso, 2, '.', ',');
        $logo = "https://fulmuv.com/admin/" . ltrim((string)($correo_contenedor["imagen"] ?? ''), "/");

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "Fulmuv");
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Reembolso realizado - FULMUV';
        $mail->AddAddress($correoEmpresa);

        $mail->Body = '
        <!doctype html>
        <html lang="es">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <title>Reembolso realizado</title>
        </head>
        <body style="margin:0;padding:0;background:#edf2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#edf2f7;padding:32px 12px;">
            <tr>
              <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:18px;overflow:hidden;">
                  <tr>
                    <td style="padding:24px 24px 8px;text-align:center;background:linear-gradient(135deg,#0f766e,#1e293b);">
                      <img src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" width="176" alt="FULMUV" style="display:block;margin:0 auto;height:auto;border:0;">
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:28px 24px;">
                      <h2 style="margin:0 0 14px;font-size:24px;color:#0f172a;">Reembolso procesado</h2>
                      <p style="margin:0 0 14px;font-size:15px;line-height:1.65;">Hola <strong>' . htmlspecialchars($titular, ENT_QUOTES, 'UTF-8') . '</strong>, te confirmamos que el reembolso de tu pago fue procesado correctamente.</p>
                      <p style="margin:0 0 14px;font-size:15px;line-height:1.65;">La membresía <strong>' . htmlspecialchars($plan, ENT_QUOTES, 'UTF-8') . '</strong> de la empresa <strong>' . htmlspecialchars($nombreEmpresa, ENT_QUOTES, 'UTF-8') . '</strong> ha sido inactivada como parte de este proceso.</p>
                      <div style="margin:20px 0;padding:16px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:14px;color:#475569;margin-bottom:8px;">Valor reembolsado</div>
                        <div style="font-size:28px;font-weight:800;color:#0f766e;">$' . $monto . '</div>
                      </div>
                      <p style="margin:0;font-size:14px;line-height:1.65;color:#475569;">Si tienes alguna consulta adicional, puedes responder directamente a este correo y nuestro equipo te ayudara.</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </body>
        </html>';

        return $mail->send();
    }

    public function reembolsarPagoAdmin($idPago)
    {
        $detalle = $this->getPagoRefundDetalle((int)$idPago);
        if (!$detalle) {
            return [
                "error" => true,
                "msg" => "No se encontro el pago seleccionado."
            ];
        }

        if (strtoupper((string)$detalle['estado']) === 'E') {
            return [
                "error" => true,
                "msg" => "El pago ya fue reembolsado previamente."
            ];
        }

        $refund = $this->reembolsar($detalle['id_transaccion']);
        if (is_array($refund) && array_key_exists("status", $refund)) {
            $trx = $refund['transaction'] ?? [];
            $extra = '';
            if (!empty($trx)) {
                $extra = " Estado actual: " . ($trx['status'] ?? 'N/D');
                if (($trx['status_detail'] ?? '') !== '') {
                    $extra .= " | status_detail: " . $trx['status_detail'];
                }
                if (($trx['message'] ?? '') !== '') {
                    $extra .= " | detalle: " . $trx['message'];
                }
            }
            return [
                "error" => true,
                "msg" => "Nuvei rechazo el reembolso." . $extra,
                "nuvei" => $refund
            ];
        }
        if ($refund === 'error') {
            return [
                "error" => true,
                "msg" => "No se pudieron obtener los detalles del reembolso en Nuvei."
            ];
        }
        if ($refund !== 'success') {
            return [
                "error" => true,
                "msg" => "No fue posible completar el reembolso."
            ];
        }

        $warnings = [];

        try {
            $this->conn->begin_transaction();

            $stmtPago = $this->conn->prepare("UPDATE pagos_transaccion SET estado = 'E' WHERE id_pagos_transaccion = ?");
            if (!$stmtPago) {
                throw new Exception("No se pudo preparar la actualizacion del pago.");
            }
            $stmtPago->bind_param("i", $idPago);
            if (!$stmtPago->execute()) {
                $err = $stmtPago->error;
                $stmtPago->close();
                throw new Exception("No se pudo marcar el pago como reembolsado: " . $err);
            }
            $stmtPago->close();

            if (!empty($detalle['id_membresia_empresa'])) {
                $stmtMem = $this->conn->prepare("UPDATE membresias_empresas SET estado = 'E' WHERE id_membresia_empresa = ?");
                if (!$stmtMem) {
                    throw new Exception("No se pudo preparar la actualizacion de la membresia.");
                }
                $idMembresiaEmpresa = (int)$detalle['id_membresia_empresa'];
                $stmtMem->bind_param("i", $idMembresiaEmpresa);
                if (!$stmtMem->execute()) {
                    $err = $stmtMem->error;
                    $stmtMem->close();
                    throw new Exception("No se pudo inactivar la membresia: " . $err);
                }
                $stmtMem->close();
            }

            if (!empty($detalle['id_factura'])) {
                if (!$this->marcarFacturaEliminadaLocal((int)$detalle['id_factura'])) {
                    throw new Exception("No se pudo inactivar la factura local.");
                }
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            $this->conn->rollback();
            return [
                "error" => true,
                "msg" => $e->getMessage()
            ];
        }

        if (!empty($detalle['id_factura_contifico'])) {
            $contifico = $this->eliminarFacturaContifico($detalle['id_factura_contifico']);
            if (!empty($contifico['error'])) {
                $warnings[] = $contifico['msg'];
            }
        }

        $mailSent = $this->correoReembolsoMembresia(
            (int)$detalle['id_empresa'],
            (float)($detalle['pago_valor'] ?? 0),
            (string)($detalle['membresia_nombre'] ?? '')
        );
        if (!$mailSent) {
            $warnings[] = 'No se pudo enviar el correo de notificacion a la empresa.';
        }

        return [
            "error" => false,
            "msg" => "Reembolso realizado correctamente. Se inactivo la membresia y el pago.",
            "warnings" => $warnings,
            "data" => [
                "id_transaccion" => $detalle['id_transaccion'],
                "empresa" => $detalle['empresa_nombre'],
                "membresia" => $detalle['membresia_nombre'],
                "valor_reembolso" => (float)($detalle['pago_valor'] ?? 0)
            ]
        ];
    }

    // registrar pago recurrrente
    // public function webstoreCreateRecurrente($token, $transaction_reference, $id_usuario, $id_empresa)
    // {
    //     $stmt = $this->conn->prepare("INSERT INTO pagos_recurrentes( token, transaction_reference, id_usuario, id_empresa) values(?,?,?,?)");
    //     $stmt->bind_param("ssss",  $token, $transaction_reference, $id_usuario, $id_empresa);
    //     $result = $stmt->execute();
    //     $stmt->close();
    //     if ($result) {
    //         return RECORD_CREATED_SUCCESSFULLY;
    //     } else {
    //         return RECORD_CREATION_FAILED;
    //     }
    // }
    // fin registrar pago recurrrente

    public function webstoreCreateRecurrente(
        $token,
        $transaction_reference,
        $id_usuario,
        $id_empresa,
        $ultimos_digitos = null,
        $marca = null,
        $exp_year = null,
        $exp_month = null, 
        $es_default = null, 
        $gateway_uid = null,
        $holder_name = null
    ) {
        $this->ensureWalletColumnsPagosRecurrentes();

        $token = trim((string)$token);
        if ($token === '') {
            return RECORD_CREATION_FAILED;
        }

        $transaction_reference = trim((string)$transaction_reference);
        $ultimos_digitos = preg_replace('/\D/', '', (string)$ultimos_digitos);
        $ultimos_digitos = $ultimos_digitos !== '' ? substr(str_pad($ultimos_digitos, 4, '0', STR_PAD_LEFT), -4) : '0000';

        $marca = trim((string)$marca);
        if ($marca === '') {
            $marca = 'Tarjeta';
        }

        $exp_year = trim((string)$exp_year);
        if ($exp_year === '') {
            $exp_year = date('Y');
        }

        $exp_month = trim((string)$exp_month);
        if ($exp_month === '') {
            $exp_month = date('n');
        }
        $exp_month = str_pad((string)((int)$exp_month > 0 ? (int)$exp_month : 1), 2, '0', STR_PAD_LEFT);

        $holder_name = trim((string)$holder_name);

        if ($holder_name === '' && strpos($token, 'NUVEI_UPO:') !== 0) {
            $remoteCards = $this->obtenerTarjetas($id_empresa);
            if (!empty($remoteCards['cards']) && is_array($remoteCards['cards'])) {
                foreach ($remoteCards['cards'] as $remoteCard) {
                    $remoteToken = trim((string)($remoteCard['token'] ?? ''));
                    if ($remoteToken !== '' && $remoteToken === $token) {
                        $holder_name = trim((string)($remoteCard['holder_name'] ?? ''));
                        if ($holder_name !== '') {
                            break;
                        }
                    }
                }
            }
        }

        $gatewayUid = trim((string)$gateway_uid);
        if ($gatewayUid === '') {
            $gatewayUid = $this->getPrimaryGatewayUserId($id_empresa);
        }
        if (trim((string)$gatewayUid) === '') {
            $gatewayUid = (string)$id_empresa;
        }

        $stmtDefault = $this->conn->prepare("SELECT id_pago_recurrente FROM pagos_recurrentes WHERE id_empresa = ? AND estado = 'A' AND es_default = 'Y' LIMIT 1");
        $stmtDefault->bind_param("s", $id_empresa);
        $stmtDefault->execute();
        $defaultExists = (bool)$stmtDefault->get_result()->fetch_assoc();
        $stmtDefault->close();

        $stmtExisting = $this->conn->prepare("SELECT id_pago_recurrente, es_default FROM pagos_recurrentes WHERE id_empresa = ? AND token = ? LIMIT 1");
        $stmtExisting->bind_param("ss", $id_empresa, $token);
        $stmtExisting->execute();
        $existing = $stmtExisting->get_result()->fetch_assoc();
        $stmtExisting->close();

        $requestedDefault = strtoupper(trim((string)$es_default));
        if ($requestedDefault !== 'Y' && $requestedDefault !== 'N') {
            $requestedDefault = '';
        }

        $defaultValue = $existing
            ? (($existing['es_default'] ?? 'N') === 'Y' ? 'Y' : ($defaultExists ? 'N' : 'Y'))
            : ($defaultExists ? 'N' : 'Y');

        if ($requestedDefault !== '') {
            $defaultValue = $requestedDefault === 'Y' ? 'Y' : ($defaultExists && !$existing ? 'N' : $defaultValue);
        }

        if ($existing) {
            $stmt = $this->conn->prepare(
                "UPDATE pagos_recurrentes
                 SET transaction_reference = ?, ultimos_digitos = ?, marca = ?, exp_year = ?, exp_month = ?,
                     es_default = ?, gateway_uid = ?, holder_name = ?, id_usuario = ?, estado = 'A'
                 WHERE id_pago_recurrente = ?"
            );
            $stmt->bind_param(
                "sssssssssi",
                $transaction_reference,
                $ultimos_digitos,
                $marca,
                $exp_year,
                $exp_month,
                $defaultValue,
                $gatewayUid,
                $holder_name,
                $id_usuario,
                $existing['id_pago_recurrente']
            );
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO pagos_recurrentes (token, transaction_reference, ultimos_digitos, marca, exp_year, exp_month, es_default, gateway_uid, holder_name, id_usuario, id_empresa)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param(
                "sssssssssss",
                $token,
                $transaction_reference,
                $ultimos_digitos,
                $marca,
                $exp_year,
                $exp_month,
                $defaultValue,
                $gatewayUid,
                $holder_name,
                $id_usuario,
                $id_empresa
            );
        }

        $result = $stmt->execute();
        $stmt->close();

        return $result ? RECORD_CREATED_SUCCESSFULLY : RECORD_CREATION_FAILED;
    }

    public function ensureCobrosProgramadosTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS cobros_programados_membresias (
            id_cobro_programado INT AUTO_INCREMENT PRIMARY KEY,
            id_empresa INT NOT NULL,
            id_usuario INT NOT NULL,
            id_membresia INT NOT NULL,
            id_membresia_empresa INT NOT NULL,
            codigo_agente VARCHAR(120) DEFAULT NULL,
            tipo_promocion VARCHAR(50) DEFAULT NULL,
            con_sucursales CHAR(1) DEFAULT 'N',
            monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            dias_extender INT NOT NULL DEFAULT 0,
            fecha_cobro DATETIME NOT NULL,
            estado CHAR(1) NOT NULL DEFAULT 'P',
            intentos INT NOT NULL DEFAULT 0,
            ultimo_error TEXT DEFAULT NULL,
            id_transaccion VARCHAR(120) DEFAULT NULL,
            codigo_autorizacion VARCHAR(120) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        return $this->conn->query($sql);
    }

    public function crearCobroProgramadoMembresia($id_empresa, $id_usuario, $id_membresia, $id_membresia_empresa, $codigo_agente, $tipo_promocion, $monto, $dias_extender, $fecha_cobro, $con_sucursales = 'N')
    {
        $this->ensureCobrosProgramadosTable();

        $stmt = $this->conn->prepare("INSERT INTO cobros_programados_membresias
            (id_empresa, id_usuario, id_membresia, id_membresia_empresa, codigo_agente, tipo_promocion, con_sucursales, monto, dias_extender, fecha_cobro, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'P')");
        $stmt->bind_param(
            "iiiisssdis",
            $id_empresa,
            $id_usuario,
            $id_membresia,
            $id_membresia_empresa,
            $codigo_agente,
            $tipo_promocion,
            $con_sucursales,
            $monto,
            $dias_extender,
            $fecha_cobro
        );
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function procesarCobrosProgramadosPendientes()
    {
        $this->ensureCobrosProgramadosTable();

        $sql = "SELECT *
                FROM cobros_programados_membresias
                WHERE estado = 'P'
                  AND fecha_cobro <= NOW()
                ORDER BY fecha_cobro ASC, id_cobro_programado ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendientes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($pendientes as $row) {
            $idProgramado = (int)$row['id_cobro_programado'];
            $idEmpresa = (int)$row['id_empresa'];
            $idUsuario = (int)$row['id_usuario'];
            $idMembresia = (int)$row['id_membresia'];
            $idMembresiaEmpresa = (int)$row['id_membresia_empresa'];
            $monto = (float)$row['monto'];
            $diasExtender = (int)$row['dias_extender'];

            $tokRow = $this->getPreferredChargeableTokenByEmpresa($idEmpresa);
            if (!$tokRow || !empty($tokRow['error']) || empty($tokRow['token'])) {
                $msg = $tokRow['msg'] ?? 'No existe una tarjeta valida para ejecutar el cobro programado.';
                $this->actualizarCobroProgramadoFallido($idProgramado, $msg);
                continue;
            }

            $resp = $this->debitToken($tokRow['token'], $idUsuario, $idMembresia, $idEmpresa, $monto, 0, "");
            $ok = false;
            $trxId = null;
            $auth = null;
            $payDate = date('Y-m-d H:i:s');

            if (is_array($resp) && isset($resp['transaction'])) {
                $trx = $resp['transaction'];
                if (strtolower($trx['status'] ?? '') === 'success') {
                    $ok = true;
                    $trxId = $trx['id'] ?? null;
                    $auth = $trx['authorization_code'] ?? null;
                    $payDate = $trx['payment_date'] ?? $payDate;
                    $monto = (float)($trx['amount'] ?? $monto);
                }
            }

            if (!$ok) {
                $msg = is_array($resp) ? json_encode($resp) : 'No fue posible ejecutar el cobro programado.';
                $this->actualizarCobroProgramadoFallido($idProgramado, $msg);
                continue;
            }

            $stmtFecha = $this->conn->prepare("SELECT fecha_fin FROM membresias_empresas WHERE id_membresia_empresa = ? LIMIT 1");
            $stmtFecha->bind_param("i", $idMembresiaEmpresa);
            $stmtFecha->execute();
            $fechaFinActual = $stmtFecha->get_result()->fetch_assoc()['fecha_fin'] ?? null;
            $stmtFecha->close();

            $nuevaFechaFin = $fechaFinActual
                ? (new DateTime($fechaFinActual))->modify('+' . $diasExtender . ' days')->format('Y-m-d H:i:s')
                : (new DateTime())->modify('+' . $diasExtender . ' days')->format('Y-m-d H:i:s');

            $stmtUp = $this->conn->prepare("UPDATE membresias_empresas
                SET fecha_fin = ?,
                    mes_prueba = 'N',
                    valor_membresia = ?
                WHERE id_membresia_empresa = ?");
            $stmtUp->bind_param("sdi", $nuevaFechaFin, $monto, $idMembresiaEmpresa);
            $stmtUp->execute();
            $stmtUp->close();

            $this->createPagoTransaccion($idUsuario, $idMembresia, $monto, $idEmpresa, 'empresa', $trxId, $auth, 'Y', $payDate, $idMembresiaEmpresa);
            $membresia = $this->getMembresiaById($idMembresia);
            $nombrePlan = $membresia["nombre"] ?? '';
            $resultadoFactura = $this->generarFacturaEmpresa($idEmpresa, $monto, $nombrePlan, $diasExtender);
            if (is_array($resultadoFactura) && !empty($resultadoFactura["error"])) {
                error_log('Error al generar factura Contifico para empresa ' . $idEmpresa . ': ' . json_encode($resultadoFactura, JSON_UNESCAPED_UNICODE));
            }
            //$this->notificaCompra($auth, $trxId, $idEmpresa, $monto, $nombrePlan, $diasExtender);

            $stmtDone = $this->conn->prepare("UPDATE cobros_programados_membresias
                SET estado = 'C', id_transaccion = ?, codigo_autorizacion = ?, ultimo_error = NULL
                WHERE id_cobro_programado = ?");
            $stmtDone->bind_param("ssi", $trxId, $auth, $idProgramado);
            $stmtDone->execute();
            $stmtDone->close();
        }
    }

    public function actualizarCobroProgramadoFallido($id_cobro_programado, $mensaje)
    {
        $stmt = $this->conn->prepare("UPDATE cobros_programados_membresias
            SET intentos = intentos + 1, ultimo_error = ?
            WHERE id_cobro_programado = ?");
        $stmt->bind_param("si", $mensaje, $id_cobro_programado);
        $stmt->execute();
        $stmt->close();
    }

    // debito con token
    public function debitToken($token, $id_usuario, $id_membresia, $id_empresa, $valor, $tipo_pago = null, $meses = null, $allowFreshToken = false)
    {
        //$valor = 1;
        if (strpos((string)$token, 'NUVEI_UPO:') === 0) {
            return [
                "error" => [
                    "type" => "NUVEI_RECURRENTE_NO_MIGRADO",
                    "help" => "La tarjeta fue registrada con el checkout nuevo de Nuvei y el cobro recurrente server-side aun no se ha migrado al flujo MIT de Nuvei."
                ]
            ];
        }

        if (!$allowFreshToken) {
            $chargeableToken = $this->getPreferredChargeableTokenByEmpresa($id_empresa, $token);
            if (!$chargeableToken || !empty($chargeableToken['error']) || empty($chargeableToken['token'])) {
                return [
                    "error" => [
                        "type" => "TOKEN_NOT_CHARGEABLE",
                        "description" => $chargeableToken['msg'] ?? "La tarjeta seleccionada no esta valida para cobro."
                    ]
                ];
            }
        }

        $empresa = $this->getEmpresaById($id_empresa);

        $id_empresa = strval($id_empresa);

        $description =  "test";

        $dev_reference =  "sasdas";
        $email =  $empresa["correo"];
        $tipo_pago = intval($tipo_pago);
        $meses = intval($meses);

        // $valoruno = 1; 
        $amount         = (float) number_format((float)$valor, 2, '.', '');
        // $amount         = (float) number_format((float)$valoruno, 2, '.', '');
        $taxable_amount = $amount;
        $vat            = 0.00;
        $tax_percentage = 0;

        $gatewayUserId = trim(strtolower((string)($empresa["correo"] ?? '')));
        if ($gatewayUserId === '') {
            $gatewayUserId = strval($id_empresa);
        }

        // datos para enviar en al api
        $data = array(
            'user' =>  array(
                "id" => $gatewayUserId,
                "email" => $email
            ),
            'order' =>  array(
                "amount" => $amount,
                "description" => $description,
                "taxable_amount" => $taxable_amount,
                "vat" => $vat,
                "tax_percentage" => $tax_percentage,
                "dev_reference" => $dev_reference,
                "installments_type" => $tipo_pago,
                "installments" => $meses
            ),
            'card' =>  array(
                "token" => $token,
            ),
        );
        //var_dump($data);
        $date = new DateTime();
        $unix_timestamp = $date->getTimestamp();
        $uniq_token_string = server_app_key . $unix_timestamp;
        $uniq_token_hash = hash('sha256', $uniq_token_string);
        $auth_token = base64_encode(server_application_code . ";" . $unix_timestamp . ";" . $uniq_token_hash);

        $ch = curl_init(paymentezURL . "transaction/debit/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Auth-Token: ' . $auth_token
        ]);


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
    // fin debito con token


    public function getOrdenPago($id_orden)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM pagos_ordenes
        WHERE id_orden = ? AND estado = 'A';");
        $stmt->bind_param("s", $id_orden);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response[] = $row;
            //return $row;
        }
        return $response;
    }

    // public function updateProductosOrdenEmpresa($productos, $id_orden, $estado)
    // {
    //     $productos = json_encode($productos);
    //     $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET productos = ?, estado_venta = ? WHERE id_orden = ?;");
    //     $stmt->bind_param("sss", $productos, $estado, $id_orden);
    //     $result = $stmt->execute();
    //     $stmt->close();
    //     if ($result) {
    //         return RECORD_UPDATED_SUCCESSFULLY;
    //     } else {
    //         return RECORD_UPDATED_FAILED;
    //     }
    // }

    public function updateProductosOrdenEmpresa($productos, $id_orden, $estado, $data)
    {
        $productosJson = json_encode($productos);

        // Casts seguros
        $peso_real_total_kg  = floatval($data["peso_real_total_kg"]);
        $largo_cm            = floatval($data["largo_cm"]);
        $ancho_cm            = floatval($data["ancho_cm"]);
        $alto_cm             = floatval($data["alto_cm"]);
        $fragil              = intval($data["fragil"]);
        $valor_producto_usd  = floatval($data["valor_producto_usd"]);

        $peso_volumetrico_kg = floatval($data["peso_volumetrico_kg"]);
        $peso_facturable_kg  = floatval($data["peso_facturable_kg"]);

        $seguro_base_usd     = floatval($data["seguro_base_usd"]);
        $seguro_iva_usd      = floatval($data["seguro_iva_usd"]);
        $seguro_total_usd    = floatval($data["seguro_total_usd"]);

        $alerta_mayor_50kg   = intval($data["alerta_mayor_50kg"]);

        $divisor_volumetrico = intval($data["divisor_volumetrico"]);
        $iva_envio           = floatval($data["iva_envio"]);
        $seguro_pct          = floatval($data["seguro_pct"]);

        try {
            $stmt = $this->conn->prepare("
            UPDATE ordenes_empresas
            SET
                productos = ?,
                estado_venta = ?,
                peso_real_total_kg = ?,
                largo_cm = ?,
                ancho_cm = ?,
                alto_cm = ?,
                fragil = ?,
                valor_producto_usd = ?,
                peso_volumetrico_kg = ?,
                peso_facturable_kg = ?,
                seguro_base_usd = ?,
                seguro_iva_usd = ?,
                seguro_total_usd = ?,
                alerta_mayor_50kg = ?,
                divisor_volumetrico = ?,
                iva_envio = ?,
                seguro_pct = ?
            WHERE id_orden = ?
        ");

            // Tipos: s (json) i (estado) d (decimales) i (enteros) ...
            $stmt->bind_param(
                "siddddiddddddiiddi",
                $productosJson,        // s
                $estado,               // i
                $peso_real_total_kg,   // d
                $largo_cm,             // d
                $ancho_cm,             // d
                $alto_cm,              // d
                $fragil,               // i
                $valor_producto_usd,   // d
                $peso_volumetrico_kg,  // d
                $peso_facturable_kg,   // d
                $seguro_base_usd,      // d
                $seguro_iva_usd,       // d
                $seguro_total_usd,     // d
                $alerta_mayor_50kg,    // i
                $divisor_volumetrico,  // i
                $iva_envio,            // d
                $seguro_pct,           // d
                $id_orden              // i
            );

            $ok = $stmt->execute();
            $err = $stmt->error;
            $stmt->close();

            if ($ok) return RECORD_UPDATED_SUCCESSFULLY;
            return "Error SQL: " . $err;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function upgradeMembresia($id_empresa, $id_membresia, $valor, $sucursales, $token_directo = null, $id_usuario_directo = null, $ya_cobrado = false, $trx_id_externo = null, $auth_code_externo = null, $facturacion = [])
    {
        $resp = ["error" => true, "msg" => "", "datos" => null];

        $actual = $this->getMembresiaActiva($id_empresa);
        if (!$actual) {
            $resp["msg"] = "La empresa no tiene membresía activa.";
            return $resp;
        }

        $nueva  = $this->getMembresiaById($id_membresia);
        if (!$nueva) {
            $resp["msg"] = "Membresía destino no existe.";
            return $resp;
        }

        try {
            $this->validarUpgradeEstricto($actual, $nueva, $valor, $sucursales);
        } catch (\Throwable $e) {
            $resp["msg"] = $e->getMessage();
            return $resp;
        }

        // base de prorrateo: último pago REAL; si no existe, se usa fecha_inicio + precio_catalogo
        $pago = $this->getUltimoPago(
            (int)$id_empresa,
            (int)$actual['id_membresia_actual'],
            (string)$actual['id_membresia_empresa']
        );

        /*$fechaBase = $pago ? new DateTime($pago['fecha_transaccion']) : new DateTime($actual['fecha_inicio']);
        $montoBase = $pago ? (float)$pago['pago_valor'] : (float)$actual['precio_catalogo'];

        $diasTot = (int)$actual['dias_permitidos'];
        if ($diasTot <= 0) {
            $resp["msg"] = "dias_permitidos inválido en la membresía actual.";
            return $resp;
        }

        $hoy    = new DateTime('now');
        $usados = max(0, (int)$fechaBase->diff($hoy)->format('%a'));
        if ($usados > $diasTot) $usados = $diasTot;

        $restantes    = max(0, $diasTot - $usados);
        $valorNoUsado = round($montoBase * ($restantes / $diasTot), 2);
        $aCobrar      = round(max(0, (float)$valor - $valorNoUsado), 2);
        //$aCobrar      = round(max(0, (float)$nueva['costo'] - $valorNoUsado), 2);
        */
        // El frontend ya envia el valor final del cobro para el upgrade.
        $valor = $this->toFloatMoney($valor);
        $valorSolicitadoUpgrade = $valor;

        $fechaBase = $pago ? new DateTime($pago['fecha_transaccion']) : new DateTime($actual['fecha_inicio']);

        // ✅ Usa como base el precio real pagado si existe; si no, el catálogo.
        //    Pero para PRORRATEO, conviene limitar a máximo el catálogo para evitar "créditos raros"
        $montoBase = $pago ? $this->toFloatMoney($pago['pago_valor']) : $this->toFloatMoney($actual['precio_catalogo']);
        $precioCatActual = $this->toFloatMoney($actual['precio_catalogo']);

        // Si el pago fue mayor que el catálogo (descuento raro inverso, recargos, etc.)
        // limita prorrateo a catálogo para que el crédito no sea mayor de lo esperado
        $montoBaseProrrateo = min($montoBase, $precioCatActual);

        /* $diasTot = (int)$actual['dias_permitidos'];
        if ($diasTot <= 0) {
            $resp["msg"] = "dias_permitidos inválido en la membresía actual.";
            return $resp;
        } */
        // Fechas reales del periodo (incluye extensiones por códigos/bonos)
        if (empty($actual['fecha_inicio']) || empty($actual['fecha_fin'])) {
            $resp["msg"] = "La membresía actual no tiene fecha_inicio/fecha_fin válidas.";
            return $resp;
        }

        $inicioPlan = new DateTime($actual['fecha_inicio']);
        $finPlan    = new DateTime($actual['fecha_fin']);
        $hoy        = new DateTime('now');

        // Usar solo fecha (sin hora) para que no cambie por minutos
        $inicioDia = new DateTime($inicioPlan->format('Y-m-d'));
        $finDia    = new DateTime($finPlan->format('Y-m-d'));
        $hoyDia    = new DateTime($hoy->format('Y-m-d'));

        // ✅ DÍAS TOTALES REALES DEL PERIODO
        $diasTot = (int)$inicioDia->diff($finDia)->format('%a');
        // ✅ Diferencia de días “estable”: compara fechas SIN hora
        $baseDia = new DateTime($fechaBase->format('Y-m-d'));

        $usados = (int)$baseDia->diff($hoyDia)->format('%a');
        $usados = max(0, min($usados, $diasTot));

        $restantes = $diasTot - $usados;

        // ✅ No redondees a mitad: calcula con precisión y redondea al final
        $creditoNoUsado = ($diasTot > 0)
            ? ($montoBaseProrrateo * ($restantes / $diasTot))
            : 0.0;

        $aCobrar = $valorSolicitadoUpgrade - $creditoNoUsado;

        // ✅ Redondeo final y clamp
        $valorNoUsado = $this->money(max(0, $creditoNoUsado));
        $aCobrar      = $this->money(max(0, $aCobrar));
        $aCobrarCalculado = $aCobrar;

        // Para upgrade, respetar el valor final recibido desde frontend como monto
        // definitivo a cobrar/facturar. El prorrateo se conserva solo como dato debug.
        if ($valorSolicitadoUpgrade > 0) {
            $aCobrar = $this->money($valorSolicitadoUpgrade);
        }

        // Si el checkout externo ya cobro un valor positivo, respetarlo para
        // registrar pago y factura aunque el prorrateo backend haya terminado en 0.
        if ($ya_cobrado && $valor > 0 && $aCobrar <= 0) {
            $aCobrar = $this->money($valor);
        }


        $diasNuevo = (int)$nueva['dias_permitidos'];
        if ($diasNuevo <= 0) throw new Exception("dias_permitidos inválido (nuevo).");

        $resp["error"] = false;
        $resp["msg"]   = "Membresia actualizada correctamente";
        $resp["datos"] = [
            "precio_catalogo_actual" => (float)$actual['precio_catalogo'],
            //"precio_catalogo_nuevo"  => (float)$nueva['costo'],
            "precio_catalogo_nuevo"  => (float)$valorSolicitadoUpgrade,
            "monto_pagado_actual"    => $montoBase,
            "fecha_pago_base"        => $fechaBase->format('Y-m-d H:i:s'),
            "dias_totales_actual"    => $diasTot,
            "dias_usados"            => $usados,
            "dias_restantes"         => $restantes,
            "valor_no_usado"         => $valorNoUsado,
            "a_cobrar_calculado"     => $aCobrarCalculado,
            "a_cobrar"               => $aCobrar,
            "tipo_nuevo"             => strtolower($nueva['tipo']),
            "factura"                => [
                "emitida" => false,
                "source" => "factura",
                "msg" => "Factura pendiente de procesar."
            ]
        ];

        $cobroExitoso = ($aCobrar <= 0);
        $trxId = null;
        $auth = null;
        $payDate = date('Y-m-d H:i:s');
        $idUsuario = null;

        if ($aCobrar > 0) {
            $idEmp = (int)$id_empresa;
            $idMem = (int)$nueva['id_membresia'];

            if ($token_directo) {
                $tokRow = $this->getPreferredChargeableTokenByEmpresa($idEmp, $token_directo);
                if (!$tokRow || !empty($tokRow['error']) || empty($tokRow['token'])) {
                    $resp["error"] = true;
                    $resp["msg"] = $tokRow['msg'] ?? "La tarjeta seleccionada no esta disponible para cobro.";
                    return $resp;
                }
                $token     = $tokRow['token'];
                $idUsuario = (int)($id_usuario_directo ?? $tokRow['id_usuario'] ?? 0);
            } else {
                $tokRow = $this->getPreferredChargeableTokenByEmpresa($idEmp);
                if (!$tokRow || !empty($tokRow['error']) || empty($tokRow['token'])) {
                    $resp["error"] = true;
                    $resp["msg"] = $tokRow['msg'] ?? "No existe una tarjeta valida para cobrar el upgrade.";
                    return $resp;
                }
                $token     = $tokRow['token'];
                $idUsuario = (int)($tokRow['id_usuario'] ?? 0);
            }

            if ($ya_cobrado) {
                // Cobro ya procesado por PaymentCheckout (client-side)
                $cobroExitoso = true;
                $trxId   = $trx_id_externo;
                $auth    = $auth_code_externo;
            } else {
                // 3) Cobro server-side (corriente=0, meses="")
                $respu = $this->debitToken($token, $idUsuario, $idMem, $idEmp, $aCobrar, 0, "");
                if (is_array($respu) && isset($respu['transaction'])) {
                    $trx = $respu['transaction'];
                    if (strtolower($trx['status'] ?? '') === 'success') {
                        $cobroExitoso = true;
                        $trxId   = $trx['id'] ?? null;
                        $auth    = $trx['authorization_code'] ?? null;
                        $payDate = $trx['payment_date'] ?? $payDate;
                        $valor   = $trx['amount'] ?? null;
                    }
                }
                if (!$cobroExitoso) {
                    $gatewayType = trim((string)($respu['error']['type'] ?? ''));
                    $gatewayDescription = trim((string)($respu['error']['description'] ?? $respu['error']['help'] ?? ''));
                    $gatewayMessage = trim((string)($respu['message'] ?? ''));
                    $resp["error"] = true;
                    if ($gatewayType === 'CarrierNotConfiguredError') {
                        $resp["msg"] = "La pasarela de pruebas no tiene un carrier configurado para este cobro o el monto esta fuera de los limites permitidos por Nuvei.";
                        $resp["gateway_error"] = $respu['error'] ?? null;
                        return $resp;
                    }
                    if ($gatewayDescription !== '' || $gatewayMessage !== '') {
                        $resp["msg"] = $gatewayDescription !== ''
                            ? $gatewayDescription
                            : $gatewayMessage;
                        $resp["gateway_error"] = $respu['error'] ?? null;
                        return $resp;
                    }
                    $resp["msg"] = "El cobro falló; no se registró la transacción.";
                    return $resp;
                }
            }
        }

        $this->conn->begin_transaction();
        try {
            // 1) cerrar TODAS las membresías activas de la empresa
            $finAhora = $hoy->format('Y-m-d H:i:s');
            $stmt = $this->conn->prepare("UPDATE membresias_empresas SET estado='E', fecha_fin=? WHERE id_empresa=? AND estado='A'");
            if (!$stmt) {
                throw new Exception("No se pudo preparar el cierre de la membresía actual: " . $this->conn->error);
            }
            $stmt->bind_param("si", $finAhora, $id_empresa);
            if (!$stmt->execute()) {
                $errorStmt = $stmt->error;
                $stmt->close();
                throw new Exception("No se pudo cerrar la membresía actual: " . $errorStmt);
            }
            $stmt->close();

            // 2) crear nueva (estado A) usando dias_permitidos del nuevo plan
            $inicio = new DateTime();
            $fin    = (clone $inicio)->modify("+{$diasNuevo} days");
            $limite = $nueva['numero'];

            $sql = "INSERT INTO membresias_empresas (id_empresa, id_membresia, fecha_inicio, fecha_fin, limite_articulos, valor_membresia)
                    VALUES (?,?,?,?,?,?)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("No se pudo preparar la nueva membresía: " . $this->conn->error);
            }
            $fiS = $inicio->format('Y-m-d H:i:s');
            $ffS = $fin->format('Y-m-d H:i:s');
            $stmt->bind_param("iissss", $id_empresa, $nueva['id_membresia'], $fiS, $ffS, $limite, $valor);
            if (!$stmt->execute()) {
                $errorStmt = $stmt->error;
                $stmt->close();
                throw new Exception("No se pudo crear la nueva membresía: " . $errorStmt);
            }
            $id_membresia_empresa_nueva = (int)$this->conn->insert_id;
            $stmt->close();

            $stmt2 = $this->conn->prepare("UPDATE empresas SET sucursales = ? WHERE id_empresa = ?");
            if (!$stmt2) {
                throw new Exception("No se pudo preparar la actualización de sucursales: " . $this->conn->error);
            }
            $stmt2->bind_param("si", $sucursales, $id_empresa);
            if (!$stmt2->execute()) {
                $errorStmt = $stmt2->error;
                $stmt2->close();
                throw new Exception("No se pudo actualizar las sucursales de la empresa: " . $errorStmt);
            }
            $stmt2->close();

            $this->updateEmpresaBillingData($id_empresa, is_array($facturacion) ? $facturacion : []);

            if ($aCobrar > 0 && $idUsuario !== null) {
                $idEmp = (int)$id_empresa;
                $idMem = (int)$nueva['id_membresia'];
                $this->createPagoTransaccion($idUsuario, $idMem, $aCobrar, $idEmp, 'empresa', $trxId, $auth, 'Y', $payDate, $id_membresia_empresa_nueva);
            }

            $this->conn->commit();
        } catch (\Throwable $e) {
            $this->conn->rollback();
            $resp["error"] = true;
            $resp["msg"] = $e->getMessage();
            return $resp;
        }

        if ($aCobrar > 0) {
            //$this->notificaCompra($auth, $trxId, (int)$id_empresa, $aCobrar);

            $resultadoFactura = $this->generarFacturaEmpresa(
                $id_empresa,
                $aCobrar,
                $nueva['nombre'] ?? '',
                $diasNuevo,
                is_array($facturacion) ? $facturacion : [],
                ["contexto" => "upgrade"]
            );
            if (is_array($resultadoFactura) && empty($resultadoFactura["error"])) {
                $resp["datos"]["factura"] = [
                    "emitida" => true,
                    "source" => $resultadoFactura["source"] ?? "factura",
                    "msg" => $resultadoFactura["msg"] ?? "Factura generada correctamente.",
                    "documento" => $resultadoFactura["documento"] ?? null,
                    "id_factura_contifico" => $resultadoFactura["id_factura_contifico"] ?? null,
                    "debug" => $resultadoFactura["debug"] ?? null
                ];
                $resp["msg"] = "Membresia actualizada correctamente y factura generada.";
            } else {
                $resp["datos"]["factura"] = [
                    "emitida" => false,
                    "source" => $resultadoFactura["source"] ?? "contifico",
                    "msg" => $resultadoFactura["msg"] ?? "No se pudo generar la factura en Contifico.",
                    "debug" => $resultadoFactura["debug"] ?? null
                ];
                $resp["msg"] = "Membresia actualizada correctamente, pero hubo un error al generar la factura.";
            }
        } else {
            $resp["datos"]["factura"] = [
                "emitida" => false,
                "source" => "factura",
                "msg" => "No se genero factura porque no hubo cobro adicional en el upgrade."
            ];
        }
        return $resp;
    }

    public function getTarjetasByEmpresa($id_empresa)
    {
        $this->ensureWalletColumnsPagosRecurrentes();

        $stmt = $this->conn->prepare("SELECT * FROM pagos_recurrentes 
                                  WHERE id_empresa = ? AND estado = 'A' 
                                  ORDER BY es_default DESC, created_at DESC");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        $tarjetas = array();
        while ($row = $result->fetch_assoc()) {
            $tarjetas[] = $row;
        }
        return $tarjetas;
    }

    public function getWalletTarjetasByEmpresa($id_empresa)
    {
        $this->ensureWalletColumnsPagosRecurrentes();

        $localCards = [];
        foreach ($this->getTarjetasByEmpresa($id_empresa) as $row) {
            $token = trim((string)($row['token'] ?? ''));
            if ($token === '') {
                continue;
            }
            $localCards[$token] = $row;
        }

        $remote = $this->obtenerTarjetas($id_empresa);
        $remoteMap = [];
        if (!empty($remote['cards']) && is_array($remote['cards'])) {
            foreach ($remote['cards'] as $remoteCard) {
                $remoteToken = trim((string)($remoteCard['token'] ?? ''));
                if ($remoteToken === '') {
                    continue;
                }
                $remoteMap[$remoteToken] = $remoteCard;
            }
        }

        $normalizeBrand = function ($value) {
            $brand = strtolower(trim((string)$value));
            if ($brand === 'vi' || strpos($brand, 'visa') !== false) return 'visa';
            if ($brand === 'mc' || strpos($brand, 'master') !== false) return 'mastercard';
            if ($brand === 'ax' || strpos($brand, 'amex') !== false || strpos($brand, 'american') !== false) return 'amex';
            if ($brand === 'dc' || strpos($brand, 'diners') !== false) return 'diners';
            if ($brand === 'di' || strpos($brand, 'discover') !== false) return 'discover';
            return $brand;
        };

        $findRemoteCardForLocal = function ($token, $local) use ($remoteMap, $normalizeBrand) {
            if (!empty($remoteMap[$token])) {
                return $remoteMap[$token];
            }

            $localLastFour = substr(preg_replace('/\D/', '', (string)($local['ultimos_digitos'] ?? '')), -4);
            $localMonth = str_pad((string)($local['exp_month'] ?? ''), 2, '0', STR_PAD_LEFT);
            $localYear = trim((string)($local['exp_year'] ?? ''));
            $localBrand = $normalizeBrand($local['marca'] ?? '');
            $localGatewayUid = trim((string)($local['gateway_uid'] ?? ''));

            foreach ($remoteMap as $remoteCard) {
                $remoteNumber = preg_replace('/\D/', '', (string)($remoteCard['number'] ?? ''));
                $remoteLastFour = substr($remoteNumber, -4);
                $remoteMonth = str_pad((string)($remoteCard['expiry_month'] ?? ''), 2, '0', STR_PAD_LEFT);
                $remoteYear = trim((string)($remoteCard['expiry_year'] ?? ''));
                $remoteBrand = $normalizeBrand($remoteCard['type'] ?? '');
                $remoteGatewayUid = trim((string)($remoteCard['gateway_uid'] ?? ''));

                $matchesLastFour = $localLastFour !== '' && $remoteLastFour !== '' && $localLastFour === $remoteLastFour;
                $matchesMonth = $localMonth !== '' && $remoteMonth !== '' ? $localMonth === $remoteMonth : true;
                $matchesYear = $localYear !== '' && $remoteYear !== '' ? $localYear === $remoteYear : true;
                $matchesBrand = $localBrand !== '' && $remoteBrand !== '' ? $localBrand === $remoteBrand : true;
                $matchesGateway = $localGatewayUid !== '' && $remoteGatewayUid !== '' ? $localGatewayUid === $remoteGatewayUid : true;

                if ($matchesLastFour && $matchesMonth && $matchesYear && $matchesBrand && $matchesGateway) {
                    return $remoteCard;
                }
            }

            return [];
        };

        $cards = [];

        foreach ($localCards as $token => $local) {
            $isNuveiToken = strpos($token, 'NUVEI_UPO:') === 0;
            $remoteCard = $findRemoteCardForLocal($token, $local);

            $expMonth = $remoteCard['expiry_month'] ?? ($local['exp_month'] ?? null);
            $expYear = $remoteCard['expiry_year'] ?? ($local['exp_year'] ?? null);
            $rawNumber = preg_replace('/\D/', '', (string)($remoteCard['number'] ?? ''));
            $lastFour = strlen($rawNumber) >= 4
                ? substr($rawNumber, -4)
                : ($local['ultimos_digitos'] ?? substr((string)($remoteCard['number'] ?? ''), -4));

            $holderName = trim((string)($remoteCard['holder_name'] ?? ($local['holder_name'] ?? '')));

            $cards[] = [
                'id_pago_recurrente' => $local['id_pago_recurrente'] ?? null,
                'token' => $token,
                'ultimos_digitos' => $lastFour,
                'marca' => $remoteCard['type'] ?? ($local['marca'] ?? ''),
                'exp_month' => $expMonth,
                'exp_year' => $expYear,
                'holder_name' => $holderName,
                'status' => $remoteCard['status'] ?? 'local',
                'banco' => $remoteCard['bank_name'] ?? '',
                'es_default' => ($local['es_default'] ?? 'N') === 'Y' ? 'Y' : 'N',
                'gateway_uid' => $remoteCard['gateway_uid'] ?? ($local['gateway_uid'] ?? $this->getPrimaryGatewayUserId($id_empresa)),
                'is_expired' => $this->isWalletTokenExpired($expMonth, $expYear),
                'source' => $isNuveiToken ? 'nuvei-local' : 'gateway-local',
                'card_client' => !empty($remoteCard)
                    ? $remoteCard
                    : [
                        'token' => $token,
                        'holder_name' => $holderName,
                        'expiry_month' => $expMonth,
                        'expiry_year' => $expYear,
                        'type' => $local['marca'] ?? '',
                        'number' => $lastFour ? ('****' . $lastFour) : '',
                        'gateway_uid' => $local['gateway_uid'] ?? '',
                        'source' => $isNuveiToken ? 'nuvei-local' : 'local-no-match'
                    ]
            ];
        }

        usort($cards, function ($a, $b) {
            if (($a['es_default'] ?? 'N') !== ($b['es_default'] ?? 'N')) {
                return ($a['es_default'] ?? 'N') === 'Y' ? -1 : 1;
            }
            return strcmp((string)($b['token'] ?? ''), (string)($a['token'] ?? ''));
        });

        return $cards;
    }

    public function setTarjetaDefaultEmpresa($id_empresa, $token)
    {
        $this->ensureWalletColumnsPagosRecurrentes();

        $chargeableSelection = $this->getPreferredChargeableTokenByEmpresa($id_empresa, $token);
        if (!$chargeableSelection || !empty($chargeableSelection['error'])) {
            return [
                "error" => true,
                "msg" => $chargeableSelection['msg'] ?? "Solo puedes marcar como predeterminadas tarjetas validas."
            ];
        }

        $stmt = $this->conn->prepare("SELECT id_pago_recurrente FROM pagos_recurrentes WHERE id_empresa = ? AND token = ? AND estado = 'A' LIMIT 1");
        $stmt->bind_param("ss", $id_empresa, $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return ["error" => true, "msg" => "La tarjeta seleccionada no existe en la wallet local."];
        }

        $stmtReset = $this->conn->prepare("UPDATE pagos_recurrentes SET es_default = 'N' WHERE id_empresa = ? AND estado = 'A'");
        $stmtReset->bind_param("s", $id_empresa);
        $stmtReset->execute();
        $stmtReset->close();

        $stmtSet = $this->conn->prepare("UPDATE pagos_recurrentes SET es_default = 'Y' WHERE id_empresa = ? AND token = ? AND estado = 'A'");
        $stmtSet->bind_param("ss", $id_empresa, $token);
        $ok = $stmtSet->execute();
        $stmtSet->close();

        return [
            "error" => !$ok,
            "msg" => $ok ? "Tarjeta predeterminada actualizada." : "No se pudo actualizar la tarjeta predeterminada."
        ];
    }

    public function deleteTarjetaEmpresa($id_empresa, $token)
    {
        $this->ensureWalletColumnsPagosRecurrentes();
        $token = trim((string)$token);

        $stmt = $this->conn->prepare("SELECT * 
                                      FROM pagos_recurrentes 
                                      WHERE id_empresa = ? AND TRIM(token) = ?
                                      ORDER BY CASE WHEN estado = 'A' THEN 0 ELSE 1 END, id_pago_recurrente DESC
                                      LIMIT 1");
        $stmt->bind_param("ss", $id_empresa, $token);
        $stmt->execute();
        $card = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$card) {
            return ["error" => true, "msg" => "La tarjeta no existe o ya fue eliminada."];
        }

        if (($card['estado'] ?? '') !== 'A') {
            return ["error" => true, "msg" => "La tarjeta ya se encontraba eliminada."];
        }

        $stmtCount = $this->conn->prepare("SELECT COUNT(*) AS total FROM pagos_recurrentes WHERE id_empresa = ? AND estado = 'A'");
        $stmtCount->bind_param("s", $id_empresa);
        $stmtCount->execute();
        $countRow = $stmtCount->get_result()->fetch_assoc();
        $stmtCount->close();

        $totalActivas = (int)($countRow['total'] ?? 0);
        if ($totalActivas <= 1) {
            return ["error" => true, "msg" => "Debes conservar al menos una tarjeta registrada como predeterminada."];
        }

        $isNuveiToken = strpos((string)$token, 'NUVEI_UPO:') === 0;

        $gatewayCandidates = [];
        $remoteCards = $this->obtenerTarjetas($id_empresa);
        if (!empty($remoteCards['cards']) && is_array($remoteCards['cards'])) {
            foreach ($remoteCards['cards'] as $remoteCard) {
                $remoteToken = trim((string)($remoteCard['token'] ?? ''));
                $remoteGatewayUid = trim((string)($remoteCard['gateway_uid'] ?? ''));
                if ($remoteToken !== '' && $remoteToken === trim((string)$token) && $remoteGatewayUid !== '') {
                    $gatewayCandidates[] = $remoteGatewayUid;
                }
            }
        }
        if (!empty($card['gateway_uid'])) {
            $gatewayCandidates[] = $card['gateway_uid'];
        }
        $gatewayCandidates = array_values(array_unique(array_merge($gatewayCandidates, $this->getEmpresaGatewayUserIds($id_empresa))));

        $gatewayDeleted = $isNuveiToken;
        $lastGatewayError = null;
        $gatewayAttempts = [];
        if (!$isNuveiToken) {
            $stgBaseUrl = 'https://ccapi.paymentez.com/v2/';
            $defaultBaseUrl = rtrim((string)paymentezURL, '/') . '/';

            foreach ($gatewayCandidates as $gatewayUid) {
                $payload = [
                    "card" => ["token" => $token],
                    "user" => ["id" => $gatewayUid]
                ];

                $responses = [
                    [
                        "base_url" => $stgBaseUrl,
                        "response" => $this->paymentezRequest('POST', 'card/delete/', $payload, $stgBaseUrl)
                    ]
                ];

                if (rtrim($stgBaseUrl, '/') !== rtrim($defaultBaseUrl, '/')) {
                    $responses[] = [
                        "base_url" => $defaultBaseUrl,
                        "response" => $this->paymentezRequest('POST', 'card/delete/', $payload)
                    ];
                }

                foreach ($responses as $attempt) {
                    $gatewayResp = $attempt['response'];
                    $httpCode = (int)($gatewayResp['http_code'] ?? 0);
                    $message = trim((string)($gatewayResp['message'] ?? ''));
                    $errorType = trim((string)($gatewayResp['error']['type'] ?? ''));
                    $errorDescription = trim((string)($gatewayResp['error']['description'] ?? $gatewayResp['error']['help'] ?? ''));

                    $gatewayAttempts[] = [
                        "gateway_uid" => $gatewayUid,
                        "base_url" => $attempt['base_url'],
                        "payload" => $payload,
                        "http_code" => $httpCode,
                        "message" => $message,
                        "error_type" => $errorType,
                        "error_description" => $errorDescription,
                        "raw" => $gatewayResp
                    ];

                    if (
                        ($httpCode >= 200 && $httpCode < 300 && empty($gatewayResp['error'])) ||
                        ($message !== '' && stripos($message, 'deleted') !== false)
                    ) {
                        $gatewayDeleted = true;
                        break 2;
                    }

                    if ($errorDescription !== '') {
                        $lastGatewayError = $errorDescription;
                    } elseif ($errorType !== '') {
                        $lastGatewayError = $errorType;
                    } elseif ($message !== '') {
                        $lastGatewayError = $message;
                    } elseif (!empty($gatewayResp)) {
                        $lastGatewayError = json_encode($gatewayResp, JSON_UNESCAPED_UNICODE);
                    } else {
                        $lastGatewayError = 'El gateway no devolvio un mensaje de error.';
                    }
                }
            }
        }

        if (!$gatewayDeleted) {
            $uidMismatch = false;
            foreach ($gatewayAttempts as $attempt) {
                $combinedAttemptMessage = trim((string)(
                    ($attempt['error_description'] ?? '') . ' ' .
                    ($attempt['error_type'] ?? '') . ' ' .
                    ($attempt['message'] ?? '')
                ));
                if (stripos($combinedAttemptMessage, 'uid does not match') !== false) {
                    $uidMismatch = true;
                    break;
                }
            }

            return [
                "error" => true,
                "msg" => $uidMismatch
                    ? "Nuvei/Paymentez rechazó la eliminación porque el user.id enviado no coincide con el dueño original del token."
                    : ($lastGatewayError ?: "No se pudo eliminar la tarjeta en el gateway."),
                "gateway" => $isNuveiToken ? "nuvei-local" : "paymentez",
                "gateway_attempts" => $gatewayAttempts,
                "token" => $token,
                "gateway_uid" => $card['gateway_uid'] ?? null
            ];
        }

        $this->conn->begin_transaction();
        try {
            $stmtDelete = $this->conn->prepare("UPDATE pagos_recurrentes SET estado = 'E', es_default = 'N' WHERE id_empresa = ? AND TRIM(token) = ?");
            $stmtDelete->bind_param("ss", $id_empresa, $token);
            if (!$stmtDelete->execute()) {
                throw new Exception($stmtDelete->error);
            }
            $stmtDelete->close();

            if (($card['es_default'] ?? 'N') === 'Y') {
                $nextCard = $this->getPreferredChargeableTokenByEmpresa($id_empresa);
                if (!$nextCard || !empty($nextCard['error']) || empty($nextCard['token'])) {
                    $stmtNext = $this->conn->prepare("SELECT token FROM pagos_recurrentes WHERE id_empresa = ? AND estado = 'A' ORDER BY created_at DESC LIMIT 1");
                    $stmtNext->bind_param("s", $id_empresa);
                    $stmtNext->execute();
                    $nextCard = $stmtNext->get_result()->fetch_assoc();
                    $stmtNext->close();
                }

                if ($nextCard && !empty($nextCard['token'])) {
                    $stmtSetDefault = $this->conn->prepare("UPDATE pagos_recurrentes SET es_default = 'Y' WHERE id_empresa = ? AND token = ?");
                    $stmtSetDefault->bind_param("ss", $id_empresa, $nextCard['token']);
                    if (!$stmtSetDefault->execute()) {
                        throw new Exception($stmtSetDefault->error);
                    }
                    $stmtSetDefault->close();
                }
            }

            $this->conn->commit();

            return [
                "error" => false,
                "msg" => "Tarjeta eliminada correctamente.",
                "gateway" => $isNuveiToken ? "nuvei-local" : "paymentez",
                "gateway_attempts" => $gatewayAttempts
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                "error" => true,
                "msg" => "La tarjeta se eliminó en gateway, pero no se pudo actualizar localmente: " . $e->getMessage(),
                "gateway" => $isNuveiToken ? "nuvei-local" : "paymentez",
                "gateway_attempts" => $gatewayAttempts
            ];
        }
    }

    private function money($n, $dec = 2)
    {
        return round((float)$n + 1e-9, $dec);
    }

    private function toFloatMoney($v)
    {
        // soporta "10,50" y "$10.50"
        $v = trim((string)$v);
        $v = str_replace(['$', ' '], ['', ''], $v);

        // si trae coma decimal: 10,50 -> 10.50
        if (preg_match('/^\d+,\d+$/', $v)) {
            $v = str_replace(',', '.', $v);
        }
        return (float)$v;
    }


    public function getMembresiaActiva($id_empresa)
    {
        $sql = "SELECT me.*,
                    m.id_membresia   AS id_membresia_actual,
                    m.nombre         AS nombre_actual,
                    m.tipo           AS tipo_actual,
                    m.costo          AS precio_catalogo,
                    m.dias_permitidos
                FROM membresias_empresas me
                JOIN membresias m ON m.id_membresia = me.id_membresia
                WHERE me.id_empresa = ? AND me.estado = 'A'
                ORDER BY me.id_membresia_empresa DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $r ?: null;
    }

    public function getMembresiaEmpresa($id_empresa)
    {
        // Buscamos la membresía activa vinculando ambas tablas para obtener el nombre y el costo base
        $stmt = $this->conn->prepare("
        SELECT 
            me.id_membresia_empresa,
            me.id_membresia,
            me.fecha_inicio,
            me.fecha_fin,
            m.costo as valor_membresia, -- El valor que pagó originalmente
            m.nombre,
            m.costo AS costo_catalogo,
            m.dias_permitidos
        FROM membresias_empresas me 
        INNER JOIN membresias m ON me.id_membresia = m.id_membresia
        WHERE me.id_empresa = ? AND me.estado = 'A' 
        LIMIT 1
    ");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }
    public function getUltimoPago($id_empresa, $id_membresia, $id_membresia_empresa)
    {
        // id_membresia_empresa es VARCHAR(100) en pagos_transaccion
        $sql = "SELECT *
                FROM pagos_transaccion
                WHERE id_empresa=? AND id_membresia=? AND id_membresia_empresa=? AND estado='A'
                ORDER BY fecha_transaccion DESC, id_pagos_transaccion DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_empresa, $id_membresia, $id_membresia_empresa);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $r ?: null;
    }

    /*  public function validarUpgradeEstricto($actual, $nueva, $valorNuevo)
    {
        // por periodo (mensual < semestral < anual)
        $rank = ['mensual' => 1, 'semestral' => 2, 'anual' => 3];
        $ta = strtolower($actual['tipo_actual']);
        $tn = strtolower($nueva['tipo']);
        if (($rank[$tn] ?? 0) < ($rank[$ta] ?? 0)) {
            throw new Exception("No se permite downgrade de periodo.");
        }
        // regla especial (ajústala a tus nombres reales)
        $nombreActual = strtolower($actual['nombre_actual'] ?? '');
        if ($ta === 'anual' && (strpos($nombreActual, 'onemuv') !== false || strpos($nombreActual, 'basicmuv') !== false) && $tn === 'mensual') {
            throw new Exception("Restricción: no se puede pasar de Anual OneMuv/BasicMuv a Mensual.");
        }
        // nuevo plan debe tener mayor precio de catálogo que el actual
        $precioCatActual = (float)($actual['precio_catalogo'] ?? 0);
        $precioCatNuevo  = (float)($nueva['costo'] ?? 0);
        if ($precioCatNuevo <= $precioCatActual) {
            throw new Exception("No se permite downgrade: el plan destino debe tener mayor precio de catálogo.");
        }
    } */

    public function validarUpgradeEstricto($actual, $nueva, $valorNuevo, $sucursales = null)
    {
        // por periodo (mensual < semestral < anual)
        $rank = ['mensual' => 1, 'semestral' => 2, 'anual' => 3];
        $ta = strtolower($actual['tipo_actual']);
        $tn = strtolower($nueva['tipo']);

        if (($rank[$tn] ?? 0) < ($rank[$ta] ?? 0)) {
            throw new Exception("No se permite downgrade de periodo.");
        }

        // regla especial
        $nombreActual = strtolower($actual['nombre_actual'] ?? '');
        if (
            $ta === 'anual'
            && (strpos($nombreActual, 'onemuv') !== false || strpos($nombreActual, 'basicmuv') !== false)
            && $tn === 'mensual'
        ) {
            throw new Exception("Restricción: no se puede pasar de Anual OneMuv/BasicMuv a Mensual.");
        }

        // Validar contra el precio de catalogo del plan destino.
        // El valor del request puede venir prorrateado o con credito a favor,
        // y no debe usarse para decidir si el plan es inferior.
        $precioCatActual = (float)($actual['precio_catalogo'] ?? 0);
        $precioCatNuevo  = (float)($nueva['costo'] ?? $valorNuevo);

        $sucFlag = is_string($sucursales) ? strtoupper($sucursales) : ($sucursales ? 'Y' : 'N');
        if ($precioCatNuevo < $precioCatActual && $sucFlag !== 'Y') {
            throw new Exception("No se permite downgrade: el plan destino debe tener mayor precio de catálogo.");
        }
    }


    public function catalogEnsure(string $entity, string $nombre, array $extra = []): array
    {
        $resp = ["error" => true, "msg" => "", "id" => null];

        // --- Mapa de entidades -> tablas/columnas (ajustado a tus tablas) ---
        $MAP = [
            // ✅ marcas de VEHÍCULO
            'marcas' => [
                'table'   => 'marcas',
                'pk'      => 'id_marca',
                'name'    => 'nombre',
                'parents' => []
            ],

            // ✅ marcas de PRODUCTO
            'marcas_productos' => [
                'table'   => 'marcas_productos',
                'pk'      => 'id_marca_producto',
                'name'    => 'nombre',
                'parents' => []
            ],

            'tipo_traccion' => [
                'table'   => 'tipo_traccion',
                'pk'      => 'id_tipo_traccion',
                'name'    => 'nombre',
                'parents' => []
            ],
            'tipos_auto' => [
                'table'   => 'tipos_auto',
                'pk'      => 'id_tipo_auto',
                'name'    => 'nombre',
                'parents' => []
            ],
            'funcionamiento_motor' => [
                'table'   => 'funcionamiento_motor',
                'pk'      => 'id_funcionamiento_motor',
                'name'    => 'nombre',
                'parents' => []
            ],
            'categorias' => [
                'table'   => 'categorias',
                'pk'      => 'id_categoria',
                'name'    => 'nombre',
                'parents' => []
            ],
            'subcategorias' => [
                'table'   => 'sub_categorias',
                'pk'      => 'id_sub_categoria',
                'name'    => 'nombre',
                'parents' => ['id_categoria']
            ],
            'modelos_autos' => [
                'table'   => 'modelos_autos',
                'pk'      => 'id_modelos_autos',
                'name'    => 'nombre',
                'parents' => []
            ],
            'nombres_productos' => [
                'table'   => 'nombres_productos',
                'pk'      => 'id_nombre_producto',
                'name'    => 'nombre',
                'parents' => []
            ],
        ];


        if (!isset($MAP[$entity])) {
            $resp["msg"] = "Entidad no permitida.";
            return $resp;
        }
        $conf = $MAP[$entity];

        $nombre = trim($nombre);
        if ($nombre === '') {
            $resp["msg"] = "Nombre vacío.";
            return $resp;
        }

        // --- WHERE dinámico: nombre (case-insensitive) + padres proporcionados ---
        $where = ["LOWER(TRIM({$conf['name']})) = LOWER(TRIM(?))"];
        $types = "s";
        $vals  = [$nombre];

        $usedParents = [];
        foreach ($conf['parents'] as $pcol) {
            if (isset($extra[$pcol]) && $extra[$pcol] !== '' && $extra[$pcol] !== null && (int)$extra[$pcol] > 0) {
                $where[] = "$pcol = ?";
                $types  .= "i";
                $vals[]  = (int)$extra[$pcol];
                $usedParents[] = $pcol;
            }
        }

        if (isset($extra['referencia']) && trim((string)$extra['referencia']) !== '') {
            $where[] = "FIND_IN_SET(?, UPPER(REPLACE(referencia,' ',''))) > 0";
            $types .= "s";
            $vals[] = str_replace(' ', '', strtoupper(trim((string)$extra['referencia'])));
        }

        // --- Buscar existente ---
        $sqlSel = "SELECT {$conf['pk']} FROM {$conf['table']} WHERE " . implode(" AND ", $where) . " LIMIT 1";
        $stmt = $this->conn->prepare($sqlSel);
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && isset($row[$conf['pk']])) {
            $resp["error"] = false;
            $resp["id"] = (int)$row[$conf['pk']];
            return $resp;
        }

        // --- Insertar si no existe ---
        $cols   = [$conf['name']];
        $ph     = ["?"];
        $itypes = "s";
        $ivals  = [$nombre];

        foreach ($usedParents as $pcol) {
            $cols[] = $pcol;
            $ph[] = "?";
            $itypes .= "i";
            $ivals[] = (int)$extra[$pcol];
        }

        // muchas de tus tablas tienen 'referencia' opcional
        if (isset($extra['referencia']) && $extra['referencia'] !== '') {
            $cols[] = 'referencia';
            $ph[] = "?";
            $itypes .= "s";
            $ivals[] = (string)$extra['referencia'];
        }

        // estado por defecto 'A'
        $cols[] = 'estado';
        $ph[] = "'A'"; // literal, no bind

        $sqlIns = "INSERT INTO {$conf['table']} (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")";
        $stmt = $this->conn->prepare($sqlIns);
        if ($itypes !== "") {
            $stmt->bind_param($itypes, ...$ivals);
        }
        $ok = $stmt->execute();
        $newId = $this->conn->insert_id;
        $stmt->close();

        if (!$ok || !$newId) {
            $resp["msg"] = "No se pudo crear el registro.";
            return $resp;
        }

        $resp["error"] = false;
        $resp["id"] = (int)$newId;
        return $resp;
    }

    public function getSubcategoriasByCategorias(array $idsCategorias)
    {
        // Sanitiza y deja únicos
        $idsCategorias2 = array_values(array_unique(array_map('intval', $idsCategorias)));
        if (empty($idsCategorias2)) return [];

        // Placeholders dinámicos
        $placeholders = implode(',', array_fill(0, count($idsCategorias2), '?'));

        // ⚠️ Usa la columna correcta en sub_categorias: id_categoria (no "categoria")
        $sql = "SELECT  
          sc.id_sub_categoria   AS sub_categoria,
          sc.nombre,
          sc.id_categoria
            FROM sub_categorias sc
            WHERE sc.estado = 'A'
              AND sc.id_categoria IN ($placeholders)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // Diagnóstico útil
            error_log("getSubcategoriasByCategorias::prepare failed: " . $this->conn->error . " | SQL: " . $sql);
            return [];
        }

        $types = str_repeat('i', count($idsCategorias2));
        $stmt->bind_param($types, ...$idsCategorias2);

        if (!$stmt->execute()) {
            error_log("getSubcategoriasByCategorias::execute failed: " . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $subcategorias = [];
        while ($row = $result->fetch_assoc()) {
            $subcategorias[] = $row;
        }
        $stmt->close();

        return $subcategorias;
    }

    public function verificar_empresa($id_empresa, $nombre_comercial, $ruc, $cedula, $nombramiento, $patente, $planilla)
    {
        // 1) Verificar si ya existe algún registro para esa empresa
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM verificacion_empresa WHERE id_empresa = ? AND estado = 'A'");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        $hayActivo = (int)($row["total"] ?? 0) > 0;

        // 2) Si existe, poner en estado 'E' los anteriores activos
        if ($hayActivo) {
            $stmt = $this->conn->prepare("UPDATE verificacion_empresa SET estado = 'E' WHERE id_empresa = ? AND estado = 'A'");
            $stmt->bind_param("s", $id_empresa);
            $stmt->execute();
            $stmt->close();
        }

        // 3) Insertar el nuevo registro como Activo (A)
        $stmt = $this->conn->prepare("
        INSERT INTO verificacion_empresa
        (id_empresa, nombre_comercial, ruc, cedula, nombramiento, patente, planilla, estado, rechazo_verificacion_empresa, observacion_verificacion)
        VALUES (?,?,?,?,?,?,?, 'A', '0', '')
    ");
        $stmt->bind_param("sssssss", $id_empresa, $nombre_comercial, $ruc, $cedula, $nombramiento, $patente, $planilla);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $this->notificaEmpresaVerificacionEnProceso($id_empresa);
            $this->notificaFulmuvNuevaVerificacion($id_empresa);
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }



    public function getProductosVendidosHoy($conCantidad = true)
    {
        $baseSelect = $conCantidad
            ? "SELECT p.*, COUNT(*) AS cantidad_vendida"
            : "SELECT DISTINCT p.id_empresa, p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria, p.tipo_creador";

        $sql = "$baseSelect
        FROM ordenes_empresas oe
        JOIN JSON_TABLE(oe.productos, '$[*]' COLUMNS (prod_id INT PATH '$.id')) jt ON TRUE
        JOIN productos p ON p.id_producto = jt.prod_id
        WHERE oe.estado = 'A'
          AND p.estado = 'A'  -- igual que en tu otro SQL
          -- todas las categorías del producto deben ser tipo 'producto' y activas
          AND NOT EXISTS (
                SELECT 1
                FROM categorias c
                WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                  AND (c.estado <> 'A' OR c.tipo <> 'producto')
          )";

        if ($conCantidad) {
            $sql .= "
            GROUP BY p.id_producto, p.nombre, p.img_frontal, p.precio_referencia
            ORDER BY cantidad_vendida DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();

        $productos = [];
        $vistos = [];

        while ($row = $res->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                // Enriquecidos (ya seguros)
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"]   = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]      = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }
        $stmt->close();

        // === Fallback: si no hay vendidos, devuelve 10 productos generales ===
        // (corregido: antes decía $out)
        if (empty($productos)) {
            if ($conCantidad) {
                $sqlFallback = "SELECT p.*, 0 AS cantidad_vendida
                            FROM productos p
                            WHERE p.estado = 'A'
                              -- mismas reglas de categorías que arriba
                              AND NOT EXISTS (
                                    SELECT 1
                                    FROM categorias c
                                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                                      AND (c.estado <> 'A' OR c.tipo <> 'producto')
                              )
                            ORDER BY p.id_producto DESC
                            LIMIT 10";
            } else {
                $sqlFallback = "SELECT p.id_empresa, p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria, p.tipo_creador
                            FROM productos p
                            WHERE p.estado = 'A'
                              -- mismas reglas de categorías que arriba
                              AND NOT EXISTS (
                                    SELECT 1
                                    FROM categorias c
                                    WHERE JSON_CONTAINS(p.categoria, JSON_QUOTE(CAST(c.id_categoria AS CHAR)), '$')
                                      AND (c.estado <> 'A' OR c.tipo <> 'producto')
                              )
                            ORDER BY p.id_producto DESC
                            LIMIT 10";
            }

            $stmt2 = $this->conn->prepare($sqlFallback);
            $stmt2->execute();
            $res2 = $stmt2->get_result();

            while ($row = $res2->fetch_assoc()) {
                $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
                if ($publicEmpresaId <= 0) {
                    continue;
                }

                if (!isset($vistos[$row['id_producto']])) {
                    $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                    $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                    $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                    $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                    $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
                    $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                    $row["verificacion"]   = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                    $row["membresia"]      = $this->getMembresiaByEmpresa($publicEmpresaId);

                    $productos[] = $row;
                    $vistos[$row['id_producto']] = true;
                }
            }
            $stmt2->close();
        }

        return $productos;
    }

    public function getOfertasImperdibles($conCantidad = true)
    {
        // Productos activos con descuento > 0 (sin requerir órdenes ni tipo de categoría)
        $sql = "SELECT p.*, 0 AS cantidad_vendida
                FROM productos p
                WHERE p.estado = 'A'
                  AND CAST(COALESCE(NULLIF(TRIM(p.descuento), ''), '0') AS DECIMAL(10,2)) > 0
                ORDER BY p.id_producto DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();

        $productos = [];
        $vistos = [];

        while ($row = $res->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            if (!isset($vistos[$row['id_producto']])) {
                $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
                $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
                $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
                $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
                $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
                $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
                $row["verificacion"]   = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
                $row["membresia"]      = $this->getMembresiaByEmpresa($publicEmpresaId);

                $productos[] = $row;
                $vistos[$row['id_producto']] = true;
            }
        }
        $stmt->close();

        return $productos;
    }



    // public function getProductosVendidosHoy($conCantidad = true)
    // {
    //     $baseSelect = $conCantidad
    //         ? "SELECT p.*, COUNT(*) AS cantidad_vendida"
    //         : "SELECT DISTINCT p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria";

    //     $sql = "$baseSelect
    //     FROM ordenes_empresas oe
    //     JOIN JSON_TABLE(oe.productos, '$[*]' COLUMNS (prod_id INT PATH '$.id')) jt ON TRUE
    //     JOIN productos p ON p.id_producto = jt.prod_id
    //     WHERE oe.estado = 'A' ";

    //     if ($conCantidad) {
    //         $sql .= " GROUP BY p.id_producto, p.nombre, p.img_frontal, p.precio_referencia
    //               ORDER BY cantidad_vendida DESC";
    //     }

    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();
    //     $res = $stmt->get_result();

    //     $productos = [];
    //     $vistos = [];

    //     while ($row = $res->fetch_assoc()) {
    //         if (!isset($vistos[$row['id_producto']])) {
    //             // Enriquecidos (ya seguros)
    //             $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
    //             $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
    //             $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
    //             $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
    //             $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
    //             $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
    //             $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);

    //             $productos[] = $row;
    //             $vistos[$row['id_producto']] = true;
    //         }
    //     }
    //     $stmt->close();

    //     // === Fallback: si no hay vendidos, devuelve 10 productos generales ===
    //     if (empty($out)) {
    //         if ($conCantidad) {
    //             $sqlFallback = "SELECT p.*, 0 AS cantidad_vendida
    //                         FROM productos p
    //                         WHERE p.estado = 'A'
    //                         ORDER BY p.id_producto DESC
    //                         LIMIT 10";
    //         } else {
    //             $sqlFallback = "SELECT p.id_empresa, p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria
    //                         FROM productos p
    //                         WHERE p.estado = 'A'
    //                         ORDER BY p.id_producto DESC
    //                         LIMIT 10";
    //         }

    //         $stmt2 = $this->conn->prepare($sqlFallback);
    //         $stmt2->execute();
    //         $res2 = $stmt2->get_result();

    //         while ($row = $res2->fetch_assoc()) {
    //             if (!isset($vistos[$row['id_producto']])) {
    //                 // Enriquecidos (ya seguros)
    //                 $row["marca"]          = $this->getMarcaByArray($row["id_marca"]);
    //                 $row["modelo"]         = $this->getModeloByArray($row["id_modelo"]);
    //                 $row["tipo_autoo"]     = $this->getTipoAutoByArray($row["tipo_auto"]);
    //                 $row["tipo_fraccionn"] = $this->getTipoTraccionByArray($row["tipo_traccion"]);
    //                 $row["categorias"]     = $this->getCategoriaByArray($row["categoria"]);
    //                 $row["subcategorias"]  = $this->getSubCategoriaByArray($row["sub_categoria"]);
    //                 $row["verificacion"] = $this->getVerificacionCuentaEmpresa($row["id_empresa"]);

    //                 $productos[] = $row;
    //                 $vistos[$row['id_producto']] = true;
    //             }
    //         }
    //         $stmt2->close();
    //     }

    //     return $productos;
    // }

    public function getServiciosVendidosHoy($conCantidad = true)
    {
        $baseSelect = $conCantidad
            ? "SELECT p.*, COUNT(*) AS cantidad_vendida"
            : "SELECT DISTINCT p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria";

        $sql = "$baseSelect
        FROM ordenes_empresas oe
        JOIN JSON_TABLE(oe.productos, '$[*]' COLUMNS (prod_id INT PATH '$.id')) jt ON TRUE
        JOIN productos p ON p.id_producto = jt.prod_id
        WHERE oe.estado = 'A' ";

        if ($conCantidad) {
            $sql .= " GROUP BY p.id_producto, p.nombre, p.img_frontal, p.precio_referencia
                  ORDER BY cantidad_vendida DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($row = $res->fetch_assoc()) {
            if (isset($row["categoria"])) {
                $row["categorias"] = $this->getCategoriaByArray($row["categoria"]);
            }
            $out[] = $row;
        }
        $stmt->close();

        // === Fallback: si no hay vendidos, devuelve 10 productos generales ===
        if (empty($out)) {
            if ($conCantidad) {
                $sqlFallback = "SELECT p.*, 0 AS cantidad_vendida
                            FROM productos p
                            WHERE p.estado = 'A'
                            ORDER BY p.id_producto DESC
                            LIMIT 10";
            } else {
                $sqlFallback = "SELECT p.id_producto, p.nombre, p.img_frontal, p.precio_referencia, p.categoria
                            FROM productos p
                            WHERE p.estado = 'A'
                            ORDER BY p.id_producto DESC
                            LIMIT 10";
            }

            $stmt2 = $this->conn->prepare($sqlFallback);
            $stmt2->execute();
            $res2 = $stmt2->get_result();

            while ($row = $res2->fetch_assoc()) {
                if (isset($row["categoria"])) {
                    $row["categorias"] = $this->getCategoriaByArray($row["categoria"]);
                }
                $out[] = $row;
            }
            $stmt2->close();
        }

        return $out;
    }


    // public function generarFacturaEmpresa($id_empresa, $valor, $membresia_nombre, $dias)
    // {
    //     if ($dias == 30) {
    //         $producto_id = "MEeg6475FAwX6dQ5"; //mensual
    //         $producto_nombre = "Plan Mensual";
    //         $producto_descripcion = "Servicio de comercio electrónico plan mensual";
    //     } else if ($dias == 180) {
    //         $producto_id = "loejL4AJCnGLEbQM"; //semestral
    //         $producto_nombre = "Plan Semestral";
    //         $producto_descripcion = "Servicio de comercio electrónico plan semestral";
    //     } else if ($dias == 365) {
    //         $producto_id = "Y4erX4goH8nKMb2L"; //anual
    //         $producto_nombre = "Plan Anual";
    //         $producto_descripcion = "Servicio de comercio electrónico plan anual";
    //     }

    //     $empresa = $this->getEmpresaById($id_empresa);
    //     //$numero_factura = "001-007-000000007";

    //     // Configurar zona horaria de Ecuador
    //     date_default_timezone_set('America/Guayaquil');

    //     // Generar la fecha actual en formato dd/mm/YYYY
    //     $fecha_emision = date("d/m/Y");

    //     // Inicializamos valores
    //     $ruc = null;
    //     $cedula = null;

    //     // Verificamos el tipo de identificación
    //     if ($empresa["tipo_identificacion"] === "ruc") {
    //         $ruc = $empresa["cedula_ruc"];
    //     } elseif ($empresa["tipo_identificacion"] === "cedula") {
    //         $cedula = $empresa["cedula_ruc"];
    //     }

    //     // IVA incluido en $valor
    //     $porcentajeIva = 15; // 15%
    //     $total_con_iva = round((float)$valor, 2);

    //     // extraer base e IVA desde el total
    //     $base = round($total_con_iva / (1 + $porcentajeIva / 100), 2);

    //     // calcular IVA como diferencia para cuadrar exactamente con 2 decimales
    //     $iva = round($total_con_iva - $base, 2);

    //     // si por redondeo queda un centavo suelto, lo ajustamos en la base
    //     if (round($base + $iva, 2) !== $total_con_iva) {
    //         $base = round($total_con_iva - $iva, 2);
    //     }

    //     //numero factura
    //     $query = "SELECT numero_factura 
    //       FROM facturas 
    //       ORDER BY id_factura DESC 
    //       LIMIT 1";
    //     $result = $this->conn->query($query);
    //     $lastFactura = $result->fetch_assoc();

    //     if ($lastFactura && !empty($lastFactura['numero_factura'])) {
    //         $ultimoNumero = $lastFactura['numero_factura'];
    //     }

    //     // Separar por guiones
    //     list($establecimiento, $punto, $secuencial) = explode("-", $ultimoNumero);

    //     // Aumentar el secuencial
    //     $nuevoSecuencial = str_pad(((int)$secuencial + 1), 9, "0", STR_PAD_LEFT);

    //     // Construir el nuevo número de factura
    //     $numero_factura = $establecimiento . "-" . $punto . "-" . $nuevoSecuencial;

    //     $data = [
    //         "pos" => "58799fc1-67a9-4ef9-b3dc-1add00a8288c",
    //         "fecha_emision" => $fecha_emision,
    //         "tipo_documento" => "FAC",
    //         "documento" => $numero_factura,
    //         "estado" => "P",
    //         "autorizacion" => "",
    //         "electronico" => true,
    //         "caja_id" => null,
    //         "cliente" => [
    //             "ruc" => $ruc,
    //             "cedula" => $cedula,
    //             "razon_social" => $empresa["razon_social"],
    //             "telefonos" => $empresa["telefono_contacto"],
    //             "direccion" => $empresa["direccion_facturacion"],
    //             "tipo" => "N",
    //             "email" => $empresa["correo"],
    //             "es_extranjero" => false
    //         ],
    //         "vendedor" => null,
    //         "descripcion" => $producto_descripcion,
    //         "subtotal_0" => 0.00,
    //         "subtotal_12" => $base,     // base gravada
    //         "iva" => $iva,              // 15% de $valor
    //         "ice" => 0.00,
    //         "servicio" => 0.00,
    //         "total" => $total_con_iva,          // base + IVA
    //         "detalles" => [[
    //             "producto_id" => $producto_id,
    //             "producto_nombre" => $producto_nombre,
    //             "cantidad" => "1.0",
    //             "precio" => $base,        // precio sin IVA
    //             "porcentaje_descuento" => "0.0",
    //             "porcentaje_iva" => $porcentajeIva,
    //             "porcentaje_ice" => null,
    //             "valor_ice" => "0.0",
    //             "base_cero" => "0.0",
    //             "base_gravable" => $base, // coincide con subtotal_12
    //             "base_no_gravable" => "0.0"
    //         ]]
    //     ];

    //     // var_dump($data);


    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/documento/',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => json_encode($data),
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json',
    //             'Authorization: ELPAz3khSjp7kh4Dqnu9kjK7D4R7WEC8bBD2k2yXcrU'
    //         ),
    //     ));

    //     $respons = curl_exec($curl);
    //     $respons = json_decode($respons, true);
    //     // var_dump($respons);
    //     curl_close($curl);


    //     $stmt = $this->conn->prepare("INSERT INTO facturas( id_factura_contifico, id_cliente, numero_factura, descripcion, tipo) values(?,?,?,'membresia','E')");
    //     $stmt->bind_param("sss",  $respons["id"], $id_empresa, $respons["documento"]);
    //     $result = $stmt->execute();
    //     $stmt->close();
    //     if ($result) {
    //         return RECORD_CREATED_SUCCESSFULLY;
    //     } else {
    //         return RECORD_CREATION_FAILED;
    //     }
    // }

    private function ensureSecuenciasFacturacionTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS secuencias_facturacion (
            id_secuencia INT AUTO_INCREMENT PRIMARY KEY,
            establecimiento VARCHAR(3) NOT NULL,
            punto_emision VARCHAR(3) NOT NULL,
            ultimo_secuencial BIGINT NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_establecimiento_punto (establecimiento, punto_emision)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        return $this->conn->query($sql);
    }

    private function obtenerUltimoNumeroFacturaLocal($numeroDefault = "001-007-000000000")
    {
        $query = "SELECT numero_factura
                  FROM facturas
                  WHERE numero_factura IS NOT NULL AND numero_factura <> ''
                  ORDER BY id_factura DESC
                  LIMIT 1";
        $result = $this->conn->query($query);
        if ($result === false) {
            return [
                "error" => true,
                "msg" => "Error al consultar el ultimo numero de factura local: " . $this->conn->error
            ];
        }

        $lastFactura = $result->fetch_assoc();
        if ($lastFactura && !empty($lastFactura['numero_factura'])) {
            return [
                "error" => false,
                "numero_factura" => $lastFactura['numero_factura']
            ];
        }

        return [
            "error" => false,
            "numero_factura" => $numeroDefault
        ];
    }

    private function obtenerSiguienteDocumentoFacturaSeguro($establecimiento = "001", $punto = "007")
    {
        if (!$this->ensureSecuenciasFacturacionTable()) {
            return [
                "error" => true,
                "source" => "bd",
                "msg" => "No se pudo asegurar la tabla de secuencias de facturacion: " . $this->conn->error
            ];
        }

        try {
            $this->conn->begin_transaction();

            $stmt = $this->conn->prepare("SELECT ultimo_secuencial
                FROM secuencias_facturacion
                WHERE establecimiento = ? AND punto_emision = ?
                FOR UPDATE");
            if (!$stmt) {
                throw new Exception("No se pudo preparar la consulta de secuencia: " . $this->conn->error);
            }
            $stmt->bind_param("ss", $establecimiento, $punto);
            if (!$stmt->execute()) {
                $errorStmt = $stmt->error;
                $stmt->close();
                throw new Exception("No se pudo consultar la secuencia de facturacion: " . $errorStmt);
            }

            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) {
                $ultimoLocal = $this->obtenerUltimoNumeroFacturaLocal($establecimiento . "-" . $punto . "-000000000");
                if (!empty($ultimoLocal["error"])) {
                    throw new Exception($ultimoLocal["msg"] ?? "No se pudo obtener el ultimo numero de factura local.");
                }

                $numeroLocal = $ultimoLocal["numero_factura"] ?? ($establecimiento . "-" . $punto . "-000000000");
                $partesLocal = explode("-", $numeroLocal);
                if (count($partesLocal) !== 3) {
                    throw new Exception("El numero de factura local no tiene un formato valido.");
                }

                $ultimoSecuencial = 0;
                if ($partesLocal[0] === $establecimiento && $partesLocal[1] === $punto) {
                    $ultimoSecuencial = (int)$partesLocal[2];
                }

                $stmtInsert = $this->conn->prepare("INSERT INTO secuencias_facturacion (establecimiento, punto_emision, ultimo_secuencial)
                    VALUES (?, ?, ?)");
                if (!$stmtInsert) {
                    throw new Exception("No se pudo preparar la creacion de secuencia: " . $this->conn->error);
                }
                $stmtInsert->bind_param("ssi", $establecimiento, $punto, $ultimoSecuencial);
                if (!$stmtInsert->execute()) {
                    $errorStmt = $stmtInsert->error;
                    $stmtInsert->close();
                    throw new Exception("No se pudo crear la secuencia de facturacion: " . $errorStmt);
                }
                $stmtInsert->close();
                $row = ["ultimo_secuencial" => $ultimoSecuencial];
            }

            $nuevoSecuencial = ((int)$row["ultimo_secuencial"]) + 1;

            $stmtUpdate = $this->conn->prepare("UPDATE secuencias_facturacion
                SET ultimo_secuencial = ?
                WHERE establecimiento = ? AND punto_emision = ?");
            if (!$stmtUpdate) {
                throw new Exception("No se pudo preparar la actualizacion de secuencia: " . $this->conn->error);
            }
            $stmtUpdate->bind_param("iss", $nuevoSecuencial, $establecimiento, $punto);
            if (!$stmtUpdate->execute()) {
                $errorStmt = $stmtUpdate->error;
                $stmtUpdate->close();
                throw new Exception("No se pudo actualizar la secuencia de facturacion: " . $errorStmt);
            }
            $stmtUpdate->close();

            $this->conn->commit();

            return [
                "error" => false,
                "documento" => $establecimiento . "-" . $punto . "-" . str_pad((string)$nuevoSecuencial, 9, "0", STR_PAD_LEFT)
            ];
        } catch (\Throwable $e) {
            $this->conn->rollback();
            return [
                "error" => true,
                "source" => "bd",
                "msg" => $e->getMessage()
            ];
        }
    }

    public function generarFacturaEmpresa($id_empresa, $valor, $membresia_nombre, $dias, $facturacionOverrides = [], $invoiceContext = [])
    {
        // Normalizar nombre del plan
        $mn = strtolower($membresia_nombre ?? '');
        if (strpos($mn, 'onemuv') !== false)        $plan_key = 'ONEMUV';
        elseif (strpos($mn, 'basicmuv') !== false)  $plan_key = 'BASICMUV';
        elseif (strpos($mn, 'fulmuv') !== false)    $plan_key = 'FULMUV';
        else                                    $plan_key = strtoupper($membresia_nombre);

        // Periodo según días
        if ($dias == 30) {
            $periodo = 'mensual';
        } elseif ($dias == 180) {
            $periodo = 'semestral';
        } else {
            $periodo = 'anual';
            $dias = 365;
        }

        $producto_nombre      = "Factura de primer plan contratado";
        $producto_descripcion = "Servicio de intermediación comercial y publicidad digital en plataforma FULMUV – Plan {$plan_key} – {$periodo} ({$dias})";

        if ($dias == 30) {
            $producto_id = "MEeg6475FAwX6dQ5"; //mensual
        } else if ($dias == 180) {
            $producto_id = "loejL4AJCnGLEbQM"; //semestral
        } else {
            $producto_id = "Y4erX4goH8nKMb2L"; //anual
        }

        $esUpgrade = strtolower((string)($invoiceContext["contexto"] ?? '')) === 'upgrade';
        if ($esUpgrade) {
            $producto_nombre = "Upgrade membresia";
            $producto_descripcion = "Servicio de intermediacion comercial y publicidad digital en plataforma FULMUV - Upgrade a Plan {$plan_key} - {$periodo} ({$dias})";
        }

        $empresa = $this->getEmpresaById($id_empresa);
        //$numero_factura = "001-007-000000007";

        // Configurar zona horaria de Ecuador
        date_default_timezone_set('America/Guayaquil');

        // Generar la fecha actual en formato dd/mm/YYYY
        $fecha_emision = date("d/m/Y");

        // Inicializamos valores
        $ruc = null;
        $cedula = null;
        $tipo_cliente = "N"; // default Persona Natural

        // Verificamos el tipo de identificación (case-insensitive)
        $tipo_id = strtolower(trim($empresa["tipo_identificacion"] ?? ''));
        if ($tipo_id === "ruc") {
            $ruc = $empresa["cedula_ruc"];
            $tipo_cliente = "R";
        } elseif ($tipo_id === "cedula") {
            $cedula = $empresa["cedula_ruc"];
            $tipo_cliente = "N";
        } elseif ($tipo_id === "pasaporte" || $tipo_id === "passport") {
            $cedula = $empresa["cedula_ruc"];
            $tipo_cliente = "P";
        }

        // IVA incluido en $valor
        $porcentajeIva = 15; // 15%
        $total_con_iva = round((float)$valor, 2);

        // extraer base e IVA desde el total
        $base = round($total_con_iva / (1 + $porcentajeIva / 100), 2);

        // calcular IVA como diferencia para cuadrar exactamente con 2 decimales
        $iva = round($total_con_iva - $base, 2);

        // si por redondeo queda un centavo suelto, lo ajustamos en la base
        if (round($base + $iva, 2) !== $total_con_iva) {
            $base = round($total_con_iva - $iva, 2);
        }

        $razonSocialFacturacion = trim((string)($facturacionOverrides["razon_social"] ?? $empresa["razon_social"] ?? ''));
        $tipoIdFacturacion = strtolower(trim((string)($facturacionOverrides["tipo_identificacion"] ?? $empresa["tipo_identificacion"] ?? '')));
        $cedulaRucFacturacion = trim((string)($facturacionOverrides["cedula_ruc"] ?? $empresa["cedula_ruc"] ?? ''));

        if (empty($empresa) || $razonSocialFacturacion === '') {
            return [
                "error" => true,
                "source" => "bd",
                "msg" => "No fue posible obtener los datos de facturacion de la empresa."
            ];
        }
        $telefonoFacturacion = trim((string)($facturacionOverrides["telefono_facturacion"] ?? $empresa["telefono_facturacion"] ?? $empresa["telefono_contacto"] ?? ''));
        $correoFacturacion = trim((string)($facturacionOverrides["correo_facturacion"] ?? $empresa["correo_facturacion"] ?? $empresa["correo"] ?? ''));
        $direccionFacturacion = trim((string)($facturacionOverrides["direccion_facturacion"] ?? $empresa["direccion_facturacion"] ?? ''));
        $facturacionPayload = [
            "razon_social" => $razonSocialFacturacion,
            "tipo_identificacion" => $tipoIdFacturacion,
            "cedula_ruc" => $cedulaRucFacturacion,
            "telefono_facturacion" => $telefonoFacturacion,
            "correo_facturacion" => $correoFacturacion,
            "direccion_facturacion" => $direccionFacturacion
        ];
        if ($tipoIdFacturacion === "ruc") {
            $ruc = $cedulaRucFacturacion;
            $cedula = null;
            $tipo_cliente = "R";
        } elseif ($tipoIdFacturacion === "cedula") {
            $cedula = $cedulaRucFacturacion;
            $ruc = null;
            $tipo_cliente = "N";
        } elseif ($tipoIdFacturacion === "pasaporte" || $tipoIdFacturacion === "passport") {
            $cedula = $cedulaRucFacturacion;
            $ruc = null;
            $tipo_cliente = "P";
        }

        $facturaDebug = [
            "tipo_identificacion_recibido" => $tipoIdFacturacion,
            "cedula_ruc_recibido" => $cedulaRucFacturacion,
            "ruc_enviado" => $ruc,
            "cedula_enviada" => $cedula
        ];

        $dataBase = [
            "pos" => "58799fc1-67a9-4ef9-b3dc-1add00a8288c",
            "fecha_emision" => $fecha_emision,
            "tipo_documento" => "FAC",
            "estado" => "P",
            "autorizacion" => "",
            "electronico" => true,
            "caja_id" => null,
            "cliente" => [
                "ruc" => $ruc,
                "cedula" => $cedula,
                "razon_social" => $razonSocialFacturacion,
                "telefonos" => $telefonoFacturacion,
                "direccion" => $direccionFacturacion,
                "tipo" => $tipo_cliente,
                "email" => $correoFacturacion,
                "es_extranjero" => false
            ],
            "vendedor" => null,
            "descripcion" => $producto_descripcion,
            "subtotal_0" => 0.00,
            "subtotal_12" => $base,     // base gravada
            "iva" => $iva,              // 15% de $valor
            "ice" => 0.00,
            "servicio" => 0.00,
            "total" => $total_con_iva,          // base + IVA
            "detalles" => [[
                "producto_id" => $producto_id,
                "producto_nombre" => $producto_nombre,
                "cantidad" => "1.0",
                "precio" => $base,        // precio sin IVA
                "porcentaje_descuento" => "0.0",
                "porcentaje_iva" => $porcentajeIva,
                "porcentaje_ice" => null,
                "valor_ice" => "0.0",
                "base_cero" => "0.0",
                "base_gravable" => $base, // coincide con subtotal_12
                "base_no_gravable" => "0.0"
            ]]
        ];

        $personaSync = $this->syncPersonaContificoCliente($facturacionPayload);
        if (!empty($personaSync["error"])) {
            $facturaDebug["persona_warning"] = $personaSync["msg"] ?? "No se pudo sincronizar la persona en Contifico.";
            $facturaDebug["persona_sync_error"] = [
                "persona" => $personaSync["debug"] ?? null,
                "raw" => $personaSync["raw"] ?? null,
                "http_code" => $personaSync["http_code"] ?? null
            ];
        } elseif (!empty($personaSync["warning"])) {
            $facturaDebug["persona_warning"] = $personaSync["warning"];
        }

        $maxIntentos = 5;
        $ultimoErrorContifico = null;

        for ($intento = 1; $intento <= $maxIntentos; $intento++) {
            $documentoSeguro = $this->obtenerSiguienteDocumentoFacturaSeguro("001", "007");
            if (!empty($documentoSeguro["error"])) {
                return $documentoSeguro;
            }

            $numero_factura = $documentoSeguro["documento"];
            $data = $dataBase;
            $data["documento"] = $numero_factura;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.contifico.com/sistema/api/v1/documento/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: ELPAz3khSjp7kh4Dqnu9kjK7D4R7WEC8bBD2k2yXcrU'
                ),
            ));

            $respuestaCruda = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            curl_close($curl);

            if ($respuestaCruda === false) {
                return [
                    "error" => true,
                    "source" => "contifico",
                    "msg" => "No fue posible conectar con Contifico: " . ($curlError ?: ("cURL error " . $curlErrno))
                ];
            }

            $respons = json_decode($respuestaCruda, true);
            if (!is_array($respons)) {
                return [
                    "error" => true,
                    "source" => "contifico",
                    "msg" => "Contifico devolvio una respuesta invalida. HTTP " . $httpCode . ". Respuesta: " . substr((string)$respuestaCruda, 0, 300)
                ];
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                $mensajeContifico = $respons["mensaje"] ?? $respons["message"] ?? $respons["error"] ?? $respons["detail"] ?? json_encode($respons, JSON_UNESCAPED_UNICODE);
                $codigoErrorContifico = $respons["cod_error"] ?? null;
                $esDocumentoDuplicado = $httpCode === 409
                    || (string)$codigoErrorContifico === "1001"
                    || stripos((string)$mensajeContifico, "Documento ya existe") !== false;

                $ultimoErrorContifico = "Contifico rechazo la factura. HTTP " . $httpCode . ": " . $mensajeContifico;

                if ($esDocumentoDuplicado && $intento < $maxIntentos) {
                    continue;
                }

                if ($esDocumentoDuplicado) {
                    $ultimoErrorContifico .= " Se agotaron los reintentos para generar un documento unico.";
                }

                return [
                    "error" => true,
                    "source" => "contifico",
                    "msg" => $ultimoErrorContifico,
                    "debug" => [
                        "factura" => $facturaDebug
                    ]
                ];
            }

            if (empty($respons["id"]) || empty($respons["documento"])) {
                return [
                    "error" => true,
                    "source" => "contifico",
                    "msg" => "Contifico no devolvio los datos esperados de la factura.",
                    "debug" => [
                        "factura" => $facturaDebug
                    ]
                ];
            }

            $this->ensureFacturaBillingColumns();

            $facturaTema = $esUpgrade ? 'Upgrade membresia' : 'membresia';
            $facturaColumns = [
                'id_factura_contifico',
                'id_cliente',
                'numero_factura',
                'descripcion',
                'tipo',
                'razon_social',
                '`cedula/ruc`',
                'direccion'
            ];
            $facturaValues = ['?', '?', '?', '?', "'E'", '?', '?', '?'];
            $facturaParams = [
                $respons["id"],
                $id_empresa,
                $respons["documento"],
                $facturaTema,
                $razonSocialFacturacion,
                $cedulaRucFacturacion,
                $direccionFacturacion
            ];

            if ($this->tableHasColumn('facturas', 'telefono_facturacion')) {
                $facturaColumns[] = 'telefono_facturacion';
                $facturaValues[] = '?';
                $facturaParams[] = $telefonoFacturacion;
            }

            if ($this->tableHasColumn('facturas', 'correo_facturacion')) {
                $facturaColumns[] = 'correo_facturacion';
                $facturaValues[] = '?';
                $facturaParams[] = $correoFacturacion;
            }

            $sqlFacturaLocal = "INSERT INTO facturas(" . implode(', ', $facturaColumns) . ") VALUES(" . implode(', ', $facturaValues) . ")";
            $stmt = $this->conn->prepare($sqlFacturaLocal);
            if (!$stmt) {
                return [
                    "error" => true,
                    "source" => "bd",
                    "msg" => "Error al preparar el guardado local de la factura: " . $this->conn->error,
                    "debug" => [
                        "factura" => $facturaDebug,
                        "sql" => $sqlFacturaLocal
                    ]
                ];
            }
            $stmt->bind_param(str_repeat('s', count($facturaParams)), ...$facturaParams);
            $result = $stmt->execute();
            $stmtError = $stmt->error;
            $stmt->close();
            if ($result) {
                return [
                    "error" => false,
                    "source" => "factura",
                    "msg" => "Factura generada correctamente.",
                    "id_factura_contifico" => $respons["id"],
                    "documento" => $respons["documento"],
                    "debug" => [
                        "factura" => $facturaDebug
                    ]
                ];
            }

            return [
                "error" => true,
                "source" => "bd",
                "msg" => "La factura se genero en Contifico, pero no se pudo guardar en la base de datos: " . $stmtError,
                "debug" => [
                    "factura" => $facturaDebug,
                    "sql" => $sqlFacturaLocal
                ]
            ];
        }

        return [
            "error" => true,
            "source" => "contifico",
            "msg" => $ultimoErrorContifico ?: "No fue posible generar un documento unico para la factura."
        ];
    }
    public function pagoOrdenEmpresa($orden, $imagenArray)
    {
        foreach ($imagenArray as $imagen) {
            $stmt = $this->conn->prepare("INSERT INTO pagos_ordenes(id_orden, imagen) values(?,?)");
            $stmt->bind_param("ss",  $orden, $imagen["archivo"]);
            $result = $stmt->execute();
        }

        $stmt->close();
        if ($result) {
            $this->updateOrdenEmpresaPago($orden);

            //CORREO DE CONFIRMACIÓN DE PAGO REALIZADO
            //$this->sendEmailPagoFulmuv($orden, $imagenArray);
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function updateOrdenEmpresaPago($id_orden)
    {
        $stmt = $this->conn->prepare("UPDATE ordenes_iso SET estado_envio = 1 WHERE id_orden_empresa = ?");
        $stmt->bind_param("s",  $id_orden);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function tienePlanFulmuvActivo($id_empresa)
    {
        $sql = "
            SELECT m.nombre AS plan, me.fecha_fin
            FROM membresias_empresas me
            INNER JOIN membresias m ON m.id_membresia = me.id_membresia
            WHERE me.id_empresa = ?
            AND me.estado = 'A'
            AND m.estado  = 'A'
            AND NOW() <= me.fecha_fin
            ORDER BY me.fecha_fin DESC
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return false;

        // Normaliza para comparar sin tildes/mayúsculas
        $plan = (string) $row['plan'];
        $norm = function (string $s): string {
            $s = mb_strtolower($s, 'UTF-8');
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
            return trim($s);
        };

        return $norm($plan) === 'fulmuv';
    }


    public function getOrdenCliente($id_cliente)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM ordenes
        WHERE id_cliente = ? AND estado = 'A';");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getIndexMarca()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM marcas_productos mp
        WHERE mp.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }

    public function getTipoEvento()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipos_eventos te
        WHERE te.estado = 'A'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }

    public function getSubTipoEvento($id_tipo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM subtipo_eventos te
        WHERE te.estado = 'A' and id_tipo = '$id_tipo'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            $response[] = $row;
        }
        return $response;
    }

    public function getGaleriaByEvento($id_evento)
    {
        $rows = [];
        $stmt = $this->conn->prepare("SELECT id_galeria, imagen FROM galerias_eventos WHERE estado='A' AND id_evento=? ORDER BY id_galeria ASC");
        $stmt->bind_param("i", $id_evento);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    public function deleteGaleriaEvento($id_galeria)
    {
        $stmt = $this->conn->prepare("UPDATE galerias_eventos SET estado='E' WHERE id_galeria = ?");
        if (!$stmt) {
            return RECORD_UPDATED_FAILED;
        }

        $stmt->bind_param("i", $id_galeria);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    // public function getCiudadesAgencia()
    // {
    //     $params = rawurlencode(json_encode(["changethemove.sas", "123456"]));
    //     $url       = "https://181.39.87.158:8021/api/ciudades/{$params}";

    //     // Archivo temporal para el log VERBOSE de cURL
    //     $verboseTmp = fopen('php://temp', 'w+');

    //     $headers = []; // headers de respuesta capturados línea a línea
    //     $ch = curl_init();
    //     curl_setopt_array($ch, [
    //         CURLOPT_URL            => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_MAXREDIRS      => 5,
    //         CURLOPT_TIMEOUT        => 25,
    //         CURLOPT_CUSTOMREQUEST  => 'GET',
    //         CURLOPT_HTTPHEADER     => ['Accept: application/json'],

    //         // ⚠️ Solo para pruebas si el cert es self-signed o usas IP
    //         CURLOPT_SSL_VERIFYPEER => false,
    //         CURLOPT_SSL_VERIFYHOST => 0,

    //         // Capturar headers de respuesta
    //         CURLOPT_HEADERFUNCTION => function ($curl, $headerLine) use (&$headers) {
    //             $len = strlen($headerLine);
    //             $headerLine = trim($headerLine);
    //             if ($headerLine === '') return $len;
    //             $parts = explode(':', $headerLine, 2);
    //             if (count($parts) === 2) {
    //                 $headers[trim($parts[0])] = trim($parts[1]);
    //             } else {
    //                 // primera línea "HTTP/1.1 200 OK"
    //                 $headers['_status_line'] = $headerLine;
    //             }
    //             return $len;
    //         },

    //         // Log de bajo nivel de cURL (muy útil)
    //         CURLOPT_VERBOSE => true,
    //         CURLOPT_STDERR  => $verboseTmp,
    //     ]);

    //     $body = curl_exec($ch);

    //     var_dump($body);

    //     $curlErr     = $body === false ? curl_error($ch) : null;
    //     $curlErrNo   = $body === false ? curl_errno($ch) : 0;
    //     $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    //     curl_close($ch);

    //     // Obtener el verbose log como string
    //     rewind($verboseTmp);
    //     $verboseLog = stream_get_contents($verboseTmp);
    //     fclose($verboseTmp);

    //     // Normalizamos el body y probamos decodificar JSON
    //     $clean = is_string($body) ? trim($body, "\xEF\xBB\xBF \t\n\r\0\x0B") : '';
    //     var_dump($clean);
    //     $json  = null;
    //     if ($clean !== '') {
    //         $json = json_decode($clean, true);
    //     }
    //     $jsonError = json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : null;

    //     // Si la API envía un contenedor de error estándar (ej.: {error:true,msg:"..."})
    //     $apiError = null;
    //     if (is_array($json)) {
    //         if (array_key_exists('error', $json) && $json['error']) {
    //             $apiError = $json['msg'] ?? $json['message'] ?? 'Error reportado por API';
    //         }
    //     }

    //     // Construimos un paquete completo de diagnóstico
    //     $result = [
    //         'ok'          => ($curlErr === null && $httpCode >= 200 && $httpCode < 300),
    //         'http_code'   => $httpCode,
    //         'content_type' => $contentType,
    //         'headers'     => $headers,
    //         'curl_error'  => $curlErr,
    //         'curl_errno'  => $curlErrNo,
    //         'json_error'  => $jsonError,
    //         'api_error'   => $apiError,
    //         'body_raw'    => $clean,
    //         'json'        => $json,
    //         'verbose'     => $verboseLog,
    //         // Si quieres devolver solo los datos cuando todo va bien:
    //         'data'        => (is_array($json) && array_is_list($json)) ? $json
    //             : ((is_array($json) && isset($json['data']) && is_array($json['data'])) ? $json['data'] : []),
    //     ];

    //     // Opcional: loguear automáticamente si hay error
    //     if (!$result['ok']) {
    //         error_log('[getCiudadesAgencia] ERROR => ' . json_encode([
    //             'http'   => $httpCode,
    //             'curl'   => $curlErr,
    //             'api'    => $apiError,
    //             'jerror' => $jsonError,
    //         ], JSON_UNESCAPED_UNICODE));
    //         // También puedes guardar $result['verbose'] a un archivo si lo necesitas persistente
    //     }

    //     return $result;
    // }

    public function getCiudadesAgencia(): array
    {
        $ch = curl_init();

        // OJO: la API del ejemplo acepta el JSON literal en el path (como en Postman)
        $params = rawurlencode(json_encode(["changethemove.sas", "123456"]));
        $url = "https://181.39.87.158:8021/api/ciudades/{$params}";

        // Opcional: si tienes dominio, úsalo:
        // $url = "https://api.midominio.com:8021/api/ciudades/{$paramsRaw}";

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            // tiempos: separa conexión de transferencia
            CURLOPT_CONNECTTIMEOUT => 10,   // tiempo para ESTABLECER la conexión
            CURLOPT_TIMEOUT        => 30,   // tiempo total de la operación
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],

            // Debug (dejar en true solo para pruebas):
            CURLOPT_VERBOSE        => true,

            // Si el cert es self-signed o CN != IP (solo pruebas):
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,

            // Forzar IPv4
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,

            // TLS 1.2 mínimo (algunas pilas lo requieren)
            // CURLOPT_SSLVERSION   => CURL_SSLVERSION_TLSv1_2,
        ]);

        // Si el servidor necesita SNI/Host pero usas IP, puedes "simular" Host:
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Accept: application/json',
        //     'Host: api.midominio.com'
        // ]);
        // Y forzar la resolución del Host a la IP:
        // curl_setopt($ch, CURLOPT_RESOLVE, ['api.midominio.com:8021:181.39.87.158']);

        ob_start(); // capturar salida VERBOSE
        $body = curl_exec($ch);
        $verboseLog = ob_get_clean();

        $errNo = curl_errno($ch);
        $err   = curl_error($ch);
        $info  = curl_getinfo($ch);
        curl_close($ch);

        if ($errNo) {
            error_log("cURL errno {$errNo}: {$err}");
            error_log("cURL info: " . json_encode($info));
            error_log("cURL verbose:\n" . $verboseLog);
            return [];
        }

        // HTTP!=200 -> mostrar por qué
        if (($info['http_code'] ?? 0) < 200 || ($info['http_code'] ?? 0) >= 300) {
            error_log("HTTP {$info['http_code']} al llamar {$url}");
            error_log("Respuesta cruda: " . substr((string)$body, 0, 1000));
            return [];
        }

        // Limpia BOM/espacios
        $clean = trim((string)$body, "\xEF\xBB\xBF \t\n\r\0\x0B");
        $json = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            error_log('Payload: ' . substr($clean, 0, 1000));
            return [];
        }

        // Si viene {data:[...]} o directamente [...]
        if (is_array($json) && array_key_exists('data', $json)) {
            return $json['data'] ?? [];
        }
        return is_array($json) ? $json : [];
    }



    function getCiudadesAgenciaDA()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://181.39.87.158:8021/api/ciudades/["changethemove.sas","123456"]',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function getTrayectos($tipo)
    {
        $rows = [];
        $stmt = $this->conn->prepare("SELECT * FROM trayecto WHERE estado='A' AND tipo = ?;");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    //     public function getTrayectosAll()
    // {
    //     $rows = [];
    //     $stmt = $this->conn->prepare("SELECT * FROM trayecto WHERE estado='A'");
    //     // $stmt->bind_param("s", $tipo);
    //     $stmt->execute();
    //     $res = $stmt->get_result();
    //     while ($r = $res->fetch_assoc()) {
    //         $rows[] = $r;
    //     }
    //     return $rows;
    // }

    public function guardarTrayecto($id_orden, $trayecto)
    {
        $stmt = $this->conn->prepare("UPDATE ordenes_empresas SET id_trayecto=? WHERE id_orden = ?");
        $stmt->bind_param("ss", $trayecto, $id_orden);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }


    public function crearGuiaGrupoEntrega(
        $razon_social,
        $nombre_cliente,
        $direccion,
        $sector_destino,
        $telefono_destino,
        $contenido,
        $numero_piezas,
        $valor_mercancia,
        $valor_asegurado,
        $peso_fisico,
        $latitud,
        $longitud,
        $idOrdenEmpresa
    ): array {

        // 1) MASTER
        $master = $this->crearGuiaGrupoEntregaMaster(
            $razon_social,
            $nombre_cliente,
            $direccion,
            $sector_destino,
            $telefono_destino,
            $contenido,
            $numero_piezas,
            $valor_mercancia,
            $valor_asegurado,
            $peso_fisico,
            $latitud,
            $longitud
        );

        $guiaMasterId = $master['guiaMasterId'] ?? null;
        $okMaster     = isset($master['result']) && strtoupper(trim((string)$master['result'])) === 'OK';

        if (!$okMaster || empty($guiaMasterId)) {
            return [
                'error'  => true,
                'msg'    => 'No se pudo crear la guía MASTER.',
                'master' => $master,
                'hija'   => null,
                'barcode' => null,
                'labelUrl' => null,
                'barcodePickup' => null,
            ];
        }

        // 2) HIJA (Entrega/Recolección)
        $resEntrega = $this->apiGuiaEntregaRecoleccion($guiaMasterId, (int)$idOrdenEmpresa);

        if (!empty($resEntrega['error'])) {
            return [
                'error' => true,
                'msg'   => 'MASTER creada pero falló api_guiaEntregaRecoleccion',
                'guiaMasterId' => $guiaMasterId,
                'master' => $master,
                'raw'    => $resEntrega
            ];
        }

        // Datos clave
        $barcodeEntrega = $resEntrega['barcode_entrega'] ?? null;
        $labelUrl       = $resEntrega['label_entrega'] ?? null;
        $barcodePickup  = $resEntrega['barcode_pickup'] ?? null;

        return [
            'error'         => false,
            'msg'           => 'Guía MASTER y Guía HIJA creadas.',
            'guiaMasterId'  => $guiaMasterId,
            'master'        => $master,

            // claves para guardar en DB
            'barcode'       => $barcodeEntrega,
            'labelUrl'      => $labelUrl,
            'barcodePickup' => $barcodePickup,

            // para depurar
            'rawEntrega'    => $resEntrega['raw'] ?? null
        ];
    }

    // private function apiGuiaEntregaRecoleccion(int $guiaMasterId, int $idOrdenEmpresa): array
    // {
    //     date_default_timezone_set('America/Guayaquil');

    //     // =========================
    //     // Helpers internos
    //     // =========================
    //     $parseDiasLaborablesMax = function ($diasStr): int {
    //         $s = trim((string)$diasStr);
    //         if ($s === '') return 1;

    //         // "1"
    //         if (preg_match('/^\d+$/', $s)) return (int)$s;

    //         // "2 a 3" / "3 a 5" / "2-3" / "3-5"
    //         if (preg_match('/(\d+)\s*(?:a|\-)\s*(\d+)/i', $s, $m)) {
    //             return (int)$m[2]; // el mayor
    //         }

    //         // fallback: extrae todos los números y toma el máximo
    //         if (preg_match_all('/\d+/', $s, $mm) && !empty($mm[0])) {
    //             $nums = array_map('intval', $mm[0]);
    //             return max(1, max($nums));
    //         }

    //         return 1;
    //     };

    //     // suma días LABORABLES (L-V). Si quieres incluir sábado, te lo ajusto.
    //     $addBusinessDays = function (DateTime $date, int $days): DateTime {
    //         $d = clone $date;
    //         $days = max(0, (int)$days);

    //         while ($days > 0) {
    //             $d->modify('+1 day');
    //             $dow = (int)$d->format('N'); // 1=Lun ... 7=Dom
    //             if ($dow <= 5) { // Lun-Vie
    //                 $days--;
    //             }
    //         }
    //         return $d;
    //     };

    //     // =========================
    //     // 1) Obtener info completa de la orden empresa
    //     // =========================
    //     $empresas = $this->getOrdenEmpresaByIdEmpresa($idOrdenEmpresa);

    //     if (empty($empresas) || empty($empresas[0])) {
    //         return ['error' => true, 'msg' => 'No se encontró orden empresa'];
    //     }

    //     $empresa = $empresas[0];

    //     // =========================
    //     // 2) Calcular totalEnvio (según tu regla)
    //     // =========================
    //     $totalEnvio = (float)($empresa["valor_producto_usd"] ?? 0) * 1.15;

    //     // =========================
    //     // 3) Contar piezas desde productos (JSON)
    //     // =========================
    //     $productosArr = $empresa["productos"] ?? "[]";
    //     if (is_string($productosArr)) $productosArr = json_decode($productosArr, true);
    //     if (!is_array($productosArr)) $productosArr = [];

    //     $totalPiezas = 0;
    //     foreach ($productosArr as $p) {
    //         // OJO: aquí tú estás sumando "cantidad" del JSON.
    //         // Si ese campo fuera stock y no la cantidad comprada, cámbialo por el campo correcto.
    //         $totalPiezas += (int)($p["cantidad"] ?? 0);
    //     }
    //     $totalPiezas = max(1, $totalPiezas);

    //     // =========================
    //     // 4) Días laborables + FechaHoraEntrega
    //     // =========================
    //     $dias_laborables = $this->getRutaByidRuta($empresa["id_ruta"] ?? 0);
    //     $diasStr = $dias_laborables["dias_laborables"] ?? "1";

    //     // regla: "1" => 1, "2 a 3" => 3, "3 a 5" => 5
    //     $diasMax = $parseDiasLaborablesMax($diasStr);

    //     // fecha actual + dias laborables (L-V)
    //     $fechaEntregaDT = $addBusinessDays(new DateTime('now'), $diasMax);

    //     // La API en tu ejemplo usa:
    //     // "FechaHoraEntrega": "2025-10-14"
    //     // "HoraEntregaHasta": "15:21:47"
    //     $fechaEntrega = $fechaEntregaDT->format('Y-m-d');
    //     $horaEntregaHasta = $fechaEntregaDT->format('H:i:s');

    //     // =========================
    //     // 5) Payload según API oficial
    //     // =========================
    //     $payload = [
    //         [
    //             "clienteid" => "1",
    //             "proyectoid" => "11",
    //             "centrocostoid" => "1",
    //             "tipopaqueteid" => "16",
    //             "comentario" => "Orden FULMUV #{$empresa['id_orden']}",

    //             // Peso y valor
    //             "peso" => (string)($empresa['peso_total'] ?? '0'),
    //             "cantidad" => (string)$totalPiezas,
    //             "valor" => (string)($totalEnvio ?? '0'),

    //             // Dirección entrega
    //             "direccionEntrega"      => (string)($empresa['domicilio']['direccion_exacta'] ?? ''),
    //             "referenciaEntrega"     => (string)($empresa['domicilio']['punto_referencial'] ?? ''),
    //             "cantonEntrega"         => (string)($empresa['domicilio']['canton'] ?? ''),
    //             "codigoPostalEntrega"   => (string)($empresa['domicilio']['codigo_postal'] ?? ''),

    //             // Destinatario
    //             "destinatarioNombre"    => (string)($empresa['facturacion']['razon_social'] ?? ''),
    //             "destinatarioEmpresa"   => (string)($empresa['datos_empresa']['nombre'] ?? ''),
    //             "destinatarioRuc"       => (string)($empresa['facturacion']['numero_identificacion'] ?? ''),
    //             "destinatarioTelefono"  => (string)($empresa['facturacion']['telefono'] ?? ''),
    //             "destinatarioCorreo"    => (string)($empresa['facturacion']['correo'] ?? ''),

    //             // ✅ FECHA/HORA ENTREGA calculada
    //             "FechaHoraEntrega" => $fechaEntrega,
    //             "HoraEntregaHasta" => $horaEntregaHasta,

    //             "customId" => "FULMUV-{$empresa['id_orden']}",

    //             // Dimensiones
    //             "dimensiones" => [
    //                 "largo"  => (string)($empresa['largo_cm'] ?? '0'),
    //                 "ancho"  => (string)($empresa['ancho_cm'] ?? '0'),
    //                 "altura" => (string)($empresa['alto_cm'] ?? '0'),
    //             ]
    //         ]
    //     ];

    //     $url = "https://portalentregas.com/ElogisticsApis/api_guiaEntregaRecoleccion/"
    //         . "?token=Aig%402018!&usuario=API_DESARROLLO&guiaMasterId={$guiaMasterId}";

    //     // =========================
    //     // 6) cURL
    //     // =========================
    //     $curl = curl_init();

    //     curl_setopt_array($curl, [
    //         CURLOPT_URL            => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_CUSTOMREQUEST  => "POST",
    //         CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    //         CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    //         CURLOPT_TIMEOUT        => 30,
    //     ]);

    //     $response = curl_exec($curl);
    //     $err      = curl_error($curl);
    //     $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     curl_close($curl);

    //     if ($response === false) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'cURL error: ' . $err,
    //             'http'  => $httpCode
    //         ];
    //     }

    //     $json = json_decode($response, true);

    //     if (!is_array($json)) {
    //         return [
    //             'error' => true,
    //             'msg'   => 'Respuesta no es JSON válido',
    //             'http'  => $httpCode,
    //             'raw'   => $response,
    //             'payload' => $payload
    //         ];
    //     }

    //     // =========================
    //     // 7) Extraer datos importantes
    //     // =========================
    //     $status  = $json['status'] ?? null;
    //     $details = $json['details'] ?? [];

    //     $barcodeEntrega = null;
    //     $labelEntrega   = null;
    //     $pickupBarcode  = null;

    //     if (is_array($details)) {
    //         foreach ($details as $d) {

    //             // Entrega: trae data + label
    //             if (!empty($d['data']) && !empty($d['label'])) {
    //                 $barcodeEntrega = (string)$d['data'];

    //                 // label puede venir como {link:"..."} o como string
    //                 if (is_array($d['label'])) {
    //                     $labelEntrega = (string)($d['label']['link'] ?? '');
    //                 } else {
    //                     $labelEntrega = (string)$d['label'];
    //                 }
    //                 continue;
    //             }

    //             // Pickup: tipo=Pickup + barcode
    //             $tipo = strtolower(trim((string)($d['tipo'] ?? '')));
    //             if ($tipo === 'Pickup' && !empty($d['barcode'])) {
    //                 $pickupBarcode = (string)$d['barcode'];
    //             }
    //         }
    //     }

    //     return [
    //         'error'           => ($status !== 'success'),
    //         'http'            => $httpCode,
    //         'status'          => $status,
    //         'dias_laborables' => [
    //             'raw' => $diasStr,
    //             'max' => $diasMax,
    //             'fecha_entrega' => $fechaEntrega,
    //             'hora_hasta' => $horaEntregaHasta,
    //         ],
    //         'barcode_entrega' => $barcodeEntrega,
    //         'label_entrega'   => $labelEntrega,
    //         'barcode_pickup'  => $pickupBarcode,
    //         'payload'         => $payload,
    //         'raw'             => $json
    //     ];
    // }



    /** ================= MASTER ================= */

    private function apiGuiaEntregaRecoleccion(int $guiaMasterId, int $idOrdenEmpresa): array
    {
        date_default_timezone_set('America/Guayaquil');

        // =========================
        // Helpers internos
        // =========================
        $parseDiasLaborablesMax = function ($diasStr): int {
            $s = trim((string)$diasStr);
            if ($s === '') return 1;

            // "1"
            if (preg_match('/^\d+$/', $s)) return (int)$s;

            // "2 a 3" / "3 a 5" / "2-3" / "3-5"
            if (preg_match('/(\d+)\s*(?:a|\-)\s*(\d+)/i', $s, $m)) {
                return (int)$m[2]; // el mayor
            }

            // fallback: extrae todos los números y toma el máximo
            if (preg_match_all('/\d+/', $s, $mm) && !empty($mm[0])) {
                $nums = array_map('intval', $mm[0]);
                return max(1, max($nums));
            }

            return 1;
        };

        // suma días LABORABLES (L-V)
        $addBusinessDays = function (DateTime $date, int $days): DateTime {
            $d = clone $date;
            $days = max(0, (int)$days);

            while ($days > 0) {
                $d->modify('+1 day');
                $dow = (int)$d->format('N'); // 1=Lun ... 7=Dom
                if ($dow <= 5) { // Lun-Vie
                    $days--;
                }
            }
            return $d;
        };

        // =========================
        // 1) Obtener info completa de la orden empresa
        // =========================
        $empresas = $this->getOrdenEmpresaByIdEmpresa($idOrdenEmpresa);

        if (empty($empresas) || empty($empresas[0])) {
            return ['error' => true, 'msg' => 'No se encontró orden empresa'];
        }

        $empresa = $empresas[0];

        // =========================
        // 2) Calcular totalEnvio (según tu regla)
        // =========================
        $totalEnvio = (float)($empresa["valor_producto_usd"] ?? 0) * 1.15;

        // =========================
        // 3) Contar piezas desde productos (JSON)
        // =========================
        $productosArr = $empresa["productos"] ?? "[]";
        if (is_string($productosArr)) $productosArr = json_decode($productosArr, true);
        if (!is_array($productosArr)) $productosArr = [];

        $totalPiezas = 0;
        foreach ($productosArr as $p) {
            $totalPiezas += (int)($p["cantidad"] ?? 0);
        }
        $totalPiezas = max(1, $totalPiezas);

        // =========================
        // 4) Días laborables + FechaHoraEntrega
        // =========================
        $dias_laborables = $this->getRutaByidRuta($empresa["id_ruta"] ?? 0); // si tu función real es getRutaById, cámbiala
        $diasStr = $dias_laborables["dias_laborables"] ?? "1";

        $diasMax = $parseDiasLaborablesMax($diasStr);

        $fechaEntregaDT = $addBusinessDays(new DateTime('now'), $diasMax);
        $fechaEntrega = $fechaEntregaDT->format('Y-m-d');
        $horaEntregaHasta = $fechaEntregaDT->format('H:i:s');

        // =========================
        // 5) Armar payload con 2 objetos (ENTREGA + RECOLECCIÓN)
        // =========================

        // --------- A) CLIENTE (ENTREGA) ----------
        $direccionCliente = (string)($empresa['domicilio']['direccion_exacta'] ?? '');
        $refCliente       = (string)($empresa['domicilio']['punto_referencial'] ?? '');
        $cantonCliente    = (string)($empresa['domicilio']['canton'] ?? '');
        $postalCliente    = (string)($empresa['domicilio']['codigo_postal'] ?? '');

        $destNombre   = (string)($empresa['facturacion']['razon_social'] ?? '');
        $destRuc      = (string)($empresa['facturacion']['numero_identificacion'] ?? '');
        $destTelefono = (string)($empresa['facturacion']['telefono'] ?? '');
        $destCorreo   = (string)($empresa['facturacion']['correo'] ?? '');

        $entrega = [
            "clienteid"        => "1",
            "proyectoid"       => "11",
            "centrocostoid"    => "1",
            "tipopaqueteid"    => "16",
            "comentario"       => "Orden FULMUV #{$empresa['id_orden']}",

            "peso"             => (string)($empresa['peso_total'] ?? '0'),
            "cantidad"         => (string)$totalPiezas,
            "valor"            => (string)($totalEnvio ?? '0'),

            "direccionEntrega"    => $direccionCliente,
            "referenciaEntrega"   => $refCliente,
            "cantonEntrega"       => $cantonCliente,
            "codigoPostalEntrega" => $postalCliente,

            // destinatario (cliente)
            "destinatarioNombre"   => $destNombre,
            "destinatarioEmpresa"  => (string)($empresa['datos_empresa']['nombre'] ?? ''), // empresa dueña de la orden
            "destinatarioRuc"      => $destRuc,
            "destinatarioTelefono" => $destTelefono,
            "destinatarioCorreo"   => $destCorreo,

            "FechaHoraEntrega" => $fechaEntrega,
            "HoraEntregaHasta" => $horaEntregaHasta,

            "customId" => "FULMUV-{$empresa['id_orden']}",

            "dimensiones" => [
                "largo"  => (string)($empresa['largo_cm'] ?? '0'),
                "ancho"  => (string)($empresa['ancho_cm'] ?? '0'),
                "altura" => (string)($empresa['alto_cm'] ?? '0'),
            ]
        ];

        // --------- B) EMPRESA SELECCIONADA (RECOLECCIÓN / PICKUP) ----------
        $empresaSel = $empresa['datos_empresa'] ?? [];

        // dirección pickup
        $direccionPickup = '';
        if (!empty($empresaSel['direccion'])) {
            $direccionPickup = (string)$empresaSel['direccion'];
        } else {
            $calleP = (string)($empresaSel['calle_principal'] ?? '');
            $calleS = (string)($empresaSel['calle_secundaria'] ?? '');
            $direccionPickup = trim($calleP . ' ' . $calleS);
        }

        $cantonPickup = (string)($empresaSel['canton'] ?? '');
        $postalPickup = (string)($empresaSel['codigo_postal'] ?? ''); // si no existe en tu BD, déjalo vacío

        $personaContacto  = (string)($empresaSel['nombre_titular'] ?? $empresaSel['nombre'] ?? '');
        $telefonoContacto = (string)($empresaSel['telefono_contacto'] ?? $empresaSel['whatsapp_contacto'] ?? '');
        $correoContacto   = (string)($empresaSel['correo'] ?? '');

        $recoleccion = [
            "Recoleccion" => true,

            "comentario"    => "Recolección - Orden FULMUV #{$empresa['id_orden']}",
            "instrucciones" => "Recoger paquete en la empresa seleccionada.",

            "peso"     => (string)($empresa['peso_total'] ?? '0'),
            "cantidad" => "0",
            "valor"    => "0",

            "direccionPickup"    => $direccionPickup,
            "referenciaPickup"   => (string)($empresaSel['referencia'] ?? ''),
            "cantonPickup"       => $cantonPickup,
            "codigoPostalPickup" => $postalPickup,

            "personaContacto"  => $personaContacto,
            "telefonoContacto" => $telefonoContacto,

            // En la documentación/ejemplo de GrupoEntregas, el objeto pickup también repite datos de entrega:
            "direccionEntrega"    => $direccionCliente,
            "referenciaEntrega"   => $refCliente,
            "cantonEntrega"       => $cantonCliente,
            "codigoPostalEntrega" => $postalCliente,

            "destinatarioNombre"   => $destNombre,
            "destinatarioEmpresa"  => (string)($empresaSel['nombre'] ?? ''),
            "destinatarioRuc"      => $destRuc,
            "destinatarioTelefono" => $destTelefono,
            "destinatarioCorreo"   => $destCorreo,

            "FechaHoraEntrega" => $fechaEntrega,
            "HoraEntregaHasta" => $horaEntregaHasta,

            "GuiaMasterDescripcion" => "Recolección Orden FULMUV #{$empresa['id_orden']}",

            "dimensiones_pickup" => [
                "largo"  => (string)($empresa['largo_cm'] ?? '0'),
                "ancho"  => (string)($empresa['ancho_cm'] ?? '0'),
                "altura" => (string)($empresa['alto_cm'] ?? '0'),
            ],
        ];

        // ✅ payload con 2 objetos
        $payload = [$entrega, $recoleccion];

        // =========================
        // 6) cURL
        // =========================
        $url = "https://portalentregas.com/ElogisticsApis/api_guiaEntregaRecoleccion/"
            . "?token=Aig%402018!&usuario=API_DESARROLLO&guiaMasterId={$guiaMasterId}";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return [
                'error' => true,
                'msg'   => 'cURL error: ' . $err,
                'http'  => $httpCode
            ];
        }

        $json = json_decode($response, true);

        if (!is_array($json)) {
            return [
                'error'   => true,
                'msg'     => 'Respuesta no es JSON válido',
                'http'    => $httpCode,
                'raw'     => $response,
                'payload' => $payload
            ];
        }

        // =========================
        // 7) Extraer datos importantes
        // =========================
        $status  = $json['status'] ?? null;
        $details = $json['details'] ?? [];

        $barcodeEntrega = null;
        $labelEntrega   = null;
        $pickupBarcode  = null;

        if (is_array($details)) {
            foreach ($details as $d) {

                // Entrega: data + label
                if (!empty($d['data']) && !empty($d['label'])) {
                    $barcodeEntrega = (string)$d['data'];

                    if (is_array($d['label'])) {
                        $labelEntrega = (string)($d['label']['link'] ?? '');
                    } else {
                        $labelEntrega = (string)$d['label'];
                    }
                    continue;
                }

                // Pickup: tipo=Pickup + barcode
                $tipo = strtolower(trim((string)($d['tipo'] ?? '')));
                if ($tipo === 'pickup' && !empty($d['barcode'])) { // ✅ corregido (antes comparabas con "Pickup")
                    $pickupBarcode = (string)$d['barcode'];
                }
            }
        }

        return [
            'error'           => ($status !== 'success'),
            'http'            => $httpCode,
            'status'          => $status,
            'dias_laborables' => [
                'raw'          => $diasStr,
                'max'          => $diasMax,
                'fecha_entrega' => $fechaEntrega,
                'hora_hasta'   => $horaEntregaHasta,
            ],
            'barcode_entrega' => $barcodeEntrega,
            'label_entrega'   => $labelEntrega,
            'barcode_pickup'  => $pickupBarcode,
            'payload'         => $payload,
            'raw'             => $json
        ];
    }



    private function crearGuiaGrupoEntregaMaster(
        $razon_social,
        $nombre_cliente,
        $direccion,
        $sector_destino,
        $telefono_destino,
        $contenido,
        $numero_piezas,
        $valor_mercancia,
        $valor_asegurado,
        $peso_fisico,
        $latitud,
        $longitud
    ) {
        $curl = curl_init();

        // Instrucciones: dirección + geo + sector (si existe)
        $instrucciones = "DIRECCIÓN: {$direccion}";
        if (!empty($sector_destino)) $instrucciones .= " | SECTOR: {$sector_destino}";
        if (!empty($latitud) && !empty($longitud)) $instrucciones .= " | UBICACIÓN: {$latitud},{$longitud}";

        // Descripción: contenido + piezas + valores + peso
        $descripcion = "CONTENIDO: {$contenido} | PIEZAS: {$numero_piezas} | "
            . "VALOR_MERCANCIA: {$valor_mercancia} | VALOR_ASEGURADO: {$valor_asegurado} | PESO_FISICO: {$peso_fisico}";

        // Parámetros fijos según tu ejemplo (ajusta si cambian)
        $params = [
            'token'          => 'Aig@2018!',
            'clienteid'      => 1,
            'proyectoid'     => 11,
            'centrocostoid'  => 1,
            'instrucciones'  => $instrucciones,
            'comentario'     => "Cliente: {$nombre_cliente} | Tel: {$telefono_destino}",
            'personaContacto' => $telefono_destino, // si quieres: aquí colocas el número del cliente
            'descripcion'    => $descripcion,
            'tipopaqueteid'  => 16,
            'fechaHoraEntrega' => date('Ymd'), // hoy en formato YYYYMMDD
            'usuario'        => 'API_DESARROLLO',
        ];

        $url = 'https://portalentregas.com/eLogisticsApis/api_insertarGuiaMaster/?' . http_build_query($params);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return ['error' => true, 'msg' => 'cURL error MASTER: ' . $err, 'http' => $httpCode];
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return ['error' => true, 'msg' => 'Respuesta MASTER no es JSON válido.', 'raw' => $response, 'http' => $httpCode];
        }

        // Agrego http por debug
        $data['_http'] = $httpCode;
        return $data;
    }


    /** ================= HIJA ================= */
    public function crearGuiaGrupoEntregaHija(
        $guiaMasterId,
        $razon_social,
        $nombre_cliente,
        $direccion,
        $sector_destino,
        $telefono_destino,
        $contenido,
        $numero_piezas,
        $valor_mercancia,
        $valor_asegurado,
        $peso_fisico,
        $latitud,
        $longitud
    ) {
        $curl = curl_init();

        // Endpoint con master id dinámico
        $url = 'https://portalentregas.com/eLogisticsApis/api_insertarGuiasHijasBarcode/?'
            . http_build_query([
                'token'       => 'Aig@2018!',
                'usuario'     => 'API_DESARROLLO',
                'guiaMasterId' => $guiaMasterId
            ]);

        // Normalizar peso (Portal a veces acepta "0,4"; mejor enviar con punto)
        $peso = str_replace(',', '.', (string)$peso_fisico);
        if ($peso === '' || !is_numeric($peso)) $peso = '0.1';

        // Armado del body JSON (según tu Postman)
        // NOTA: cantonEntrega / codigoPostalEntrega no los tienes como parámetros,
        // así que los dejo vacíos o puedes rellenarlos desde tu BD.
        $payload = [[
            "clienteid"           => "1",
            "proyectoid"          => "11",
            "centrocostoid"       => "1",
            "tipopaqueteid"       => "16",

            // comentario: aquí metemos contenido/resumen
            "comentario"          => "CONTENIDO: {$contenido} | VALOR: {$valor_mercancia} | ASEGURADO: {$valor_asegurado}",

            "peso"                => (string)$peso,
            "cantidad"            => (string)($numero_piezas ?? 1),
            "valor"               => (string)($valor_mercancia ?? 0),

            "direccionEntrega"    => (string)$direccion,
            "referenciaEntrega"   => !empty($sector_destino)
                ? "SECTOR: {$sector_destino} | GPS: {$latitud},{$longitud}"
                : "GPS: {$latitud},{$longitud}",

            "cantonEntrega"       => "",      // <- si tienes el código, ponlo aquí
            "codigoPostalEntrega" => "",      // <- si tienes el CP, ponlo aquí

            "destinatarioNombre"  => (string)$nombre_cliente,
            "destinatarioEmpresa" => (string)$razon_social,
            "destinatarioRuc"     => "",      // <- si tienes cédula/ruc, ponlo aquí
            "destinatarioTelefono" => (string)$telefono_destino,
            "destinatarioCorreo"  => "",      // <- si tienes correo, ponlo aquí

            // Campos personalizados (útiles para tu trazabilidad)
            "personalizado1"      => "PIEZAS: {$numero_piezas}",
            "personalizado2"      => "ASEGURADO: {$valor_asegurado}",
            "personalizado3"      => "PESO: {$peso_fisico}",
            "personalizado4"      => "GPS: {$latitud},{$longitud}",
            "customId"            => "FULMUV-" . $guiaMasterId
        ]];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return ['error' => true, 'msg' => 'cURL error HIJA: ' . $err, 'http' => $httpCode];
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return ['error' => true, 'msg' => 'Respuesta HIJA no es JSON válido.', 'raw' => $response, 'http' => $httpCode];
        }

        $data['_http'] = $httpCode;
        return $data;
    }


    public function createGuiaServientrega(
        $razon_social,
        $nombre_cliente,
        $direccion,
        $sector_destino,
        $telefono_destino,
        $contenido,
        $numero_piezas,
        $valor_mercancia,
        $valor_asegurado,
        $peso_fisico,
        $id_trayecto
    ) {


        $url = 'https://181.39.87.158:8021/api/guiawebs/';

        $razon_social = (string)$razon_social;
        $nombre_cliente = (string)$nombre_cliente;
        $direccion = (string)$direccion;
        $sector_destino = (string)$sector_destino;
        $telefono_destino = (string)$telefono_destino;

        $contenido = (string)$contenido;
        $numero_piezas = (int)$numero_piezas;
        $valor_mercancia = (float)$valor_mercancia;
        $valor_asegurado = (float)$valor_asegurado;
        $peso_fisico = (float)$peso_fisico;

        $user_servientrega = (string)user_servientrega;
        $password_servientrega = (string)password_servientrega;

        // JSON idéntico al que usaste en Postman
        $payload = [
            "id_tipo_logistica" => 1,
            "detalle_envio_1" => "",
            "detalle_envio_2" => "",
            "detalle_envio_3" => "",
            "id_ciudad_origen" => 1,
            "id_ciudad_destino" => $id_trayecto,
            "id_destinatario_ne_cl" => "002dest",
            "razon_social_desti_ne" => $razon_social,
            "nombre_destinatario_ne" => $nombre_cliente,
            "apellido_destinatar_ne" => "",
            "direccion1_destinat_ne" => $direccion,
            "sector_destinat_ne" => "",
            "telefono1_destinat_ne" => $telefono_destino,
            "telefono2_destinat_ne" => "",
            "codigo_postal_dest_ne" => "",
            "id_remitente_cl" => "001remi",
            "razon_social_remite" => "servientrega ecuador s.a",
            "nombre_remitente" => "gustavo ",
            "apellido_remite" => "villalba lopez",
            "direccion1_remite" => "panama 306 y thomas y martinez",
            "sector_remite" => "",
            "telefono1_remite" => "123156",
            "telefono2_remite" => "",
            "codigo_postal_remi" => "",
            "id_producto" => 2,
            "contenido" => $contenido,
            "numero_piezas" => 1,
            "valor_mercancia" => $valor_mercancia,
            "valor_asegurado" => 0,
            "largo" => 0,
            "ancho" => 0,
            "alto" => 0,
            "peso_fisico" => $peso_fisico,
            "login_creacion" => $user_servientrega,
            "password" => $password_servientrega
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: PostmanRuntime/7.40.0',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Expect:',
            'Content-Length: ' . strlen($json),
            // 'Host: 181.39.87.158:8021', // normalmente cURL lo pone solo
        ];


        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST              => true,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_POSTFIELDS        => $json,
            CURLOPT_HTTPHEADER        => $headers,
            CURLOPT_TIMEOUT           => 30,
            CURLOPT_CONNECTTIMEOUT    => 10,
            CURLOPT_ENCODING          => '',           // gzip/deflate/br
            CURLOPT_HEADER            => true,         // capturar headers respuesta
            CURLOPT_IPRESOLVE         => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER    => false,        // por usar IP + HTTPS (solo pruebas)
            CURLOPT_SSL_VERIFYHOST    => 0,
            CURLOPT_HTTP_VERSION      => CURL_HTTP_VERSION_1_1,
            CURLOPT_VERBOSE           => false,
            CURLOPT_FORBID_REUSE      => false,
            CURLOPT_FRESH_CONNECT     => false,
            CURLOPT_CERTINFO          => false,
            CURLOPT_TCP_FASTOPEN      => true,
            CURLOPT_HEADEROPT         => CURLHEADER_UNIFIED,
            CURLOPT_FAILONERROR       => false,
            CURLOPT_HTTPHEADEROPT     => 0,
            CURLOPT_NOSIGNAL          => 1,
            CURLOPT_PROXY             => null,
            CURLOPT_COOKIEFILE        => '',
            CURLOPT_COOKIEJAR         => '',
            CURLOPT_MAXREDIRS         => 10,
            CURLOPT_HTTPAUTH          => CURLAUTH_ANY,
            CURLOPT_DNS_CACHE_TIMEOUT => 60,
            CURLOPT_CONNECT_ONLY      => false,
            CURLOPT_BUFFERSIZE        => 128000,
            CURLOPT_AUTOREFERER       => true,
            CURLOPT_REFERER           => ''
        ]);


        // también saca los headers de la solicitud
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $raw   = curl_exec($ch);
        $reqH  = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $errno = curl_errno($ch);
        $err   = curl_error($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $respHeaders = substr($raw, 0, $hsz);
        $body        = substr($raw, $hsz);

        if ($errno) {
            return ["error" => true, "msj" => "cURL error", "curl_errno" => $errno, "curl_error" => $err, "request" => $reqH];
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => true, "msj" => "Respuesta no JSON del proveedor", "http" => $code, "headers" => $respHeaders, "raw" => $body, "request" => $reqH];
        }

        return $data;
    }


    // public function createGuiaServientrega($razon_social, $nombre_cliente, $direccion, $sector_destino, $telefono_destino, $productos, $total_mercancia, $valor_asegurado, $peso)
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://181.39.87.158:8021/api/guiawebs/',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => '{
    //             "id_tipo_logistica": 1,
    //             "detalle_envio_1": "",
    //             "detalle_envio_2": "",
    //             "detalle_envio_3": "",
    //             "id_ciudad_origen": 1,
    //             "id_ciudad_destino": 8,
    //             "id_destinatario_ne_cl": "001dest",
    //             "razon_social_desti_ne": "' . $razon_social . '",
    //             "nombre_destinatario_ne": "' . $nombre_cliente . '",
    //             "apellido_destinatar_ne": "",
    //             "direccion1_destinat_ne": "' . $direccion . '",
    //             "sector_destinat_ne": "' . $sector_destino . '",
    //             "telefono1_destinat_ne": "' . $telefono_destino . '",
    //             "telefono2_destinat_ne": "",
    //             "codigo_postal_dest_ne": "",
    //             "id_remitente_cl": "001remi",
    //             "razon_social_remite": "servientrega ecuador s.a",
    //             "nombre_remitente": "gustavo ",
    //             "apellido_remite": "villalba lopez",
    //             "direccion1_remite": "panama 306 y thomas y martinez",
    //             "sector_remite": "",
    //             "telefono1_remite": "123156",
    //             "telefono2_remite": "",
    //             "codigo_postal_remi": "",
    //             "id_producto": 2,
    //             "contenido": "' . $productos . '",
    //             "numero_piezas": "' . $productos . '",
    //             "valor_mercancia": "' . $total_mercancia . '",
    //             "valor_asegurado": "' . $valor_asegurado . '",
    //             "largo": 0,
    //             "ancho": 0,
    //             "alto": 0,
    //             "peso_fisico": "' . $peso . '",
    //             "login_creacion": "' . user_servientrega . '",
    //             "password": "' . password_servientrega . '",
    //         }',
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/json'
    //         ),
    //     ));

    //     $response = curl_exec($curl);

    //     curl_close($curl);
    //     $data = json_decode($response, true);

    //     return $data;
    // }

    public function getTransmision()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM transmision WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTapiceria()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tapiceria
        WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTapiceriaByIdArray($ids)
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
       SELECT id_tapiceria AS id, nombre
        FROM tapiceria
        WHERE estado = 'A' AND id_tapiceria IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getColores()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM colores
        WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getColorById($id)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM colores
        WHERE estado = 'A' and id_color = '$id';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getTipoVendedor()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM tipo_vendedor
        WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getClimatizacion()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM climatizacion
        WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getDireccion()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT *
        FROM direccion
        WHERE estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getDireccionByIdArray($ids)
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
       SELECT id_direccion AS id, nombre
        FROM direccion
        WHERE estado = 'A' AND id_direccion IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getCliminatizacionByIdArray($ids)
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
       SELECT id_climatizacion AS id, nombre
        FROM climatizacion
        WHERE estado = 'A' AND id_climatizacion IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function createVehiculo($descripcion, $provincia, $canton, $tags, $precio_referencia, $img_frontal, $img_posterior, $archivos, $id_empresa, $descuento, $tipo_vehiculo, $modelo, $marca, $traccion, $iva, $negociable, $anio, $condicion, $tipo_vendedor, $kilometraje, $transmision, $inicio_placa, $fin_placa, $color, $cilindraje, $tapiceria, $duenio, $direccion, $climatizacion, $funcionamiento_motor, $referencias, $estado, $tipo_creador)
    {
        $provinciaJson      = json_encode($provincia);
        $cantonJson         = json_encode($canton);
        $condicionJson      = json_encode($condicion);
        $transmisionJson    = json_encode($transmision);
        $tipoVendedorJson   = json_encode($tipo_vendedor);
        $tapiceriaJson      = json_encode($tapiceria);
        $duenioJson         = json_encode($duenio);
        $direccionJson      = json_encode($direccion);
        $climatizacionJson  = json_encode($climatizacion);
        $referencias        = json_encode($referencias);
        $stmt = $this->conn->prepare("INSERT INTO vehiculos (
                descripcion,
                provincia,
                canton,
                precio_referencia,
                img_frontal,
                img_posterior,
                id_empresa,
                descuento,
                tipo_auto,
                id_modelo,
                id_marca,
                tipo_traccion,
                funcionamiento_motor,
                negociable,
                anio,
                condicion,
                tipo_vendedor,
                kilometraje,
                transmision,
                inicio_placa,
                fin_placa,
                color,
                cilindraje,
                tapiceria,
                tipo_dueno,
                direccion,
                climatizacion,
                tags,
                referencias,
                estado,
                tipo_creador
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);");
        $stmt->bind_param(
            "ssssssissssssssssssssssssssssss", // ← explicación abajo
            $descripcion,            // s
            $provinciaJson,          // s (JSON)
            $cantonJson,             // s (JSON)
            $precio_referencia,      // s (varchar)
            $img_frontal,            // s
            $img_posterior,          // s
            $id_empresa,             // i
            $descuento,              // d
            $tipo_vehiculo,          // s (tipo_auto)
            $modelo,                 // s (id_modelo)
            $marca,                  // s (id_marca)
            $traccion,               // s (tipo_traccion)
            $funcionamiento_motor,   // s
            $negociable,             // i (tinyint)
            $anio,                   // s
            $condicionJson,          // s (JSON)
            $tipoVendedorJson,       // s (JSON)
            $kilometraje,            // s
            $transmisionJson,        // s (JSON)
            $inicio_placa,           // s
            $fin_placa,              // s
            $color,                  // s
            $cilindraje,             // s
            $tapiceriaJson,          // s (JSON)
            $duenioJson,             // s (JSON)
            $direccionJson,          // s (JSON)
            $climatizacionJson,       // s (JSON)
            $tags,       // s (JSON)
            $referencias,
            $estado,
            $tipo_creador
        );
        $result = $stmt->execute();
        if (!$result) {
            // Error al ejecutar
            echo "Error en execute(): " . $stmt->error;
        }
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            // Insertar archivos si existen
            if (!empty($archivos) && is_array($archivos)) {
                $stmtArchivos = $this->conn->prepare("INSERT INTO archivos_vehiculos (id_vehiculo, archivo, tipo) VALUES (?, ?, ?)");
                foreach ($archivos["archivos"] as $archivoData) {
                    $rutaArchivo = $archivoData['archivo'];
                    $tipoArchivo = $archivoData['tipo'];
                    $stmtArchivos->bind_param("iss", $ultimo_id, $rutaArchivo, $tipoArchivo);
                    $stmtArchivos->execute();
                }
                $stmtArchivos->close();
            }
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function createEmpleo($titulo, $descripcion, $provincia, $canton, $tags, $img_frontal, $img_posterior, $archivos, $id_empresa, $tipo_creador, $fecha_inicio, $fecha_fin)
    {
        $stmt = $this->conn->prepare("INSERT INTO empleos (
                titulo,
                descripcion,
                provincia,
                canton,
                img_frontal,
                img_posterior,
                id_empresa,
                tags,
                tipo_creador,
                fecha_inicio,
                fecha_fin
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?);");
        $stmt->bind_param(
            "ssssssissss",
            $titulo,
            $descripcion,
            $provincia,
            $canton,
            $img_frontal,
            $img_posterior,
            $id_empresa,
            $tags,
            $tipo_creador,
            $fecha_inicio,
            $fecha_fin
        );
        $result = $stmt->execute();
        if (!$result) {
            // Error al ejecutar
            echo "Error en execute(): " . $stmt->error;
        }
        $ultimo_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
            // Insertar archivos si existen
            if (!empty($archivos) && is_array($archivos)) {
                $stmtArchivos = $this->conn->prepare("INSERT INTO archivos_empleos (id_empleo, archivo, tipo) VALUES (?, ?, ?)");
                foreach ($archivos["archivos"] as $archivoData) {
                    $rutaArchivo = $archivoData['archivo'];
                    $tipoArchivo = $archivoData['tipo'];
                    $stmtArchivos->bind_param("iss", $ultimo_id, $rutaArchivo, $tipoArchivo);
                    $stmtArchivos->execute();
                }
                $stmtArchivos->close();
            }
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function getVehiculos($id_empresa)
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*, m.nombre AS modelo
            FROM vehiculos v
            INNER JOIN modelos_autos m
            ON m.id_modelos_autos = v.id_modelo
            WHERE v.estado = 'A' AND v.id_empresa = ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $this->hydrateVehiculoListRow($row);
        }
        return $response;
    }

    public function getVehiculosFiltro($id_empresa, $consulta)
    {
        if ($consulta == "0") {
            $consulta = '%';
        } else {
            $consulta = '%' . $consulta . '%';
        }
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*, m.nombre AS modelo
            FROM vehiculos v
            INNER JOIN modelos_autos m
            ON m.id_modelos_autos = v.id_modelo
            WHERE v.estado = 'A' AND v.id_empresa = ? AND m.nombre LIKE ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("ss", $id_empresa, $consulta);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $this->hydrateVehiculoListRow($row);
        }
        return $response;
    }

    private function hydrateVehiculoListRow($row)
    {
        $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
        $row['modeloArray'] = $this->getModeloByArray($row['id_modelo']);
        $row['tipo_autoArray'] = $this->getTipoAutoByArray($row['tipo_auto']);
        $row['marcaArray'] = $this->getMarcaByArray($row['id_marca']);
        $row['transmisionArray'] = $this->getTransmisionByArray($row['transmision']);
        $row['tipo_traccionArray'] = $this->getTipoTraccionByArray($row['tipo_traccion']);
        $row['funcionamiento_motorArray'] = $this->getFuncionamientoMotorByArray($row['funcionamiento_motor']);
        $row['tipo_vendedorArray'] = $this->getTipoVendedorByArray($row['tipo_vendedor']);
        $row['colorArray'] = $this->getColorById($row['color']);
        $row['tapiceriaArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
        $row['direccionArray'] = $this->getDireccionByIdArray($row['direccion']);
        $row['climatizacionArray'] = $this->getCliminatizacionByIdArray($row['climatizacion']);
        return $row;
    }

    public function getEmpleos($id_empresa, $tipo)
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT e.*
            FROM empleos e
            WHERE e.estado = 'A' AND e.id_empresa = ? AND e.tipo_creador = ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("ss", $id_empresa, $tipo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['archivos'] = $this->getArchivosByEmpleos($row['id_empleo']);
            $response[] = $row;
        }
        return $response;
    }

    public function getEmpleosFiltro($id_empresa, $tipo, $consulta)
    {
        if ($consulta == "0") {
            $consulta = '%';
        } else {
            $consulta = '%' . $consulta . '%';
        }
        $response = [];
        $stmt = $this->conn->prepare("SELECT e.*
            FROM empleos e
            WHERE e.estado = 'A' AND e.id_empresa = ? AND e.tipo_creador = ? AND e.titulo LIKE ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("sss", $id_empresa, $tipo, $consulta);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['archivos'] = $this->getArchivosByEmpleos($row['id_empleo']);
            $response[] = $row;
        }
        return $response;
    }

    public function getVehiculosAll()
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*
            FROM vehiculos v
            WHERE v.estado = 'A';");
        // Si id_empresa es INT usa "i"
        // $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
            $row['modeloArray'] = $this->getModeloById($row['id_modelo']);
            $row['tipo_autoArray'] = $this->getTiposAutoById($row['tipo_auto']);
            $row['marcaArray'] = $this->getMarcaById($row['id_marca']);
            $row['transmisionArray'] = $this->getTransmisionByArray($row['transmision']);
            $row['tipo_traccionArray'] = $this->getTipoTraccionByArray($row['tipo_traccion']);
            $row['funcionamiento_motorArray'] = $this->getFuncionamientoMotorByArray($row['funcionamiento_motor']);
            $row['tipo_vendedorArray'] = $this->getTipoVendedorByArray($row['tipo_vendedor']);
            $row['colorArray'] = $this->getColorById($row['color']);
            $row['tapiceriaArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['direccionArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['climatizacionArray'] = $this->getCliminatizacionByIdArray($row['climatizacion']);
            $response[] = $row;
        }
        return $response;
    }

    public function getVehiculosLlegadosAll()
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*
            FROM vehiculos v
            WHERE v.estado = 'A' ORDER BY id_vehiculo DESC LIMIT 20;");
        // Si id_empresa es INT usa "i"
        // $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
            $row['modeloArray'] = $this->getModeloById($row['id_modelo']);
            $row['tipo_autoArray'] = $this->getTiposAutoById($row['tipo_auto']);
            $row['marcaArray'] = $this->getMarcaById($row['id_marca']);
            $row['transmisionArray'] = $this->getTransmisionByArray($row['transmision']);
            $row['tipo_traccionArray'] = $this->getTipoTraccionByArray($row['tipo_traccion']);
            $row['funcionamiento_motorArray'] = $this->getFuncionamientoMotorByArray($row['funcionamiento_motor']);
            $row['tipo_vendedorArray'] = $this->getTipoVendedorByArray($row['tipo_vendedor']);
            $row['colorArray'] = $this->getColorById($row['color']);
            $row['tapiceriaArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['direccionArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['climatizacionArray'] = $this->getCliminatizacionByIdArray($row['climatizacion']);
            $row["verificacion"] = $this->getVerificacionCuentaEmpresa($publicEmpresaId);
            $row["membresia"] = $this->getMembresiaByEmpresa($publicEmpresaId);

            $response[] = $row;
        }
        return $response;
    }

    public function getVehiculosLlegadosSearchAll($search)
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*
            FROM vehiculos v
            WHERE v.estado = 'A' ORDER BY id_vehiculo DESC LIMIT 20;");
        // Si id_empresa es INT usa "i"
        // $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $publicEmpresaId = $this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa");
            if ($publicEmpresaId <= 0) {
                continue;
            }

            $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
            $row['modeloArray'] = $this->getModeloById($row['id_modelo']);
            $row['tipo_autoArray'] = $this->getTiposAutoById($row['tipo_auto']);
            $row['marcaArray'] = $this->getMarcaById($row['id_marca']);
            $row['transmisionArray'] = $this->getTransmisionByArray($row['transmision']);
            $row['tipo_traccionArray'] = $this->getTipoTraccionByArray($row['tipo_traccion']);
            $row['funcionamiento_motorArray'] = $this->getFuncionamientoMotorByArray($row['funcionamiento_motor']);
            $row['tipo_vendedorArray'] = $this->getTipoVendedorByArray($row['tipo_vendedor']);
            $row['colorArray'] = $this->getColorById($row['color']);
            $row['tapiceriaArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['direccionArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['climatizacionArray'] = $this->getCliminatizacionByIdArray($row['climatizacion']);
            $response[] = $row;
        }
        return $response;
    }

    public function getVehiculosAllById($id)
    {
        $response = [];
        $stmt = $this->conn->prepare("SELECT v.*
            FROM vehiculos v
            WHERE v.estado = 'A' and v.id_vehiculo = ?;");
        // Si id_empresa es INT usa "i"
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
            $row['modeloArray'] = $this->getModeloById($row['id_modelo']);
            $row['empresa'] = $this->getEmpresaById($row['id_empresa']);
            $row['tipo_autoArray'] = $this->getTiposAutoById($row['tipo_auto']);
            $row['marcaArray'] = $this->getMarcaById($row['id_marca']);
            $row['transmisionArray'] = $this->getTransmisionByArray($row['transmision']);
            $row['tipo_traccionArray'] = $this->getTipoTraccionByArray($row['tipo_traccion']);
            $row['funcionamiento_motorArray'] = $this->getFuncionamientoMotorByArray($row['funcionamiento_motor']);
            $row['tipo_vendedorArray'] = $this->getTipoVendedorByArray($row['tipo_vendedor']);
            $row['colorArray'] = $this->getColorById($row['color']);
            $row['tapiceriaArray'] = $this->getTapiceriaByIdArray($row['tapiceria']);
            $row['direccionArray'] = $this->getDireccionByIdArray($row['direccion']);
            $row['climatizacionArray'] = $this->getCliminatizacionByIdArray($row['climatizacion']);
            $response[] = $row;
        }
        return $response;
    }

    public function getTransmisionByArray($ids): array
    {
        $ids = $this->normalizeIds($ids);
        if (empty($ids)) return [];

        $in = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
       SELECT id_transmision AS id, nombre
        FROM transmision
        WHERE estado = 'A' AND id_transmision IN ($in)
        ORDER BY nombre
    ";
        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r; // ['id'=>..., 'nombre'=>...]
        }
        return $out;
    }

    public function getTipoVendedorByArray($ids): array
    {
        // Normaliza siempre
        $ids = $this->normalizeIds($ids);

        // SI NO HAY IDS VÁLIDOS → RETORNA VACÍO SIN CONSULTAR
        if (empty($ids)) {
            return [];
        }

        // Construcción segura de placeholders
        $in = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
        SELECT id_tipo_vendedor AS id, nombre
        FROM tipo_vendedor
        WHERE estado = 'A' AND id_tipo_vendedor IN ($in)
        ORDER BY nombre
    ";

        $stmt = $this->conn->prepare($sql);
        $this->bindMany($stmt, $ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        while ($r = $res->fetch_assoc()) {
            $out[] = $r;
        }

        return $out;
    }


    public function getVehiculoById($id_vehiculo)
    {
        $stmt = $this->conn->prepare("SELECT *
        FROM vehiculos
        WHERE id_vehiculo = ? and estado = 'A'");
        $stmt->bind_param("s", $id_vehiculo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row['archivos'] = $this->getArchivosByVehiculos($row['id_vehiculo']);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function updateVehiculo($id_vehiculo, $descripcion, $provincia, $canton, $tags, $precio_referencia, $descuento)
    {
        $provincia = json_encode($provincia);
        $canton = json_encode($canton);
        $stmt = $this->conn->prepare("UPDATE vehiculos SET descripcion=?, provincia=?, canton=?, tags=?, precio_referencia=?, descuento=? WHERE id_vehiculo = ?");
        $stmt->bind_param("sssssss", $descripcion, $provincia, $canton, $tags, $precio_referencia, $descuento, $id_vehiculo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function updateVehiculoFull(
        $id_vehiculo,
        $id_modelo,
        $anio,
        $condicion,              // JSON string
        $tipo_auto,
        $id_marca,
        $kilometraje,
        $transmision,            // JSON string
        $tipo_traccion,
        $funcionamiento_motor,
        $inicio_placa,
        $fin_placa,
        $tipo_vendedor,          // JSON string
        $provincia,              // JSON string
        $canton,                 // JSON string
        $img_frontal,
        $img_posterior,
        $color,
        $cilindraje,
        $tapiceria,              // JSON string
        $tipo_dueno,             // JSON string
        $direccion,              // JSON string
        $climatizacion,          // JSON string
        $descripcion,
        $precio_referencia,
        $id_empresa,
        $descuento,
        $tipo_creador,
        $negociable,
        $estado,
        $tags,
        $referencias,            // JSON string
        $archivos,                // array o string JSON
        $iva
    ) {
        $id_vehiculo = (int)$id_vehiculo;
        $id_empresa  = (int)$id_empresa;
        $descuento   = (float)$descuento;
        $negociable  = (int)$negociable;

        $condicion      = json_encode($condicion);
        $transmision    = json_encode($transmision);
        $tipo_vendedor  = json_encode($tipo_vendedor);
        $provincia      = json_encode($provincia);
        $canton         = json_encode($canton);
        $tapiceria      = json_encode($tapiceria);
        $tipo_dueno     = json_encode($tipo_dueno);
        $direccion      = json_encode($direccion);
        $climatizacion  = json_encode($climatizacion);
        $referencias    = json_encode($referencias);

        $sql = "
            UPDATE vehiculos SET
                id_modelo=?,
                anio=?,
                condicion=?,
                tipo_auto=?,
                id_marca=?,
                kilometraje=?,
                transmision=?,
                tipo_traccion=?,
                funcionamiento_motor=?,
                inicio_placa=?,
                fin_placa=?,
                tipo_vendedor=?,
                provincia=?,
                canton=?,
                img_frontal=?,
                img_posterior=?,
                color=?,
                cilindraje=?,
                tapiceria=?,
                tipo_dueno=?,
                direccion=?,
                climatizacion=?,
                descripcion=?,
                precio_referencia=?,
                id_empresa=?,
                descuento=?,
                tipo_creador=?,
                negociable=?,
                estado=?,
                tags=?,
                referencias=?
            WHERE id_vehiculo=?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("updateVehiculoFull prepare error: " . $this->conn->error);
            return RECORD_UPDATED_FAILED;
        }

        // 31 campos SET + WHERE = 32 parámetros
        // Tipos:
        // s = string, i = int, d = double
        $stmt->bind_param(
            "sssssssssssssssssssssssidsissssi",
            $id_modelo,              // s
            $anio,                   // s
            $condicion,              // s (json)
            $tipo_auto,              // s
            $id_marca,               // s
            $kilometraje,            // s
            $transmision,            // s (json)
            $tipo_traccion,          // s
            $funcionamiento_motor,   // s
            $inicio_placa,           // s
            $fin_placa,              // s
            $tipo_vendedor,          // s (json)
            $provincia,              // s (json)
            $canton,                 // s (json)
            $img_frontal,            // s
            $img_posterior,          // s
            $color,                  // s
            $cilindraje,             // s
            $tapiceria,              // s (json)
            $tipo_dueno,             // s (json)
            $direccion,              // s (json)
            $climatizacion,          // s (json)
            $descripcion,            // s (text -> string)
            $precio_referencia,      // s (varchar)
            $id_empresa,             // i
            $descuento,              // d
            $tipo_creador,           // s
            $negociable,             // i
            $estado,                 // s
            $tags,                   // s
            $referencias,            // s (json),
            $id_vehiculo             // i
        );

        $ok  = $stmt->execute();
        if (!$ok) error_log("updateVehiculoFull execute error: " . $stmt->error);
        $stmt->close();

        if (!$ok) return RECORD_UPDATED_FAILED;

        // ✅ Guardar archivos (si tu tabla archivos_vehiculos existe)
        // Acepta:
        // - $archivos = ["archivos" => [ ["archivo"=>"ruta", "tipo"=>"pdf"], ...]]
        // - o $archivos = '[{"archivo":"...","tipo":"..."}]'
        if (is_string($archivos)) {
            $tmp = json_decode($archivos, true);
            if (json_last_error() === JSON_ERROR_NONE) $archivos = $tmp;
        }

        // Si viene como ["archivos"=>[...]] o directamente como [...]
        $lista = [];
        if (is_array($archivos)) {
            if (isset($archivos["archivos"]) && is_array($archivos["archivos"])) $lista = $archivos["archivos"];
            else $lista = $archivos;
        }

        if (!empty($lista)) {
            $stmtArch = $this->conn->prepare("INSERT INTO archivos_vehiculos (id_vehiculo, archivo, tipo) VALUES (?, ?, ?)");
            if ($stmtArch) {
                foreach ($lista as $a) {
                    $ruta = $a["archivo"] ?? "";
                    $tipo = $a["tipo"] ?? "";
                    if ($ruta === "") continue;
                    $stmtArch->bind_param("iss", $id_vehiculo, $ruta, $tipo);
                    $stmtArch->execute();
                }
                $stmtArch->close();
            }
        }

        return RECORD_UPDATED_SUCCESSFULLY;
    }

    public function updateVehiculoBasic(
        $id_vehiculo,
        $id_modelo,
        $anio,
        $tipo_auto,
        $id_marca,
        $kilometraje,
        $tipo_traccion,
        $funcionamiento_motor,
        $inicio_placa,
        $fin_placa,
        $img_frontal,
        $img_posterior,
        $color,
        $cilindraje,
        $descripcion,
        $precio_referencia,
        $id_empresa,
        $descuento,
        $tipo_creador,
        $negociable,
        $estado,
        $tags,
        $condicion,
        $archivos
    ) {
        $id_vehiculo = (int)$id_vehiculo;
        $id_empresa  = (int)$id_empresa;
        $descuento   = (float)$descuento;
        $negociable  = (int)$negociable;
        $condicion = json_encode($condicion);

        $sql = "
            UPDATE vehiculos SET
                id_modelo=?,
                anio=?,
                tipo_auto=?,
                id_marca=?,
                kilometraje=?,
                tipo_traccion=?,
                funcionamiento_motor=?,
                inicio_placa=?,
                fin_placa=?,
                img_frontal=?,
                img_posterior=?,
                color=?,
                cilindraje=?,
                descripcion=?,
                precio_referencia=?,
                id_empresa=?,
                descuento=?,
                tipo_creador=?,
                negociable=?,
                estado=?,
                tags=?,
                condicion=?
            WHERE id_vehiculo=?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("updateVehiculoBasic prepare error: " . $this->conn->error);
            return RECORD_UPDATED_FAILED;
        }

        // 23 params: 21 SET + id_vehiculo
        $stmt->bind_param(
            "ssssssssssssssssssisssi",
            $id_modelo,
            $anio,
            $tipo_auto,
            $id_marca,
            $kilometraje,
            $tipo_traccion,
            $funcionamiento_motor,
            $inicio_placa,
            $fin_placa,
            $img_frontal,
            $img_posterior,
            $color,
            $cilindraje,
            $descripcion,
            $precio_referencia,
            $id_empresa,      // i
            $descuento,       // d
            $tipo_creador,
            $negociable,      // i
            $estado,
            $tags,
            $condicion,
            $id_vehiculo      // i
        );

        $ok  = $stmt->execute();
        $aff = $stmt->affected_rows;
        if (!$ok) error_log("updateVehiculoBasic execute error: " . $stmt->error);
        else error_log("updateVehiculoBasic OK id=$id_vehiculo affected=$aff");
        $stmt->close();

        // éxito si ejecutó (aunque no cambie nada)
        if ($ok) {
            // Insertar archivos si existen
            if (!empty($archivos) && is_array($archivos)) {
                $stmtArchivos = $this->conn->prepare("INSERT INTO archivos_vehiculos (id_vehiculo, archivo, tipo) VALUES (?, ?, ?)");
                foreach ($archivos["archivos"] as $archivoData) {
                    $rutaArchivo = $archivoData['archivo'];
                    $tipoArchivo = $archivoData['tipo'];
                    $stmtArchivos->bind_param("iss", $id_vehiculo, $rutaArchivo, $tipoArchivo);
                    $stmtArchivos->execute();
                }
                $stmtArchivos->close();
            }
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
        //return $ok ? RECORD_UPDATED_SUCCESSFULLY : RECORD_UPDATED_FAILED;
    }

    private function toJson($v)
    {
        // si viene null o vacío => []
        if ($v === null || $v === '') return json_encode([]);

        // si viene array => ok
        if (is_array($v)) return json_encode($v);

        // si viene string, puede ser JSON ya listo
        $s = trim((string)$v);

        // si ya parece JSON válido (empieza con [ o { ), lo dejamos tal cual
        if ($s !== '' && ($s[0] === '[' || $s[0] === '{')) {
            json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE) return $s;
        }

        // si vino algo simple, lo metemos en array
        return json_encode([$s]);
    }

    public function deleteVehiculo($id_vehiculo)
    {
        $stmt = $this->conn->prepare("UPDATE vehiculos SET estado = 'E' WHERE id_vehiculo = ?");
        $stmt->bind_param("s", $id_vehiculo);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return RECORD_UPDATED_SUCCESSFULLY;
        } else {
            return RECORD_UPDATED_FAILED;
        }
    }

    public function getPDFGUIAA4($id_guia)
    {
        $curl = curl_init();

        // 1) Construir la URL correctamente (JSON en el path, urlencode)
        $params = rawurlencode(json_encode(["$id_guia", "changethemove.sas", "123456", "1"]));
        $url = "https://181.39.87.158:7777/api/GuiasWeb/{$params}";

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json'
            ],

            // ⚠️ Si el servidor usa certificado self-signed o CN no coincide con IP:
            // Descomenta las dos líneas siguientes SOLO para pruebas:
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            // Error de cURL
            error_log('cURL error (getCiudadesAgencia): ' . curl_error($curl));
            curl_close($curl);
            return []; // o lanza excepción si prefieres
        }

        curl_close($curl);

        // 2) A veces hay BOM/espacios -> limpiar antes de decodificar
        $clean = trim($response, "\xEF\xBB\xBF \t\n\r\0\x0B");

        $data = json_decode($clean, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error (getCiudadesAgencia): ' . json_last_error_msg() . ' | payload: ' . substr($clean, 0, 500));
            return [];
        }

        // 3) Si la API devuelve {error:false, data:[...]} retornamos el array de data
        if (is_array($data) && array_key_exists('data', $data)) {
            return $data['data'] ?? [];
        }

        // Si devuelve directamente un array, lo retornamos tal cual
        return is_array($data) ? $data : [];
    }

    public function validaVerificacion($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * FROM verificacion_empresa WHERE id_empresa = ? AND estado = 'A'");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                return $row;
            }
        } else {
            return false;
        }
    }

    // 1) Buscar por provincia_destino (LIKE opcional) y devolver 1 fila por provincia (más reciente)
    public function getRutasByProvinciaLike($search)
    {
        $response = [];
        $q = trim((string)$search); // robusto ante null

        // Usamos un filtro opcional: si $q == '' no se aplica LIKE
        // Tomamos la fila más reciente por provincia (id_ruta DESC)
        $sql = "
        SELECT *
        FROM (
            SELECT r.*,
                   ROW_NUMBER() OVER (
                       PARTITION BY LOWER(TRIM(r.provincia_destino))
                       ORDER BY r.id_ruta DESC
                   ) AS rn
            FROM rutas r
            WHERE r.estado = 'A'
              AND (? = '' OR r.provincia_destino LIKE CONCAT('%', ?, '%'))
        ) t
        WHERE t.rn = 1
        ORDER BY t.provincia_destino ASC
    ";

        $stmt = $this->conn->prepare($sql);
        // bind dos veces el mismo valor para ? = '' y para LIKE
        $stmt->bind_param("ss", $q, $q);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[] = $row; // fila completa
        }
        return $response; // siempre array (vacío si no hay coincidencias)
    }

    // 2) Buscar cantón por provincia_destino + search en canton_destino
    //    Devuelve solo nombres de cantón (DISTINCT). Cambia el SELECT si quieres filas completas.
    public function getCantonesDestinoByProvinciaLike($search, $nombre_provincia)
    {
        $response = [];
        $q    = trim($search);
        $prov = trim($nombre_provincia);
        if ($prov === '') return $response; // provincia obligatoria

        $like = ($q === '') ? '%' : "%{$q}%";

        // MySQL 8.0+: una fila por cantón (la más reciente por id_ruta)
        $sql = "
        SELECT *
        FROM (
            SELECT r.*,
                   ROW_NUMBER() OVER (
                       PARTITION BY LOWER(TRIM(r.canton_destino))
                       ORDER BY r.id_ruta ASC
                   ) AS rn
            FROM rutas r
            WHERE r.estado = 'A'
              AND TRIM(r.provincia_destino) = ?
              AND r.canton_destino LIKE ?
        ) t
        WHERE t.rn = 1
        ORDER BY t.canton_destino ASC
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $prov, $like);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[] = $row; // fila completa de la ruta
        }
        return $response;
    }

    // public function getRutaGrupoConTrayectos($provincia, $canton, $parroquia, $limit = 200)
    // {
    //     $response = [];

    //     $prov = trim((string)$provincia);
    //     $can  = trim((string)$canton);
    //     $parr = trim((string)$parroquia);

    //     // Si parroquia es opcional, puedes permitir $parr vacío (yo lo permito)
    //     if ($prov === '' || $can === '') return [];

    //     $limit = max(1, (int)$limit);

    //     // LIKEs
    //     $likeProv = '%' . $prov . '%';
    //     $likeCan  = '%' . $can  . '%';
    //     $likeParr = '%' . $parr . '%';

    //     /**
    //      * 1) Traer ruta_grupo por provincia/cantón y (opcional) parroquia
    //      */
    //     $sql = "
    //     SELECT rg.*
    //     FROM ruta_grupo rg
    //     WHERE rg.estado = 'A'
    //       AND rg.provincia LIKE ?
    //       AND rg.canton LIKE ?
    //       " . ($parr !== '' ? " AND rg.parroquia LIKE ? " : "") . "
    //     ORDER BY rg.parroquia ASC
    //     LIMIT ?
    // ";

    //     $stmt = $this->conn->prepare($sql);
    //     if (!$stmt) return [];

    //     if ($parr !== '') {
    //         // s s s i
    //         $stmt->bind_param("sssi", $likeProv, $likeCan, $likeParr, $limit);
    //     } else {
    //         // s s i
    //         $stmt->bind_param("ssi", $likeProv, $likeCan, $limit);
    //     }

    //     if (!$stmt->execute()) {
    //         $stmt->close();
    //         return [];
    //     }

    //     $res  = $stmt->get_result();
    //     $rows = [];
    //     while ($row = $res->fetch_assoc()) $rows[] = $row;
    //     $stmt->close();

    //     if (!$rows) return [];

    //     /**
    //      * 2) Coberturas únicas (tipo_cobertura)
    //      */
    //     $coberturas = [];
    //     foreach ($rows as $r) {
    //         $tc = trim((string)($r['tipo_cobertura'] ?? ''));
    //         if ($tc !== '') $coberturas[$tc] = true;
    //     }
    //     $coberturas = array_keys($coberturas);

    //     /**
    //      * 3) Trayectos por cobertura (IN ? ? ?)
    //      *    IMPORTANTE: aquí debes confirmar el nombre real del campo.
    //      *    Yo lo dejo como t.ciudad_entrega porque así lo pusiste.
    //      */
    //     $trayectosByCobertura = [];

    //     if (!empty($coberturas)) {
    //         $placeholders = implode(',', array_fill(0, count($coberturas), '?'));
    //         $types = str_repeat('s', count($coberturas));

    //         $sql2 = "
    //         SELECT
    //             *
    //         FROM trayecto t
    //         WHERE nombre LIKE '%$tc%'
    //     ";

    //         $stmt2 = $this->conn->prepare($sql2);
    //         if ($stmt2) {
    //             $stmt2->bind_param($types, ...$coberturas);

    //             if ($stmt2->execute()) {
    //                 $res2 = $stmt2->get_result();
    //                 while ($t = $res2->fetch_assoc()) {
    //                     $key = trim((string)($t['key_cobertura'] ?? ''));
    //                     if ($key === '') continue;

    //                     // Soporta decimales con coma si vinieran así
    //                     $base = (float)str_replace(',', '.', (string)($t['base'] ?? 0));
    //                     $adi  = (float)str_replace(',', '.', (string)($t['adicional'] ?? 0));

    //                     $trayectosByCobertura[$key] = [
    //                         "base" => $base,
    //                         "adicional" => $adi,
    //                     ];
    //                 }
    //             }

    //             $stmt2->close();
    //         }
    //     }

    //     /**
    //      * 4) Armar respuesta final
    //      */
    //     foreach ($rows as $r) {
    //         $tc = trim((string)($r['tipo_cobertura'] ?? ''));

    //         $r['trayecto'] = $tc !== '' ? ($trayectosByCobertura[$tc] ?? null) : null;

    //         // Si deseas “rellenar” campos de ruta_grupo con el trayecto:
    //         if (!empty($r['trayecto'])) {
    //             $r['valor_kg'] = $r['trayecto']['base'];
    //             $r['valor_kg_adicional'] = $r['trayecto']['adicional'];
    //         }

    //         $response[] = $r;
    //     }

    //     return $response;
    // }

    public function getRutaGrupoConTrayectos($provincia, $canton, $parroquia, $limit = 200)
    {
        $response = [];

        $prov = trim((string)$provincia);
        $can  = trim((string)$canton);
        $parr = trim((string)$parroquia);

        if ($prov === '' || $can === '') return [];

        $limit = max(1, (int)$limit);

        $likeProv = '%' . $prov . '%';
        $likeCan  = '%' . $can  . '%';
        $likeParr = '%' . $parr . '%';

        /* =======================
     * 1) RUTAS
     * ======================= */
        $sql = "
        SELECT rg.*
        FROM ruta_grupo rg
        WHERE rg.estado = 'A'
          AND rg.provincia LIKE ?
          AND rg.canton LIKE ?
          " . ($parr !== '' ? " AND rg.parroquia LIKE ? " : "") . "
        ORDER BY rg.parroquia ASC
        LIMIT ?
    ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        if ($parr !== '') {
            $stmt->bind_param("sssi", $likeProv, $likeCan, $likeParr, $limit);
        } else {
            $stmt->bind_param("ssi", $likeProv, $likeCan, $limit);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();

        if (!$rows) return [];

        /* =======================
     * 2) COBERTURAS ÚNICAS
     * ======================= */
        $coberturas = [];
        foreach ($rows as $r) {
            $tc = trim((string)($r['tipo_cobertura'] ?? ''));
            if ($tc !== '') {
                $coberturas[$tc] = true;
            }
        }
        $coberturas = array_keys($coberturas);

        /* =======================
     * 3) TRAYECTOS
     * ======================= */
        $trayectosMap = [];

        if (!empty($coberturas)) {

            // WHERE (nombre LIKE ? OR nombre LIKE ? ...)
            $likes = [];
            foreach ($coberturas as $c) {
                $likes[] = "nombre LIKE ?";
            }

            $sql2 = "
            SELECT nombre, valor, adicional
            FROM trayecto
            WHERE estado = 'A'
              AND (" . implode(' OR ', $likes) . ")
        ";

            $stmt2 = $this->conn->prepare($sql2);
            if ($stmt2) {

                $params = array_map(fn($c) => '%' . $c . '%', $coberturas);
                $types  = str_repeat('s', count($params));

                $stmt2->bind_param($types, ...$params);

                if ($stmt2->execute()) {
                    $res2 = $stmt2->get_result();
                    while ($t = $res2->fetch_assoc()) {

                        foreach ($coberturas as $tc) {
                            if (stripos($t['nombre'], $tc) !== false) {
                                $trayectosMap[$tc] = [
                                    'valor'     => (float)$t['valor'],
                                    'adicional' => (float)$t['adicional'],
                                ];
                            }
                        }
                    }
                }
                $stmt2->close();
            }
        }

        /* =======================
     * 4) ARMAR RESPUESTA
     * ======================= */
        foreach ($rows as $r) {
            $tc = trim((string)($r['tipo_cobertura'] ?? ''));

            $r['trayecto'] = $trayectosMap[$tc] ?? null;

            if ($r['trayecto']) {
                $r['id_trayecto'] = $r['trayecto']['id_trayecto'];
                $r['nombre'] = $r['trayecto']['nombre'];
                $r['valor_kg'] = $r['trayecto']['valor'];
                $r['valor_kg_adicional'] = $r['trayecto']['adicional'];
            }

            $response[] = $r;
        }

        return $response;
    }



    public function getSectoresByProvinciaCantonLike($provincia, $canton, $search, $limit = 200)
    {
        $response = [];
        $prov = trim($provincia);
        $can  = trim($canton);
        $q    = trim($search);

        if ($prov === '' || $can === '') return $response;

        $like  = ($q === '') ? '%' : "%{$q}%";
        $limit = max(1, (int)$limit); // por si te lo pasan desde el cliente

        /*
      sector_alias:
        - Si zona_peligrosa tiene valor => TRIM(zona_peligrosa)
        - Si está vacía/NULL        => TRIM(canton_destino)
      Particionamos por ese alias y elegimos como rn=1 la fila con
      aplica_domicilio = 'Y' cuando exista (prioridad), para que no se “pierda”.
    */
        $sql = "SELECT *
            FROM (
                SELECT r.*,
                       CASE
                         WHEN r.zona_peligrosa IS NULL OR r.zona_peligrosa = ''
                           THEN TRIM(r.canton_destino)
                         ELSE TRIM(r.zona_peligrosa)
                       END AS sector,
                       ROW_NUMBER() OVER (
                         PARTITION BY
                           CASE
                             WHEN r.zona_peligrosa IS NULL OR r.zona_peligrosa = ''
                               THEN TRIM(r.canton_destino)
                             ELSE TRIM(r.zona_peligrosa)
                           END
                         ORDER BY
                           CASE WHEN UPPER(r.aplica_domicilio) = 'Y' THEN 0 ELSE 1 END,
                           r.updated_at DESC, r.id_ruta DESC
                       ) AS rn
                FROM rutas r
                WHERE r.estado = 'A'
                  AND TRIM(r.provincia_destino) = ?
                  AND TRIM(r.canton_destino)    = ?
            ) t
            WHERE t.rn = 1
              AND t.sector IS NOT NULL
              AND t.sector <> ''
              AND t.sector LIKE ?
            ORDER BY t.sector ASC
            LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $prov, $can, $like, $limit);
        $stmt->execute();

        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            unset($row['rn']);       // auxiliar
            // Si no quieres exponer zona_peligrosa original:
            // unset($row['zona_peligrosa']);
            $response[] = $row;      // trae la fila “representante” (prioriza Y) + sector alias
        }
        return $response;
    }



    public function getRutaByProvinciaCantonSector($provincia, $canton, $sector)
    {
        $prov = trim($provincia);
        $can  = trim($canton);
        $sec  = trim($sector);
        if ($prov === '' || $can === '' || $sec === '') return null;

        $sql = "SELECT *
            FROM rutas
            WHERE estado = 'A'
              AND TRIM(provincia_destino) = ?
              AND TRIM(canton_destino)    = ?
              AND (
                    TRIM(zona_peligrosa) = ?
                 OR (
                        (zona_peligrosa IS NULL OR zona_peligrosa = '')
                    AND TRIM(canton_destino) = ?
                 )
              )
            ORDER BY
              CASE WHEN UPPER(aplica_domicilio)='Y' THEN 0 ELSE 1 END,
              updated_at DESC, id_ruta DESC
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $prov, $can, $sec, $sec);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public function debitarMembresiasDiarias()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // debug útil
        $db = $this->conn;                                     // tu conexión mysqli

        date_default_timezone_set('America/Guayaquil');
        $VENTANA_MIN = 5;

        $this->procesarCobrosProgramadosPendientes();

        // TZ en MySQL
        $db->query("SET time_zone = '-05:00'");

        $sql = "SELECT *
            FROM membresias_empresas me
            WHERE me.estado = 'A'
            AND me.fecha_fin >= CURDATE()
            AND me.fecha_fin <  CURDATE() + INTERVAL 1 DAY;";

        $stmt = $db->prepare($sql);
        // 4 placeholders ? => 4 enteros "iiii"
        //$stmt->bind_param("iiii", $VENTANA_MIN, $VENTANA_MIN, $VENTANA_MIN, $VENTANA_MIN);
        $stmt->execute();

        $result = $stmt->get_result();
        $candidatos = $result->fetch_all(MYSQLI_ASSOC);

        // var_dump($candidatos);
        $stmt->close();

        try {
            foreach ($candidatos as $r) {
                $idME  = (int)$r['id_membresia_empresa'];
                $idEmp = (int)$r['id_empresa'];
                $idMem = (int)$r['id_membresia'];
                $fin   = $r['fecha_fin']; // datetime

                // 2) Token activo e id_usuario (pagos_recurrentes)
                $tokRow = $this->getPreferredChargeableTokenByEmpresa($idEmp);
                if (!$tokRow || !empty($tokRow['error']) || empty($tokRow['token'])) {
                    continue;
                }
                $token     = $tokRow['token'] ?? null;
                $idUsuario = (int)($tokRow['id_usuario'] ?? 0);

                // 3) Costo y días de la membresía
                $valor = $this->getCostoMembresiaMySQLi($db, $idMem);
                $dias  = $this->getDiasMembresiaMySQLi($db, $idMem);
                if ($valor === null || $dias === null) {
                    //continue;
                }

                // 4) Cobro (corriente=0, meses="")
                $resp = $this->debitToken($token, $idUsuario, $idMem, $idEmp, $valor, 0, "");
                $ok = false;
                $trxId = null;
                $auth = null;
                $payDate = date('Y-m-d H:i:s');
                if (is_array($resp) && isset($resp['transaction'])) {
                    $trx = $resp['transaction'];
                    if (strtolower($trx['status'] ?? '') === 'success') {
                        $ok      = true;
                        $trxId   = $trx['id'] ?? null;
                        $auth    = $trx['authorization_code'] ?? null;
                        $payDate = $trx['payment_date'] ?? $payDate;
                        $valor   = $trx['amount'] ?? null;


                        //insert transaction
                        $this->createPagoTransaccion($idUsuario, $idMem, $valor, $idEmp, 'empresa', $trxId, $auth, 'Y', $payDate, $idME);
                        //$this->notificaCompra($auth, $trxId, $idEmp, $valor);
                    }
                }
                if (!$ok) {
                    // si falla, continúa con las demás (o haz rollback de todo si prefieres)
                    //continue;
                }
            }

            //$db->commit();
        } catch (Throwable $e) {
            //$db->rollback();
            error_log("CRON recurrentes error: " . $e->getMessage());
        }
    }

    /* === Helpers mysqli con bind_param === */
    public function getCostoMembresiaMySQLi(mysqli $db, int $id_membresia): ?float
    {
        $sql = "SELECT costo FROM membresias WHERE id_membresia = ? LIMIT 1";
        $st  = $db->prepare($sql);
        $st->bind_param("i", $id_membresia);
        $st->execute();
        $st->bind_result($costo);
        $ok = $st->fetch();
        $st->close();
        return $ok ? (float)$costo : null;
    }
    public function getDiasMembresiaMySQLi(mysqli $db, int $id_membresia): ?int
    {
        $sql = "SELECT dias_permitidos FROM membresias WHERE id_membresia = ? LIMIT 1";
        $st  = $db->prepare($sql);
        $st->bind_param("i", $id_membresia);
        $st->execute();
        $st->bind_result($dias);
        $ok = $st->fetch();
        $st->close();
        return $ok ? (int)$dias : null;
    }

    // Obtener el último registro por IP+versión
    public function getLastCookieConsentByIpVersion($ip_addr, $version_texto)
    {
        $stmt = $this->conn->prepare("
        SELECT * FROM cookies_consent WHERE ip_addr = ? AND version_texto = ? ORDER BY created_at DESC LIMIT 1
    ");
        $stmt->bind_param("ss", $ip_addr, $version_texto);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    // Insertar consentimiento (devuelve lastInsertId o RECORD_CREATION_FAILED)
    public function insertCookieConsent(
        $version_texto,
        $decision,
        $timestampMs,
        $ip_addr,
        $user_agent,
        $cookie_essential,
        $cookie_analiticas,
        $cookie_publicidad,
        $source_page
    ) {
        // Normaliza decision a los valores esperados
        $allowed = array('accept_all', 'configurar', 'reject');
        if (!in_array($decision, $allowed)) {
            $decision = 'configurar';
        }

        // token simple (puedes reemplazar por UUID del servidor si prefieres)
        $consent_token = $this->generateUuidV4();

        // Si tu columna ts_ms es TIMESTAMP: convertimos ms -> 'Y-m-d H:i:s'
        $ts = $timestampMs > 0 ? date('Y-m-d H:i:s', intval($timestampMs / 1000)) : date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("
        INSERT INTO cookies_consent
        (consent_token, decision, version_texto, ts_ms, ip_addr, user_agent,
         cookie_essential, cookie_analiticas, cookie_publicidad, source_page)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $stmt->bind_param(
            "ssssssiiss",
            $consent_token,
            $decision,
            $version_texto,
            $ts,
            $ip_addr,
            $user_agent,
            $cookie_essential,
            $cookie_analiticas,
            $cookie_publicidad,
            $source_page
        );

        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            return $this->conn->insert_id; // lastInsertId
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    // Helper UUID v4 (igual estilo clase)
    private function generateUuidV4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function getProductTermsAcceptByIp($product_id, $ip_addr, $version_terminos)
    {
        $stmt = $this->conn->prepare("SELECT * FROM terminos_producto_accept WHERE ip_addr = ? AND version_terminos = ? ORDER BY ts_accept DESC LIMIT 1");
        $stmt->bind_param("ss", $ip_addr, $version_terminos);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function insertProductTermsAccept($product_id, $ip_addr, $version_terminos, $user_agent, $source_page)
    {

        $stmt = $this->conn->prepare("
        INSERT INTO terminos_producto_accept
        (product_id, ip_addr, version_terminos, user_agent, source_page)
        VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->bind_param("issss", $product_id, $ip_addr, $version_terminos, $user_agent, $source_page);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok) {
            return $this->conn->insert_id;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function getVerificaciones()
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT v.*, e.nombre, e.img_path, e.correo, e.direccion, e.cedula_ruc
        FROM verificacion_empresa v
        INNER JOIN empresas e ON e.id_empresa = v.id_empresa
        WHERE v.estado = 'A' and e.estado = 'A';");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function getVerificacionById($id_verificacion)
    {
        $stmt = $this->conn->prepare("SELECT * FROM verificacion_empresa where id_verificacion = ? and estado ='A'");
        $stmt->bind_param("s", $id_verificacion);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        } else return RECORD_DOES_NOT_EXIST;
    }

    public function notificaNuevoCliente($correo, $nombre, $pass, $nombreUsuario = null, $labelUsuario = 'Usuario')
    {
        $correo_contenedor = $this->getContenedor();
        $usuarioAcceso = !empty($nombreUsuario) ? $nombreUsuario : $correo;
        $mail = new PHPMailer();
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FULMUV");

        $mail->IsHTML(true);
        $mail->Subject = utf8_decode('Bienvenido a FULMUV');
        $mail->AddAddress($correo);
        $mail->AddBCC("jacarrasco@bonsai.com.ec");
        $mail->Body = utf8_decode('
                <!DOCTYPE html>
                <html lang="es">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <title>Bienvenido a FULMUV</title>
                </head>
                <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#111827;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 0;">
                    <tr>
                    <td align="center" style="padding:0 12px;">

                        <!-- Contenedor -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.08);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background:#004E60;padding:22px 24px;text-align:center;">
                            <img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" alt="FULMUV" style="max-width:190px;width:100%;height:auto;display:block;margin:0 auto;">
                            <div style="margin-top:12px;color:#e6f9fc;font-size:13px;letter-spacing:.3px;">
                                Acceso para empresas y vendedores
                            </div>
                            </td>
                        </tr>

                        <!-- Contenido -->
                        <tr>
                            <td style="padding:26px 24px 10px 24px;">
                            <h1 style="margin:0 0 8px 0;font-size:22px;line-height:1.25;color:#0f172a;">
                                ¡Bienvenido(a) a FULMUV!
                            </h1>

                            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.6;color:#334155;">
                                Hola <b>' . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . '</b>, tu cuenta de <b>Empresa/Vendedor</b> ha sido creada.
                                Con estas credenciales podrás ingresar a la <b>plataforma web</b> para gestionar tus productos/servicios y acceder a los
                                <b>beneficios</b> disponibles dentro de FULMUV.
                            </p>

                            <!-- Card credenciales -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:14px 14px;margin:14px 0;">
                                <tr>
                                <td style="padding:0;">
                                    <div style="font-size:13px;color:#64748b;margin-bottom:10px;">
                                    Tus datos de acceso
                                    </div>

                                    <div style="font-size:14px;color:#0f172a;line-height:1.5;margin-bottom:8px;">
                                    <b>' . htmlspecialchars($labelUsuario, ENT_QUOTES, "UTF-8") . ':</b>
                                    <span style="color:#0f172a;">' . htmlspecialchars($usuarioAcceso, ENT_QUOTES, "UTF-8") . '</span>
                                    </div>

                                    <div style="font-size:14px;color:#0f172a;line-height:1.5;">
                                    <b>Contraseña:</b>
                                    <span style="color:#0f172a;">' . htmlspecialchars($pass, ENT_QUOTES, "UTF-8") . '</span>
                                    </div>

                                    <div style="margin-top:12px;font-size:12px;color:#64748b;line-height:1.5;">
                                    Recomendación: cambia tu contraseña después de ingresar para mayor seguridad.
                                    </div>
                                </td>
                                </tr>
                            </table>

                            <!-- Beneficios -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 10px 0;">
                                <tr>
                                <td style="font-size:13px;color:#334155;line-height:1.6;">
                                    <b>Desde tu panel podrás:</b>
                                    <ul style="margin:8px 0 0 18px;padding:0;color:#334155;">
                                    <li>Administrar tu catálogo de productos y servicios.</li>
                                    <li>Dar seguimiento a pedidos y solicitudes de clientes.</li>
                                    <li>Actualizar información de tu empresa y medios de contacto.</li>
                                    </ul>
                                </td>
                                </tr>
                            </table>

                            <!-- Botón CTA -->
                            <div style="text-align:center;padding:14px 0 4px 0;">
                                <a href="https://fulmuv.com/empresa/login.php"
                                style="display:inline-block;background:#004E60;color:#ffffff;text-decoration:none;font-weight:700;
                                        font-size:14px;padding:12px 18px;border-radius:10px;">
                                Acceder a FULMUV
                                </a>
                            </div>

                            <p style="margin:12px 0 0 0;font-size:12px;line-height:1.6;color:#64748b;text-align:center;">
                                Si no solicitaste esta cuenta, por favor ignora este mensaje o contáctanos.
                            </p>

                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding:16px 24px;background:#ffffff;border-top:1px solid #e5e7eb;">
                            <div style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.5;">
                                © ' . date('Y') . ' FULMUV · Plataforma web para empresas y vendedores
                            </div>
                            </td>
                        </tr>

                        </table>
                        <!-- /Contenedor -->

                    </td>
                    </tr>
                </table>
                </body>
                </html>
                ');

        return $mail->send();
    }

    private function isClienteExists($correo)
    {
        $stmt = $this->conn->prepare("SELECT id_cliente FROM clientes WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

    public function registroCliente($nombres, $correo, $telefono, $password)
    {
        if (!$this->isClienteExists($correo)) {

            // Encriptar password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("INSERT INTO clientes (nombres, correo, telefono, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombres, $correo, $telefono, $passwordHash);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {

                // 👇 AQUÍ SE ENVÍA LA NOTIFICACIÓN
                // OJO: se envía el password ORIGINAL (no el hash)
                $this->notificaNuevoCliente2($correo, $nombres, $password);

                return RECORD_CREATED_SUCCESSFULLY;
            } else {
                return RECORD_CREATION_FAILED;
            }
        } else {
            return RECORD_ALREADY_EXISTED;
        }
    }

    public function notificaNuevoCliente2($correo, $nombre, $pass)
    {
        $correo_contenedor = $this->getContenedor();
        $mail = new PHPMailer();
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail

        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FULMUV");

        $mail->IsHTML(true);
        $mail->Subject = utf8_decode('Bienvenido a FULMUV - Seguimiento de tu pedido');
        $mail->AddAddress($correo);
        $mail->AddBCC("josecarrasco1998@outlook.com");
        $mail->Body = utf8_decode('
        <!DOCTYPE html>
        <html lang="es">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Accede a FULMUV para ver tu pedido</title>
        </head>
        <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#111827;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 0;">
            <tr>
            <td align="center" style="padding:0 12px;">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                    style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.08);">

                <!-- Header -->
                 <tr>
                    <td style="background:#004E60;padding:22px 24px;text-align:center;">
                    <img src="https://fulmuv.com/admin/' . $correo_contenedor["imagen"] . '" alt="FULMUV" style="max-width:190px;width:100%;height:auto;display:block;margin:0 auto;">
                    <div style="margin-top:12px;color:#e6f9fc;font-size:13px;letter-spacing:.3px;">
                        Acceso para clientes
                    </div>
                    </td>
                </tr>

                <!-- Contenido -->
                <tr>
                    <td style="padding:26px 24px 10px 24px;">
                    <h1 style="margin:0 0 8px 0;font-size:22px;line-height:1.25;color:#0f172a;">
                        ¡Bienvenido(a) a FULMUV!
                    </h1>

                    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.6;color:#334155;">
                        Hola <b>' . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . '</b>, tu cuenta ha sido creada.
                        Inicia sesión en la <b>plataforma web</b> para <b>dar seguimiento a tu pedido</b>, revisar el estado de tu compra,
                        ver detalles del envío y gestionar tus pagos cuando aplique.
                    </p>

                    <!-- Card credenciales -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                            style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:14px 14px;margin:14px 0;">
                        <tr>
                        <td style="padding:0;">
                            <div style="font-size:13px;color:#64748b;margin-bottom:10px;">
                            Tus datos de acceso
                            </div>

                            <div style="font-size:14px;color:#0f172a;line-height:1.5;margin-bottom:8px;">
                            <b>Usuario:</b>
                            <span style="color:#0f172a;">' . htmlspecialchars($correo, ENT_QUOTES, "UTF-8") . '</span>
                            </div>

                            <div style="font-size:14px;color:#0f172a;line-height:1.5;">
                            <b>Contraseña:</b>
                            <span style="color:#0f172a;">' . htmlspecialchars($pass, ENT_QUOTES, "UTF-8") . '</span>
                            </div>

                            <div style="margin-top:12px;font-size:12px;color:#64748b;line-height:1.5;">
                            Recomendación: cambia tu contraseña después de ingresar para mayor seguridad.
                            </div>
                        </td>
                        </tr>
                    </table>

                    <!-- Info de seguimiento -->
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 10px 0;">
                        <tr>
                        <td style="font-size:13px;color:#334155;line-height:1.6;">
                            <b>Dentro de tu cuenta podrás:</b>
                            <ul style="margin:8px 0 0 18px;padding:0;color:#334155;">
                            <li>Ver el estado de tu pedido (pendiente, procesado, enviado, entregado).</li>
                            <li>Revisar el detalle de productos y valores.</li>
                            <li>Consultar información de envío y guía cuando esté disponible.</li>
                            </ul>
                        </td>
                        </tr>
                    </table>

                    <!-- Botón CTA -->
                    <div style="text-align:center;padding:14px 0 4px 0;">
                        <a href="https://fulmuv.com/login.php"
                        style="display:inline-block;background:#004E60;color:#ffffff;text-decoration:none;font-weight:700;
                                font-size:14px;padding:12px 18px;border-radius:10px;">
                        Iniciar sesión y ver mi pedido
                        </a>
                    </div>

                    <p style="margin:12px 0 0 0;font-size:12px;line-height:1.6;color:#64748b;text-align:center;">
                        Si no reconoces este registro, por favor ignora este mensaje o contáctanos.
                    </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:16px 24px;background:#ffffff;border-top:1px solid #e5e7eb;">
                    <div style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.5;">
                        © ' . date('Y') . ' FULMUV · Seguimiento de pedidos en plataforma web
                    </div>
                    </td>
                </tr>

                </table>

            </td>
            </tr>
        </table>
        </body>
        </html>
        ');

        return $mail->send();
    }

    public function getEmpleoById($id_empleo)
    {
        $stmt = $this->conn->prepare("SELECT * 
        FROM empleos
        WHERE estado = 'A' AND id_empleo = ?");
        $stmt->bind_param("s", $id_empleo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            /* if ($detalle) {
                $row["sucursales"] = $this->getSucursalesByEmpresa($row["id_empresa"]);
                $row["usuarios"] = [];
            } */
            $row["archivos"] = $this->getArchivosByEmpleos($row["id_empleo"]);
            return $row;
        }
        return RECORD_DOES_NOT_EXIST;
    }

    public function getEmpleosAll()
    {

        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM empleos
        WHERE estado = 'A'");
        // $stmt->bind_param("s", $id_empleo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($this->getPublicEmpresaIdForCreator($row["id_empresa"], $row["tipo_creador"] ?? "empresa") <= 0) {
                continue;
            }
            $response[] = $row;
        }
        return $response;
    }

    public function updateEmpleo(
        $id_empleo,
        $titulo,
        $descripcion,
        $provincia,
        $canton,
        $tags,
        $img_frontal,
        $img_posterior,
        $id_empresa,
        $tipo_creador,
        $fecha_inicio,
        $fecha_fin,
        $estado,
        $archivos
    ) {
        // Si en edición no mandas nuevas imágenes, no las actualices
        $setImgs = "";
        $types = "ssssssiss";
        $params = [];

        // Base SET
        $sql = "UPDATE empleos SET 
                titulo=?,
                descripcion=?,
                provincia=?,
                canton=?,
                tags=?,
                id_empresa=?,
                tipo_creador=?,
                fecha_inicio=?,
                fecha_fin=?";

        $params[] = $titulo;
        $params[] = $descripcion;
        $params[] = $provincia;
        $params[] = $canton;
        $params[] = $tags;
        $params[] = (int)$id_empresa;
        $params[] = $tipo_creador;
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;

        // img_frontal opcional
        if (!empty($img_frontal)) {
            $sql .= ", img_frontal=?";
            $types .= "s";
            $params[] = $img_frontal;
        }

        // img_posterior opcional
        if (!empty($img_posterior)) {
            $sql .= ", img_posterior=?";
            $types .= "s";
            $params[] = $img_posterior;
        }

        // estado opcional
        if (!empty($estado)) {
            $sql .= ", estado=?";
            $types .= "s";
            $params[] = $estado;
        }

        $sql .= " WHERE id_empleo=?";
        $types .= "i";
        $params[] = (int)$id_empleo;

        $stmt = $this->conn->prepare($sql);

        // bind_param dinámico
        $stmt->bind_param($types, ...$params);

        $ok = $stmt->execute();
        if (!$ok) {
            // para depurar
            // error_log("UPDATE EMPLEO ERROR: " . $stmt->error);
            $stmt->close();
            return RECORD_UPDATED_FAILED;
        }

        $stmt->close();
        // ✅ Si quieres también actualizar anexos:
        // - lo típico: borrar los anteriores y reinsertar los nuevos
        if (!empty($archivos) && is_array($archivos)) {
            $stmtArchivos = $this->conn->prepare("INSERT INTO archivos_empleos (id_empleo, archivo, tipo) VALUES (?, ?, ?)");
            foreach ($archivos["archivos"] as $archivoData) {
                $rutaArchivo = $archivoData['archivo'];
                $tipoArchivo = $archivoData['tipo'];
                $stmtArchivos->bind_param("iss", $id_empleo, $rutaArchivo, $tipoArchivo);
                $stmtArchivos->execute();
            }
            $stmtArchivos->close();
        }

        return RECORD_UPDATED_SUCCESSFULLY;
    }


    public function createPostulanteTrabajoEmpresa($nombres_apellidos, $cedula, $correo, $telefono, $cv, $id_empleo, $id_empresa)
    {
        $stmt = $this->conn->prepare("
        INSERT INTO postulante_trabajo_empresa
        (nombres_apellidos, correo, cedula, telefono, id_empleo, id_empresa, cv)
        VALUES (?,?,?,?,?,?,?)
    ");

        $stmt->bind_param(
            "ssssiis",
            $nombres_apellidos,
            $correo,
            $cedula,
            $telefono,
            $id_empleo,
            $id_empresa,
            $cv
        );

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return RECORD_CREATED_SUCCESSFULLY;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function registroMultipleProductosExcel($data)
    {
        [
            $titulo,
            $marcaNombre,
            $nombre,
            $descripcion,
            $codigo,
            $categoriaNombre,
            $subCategoriaNombre,
            $tags,
            $precio,
            $descuento,
            $peso,
            $iva,
            $negociable,
            $unidad,
            $marcas_vehiculo,
            $modelosTxt,
            $tipoAutoTxt,
            $traccionTxt,
            $referencias,
            $emergencia_24_7,
            $emergencia_carretera,
            $emergencia_domicilio,
            $archivos,
            $id_empresa,
            $tipo_creador
        ] = $data;

        $id_marca_producto = $this->obtenerId('marcas_productos', 'id_marca_producto', 'nombre', $marcaNombre);
        /*$id_categoria = $this->obtenerIdsMultiples('categorias', 'id_categoria', 'nombre', $categoriaNombre);
        $id_categoria = json_encode($id_categoria, JSON_UNESCAPED_UNICODE);
        $id_subcategoria = $this->obtenerIdsMultiples('sub_categorias', 'id_sub_categoria', 'nombre', $subCategoriaNombre);
        $id_subcategoria = json_encode($id_subcategoria, JSON_UNESCAPED_UNICODE);
        $id_marca_vehiculo = $this->obtenerIdsMultiples('marcas', 'id_marca', 'nombre', $marcas_vehiculo);
        $id_marca_vehiculo = json_encode($id_marca_vehiculo, JSON_UNESCAPED_UNICODE);
        $modelos = $this->obtenerIdsMultiples('modelos_autos', 'id_modelos_autos', 'nombre', $modelosTxt);
        $modelos = json_encode($modelos, JSON_UNESCAPED_UNICODE);
        $tipo_auto = $this->obtenerIdsMultiples('tipos_auto', 'id_tipo_auto', 'nombre', $tipoAutoTxt);
        $tipo_auto = json_encode($tipo_auto, JSON_UNESCAPED_UNICODE);
        $traccion = $this->obtenerIdsMultiples('tipo_traccion', 'id_tipo_traccion', 'nombre', $traccionTxt);
        $traccion = json_encode($traccion, JSON_UNESCAPED_UNICODE);
        $referencias = json_encode($referencias, JSON_UNESCAPED_UNICODE);*/
        $id_categoria       = $this->obtenerIdsMultiples('categorias', 'id_categoria', 'nombre', $categoriaNombre);
        $id_subcategoria    = $this->obtenerIdsMultiples('sub_categorias', 'id_sub_categoria', 'nombre', $subCategoriaNombre);
        $id_marca_vehiculo  = $this->obtenerIdsMultiples('marcas', 'id_marca', 'nombre', $marcas_vehiculo);
        $modelos            = $this->obtenerIdsMultiples('modelos_autos', 'id_modelos_autos', 'nombre', $modelosTxt);
        $tipo_auto          = $this->obtenerIdsMultiples('tipos_auto', 'id_tipo_auto', 'nombre', $tipoAutoTxt);
        $traccion           = $this->obtenerIdsMultiples('tipo_traccion', 'id_tipo_traccion', 'nombre', $traccionTxt);

        $id_categoria_json      = $this->jsonArrayStrings($id_categoria);
        $id_subcategoria_json   = $this->jsonArrayStrings($id_subcategoria);
        $id_marca_json          = $this->jsonArrayStrings($id_marca_vehiculo);
        $modelos_json           = $this->jsonArrayStrings($modelos);
        $tipo_auto_json         = $this->jsonArrayStrings($tipo_auto);
        $traccion_json          = $this->jsonArrayStrings($traccion);
        $referencias_json       = $this->jsonArrayStrings($referencias);



        $stmt = $this->conn->prepare("INSERT INTO productos (titulo_producto,marca_producto,nombre,descripcion,codigo,categoria,sub_categoria,tags,precio_referencia,id_empresa,descuento,peso,iva,negociable,unidad,id_marca,id_modelo,tipo_auto,tipo_traccion,referencias,emergencia_24_7,emergencia_carretera,emergencia_domicilio,tipo_creador) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssssssssssssssssss", $titulo, $id_marca_producto, $nombre, $descripcion, $codigo, $id_categoria_json, $id_subcategoria_json, $tags, $precio, $id_empresa, $descuento, $peso, $iva, $negociable, $unidad, $id_marca_json, $modelos_json, $tipo_auto_json, $traccion_json, $referencias_json, $emergencia_24_7, $emergencia_carretera, $emergencia_domicilio, $tipo_creador);
        $result = $stmt->execute();
        $id_producto = $this->conn->insert_id;
        $stmt->close();
        if ($result) {
            //return RECORD_CREATED_SUCCESSFULLY;
            return $id_producto;
        } else {
            return RECORD_CREATION_FAILED;
        }
    }

    public function jsonArrayStrings($value)
    {
        // null o vacío -> []
        if ($value === null) return json_encode([]);
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || strtolower($value) === 'null') return json_encode([]);

            // Si ya viene como JSON: ["1","2"] o [1,2]
            if (preg_match('/^\s*\[.*\]\s*$/', $value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $decoded = array_values(array_unique(array_map(fn($x) => (string)$x, $decoded)));
                    return json_encode($decoded, JSON_UNESCAPED_UNICODE);
                }
            }

            // Si viene como CSV: "Toyota, Kia"
            $value = str_replace(';', ',', $value);
            $parts = array_filter(array_map('trim', explode(',', $value)), fn($x) => $x !== '');
            $parts = array_values(array_unique(array_map(fn($x) => (string)$x, $parts)));
            return json_encode($parts, JSON_UNESCAPED_UNICODE);
        }

        // Si viene array PHP
        if (is_array($value)) {
            $value = array_values(array_unique(array_map(fn($x) => (string)$x, $value)));
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // Si viene número suelto
        return json_encode([(string)$value], JSON_UNESCAPED_UNICODE);
    }


    public function obtenerId($tabla, $pk, $campo, $valor)
    {
        $id = null;

        $sql = "SELECT $pk FROM $tabla WHERE $campo = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();

        if (empty($id)) {
            $sql = "INSERT INTO $tabla ($campo) VALUES (?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $valor);
            $stmt->execute();
            $id = $this->conn->insert_id;
            $stmt->close();
        }

        return $id;
    }

    public function obtenerIdsMultiples($tabla, $pk, $campo, $texto)
    {
        if ($texto === null) return [];
        $texto = trim((string)$texto);
        if ($texto === '') return [];

        // soporta ; o ,
        $texto = str_replace(';', ',', $texto);

        // si ya viene JSON en texto: ["1","2"]
        if (preg_match('/^\s*\[.*\]\s*$/', $texto)) {
            $decoded = json_decode($texto, true);
            if (is_array($decoded)) {
                return array_values(array_unique(array_map(fn($x) => (string)$x, $decoded)));
            }
        }

        $items = array_filter(array_map('trim', explode(',', $texto)), fn($x) => $x !== '');

        $ids = [];
        foreach ($items as $item) {
            $ids[] = (string) $this->obtenerId($tabla, $pk, $campo, $item); // 👈 string
        }

        return array_values(array_unique($ids));
    }


    /*function obtenerIdsMultiples($tabla, $pk, $campo, $texto) {
        $items = array_map('trim', explode(',', $texto));
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $this->obtenerId($tabla, $pk, $campo, $item);
        }
        return $ids;
    }*/


    public function getProvinciasGrupoEntrega()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://portalentregas.com/ApisGenesys/api/ObtenerProvincia',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=gb58j1v9ke5hkebbmd1juqtake; sc_actual_lang_eLogisticsApis=es'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Validar error de CURL
        if ($response === false) {
            return [
                "error" => true,
                "message" => "Error al conectar con la API."
            ];
        }

        // Convertimos JSON a array
        $data = json_decode($response, true);

        // Validar JSON inválido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "error" => true,
                "message" => "Respuesta inválida de la API.",
                "raw"    => $response
            ];
        }

        return $data; // ← Aquí ya retorna un array
    }


    public function getCantonesByIdProvinciaGrupoEntrega($id_provincia)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://portalentregas.com/ApisGenesys/api/ObtenerCanton?Provincia=' . $id_provincia,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=gb58j1v9ke5hkebbmd1juqtake; sc_actual_lang_eLogisticsApis=es'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Validar error de CURL
        if ($response === false) {
            return [
                "error" => true,
                "message" => "Error al conectar con la API."
            ];
        }

        // Convertimos JSON a array
        $data = json_decode($response, true);

        // Validar JSON inválido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "error" => true,
                "message" => "Respuesta inválida de la API.",
                "raw"    => $response
            ];
        }

        return $data; // ← Aquí ya retorna un array
    }


    public function getParroquiaByIdCantonGrupoEntrega($id_canton)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://portalentregas.com/ApisGenesys/api/ObtenerParroquia?Canton=' . $id_canton,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=gb58j1v9ke5hkebbmd1juqtake; sc_actual_lang_eLogisticsApis=es'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Validar error de CURL
        if ($response === false) {
            return [
                "error" => true,
                "message" => "Error al conectar con la API."
            ];
        }

        // Convertimos JSON a array
        $data = json_decode($response, true);

        // Validar JSON inválido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "error" => true,
                "message" => "Respuesta inválida de la API.",
                "raw"    => $response
            ];
        }

        return $data; // ← Aquí ya retorna un array
    }


    public function insertRutasExcel($rows)
    {
        // Validación rápida
        if (!is_array($rows) || count($rows) === 0) {
            return ["error" => true, "msg" => "El array está vacío", "insertados" => 0, "fallidos" => []];
        }

        // OJO: tu tabla (según captura) tiene:
        // provincia, canton, parroquia, tipo_cobertura, dias_laborables, fuera_cobertura, valor_kg, valor_kg_adicional, estado
        $sql = "INSERT INTO ruta_grupo
            (provincia, canton, parroquia, tipo_cobertura, dias_laborables, fuera_cobertura, valor_kg, valor_kg_adicional, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'A')";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ["error" => true, "msg" => "Error prepare: " . $this->conn->error];
        }

        $insertados = 0;
        $fallidos = [];

        // Transacción
        $this->conn->begin_transaction();

        try {
            foreach ($rows as $i => $r) {
                // Asegurar keys esperadas (tu JSON ya viene con estos nombres)
                $provincia       = trim((string)($r["provincia"] ?? ""));
                $canton          = trim((string)($r["canton"] ?? ""));
                $parroquia       = trim((string)($r["parroquia"] ?? ""));
                $tipo_cobertura  = trim((string)($r["tipo_cobertura"] ?? ""));
                $dias_laborables = trim((string)($r["dias_laborables"] ?? ""));
                $fuera_cobertura = trim((string)($r["fuera_cobertura"] ?? ""));
                $valor_kg        = (string)($r["valor_kg"] ?? "0");
                $valor_add       = (string)($r["valor_kg_adicional"] ?? "0");

                // Normalizar decimales: "5,69" -> "5.69"
                $valor_kg  = str_replace(",", ".", trim($valor_kg));
                $valor_add = str_replace(",", ".", trim($valor_add));

                // Validación mínima (ajusta a tu necesidad)
                if ($provincia === "" || $canton === "" || $tipo_cobertura === "") {
                    $fallidos[] = ["fila" => $i + 2, "motivo" => "Campos obligatorios vacíos (provincia/canton/tipo_cobertura)"];
                    continue;
                }

                // Si tu DB tiene valor_kg como VARCHAR, puedes dejarlo string.
                // Si lo cambias a DECIMAL, esto sigue funcionando (mejor).
                $stmt->bind_param(
                    "ssssssss",
                    $provincia,
                    $canton,
                    $parroquia,
                    $tipo_cobertura,
                    $dias_laborables,
                    $fuera_cobertura,
                    $valor_kg,
                    $valor_add
                );

                if ($stmt->execute()) {
                    $insertados++;
                } else {
                    $fallidos[] = [
                        "fila" => $i + 2, // +2 porque fila 1 es cabecera y arrays comienzan en 0
                        "motivo" => $stmt->error
                    ];
                }
            }

            // Si quieres que aunque existan fallidos se guarde lo correcto:
            $this->conn->commit();

            $stmt->close();

            return [
                "error" => false,
                "msg" => "Proceso finalizado",
                "insertados" => $insertados,
                "fallidos" => $fallidos
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            $stmt->close();

            return [
                "error" => true,
                "msg" => "Error en transacción: " . $e->getMessage(),
                "insertados" => $insertados,
                "fallidos" => $fallidos
            ];
        }
    }


    public function enviarGraciasCompra(int $id_orden): array
    {
        // 1) Construir el body
        $det = $this->construirBodyGraciasCompra($id_orden);
        if (!empty($det['error'])) {
            return $det;
        }

        $toEmail = trim((string)($det['cliente_email'] ?? ''));
        if ($toEmail === '') {
            return ['error' => true, 'msg' => 'La orden no tiene correo de cliente.'];
        }

        // 2) Enviar con PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host       = "smtp.gmail.com";
            $mail->Port       = 465;
            $mail->Username   = 'bonsaidev@bonsai.com.ec';
            $mail->Password   = 'ykdvtvcizzgjyfhy';

            $mail->setFrom("bonsaidev@bonsai.com.ec", "FulMuv");
            $mail->isHTML(true);

            // Importante para tildes/ñ
            $mail->CharSet = 'UTF-8';

            $mail->Subject = "Gracias por tu compra en FULMUV (#{$id_orden})";
            $mail->addAddress($toEmail);

            $mail->addBCC("jacarrasco@bonsai.com.ec");

            // Body (HTML generado)
            $mail->Body = $det['body_html'];

            // Fallback texto plano (por si un cliente bloquea HTML)
            $mail->AltBody = "Gracias por tu compra en FULMUV. Revisa el detalle en tu cuenta. Orden #{$id_orden}";

            $ok = $mail->send();

            return [
                'error' => !$ok,
                'msg'   => $ok ? 'Correo enviado correctamente.' : 'No se pudo enviar el correo.',
                'to'    => $toEmail,
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'msg'   => 'Error enviando correo: ' . $e->getMessage(),
                'to'    => $toEmail,
            ];
        }
    }

    public function construirBodyGraciasCompra(int $id_orden): array
    {
        date_default_timezone_set('America/Guayaquil');

        // =========================
        // 0) Contenedor / cabecera (logo FULMUV)
        // =========================
        $correo_contenedor = $this->getContenedor(); // Debe devolver ['imagen' => 'ruta/archivo.png'] al menos
        $logoPath = isset($correo_contenedor["imagen"]) ? trim((string)$correo_contenedor["imagen"]) : '';

        // Si la ruta ya viene con http(s), úsala tal cual. Si no, arma URL absoluta.
        $logoUrl = '';
        if ($logoPath !== '') {
            $logoUrl = (preg_match('/^https?:\/\//i', $logoPath))
                ? $logoPath
                : "https://fulmuv.com/admin/" . ltrim($logoPath, '/');
        }

        $headerHtml = '
        <div style="background:#00686f; padding:14px 16px; text-align:center;">
            ' . ($logoUrl !== ''
            ? '<img src="' . htmlspecialchars($logoUrl) . '" width="200" style="max-width:200px;width:200px;height:auto;display:inline-block;" alt="FULMUV"/>'
            : '<div style="color:#fff;font-weight:700;font-size:18px;letter-spacing:.5px;">FULMUV</div>'
        ) . '
        </div>
    ';

        // =========================
        // 1) Traer filas (una o varias) de la orden
        // =========================
        $sql = "
        SELECT 
            o.numero_orden,
            o.subtotal,
            o.iva,
            o.total,
            o.created_at,

            c.nombres AS cliente_nombres,
            c.apellidos AS cliente_apellidos,
            c.correo AS cliente_correo,

            oe.id_orden,
            oe.productos,
            oe.id_trayecto,
            oe.peso_total,
            oe.peso_real_total_kg,

            t.nombre AS trayecto_nombre,
            t.valor  AS trayecto_valor_base_2kg,
            t.adicional AS trayecto_valor_adicional_kg
        FROM ordenes o
        INNER JOIN clientes c ON c.id_cliente = o.id_cliente
        INNER JOIN ordenes_empresas oe ON oe.id_ordenes = o.id_orden
        LEFT JOIN trayecto t ON t.id_trayecto = oe.id_trayecto
        WHERE oe.id_orden = ?
    ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['error' => true, 'msg' => 'Prepare falló: ' . $this->conn->error];

        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $res = $stmt->get_result();

        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();

        if (empty($rows)) return ['error' => true, 'msg' => 'No se encontró la orden.'];

        // =========================
        // 2) Datos del cliente / orden
        // =========================
        $first = $rows[0];
        $clienteNombre = trim(($first['cliente_nombres'] ?? '') . ' ' . ($first['cliente_apellidos'] ?? ''));
        $numeroOrden   = $first['numero_orden'] ?? $id_orden;

        // =========================
        // 3) Convertir subtotal/iva/total
        // =========================
        $toFloat = function ($v) {
            $v = (string)$v;
            $v = str_replace(['$', ' '], '', $v);
            $v = str_replace(',', '.', $v);
            return (float)$v;
        };

        $subtotal = $toFloat($first['subtotal'] ?? 0);
        $iva      = $toFloat($first['iva'] ?? 0);
        $total    = $toFloat($first['total'] ?? 0);

        // =========================
        // 4) Acumular productos desde JSON (sin descuentos)
        // =========================
        $productos = [];
        foreach ($rows as $r) {
            $arr = $r['productos'] ?? '[]';
            if (is_string($arr)) $arr = json_decode($arr, true);
            if (!is_array($arr)) $arr = [];
            foreach ($arr as $p) $productos[] = $p;
        }

        $rowsHtml = '';
        foreach ($productos as $p) {
            $nombre = trim((string)($p['nombre'] ?? $p['tags'] ?? 'Producto'));
            $precio = $toFloat($p['precio'] ?? 0);
            $cant   = (int)($p['cantidad'] ?? 1);

            $lineaTotal = max(0, $precio * $cant);

            $rowsHtml .= '
            <tr>
                <td style="padding:10px;border-bottom:1px solid #eaecf0;">
                    <div style="font-weight:600;color:#101828;">' . htmlspecialchars($nombre) . '</div>
                    <div style="font-size:12px;color:#667085;">Precio: $' . number_format($precio, 2) . ' · Cant: ' . $cant . '</div>
                </td>
                <td align="right" style="padding:10px;border-bottom:1px solid #eaecf0;font-weight:600;color:#101828;">
                    $' . number_format($lineaTotal, 2) . '
                </td>
            </tr>
        ';
        }

        // =========================
        // 5) Envío con seguro 10%: (base + adicionales) * 1.10
        // =========================
        $envioEstimadoTotal = 0.0;
        $envioDetalles = [];

        foreach ($rows as $r) {
            $trayectoNombre = (string)($r['trayecto_nombre'] ?? 'ESPECIAL');
            $base2kg        = $toFloat($r['trayecto_valor_base_2kg'] ?? 0);
            $adicionalKg    = $toFloat($r['trayecto_valor_adicional_kg'] ?? 0);

            $peso = $toFloat($r['peso_real_total_kg'] ?? 0);
            if ($peso <= 0) $peso = $toFloat($r['peso_total'] ?? 0);

            $extra = max(0, $peso - 2);
            $kgExtraCobro = (int)ceil($extra);

            $envioSinSeguro = $base2kg + ($kgExtraCobro * $adicionalKg);
            $envioConSeguro = $envioSinSeguro * 1.10;

            $envioEstimadoTotal += $envioConSeguro;

            $envioDetalles[] = [
                'trayecto' => $trayectoNombre,
                'peso' => $peso,
                'base2kg' => $base2kg,
                'adicional' => $adicionalKg,
                'kg_extra' => $kgExtraCobro,
                'envio_sin_seguro' => $envioSinSeguro,
                'envio' => $envioConSeguro
            ];
        }

        $saludo = 'Gracias ' . htmlspecialchars($clienteNombre) . ' por tu compra en FULMUV.';
        $fecha  = date('Y-m-d H:i:s');

        $envioMsg = '';
        foreach ($envioDetalles as $d) {
            $envioMsg .= '
            <div style="margin-top:6px;color:#344054;font-size:13px;">
                <b>Trayecto:</b> ' . htmlspecialchars($d['trayecto']) . ' ·
                <b>Peso:</b> ' . number_format($d['peso'], 2) . ' kg ·
                <b>Base (2kg):</b> $' . number_format($d['base2kg'], 2) . ' ·
                <b>Kg extra:</b> ' . (int)$d['kg_extra'] . ' × $' . number_format($d['adicional'], 2) . '
                = <b>$' . number_format($d['envio_sin_seguro'], 2) . '</b>
                <span style="color:#0f766e;font-weight:700;"> + Seguro (10%)</span>
                = <b style="color:#101828;">$' . number_format($d['envio'], 2) . '</b>
            </div>
        ';
        }

        // =========================
        // 6) Body final con cabecera (logo)
        // =========================
        $body = '
        <div style="background:#f3f4f6;padding:18px 0;">
            <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #eaecf0;border-radius:14px;overflow:hidden;">
                ' . $headerHtml . '

                <div style="padding:18px;">
                    <div style="font:14px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#344054;">
                        <p style="margin:0 0 10px 0;"><b>' . $saludo . '</b></p>
                        <p style="margin:0 0 10px 0;">Este es el detalle de tu orden en FULMUV (<b>#' . htmlspecialchars((string)$numeroOrden) . '</b>)</p>

                        <div style="background:#f9fafb;border:1px solid #eaecf0;border-radius:10px;padding:12px;margin:12px 0;">
                            <div style="font-weight:700;color:#101828;margin-bottom:6px;">Detalle de productos</div>

                            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#fff;border:1px solid #eaecf0;border-radius:10px;overflow:hidden;">
                                <thead>
                                    <tr>
                                        <th align="left" style="padding:10px;background:#f2f4f7;border-bottom:1px solid #eaecf0;color:#475467;font-size:12px;">Producto</th>
                                        <th align="right" style="padding:10px;background:#f2f4f7;border-bottom:1px solid #eaecf0;color:#475467;font-size:12px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ' . (!empty($rowsHtml) ? $rowsHtml : '
                                    <tr><td style="padding:10px;">(Sin productos disponibles)</td><td></td></tr>') . '
                                </tbody>
                            </table>

                            <div style="margin-top:12px;border-top:1px dashed #d0d5dd;padding-top:10px;">
                                <div style="display:flex;justify-content:space-between;margin:4px 0;">
                                    <span>Subtotal</span><b>$' . number_format($subtotal, 2) . '</b>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin:4px 0;">
                                    <span>IVA</span><b>$' . number_format($iva, 2) . '</b>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin:6px 0;font-size:16px;color:#101828;">
                                    <span>Total a pagar</span><b>$' . number_format($total, 2) . '</b>
                                </div>
                            </div>

                            <div style="margin-top:12px;color:#667085;font-size:12px;">
                                Fecha: ' . $fecha . '
                            </div>
                        </div>

                        <p style="margin:0;">
                            Puedes revisar tu orden aquí:
                            <a href="https://fulmuv.com/login.php" style="color:#0ea5e9;text-decoration:none;font-weight:600;">
                                Ver orden en FULMUV
                            </a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    ';

        return [
            'error' => false,
            'cliente_email' => $first['cliente_correo'] ?? null,
            'cliente_nombre' => $clienteNombre,
            'body_html' => $body
        ];
    }

    public function getBorradorProductos($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM productos
        WHERE id_empresa = ? AND estado = 'P' AND tipo_producto = 'producto';");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            // $row['categorias']    = $this->getCategoriaByArray($row['categoria']);       // 👈 le paso el JSON/array
            // if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "producto") {
            $response[] = $row;
            // }
        }
        return $response;
    }

    public function getBorradorServicios($id_empresa)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM productos
        WHERE id_empresa = ? AND estado = 'P' AND tipo_producto = 'servicio';");
        $stmt->bind_param("s", $id_empresa);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row["archivos"] = $this->getArchivosByProductos($row["id_producto"]);
            // $row['categorias']    = $this->getCategoriaByArray($row['categoria']);       // 👈 le paso el JSON/array
            // if (!empty($row["categorias"]) && $row["categorias"][0]["tipo"] === "servicio") {
            $response[] = $row;
            // }
        }
        return $response;
    }

    public function publicarSeleccionados($id_empresa, $idsJson)
    {
        $ids = json_decode($idsJson, true);
        if (!is_array($ids)) $ids = [];

        // limpiar: solo enteros
        $ids = array_values(array_filter(array_map('intval', $ids), function ($x) {
            return $x > 0;
        }));

        if (count($ids) === 0) {
            return ["error" => true, "msg" => "No hay ids para publicar"];
        }

        // placeholders ?,?,?,?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "UPDATE productos
                SET estado='A'
                WHERE id_empresa=? AND id_producto IN ($placeholders)";

        $stmt = $this->conn->prepare($sql);

        // bind dinámico
        $types = str_repeat('i', 1 + count($ids));
        $params = array_merge([$id_empresa], $ids);

        // mysqli bind_param requiere referencias
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        $ok = $stmt->execute();
        $updated = $stmt->affected_rows;

        return ["error" => !$ok, "updated" => $updated];
    }


    public function apiTrackingGrupoEntregas(string $codigo): array
    {
        date_default_timezone_set('America/Guayaquil');

        $codigo = trim($codigo);
        if ($codigo === '') {
            return ['error' => true, 'msg' => 'Código de tracking vacío'];
        }

        // ✅ 1) URL del API Tracking (CAMBIA AQUÍ según documentación real)
        // Ejemplos típicos:
        // - https://portalentregas.com/ElogisticsApis/api_tracking/
        // - https://portalentregas.com/ElogisticsApis/api_trackingGuia/
        $endpoint = "https://portalentregas.com/ApisGenesys/api/ConsultaGuiaNAC"; // <-- AJUSTAR

        // ✅ 2) Autenticación (si el tracking usa token/usuario igual que el otro API)
        $token = "Aig%402018!";
        $usuario = "API_DESARROLLO";

        // Si va por query params:
        $url = $endpoint . "?token={$token}&usuario={$usuario}";

        // ✅ 3) Payload POST (CAMBIA EL NOMBRE DEL CAMPO si aplica)
        $payload = [
            "codigo" => $codigo, // <-- puede ser "barcode" o "guia" según API real
        ];

        // ✅ 4) cURL
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return [
                'error' => true,
                'msg'   => 'cURL error: ' . $err,
                'http'  => $httpCode,
            ];
        }

        $json = json_decode($response, true);

        if (!is_array($json)) {
            return [
                'error'   => true,
                'msg'     => 'Respuesta no es JSON válido',
                'http'    => $httpCode,
                'raw'     => $response,
                'payload' => $payload,
            ];
        }

        // ✅ 5) Normalizar salida (depende de cómo responda tu API)
        // Asumimos algo tipo:
        // { status: "success", details: [...], ... }
        $status  = $json['status'] ?? null;
        $details = $json['details'] ?? null;

        // Si quieres extraer eventos/historial (si vienen en array):
        $eventos = [];
        if (is_array($details)) {
            // Ajusta el mapping según tu JSON real
            foreach ($details as $d) {
                $eventos[] = [
                    'fecha'       => $d['fecha'] ?? ($d['date'] ?? null),
                    'hora'        => $d['hora'] ?? ($d['time'] ?? null),
                    'estado'      => $d['estado'] ?? ($d['status'] ?? null),
                    'detalle'     => $d['detalle'] ?? ($d['description'] ?? null),
                    'ubicacion'   => $d['ubicacion'] ?? ($d['location'] ?? null),
                ];
            }
        }

        return [
            'error'   => ($status !== 'success'),
            'http'    => $httpCode,
            'status'  => $status,
            'codigo'  => $codigo,
            'eventos' => $eventos,   // útil si el API trae historial
            'raw'     => $json,      // respuesta completa por si necesitas campos extra
            'payload' => $payload,
        ];
    }



    public function getEmpleoEnviado($id_empleo)
    {
        $response = array();
        $stmt = $this->conn->prepare("SELECT * 
        FROM postulante_trabajo_empresa
        WHERE id_empleo = ? AND estado = 'A'");
        $stmt->bind_param("s", $id_empleo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
        return $response;
    }

    public function notificaEmpresaVerificacionEnProceso(int $id_empresa)
    {
        $empresa = $this->getEmpresaById($id_empresa);
        $correo_contenedor = $this->getContenedor();

        // if (!$empresa || empty($empresa["correo"])) {
        //     return false;
        // }

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;

        // ✅ Credenciales desde ENV (recomendado)
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Verificación en proceso - FULMUV';

        // destinatario: empresa
        //$mail->AddAddress($empresa["correo"]);
        $mail->AddBCC("jacarrasco@bonsai.com.ec");
        $nombre = $empresa["nombre_titular"] ?? $empresa["nombre_comercial"] ?? 'Tu empresa';
        $logo = "https://fulmuv.com/admin/" . ($correo_contenedor["imagen"]);

        $mail->Body = '
    <!doctype html>
    <html lang="es">
    <head><meta charset="utf-8"></head>
    <body style="margin:0;padding:0;background:#f2f4f7;">
      <center style="width:100%;background:#f2f4f7;">
        <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="padding:24px 16px 8px 16px;text-align:center;">
              <img src="' . $logo . '" width="160" alt="FULMUV" style="display:block;margin:0 auto 8px auto;border:0;">
            </td>
          </tr>

          <tr>
            <td style="padding:0 16px 12px 16px;">
              <table role="presentation" width="100%" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td style="padding:16px 20px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td style="font:600 16px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#101828;">
                          Tu verificación está en proceso
                        </td>
                        <td align="right" style="font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#667085;">
                          ' . $fecha . '
                        </td>
                      </tr>
                    </table>

                    <div style="margin-top:10px;font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#344054;">
                      <p style="margin:0 0 10px 0;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>

                      <p style="margin:0 0 10px 0;">
                        Hemos recibido tu solicitud de <strong>verificación de empresa</strong>.
                        En este momento tu verificación se encuentra <strong>en proceso de revisión</strong>.
                      </p>

                      <p style="margin:0 0 10px 0;">
                        Te notificaremos por este medio cuando el proceso finalice y tu sello de
                        <strong>Empresa Verificada</strong> esté activo.
                      </p>
                    </div>

                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 16px 32px 16px;text-align:center;">
              <div style="font:12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#98a2b3;">
                © ' . date('Y') . ' FULMUV
              </div>
            </td>
          </tr>
        </table>
      </center>
    </body>
    </html>';

        return $mail->send();
    }

    public function notificaFulmuvNuevaVerificacion(int $id_empresa)
    {
        $empresa = $this->getEmpresaById($id_empresa);
        $correo_contenedor = $this->getContenedor();

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;

        // ✅ Credenciales desde ENV (recomendado)
        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Nueva verificación de empresa - Revisar documentos';

        // ✅ destinatarios: admins FULMUV
        $correos_default = $this->getCorreosDefault();
        if ($correos_default && is_array($correos_default)) {
            foreach ($correos_default as $c) {
                //if (!empty($c["correo"])) $mail->AddAddress($c["correo"]);
            }
        }

        $mail->AddBCC("jacarrasco@bonsai.com.ec");

        // // Si no hay admins configurados, no enviar
        // if (method_exists($mail, 'getToAddresses') && count($mail->getToAddresses()) === 0) {
        //     return false;
        // }

        $nombreComercial = $empresa["nombre_comercial"] ?? $empresa["nombre_titular"] ?? 'Empresa';
        $correoEmpresa   = $empresa["correo"] ?? '-';
        $telefonoEmpresa = $empresa["telefono_contacto"] ?? '-';

        $logo = "https://fulmuv.com/admin/" . ($correo_contenedor["imagen"]);

        $mail->Body = '
    <!doctype html>
    <html lang="es">
    <head><meta charset="utf-8"></head>
    <body style="margin:0;padding:0;background:#f2f4f7;">
      <center style="width:100%;background:#f2f4f7;">
        <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="padding:24px 16px 8px 16px;text-align:center;">
              <img src="' . $logo . '" width="160" alt="FULMUV" style="display:block;margin:0 auto 8px auto;border:0;">
            </td>
          </tr>

          <tr>
            <td style="padding:0 16px 12px 16px;">
              <table role="presentation" width="100%" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td style="padding:16px 20px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td style="font:600 16px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#101828;">
                          Nueva verificación de empresa
                        </td>
                        <td align="right" style="font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#667085;">
                          ' . $fecha . '
                        </td>
                      </tr>
                    </table>

                    <div style="margin-top:10px;font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#344054;">
                      <p style="margin:0 0 10px 0;">
                        La empresa <strong>' . htmlspecialchars($nombreComercial) . '</strong> completó el proceso de verificación.
                        Se requiere revisión de documentos.
                      </p>

                      <div style="margin-top:12px;padding:10px 12px;border:1px solid #e4e7ec;border-radius:8px;background:#f9fafb;">
                        <div><strong>ID Empresa:</strong> ' . intval($id_empresa) . '</div>
                        <div><strong>Empresa:</strong> ' . htmlspecialchars($nombreComercial) . '</div>
                        <div><strong>Correo:</strong> ' . htmlspecialchars($correoEmpresa) . '</div>
                        <div><strong>Teléfono:</strong> ' . htmlspecialchars($telefonoEmpresa) . '</div>
                      </div>

                      <p style="margin:12px 0 0 0;">
                        Acción: <strong>revisar los archivos subidos</strong> y aprobar/rechazar la verificación.
                      </p>
                    </div>

                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 16px 32px 16px;text-align:center;">
              <div style="font:12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#98a2b3;">
                © ' . date('Y') . ' FULMUV
              </div>
            </td>
          </tr>
        </table>
      </center>
    </body>
    </html>';

        return $mail->send();
    }

    public function correoVerificacionAprobada(int $id_empresa)
    {
        $empresa = $this->getEmpresaById($id_empresa);
        $correo_contenedor = $this->getContenedor();

        if (!$empresa || empty($empresa["correo"])) return false;

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;

        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Empresa verificada - FULMUV';

        // $mail->AddAddress($empresa["correo"]);
        $mail->AddBCC("jacarrasco@bonsai.com.ec");

        $logo = "https://fulmuv.com/admin/" . ltrim(($correo_contenedor["imagen"] ?? ''), "/");
        $nombre = $empresa["nombre_comercial"] ?? $empresa["nombre"] ?? $empresa["nombre_titular"] ?? 'Tu empresa';

        $mail->Body = '
    <!doctype html><html lang="es"><head><meta charset="utf-8"></head>
    <body style="margin:0;padding:0;background:#f2f4f7;">
      <center style="width:100%;background:#f2f4f7;">
        <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="padding:24px 16px 8px 16px;text-align:center;">
              <img src="' . $logo . '" width="160" alt="FULMUV" style="display:block;margin:0 auto 8px auto;border:0;">
            </td>
          </tr>

          <tr>
            <td style="padding:0 16px 12px 16px;">
              <table role="presentation" width="100%" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td style="padding:16px 20px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td style="font:600 16px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#101828;">
                          ¡Tu empresa ya está verificada!
                        </td>
                        <td align="right" style="font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#667085;">
                          ' . $fecha . '
                        </td>
                      </tr>
                    </table>

                    <div style="margin-top:10px;font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#344054;">
                      <p style="margin:0 0 10px 0;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>

                      <p style="margin:0 0 10px 0;">
                        ¡Gracias por ser parte de <strong>FULMUV</strong>! Tu cuenta ha sido <strong>verificada</strong>.
                      </p>

                      <div style="margin-top:12px;padding:10px 12px;border:1px solid #e4e7ec;border-radius:8px;background:#f9fafb;">
                        <div>✅ A partir de ahora tu perfil mostrará el <strong>sello de Empresa Verificada</strong> y tendrás mayor confianza frente a tus clientes.</div>
                      </div>

                      <p style="margin:12px 0 0 0;">Equipo FULMUV</p>
                    </div>

                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 16px 32px 16px;text-align:center;">
              <div style="font:12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#98a2b3;">
                © ' . date('Y') . ' FULMUV
              </div>
            </td>
          </tr>
        </table>
      </center>
    </body></html>';

        return $mail->send();
    }

    public function correoVerificacionRechazada(int $id_empresa, string $motivo)
    {
        $empresa = $this->getEmpresaById($id_empresa);
        $correo_contenedor = $this->getContenedor();

        if (!$empresa || empty($empresa["correo"])) return false;

        date_default_timezone_set('America/Guayaquil');
        $fecha = date('Y-m-d H:i:s');

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;

        $mail->Username = 'bonsaidev@bonsai.com.ec';
        $mail->Password = 'ykdvtvcizzgjyfhy';
        $mail->SetFrom("bonsaidev@bonsai.com.ec", "FulMuv");

        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Verificación rechazada - FULMUV';

        // $mail->AddAddress($empresa["correo"]);
        $mail->AddBCC("jacarrasco@bonsai.com.ec");

        $logo = "https://fulmuv.com/admin/" . ltrim(($correo_contenedor["imagen"] ?? ''), "/");
        $nombre = $empresa["nombre_comercial"] ?? $empresa["nombre"] ?? $empresa["nombre_titular"] ?? 'Tu empresa';

        $motivoSeguro = nl2br(htmlspecialchars(trim($motivo ?: 'Sin observación.')));

        $mail->Body = '
    <!doctype html><html lang="es"><head><meta charset="utf-8"></head>
    <body style="margin:0;padding:0;background:#f2f4f7;">
      <center style="width:100%;background:#f2f4f7;">
        <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td style="padding:24px 16px 8px 16px;text-align:center;">
              <img src="' . $logo . '" width="160" alt="FULMUV" style="display:block;margin:0 auto 8px auto;border:0;">
            </td>
          </tr>

          <tr>
            <td style="padding:0 16px 12px 16px;">
              <table role="presentation" width="100%" style="background:#ffffff;border-radius:10px;border:1px solid #e4e7ec;" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td style="padding:16px 20px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td style="font:600 16px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#101828;">
                          Verificación rechazada
                        </td>
                        <td align="right" style="font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#667085;">
                          ' . $fecha . '
                        </td>
                      </tr>
                    </table>

                    <div style="margin-top:10px;font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#344054;">
                      <p style="margin:0 0 10px 0;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>

                      <p style="margin:0 0 10px 0;">
                        Hemos revisado tu proceso de verificación y por el momento fue <strong>rechazado</strong>.
                      </p>

                      <div style="margin-top:12px;padding:12px;border:1px solid #fecaca;border-radius:8px;background:#fff1f2;">
                        <div style="font-weight:600;color:#991b1b;margin-bottom:6px;">Motivo / Observación:</div>
                        <div style="color:#7f1d1d;">' . $motivoSeguro . '</div>
                      </div>

                      <p style="margin:12px 0 0 0;">
                        Puedes corregir los documentos y volver a intentar el proceso de verificación desde tu panel en FULMUV.
                      </p>

                      <p style="margin:12px 0 0 0;">Equipo FULMUV</p>
                    </div>

                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 16px 32px 16px;text-align:center;">
              <div style="font:12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#98a2b3;">
                © ' . date('Y') . ' FULMUV
              </div>
            </td>
          </tr>
        </table>
      </center>
    </body></html>';

        return $mail->send();
    }

    public function getVehiculosBorradorByEmpresa($id_empresa)
    {
        $id_empresa = (int)$id_empresa;

        $sql = "SELECT *
                FROM vehiculos
                WHERE id_empresa = ?
                AND estado = 'P';";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("getVehiculosBorradorByEmpresa prepare error: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $id_empresa);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row["archivos"] = $this->getArchivosByVehiculos($row["id_vehiculo"]);
            $data[] = $row;
        }
        $stmt->close();

        return $data;
    }


    public function createContactoFulmuv($motivo, $nombre_empresa, $titular, $telefono, $correo, $comentario)
    {
        $stmt = $this->conn->prepare("
        INSERT INTO contacto_fulmuv
        (motivo, nombre_empresa, titular, telefono, correo, comentario)
        VALUES (?,?,?,?,?,?)
    ");

        $stmt->bind_param(
            "ssssss",
            $motivo,
            $nombre_empresa,
            $titular,
            $telefono,
            $correo,
            $comentario
        );

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {

            $this->enviarCorreoConfirmacionContactoFulmuv([
                "motivo" => $motivo,
                "nombre_empresa" => $nombre_empresa,
                "titular" => $titular,
                "telefono" => $telefono,
                "correo" => $correo,
                "comentario" => $comentario
            ]);

            // ✅ LLAMAR FUNCIÓN DE CORREO SOLO SI SE GUARDÓ
            $this->enviarCorreoNuevoAnuncioFulmuv([
                "motivo" => $motivo,
                "nombre_empresa" => $nombre_empresa,
                "titular" => $titular,
                "telefono" => $telefono,
                "correo" => $correo,
                "comentario" => $comentario
            ]);

            return RECORD_CREATED_SUCCESSFULLY;
        } else {

            return RECORD_CREATION_FAILED;
        }
    }
}
