<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$adminPassword = 'MariaEspina2026';
$imageMapPath = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'image-map.json';

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function loadImageMap(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false || $content === '') {
        return [];
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'message' => 'Metodo no permitido.']);
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$password = (string) ($payload['password'] ?? '');
$key = (string) ($payload['key'] ?? '');

if ($password !== $adminPassword) {
    respond(403, ['ok' => false, 'message' => 'Clave invalida.']);
}

if (!preg_match('/^[a-z0-9:-]+$/', $key)) {
    respond(422, ['ok' => false, 'message' => 'Identificador de imagen invalido.']);
}

$map = loadImageMap($imageMapPath);
$previousUrl = $map[$key] ?? '';

if (is_string($previousUrl) && str_starts_with($previousUrl, './img/admin/')) {
    $previousPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['./', '/'], ['', DIRECTORY_SEPARATOR], $previousUrl);
    if (is_file($previousPath)) {
        @unlink($previousPath);
    }
}

unset($map[$key]);

if (file_put_contents($imageMapPath, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
    respond(500, ['ok' => false, 'message' => 'No se pudo actualizar el mapa de imagenes.']);
}

respond(200, ['ok' => true]);
