<?php

session_start();

// Encabezado para garantizar respuesta JSON limpia
header("Content-Type: application/json; charset=UTF-8");

// Compatibilidad de archivo para Linux/Railway (Conexion.php vs conexion.php)
if (file_exists("Conexion.php")) {
    require_once "Conexion.php";
} else {
    require_once "conexion.php";
}

try {

    // Verificar sesión activa del usuario
    if (!isset($_SESSION["usuario_id"])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "mensaje" => "Debes iniciar sesión para ver tus compras."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $usuario_id = $_SESSION["usuario_id"];

    $database = new Conexion();
    $db = $database->getConexion();

    // Consultar el historial de compras del usuario
    $sql = "
        SELECT
            id,
            nombre_producto,
            precio,
            cantidad,
            total,
            fecha
        FROM compras
        WHERE usuario_id = ?
        ORDER BY fecha DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario_id]);

    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "compras" => $compras
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error al consultar compras: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>