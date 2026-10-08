<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "Conexion.php";

try {

    // Recibir datos
    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $username = trim($data["username"] ?? "");
    $password = trim($data["password"] ?? "");

    // Validar campos
    if ($username === "" || $password === "") {

        echo json_encode([
            "success" => false,
            "mensaje" => "Debes llenar todos los campos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Conexión
    $database = new Conexion();
    $db = $database->getConexion();

    // Buscar usuario
    $sql = "
        SELECT
            id,
            username,
            password,
            rol
        FROM usuarios
        WHERE username = ?
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        $username
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario no encontrado
    if (!$usuario) {

        echo json_encode([
            "success" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Verificar contraseña
    if (!password_verify($password, $usuario["password"])) {

        echo json_encode([
            "success" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // ==========================================
    // CREAR SESIÓN
    // ==========================================

    $_SESSION["usuario_id"] = $usuario["id"];

    $_SESSION["username"] = $usuario["username"];

    $_SESSION["rol"] = $usuario["rol"];


    // ==========================================
    // RESPUESTA
    // ==========================================

    echo json_encode([
        "success" => true,
        "mensaje" => "Inicio de sesión correcto.",
        "usuario_id" => $usuario["id"],
        "username" => $usuario["username"],
        "rol" => $usuario["rol"]
    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>