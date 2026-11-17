<?php

require_once __DIR__ . '/../../config/bootstrap.php';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Digital Library</title>

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
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 22px;
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

        .hero {
            min-height: 560px;
            display: flex;
            align-items: center;
        }

        .hero-content {
            width: min(1200px, 90%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 50px;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 48px;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero-text h1 span {
            color: #2563eb;
        }

        .hero-text p {
            color: #6b7280;
            font-size: 17px;
            line-height: 1.7;
            max-width: 600px;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 13px 22px;
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
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
        }

        .btn-secondary:hover {
            background: #eff6ff;
        }

        .hero-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        }

        .hero-card-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 22px;
        }

        .hero-card h2 {
            margin-bottom: 12px;
        }

        .hero-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        .features {
            background: white;
            padding: 70px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #6b7280;
        }

        .feature-grid {
            width: min(1200px, 90%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .feature-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 28px;
            background: #ffffff;
        }

        .feature-icon {
            font-size: 30px;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        .cta {
            width: min(1200px, 90%);
            margin: 60px auto;
            background: #2563eb;
            color: white;
            border-radius: 18px;
            padding: 45px;
            text-align: center;
        }

        .cta h2 {
            margin-bottom: 12px;
        }

        .cta p {
            margin-bottom: 25px;
        }

        .cta .btn {
            background: white;
            color: #2563eb;
        }

        .footer {
            background: #111827;
            color: #d1d5db;
            text-align: center;
            padding: 25px 15px;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .hero-content {
                grid-template-columns: 1fr;
                padding: 60px 0;
            }

            .hero-text h1 {
                font-size: 36px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .nav-links {
                gap: 12px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-text h1 {
                font-size: 30px;
            }

            .btn {
                display: block;
                text-align: center;
                margin: 10px 0;
            }

            .cta {
                padding: 30px 20px;
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

<main>

    <section class="hero">

        <div class="hero-content">

            <div class="hero-text">

                <h1>
                    Temukan dan Nikmati
                    <span>E-Book Favoritmu</span>
                </h1>

                <p>
                    Digital Library menyediakan berbagai koleksi
                    e-book yang dapat kamu jelajahi dengan mudah.
                    Cari buku berdasarkan judul, penulis, dan kategori.
                </p>

                <a
                    href="<?= app_url('pages/public/catalog.php') ?>"
                    class="btn btn-primary"
                >
                    Jelajahi Katalog
                </a>

                <a
                    href="<?= app_url('pages/auth/register.php') ?>"
                    class="btn btn-secondary"
                >
                    Daftar Sekarang
                </a>

            </div>

            <div class="hero-card">

                <div class="hero-card-icon">
                    📚
                </div>

                <h2>
                    Perpustakaan Digital
                </h2>

                <p>
                    Akses katalog e-book dengan tampilan
                    yang sederhana, nyaman, dan mudah digunakan.
                </p>

            </div>

        </div>

    </section>

    <section class="features">

        <div class="section-title">

            <h2>
                Kenapa Digital Library?
            </h2>

            <p>
                Nikmati pengalaman menjelajahi koleksi e-book
                dengan lebih mudah.
            </p>

        </div>

        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">
                    🔎
                </div>

                <h3>
                    Pencarian Mudah
                </h3>

                <p>
                    Temukan e-book berdasarkan judul,
                    penulis, atau deskripsi.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">
                    📖
                </div>

                <h3>
                    Katalog Terorganisir
                </h3>

                <p>
                    Jelajahi koleksi e-book berdasarkan
                    kategori yang tersedia.
                </p>

            </div>

            <div class="feature-card">

                <div class="feature-icon">
                    💻
                </div>

                <h3>
                    Akses Praktis
                </h3>

                <p>
                    Tampilan dirancang agar nyaman digunakan
                    melalui berbagai ukuran perangkat.
                </p>

            </div>

        </div>

    </section>

    <section class="cta">

        <h2>
            Siap Menjelajahi Koleksi?
        </h2>

        <p>
            Mulai temukan e-book yang kamu butuhkan sekarang.
        </p>

        <a
            href="<?= app_url('pages/public/catalog.php') ?>"
            class="btn"
        >
            Lihat Katalog
        </a>

    </section>

</main>

<footer class="footer">

    <p>
        &copy; <?= date('Y') ?> Digital Library. All rights reserved.
    </p>

</footer>

</body>
</html>