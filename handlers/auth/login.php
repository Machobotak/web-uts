
<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

// Handler hanya menerima POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('pages/auth/login.php'));
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ' . app_url('pages/auth/login.php?error=empty'));
    exit;
}

try {
    $auth = new AuthService($storage);

    $user = $auth->login($email, $password);

    if ($user === null) {
        header('Location: ' . app_url('pages/auth/login.php?error=invalid'));
        exit;
    }

    if ($user->getRole() === 'admin') {
        header('Location: ' . app_url('pages/admin/dashboard.php'));
        exit;
    }

    header('Location: ' . app_url('pages/member/dashboard.php'));
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'Terjadi kesalahan pada server: ' . htmlspecialchars($e->getMessage());
}
