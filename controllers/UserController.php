<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../utils/Middleware.php';

class UserController {

    private $user;
    private $animal;

    public function __construct() {
        $this->user   = new User();
        $this->animal = new Animal();
    }

    // GET /api/usuarios/{id}
    // Perfil público de un usuario.
    public function show($id) {
        $usuario = $this->user->buscarPorId($id);

        if (!$usuario || !$usuario['activo']) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado.']);
            return;
        }

        // Devolver solo los campos públicos
        echo json_encode([
            'id'             => $usuario['id'],
            'nombre'         => $usuario['nombre'],
            'provincia'      => $usuario['provincia'],
            'localidad'      => $usuario['localidad'],
            'biografia'      => $usuario['biografia'],
            'foto_perfil'    => $usuario['foto_perfil'],
            'fecha_registro' => $usuario['fecha_registro']
        ]);
    }

    // GET /api/usuarios/{id}/animales
    // Anuncios publicados por un usuario (solo los DISPONIBLES si es perfil ajeno).
    public function animales($id) {
        $usuarioAutenticado = null;
        $token = \JwtUtil::obtenerTokenDelHeader();
        if ($token) {
            $usuarioAutenticado = \JwtUtil::validarToken($token);
        }

        $esPropietario = $usuarioAutenticado && $usuarioAutenticado['sub'] == $id;

        $animales = $this->animal->obtenerPorUsuario($id);

        // Si no es el propietario, filtrar solo los disponibles
        if (!$esPropietario) {
            $animales = array_values(array_filter($animales, function($a) {
                return $a['estado'] === 'DISPONIBLE';
            }));
        }

        echo json_encode($animales);
    }

    // PUT /api/usuarios/perfil
    // El usuario autenticado edita su propio perfil.
    public function actualizar() {
        $usuarioAutenticado = Middleware::autenticado();
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es obligatorio.']);
            return;
        }

        $this->user->actualizar(
            $usuarioAutenticado['sub'],
            $datos['nombre'],
            $datos['telefono']  ?? null,
            $datos['provincia'] ?? null,
            $datos['localidad'] ?? null,
            $datos['biografia'] ?? null
        );

        echo json_encode(['mensaje' => 'Perfil actualizado correctamente.']);
    }

    // POST /api/usuarios/foto
    // El usuario autenticado sube su foto de perfil.
    
}
