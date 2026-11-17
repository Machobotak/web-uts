<?php
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    (new AuthMiddleware(new AuthService($storage)))->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $data = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
        ? json_decode(file_get_contents('php://input'), true)
        : $_POST;

    if (!is_array($data)) {
        throw new \InvalidArgumentException('Data tidak valid.');
    }

    $ebook = (new CatalogService($storage))->createEbook($data);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Ebook berhasil ditambahkan.',
        'data' => $ebook->toArray()
    ]);
} catch (\InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (\Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
