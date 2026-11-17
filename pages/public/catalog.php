<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;

$catalogService = new CatalogService($storage);

$keyword = trim($_GET['search'] ?? '');
$category = (int) ($_GET['category'] ?? 0);

$ebooks = $catalogService->searchEbooks([
    'q' => $keyword,
    'category' => $category
]);

$categoryNames = $catalogService->getCategoryNameMap();

foreach ($ebooks as $index => $ebook) {
    $ebooks[$index]['category_name'] =
        $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';
}

$categories = $catalogService->getAllCategories();

$pageTitle = 'Katalog E-Book - Digital Library';
$activeNav = 'catalog';
$pageCss = 'catalog.css';

require __DIR__ . '/../partials/header.php';

?>

<main class="container">

    <section class="hero">
        <h1>Katalog E-Book</h1>
        <p>Temukan berbagai koleksi e-book yang tersedia di Digital Library.</p>
    </section>

    <section class="filter-box">

        <form method="GET" class="filter-form">

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Cari judul, penulis, atau deskripsi..."
                value="<?= htmlspecialchars($keyword) ?>"
            >

            <select name="category" class="category-select">

                <option value="">Semua Kategori</option>

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

            <button type="submit" class="btn btn-primary">
                Cari
            </button>

        </form>

    </section>

    <?php if ($keyword !== '' || $category > 0): ?>

        <p class="result-info">
            Hasil pencarian:
            <strong><?= count($ebooks) ?></strong> e-book ditemukan.
        </p>

    <?php endif; ?>

    <section class="ebook-grid">

        <?php if (empty($ebooks)): ?>

            <div class="empty">

                <?php if ($keyword !== '' || $category > 0): ?>

                    <h2>E-Book Tidak Ditemukan</h2>

                    <p>
                        Tidak ada e-book yang cocok dengan kata kunci
                        atau kategori yang dipilih.
                    </p>

                    <a
                        href="<?= app_url('pages/public/catalog.php') ?>"
                        class="btn btn-primary"
                    >
                        Reset Pencarian
                    </a>

                <?php else: ?>

                    <h2>Belum Ada E-Book</h2>

                    <p>
                        Saat ini belum ada koleksi e-book yang tersedia.
                    </p>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <?php foreach ($ebooks as $ebook): ?>

                <article class="ebook-card">

                    <div class="cover">

                        <?php if (!empty($ebook['cover'])): ?>

                            <img
                                src="<?= htmlspecialchars(app_url('public/uploads/covers/' . rawurlencode($ebook['cover']))) ?>"
                                alt="<?= htmlspecialchars($ebook['title'] ?? 'Cover E-Book') ?>"
                                onerror="this.parentElement.classList.add('is-empty'); this.remove();"
                            >

                        <?php else: ?>

                            <span>Cover E-Book</span>

                        <?php endif; ?>

                    </div>

                    <div class="card-content">

                        <?php if (!empty($ebook['category_name'])): ?>

                            <span class="category">
                                <?= htmlspecialchars($ebook['category_name']) ?>
                            </span>

                        <?php endif; ?>

                        <h2>
                            <?= htmlspecialchars($ebook['title'] ?? 'Tanpa Judul') ?>
                        </h2>

                        <p class="author">
                            <?= htmlspecialchars($ebook['author'] ?? 'Penulis tidak diketahui') ?>
                        </p>

                        <?php if (!empty($ebook['description'])): ?>

                            <p class="description">
                                <?= htmlspecialchars($ebook['description']) ?>
                            </p>

                        <?php endif; ?>

                        <a
                            href="<?= app_url('pages/public/detail.php?id=' . urlencode((string) ($ebook['id_ebook'] ?? ''))) ?>"
                            class="detail-link"
                        >
                            Lihat Detail →
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
