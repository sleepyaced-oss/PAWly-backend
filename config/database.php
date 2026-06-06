<?php

class Database {

    private $host     = 'localhost';
    private $dbname   = 'pawly_db';
    private $username = 'pawly';
    private $password = 'pawly123';
    private $charset  = 'utf8mb4';

    private $conn = null;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error de conexión con la base de datos',
                'detalle' => $e->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}
