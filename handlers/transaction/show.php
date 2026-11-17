<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $user = $middleware->requireLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $id = filter_var(
        $_GET['id_transaction'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        http_response_code(400);
        throw new \InvalidArgumentException(
            'ID Transaksi tidak valid.'
        );
    }

    $transactionService = new TransactionService($storage);

    $transaction = $transactionService->findTransactionById($id);

    if ($transaction === null) {
        http_response_code(404);
        throw new \RuntimeException('Transaksi tidak ditemukan.');
    }

    // Member hanya boleh membuka transaksi miliknya sendiri.
    if (
        $user->getRole() !== 'admin'
        && (int) $transaction['id_user'] !== $user->getIdUser()
    ) {
        http_response_code(403);
        throw new \RuntimeException(
            'Anda tidak memiliki izin untuk mengakses halaman ini.'
        );
    }

    $data = $transaction;
    $data['details'] =
        $transactionService->getTransactionDetailsWithEbook($id);
    $data['total_items'] = count($data['details']);

    echo json_encode([
        'success' => true,
        'message' => 'Detail Transaksi berhasil diambil.',
        'data' => $data
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
