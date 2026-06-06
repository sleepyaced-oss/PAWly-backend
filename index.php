<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AnimalController.php';
require_once __DIR__ . '/controllers/FavoritoController.php';
require_once __DIR__ . '/controllers/SolicitudController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/AdminController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^.*?/api#', '', $uri);
error_log("METHOD: " . $method . " URI: [" . $uri . "]");
$uri = rtrim($uri, '/');

// ── AUTH ──────────────────────────────────────────────────────────────────────
if ($method === 'POST' && $uri === '/auth/register') {
    (new AuthController())->register();
    exit();
}
if ($method === 'POST' && $uri === '/auth/login') {
    (new AuthController())->login();
    exit();
}
if ($method === 'GET' && $uri === '/auth/me') {
    (new AuthController())->me(Middleware::autenticado());
    exit();
}

// ── ANIMALES ──────────────────────────────────────────────────────────────────
if ($method === 'GET' && $uri === '/animales') {
    (new AnimalController())->index();
    exit();
}
if ($method === 'GET' && preg_match('#^/animales/(\d+)$#', $uri, $m)) {
    (new AnimalController())->show($m[1]);
    exit();
}
if ($method === 'POST' && $uri === '/animales') {
    (new AnimalController())->store();
    exit();
}
if ($method === 'PUT' && preg_match('#^/animales/(\d+)$#', $uri, $m)) {
    (new AnimalController())->update($m[1]);
    exit();
}
if ($method === 'DELETE' && preg_match('#^/animales/(\d+)$#', $uri, $m)) {
    (new AnimalController())->destroy($m[1]);
    exit();
}

// ── FAVORITOS ─────────────────────────────────────────────────────────────────
if ($method === 'GET' && $uri === '/favoritos') {
    (new FavoritoController())->index();
    exit();
}
if ($method === 'POST' && preg_match('#^/favoritos/(\d+)$#', $uri, $m)) {
    (new FavoritoController())->toggle($m[1]);
    exit();
}

// ── SOLICITUDES ───────────────────────────────────────────────────────────────
// GET /admin/solicitudes
if ($method === 'GET' && $uri === '/admin/solicitudes') {
    (new AdminController())->todasSolicitudes();
    exit();
}
if ($method === 'GET' && $uri === '/solicitudes/recibidas') {
    (new SolicitudController())->recibidas();
    exit();
}
if ($method === 'GET' && $uri === '/solicitudes/enviadas') {
    (new SolicitudController())->enviadas();
    exit();
}
if ($method === 'POST' && preg_match('#^/solicitudes/(\d+)$#', $uri, $m)) {
    (new SolicitudController())->store($m[1]);
    exit();
}
if ($method === 'PUT' && preg_match('#^/solicitudes/(\d+)/aceptar$#', $uri, $m)) {
    (new SolicitudController())->aceptar($m[1]);
    exit();
}
if ($method === 'PUT' && preg_match('#^/solicitudes/(\d+)/rechazar$#', $uri, $m)) {
    (new SolicitudController())->rechazar($m[1]);
    exit();
}
if ($method === 'DELETE' && preg_match('#^/solicitudes/(\d+)$#', $uri, $m)) {
    (new SolicitudController())->destroy($m[1]);
    exit();
}

// ── USUARIOS ──────────────────────────────────────────────────────────────────
if ($method === 'PUT' && $uri === '/usuarios/perfil') {
    (new UserController())->actualizar();
    exit();
}
if ($method === 'GET' && preg_match('#^/usuarios/(\d+)/animales$#', $uri, $m)) {
    (new UserController())->animales($m[1]);
    exit();
}
if ($method === 'GET' && preg_match('#^/usuarios/(\d+)$#', $uri, $m)) {
    (new UserController())->show($m[1]);
    exit();
}

// ── ADMIN ─────────────────────────────────────────────────────────────────────
if ($method === 'PUT' && preg_match('#^/admin/usuarios/(\d+)$#', $uri, $m)) {
    (new AdminController())->actualizarUsuario($m[1]);
    exit();
}
if ($method === 'GET' && $uri === '/admin/stats') {
    (new AdminController())->stats();
    exit();
}
if ($method === 'GET' && $uri === '/admin/usuarios') {
    (new AdminController())->usuarios();
    exit();
}
if ($method === 'DELETE' && preg_match('#^/admin/usuarios/(\d+)$#', $uri, $m)) {
    (new AdminController())->eliminarUsuario($m[1]);
    exit();
}
// GET /admin/animales
if ($method === 'GET' && $uri === '/admin/animales') {
    (new AdminController())->todosAnimales();
    exit();
}
if ($method === 'GET' && $uri === '/admin/animales/pendientes') {
    (new AdminController())->animalesPendientes();
    exit();
}
if ($method === 'PUT' && preg_match('#^/admin/animales/(\d+)/aprobar$#', $uri, $m)) {
    (new AdminController())->aprobarAnimal($m[1]);
    exit();
}
if ($method === 'PUT' && preg_match('#^/admin/animales/(\d+)/rechazar$#', $uri, $m)) {
    (new AdminController())->rechazarAnimal($m[1]);
    exit();
}

// ── 404 ───────────────────────────────────────────────────────────────────────
http_response_code(404);
echo json_encode(['error' => 'Ruta no encontrada.']);
