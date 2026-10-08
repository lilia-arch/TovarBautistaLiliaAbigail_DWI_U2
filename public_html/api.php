<?php
// api.php
header("Content-Type: application/json; charset=UTF-8");
require_once 'conexion.php';

$baseDeDatos = new Conexion();
$db = $baseDeDatos->getConexion();

try {
    // Consulta simple para traer al jugador
    $query = "SELECT * FROM jugadores LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["mensaje" => "No se encontraron datos."], JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "mensaje" => "Error en la consulta: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>