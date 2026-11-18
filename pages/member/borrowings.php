<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Support\Shelf;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk member yang sudah login.
$member = $middleware->requireRole('member');

$memberId = $member->getIdUser();

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$keyword = trim($_GET['search'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$pilih = (int) ($_GET['pilih'] ?? 0);

$ebooks = $catalogService->searchEbooks([
    'q' => $keyword,
    'category' => $category
]);

$categories = $catalogService->getAllCategories();
$isFiltered = $keyword !== '' || $category > 0;

$categoryNames = $catalogService->getCategoryNameMap();

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
    'pending' => 'Menunggu',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];

$pageTitle = 'Peminjaman Ebook | Digital Library';
$pageHeading = 'Peminjaman ebook';
$pageSubtitle = 'Tandai judul di rak, lalu ajukan sekaligus.';
$activeNav = 'borrowings';
$userName = $member->getName();
$userRole = 'member';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="panel" id="rak">

    <div class="panel-head">
        <h2>Rak ebook</h2>
        <span class="hint">
            <?= count($ebooks) ?> judul<?= $isFiltered ? ' cocok dengan filter' : ' di rak' ?>
        </span>
    </div>

    <form method="GET" class="filter-bar" action="<?= e(app_url('pages/member/borrowings.php')) ?>#rak">

        <div class="filter-field">
            <label for="search">Kata kunci</label>
            <input
                type="search"
                id="search"
                name="search"
                class="search-input"
                placeholder="Judul, penulis, penerbit, atau ISBN"
                value="<?= e($keyword) ?>"
            >
        </div>

        <div class="filter-field">
            <label for="category">Kategori</label>
            <select name="category" id="category" class="category-select">

                <option value="">Semua kategori</option>

                <?php foreach ($categories as $item): ?>

                    <?php $categoryId = (int) ($item['id_category'] ?? 0); ?>

                    <option
                        value="<?= $categoryId ?>"
                        <?= $category === $categoryId ? 'selected' : '' ?>
                    ><?= e($item['name'] ?? '') ?></option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="filter-actions">

            <button type="submit" class="btn btn-primary">
                <?= icon('search') ?>
                Cari
            </button>

            <?php if ($isFiltered): ?>
                <a href="<?= e(app_url('pages/member/borrowings.php')) ?>#rak" class="btn btn-quiet">
                    Reset
                </a>
            <?php endif; ?>

        </div>

    </form>

    <?php if (empty($ebooks)): ?>

        <div class="shelf-empty">
            <?= icon('shelf') ?>

            <?php if ($isFiltered): ?>
                <h2>Tidak ada judul yang cocok</h2>
                <p>Coba kata kunci lain atau pilih kategori yang berbeda.</p>
                <p>
                    <a href="<?= e(app_url('pages/member/borrowings.php')) ?>#rak" class="btn btn-secondary">
                        Reset filter
                    </a>
                </p>
            <?php else: ?>
                <h2>Rak masih kosong</h2>
                <p>Belum ada ebook yang bisa dipinjam saat ini.</p>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <form id="borrowForm">

            <div class="shelf-grid">
                <?php foreach ($ebooks as $ebook): ?>
                    <?php
                    $isAvailable = ($ebook['status'] ?? '') === 'available'
                        && (int) ($ebook['license_limit'] ?? 0) > 0;

                    $coverFile = basename((string) ($ebook['cover'] ?? ''));
                    $hasCover = $coverFile !== ''
                        && is_file(__DIR__ . '/../../public/uploads/covers/' . $coverFile);

                    $title = (string) ($ebook['title'] ?? 'Tanpa Judul');
                    $initial = preg_match('/\X/u', trim($title), $match) === 1
                        ? strtoupper($match[0])
                        : '';
                    $category = $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';
                    $ebookId = (int) ($ebook['id_ebook'] ?? 0);
                    $isPicked = $pilih === $ebookId && $isAvailable;
                    ?>
                    <label class="book-card<?= $isAvailable ? '' : ' is-unavailable' ?>">

                        <span class="book-cover">

                            <?php if ($hasCover): ?>

                                <img
                                    src="<?= e(app_url('public/uploads/covers/' . rawurlencode($coverFile))) ?>"
                                    alt="Cover <?= e($title) ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <span class="book-initial"><?= e($initial) ?></span>

                            <?php endif; ?>

                            <span class="book-state">
                                <?= $isAvailable ? 'Tersedia' : 'Habis' ?>
                            </span>

                        </span>

                        <span class="book-body">

                            <span class="book-label">
                                <span class="book-cat band-<?= Shelf::band($category) ?>">
                                    <?= e($category !== '' ? $category : 'Lainnya') ?>
                                </span>
                                <span class="call-code"><?= e(Shelf::code($category, $ebookId)) ?></span>
                            </span>

                            <span class="book-title"><?= e($title) ?></span>

                            <span class="book-author"><?= e($ebook['author'] ?? '—') ?></span>

                            <span class="book-desc">
                                <?= e($ebook['description'] ?? 'Tidak ada deskripsi.') ?>
                            </span>

                            <span class="book-foot">

                                <span class="license">
                                    <?= (int) ($ebook['license_limit'] ?? 0) ?>/<?= (int) ($ebook['license_total'] ?? 0) ?>
                                    <small>salinan</small>
                                </span>

                                <input
                                    type="checkbox"
                                    class="book-check"
                                    name="ebook_ids[]"
                                    value="<?= $ebookId ?>"
                                    aria-label="Pilih <?= e($title) ?>"
                                    <?= $isAvailable ? '' : 'disabled' ?>
                                    <?= $isPicked ? 'checked' : '' ?>
                                >

                            </span>

                        </span>

                    </label>
                <?php endforeach; ?>

            </div>

            <div class="borrow-bar">
                <p class="borrow-count" id="borrowCount" aria-live="polite">
                    Belum ada judul dipilih
                </p>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="borrowSubmit"
                    disabled
                >Ajukan peminjaman</button>
            </div>

        </form>

    <?php endif; ?>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Pengajuan saya</h2>
        <span class="hint"><?= count($transactions) ?> pengajuan</span>
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
                    <td colspan="6" class="empty">
                        Kamu belum memiliki pengajuan peminjaman.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                    $transactionId = (int) ($transaction['id_transaction'] ?? 0);
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
                        <td class="id">#<?= $transactionId ?></td>
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
                            <?php if ($status === 'pending'): ?>
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm cancel-transaction"
                                    data-id="<?= $transactionId ?>"
                                >Batalkan</button>
                            <?php elseif ($status === 'approved'): ?>
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

<script>
const messageBox = document.getElementById('message');
const borrowUrl = <?= json_encode(app_url('handlers/transaction/borrow.php')) ?>;
const cancelUrl = <?= json_encode(app_url('handlers/transaction/cancel.php')) ?>;

function showMessage(text, success) {
    messageBox.textContent = text;
    messageBox.className = 'notice ' + (success ? 'notice-success' : 'notice-error');
    messageBox.hidden = false;

    if (success && window.flashMessage) {
        window.flashMessage(text, true);
    }
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
    const countNote = document.getElementById('borrowCount');
    const submitButton = document.getElementById('borrowSubmit');
    const checkboxes = borrowForm.querySelectorAll('input[name="ebook_ids[]"]');

    function syncSelection() {
        const selected = borrowForm.querySelectorAll('input[name="ebook_ids[]"]:checked').length;

        countNote.textContent = selected === 0
            ? 'Belum ada judul dipilih'
            : selected + ' judul dipilih';
        submitButton.disabled = selected === 0;
    }

    checkboxes.forEach(checkbox => checkbox.addEventListener('change', syncSelection));
    syncSelection();
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
        if (!window.armAction(this, 'Klik lagi untuk membatalkan')) return;

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
