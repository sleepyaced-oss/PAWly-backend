<?php

require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../utils/Middleware.php';

class SolicitudController
{

    private $solicitud;
    private $animal;

    public function __construct()
    {
        $this->solicitud = new Solicitud();
        $this->animal = new Animal();
    }

    // GET /api/solicitudes/recibidas
    // Solicitudes recibidas en los anuncios del usuario autenticado.
    public function recibidas()
    {
        $usuarioAutenticado = Middleware::autenticado();

        $solicitudes = $this->solicitud->obtenerRecibidas($usuarioAutenticado['sub']);

        echo json_encode($solicitudes);
    }

    // GET /api/solicitudes/enviadas
    // Solicitudes enviadas por el usuario autenticado.
    public function enviadas()
    {
        $usuarioAutenticado = Middleware::autenticado();

        $solicitudes = $this->solicitud->obtenerEnviadas($usuarioAutenticado['sub']);

        echo json_encode($solicitudes);
    }

    // POST /api/solicitudes/{animal_id}
    // Envía una solicitud de adopción para un animal.
    public function store($animal_id)
    {
        $usuarioAutenticado = Middleware::autenticado();
        $solicitante_id = $usuarioAutenticado['sub'];

        $datos = json_decode(file_get_contents('php://input'), true);

        // Comprobar que el animal existe y está disponible
        $animal = $this->animal->obtenerPorId($animal_id);

        if (!$animal || $animal['estado'] !== 'DISPONIBLE') {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado o no disponible.']);
            return;
        }

        // No puedes solicitar tu propio animal
        if ($animal['publicador_id'] == $solicitante_id) {
            http_response_code(400);
            echo json_encode(['error' => 'No puedes enviar una solicitud a tu propio anuncio.']);
            return;
        }

        // Comprobar si ya hay una solicitud pendiente para este animal
        if ($this->solicitud->yaExiste($solicitante_id, $animal_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya tienes una solicitud pendiente para este animal.']);
            return;
        }

        $id = $this->solicitud->crear(
            $animal_id,
            $solicitante_id,
            $animal['publicador_id'],
            $datos['mensaje'] ?? null
        );

        http_response_code(201);
        echo json_encode([
            'mensaje' => 'Solicitud enviada correctamente.',
            'id' => $id
        ]);
    }

    // PUT /api/solicitudes/{id}/aceptar
    // El publicador acepta la solicitud.
    public function aceptar($id)
    {
        $usuarioAutenticado = Middleware::autenticado();

        $solicitud = $this->solicitud->obtenerPorId($id);

        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitud no encontrada.']);
            return;
        }

        if ($solicitud['publicador_id'] != $usuarioAutenticado['sub']) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para gestionar esta solicitud.']);
            return;
        }

        if ($solicitud['estado'] !== 'PENDIENTE') {
            http_response_code(400);
            echo json_encode(['error' => 'Esta solicitud ya ha sido gestionada.']);
            return;
        }

        $this->solicitud->cambiarEstado($id, 'ACEPTADA');

        // Marcar el animal como adoptado
        $animal = new Animal();
        $animal->marcarAdoptado($solicitud['animal_id']);

        echo json_encode(['mensaje' => 'Solicitud aceptada correctamente.']);
    }

    // PUT /api/solicitudes/{id}/rechazar
    // El publicador rechaza la solicitud.
    public function rechazar($id)
    {
        $usuarioAutenticado = Middleware::autenticado();

        $solicitud = $this->solicitud->obtenerPorId($id);

        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitud no encontrada.']);
            return;
        }

        // Solo el publicador puede rechazar
        if ($solicitud['publicador_id'] != $usuarioAutenticado['sub']) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para gestionar esta solicitud.']);
            return;
        }

        if ($solicitud['estado'] !== 'PENDIENTE') {
            http_response_code(400);
            echo json_encode(['error' => 'Esta solicitud ya ha sido gestionada.']);
            return;
        }

        $this->solicitud->cambiarEstado($id, 'RECHAZADA');

        echo json_encode(['mensaje' => 'Solicitud rechazada.']);
    }

    // DELETE /api/solicitudes/{id}
    // El solicitante cancela su propia solicitud (solo si está pendiente).
    public function destroy($id)
    {
        $usuarioAutenticado = Middleware::autenticado();

        $solicitud = $this->solicitud->obtenerPorId($id);

        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['error' => 'Solicitud no encontrada.']);
            return;
        }

        // Solo el solicitante puede cancelarla
        if ($solicitud['solicitante_id'] != $usuarioAutenticado['sub']) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para cancelar esta solicitud.']);
            return;
        }

        if ($solicitud['estado'] !== 'PENDIENTE') {
            http_response_code(400);
            echo json_encode(['error' => 'Solo puedes cancelar solicitudes pendientes.']);
            return;
        }

        $this->solicitud->eliminar($id);

        echo json_encode(['mensaje' => 'Solicitud cancelada correctamente.']);
    }
}
