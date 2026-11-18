<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$users = $authService->getAllUsers();
$ebooks = $catalogService->getAllEbooks();
$categories = $catalogService->getAllCategories();
$transactions = $transactionService->getAllTransactions();

// Statistik ringkas.
$members = array_filter(
    $users,
    fn($user) => ($user['role'] ?? '') === 'member'
);

$activeMembers = array_filter(
    $members,
    fn($member) => ($member['status'] ?? '') === 'active'
);

$pendingTransactions = array_filter(
    $transactions,
    fn($transaction) => ($transaction['status'] ?? '') === 'pending'
);

// Lima member terbaru untuk tabel ringkasan.
$latestMembers = array_slice(array_reverse($members), 0, 5);

$pageTitle = 'Dashboard Admin | Digital Library';
$pageHeading = 'Dashboard Admin';
$pageSubtitle = 'Kelola sistem perpustakaan digital melalui dashboard ini.';
$activeNav = 'dashboard';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="stats">

    <div class="stat">
        <p class="stat-label">Total Member</p>
        <p class="stat-value"><?= count($members) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Member Aktif</p>
        <p class="stat-value"><?= count($activeMembers) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Total Ebook</p>
        <p class="stat-value"><?= count($ebooks) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Total Kategori</p>
        <p class="stat-value"><?= count($categories) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Total Transaksi</p>
        <p class="stat-value"><?= count($transactions) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Menunggu Persetujuan</p>
        <p class="stat-value"><?= count($pendingTransactions) ?></p>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Data Member Terbaru</h2>

        <a
            class="btn btn-outline"
            href="<?= e(app_url('pages/admin/members.php')) ?>"
        >Kelola Member</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($latestMembers)): ?>
                <tr>
                    <td colspan="4" class="empty">Belum ada data member.</td>
                </tr>
            <?php else: ?>
                <?php foreach (array_values($latestMembers) as $index => $member): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($member['name'] ?? '-') ?></td>
                        <td><?= e($member['email'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= ($member['status'] ?? '') === 'active'
                                ? 'badge-approved'
                                : 'badge-rejected' ?>">
                                <?= e($member['status'] ?? '-') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
