<?php

require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../utils/Middleware.php';

class AnimalController
{

    private $animal;

    public function __construct()
    {
        $this->animal = new Animal();
    }

    // GET /api/animales
    // Publica, sin login. Acepta filtros por query string.
    public function index()
    {
        $especie = $_GET['especie'] ?? null;
        $etapa_vida = $_GET['etapa_vida'] ?? null;
        $sexo = $_GET['sexo'] ?? null;
        $provincia = $_GET['provincia'] ?? null;
        $color = $_GET['color'] ?? null;
        $orden = $_GET['orden'] ?? 'desc';

        $animales = $this->animal->obtenerTodos(
            $especie,
            $etapa_vida,
            $sexo,
            $provincia,
            $color,
            $orden
        );

        echo json_encode($animales);
    }

    // GET /api/animales/{id}
    // Publica, sin login.
    public function show($id)
    {
        $animal = $this->animal->obtenerPorId($id);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado.']);
            return;
        }

        // Solo se puede ver si está disponible o en proceso
        if ($animal['estado'] === 'PENDIENTE_APROBACION' || $animal['estado'] === 'RECHAZADO') {
            http_response_code(404);
            echo json_encode(['error' => 'Este anuncio no está disponible.']);
            return;
        }

        echo json_encode($animal);
    }

    // POST /api/animales
    // Requiere login.
    public function store()
    {
        $usuarioAutenticado = Middleware::autenticado();
        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos obligatorios
        $requeridos = ['nombre', 'especie', 'sexo', 'etapa_vida', 'tamanyo', 'color', 'descripcion', 'provincia', 'localidad'];
        foreach ($requeridos as $campo) {
            if (empty($datos[$campo])) {
                http_response_code(400);
                echo json_encode(['error' => "El campo '$campo' es obligatorio."]);
                return;
            }
        }

        $id = $this->animal->crear($datos, $usuarioAutenticado['sub']);

        http_response_code(201);
        echo json_encode([
            'mensaje' => 'Anuncio publicado correctamente. Pendiente de aprobación.',
            'id' => $id
        ]);
    }

    // PUT /api/animales/{id}
    // Requiere login. Solo el publicador puede editar.
    public function update($id)
    {
        $usuarioAutenticado = Middleware::autenticado();
        $datos = json_decode(file_get_contents('php://input'), true);

        $animal = $this->animal->obtenerPorId($id);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado.']);
            return;
        }

        // Comprobar que es el publicador
        $esPublicador = $animal['publicador_id'] == $usuarioAutenticado['sub'];
        $esAdmin = $usuarioAutenticado['rol'] === 'ADMIN';

        if (!$esPublicador && !$esAdmin) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para editar este anuncio.']);
            return;
        }

        $this->animal->editar($id, $datos);

        echo json_encode(['mensaje' => 'Anuncio actualizado. Pendiente de aprobación.']);
    }

    // DELETE /api/animales/{id}
    // Requiere login. Solo el publicador o admin puede eliminar.
    public function destroy($id)
    {
        $usuarioAutenticado = Middleware::autenticado();

        $animal = $this->animal->obtenerPorId($id);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal no encontrado.']);
            return;
        }

        $esPublicador = $animal['publicador_id'] == $usuarioAutenticado['sub'];
        $esAdmin = $usuarioAutenticado['rol'] === 'ADMIN';

        if (!$esPublicador && !$esAdmin) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para eliminar este anuncio.']);
            return;
        }

        $this->animal->eliminar($id);

        echo json_encode(['mensaje' => 'Anuncio eliminado correctamente.']);
    }


}
