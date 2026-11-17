<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;

$error = '';
$success = '';

$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $authService = new AuthService($storage);

            $authService->register([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'address' => $address
            ]);

            $success = 'Registrasi berhasil. Silakan login dengan akun yang baru dibuat.';

            $name = '';
            $email = '';
            $phone = '';
            $address = '';

        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Registrasi - Digital Library';
$activeNav = 'register';
$useAuthStyles = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="auth">

    <div class="card">

        <h1>Registrasi</h1>

        <p class="subtitle">
            Buat akun untuk menggunakan Digital Library
        </p>

        <?php if ($error !== ''): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <?php if ($success !== ''): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="name">Nama</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="Masukkan nama lengkap"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>

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
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password minimal 8 karakter"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Ulangi password"
                    minlength="8"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone">Nomor Telepon</label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars($phone) ?>"
                    placeholder="Contoh: 081234567890"
                >
            </div>

            <div class="form-group">
                <label for="address">Alamat</label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Masukkan alamat"
                ><?= htmlspecialchars($address) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Daftar
            </button>

        </form>

        <div class="auth-switch">
            Sudah punya akun?
            <a href="<?= app_url('pages/auth/login.php') ?>">
                Login sekarang
            </a>
        </div>

    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
