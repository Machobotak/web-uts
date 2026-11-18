<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\CatalogService;
use App\Support\Shelf;

$catalogService = new CatalogService($storage);
$categoryNames = $catalogService->getCategoryNameMap();
$latestEbooks = array_slice($catalogService->getAllEbooks(), 0, 3);
$totalEbooks = count($catalogService->getAllEbooks());

$pageTitle = 'Digital Library';
$activeNav = 'home';
$pageCss = 'home.css';

require __DIR__ . '/../partials/header.php';

?>

<section class="hero">

    <div class="hero-inner">

        <div>


            <h1>
                Temukan buku,<br>
                pinjam, lalu baca.
            </h1>

            <p class="hero-lede">
                Koleksi ini memakai label rak: nomor panggil, pita kategori,
                dan sisa lisensi terlihat sebelum kamu meminjam. Cari judul,
                ajukan pinjam, lalu baca di browser.
            </p>

            <div class="hero-actions">

                <a href="<?= app_url('pages/public/catalog.php') ?>" class="btn btn-primary">
                    <?= icon('search') ?>
                    Jelajahi katalog
                </a>

                <a href="<?= app_url('pages/auth/register.php') ?>" class="btn btn-secondary">
                    Daftar sekarang
                </a>

            </div>

            <p class="hero-note">
                <?= $totalEbooks ?> judul di rak &middot; <?= count($categoryNames) ?> kategori
            </p>

        </div>

        <aside class="shelf-preview" aria-label="Rak terbaru">

            <div class="shelf-preview-head">
                <h2>Rak terbaru</h2>
                <a href="<?= app_url('pages/public/catalog.php') ?>">Semua</a>
            </div>

            <div class="preview-grid">

                <?php foreach ($latestEbooks as $ebook): ?>

                    <?php
                    $ebookId = (int) ($ebook['id_ebook'] ?? 0);
                    $title = $ebook['title'] ?? 'Tanpa Judul';
                    $categoryName = $categoryNames[(int) ($ebook['id_category'] ?? 0)] ?? '';
                    $cover = $ebook['cover'] ?? '';
                    ?>

                    <a
                        class="preview-card"
                        href="<?= app_url('pages/public/detail.php?id=' . $ebookId) ?>"
                    >
                        <span class="preview-cover">
                            <?php if ($cover !== ''): ?>
                                <img
                                    src="<?= htmlspecialchars(app_url('public/uploads/covers/' . rawurlencode($cover))) ?>"
                                    alt=""
                                    onerror="this.remove();"
                                >
                            <?php else: ?>
                                <?= htmlspecialchars(mb_strtoupper(mb_substr($title, 0, 1))) ?>
                            <?php endif; ?>
                        </span>

                        <span class="preview-title"><?= htmlspecialchars($title) ?></span>

                        <span class="preview-author">
                            <?= htmlspecialchars($ebook['author'] ?? '—') ?>
                        </span>

                        <?php if ($categoryName !== ''): ?>
                            <span class="call-code">
                                <?= htmlspecialchars(Shelf::code($categoryName, $ebookId)) ?>
                            </span>
                        <?php endif; ?>
                    </a>

                <?php endforeach; ?>

                <?php if (empty($latestEbooks)): ?>
                    <p class="hint">Rak masih kosong.</p>
                <?php endif; ?>

            </div>

        </aside>

    </div>

</section>

<section class="flow">

    <div class="flow-inner">

        <div class="section-head">
            <h2>Dari rak ke pembaca</h2>
            <p>
                Satu alur untuk setiap judul: cari, ajukan, lalu baca
                sampai tenggat. Lisensi ditahan sejak pengajuan dibuat.
            </p>
        </div>

        <div class="flow-grid">

            <ol class="flow-list">

                <li>
                    <span class="flow-num" aria-hidden="true">01</span>
                    <div>
                        <h3>Cari dan saring</h3>
                        <p>
                            Kata kunci menyaring judul, penulis, penerbit, dan
                            ISBN; kategori mempersempit rak. Bar filter menempel
                            saat rak digulir.
                        </p>
                    </div>
                </li>

                <li>
                    <span class="flow-num" aria-hidden="true">02</span>
                    <div>
                        <h3>Ajukan pinjam</h3>
                        <p>
                            Tandai satu atau beberapa judul di halaman
                            peminjaman, lalu kirim satu pengajuan. Salinan
                            ditahan agar sisa lisensi tetap akurat.
                        </p>
                    </div>
                </li>

                <li>
                    <span class="flow-num" aria-hidden="true">03</span>
                    <div>
                        <h3>Baca sampai tenggat</h3>
                        <p>
                            Setelah disetujui, ebook terbuka di pembaca.
                            Batas baca dan sisa hari tampil di halaman
                            peminjaman.
                        </p>
                    </div>
                </li>

            </ol>

            <aside class="flow-terms" aria-label="Ketentuan pinjam">

                <h3>Ketentuan pinjam</h3>

                <dl>
                    <div>
                        <dt>Lama pinjam</dt>
                        <dd>7 hari</dd>
                    </div>
                    <div>
                        <dt>Lisensi</dt>
                        <dd>Ditahan sejak pengajuan</dd>
                    </div>
                    <div>
                        <dt>Batas baca</dt>
                        <dd>Tampil di halaman peminjaman</dd>
                    </div>
                    <div>
                        <dt>Berkas</dt>
                        <dd>Dibaca di browser, tanpa unduh</dd>
                    </div>
                </dl>

            </aside>

        </div>

    </div>

</section>

<section class="cta">

    <div>
        <h2>Siap menjelajahi rak?</h2>
        <p>
            Buat akun, ajukan peminjaman, dan mulai membaca dari perangkat
            apa pun. Tidak perlu memasang aplikasi.
        </p>
    </div>

    <a href="<?= app_url('pages/auth/register.php') ?>" class="btn btn-light">
        Buat akun
        <?= icon('arrow-right') ?>
    </a>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
