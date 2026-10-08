<?php
class Conexion {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host     = getenv('MYSQLHOST')     ?: 'mysql.railway.internal';
        $this->username = getenv('MYSQLUSER')     ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: 'wwprUUqnuMgQBDzjTlaYnvrLPdIeBkjB';
        $this->db_name  = getenv('MYSQLDATABASE') ?: 'railway'; // En Railway se llama railway
        $this->port     = getenv('MYSQLPORT')     ?: '3306';
    }

    public function getConexion() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode([
                "success" => false,
                "mensaje" => "Error de conexión: " . $exception->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        return $this->conn;
    }
}
?>