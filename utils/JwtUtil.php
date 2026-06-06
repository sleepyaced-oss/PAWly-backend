<?php

class JwtUtil {

    private static $secret = 'pawly_secret_key_2025_muy_larga_y_segura';
    private static $expiracion = 86400; // 24 horas en segundos

    // Genera un token JWT dado el id, email y rol del usuario
    public static function generarToken($id, $email, $rol) {
        $header = self::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload = self::base64UrlEncode(json_encode([
            'sub'   => $id,
            'email' => $email,
            'rol'   => $rol,
            'iat'   => time(),
            'exp'   => time() + self::$expiracion
        ]));

        $firma = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", self::$secret, true)
        );

        return "$header.$payload.$firma";
    }

    // Valida el token y devuelve el payload si es correcto, o false si no
    public static function validarToken($token) {
        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            return false;
        }

        [$header, $payload, $firma] = $partes;

        // Verificar la firma
        $firmaEsperada = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", self::$secret, true)
        );

        if (!hash_equals($firmaEsperada, $firma)) {
            return false;
        }

        // Decodificar el payload
        $datos = json_decode(self::base64UrlDecode($payload), true);

        if (!$datos) {
            return false;
        }

        // Verificar que no ha expirado
        if (isset($datos['exp']) && $datos['exp'] < time()) {
            return false;
        }

        return $datos;
    }

    // Extrae el token del header Authorization: Bearer <token>
    public static function obtenerTokenDelHeader() {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $partes = explode(' ', $headers['Authorization']);

        if (count($partes) !== 2 || $partes[0] !== 'Bearer') {
            return null;
        }

        return $partes[1];
    }

    // Helpers de codificacion Base64 URL-safe
    private static function base64UrlEncode($datos) {
        return rtrim(strtr(base64_encode($datos), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($datos) {
        return base64_decode(strtr($datos, '-_', '+/'));
    }
}
