<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;

$catalogService = new CatalogService($storage);

$keyword = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$ebooks = $catalogService->search($keyword, $category);

$categories = [];

foreach ($storage->read('categories.json') as $item) {
    if (is_array($item)) {
        $name = $item['name'] ?? $item['title'] ?? '';

        if ($name !== '') {
            $categories[] = $name;
        }
    } elseif (is_string($item) && $item !== '') {
        $categories[] = $item;
    }
}

$categories = array_values(array_unique($categories));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Katalog E-Book - Digital Library</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .container {
            width: min(1200px, 90%);
            margin: 0 auto;
        }

        .hero {
            padding: 55px 0 30px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .hero p {
            color: #6b7280;
            font-size: 16px;
        }

        .filter-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-input,
        .category-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
        }

        .search-input {
            flex: 1;
            min-width: 220px;
        }

        .category-select {
            min-width: 180px;
            background: white;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-size: 15px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .result-info {
            margin-bottom: 20px;
            color: #6b7280;
        }

        .ebook-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
            padding-bottom: 60px;
        }

        .ebook-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .ebook-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10);
        }

        .cover {
            width: 100%;
            height: 250px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            overflow: hidden;
        }

        .cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-content {
            padding: 18px;
        }

        .category {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .card-content h2 {
            font-size: 18px;
            margin-bottom: 7px;
        }

        .author {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .description {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .detail-link {
            display: inline-block;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .empty {
            grid-column: 1 / -1;
            background: white;
            border-radius: 14px;
            padding: 50px 20px;
            text-align: center;
            color: #6b7280;
        }

        .empty h2 {
            color: #374151;
            margin-bottom: 10px;
        }

        .footer {
            background: #111827;
            color: #d1d5db;
            text-align: center;
            padding: 25px 15px;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .ebook-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero {
                padding-top: 35px;
            }

            .hero h1 {
                font-size: 28px;
            }

            .ebook-grid {
                grid-template-columns: 1fr;
            }

            .search-input,
            .category-select,
            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a href="<?= app_url('pages/public/home.php') ?>" class="logo">
        Digital Library
    </a>

    <div class="nav-links">
        <a href="<?= app_url('pages/public/home.php') ?>">Home</a>
        <a href="<?= app_url('pages/public/catalog.php') ?>">Katalog</a>
        <a href="<?= app_url('pages/auth/login.php') ?>">Login</a>
        <a href="<?= app_url('pages/auth/register.php') ?>">Registrasi</a>
    </div>
</nav>

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

                    <option
                        value="<?= htmlspecialchars($item) ?>"
                        <?= $category === $item ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($item) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit" class="btn">
                Cari
            </button>

        </form>

    </section>

    <?php if ($keyword !== '' || $category !== ''): ?>

        <p class="result-info">
            Hasil pencarian:
            <strong><?= count($ebooks) ?></strong> e-book ditemukan.
        </p>

    <?php endif; ?>

    <section class="ebook-grid">

        <?php if (empty($ebooks)): ?>

            <div class="empty">

                <h2>Belum Ada E-Book</h2>

                <p>
                    Saat ini belum ada koleksi e-book yang tersedia.
                </p>

            </div>

        <?php else: ?>

            <?php foreach ($ebooks as $ebook): ?>

                <article class="ebook-card">

                    <div class="cover">

                        <?php if (!empty($ebook['cover'])): ?>

                            <img
                                src="<?= htmlspecialchars($ebook['cover']) ?>"
                                alt="<?= htmlspecialchars($ebook['title'] ?? 'Cover E-Book') ?>"
                            >

                        <?php else: ?>

                            <span>Cover E-Book</span>

                        <?php endif; ?>

                    </div>

                    <div class="card-content">

                        <?php if (!empty($ebook['category'])): ?>

                            <span class="category">
                                <?= htmlspecialchars($ebook['category']) ?>
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
                            href="<?= app_url('pages/public/detail.php?id=' . urlencode($ebook['id'] ?? '')) ?>"
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

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Digital Library. All rights reserved.</p>
</footer>

</body>
</html>