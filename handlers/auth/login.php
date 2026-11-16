<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

function sendResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');

    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Mendeteksi jenis request.
$isJson = str_contains(
    $_SERVER['CONTENT_TYPE'] ?? '',
    'application/json'
);

// Mengambil data.
if ($isJson) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $data = $_POST;
}

// Hanya menerima POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse([
        'success' => false,
        'message' => 'Method tidak diizinkan.'
    ], 405);
}

try {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($email === '' || $password === '') {
        throw new \InvalidArgumentException(
            'Email dan password wajib diisi.'
        );
    }

    $authService = new AuthService($storage);

    // Memproses login.
    $user = $authService->login($email, $password);

    if ($user === null) {
        if ($isJson) {
            sendResponse([
                'success' => false,
                'message' => 'Email atau password salah, atau akun tidak aktif.'
            ], 401);
        }

        header('Location: ' . app_url('pages/auth/login.php?error=invalid'));
        exit;
    }

    // Menentukan halaman berdasarkan role.
    $dashboard = $user->getRole() === 'admin'
        ? 'pages/admin/dashboard.php'
        : 'pages/member/dashboard.php';

    $response = [
        'success' => true,
        'message' => 'Login berhasil.',
        'data' => [
            'user' => $user->toPublicArray(),
            'redirect' => app_url($dashboard)
        ]
    ];

    if ($isJson) {
        sendResponse($response);
    }

    header('Location: ' . app_url($dashboard));
    exit;

} catch (\InvalidArgumentException $e) {

    if ($isJson) {
        sendResponse([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }

    header('Location: ' . app_url('pages/auth/login.php?error=empty'));
    exit;

} catch (\Throwable $e) {

    error_log($e->getMessage());

    sendResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ], 500);
}
