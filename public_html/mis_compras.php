
<?php
// ============================================
// TENISSTORE - HISTORIAL DE COMPRAS
// Archivo: mis_compras.php
// ============================================

// RESPUESTA JSON
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
// CONFIGURACIÓN DE SESIÓN
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
// VERIFICAR INICIO DE SESIÓN
// ============================================

if (
    !isset($_SESSION["usuario_id"]) ||
    (int) $_SESSION["usuario_id"] <= 0
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "mensaje" => "Debes iniciar sesión para ver tus compras.",
        "compras" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];

// ============================================
// CONEXIÓN A MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    // ========================================
    // CONSULTAR COMPRAS DEL USUARIO
    // INCLUYE LA COLUMNA FECHA
    // ========================================

    $sql = "
        SELECT
            id,
            producto_id,
            nombre_producto,
            precio,
            cantidad,
            usuario_id,
            total,
            fecha
        FROM compras
        WHERE usuario_id = :usuario_id
        ORDER BY fecha DESC, id DESC
    ";

    $stmt = $db->prepare($sql);

    $stmt->bindValue(
        ":usuario_id",
        $usuario_id,
        PDO::PARAM_INT
    );

    $stmt->execute();

    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ========================================
    // ORGANIZAR LOS DATOS
    // ========================================

    foreach ($compras as &$compra) {

        $compra["id"] =
            (int) $compra["id"];

        $compra["producto_id"] =
            (int) $compra["producto_id"];

        $compra["usuario_id"] =
            (int) $compra["usuario_id"];

        $compra["nombre_producto"] =
            $compra["nombre_producto"] ?? "";

        $compra["precio"] =
            (float) $compra["precio"];

        $compra["cantidad"] =
            (int) $compra["cantidad"];

        $compra["total"] =
            (float) $compra["total"];

        // CONSERVAR LA FECHA DE MYSQL
        $compra["fecha"] =
            $compra["fecha"] ?? "";
    }

    unset($compra);

    // ========================================
    // RESPUESTA EXITOSA
    // ========================================

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "mensaje" => count($compras) > 0
            ? "Compras encontradas correctamente."
            : "Todavía no tienes compras registradas.",
        "compras" => $compras,
        "total_compras" => count($compras)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    // GUARDAR ERROR EN EL SERVIDOR
    error_log(
        "Error en mis_compras.php: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo cargar el historial de compras.",
        "compras" => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
