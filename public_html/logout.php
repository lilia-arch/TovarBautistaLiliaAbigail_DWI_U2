
<?php
// ============================================
// TENISSTORE - CERRAR SESIÓN
// Archivo: logout.php
// ============================================

// Respuesta JSON
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

// Permitir únicamente POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "mensaje" => "Método no permitido."
    ]);

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
// ELIMINAR DATOS DE LA SESIÓN
// ============================================

$_SESSION = [];

// ============================================
// ELIMINAR COOKIE DE SESIÓN
// ============================================

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        [
            "expires" => time() - 3600,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => $params["samesite"]
        ]
    );
}

// ============================================
// DESTRUIR SESIÓN
// ============================================

session_destroy();

// ============================================
// RESPUESTA EXITOSA
// ============================================

http_response_code(200);

echo json_encode([
    "success" => true,
    "mensaje" => "Sesión cerrada correctamente."
], JSON_UNESCAPED_UNICODE);

?>
