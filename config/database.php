<?php

class Database {

    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    private $conn = null;

    public function __construct() {
        $this->host     = getenv('DB_HOST')     ?: 'localhost';
        $this->dbname   = getenv('DB_NAME')     ?: 'pawly_db';
        $this->username = getenv('DB_USERNAME') ?: 'pawly';
        $this->password = getenv('DB_PASSWORD') ?: 'pawly123';
    }

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $port = getenv('DB_PORT') ?: '3306';
            $dsn = "mysql:host={$this->host};port={$port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
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