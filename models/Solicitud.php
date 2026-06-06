<?php

require_once __DIR__ . '/../config/database.php';

class Solicitud
{

    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Devuelve todas las solicitudes recibidas en los anuncios de un usuario
    public function obtenerRecibidas($publicador_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.*, a.nombre as animal_nombre, a.especie, a.imagen,
                    u.nombre as solicitante_nombre
             FROM solicitudes_adopcion s
             JOIN animales a ON s.animal_id = a.id
             JOIN usuarios u ON s.solicitante_id = u.id
             WHERE s.publicador_id = :publicador_id
             ORDER BY s.fecha_solicitud DESC"
        );
        $stmt->execute([':publicador_id' => $publicador_id]);
        return $stmt->fetchAll();
    }

    // Devuelve todas las solicitudes enviadas por un usuario
    public function obtenerEnviadas($solicitante_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.*, a.nombre as animal_nombre, a.especie, a.imagen,
                    u.nombre as publicador_nombre
             FROM solicitudes_adopcion s
             JOIN animales a ON s.animal_id = a.id
             JOIN usuarios u ON s.publicador_id = u.id
             WHERE s.solicitante_id = :solicitante_id
             ORDER BY s.fecha_solicitud DESC"
        );
        $stmt->execute([':solicitante_id' => $solicitante_id]);
        return $stmt->fetchAll();
    }

    // Busca una solicitud por su id
    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM solicitudes_adopcion WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Comprueba si ya existe una solicitud activa del mismo usuario para el mismo animal
    public function yaExiste($solicitante_id, $animal_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM solicitudes_adopcion
             WHERE solicitante_id = :solicitante_id
             AND animal_id = :animal_id
             AND estado = 'PENDIENTE'
             LIMIT 1"
        );
        $stmt->execute([
            ':solicitante_id' => $solicitante_id,
            ':animal_id' => $animal_id
        ]);
        return $stmt->fetch() !== false;
    }

    // Crea una nueva solicitud de adopcion
    public function crear($animal_id, $solicitante_id, $publicador_id, $mensaje)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO solicitudes_adopcion (animal_id, solicitante_id, publicador_id, mensaje)
             VALUES (:animal_id, :solicitante_id, :publicador_id, :mensaje)"
        );
        $stmt->execute([
            ':animal_id' => $animal_id,
            ':solicitante_id' => $solicitante_id,
            ':publicador_id' => $publicador_id,
            ':mensaje' => $mensaje ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    // Cambia el estado de una solicitud (ACEPTADA o RECHAZADA)
    public function cambiarEstado($id, $estado)
    {
        $stmt = $this->conn->prepare(
            "UPDATE solicitudes_adopcion SET estado = :estado WHERE id = :id"
        );
        $stmt->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }

    // Elimina una solicitud (solo si esta pendiente)
    public function eliminar($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM solicitudes_adopcion WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    public function obtenerTodas()
    {
        $stmt = $this->conn->prepare(
            "SELECT s.*, a.nombre as animal_nombre, a.especie,
                u1.nombre as solicitante_nombre,
                u2.nombre as publicador_nombre
         FROM solicitudes_adopcion s
         JOIN animales a  ON s.animal_id      = a.id
         JOIN usuarios u1 ON s.solicitante_id = u1.id
         JOIN usuarios u2 ON s.publicador_id  = u2.id
         ORDER BY s.fecha_solicitud DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
