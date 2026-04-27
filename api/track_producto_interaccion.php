<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/include/DbConnect.php';

function getEmpresaPorTipo(mysqli $conn, string $tipo, int $idProducto): int
{
    switch ($tipo) {
        case 'empresa':
            return $idProducto;
        case 'vehiculo':
            $stmt = $conn->prepare("SELECT id_empresa FROM vehiculos WHERE id_vehiculo = ? LIMIT 1");
            break;
        case 'evento':
            $stmt = $conn->prepare("SELECT id_empresa FROM eventos WHERE id_evento = ? LIMIT 1");
            break;
        case 'servicio':
        case 'producto':
        default:
            $stmt = $conn->prepare("SELECT id_empresa FROM productos WHERE id_producto = ? LIMIT 1");
            break;
    }
    $stmt->bind_param("i", $idProducto);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return (int)($row["id_empresa"] ?? 0);
}

try {
    $idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
    $tipoEvento = trim($_POST['tipo_evento'] ?? '');
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    $sessionKey = trim($_POST['session_key'] ?? '');
    $metadata = $_POST['metadata'] ?? null;
    $tipo = strtolower(trim($_POST['tipo'] ?? 'producto'));
    $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : (int)($_SESSION['id_usuario'] ?? 0);

    if ($idProducto <= 0 || $tipoEvento === '') {
        echo json_encode(["error" => true, "msg" => "Datos incompletos."]);
        exit;
    }

    $db = new DbConnect();
    $conn = $db->connect();

    $idEmpresa = getEmpresaPorTipo($conn, $tipo, $idProducto);

    // Verificar columnas existentes para insert dinámico
    $cols = ["id_producto", "id_empresa", "tipo_evento", "cantidad", "metadata", "session_key"];
    $vals = [$idProducto, $idEmpresa, $tipoEvento, $cantidad, $metadata, $sessionKey];
    $types = "iisiss";

    $stmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'producto_interacciones'");
    $stmt->execute();
    $res = $stmt->get_result();
    $columns = [];
    while ($row = $res->fetch_assoc()) $columns[] = $row["COLUMN_NAME"];
    $stmt->close();

    if (in_array("id_usuario", $columns, true)) {
        $cols[] = "id_usuario";
        $vals[] = $idUsuario;
        $types .= "i";
    }
    if (in_array("tipo", $columns, true)) {
        $cols[] = "tipo";
        $vals[] = $tipo;
        $types .= "s";
    }
    if (in_array("created_at", $columns, true)) {
        $cols[] = "created_at";
        $vals[] = date("Y-m-d H:i:s");
        $types .= "s";
    }

    $placeholders = implode(",", array_fill(0, count($cols), "?"));
    $sql = "INSERT INTO producto_interacciones (" . implode(",", $cols) . ") VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    $bindParams = [];
    $bindParams[] = $types;
    foreach ($vals as $k => $v) {
        $bindParams[] = &$vals[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();

    echo json_encode(["error" => $ok ? false : true, "msg" => $ok ? "ok" : $err]);
} catch (Throwable $e) {
    echo json_encode(["error" => true, "msg" => "Error al registrar interaccion."]);
}
