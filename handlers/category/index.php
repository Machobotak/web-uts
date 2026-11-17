<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    // Kategori dapat dibaca oleh seluruh pengguna yang sudah login.
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $catalogService = new CatalogService($storage);

    // Menyertakan jumlah ebook yang memakai tiap kategori.
    $categories = $catalogService->getCategoriesWithEbookCount();

    // Filter opsional: ?q=
    $keyword = strtolower(trim($_GET['q'] ?? ''));

    if ($keyword !== '') {
        $categories = array_values(array_filter(
            $categories,
            fn(array $category) => str_contains(
                strtolower(
                    ($category['name'] ?? '') . ' ' .
                    ($category['description'] ?? '')
                ),
                $keyword
            )
        ));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Daftar Kategori berhasil diambil.',
        'data' => $categories
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