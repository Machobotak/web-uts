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

$pageTitle = 'Login - Digital Library';
$activeNav = 'login';
$useAuthStyles = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="auth">

    <div class="card card-narrow">

        <h1>Login</h1>

        <p class="subtitle">
            Masuk untuk mengakses Digital Library
        </p>

        <?php if ($error !== ''): ?>

            <div class="alert alert-error">
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

            <button type="submit" class="btn btn-primary">
                Login
            </button>

        </form>

        <div class="auth-switch">

            Belum punya akun?

            <a href="<?= app_url('pages/auth/register.php') ?>">
                Daftar sekarang
            </a>

        </div>

    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
