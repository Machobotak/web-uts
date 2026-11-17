<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $authService = new AuthService($storage);

        // Coba login dan simpan hasil user.
        $user = $authService->login($email, $password);

        // Jika login gagal.
        if ($user === null) {
            $error = 'Email atau password salah, atau akun tidak aktif.';
        } else {
            // Ambil role dari user yang berhasil login.
            $role = $user->getRole();

            // Admin tetap diarahkan ke dashboard admin.
            if ($role === 'admin') {
                header('Location: ' . app_url('pages/admin/dashboard.php'));
            } else {
                // Member diarahkan ke halaman Home.
                header('Location: ' . app_url('pages/public/home.php'));
            }

            exit;
        }

    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}

$homeUrl = app_url('pages/public/home.php');
$catalogUrl = app_url('pages/public/catalog.php');
$loginUrl = app_url('pages/auth/login.php');
$registerUrl = app_url('pages/auth/register.php');

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Digital Library</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        nav {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: #2563eb;
            font-size: 21px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 22px;
        }

        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .container {
            min-height: calc(100vh - 140px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 28px;
            font-size: 14px;
        }

        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
        }

        button {
            width: 100%;
            border: none;
            background: #2563eb;
            color: white;
            padding: 13px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .register {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            color: #6b7280;
        }

        .register a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            nav {
                flex-direction: column;
                gap: 14px;
            }

            .nav-links {
                gap: 14px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .card {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a class="logo" href="<?= htmlspecialchars($homeUrl) ?>">
        Digital Library
    </a>

    <div class="nav-links">
        <a href="<?= htmlspecialchars($homeUrl) ?>">
            Home
        </a>

        <a href="<?= htmlspecialchars($catalogUrl) ?>">
            Katalog
        </a>

        <a href="<?= htmlspecialchars($loginUrl) ?>">
            Login
        </a>

        <a href="<?= htmlspecialchars($registerUrl) ?>">
            Registrasi
        </a>
    </div>
</nav>

<main class="container">

    <div class="card">

        <h1>Login</h1>

        <p class="subtitle">
            Masuk untuk mengakses Digital Library
        </p>

        <?php if ($error !== ''): ?>

            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Masukkan email"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>

            <button type="submit">
                Login
            </button>

        </form>

        <div class="register">

            Belum punya akun?

            <a href="<?= htmlspecialchars($registerUrl) ?>">
                Daftar sekarang
            </a>

        </div>

    </div>

</main>

<footer>
    &copy; 2026 Digital Library. All rights reserved.
</footer>

</body>
</html>