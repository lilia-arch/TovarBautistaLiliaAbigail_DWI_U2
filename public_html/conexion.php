<?php
// ============================================
// TENISSTORE - CONEXIÓN A BASE DE DATOS
// Archivo: conexion.php
// ============================================

class Conexion {

    // CONFIGURACIÓN DE HOSTINGER

    private $host = "localhost";

    private $db_name = "u381464771_sistema_login";

    private $username = "u381464771_sistema_login";

    // Coloca aquí tu NUEVA contraseña de MySQL
    private $password = "A2026_Abi#DB";

    private $conn = null;

    // ========================================
    // CONECTAR CON MYSQL
    // ========================================

    public function getConexion() {

        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {

            $dsn = "mysql:host=" .
                $this->host .
                ";dbname=" .
                $this->db_name .
                ";charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE =>
                        PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE =>
                        PDO::FETCH_ASSOC,

                    PDO::ATTR_EMULATE_PREPARES =>
                        false
                ]
            );

            return $this->conn;

        } catch (PDOException $e) {

            // Registrar el error sin mostrar
            // datos privados al visitante
            error_log(
                "Error de conexión MySQL: " .
                $e->getMessage()
            );

            throw new RuntimeException(
                "No se pudo conectar con la base de datos.",
                0,
                $e
            );
        }
    }
}
