<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\ProfileService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new \RuntimeException('Method tidak diizinkan.');
    }

    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);

    // Hanya Member yang dapat mengakses.
    $user = $middleware->requireRole('member');

    $profileService = new ProfileService($storage);

    $member = $profileService->getProfile(
        $user->getIdUser()
    );

    if ($member === null) {
        http_response_code(404);
        throw new \RuntimeException('Profil tidak ditemukan.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diambil.',
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
