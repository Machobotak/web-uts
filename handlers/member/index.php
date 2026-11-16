<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\MemberService;
use App\Middleware\AuthMiddleware;
use App\Models\Member;

header('Content-Type: application/json');

try {
    // Memastikan hanya Admin yang dapat mengakses.
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $memberService = new MemberService($storage);
    $members = $memberService->getAllMembers();

    // Menghilangkan password dari response.
    $result = array_map(
        fn($data) => (new Member($data))->toPublicArray(),
        $members
    );

    echo json_encode([
        'success' => true,
        'message' => 'Daftar Member berhasil diambil.',
        'data' => $result
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
