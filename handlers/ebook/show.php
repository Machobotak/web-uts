<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;
use App\Models\Ebook;

header('Content-Type: application/json');

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $id = filter_input(INPUT_GET, 'id_ebook', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        throw new \InvalidArgumentException('ID Ebook tidak valid.');
    }

    $catalogService = new CatalogService($storage);
    $ebookData = $catalogService->findEbookById($id);

    if ($ebookData === null) {
        http_response_code(404);
        throw new \RuntimeException('Ebook tidak ditemukan.');
    }

    $ebook = (new Ebook($ebookData))->toArray();

    $categoryNames = $catalogService->getCategoryNameMap();
    $ebook['category_name'] =
        $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';

    echo json_encode([
        'success' => true,
        'message' => 'Detail Ebook berhasil diambil.',
        'data' => $ebook
    ], JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}