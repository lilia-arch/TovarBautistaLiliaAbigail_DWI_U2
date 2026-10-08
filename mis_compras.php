<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "Conexion.php";

try {

    // Verificar sesión
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

    $stmt->execute([
        $usuario_id
    ]);

    $compras =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "compras" => $compras
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" =>
            "Error al consultar compras: " .
            $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>