<?php

require_once __DIR__ . '/../models/Favorito.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../utils/Middleware.php';

class FavoritoController {

    private $favorito;
    private $animal;

    public function __construct() {
        $this->favorito = new Favorito();
        $this->animal   = new Animal();
    }

    // GET /api/favoritos
    // Devuelve los favoritos del usuario autenticado.
    public function index() {
        $usuarioAutenticado = Middleware::autenticado();

        $favoritos = $this->favorito->obtenerPorUsuario($usuarioAutenticado['sub']);

        echo json_encode($favoritos);
    }

    // POST /api/favoritos/{animal_id}
    // Añade un animal a favoritos. Si ya existe, lo elimina (toggle).
    public function toggle($animal_id) {
        $usuarioAutenticado = Middleware::autenticado();
        $usuario_id = $usuarioAutenticado['sub'];

        // Comprobar que el animal existe y está disponible
        $animal = $this->animal->obtenerPorId($animal_id);

        if (!$animal || $animal['estado'] !== 'DISPONIBLE') {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado o no disponible.']);
            return;
        }

        // No puedes marcar como favorito tu propio anuncio
        if ($animal['publicador_id'] == $usuario_id) {
            http_response_code(400);
            echo json_encode(['error' => 'No puedes guardar tu propio anuncio como favorito.']);
            return;
        }

        if ($this->favorito->existe($usuario_id, $animal_id)) {
            $this->favorito->eliminar($usuario_id, $animal_id);
            echo json_encode([
                'mensaje'   => 'Animal eliminado de favoritos.',
                'favorito'  => false
            ]);
        } else {
            $this->favorito->añadir($usuario_id, $animal_id);
            echo json_encode([
                'mensaje'   => 'Animal añadido a favoritos.',
                'favorito'  => true
            ]);
        }
    }
}
