<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Support\Shelf;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$transactionId = (int) ($_GET['id'] ?? 0);

$transactionService = new TransactionService($storage);
$transaction = $transactionService->findTransactionById($transactionId);

if ($transaction === null) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}

$details = $transactionService->getTransactionDetailsWithEbook($transactionId);

// Data member pemilik transaksi.
$member = $authService->findById((int) ($transaction['id_user'] ?? 0));

// Admin yang memproses transaksi (bila sudah diproses).
$processor = $transaction['approved_by']
    ? $authService->findById((int) $transaction['approved_by'])
    : null;

$statusLabels = [
    'pending' => 'Menunggu',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];

$detailStatusLabels = [
    'active' => 'Dipinjam',
    'returned' => 'Dikembalikan',
    'overdue' => 'Terlambat',
    'cancelled' => 'Dibatalkan',
];

$status = $transaction['status'] ?? 'pending';

$startDate = $details[0]['start_date'] ?? ($transaction['create_at'] ?? '');
$dueDate = '';

foreach ($details as $detail) {
    $candidate = $detail['due_date'] ?? '';

    if ($candidate !== '' && ($dueDate === '' || $candidate > $dueDate)) {
        $dueDate = $candidate;
    }
}

$pageTitle = 'Transaksi #' . $transactionId . ' | Digital Library';
$pageHeading = 'Transaksi #' . $transactionId;
$pageSubtitle = 'Rincian pengajuan, tenggat, dan status tiap ebook.';
$activeNav = 'transactions';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="panel">

    <div class="panel-head">
        <h2>Ringkasan</h2>

        <a class="btn btn-secondary btn-sm" href="<?= e(app_url('pages/admin/transactions.php')) ?>">
            <?= icon('arrow-left') ?>
            Kembali ke transaksi
        </a>
    </div>

    <div class="detail-grid">

        <div class="detail-item">
            <span>Member</span>
            <strong><?= e($member['name'] ?? 'Member tidak ditemukan') ?></strong>
        </div>

        <div class="detail-item">
            <span>Email</span>
            <strong><?= e($member['email'] ?? '—') ?></strong>
        </div>

        <div class="detail-item">
            <span>Status</span>
            <strong>
                <span class="badge badge-<?= e($status) ?>">
                    <?= e($statusLabels[$status] ?? $status) ?>
                </span>
            </strong>
        </div>

        <div class="detail-item">
            <span>Tanggal pinjam</span>
            <strong><?= e(Shelf::date($startDate)) ?></strong>
        </div>

        <div class="detail-item">
            <span>Jatuh tempo</span>
            <strong><?= e($dueDate !== '' ? Shelf::date($dueDate) : '—') ?></strong>
        </div>

        <div class="detail-item">
            <span>Diproses oleh</span>
            <strong><?= e($processor['name'] ?? '—') ?></strong>
        </div>

        <div class="detail-item">
            <span>Waktu proses</span>
            <strong><?= e(
                ($transaction['processed_at'] ?? '') !== ''
                    ? Shelf::date($transaction['processed_at'])
                    : '—'
            ) ?></strong>
        </div>

        <div class="detail-item">
            <span>Catatan</span>
            <strong><?= e(($transaction['notes'] ?? '') !== '' ? $transaction['notes'] : '—') ?></strong>
        </div>

    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Ebook dalam pengajuan</h2>
        <span class="hint"><?= count($details) ?> judul</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="number">No</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Jatuh tempo</th>
                    <th>Status</th>
                    <th>Dikembalikan</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($details)): ?>
                <tr>
                    <td colspan="6" class="empty">Tidak ada detail ebook.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($details as $index => $detail): ?>
                    <?php $detailStatus = $detail['status'] ?? 'active'; ?>
                    <tr>
                        <td class="number"><?= $index + 1 ?></td>
                        <td><?= e($detail['ebook_title'] ?? 'Ebook') ?></td>
                        <td><?= e($detail['ebook_author'] ?? '—') ?></td>
                        <td class="number"><?= e(Shelf::date($detail['due_date'] ?? null)) ?></td>
                        <td>
                            <span class="badge badge-<?= e($detailStatus) ?>">
                                <?= e($detailStatusLabels[$detailStatus] ?? $detailStatus) ?>
                            </span>
                        </td>
                        <td class="number">
                            <?= e(
                                ($detail['returned_at'] ?? '') !== ''
                                    ? Shelf::date($detail['returned_at'])
                                    : '—'
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
