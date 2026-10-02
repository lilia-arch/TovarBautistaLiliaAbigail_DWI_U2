<?php
// recuperar.php
header("Content-Type: application/json; charset=UTF-8");
require_once 'Conexion.php';

$database = new Conexion();
$db = $database->getConexion();
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->new_password)) {
    // Verificamos que el usuario exista
    $checkQuery = "SELECT id FROM usuarios WHERE username = :username";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":username", $data->username);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        // Encriptamos la nueva contraseña
        $new_pass_hash = password_hash($data->new_password, PASSWORD_BCRYPT);
        
        // REQUISITO: Recuperación de contraseñas
        $updateQuery = "UPDATE usuarios SET password = :password WHERE username = :username";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":password", $new_pass_hash);
        $updateStmt->bindParam(":username", $data->username);
        
        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "mensaje" => "Contraseña recuperada y actualizada."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar la contraseña."]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Usuario no encontrado."]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos."]);
}
?>