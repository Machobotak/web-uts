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

    $transactionService = new TransactionService($storage);

    // Member hanya dapat melihat transaksi miliknya sendiri.
    $idUser = $user->getRole() === 'admin'
        ? filter_var($_GET['id_user'] ?? 0, FILTER_VALIDATE_INT)
        : $user->getIdUser();

    $transactions = $transactionService->searchTransactions([
        'status' => $_GET['status'] ?? '',
        'id_user' => $idUser
    ]);

    $transactions = array_map(
        function (array $transaction) use ($transactionService) {
            $transaction['total_items'] =
                $transactionService->countTransactionDetails(
                    (int) $transaction['id_transaction']
                );

            return $transaction;
        },
        $transactions
    );

    echo json_encode([
        'success' => true,
        'message' => 'Daftar Transaksi berhasil diambil.',
        'data' => $transactions
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
