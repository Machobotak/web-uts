<?php

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('member');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Metode tidak diizinkan.');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $memberId = (int) ($_SESSION['user_id'] ?? 0);

    if ($id <= 0 || $memberId <= 0) {
        throw new RuntimeException('Data transaksi tidak valid.');
    }

    $service = new TransactionService($storage);
    $service->cancel($id, $memberId);

    header('Location: ' . app_url('pages/member/history.php?success=cancelled'));
    exit;
} catch (Throwable $e) {
    header('Location: ' . app_url('pages/member/borrowings.php?error=' . urlencode($e->getMessage())));
    exit;
}