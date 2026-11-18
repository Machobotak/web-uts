<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$transactionService = new TransactionService($storage);

// Peta id_user => data pengguna untuk kolom nama member.
$users = [];
foreach ($authService->getAllUsers() as $user) {
    $users[(int) ($user['id_user'] ?? 0)] = $user;
}

// Lengkapi transaksi dengan member dan detail ebook-nya.
$transactions = $transactionService->getAllTransactions();

foreach ($transactions as $index => $transaction) {
    $details = $transactionService->getTransactionDetailsWithEbook(
        (int) ($transaction['id_transaction'] ?? 0)
    );

    $dueDates = array_filter(
        array_map(fn($detail) => $detail['due_date'] ?? '', $details)
    );

    $transactions[$index]['member'] = $users[(int) ($transaction['id_user'] ?? 0)] ?? null;
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

$pending = count(array_filter(
    $transactions,
    fn($transaction) => ($transaction['status'] ?? '') === 'pending'
));

$approved = count(array_filter(
    $transactions,
    fn($transaction) => ($transaction['status'] ?? '') === 'approved'
));

$rejected = count(array_filter(
    $transactions,
    fn($transaction) => ($transaction['status'] ?? '') === 'rejected'
));

$pageTitle = 'Manajemen Transaksi | Digital Library';
$pageHeading = 'Manajemen Transaksi';
$pageSubtitle = 'Kelola pengajuan peminjaman ebook anggota.';
$activeNav = 'transactions';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="stats">

    <div class="stat">
        <p class="stat-label">Total Transaksi</p>
        <p class="stat-value"><?= count($transactions) ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Menunggu Persetujuan</p>
        <p class="stat-value"><?= $pending ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Disetujui</p>
        <p class="stat-value"><?= $approved ?></p>
    </div>

    <div class="stat">
        <p class="stat-label">Ditolak</p>
        <p class="stat-value"><?= $rejected ?></p>
    </div>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Daftar Transaksi Peminjaman</h2>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Member</th>
                    <th>Ebook</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada transaksi peminjaman.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                    $transactionId = (int) ($transaction['id_transaction'] ?? 0);
                    $status = $transaction['status'] ?? 'pending';

                    $ebookTitles = array_filter(array_map(
                        fn($detail) => $detail['ebook_title'] ?? '',
                        $transaction['details']
                    ));

                    $memberName = $transaction['member']['name']
                        ?? 'Member tidak ditemukan';

                    $startDate = $transaction['details'][0]['start_date']
                        ?? ($transaction['create_at'] ?? '-');
                    ?>
                    <tr>
                        <td>#<?= $transactionId ?></td>
                        <td><?= e($memberName) ?></td>
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
                                <a
                                    class="btn btn-secondary"
                                    href="<?= e(app_url('pages/admin/transaction-detail.php?id=' . $transactionId)) ?>"
                                >Detail</a>

                                <?php if ($status === 'pending'): ?>
                                    <button
                                        type="button"
                                        class="btn btn-primary approve-transaction"
                                        data-id="<?= $transactionId ?>"
                                    >Setujui</button>

                                    <button
                                        type="button"
                                        class="btn btn-outline reject-transaction"
                                        data-id="<?= $transactionId ?>"
                                    >Tolak</button>
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

<script>
const messageBox = document.getElementById('message');
const approveUrl = <?= json_encode(app_url('handlers/transaction/approve.php')) ?>;
const rejectUrl = <?= json_encode(app_url('handlers/transaction/reject.php')) ?>;

function showMessage(text, success) {
    messageBox.textContent = text;
    messageBox.className = 'notice ' + (success ? 'notice-success' : 'notice-error');
    messageBox.hidden = false;
}

async function sendRequest(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        body: data
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Permintaan gagal.');
    }

    return result;
}

document.querySelectorAll('.approve-transaction').forEach(button => {
    button.addEventListener('click', async function () {
        if (!confirm('Setujui pengajuan peminjaman ini?')) return;

        const data = new FormData();
        data.append('id_transaction', this.dataset.id);

        try {
            const result = await sendRequest(approveUrl, data);
            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});

document.querySelectorAll('.reject-transaction').forEach(button => {
    button.addEventListener('click', async function () {
        const notes = prompt('Alasan penolakan (opsional):');
        if (notes === null) return;

        const data = new FormData();
        data.append('id_transaction', this.dataset.id);
        data.append('notes', notes);

        try {
            const result = await sendRequest(rejectUrl, data);
            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
