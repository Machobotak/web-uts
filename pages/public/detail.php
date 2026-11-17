<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;

$catalogService = new CatalogService($storage);

$id = trim($_GET['id'] ?? '');

$ebook = $id !== ''
    ? $catalogService->findById($id)
    : null;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $ebook ? htmlspecialchars($ebook['title'] ?? 'Detail E-Book') : 'E-Book Tidak Ditemukan' ?>
        - Digital Library
    </title>

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
            width: min(1000px, 90%);
            margin: 0 auto;
        }

        .detail-section {
            padding: 60px 0;
        }

        .back-link {
            display: inline-block;
            color: #2563eb;
            text-decoration: none;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .detail-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 35px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .cover {
            width: 100%;
            height: 390px;
            background: #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }

        .cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .content h1 {
            font-size: 34px;
            margin-bottom: 10px;
        }

        .author {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .description {
            color: #4b5563;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .info {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-bottom: 25px;
        }

        .info p {
            margin-bottom: 8px;
            color: #4b5563;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-right: 10px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .empty {
            background: #ffffff;
            border-radius: 18px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }

        .empty h1 {
            margin-bottom: 12px;
        }

        .empty p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .footer {
            background: #111827;
            color: #d1d5db;
            text-align: center;
            padding: 25px 15px;
            font-size: 14px;
        }

        @media (max-width: 750px) {
            .navbar {
                flex-direction: column;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .detail-card {
                grid-template-columns: 1fr;
            }

            .cover {
                max-width: 300px;
                margin: 0 auto;
            }

            .content h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">

    <a
        href="<?= app_url('pages/public/home.php') ?>"
        class="logo"
    >
        Digital Library
    </a>

    <div class="nav-links">

        <a href="<?= app_url('pages/public/home.php') ?>">
            Home
        </a>

        <a href="<?= app_url('pages/public/catalog.php') ?>">
            Katalog
        </a>

        <a href="<?= app_url('pages/auth/login.php') ?>">
            Login
        </a>

        <a href="<?= app_url('pages/auth/register.php') ?>">
            Registrasi
        </a>

    </div>

</nav>

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
                            src="<?= htmlspecialchars($ebook['cover']) ?>"
                            alt="<?= htmlspecialchars($ebook['title'] ?? 'Cover E-Book') ?>"
                        >

                    <?php else: ?>

                        <span>
                            Cover E-Book
                        </span>

                    <?php endif; ?>

                </div>

                <div class="content">

                    <?php if (!empty($ebook['category'])): ?>

                        <span class="category">
                            <?= htmlspecialchars($ebook['category']) ?>
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
                            <?= htmlspecialchars($ebook['category'] ?? '-') ?>
                        </p>

                        <p>
                            <strong>ID E-Book:</strong>
                            <?= htmlspecialchars($ebook['id'] ?? '-') ?>
                        </p>

                    </div>

                    <?php if (!empty($ebook['file'])): ?>

                        <a
                            href="<?= htmlspecialchars($ebook['file']) ?>"
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

<footer class="footer">

    <p>
        &copy; <?= date('Y') ?> Digital Library. All rights reserved.
    </p>

</footer>

</body>
</html>