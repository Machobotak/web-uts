<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    // Mendukung JSON dan Form POST.
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($data)) {
            http_response_code(400);
            throw new \InvalidArgumentException(
                'Format JSON tidak valid.'
            );
        }
    } else {
        $data = $_POST;
    }

    $idDetail = filter_var(
        $data['id_detail'] ?? $data['id_transaction'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$idDetail || $idDetail < 1) {
        http_response_code(400);
        throw new \InvalidArgumentException(
            'ID Detail Transaksi tidak valid.'
        );
    }

    $transactionService = new TransactionService($storage);

    if ($transactionService->findDetailById($idDetail) === null) {
        http_response_code(404);
        throw new \RuntimeException(
            'Detail transaksi tidak ditemukan.'
        );
    }

    $detail = $transactionService->returnDetail($idDetail);

    echo json_encode([
        'success' => true,
        'message' => 'Ebook berhasil dikembalikan.',
        'data' => $detail->toArray()
    ], JSON_PRETTY_PRINT);

} catch (\InvalidArgumentException $e) {
    if (http_response_code() < 400) {
        http_response_code(422);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} catch (\Throwable $e) {
    if (http_response_code() < 400) {
        error_log($e->getMessage());
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan pada server.'
        ]);
    } else {
        // Status sudah ditentukan oleh pengecekan di atas (400/404/405).
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
