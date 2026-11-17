<?php
require_once __DIR__ . '/../../config/bootstrap.php';

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Digital Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 35px;
            width: 350px;
            max-width: 85%;
            border-radius: 12px;
        }
        h2, p { text-align: center; }
        label { display: block; margin: 15px 0 7px; }
        input {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #3448a5;
            color: white;
            border: 0;
            cursor: pointer;
        }
        .error { color: red; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Digital Library</h2>
    <p>Silakan masuk ke akun kamu</p>

    <?php if ($error === 'invalid'): ?>
        <p class="error">Email atau password salah.</p>
    <?php elseif ($error === 'empty'): ?>
        <p class="error">Email dan password wajib diisi.</p>
    <?php endif; ?>

    <form action="<?= htmlspecialchars(app_url('handlers/auth/login.php')) ?>" method="POST">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
