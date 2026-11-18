<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk member yang sudah login.
$member = $middleware->requireRole('member');

$memberId = $member->getIdUser();

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$ebooks = $catalogService->getAllEbooks();
$transactions = $transactionService->getTransactionsByUser($memberId);

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

$pageTitle = 'Peminjaman Ebook | Digital Library';
$pageHeading = 'Peminjaman Ebook';
$pageSubtitle = 'Pilih ebook yang ingin kamu pinjam.';
$activeNav = 'borrowings';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="panel">

    <div class="panel-head">
        <h2>Katalog Ebook</h2>
    </div>

    <?php if (empty($ebooks)): ?>

        <p class="empty">Belum ada ebook yang tersedia.</p>

    <?php else: ?>

        <form id="borrowForm">

            <div class="ebook-grid">

                <?php foreach ($ebooks as $ebook): ?>
                    <?php
                    $isAvailable = ($ebook['status'] ?? '') === 'available'
                        && (int) ($ebook['license_limit'] ?? 0) > 0;
                    ?>
                    <div class="ebook-card">

                        <h3><?= e($ebook['title'] ?? 'Tanpa Judul') ?></h3>

                        <p class="ebook-meta">
                            Penulis: <?= e($ebook['author'] ?? '-') ?>
                        </p>

                        <p class="ebook-meta">
                            <?= e($ebook['description'] ?? 'Tidak ada deskripsi.') ?>
                        </p>

                        <p class="ebook-meta">
                            Sisa lisensi: <?= (int) ($ebook['license_limit'] ?? 0) ?>
                        </p>

                        <label class="checkbox">
                            <input
                                type="checkbox"
                                name="ebook_ids[]"
                                value="<?= (int) ($ebook['id_ebook'] ?? 0) ?>"
                                <?= $isAvailable ? '' : 'disabled' ?>
                            >
                            <?= $isAvailable ? 'Pilih ebook' : 'Tidak tersedia' ?>
                        </label>

                    </div>
                <?php endforeach; ?>

            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    Ajukan Peminjaman
                </button>
            </div>

        </form>

    <?php endif; ?>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Pengajuan Peminjaman Saya</h2>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ebook</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="6" class="empty">
                        Kamu belum memiliki pengajuan peminjaman.
                    </td>
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

                    $startDate = $transaction['details'][0]['start_date']
                        ?? ($transaction['create_at'] ?? '-');
                    ?>
                    <tr>
                        <td>#<?= $transactionId ?></td>
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
                            <?php if ($status === 'pending'): ?>
                                <button
                                    type="button"
                                    class="btn btn-outline cancel-transaction"
                                    data-id="<?= $transactionId ?>"
                                >Batalkan</button>
                            <?php elseif ($status === 'approved'): ?>
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

<script>
const messageBox = document.getElementById('message');
const borrowUrl = <?= json_encode(app_url('handlers/transaction/borrow.php')) ?>;
const cancelUrl = <?= json_encode(app_url('handlers/transaction/cancel.php')) ?>;

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

const borrowForm = document.getElementById('borrowForm');

if (borrowForm) {
    borrowForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const checked = this.querySelectorAll('input[name="ebook_ids[]"]:checked');

        if (checked.length === 0) {
            showMessage('Pilih minimal satu ebook terlebih dahulu.', false);
            return;
        }

        const data = new FormData();
        checked.forEach((checkbox, index) => {
            data.append('items[' + index + '][id_ebook]', checkbox.value);
        });

        try {
            const result = await sendRequest(borrowUrl, data);
            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 700);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
}

document.querySelectorAll('.cancel-transaction').forEach(button => {
    button.addEventListener('click', async function () {
        if (!confirm('Batalkan pengajuan peminjaman ini?')) return;

        const data = new FormData();
        data.append('id_transaction', this.dataset.id);

        try {
            const result = await sendRequest(cancelUrl, data);
            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 700);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
