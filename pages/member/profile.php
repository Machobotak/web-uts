<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\ProfileService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk member yang sudah login.
$member = $middleware->requireRole('member');

$memberId = $member->getIdUser();

$profileService = new ProfileService($storage);
$profile = $profileService->getProfile($memberId);

if ($profile === null) {
    http_response_code(404);
    exit('Profil member tidak ditemukan.');
}

$message = '';
$messageType = 'success';

// Form profil dan ganti password diproses di halaman ini.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_profile') {
            $profile = $profileService->updateProfile($memberId, [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
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
            throw new \InvalidArgumentException('Permintaan tidak valid.');
        }
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }

    $profile = $profileService->getProfile($memberId);
}

$pageTitle = 'Profil Saya | Digital Library';
$pageHeading = 'Profil saya';
$pageSubtitle = 'Perbarui data akun dan password.';
$activeNav = 'profile';
$userName = $profile->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<?php if ($message !== ''): ?>
    <div class="notice <?= $messageType === 'success' ? 'notice-success' : 'notice-error' ?>">
        <?= e($message) ?>
    </div>
<?php endif; ?>

<section class="panel">

    <div class="panel-head">
        <h2>Informasi profil</h2>
        <span class="badge badge-active">Member #<?= $memberId ?></span>
    </div>

    <form method="POST">

        <input type="hidden" name="action" value="update_profile">

        <div class="form-grid">

            <div class="field">
                <label for="profile-name">Nama lengkap</label>

                <input
                    type="text"
                    id="profile-name"
                    name="name"
                    value="<?= e($profile->getName()) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="profile-email">Email</label>

                <input
                    type="email"
                    id="profile-email"
                    name="email"
                    value="<?= e($profile->getEmail()) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="profile-phone">Nomor HP</label>

                <input
                    type="text"
                    id="profile-phone"
                    name="phone"
                    value="<?= e($profile->toPublicArray()['phone'] ?? '') ?>"
                >
            </div>

        </div>

        <div class="field">
            <label for="profile-address">Alamat</label>

            <textarea
                id="profile-address"
                name="address"
            ><?= e($profile->toPublicArray()['address'] ?? '') ?></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">
                Simpan perubahan
            </button>
        </div>

    </form>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Ganti password</h2>
        <span class="hint">Minimal 8 karakter</span>
    </div>

    <form method="POST">

        <input type="hidden" name="action" value="change_password">

        <div class="form-grid">

            <div class="field">
                <label for="old-password">Password lama</label>

                <input
                    type="password"
                    id="old-password"
                    name="old_password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="field">
                <label for="new-password">Password baru</label>

                <input
                    type="password"
                    id="new-password"
                    name="new_password"
                    minlength="8"
                    required
                    autocomplete="new-password"
                >
            </div>

        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">
                Ganti password
            </button>
        </div>

    </form>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
