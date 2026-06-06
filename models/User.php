<?php

require_once __DIR__ . '/../config/database.php';

class User {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre, email, telefono, provincia, localidad, biografia, foto_perfil, rol, activo, fecha_registro
             FROM usuarios WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function existeEmail($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    public function crear($nombre, $email, $password, $telefono, $provincia, $localidad, $rol = 'USER') {
        $stmt = $this->conn->prepare(
            "INSERT INTO usuarios (nombre, email, password, telefono, provincia, localidad, rol)
             VALUES (:nombre, :email, :password, :telefono, :provincia, :localidad, :rol)"
        );
        $stmt->execute([
            ':nombre'    => $nombre,
            ':email'     => $email,
            ':password'  => $password,
            ':telefono'  => $telefono,
            ':provincia' => $provincia,
            ':localidad' => $localidad,
            ':rol'       => $rol,
        ]);
        return $this->conn->lastInsertId();
    }

    public function actualizar($id, $nombre, $telefono, $provincia, $localidad, $biografia) {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios
             SET nombre = :nombre, telefono = :telefono, provincia = :provincia,
                 localidad = :localidad, biografia = :biografia
             WHERE id = :id"
        );
        $stmt->execute([
            ':id'        => $id,
            ':nombre'    => $nombre,
            ':telefono'  => $telefono,
            ':provincia' => $provincia,
            ':localidad' => $localidad,
            ':biografia' => $biografia,
        ]);
    }

    public function actualizarFoto($id, $ruta) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
        $stmt->execute([':foto' => $ruta, ':id' => $id]);
    }

    public function obtenerTodos() {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre, email, telefono, provincia, localidad, rol, activo, fecha_registro
             FROM usuarios ORDER BY fecha_registro DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function cambiarEstado($id, $activo) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET activo = :activo WHERE id = :id");
        $stmt->execute([':id' => $id, ':activo' => $activo]);
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function actualizarAdmin($id, $datos) {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios
             SET nombre = :nombre, email = :email, telefono = :telefono,
                 provincia = :provincia, localidad = :localidad, rol = :rol
             WHERE id = :id"
        );
        $stmt->execute([
            ':id'        => $id,
            ':nombre'    => $datos['nombre'],
            ':email'     => $datos['email']     ?? null,
            ':telefono'  => $datos['telefono']  ?? null,
            ':provincia' => $datos['provincia'] ?? null,
            ':localidad' => $datos['localidad'] ?? null,
            ':rol'       => $datos['rol']       ?? 'USER',
        ]);
    }
}
