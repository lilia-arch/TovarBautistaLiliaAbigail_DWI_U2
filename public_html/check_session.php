
<?php
// ============================================
// TENISSTORE - VERIFICAR SESIÓN
// Archivo: check_session.php
// ============================================

// Respuesta en formato JSON
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

// Solo permitir GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "mensaje" => "Método no permitido."
    ]);

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
// VERIFICAR SI EXISTE UNA SESIÓN
// ============================================

if (
    !isset($_SESSION["usuario_id"]) ||
    !isset($_SESSION["username"]) ||
    !isset($_SESSION["rol"])
) {

    echo json_encode([
        "success" => true,
        "loggedIn" => false,
        "mensaje" => "No hay una sesión activa."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ============================================
// VALIDAR USUARIO EN MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    $sql = "
        SELECT id, username, rol
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ":id" => $_SESSION["usuario_id"]
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario ya no existe
    if (!$usuario) {

        $_SESSION = [];

        echo json_encode([
            "success" => true,
            "loggedIn" => false,
            "mensaje" => "La cuenta ya no está disponible."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // ========================================
    // ACTUALIZAR DATOS DE LA SESIÓN
    // ========================================

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["user_id"] = $usuario["id"];
    $_SESSION["username"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol"];

    // ========================================
    // RESPUESTA EXITOSA
    // ========================================

    echo json_encode([
        "success" => true,
        "loggedIn" => true,
        "username" => $usuario["username"],
        "rol" => $usuario["rol"],
        "mensaje" => "Sesión activa."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    error_log(
        "Error en check_session.php: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "loggedIn" => false,
        "mensaje" => "No se pudo verificar la sesión."
    ], JSON_UNESCAPED_UNICODE);
}
