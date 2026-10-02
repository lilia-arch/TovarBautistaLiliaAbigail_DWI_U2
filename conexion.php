<?php
class Conexion {
    private $host = "localhost";
    private $db_name = "gamestore_db";
    private $username = "root"; // Usuario por defecto en XAMPP
    private $password = "";     // Contraseña por defecto (vacía)
    public $conn;

    public function getConexion() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(["success" => false, "mensaje" => "Error de conexión: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>