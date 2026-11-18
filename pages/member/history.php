<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Support\Shelf;
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
    'pending' => 'Menunggu',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];

$pageTitle = 'Riwayat Peminjaman | Digital Library';
$pageHeading = 'Riwayat peminjaman';
$pageSubtitle = 'Semua pengajuan, beserta sisa masa bacanya.';
$activeNav = 'history';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<section class="panel">

    <div class="panel-head">
        <h2>Semua transaksi</h2>
        <span class="hint"><?= count($transactions) ?> transaksi</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ebook</th>
                    <th>Diajukan</th>
                    <th>Jatuh tempo</th>
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

                    $ebookTitles = array_values(array_filter(array_map(
                        fn($detail) => $detail['ebook_title'] ?? '',
                        $transaction['details']
                    )));

                    $startDate = $transaction['details'][0]['start_date']
                        ?? ($transaction['create_at'] ?? '');

                    $remaining = Shelf::remaining(Shelf::daysLeft($transaction['due_date']));
                    $meter = max(0, min(100, (int) round($remaining / 7 * 100)));
                    ?>
                    <tr>
                        <td class="id">#<?= (int) ($transaction['id_transaction'] ?? 0) ?></td>
                        <td>
                            <?= e($ebookTitles[0] ?? '—') ?>
                            <?php if (count($ebookTitles) > 1): ?>
                                <span class="id">+<?= count($ebookTitles) - 1 ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="number"><?= e(Shelf::date($startDate)) ?></td>
                        <td>
                            <div class="due<?= $remaining === 0 ? ' is-overdue' : ($remaining <= 2 ? ' is-low' : '') ?>">
                                <span class="due-date">
                                    <?= e($transaction['due_date'] !== ''
                                        ? Shelf::date($transaction['due_date'])
                                        : '—') ?>
                                </span>
                                <?php if ($status === 'approved' && $transaction['due_date'] !== ''): ?>
                                    <span class="due-note"><?= e(Shelf::remainingLabel(Shelf::daysLeft($transaction['due_date']))) ?></span>
                                    <span class="meter" aria-hidden="true">
                                        <span style="width: <?= $meter ?>%"></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= e($status) ?>">
                                <?= e($statusLabels[$status] ?? $status) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                            <?php if ($status === 'approved'): ?>
                                <?php $hasAction = false; ?>

                                <?php foreach ($transaction['details'] as $detail): ?>
                                    <?php if (($detail['status'] ?? '') === 'returned') {
                                        continue;
                                    } ?>

                                    <?php $hasAction = true; ?>

                                    <?php if (!empty($detail['is_overdue'])): ?>
                                        <span class="badge badge-overdue">Lewat tenggat</span>
                                    <?php else: ?>
                                        <a
                                            class="btn btn-primary btn-sm"
                                            href="<?= e(app_url('pages/member/reader.php?id=' . (int) ($detail['id_ebook'] ?? 0))) ?>"
                                        >Baca</a>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if (!$hasAction): ?>—<?php endif; ?>
                            <?php else: ?>
                                —
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
