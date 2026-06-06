<?php

require_once __DIR__ . '/../utils/JwtUtil.php';

class Middleware {

    // Comprueba que el usuario ha iniciado sesion.
    // Si el token es valido devuelve los datos del usuario.
    // Si no, responde con 401 y para la ejecucion.
    public static function autenticado() {
        $token = JwtUtil::obtenerTokenDelHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado. Inicia sesion.']);
            exit();
        }

        $datos = JwtUtil::validarToken($token);

        if (!$datos) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalido o expirado.']);
            exit();
        }

        return $datos;
    }

    // Comprueba que el usuario ha iniciado sesion Y ademas es ADMIN.
    // Si no es admin responde con 403 y para la ejecucion.
    public static function soloAdmin() {
        $datos = self::autenticado();

        if ($datos['rol'] !== 'ADMIN') {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para realizar esta accion.']);
            exit();
        }

        return $datos;
    }
}
