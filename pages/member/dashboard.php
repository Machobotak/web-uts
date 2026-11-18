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
$pageHeading = 'Dashboard member';
$pageSubtitle = 'Aktivitas pinjam dan jalan pintas ke rak.';
$activeNav = 'dashboard';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="stats" aria-label="Ringkasan peminjaman">

    <div class="stat">
        <p class="stat-label">Total peminjaman</p>
        <p class="stat-value"><?= count($transactions) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Menunggu</p>
        <p class="stat-value"><?= $pending ?></p>
        <p class="stat-note">menunggu persetujuan admin</p>
    </div>

    <div class="stat">
        <p class="stat-label">Disetujui</p>
        <p class="stat-value"><?= $approved ?></p>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Menu cepat</h2>
    </div>

    <nav class="quick-list" aria-label="Menu member">

        <a class="quick-link" href="<?= e(app_url('pages/member/borrowings.php')) ?>">
            <?= icon('book') ?>
            <span>
                <span class="quick-title">Cari dan pinjam ebook</span>
                <span class="quick-note">Telusuri rak, pilih judul, ajukan peminjaman.</span>
            </span>
            <?= icon('chevron-right', 'chevron') ?>
        </a>

        <a class="quick-link" href="<?= e(app_url('pages/member/history.php')) ?>">
            <?= icon('clock') ?>
            <span>
                <span class="quick-title">Riwayat peminjaman</span>
                <span class="quick-note">Lihat status pengajuan dan tenggat baca.</span>
            </span>
            <?= icon('chevron-right', 'chevron') ?>
        </a>

        <a class="quick-link" href="<?= e(app_url('pages/member/profile.php')) ?>">
            <?= icon('user') ?>
            <span>
                <span class="quick-title">Profil saya</span>
                <span class="quick-note">Perbarui nama, telepon, dan alamat.</span>
            </span>
            <?= icon('chevron-right', 'chevron') ?>
        </a>

    </nav>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
