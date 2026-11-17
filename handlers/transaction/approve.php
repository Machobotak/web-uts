<?php

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metode tidak diizinkan.');
    }

    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        throw new RuntimeException('ID transaksi tidak valid.');
    }

    $service = new TransactionService($storage);
    $service->updateStatus($id, 'approved');

    header('Location: ' . app_url('pages/admin/transactions.php?success=approved'));
    exit;
} catch (Throwable $e) {
    header('Location: ' . app_url('pages/admin/transactions.php?error=' . urlencode($e->getMessage())));
    exit;
}