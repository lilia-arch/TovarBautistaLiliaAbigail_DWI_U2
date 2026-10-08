
<?php
// ============================================
// TENISSTORE - REGISTRO DE USUARIOS
// Archivo: registro.php
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
    ]);

    exit;
}

// ============================================
// RECIBIR DATOS DEL FORMULARIO
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
        "mensaje" => "Ingresa usuario y contraseña."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$username = trim($data["username"]);
$password = $data["password"];

// ============================================
// VALIDAR USUARIO
// ============================================

if (
    !preg_match(
        '/^[a-zA-Z0-9_]{3,30}$/',
        $username
    )
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "El usuario debe tener entre 3 y 30 caracteres. Utiliza letras, números o guion bajo."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ============================================
// VALIDAR CONTRASEÑA
// ============================================

if (strlen($password) < 8) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "La contraseña debe tener al menos 8 caracteres."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if (strlen($password) > 72) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "mensaje" => "La contraseña no puede superar los 72 bytes."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Todos los registros públicos son usuarios normales.
// Nunca aceptar el rol desde JavaScript.
$rol = "usuario";

// ============================================
// CONEXIÓN MYSQL
// ============================================

try {

    require_once __DIR__ . "/conexion.php";

    $database = new Conexion();
    $db = $database->getConexion();

    // ========================================
    // VERIFICAR SI EXISTE EL USUARIO
    // ========================================

    $sql = "
        SELECT id
        FROM usuarios
        WHERE username = :username
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ":username" => $username
    ]);

    if ($stmt->fetch()) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "mensaje" => "Este nombre de usuario ya está registrado."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // ========================================
    // CIFRAR CONTRASEÑA
    // ========================================

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // ========================================
    // GUARDAR USUARIO
    // ========================================

    $sql = "
        INSERT INTO usuarios
        (
            username,
            password,
            rol
        )
        VALUES
        (
            :username,
            :password,
            :rol
        )
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        ":username" => $username,
        ":password" => $passwordHash,
        ":rol" => $rol
    ]);

    // ========================================
    // REGISTRO EXITOSO
    // ========================================

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "mensaje" => "Registro exitoso. Ya puedes iniciar sesión."
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    error_log(
        "Error de registro: " . $e->getMessage()
    );

    // Código MySQL 1062: valor duplicado
    if (
        isset($e->errorInfo[1]) &&
        (int)$e->errorInfo[1] === 1062
    ) {

        http_response_code(409);

        echo json_encode([
            "success" => false,
            "mensaje" => "Este nombre de usuario ya está registrado."
        ], JSON_UNESCAPED_UNICODE);

    } else {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "mensaje" => "Error interno al registrar el usuario."
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Throwable $e) {

    error_log(
        "Error del servidor: " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "No se pudo completar el registro."
    ], JSON_UNESCAPED_UNICODE);
}
