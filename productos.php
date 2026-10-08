<?php
header("Content-Type: application/json; charset=UTF-8");

if (file_exists("Conexion.php")) {
    require_once "Conexion.php";
} else {
    require_once "conexion.php";
}

try {
    $database = new Conexion();
    $db = $database->getConexion();

    $query = "
        SELECT
            id,
            nombre,
            descripcion,
            precio,
            stock,
            talla,
            imagen
        FROM productos
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "productos" => $productos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error al consultar productos: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>