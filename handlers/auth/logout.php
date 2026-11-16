<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

// Hanya menerima POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method tidak diizinkan.');
}

try {
    $authService = new AuthService($storage);

    // Mengakhiri session.
    $authService->logout();

    if (wants_json()) {
        header('Content-Type: application/json');

        echo json_encode([
            'success' => true,
            'message' => 'Logout berhasil.'
        ]);

        exit;
    }

    // Redirect ke halaman login.
    header('Location: ' . app_url('pages/auth/login.php?logout=1'));
    exit;

} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);
    echo 'Terjadi kesalahan saat logout.';
}
