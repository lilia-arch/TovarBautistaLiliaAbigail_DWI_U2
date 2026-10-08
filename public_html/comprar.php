
<?php
// ============================================
// TENISSTORE - REALIZAR COMPRAS
// Archivo: comprar.php
// ============================================

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

// ============================================
// PERMITIR SOLO POST
// ============================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "mensaje" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ============================================
// CONFIGURAR SESIÓN
// ============================================

ini_set("session.use_strict_mode", "1");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

// ============================================
// VERIFICAR USUARIO
// ============================================

if (!isset($_SESSION["usuario_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "mensaje" => "Debes iniciar sesión para comprar."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

// ============================================
// RECIBIR DATOS
// ============================================

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (
    !is_array($data) ||
    !isset($data["producto_id"]) ||
    filter_var(
        $data["producto_id"],
        FILTER_VALIDATE_INT
    ) === false
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "Producto inválido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$producto_id = (int) $data["producto_id"];

if ($producto_id <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "El ID del producto no es válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Cada compra directa es de una unidad
$cantidad = 1;

$db = null;

try {

    // ========================================
    // CONEXIÓN MYSQL
    // ========================================

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    // ========================================
    // INICIAR TRANSACCIÓN
    // ========================================

    $db->beginTransaction();

    // ========================================
    // CONSULTAR PRODUCTO Y BLOQUEAR FILA
    // ========================================

    $sql = "
        SELECT
            id,
            nombre,
            precio,
            stock
        FROM productos
        WHERE id = :id
        FOR UPDATE
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ":id" => $producto_id
    ]);

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new RuntimeException(
            "El producto no existe.",
            404
        );
    }

    // ========================================
    // VERIFICAR EXISTENCIAS
    // ========================================

    if ((int)$producto["stock"] < $cantidad) {

        throw new RuntimeException(
            "No hay suficientes productos disponibles.",
            409
        );
    }

    // ========================================
    // CALCULAR TOTAL
    // ========================================

    $precio = (float) $producto["precio"];

    if ($precio < 0) {
        throw new RuntimeException(
            "El precio del producto no es válido.",
            400
        );
    }

    $total = round($precio * $cantidad, 2);

    // ========================================
    // DESCONTAR INVENTARIO
    // ========================================

    $sqlUpdate = "
        UPDATE productos
        SET stock = stock - :cantidad
        WHERE id = :id
        AND stock >= :cantidad_minima
    ";

    $stmtUpdate = $db->prepare($sqlUpdate);

    $stmtUpdate->execute([
        ":cantidad" => $cantidad,
        ":id" => $producto_id,
        ":cantidad_minima" => $cantidad
    ]);

    if ($stmtUpdate->rowCount() !== 1) {

        throw new RuntimeException(
            "No hay stock disponible.",
            409
        );
    }

    // ========================================
    // GUARDAR COMPRA
    // ========================================

    $sqlCompra = "
        INSERT INTO compras
        (
            producto_id,
            usuario_id,
            nombre_producto,
            precio,
            cantidad,
            total
        )
        VALUES
        (
            :producto_id,
            :usuario_id,
            :nombre_producto,
            :precio,
            :cantidad,
            :total
        )
    ";

    $stmtCompra = $db->prepare($sqlCompra);

    $stmtCompra->execute([
        ":producto_id" => $producto_id,
        ":usuario_id" => $usuario_id,
        ":nombre_producto" => $producto["nombre"],
        ":precio" => $precio,
        ":cantidad" => $cantidad,
        ":total" => $total
    ]);

    $compra_id = $db->lastInsertId();

    // ========================================
    // CONFIRMAR COMPRA
    // ========================================

    $db->commit();

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "mensaje" => "Compra registrada correctamente.",
        "compra_id" => $compra_id,
        "producto" => $producto["nombre"],
        "precio" => $precio,
        "cantidad" => $cantidad,
        "total" => $total
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    // ========================================
    // CANCELAR CAMBIOS SI HAY ERROR
    // ========================================

    if (
        $db instanceof PDO &&
        $db->inTransaction()
    ) {
        $db->rollBack();
    }

    error_log(
        "Error en comprar.php: " .
        $e->getMessage()
    );

    $codigo = (int) $e->getCode();

    if (!in_array($codigo, [400, 404, 409], true)) {
        $codigo = 500;
    }

    http_response_code($codigo);

    echo json_encode([
        "success" => false,
        "mensaje" => $codigo === 500
            ? "No se pudo registrar la compra."
            : $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
