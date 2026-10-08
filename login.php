<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

// Compatibilidad de nombre de archivo para servidores Linux (Railway)
if (file_exists("Conexion.php")) {
    require_once "Conexion.php";
} else {
    require_once "conexion.php";
}

try {

    // Recibir datos en formato JSON
    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    // Soporte para variables enviadas como 'username' / 'usuario' y 'password' / 'clave'
    $username = trim($data["username"] ?? $data["usuario"] ?? "");
    $password = trim($data["password"] ?? $data["clave"] ?? "");

    // Validar campos vacíos
    if ($username === "" || $password === "") {

        echo json_encode([
            "success" => false,
            "mensaje" => "Debes llenar todos los campos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Conexión a la base de datos
    $database = new Conexion();
    $db = $database->getConexion();

    // Buscar usuario en la tabla 'usuarios'
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
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario no encontrado
    if (!$usuario) {

        echo json_encode([
            "success" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Verificar contraseña (Soporta hash con password_verify y texto plano como respaldo)
    $esValida = password_verify($password, $usuario["password"]) || ($password === $usuario["password"]);

    if (!$esValida) {

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
    $_SESSION["username"]   = $usuario["username"];
    $_SESSION["rol"]        = $usuario["rol"];

    // ==========================================
    // RESPUESTA EXITOSA
    // ==========================================

    echo json_encode([
        "success"    => true,
        "mensaje"    => "Inicio de sesión correcto.",
        "usuario_id" => $usuario["id"],
        "username"   => $usuario["username"],
        "rol"        => $usuario["rol"]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión en el servidor: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>