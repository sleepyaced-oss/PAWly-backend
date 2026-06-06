<?php

require_once __DIR__ . '/../config/database.php';

class Favorito {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Devuelve todos los favoritos de un usuario con datos del animal
    public function obtenerPorUsuario($usuario_id) {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre as publicador_nombre, u.foto_perfil as publicador_foto
             FROM favoritos f
             JOIN animales a ON f.animal_id = a.id
             JOIN usuarios u ON a.publicador_id = u.id
             WHERE f.usuario_id = :usuario_id
             AND a.estado = 'DISPONIBLE'
             ORDER BY f.fecha_guardado DESC"
        );
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetchAll();
    }

    // Comprueba si un animal ya es favorito de un usuario
    public function existe($usuario_id, $animal_id) {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM favoritos
             WHERE usuario_id = :usuario_id AND animal_id = :animal_id
             LIMIT 1"
        );
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':animal_id'  => $animal_id
        ]);
        return $stmt->fetch() !== false;
    }

    // Añade un animal a favoritos
    public function añadir($usuario_id, $animal_id) {
        $stmt = $this->conn->prepare(
            "INSERT INTO favoritos (usuario_id, animal_id)
             VALUES (:usuario_id, :animal_id)"
        );
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':animal_id'  => $animal_id
        ]);
    }

    // Elimina un animal de favoritos
    public function eliminar($usuario_id, $animal_id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM favoritos
             WHERE usuario_id = :usuario_id AND animal_id = :animal_id"
        );
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':animal_id'  => $animal_id
        ]);
    }
}
