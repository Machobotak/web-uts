<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;
use App\Support\Shelf;

$catalogService = new CatalogService($storage);

$keyword = trim($_GET['search'] ?? '');
$category = (int) ($_GET['category'] ?? 0);

$ebooks = $catalogService->searchEbooks([
    'q' => $keyword,
    'category' => $category
]);

$categoryNames = $catalogService->getCategoryNameMap();

$categories = $catalogService->getAllCategories();

$isFiltered = $keyword !== '' || $category > 0;

$pageTitle = 'Katalog E-Book - Digital Library';
$activeNav = 'catalog';
$pageCss = 'catalog.css';

require __DIR__ . '/../partials/header.php';

?>

<div class="container catalog">

    <div class="catalog-head">

        <div>
            <h1>Katalog e-book</h1>
            <p>
                Setiap kartu membawa nomor panggil dan pita kategorinya.
                Pilih judul untuk melihat keterangan lengkap.
            </p>
        </div>

        <p class="catalog-count">
            <strong><?= count($ebooks) ?></strong> judul
            <?= $isFiltered ? 'cocok dengan filter' : 'di rak' ?>
        </p>

    </div>

    <form method="GET" class="filter-bar">

        <div class="filter-field">
            <label for="search">Kata kunci</label>
            <input
                type="search"
                id="search"
                name="search"
                class="search-input"
                placeholder="Judul, penulis, penerbit, atau ISBN"
                value="<?= htmlspecialchars($keyword) ?>"
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
                    >
                        <?= htmlspecialchars($item['name'] ?? '') ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="filter-actions">

            <button type="submit" class="btn btn-primary">
                <?= icon('search') ?>
                Cari
            </button>

            <?php if ($isFiltered): ?>
                <a href="<?= app_url('pages/public/catalog.php') ?>" class="btn btn-quiet">
                    Reset
                </a>
            <?php endif; ?>

        </div>

    </form>

    <section class="shelf-grid" aria-label="Rak e-book">

        <?php if (empty($ebooks)): ?>

            <div class="shelf-empty">

                <?= icon('shelf') ?>

                <?php if ($isFiltered): ?>

                    <h2>Tidak ada judul yang cocok</h2>
                    <p>
                        Coba kata kunci lain atau pilih kategori yang berbeda.
                    </p>

                <?php else: ?>

                    <h2>Rak masih kosong</h2>
                    <p>Belum ada koleksi e-book yang tersedia saat ini.</p>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <?php foreach ($ebooks as $ebook): ?>

                <?php
                $ebookId = (int) ($ebook['id_ebook'] ?? 0);
                $title = $ebook['title'] ?? 'Tanpa Judul';
                $categoryName = $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';
                $cover = $ebook['cover'] ?? '';
                $licenseLimit = (int) ($ebook['license_limit'] ?? 0);
                $licenseTotal = (int) ($ebook['license_total'] ?? $licenseLimit);
                $available = $licenseLimit > 0;
                ?>

                <article class="book-card<?= $available ? '' : ' is-unavailable' ?>">

                    <a
                        class="book-card-link"
                        href="<?= app_url('pages/public/detail.php?id=' . $ebookId) ?>"
                        aria-label="Detail <?= htmlspecialchars($title) ?>"
                    ></a>

                    <div class="book-cover" aria-hidden="true">

                        <span class="book-initial">
                            <?= htmlspecialchars(mb_strtoupper(mb_substr($title, 0, 1))) ?>
                        </span>

                        <?php if ($cover !== ''): ?>
                            <img
                                src="<?= htmlspecialchars(app_url('public/uploads/covers/' . rawurlencode($cover))) ?>"
                                alt=""
                                onerror="this.remove();"
                            >
                        <?php endif; ?>

                        <span class="book-state">
                            <?= $available ? 'Tersedia' : 'Habis' ?>
                        </span>

                    </div>

                    <div class="book-body">

                        <div class="book-label">

                            <?php if ($categoryName !== ''): ?>
                                <span class="book-cat band-<?= Shelf::band($categoryName) ?>">
                                    <?= htmlspecialchars($categoryName) ?>
                                </span>
                            <?php endif; ?>

                            <span class="call-code">
                                <?= htmlspecialchars(Shelf::code($categoryName, $ebookId)) ?>
                            </span>

                        </div>

                        <h2 class="book-title"><?= htmlspecialchars($title) ?></h2>

                        <p class="book-author">
                            <?= htmlspecialchars($ebook['author'] ?? 'Penulis tidak diketahui') ?>
                        </p>

                        <?php if (!empty($ebook['description'])): ?>
                            <p class="book-desc">
                                <?= htmlspecialchars($ebook['description']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="book-foot">

                            <span class="license">
                                <?= $licenseLimit ?>/<?= $licenseTotal ?>
                                <small>lisensi</small>
                            </span>

                            <span class="book-link">
                                Lihat detail
                                <?= icon('arrow-right') ?>
                            </span>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
