<?php

require_once __DIR__ . '/../config/database.php';

class Animal
{

    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Devuelve animales DISPONIBLES con filtros opcionales
    public function obtenerTodos($especie, $etapa_vida, $sexo, $provincia, $color, $orden)
    {
        $sql = "SELECT a.*, u.nombre as publicador_nombre, u.foto_perfil as publicador_foto
                FROM animales a
                JOIN usuarios u ON a.publicador_id = u.id
                WHERE a.estado = 'DISPONIBLE'";

        $params = [];

        if ($especie) {
            $sql .= " AND a.especie = :especie";
            $params[':especie'] = $especie;
        }
        if ($etapa_vida) {
            $sql .= " AND a.etapa_vida = :etapa_vida";
            $params[':etapa_vida'] = $etapa_vida;
        }
        if ($sexo) {
            $sql .= " AND a.sexo = :sexo";
            $params[':sexo'] = $sexo;
        }
        if ($provincia) {
            $sql .= " AND a.provincia = :provincia";
            $params[':provincia'] = $provincia;
        }
        if ($color) {
            $sql .= " AND a.color = :color";
            $params[':color'] = $color;
        }

        $sql .= $orden === 'asc'
            ? " ORDER BY a.fecha_publicacion ASC"
            : " ORDER BY a.fecha_publicacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Devuelve el detalle de un animal por su id
    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre as publicador_nombre, u.foto_perfil as publicador_foto
             FROM animales a
             JOIN usuarios u ON a.publicador_id = u.id
             WHERE a.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Devuelve todos los anuncios de un usuario concreto
    public function obtenerPorUsuario($publicador_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM animales
             WHERE publicador_id = :publicador_id
             ORDER BY fecha_publicacion DESC"
        );
        $stmt->execute([':publicador_id' => $publicador_id]);
        return $stmt->fetchAll();
    }

    // Crea un nuevo anuncio y devuelve su id
    public function crear($datos, $publicador_id)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO animales
                (nombre, especie, raza, sexo, etapa_vida, tamanyo, color, descripcion,
                 esterilizado, vacunado, desparasitado, microchip, provincia, localidad, publicador_id)
             VALUES
                (:nombre, :especie, :raza, :sexo, :etapa_vida, :tamanyo, :color, :descripcion,
                 :esterilizado, :vacunado, :desparasitado, :microchip, :provincia, :localidad, :publicador_id)"
        );
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':especie' => $datos['especie'],
            ':raza' => $datos['raza'] ?? null,
            ':sexo' => $datos['sexo'],
            ':etapa_vida' => $datos['etapa_vida'],
            ':tamanyo' => $datos['tamanyo'],
            ':color' => $datos['color'],
            ':descripcion' => $datos['descripcion'],
            ':esterilizado' => $datos['esterilizado'] ?? 0,
            ':vacunado' => $datos['vacunado'] ?? 0,
            ':desparasitado' => $datos['desparasitado'] ?? 0,
            ':microchip' => $datos['microchip'] ?? 0,
            ':provincia' => $datos['provincia'],
            ':localidad' => $datos['localidad'],
            ':publicador_id' => $publicador_id
        ]);
        return $this->conn->lastInsertId();
    }

    // Edita un anuncio existente y lo vuelve a poner en pendiente
    public function editar($id, $datos)
    {
        $stmt = $this->conn->prepare(
            "UPDATE animales
             SET nombre = :nombre, especie = :especie, raza = :raza, sexo = :sexo,
                 etapa_vida = :etapa_vida, tamanyo = :tamanyo, color = :color,
                 descripcion = :descripcion, esterilizado = :esterilizado,
                 vacunado = :vacunado, desparasitado = :desparasitado,
                 microchip = :microchip, provincia = :provincia, localidad = :localidad,
                 estado = 'PENDIENTE_APROBACION'
             WHERE id = :id"
        );
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':especie' => $datos['especie'],
            ':raza' => $datos['raza'] ?? null,
            ':sexo' => $datos['sexo'],
            ':etapa_vida' => $datos['etapa_vida'],
            ':tamanyo' => $datos['tamanyo'],
            ':color' => $datos['color'],
            ':descripcion' => $datos['descripcion'],
            ':esterilizado' => $datos['esterilizado'] ?? 0,
            ':vacunado' => $datos['vacunado'] ?? 0,
            ':desparasitado' => $datos['desparasitado'] ?? 0,
            ':microchip' => $datos['microchip'] ?? 0,
            ':provincia' => $datos['provincia'],
            ':localidad' => $datos['localidad']
        ]);
    }

    // Elimina un anuncio por su id
    public function eliminar($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM animales WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    // Guarda la ruta de la imagen de un animal
    public function actualizarImagen($id, $rutaImagen)
    {
        $stmt = $this->conn->prepare(
            "UPDATE animales SET imagen = :imagen WHERE id = :id"
        );
        $stmt->execute([
            ':imagen' => $rutaImagen,
            ':id' => $id
        ]);
    }

    // --- Para el admin ---

    // Devuelve todos los anuncios pendientes de aprobacion
    public function obtenerPendientes()
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre as publicador_nombre
             FROM animales a
             JOIN usuarios u ON a.publicador_id = u.id
             WHERE a.estado = 'PENDIENTE_APROBACION'
             ORDER BY a.fecha_publicacion ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Aprueba un anuncio
    public function aprobar($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE animales SET estado = 'DISPONIBLE', motivo_rechazo = NULL WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    // Rechaza un anuncio con un motivo
    public function rechazar($id, $motivo)
    {
        $stmt = $this->conn->prepare(
            "UPDATE animales SET estado = 'RECHAZADO', motivo_rechazo = :motivo WHERE id = :id"
        );
        $stmt->execute([
            ':id' => $id,
            ':motivo' => $motivo
        ]);
    }

    // Cuenta los anuncios pendientes
    public function contarPendientes()
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as total FROM animales WHERE estado = 'PENDIENTE_APROBACION'"
        );
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    public function obtenerTodosAdmin()
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre as publicador_nombre
         FROM animales a
         JOIN usuarios u ON a.publicador_id = u.id
         ORDER BY a.fecha_publicacion DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function marcarAdoptado($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE animales SET estado = 'ADOPTADO' WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

}
