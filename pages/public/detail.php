<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Support\Shelf;

$catalogService = new CatalogService($storage);
$transactionService = new TransactionService($storage);

$currentUser = (new AuthMiddleware(new AuthService($storage)))->getCurrentUser();
$member = $currentUser !== null && $currentUser->getRole() === 'member'
    ? $currentUser
    : null;
$isAdmin = $currentUser !== null && $currentUser->getRole() === 'admin';

$id = (int) ($_GET['id'] ?? 0);

$ebook = $id > 0
    ? $catalogService->findEbookById($id)
    : null;

$categoryName = '';

if ($ebook !== null) {
    $categoryNames = $catalogService->getCategoryNameMap();
    $categoryName =
        $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';
}

$pageTitle = $ebook
    ? ($ebook['title'] ?? 'Detail E-Book') . ' - Digital Library'
    : 'E-Book Tidak Ditemukan - Digital Library';
$activeNav = 'catalog';
$pageCss = 'detail.css';

require __DIR__ . '/../partials/header.php';

?>

<div class="container detail">

    <a href="<?= app_url('pages/public/catalog.php') ?>" class="back-link">
        <?= icon('arrow-left') ?>
        Kembali ke katalog
    </a>

    <?php if ($ebook === null): ?>

        <div class="shelf-empty">

            <?= icon('shelf') ?>

            <h1>E-book tidak ditemukan</h1>
            <p>Data yang kamu cari belum tersedia atau sudah dihapus.</p>

            <p>
                <a href="<?= app_url('pages/public/catalog.php') ?>" class="btn btn-primary">
                    Buka katalog
                </a>
            </p>

        </div>

    <?php else: ?>

        <?php
        $ebookId = (int) ($ebook['id_ebook'] ?? 0);
        $title = $ebook['title'] ?? 'Tanpa Judul';
        $cover = $ebook['cover'] ?? '';
        $licenseLimit = (int) ($ebook['license_limit'] ?? 0);
        $licenseTotal = (int) ($ebook['license_total'] ?? $licenseLimit);

        $specs = [
            'Kategori' => $categoryName,
            'Penerbit' => trim((string) ($ebook['publisher'] ?? '')),
            'Tahun terbit' => trim((string) ($ebook['publication_year'] ?? '')),
            'ISBN' => trim((string) ($ebook['isbn'] ?? '')),
            'Lisensi' => $licenseLimit . '/' . $licenseTotal,
            'Nomor panggil' => Shelf::code($categoryName, $ebookId),
        ];

        $specs = array_filter($specs, fn ($value) => $value !== '' && $value !== '0/0');

        $hasFile = $catalogService->resolveEbookFile($ebook) !== null;

        // Status akses menentukan satu aksi utama yang benar-benar bisa dipakai.
        $loan = $member !== null
            ? $transactionService->findHeldLoan($member->getIdUser(), $ebookId)
            : null;

        $readerUrl = app_url('pages/member/reader.php?id=' . $ebookId);
        $borrowUrl = app_url('pages/member/borrowings.php?pilih=' . $ebookId);

        if ($member !== null && $loan !== null && !$loan['overdue'] && $hasFile) {
            $primary = [
                'label' => 'Baca e-book',
                'url' => $readerUrl,
                'icon' => 'book',
            ];
            $secondary = [
                'label' => 'Lihat peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
            ];
            $statusNote = 'Sedang kamu pinjam sampai '
                . Shelf::date($loan['detail']['due_date'] ?? '') . '.';
        } elseif ($member !== null && $loan !== null && !$loan['overdue']) {
            $primary = [
                'label' => 'Berkas belum tersedia',
                'url' => null,
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Lihat peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
            ];
            $statusNote = 'Pinjaman aktif sampai '
                . Shelf::date($loan['detail']['due_date'] ?? '')
                . ', tetapi berkasnya belum diunggah.';
        } elseif ($member !== null && $loan !== null) {
            $primary = [
                'label' => 'Ajukan pinjaman baru',
                'url' => $borrowUrl,
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Lihat peminjaman',
                'url' => app_url('pages/member/borrowings.php'),
            ];
            $statusNote = 'Batas baca sebelumnya sudah lewat pada '
                . Shelf::date($loan['detail']['due_date'] ?? '') . '.';
        } elseif ($member !== null && !$hasFile) {
            $primary = [
                'label' => 'Berkas belum tersedia',
                'url' => null,
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Buka katalog',
                'url' => app_url('pages/public/catalog.php'),
            ];
            $statusNote = 'Berkas ebook ini belum diunggah. Hubungi admin perpustakaan.';
        } elseif ($member !== null && $licenseLimit > 0) {
            $primary = [
                'label' => 'Ajukan peminjaman',
                'url' => $borrowUrl,
                'icon' => 'plus',
            ];
            $secondary = [
                'label' => 'Lihat peminjaman saya',
                'url' => app_url('pages/member/borrowings.php'),
            ];
            $statusNote = 'Lisensi tersisa ' . $licenseLimit . ' dari '
                . $licenseTotal . '. Pinjaman berjalan '
                . TransactionService::DEFAULT_LOAN_DAYS . ' hari.';
        } elseif ($member !== null) {
            $primary = [
                'label' => 'Stok lisensi habis',
                'url' => null,
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Buka katalog',
                'url' => app_url('pages/public/catalog.php'),
            ];
            $statusNote = 'Semua lisensi sedang dipinjam. Pilih judul lain '
                . 'atau kembali saat salinan dikembalikan.';
        } elseif ($isAdmin) {
            $primary = [
                'label' => 'Kelola transaksi',
                'url' => app_url('pages/admin/transactions.php'),
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Kelola koleksi',
                'url' => app_url('pages/admin/ebooks.php'),
            ];
            $statusNote = 'Akun admin mengelola koleksi; peminjaman diajukan member.';
        } else {
            $primary = [
                'label' => 'Masuk untuk meminjam',
                'url' => app_url('pages/auth/login.php'),
                'icon' => null,
            ];
            $secondary = [
                'label' => 'Daftar akun',
                'url' => app_url('pages/auth/register.php'),
            ];
            $statusNote = 'Peminjaman butuh akun member. Lisensi tersisa '
                . $licenseLimit . ' dari ' . $licenseTotal . '.';
        }
        ?>

        <article class="detail-card">

            <div class="detail-cover">

                <?php if ($cover !== ''): ?>
                    <img
                        src="<?= htmlspecialchars(app_url('public/uploads/covers/' . rawurlencode($cover))) ?>"
                        alt="Sampul <?= htmlspecialchars($title) ?>"
                        onerror="this.remove();"
                    >
                <?php else: ?>
                    <span class="book-initial" aria-hidden="true">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($title, 0, 1))) ?>
                    </span>
                <?php endif; ?>

            </div>

            <div class="detail-content">

                <div class="detail-meta">

                    <?php if ($categoryName !== ''): ?>
                        <span class="book-cat band-<?= Shelf::band($categoryName) ?>">
                            <?= htmlspecialchars($categoryName) ?>
                        </span>
                    <?php endif; ?>

                    <span class="call-code">
                        <?= htmlspecialchars(Shelf::code($categoryName, $ebookId)) ?>
                    </span>

                    <span class="badge <?= $licenseLimit > 0 ? 'badge-approved' : 'badge-rejected' ?>">
                        <?= $licenseLimit > 0 ? 'Tersedia' : 'Habis' ?>
                    </span>

                </div>

                <h1><?= htmlspecialchars($title) ?></h1>

                <p class="detail-author">
                    oleh <?= htmlspecialchars($ebook['author'] ?? 'Penulis tidak diketahui') ?>
                </p>

                <p class="detail-desc">
                    <?= htmlspecialchars(
                        !empty($ebook['description'])
                            ? $ebook['description']
                            : 'Deskripsi e-book belum tersedia.'
                    ) ?>
                </p>

                <dl class="detail-spec">

                    <?php foreach ($specs as $label => $value): ?>
                        <div>
                            <dt><?= htmlspecialchars($label) ?></dt>
                            <dd><?= htmlspecialchars($value) ?></dd>
                        </div>
                    <?php endforeach; ?>

                </dl>

                <div class="detail-actions">

                    <?php if ($primary['url'] === null): ?>
                        <span class="btn btn-primary is-disabled" aria-disabled="true">
                            <?= htmlspecialchars($primary['label']) ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($primary['url']) ?>" class="btn btn-primary">
                            <?php if ($primary['icon'] !== null): ?>
                                <?= icon($primary['icon']) ?>
                            <?php endif; ?>
                            <?= htmlspecialchars($primary['label']) ?>
                        </a>
                    <?php endif; ?>

                    <a href="<?= htmlspecialchars($secondary['url']) ?>" class="btn btn-secondary">
                        <?= htmlspecialchars($secondary['label']) ?>
                    </a>

                </div>

                <p class="status-note">
                    <?= icon('clock') ?>
                    <?= htmlspecialchars($statusNote) ?>
                </p>

            </div>

        </article>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
