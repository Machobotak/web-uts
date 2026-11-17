<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;
use App\Models\Category;

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

    $id = filter_input(INPUT_GET, 'id_category', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        throw new \InvalidArgumentException('ID Kategori tidak valid.');
    }

    $catalogService = new CatalogService($storage);
    $categoryData = $catalogService->findCategoryById($id);

    if ($categoryData === null) {
        http_response_code(404);
        throw new \RuntimeException('Kategori tidak ditemukan.');
    }

    $category = (new Category($categoryData))->toArray();

    // Jumlah ebook yang memakai kategori ini.
    $category['ebook_count'] = $catalogService->countEbooksByCategory($id);

    echo json_encode([
        'success' => true,
        'message' => 'Detail Kategori berhasil diambil.',
        'data' => $category
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