
<?php
// ============================================
// TENISSTORE - INICIO DE SESIÓN
// Archivo: login.php
// ============================================

// Responder siempre en formato JSON
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "mensaje" => "Método no permitido."
    ]);

    exit;
}

// Configurar seguridad de sesión
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
// RECIBIR DATOS
// ============================================

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (
    !is_array($data) ||
    !isset($data["username"], $data["password"]) ||
    !is_string($data["username"]) ||
    !is_string($data["password"])
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "Datos inválidos."
    ]);

    exit;
}

$username = trim($data["username"]);
$password = $data["password"];

if ($username === "" || $password === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "Ingresa usuario y contraseña."
    ]);

    exit;
}

// ============================================
// CONECTAR CON MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    if (!$db instanceof PDO) {
        throw new Exception(
            "No se pudo establecer la conexión."
        );
    }

    // ========================================
    // BUSCAR USUARIO
    // ========================================

    $sql = "
        SELECT
            id,
            username,
            password,
            rol
        FROM usuarios
        WHERE username = :username
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ":username" => $username
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // ========================================
    // VERIFICAR CONTRASEÑA
    // ========================================

    if (
        !$usuario ||
        !password_verify(
            $password,
            $usuario["password"]
        )
    ) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "mensaje" => "Usuario o contraseña incorrectos."
        ]);

        exit;
    }

    // ========================================
    // CREAR SESIÓN SEGURA
    // ========================================

    session_regenerate_id(true);

    // Mismo identificador para todos los PHP
    $_SESSION["usuario_id"] = $usuario["id"];

    // Compatibilidad temporal con archivos anteriores
    $_SESSION["user_id"] = $usuario["id"];

    $_SESSION["username"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol"];

    // ========================================
    // RESPUESTA EXITOSA
    // ========================================

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "loggedIn" => true,
        "username" => $usuario["username"],
        "rol" => $usuario["rol"],
        "mensaje" => "Inicio de sesión exitoso."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    // Guardar detalles solo en el registro del servidor
    error_log(
        "Error en login.php: " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error interno del servidor. Intenta nuevamente."
    ], JSON_UNESCAPED_UNICODE);
}
