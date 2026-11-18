<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;

$catalogService = new CatalogService($storage);

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

<main class="container">

    <section class="detail-section">

        <a
            href="<?= app_url('pages/public/catalog.php') ?>"
            class="back-link"
        >
            ← Kembali ke Katalog
        </a>

        <?php if ($ebook === null): ?>

            <div class="empty">

                <h1>
                    E-Book Tidak Ditemukan
                </h1>

                <p>
                    Data e-book yang kamu cari belum tersedia
                    atau tidak ditemukan.
                </p>

                <a
                    href="<?= app_url('pages/public/catalog.php') ?>"
                    class="btn btn-primary"
                >
                    Kembali ke Katalog
                </a>

            </div>

        <?php else: ?>

            <div class="detail-card">

                <div class="cover">

                    <?php if (!empty($ebook['cover'])): ?>

                        <img
                            src="<?= htmlspecialchars(app_url('public/uploads/covers/' . rawurlencode($ebook['cover']))) ?>"
                            alt="<?= htmlspecialchars($ebook['title'] ?? 'Cover E-Book') ?>"
                            onerror="this.parentElement.classList.add('is-empty'); this.remove();"
                        >

                    <?php else: ?>

                        <span>
                            Cover E-Book
                        </span>

                    <?php endif; ?>

                </div>

                <div class="content">

                    <?php if ($categoryName !== ''): ?>

                        <span class="category">
                            <?= htmlspecialchars($categoryName) ?>
                        </span>

                    <?php endif; ?>

                    <h1>
                        <?= htmlspecialchars($ebook['title'] ?? 'Tanpa Judul') ?>
                    </h1>

                    <p class="author">
                        Penulis:
                        <?= htmlspecialchars($ebook['author'] ?? 'Tidak diketahui') ?>
                    </p>

                    <?php if (!empty($ebook['description'])): ?>

                        <p class="description">
                            <?= nl2br(htmlspecialchars($ebook['description'])) ?>
                        </p>

                    <?php else: ?>

                        <p class="description">
                            Deskripsi e-book belum tersedia.
                        </p>

                    <?php endif; ?>

                    <div class="info">

                        <p>
                            <strong>Kategori:</strong>
                            <?= htmlspecialchars($categoryName !== '' ? $categoryName : '-') ?>
                        </p>

                        <p>
                            <strong>ID E-Book:</strong>
                            <?= htmlspecialchars((string) ($ebook['id_ebook'] ?? '-')) ?>
                        </p>

                    </div>

                    <?php if (!empty($ebook['file_path'])): ?>

                        <a
                            href="<?= htmlspecialchars(app_url('pages/member/reader.php?id=' . urlencode((string) ($ebook['id_ebook'] ?? '')))) ?>"
                            class="btn btn-primary"
                        >
                            Baca E-Book
                        </a>

                    <?php endif; ?>

                    <a
                        href="<?= app_url('pages/public/catalog.php') ?>"
                        class="btn btn-secondary"
                    >
                        Kembali

                    </a>

                </div>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
