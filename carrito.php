<?php

session_start();

// Encabezado para garantizar que la respuesta sea siempre JSON
header("Content-Type: application/json; charset=UTF-8");

// Compatibilidad de nombres para Linux (Railway)
if (file_exists("Conexion.php")) {
    require_once "Conexion.php";
} else {
    require_once "conexion.php";
}

try {
    $database = new Conexion();
    $db = $database->getConexion();

    // Crear carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Recibir datos en JSON
    $input = file_get_contents("php://input");
    $data = json_decode($input);

    $accion = $data->accion ?? $_GET['accion'] ?? null;

    // 1. AGREGAR PRODUCTO
    if ($accion === "agregar") {
        if (empty($data->producto_id) || empty($data->cantidad)) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Faltan datos obligatorios."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $producto_id = $data->producto_id;
        $cantidad = (int)$data->cantidad;

        // Buscar producto
        $query = "SELECT * FROM productos WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $producto_id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            echo json_encode([
                "success" => false,
                "mensaje" => "El producto no existe."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar stock disponible
        if ($cantidad > $producto['stock']) {
            echo json_encode([
                "success" => false,
                "mensaje" => "No hay suficiente stock disponible."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Incrementar o agregar al carrito en sesión
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = $cantidad;
        }

        echo json_encode([
            "success" => true,
            "mensaje" => "Producto agregado al carrito."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. VER CARRITO
    if ($accion === "ver") {
        $carrito = [];
        $total = 0;

        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            $query = "SELECT * FROM productos WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $producto_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                $subtotal = $producto['precio'] * $cantidad;
                $total += $subtotal;

                $carrito[] = [
                    "id"       => $producto['id'],
                    "nombre"   => $producto['nombre'],
                    "precio"   => $producto['precio'],
                    "cantidad" => $cantidad,
                    "subtotal" => $subtotal
                ];
            }
        }

        echo json_encode([
            "success"   => true,
            "productos" => $carrito,
            "total"     => $total
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. ELIMINAR PRODUCTO
    if ($accion === "eliminar") {
        if (!empty($data->producto_id) && isset($_SESSION['carrito'][$data->producto_id])) {
            unset($_SESSION['carrito'][$data->producto_id]);
            echo json_encode([
                "success" => true,
                "mensaje" => "Producto eliminado del carrito."
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "No se encontró el producto en el carrito."
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 4. VACIAR CARRITO
    if ($accion === "vaciar") {
        $_SESSION['carrito'] = [];
        echo json_encode([
            "success" => true,
            "mensaje" => "Carrito vaciado."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Acción no válida o no especificada
    echo json_encode([
        "success" => false,
        "mensaje" => "Acción no válida o no especificada."
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error interno en el servidor: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>