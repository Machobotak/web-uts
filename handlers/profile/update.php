<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\ProfileService;
use App\Middleware\AuthMiddleware;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method tidak diizinkan.'
        ]);

        exit;
    }

    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);

    // Hanya Member yang dapat mengakses.
    $user = $middleware->requireRole('member');

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

    $profileService = new ProfileService($storage);

    $current = $profileService->getProfile(
        $user->getIdUser()
    );

    if ($current === null) {
        http_response_code(404);
        throw new \RuntimeException('Profil tidak ditemukan.');
    }

    // Field yang tidak dikirim memakai nilai lama,
    // sehingga update sebagian tetap valid.
    $currentData = $current->toArray();

    foreach (['name', 'email', 'phone', 'address'] as $field) {
        if (!array_key_exists($field, $data)) {
            $data[$field] = $currentData[$field];
        }
    }

    $member = $profileService->updateProfile(
        $user->getIdUser(),
        $data
    );

    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diperbarui.',
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
