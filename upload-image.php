<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$adminPassword = 'MariaEspina2026';
$imageDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'admin';
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

$password = (string) ($_POST['password'] ?? '');
$key = (string) ($_POST['key'] ?? '');

if ($password !== $adminPassword) {
    respond(403, ['ok' => false, 'message' => 'Clave invalida.']);
}

if (!preg_match('/^[a-z0-9:-]+$/', $key)) {
    respond(422, ['ok' => false, 'message' => 'Identificador de imagen invalido.']);
}

if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
    respond(422, ['ok' => false, 'message' => 'No se recibio ninguna imagen.']);
}

$file = $_FILES['image'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    respond(422, ['ok' => false, 'message' => 'La subida de la imagen fallo.']);
}

$tmpName = (string) ($file['tmp_name'] ?? '');
if ($tmpName === '' || !is_uploaded_file($tmpName)) {
    respond(422, ['ok' => false, 'message' => 'Archivo temporal invalido.']);
}

$mimeType = mime_content_type($tmpName) ?: '';
$extensions = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif'
];

if (!isset($extensions[$mimeType])) {
    respond(422, ['ok' => false, 'message' => 'Formato de imagen no permitido.']);
}

if (!is_dir($imageDirectory) && !mkdir($imageDirectory, 0775, true) && !is_dir($imageDirectory)) {
    respond(500, ['ok' => false, 'message' => 'No se pudo preparar la carpeta de imagenes.']);
}

$map = loadImageMap($imageMapPath);
$previousUrl = $map[$key] ?? '';
if (is_string($previousUrl) && str_starts_with($previousUrl, './img/admin/')) {
    $previousPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['./', '/'], ['', DIRECTORY_SEPARATOR], $previousUrl);
    if (is_file($previousPath)) {
        @unlink($previousPath);
    }
}

$safeKey = str_replace([':', '/'], '-', $key);
$fileName = $safeKey . '-' . time() . '.' . $extensions[$mimeType];
$destinationPath = $imageDirectory . DIRECTORY_SEPARATOR . $fileName;

if (!move_uploaded_file($tmpName, $destinationPath)) {
    respond(500, ['ok' => false, 'message' => 'No se pudo guardar la imagen en el servidor.']);
}

$publicUrl = './img/admin/' . rawurlencode($fileName);
$map[$key] = $publicUrl;

if (file_put_contents($imageMapPath, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
    @unlink($destinationPath);
    respond(500, ['ok' => false, 'message' => 'No se pudo actualizar el mapa de imagenes.']);
}

respond(200, ['ok' => true, 'url' => $publicUrl]);
