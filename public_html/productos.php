
<?php
// ============================================
// TENISSTORE - CATÁLOGO DE PRODUCTOS
// Archivo: productos.php
// ============================================

// RESPUESTA EN FORMATO JSON
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

// ============================================
// PERMITIR SOLO GET
// ============================================

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "mensaje" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ============================================
// CONEXIÓN A MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    // ========================================
    // CONSULTAR PRODUCTOS
    // ========================================

    $sql = "
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

    $stmt = $db->prepare($sql);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ========================================
    // ORGANIZAR DATOS
    // ========================================

    foreach ($productos as &$producto) {

        $producto["id"] = (int) $producto["id"];

        $producto["precio"] = (float) $producto["precio"];

        $producto["stock"] = (int) $producto["stock"];

        $producto["nombre"] =
            $producto["nombre"] ?? "";

        $producto["descripcion"] =
            $producto["descripcion"] ?? "";

        $producto["imagen"] =
            $producto["imagen"] ?? "";

        $producto["talla"] =
            $producto["talla"] ?? "";
    }

    unset($producto);

    // ========================================
    // RESPUESTA EXITOSA
    // ========================================

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "productos" => $productos,
        "total_productos" => count($productos)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    // Guardar error en el servidor
    error_log(
        "Error en productos.php: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo cargar el catálogo de productos."
    ], JSON_UNESCAPED_UNICODE);
}
?>
