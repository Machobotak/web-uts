<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    // Ebook dapat dibaca oleh seluruh pengguna yang sudah login.
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $catalogService = new CatalogService($storage);

    // Filter opsional: ?q=, ?category=, ?status=
    $ebooks = $catalogService->searchEbooks([
        'q' => $_GET['q'] ?? '',
        'category' => $_GET['category'] ?? 0,
        'status' => $_GET['status'] ?? ''
    ]);

    $categoryNames = $catalogService->getCategoryNameMap();

    $ebooks = array_map(
        function (array $ebook) use ($categoryNames) {
            $ebook['category_name'] =
                $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';

            return $ebook;
        },
        $ebooks
    );

    echo json_encode([
        'success' => true,
        'message' => 'Daftar Ebook berhasil diambil.',
        'data' => $ebooks
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