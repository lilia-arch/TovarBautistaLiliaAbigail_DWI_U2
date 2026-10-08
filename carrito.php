<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once 'Conexion.php';

$database = new Conexion();
$db = $database->getConexion();


// Crear carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}


// Recibir datos
$data = json_decode(file_get_contents("php://input"));


// AGREGAR PRODUCTO
if (isset($data->accion) && $data->accion == "agregar") {

    if (empty($data->producto_id) || empty($data->cantidad)) {

        echo json_encode([
            "success" => false,
            "mensaje" => "Faltan datos."
        ]);

        exit;
    }


    $producto_id = $data->producto_id;
    $cantidad = $data->cantidad;


    // Buscar producto
    $query = "SELECT * FROM productos WHERE id = :id";

    $stmt = $db->prepare($query);

    $stmt->bindParam(":id", $producto_id);

    $stmt->execute();


    if ($stmt->rowCount() == 0) {

        echo json_encode([
            "success" => false,
            "mensaje" => "El producto no existe."
        ]);

        exit;
    }


    $producto = $stmt->fetch(PDO::FETCH_ASSOC);


    // Verificar stock
    if ($cantidad > $producto['stock']) {

        echo json_encode([
            "success" => false,
            "mensaje" => "No hay suficiente stock."
        ]);

        exit;
    }


    // Agregar al carrito
    if (isset($_SESSION['carrito'][$producto_id])) {

        $_SESSION['carrito'][$producto_id] += $cantidad;

    } else {

        $_SESSION['carrito'][$producto_id] = $cantidad;
    }


    echo json_encode([
        "success" => true,
        "mensaje" => "Producto agregado al carrito."
    ]);

    exit;
}


// VER CARRITO
if (isset($data->accion) && $data->accion == "ver") {

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

                "id" => $producto['id'],

                "nombre" => $producto['nombre'],

                "precio" => $producto['precio'],

                "cantidad" => $cantidad,

                "subtotal" => $subtotal
            ];
        }
    }


    echo json_encode([

        "success" => true,

        "productos" => $carrito,

        "total" => $total
    ]);

    exit;
}


// ELIMINAR PRODUCTO
if (isset($data->accion) && $data->accion == "eliminar") {

    if (isset($data->producto_id)) {

        unset($_SESSION['carrito'][$data->producto_id]);

        echo json_encode([

            "success" => true,

            "mensaje" => "Producto eliminado del carrito."
        ]);

    } else {

        echo json_encode([

            "success" => false,

            "mensaje" => "Falta el producto."
        ]);
    }

    exit;
}


// VACIAR CARRITO
if (isset($data->accion) && $data->accion == "vaciar") {

    $_SESSION['carrito'] = [];

    echo json_encode([

        "success" => true,

        "mensaje" => "Carrito vacío."
    ]);

    exit;
}


echo json_encode([

    "success" => false,

    "mensaje" => "Acción no válida."
]);

?>