<?php
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CategoryService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    $auth = new AuthService($storage);
    (new AuthMiddleware($auth))->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new RuntimeException('Method tidak diizinkan.');
    }

    $data = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
        ? json_decode(file_get_contents('php://input'), true)
        : $_POST;

    if (!is_array($data)) {
        throw new InvalidArgumentException('Data tidak valid.');
    }

    $id = (int) ($data['id'] ?? 0);

    if ($id < 1) {
        throw new InvalidArgumentException('ID kategori tidak valid.');
    }

    (new CategoryService($storage))->deleteCategory($id);

    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil dihapus.'
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
