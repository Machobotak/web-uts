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

// Lengkapi transaksi dengan judul ebook dan tanggal jatuh tempo.
foreach ($transactions as $index => $transaction) {
    $details = $transactionService->getTransactionDetailsWithEbook(
        (int) ($transaction['id_transaction'] ?? 0)
    );

    $dueDates = array_filter(
        array_map(fn($detail) => $detail['due_date'] ?? '', $details)
    );

    $transactions[$index]['details'] = $details;
    $transactions[$index]['due_date'] = empty($dueDates) ? '' : max($dueDates);
}

$statusLabels = [
    'pending' => 'Menunggu Persetujuan',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];

$pageTitle = 'Riwayat Peminjaman | Digital Library';
$pageHeading = 'Riwayat Peminjaman';
$pageSubtitle = 'Daftar seluruh transaksi peminjaman ebook kamu.';
$activeNav = 'history';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="panel">

    <div class="panel-head">
        <h2>Semua Transaksi (<?= count($transactions) ?>)</h2>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Judul Ebook</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="6" class="empty">Belum ada riwayat peminjaman.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                    $status = $transaction['status'] ?? 'pending';

                    $ebookTitles = array_filter(array_map(
                        fn($detail) => $detail['ebook_title'] ?? '',
                        $transaction['details']
                    ));

                    $startDate = $transaction['details'][0]['start_date']
                        ?? ($transaction['create_at'] ?? '-');
                    ?>
                    <tr>
                        <td>#<?= (int) ($transaction['id_transaction'] ?? 0) ?></td>
                        <td><?= e(implode(', ', $ebookTitles) ?: '-') ?></td>
                        <td><?= e($startDate) ?></td>
                        <td><?= e($transaction['due_date'] !== '' ? $transaction['due_date'] : '-') ?></td>
                        <td>
                            <span class="badge badge-<?= e($status) ?>">
                                <?= e($statusLabels[$status] ?? $status) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                            <?php if ($status === 'approved'): ?>
                                <?php foreach ($transaction['details'] as $detail): ?>
                                    <?php if (($detail['status'] ?? '') !== 'returned'): ?>
                                        <a
                                            class="btn btn-primary"
                                            href="<?= e(app_url('pages/member/reader.php?id=' . (int) ($detail['id_ebook'] ?? 0))) ?>"
                                        >Baca <?= e($detail['ebook_title'] ?: 'Ebook') ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
