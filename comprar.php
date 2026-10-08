<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "Conexion.php";

try {

    // ==========================================
    // VERIFICAR SESIÓN
    // ==========================================

    if (!isset($_SESSION["usuario_id"])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "mensaje" => "Debes iniciar sesión para realizar una compra."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // ID del usuario que inició sesión
    $usuario_id = $_SESSION["usuario_id"];


    // ==========================================
    // RECIBIR DATOS DE JAVASCRIPT
    // ==========================================

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $producto_id = intval(
        $data["producto_id"] ?? 0
    );


    // ==========================================
    // VALIDAR PRODUCTO
    // ==========================================

    if ($producto_id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "mensaje" => "Producto inválido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==========================================
    // CONECTAR A LA BASE DE DATOS
    // ==========================================

    $database = new Conexion();

    $db = $database->getConexion();


    // ==========================================
    // BUSCAR PRODUCTO
    // ==========================================

    $sql = "
        SELECT
            id,
            nombre,
            precio,
            stock
        FROM productos
        WHERE id = ?
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        $producto_id
    ]);

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);


    // ==========================================
    // VERIFICAR QUE EXISTA
    // ==========================================

    if (!$producto) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "mensaje" => "El producto no existe."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==========================================
    // VERIFICAR STOCK
    // ==========================================

    if ($producto["stock"] <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "mensaje" => "El producto está agotado."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // ==========================================
    // DATOS DE LA COMPRA
    // ==========================================

    $cantidad = 1;

    $total = $producto["precio"] * $cantidad;


    // ==========================================
    // INICIAR TRANSACCIÓN
    // ==========================================

    $db->beginTransaction();


    // ==========================================
    // DESCONTAR STOCK
    // ==========================================

    $sqlUpdate = "
        UPDATE productos
        SET stock = stock - 1
        WHERE id = ?
        AND stock > 0
    ";

    $stmtUpdate = $db->prepare($sqlUpdate);

    $stmtUpdate->execute([
        $producto_id
    ]);


    // Verificar que realmente se haya descontado
    if ($stmtUpdate->rowCount() == 0) {

        throw new Exception(
            "No hay stock disponible."
        );
    }


    // ==========================================
    // GUARDAR LA COMPRA
    // ==========================================

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
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmtCompra = $db->prepare($sqlCompra);

    $stmtCompra->execute([
        $producto["id"],
        $usuario_id,
        $producto["nombre"],
        $producto["precio"],
        $cantidad,
        $total
    ]);


    // ==========================================
    // CONFIRMAR TRANSACCIÓN
    // ==========================================

    $db->commit();


    // ==========================================
    // RESPUESTA EXITOSA
    // ==========================================

    echo json_encode([
        "success" => true,
        "mensaje" => "Compra realizada correctamente.",
        "producto" => $producto["nombre"],
        "precio" => $producto["precio"],
        "cantidad" => $cantidad,
        "total" => $total
    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {


    // ==========================================
    // CANCELAR TRANSACCIÓN SI HUBO ERROR
    // ==========================================

    if (
        isset($db) &&
        $db->inTransaction()
    ) {
        $db->rollBack();
    }


    // ==========================================
    // RESPUESTA DE ERROR
    // ==========================================

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>