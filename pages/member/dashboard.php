<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk member yang sudah login.
$member = $middleware->requireRole('member');

$transactionService = new TransactionService($storage);
$transactions = $transactionService->getTransactionsByUser($member->getIdUser());

$pending = 0;
$approved = 0;

foreach ($transactions as $transaction) {
    $status = $transaction['status'] ?? '';

    if ($status === 'pending') {
        $pending++;
    }

    if ($status === 'approved') {
        $approved++;
    }
}

$pageTitle = 'Dashboard Member | Digital Library';
$pageHeading = 'Dashboard Member';
$pageSubtitle = 'Kelola aktivitas peminjaman dan temukan ebook favoritmu.';
$activeNav = 'dashboard';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="stats">

    <div class="stat">
        <p class="stat-label">Total Peminjaman</p>
        <p class="stat-value"><?= count($transactions) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Menunggu Persetujuan</p>
        <p class="stat-value"><?= $pending ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Peminjaman Disetujui</p>
        <p class="stat-value"><?= $approved ?></p>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Menu Cepat</h2>
    </div>

    <div class="ebook-grid">

        <a class="ebook-card" href="<?= e(app_url('pages/member/borrowings.php')) ?>">
            <h3>Cari dan Pinjam Ebook</h3>
            <p class="ebook-meta">
                Lihat katalog dan ajukan peminjaman ebook.
            </p>
        </a>

        <a class="ebook-card" href="<?= e(app_url('pages/member/history.php')) ?>">
            <h3>Riwayat Peminjaman</h3>
            <p class="ebook-meta">
                Lihat status dan daftar peminjamanmu.
            </p>
        </a>

        <a class="ebook-card" href="<?= e(app_url('pages/member/profile.php')) ?>">
            <h3>Profil Saya</h3>
            <p class="ebook-meta">
                Lihat dan kelola informasi akun.
            </p>
        </a>

    </div>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
