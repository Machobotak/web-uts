
<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\MemberService;
use App\Middleware\AuthMiddleware;
use App\Models\Member;

header('Content-Type: application/json');

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        throw new \InvalidArgumentException('ID Member tidak valid.');
    }

    $memberService = new MemberService($storage);
    $memberData = $memberService->findById($id);

    if ($memberData === null) {
        http_response_code(404);
        throw new \RuntimeException('Member tidak ditemukan.');
    }

    $member = new Member($memberData);

    echo json_encode([
        'success' => true,
        'message' => 'Detail Member berhasil diambil.',
        'data' => $member->toPublicArray()
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
