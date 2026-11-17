<?php

require_once __DIR__ . '/../../config/bootstrap.php';

$pageTitle = 'Digital Library';
$activeNav = 'home';
$pageCss = 'home.css';

require __DIR__ . '/../partials/header.php';

?>

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
                    class="btn btn-outline"
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
            class="btn btn-light"
        >
            Lihat Katalog
        </a>

    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
