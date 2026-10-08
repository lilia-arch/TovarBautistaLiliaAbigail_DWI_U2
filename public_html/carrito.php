<?php
// ============================================
// TENISSTORE - CARRITO DE COMPRAS
// Archivo: carrito.php
// ============================================

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

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
// INICIALIZAR CARRITO
// ============================================

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

// ============================================
// FUNCIÓN PARA RESPONDER EN JSON
// ============================================

function responder($success, $mensaje, $datos = [], $codigo = 200) {

    http_response_code($codigo);

    echo json_encode(
        array_merge([
            "success" => $success,
            "mensaje" => $mensaje
        ], $datos),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// ============================================
// CONEXIÓN MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    // ========================================
    // RECIBIR PETICIÓN
    // ========================================

    $metodo = $_SERVER["REQUEST_METHOD"];

    if (!in_array($metodo, ["GET", "POST"], true)) {
        responder(false, "Método no permitido.", [], 405);
    }

    $data = [];

    if ($metodo === "POST") {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($data)) {
            responder(false, "Datos inválidos.", [], 400);
        }
    }

    $accion = $metodo === "GET"
        ? "ver"
        : ($data["accion"] ?? "");

    // ========================================
    // AGREGAR PRODUCTO
    // ========================================

    if ($accion === "agregar") {

        $producto_id = filter_var(
            $data["producto_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_var(
            $data["cantidad"] ?? 1,
            FILTER_VALIDATE_INT
        );

        if (!$producto_id || !$cantidad || $cantidad < 1) {
            responder(false, "Producto o cantidad inválidos.", [], 400);
        }

        $stmt = $db->prepare("
            SELECT id, nombre, precio, stock, imagen, talla
            FROM productos
            WHERE id = :id
        ");

        $stmt->execute([
            ":id" => $producto_id
        ]);

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            responder(false, "El producto no existe.", [], 404);
        }

        $actual = $_SESSION["carrito"][$producto_id] ?? 0;

        $nuevaCantidad = $actual + $cantidad;

        if ($nuevaCantidad > (int)$producto["stock"]) {
            responder(
                false,
                "No hay suficientes existencias.",
                [],
                409
            );
        }

        $_SESSION["carrito"][$producto_id] = $nuevaCantidad;

        responder(true, "Producto agregado al carrito.");
    }

    // ========================================
    // ACTUALIZAR CANTIDAD
    // ========================================

    if ($accion === "actualizar") {

        $producto_id = filter_var(
            $data["producto_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_var(
            $data["cantidad"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$producto_id || $cantidad === false || $cantidad === null || $cantidad < 0) {
            responder(false, "Datos inválidos.", [], 400);
        }

        if ($cantidad === 0) {

            unset($_SESSION["carrito"][$producto_id]);

            responder(true, "Producto eliminado del carrito.");
        }

        $stmt = $db->prepare("
            SELECT stock
            FROM productos
            WHERE id = :id
        ");

        $stmt->execute([
            ":id" => $producto_id
        ]);

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            responder(false, "El producto no existe.", [], 404);
        }

        if ($cantidad > (int)$producto["stock"]) {
            responder(false, "Cantidad superior al stock disponible.", [], 409);
        }

        $_SESSION["carrito"][$producto_id] = $cantidad;

        responder(true, "Cantidad actualizada.");
    }

    // ========================================
    // ELIMINAR PRODUCTO
    // ========================================

    if ($accion === "eliminar") {

        $producto_id = filter_var(
            $data["producto_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$producto_id || $producto_id < 1) {
            responder(false, "Producto inválido.", [], 400);
        }

        unset($_SESSION["carrito"][$producto_id]);

        responder(true, "Producto eliminado del carrito.");
    }

    // ========================================
    // VACIAR CARRITO
    // ========================================

    if ($accion === "vaciar") {

        $_SESSION["carrito"] = [];

        responder(true, "Carrito vaciado correctamente.");
    }

    // ========================================
    // CONSULTAR CARRITO
    // ========================================

    if ($accion === "ver") {

        $productos = [];
        $total = 0;
        $totalArticulos = 0;

        $stmt = $db->prepare("
            SELECT id, nombre, precio, stock, imagen, talla
            FROM productos
            WHERE id = :id
        ");

        foreach ($_SESSION["carrito"] as $id => $cantidad) {

            $stmt->execute([
                ":id" => (int)$id
            ]);

            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                unset($_SESSION["carrito"][$id]);
                continue;
            }

            $cantidad = (int)$cantidad;

            if ($cantidad < 1) {
                unset($_SESSION["carrito"][$id]);
                continue;
            }

            $precio = (float)$producto["precio"];
            $subtotal = round($precio * $cantidad, 2);

            $productos[] = [
                "id" => (int)$producto["id"],
                "nombre" => $producto["nombre"],
                "precio" => $precio,
                "cantidad" => $cantidad,
                "stock" => (int)$producto["stock"],
                "imagen" => $producto["imagen"],
                "talla" => $producto["talla"],
                "subtotal" => $subtotal
            ];

            $total += $subtotal;
            $totalArticulos += $cantidad;
        }

        responder(true, "Carrito cargado correctamente.", [
            "productos" => $productos,
            "total" => round($total, 2),
            "total_articulos" => $totalArticulos
        ]);
    }

    // ========================================
    // ACCIÓN DESCONOCIDA
    // ========================================

    responder(false, "Acción no válida.", [], 400);

} catch (Throwable $e) {

    error_log(
        "Error en carrito.php: " . $e->getMessage()
    );

    responder(
        false,
        "Ocurrió un error al procesar el carrito.",
        [],
        500
    );
}
?>