<?php
// api.php
header("Content-Type: application/json; charset=UTF-8");
require_once 'Conexion.php';

$baseDeDatos = new Conexion();
$db = $baseDeDatos->getConexion();

// Consulta simple para traer al jugador
$query = "SELECT * FROM jugadores LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($row) {
    echo json_encode($row);
} else {
    echo json_encode(["mensaje" => "No se encontraron datos."]);
}
?>