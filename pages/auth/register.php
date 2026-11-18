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

<div class="auth">

    <div class="auth-card auth-card--wide">

        <span class="auth-brand">
            <?= icon('shelf') ?>
            Digital Library
        </span>

        <h1>Buat akun member</h1>

        <p class="auth-subtitle">
            Setelah terdaftar, kamu bisa mengajukan peminjaman dan membaca
            koleksi langsung dari peramban.
        </p>

        <?php if ($error !== ''): ?>
            <div class="notice notice-error" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="notice notice-success" role="status">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="field">
                <label for="name">Nama lengkap</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="Nama sesuai identitas"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="field">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="nama@kampus.ac.id"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="field">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="field">
                <label for="confirm_password">Konfirmasi password</label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Ulangi password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="field">
                <label for="phone">Nomor telepon</label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars($phone) ?>"
                    placeholder="081234567890"
                    autocomplete="tel"
                >
            </div>

            <div class="field">
                <label for="address">Alamat</label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Alamat tempat tinggal"
                ><?= htmlspecialchars($address) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Daftar
            </button>

        </form>

        <p class="auth-switch">
            Sudah punya akun?
            <a href="<?= app_url('pages/auth/login.php') ?>">Login sekarang</a>
        </p>

    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
