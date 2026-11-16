<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\MemberService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $middleware->requireRole('admin');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $memberService = new MemberService($storage);

    $member = $memberService->createMember($data);

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Member berhasil ditambahkan.',
        'data' => $member->toPublicArray()
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
    error_log($e->getMessage());

    if (http_response_code() < 400) {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
