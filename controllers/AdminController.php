<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../utils/Middleware.php';

class AdminController
{

    private $user;
    private $animal;
    private $solicitud;

    public function __construct()
    {
        $this->user = new User();
        $this->animal = new Animal();
    }

    // GET /api/admin/usuarios
    // Lista todos los usuarios del sistema.
    public function usuarios()
    {
        Middleware::soloAdmin();

        $usuarios = $this->user->obtenerTodos();

        echo json_encode($usuarios);
    }

    // PUT /api/admin/usuarios/{id}/activar
    // Activa la cuenta de un usuario.
    public function activarUsuario($id)
    {
        Middleware::soloAdmin();

        $usuario = $this->user->buscarPorId($id);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado.']);
            return;
        }

        $this->user->cambiarEstado($id, 1);

        echo json_encode(['mensaje' => 'Usuario activado correctamente.']);
    }

    // PUT /api/admin/usuarios/{id}/desactivar
    // Desactiva la cuenta de un usuario.
    public function desactivarUsuario($id)
    {
        Middleware::soloAdmin();

        $usuario = $this->user->buscarPorId($id);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado.']);
            return;
        }

        // No se puede desactivar a otro admin
        if ($usuario['rol'] === 'ADMIN') {
            http_response_code(400);
            echo json_encode(['error' => 'No puedes desactivar a un administrador.']);
            return;
        }

        $this->user->cambiarEstado($id, 0);

        echo json_encode(['mensaje' => 'Usuario desactivado correctamente.']);
    }

    // GET /api/admin/animales/pendientes
    // Lista los anuncios pendientes de aprobación.
    public function animalesPendientes()
    {
        Middleware::soloAdmin();

        $animales = $this->animal->obtenerPendientes();

        echo json_encode($animales);
    }

    // PUT /api/admin/animales/{id}/aprobar
    // Aprueba un anuncio.
    public function aprobarAnimal($id)
    {
        Middleware::soloAdmin();

        $animal = $this->animal->obtenerPorId($id);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado.']);
            return;
        }

        if ($animal['estado'] !== 'PENDIENTE_APROBACION') {
            http_response_code(400);
            echo json_encode(['error' => 'Este anuncio no está pendiente de aprobación.']);
            return;
        }

        $this->animal->aprobar($id);

        echo json_encode(['mensaje' => 'Anuncio aprobado correctamente.']);
    }

    // PUT /api/admin/animales/{id}/rechazar
    // Rechaza un anuncio con un motivo obligatorio.
    public function rechazarAnimal($id)
    {
        Middleware::soloAdmin();

        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['motivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El motivo de rechazo es obligatorio.']);
            return;
        }

        $animal = $this->animal->obtenerPorId($id);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado.']);
            return;
        }

        if ($animal['estado'] !== 'PENDIENTE_APROBACION') {
            http_response_code(400);
            echo json_encode(['error' => 'Este anuncio no está pendiente de aprobación.']);
            return;
        }

        $this->animal->rechazar($id, $datos['motivo']);

        echo json_encode(['mensaje' => 'Anuncio rechazado.']);
    }

    // GET /api/admin/stats
    // Estadísticas básicas del panel de administración.
    public function stats()
    {
        Middleware::soloAdmin();

        echo json_encode([
            'pendientes_aprobacion' => $this->animal->contarPendientes()
        ]);
    }

    // GET /api/admin/animales
    public function todosAnimales()
    {
        Middleware::soloAdmin();
        echo json_encode($this->animal->obtenerTodosAdmin());
    }

    // GET /api/admin/solicitudes
    public function todasSolicitudes()
    {
        Middleware::soloAdmin();
        $solicitud = new Solicitud();
        echo json_encode($solicitud->obtenerTodas());
    }

    public function actualizarUsuario($id)
    {
        Middleware::soloAdmin();
        $datos = json_decode(file_get_contents('php://input'), true);
        if (empty($datos['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es obligatorio.']);
            return;
        }
        $this->user->actualizarAdmin($id, $datos);
        echo json_encode(['mensaje' => 'Usuario actualizado correctamente.']);
    }

    // DELETE /api/admin/usuarios/{id}
    public function eliminarUsuario($id)
    {
        Middleware::soloAdmin();

        $usuario = $this->user->buscarPorId($id);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado.']);
            return;
        }

        if ($usuario['rol'] === 'ADMIN') {
            http_response_code(400);
            echo json_encode(['error' => 'No puedes eliminar a un administrador.']);
            return;
        }

        $this->user->eliminar($id);
        echo json_encode(['mensaje' => 'Usuario eliminado correctamente.']);
    }
}
