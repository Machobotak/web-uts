<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

// Mengatur response berdasarkan jenis request.
function sendResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');

    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Mendeteksi request JSON atau Form POST.
$isJson = str_contains(
    $_SERVER['CONTENT_TYPE'] ?? '',
    'application/json'
);

// Mengambil data dari request.
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
    $authService = new AuthService($storage);

    // Registrasi selalu membuat akun Member.
    $member = $authService->register($data);

    $response = [
        'success' => true,
        'message' => 'Registrasi berhasil.',
        'data' => $member->toPublicArray()
    ];

    if ($isJson) {
        sendResponse($response, 201);
    }

    // Redirect jika menggunakan HTML Form.
    header('Location: ' . app_url('pages/auth/login.php?registered=1'));
    exit;

} catch (\InvalidArgumentException $e) {

    if ($isJson) {
        sendResponse([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }

    header(
        'Location: ' . app_url(
            'pages/auth/register.php?error=' . urlencode($e->getMessage())
        )
    );
    exit;

} catch (\Throwable $e) {

    error_log($e->getMessage());

    sendResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ], 500);
}
