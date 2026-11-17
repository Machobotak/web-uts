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

    $memberId = (int) ($_SESSION['user_id'] ?? 0);
    $ebookIds = $_POST['ebook_ids'] ?? [];

    if (!is_array($ebookIds) && !is_string($ebookIds)) {
        throw new RuntimeException('Pilihan ebook tidak valid.');
    }

    if (is_string($ebookIds)) {
        $ebookIds = [$ebookIds];
    }

    $service = new TransactionService($storage);
    $service->borrow($memberId, $ebookIds);

    header('Location: ' . app_url('pages/member/borrowings.php?success=borrowed'));
    exit;
} catch (Throwable $e) {
    header('Location: ' . app_url('pages/member/borrowings.php?error=' . urlencode($e->getMessage())));
    exit;
}