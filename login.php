<?php
// login.php
session_start(); // REQUISITO: Manejo de multisesiones
header("Content-Type: application/json; charset=UTF-8");
require_once 'Conexion.php';

$database = new Conexion();
$db = $database->getConexion();
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $query = "SELECT id, username, password, rol FROM usuarios WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $data->username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($data->password, $row['password'])) {
            // Guardamos los datos en la sesión del servidor
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['rol'] = $row['rol']; // REQUISITO: Autenticación por tipo de usuario

            echo json_encode([
                "success" => true, 
                "username" => $row['username'], 
                "rol" => $row['rol'],
                "mensaje" => "Login exitoso."
            ]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Contraseña incorrecta."]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "El usuario no existe."]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos."]);
}
?>