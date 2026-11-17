<?php

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Services\ProfileService;

require_once __DIR__ . '/../../config/bootstrap.php';

$message = '';
$messageType = 'success';

try {
    $auth = new AuthService($storage);
    $middleware = new AuthMiddleware($auth);
    $loggedInMember = $middleware->requireRole('member');

    $memberId = (int) ($_SESSION['user_id'] ?? 0);

    $profileService = new ProfileService($storage);
    $profile = $profileService->getProfile($memberId);

    if (!$profile) {
        throw new RuntimeException('Profil member tidak ditemukan.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'update_profile') {
                $profile = $profileService->updateProfile($memberId, [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? ''
                ]);

                $_SESSION['user_name'] = $profile->getName();

                $message = 'Profil berhasil diperbarui.';
            } elseif ($action === 'change_password') {
                $profileService->changePassword(
                    $memberId,
                    $_POST['old_password'] ?? '',
                    $_POST['new_password'] ?? ''
                );

                $message = 'Password berhasil diperbarui.';
            } else {
                throw new InvalidArgumentException('Permintaan tidak valid.');
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }

        $profile = $profileService->getProfile($memberId);
    }

    $profileData = $profile->toArray();

} catch (Throwable $e) {
    http_response_code(403);
    exit('Tidak dapat mengakses profil. Silakan login kembali sebagai member.');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Digital Library</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: #f4f6fb;
            font-family: Arial, sans-serif;
            color: #263047;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 8px;
            color: #263b65;
        }

        .header p {
            color: #718096;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 13px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px #0000000a;
        }

        .panel h2 {
            margin-top: 0;
            color: #263b65;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d8deea;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #496bb0;
        }

        button {
            border: none;
            background: #263b65;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #354f83;
        }

        .message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #e5f7ec;
            color: #237344;
        }

        .error {
            background: #fdeaea;
            color: #a52d2d;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #263b65;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .panel {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <a class="back" href="dashboard.php">← Kembali ke Dashboard</a>

    <div class="header">
        <h1>Profil Saya</h1>
        <p>Kelola informasi akun dan keamanan password kamu.</p>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= e($messageType) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <h2>Informasi Profil</h2>

        <form method="POST">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input
                    type="text"
                    name="name"
                    value="<?= e($profileData['name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?= e($profileData['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Nomor HP</label>
                <input
                    type="text"
                    name="phone"
                    value="<?= e($profileData['phone'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address"><?= e($profileData['address'] ?? '') ?></textarea>
            </div>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </section>

    <section class="panel">
        <h2>Ganti Password</h2>

        <form method="POST">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label>Password Lama</label>
                <input
                    type="password"
                    name="old_password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="form-group">
                <label>Password Baru</label>
                <input
                    type="password"
                    name="new_password"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
                <small>Minimal 8 karakter.</small>
            </div>

            <button type="submit">Ganti Password</button>
        </form>
    </section>

</div>
</body>
</html>