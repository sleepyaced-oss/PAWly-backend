<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JwtUtil.php';

class AuthController {

    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // POST /api/auth/register
    public function register() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, email y contrasena son obligatorios.']);
            return;
        }

        if ($this->user->existeEmail($datos['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe una cuenta con ese email.']);
            return;
        }

        $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        $rol = $datos['rol'] ?? 'USER';

        $id = $this->user->crear(
            $datos['nombre'],
            $datos['email'],
            $datos['password'],
            $datos['telefono']  ?? null,
            $datos['provincia'] ?? null,
            $datos['localidad'] ?? null,
            $rol
        );

        $token = JwtUtil::generarToken($id, $datos['email'], $rol);

        http_response_code(201);
        echo json_encode([
            'token'  => $token,
            'id'     => $id,
            'nombre' => $datos['nombre'],
            'email'  => $datos['email'],
            'rol'    => $rol
        ]);
    }

    // POST /api/auth/login
    public function login() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['email']) || empty($datos['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contrasena son obligatorios.']);
            return;
        }

        $usuario = $this->user->buscarPorEmail($datos['email']);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['error' => 'Email o contrasena incorrectos.']);
            return;
        }

        if (!password_verify($datos['password'], $usuario['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Email o contrasena incorrectos.']);
            return;
        }

        if (!$usuario['activo']) {
            http_response_code(403);
            echo json_encode(['error' => 'Tu cuenta ha sido desactivada. Contacta con el administrador.']);
            return;
        }

        $token = JwtUtil::generarToken($usuario['id'], $usuario['email'], $usuario['rol']);

        echo json_encode([
            'token'  => $token,
            'id'     => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email'  => $usuario['email'],
            'rol'    => $usuario['rol']
        ]);
    }

    // GET /api/auth/me
    public function me($usuarioAutenticado) {
        $usuario = $this->user->buscarPorId($usuarioAutenticado['sub']);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado.']);
            return;
        }

        echo json_encode($usuario);
    }
}
