<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Support\Shelf;
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

// Lima pengajuan terlama yang masih menunggu keputusan (yang paling lama dulu).
$queue = array_slice(array_reverse(array_values($pendingTransactions)), 0, 5);

$userNames = [];
foreach ($users as $user) {
    $userNames[(int) ($user['id_user'] ?? 0)] = $user['name'] ?? 'Pengguna';
}

$queueRows = [];
foreach ($queue as $transaction) {
    $details = $transactionService->getTransactionDetailsWithEbook(
        (int) ($transaction['id_transaction'] ?? 0)
    );

    $titles = array_values(array_filter(array_map(
        fn($detail) => $detail['ebook_title'] ?? '',
        $details
    )));

    $queueRows[] = [
        'id' => (int) ($transaction['id_transaction'] ?? 0),
        'member' => $userNames[(int) ($transaction['id_user'] ?? 0)] ?? 'Pengguna tidak ditemukan',
        'title' => $titles[0] ?? '—',
        'extra' => max(0, count($titles) - 1),
        'created' => $transaction['create_at'] ?? '',
    ];
}

// Lima member terbaru untuk tabel ringkasan.
$latestMembers = array_slice(array_reverse($members), 0, 5);

$pageTitle = 'Dashboard Admin | Digital Library';
$pageHeading = 'Dashboard admin';
$pageSubtitle = 'Ringkasan koleksi, member, dan antrean persetujuan.';
$activeNav = 'dashboard';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="stats" aria-label="Ringkasan sistem">

    <div class="stat">
        <p class="stat-label">Total member</p>
        <p class="stat-value"><?= count($members) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Member aktif</p>
        <p class="stat-value"><?= count($activeMembers) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Total ebook</p>
        <p class="stat-value"><?= count($ebooks) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Kategori</p>
        <p class="stat-value"><?= count($categories) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Transaksi</p>
        <p class="stat-value"><?= count($transactions) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Menunggu</p>
        <p class="stat-value"><?= count($pendingTransactions) ?></p>
        <p class="stat-note">perlu ditinjau</p>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Antrean persetujuan</h2>
        <span class="hint">
            <?= count($pendingTransactions) ?> pengajuan menunggu
        </span>
        <a class="btn btn-primary btn-sm" href="<?= e(app_url('pages/admin/transactions.php')) ?>">
            Kelola transaksi
            <?= icon('arrow-right') ?>
        </a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Member</th>
                    <th>Ebook</th>
                    <th>Diajukan</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($queueRows)): ?>
                <tr>
                    <td colspan="4" class="empty">
                        Tidak ada pengajuan yang menunggu keputusan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($queueRows as $row): ?>
                    <tr>
                        <td class="id">#<?= $row['id'] ?></td>
                        <td><?= e($row['member']) ?></td>
                        <td>
                            <?= e($row['title']) ?>
                            <?php if ($row['extra'] > 0): ?>
                                <span class="id">+<?= $row['extra'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="number"><?= e(Shelf::date($row['created'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Member terbaru</h2>

        <a class="btn btn-secondary btn-sm" href="<?= e(app_url('pages/admin/members.php')) ?>">
            Kelola member
            <?= icon('arrow-right') ?>
        </a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="number">No</th>
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
                    <?php $isActive = ($member['status'] ?? '') === 'active'; ?>
                    <tr>
                        <td class="number"><?= $index + 1 ?></td>
                        <td><?= e($member['name'] ?? '-') ?></td>
                        <td><?= e($member['email'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $isActive ? 'badge-approved' : 'badge-rejected' ?>">
                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
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
