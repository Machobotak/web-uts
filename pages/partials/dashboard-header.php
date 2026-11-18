<?php
/**
 * Partial tata letak halaman aplikasi: <head> + rel navigasi.
 *
 * Dipakai halaman admin/member:
 *   require __DIR__ . '/../partials/dashboard-header.php';
 *
 * Variabel yang dibaca:
 *   $pageTitle     judul dokumen (<title>)
 *   $pageHeading   judul halaman pada bilah atas
 *   $pageSubtitle  deskripsi singkat di bawah judul (opsional)
 *   $activeNav     kunci menu aktif, lihat $navItems di bawah
 *   $userName      nama pengguna yang sedang login
 *   $userRole      admin | member
 *   $layoutMode    app (bawaan) | reader
 *   $backUrl       tujuan tombol kembali pada mode reader
 *   $readerMeta    baris keterangan pada mode reader (penulis, batas baca)
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/icons.php';

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'Digital Library';
$pageHeading = $pageHeading ?? '';
$pageSubtitle = $pageSubtitle ?? '';
$activeNav = $activeNav ?? '';
$userName = $userName ?? '';
$userRole = $userRole ?? 'member';
$layoutMode = $layoutMode ?? 'app';
$backUrl = $backUrl ?? app_url('pages/member/borrowings.php');
$readerMeta = $readerMeta ?? '';

$isReader = $layoutMode === 'reader';

if ($userRole === 'admin') {
    $homeUrl = app_url('pages/admin/dashboard.php');
    $navItems = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => $homeUrl],
        'ebooks' => ['label' => 'Ebook', 'icon' => 'book', 'url' => app_url('pages/admin/ebooks.php')],
        'categories' => ['label' => 'Kategori', 'icon' => 'tag', 'url' => app_url('pages/admin/categories.php')],
        'members' => ['label' => 'Member', 'icon' => 'users', 'url' => app_url('pages/admin/members.php')],
        'transactions' => ['label' => 'Transaksi', 'icon' => 'swap', 'url' => app_url('pages/admin/transactions.php')],
    ];
    $roleLabel = 'Admin';
} else {
    $homeUrl = app_url('pages/member/dashboard.php');
    $navItems = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => $homeUrl],
        'borrowings' => ['label' => 'Peminjaman', 'icon' => 'book', 'url' => app_url('pages/member/borrowings.php')],
        'history' => ['label' => 'Riwayat', 'icon' => 'clock', 'url' => app_url('pages/member/history.php')],
        'profile' => ['label' => 'Profil', 'icon' => 'user', 'url' => app_url('pages/member/profile.php')],
    ];
    $roleLabel = 'Member';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="<?= e(asset_url('public/assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('public/assets/css/dashboard.css')) ?>">
</head>

<body data-nav="closed">

<a class="skip-link" href="#konten">Ke konten utama</a>

<?php if ($isReader): ?>

    <header class="reader-top">

        <a class="reader-home" href="<?= e($homeUrl) ?>">
            <?= icon('shelf') ?>
            <span>Digital Library</span>
        </a>

        <div class="reader-id">
            <h1><?= e($pageHeading) ?></h1>

            <?php if ($readerMeta !== ''): ?>
                <p><?= e($readerMeta) ?></p>
            <?php endif; ?>
        </div>

        <a class="btn btn-secondary btn-sm" href="<?= e($backUrl) ?>">
            <?= icon('arrow-left') ?>
            Kembali
        </a>

    </header>

    <main class="reader-main" id="konten">

<?php else: ?>

    <div class="layout">

        <aside class="sidebar" id="mainNav" aria-label="Navigasi aplikasi">

            <a class="brand" href="<?= e($homeUrl) ?>">
                <?= icon('shelf') ?>
                <span>
                    <span class="brand-name">Digital Library</span>
                    <span class="brand-tag">Perpustakaan digital</span>
                </span>
            </a>

            <p class="sidebar-label">Menu Utama</p>

            <ul class="nav-list">
                <?php foreach ($navItems as $key => $item): ?>
                    <li>
                        <a
                            href="<?= e($item['url']) ?>"
                            class="nav-link"
                            <?= $key === $activeNav ? 'aria-current="page"' : '' ?>
                        >
                            <?= icon($item['icon']) ?>
                            <?= e($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="rail-foot">

                <p class="rail-user">
                    <strong><?= e($userName) ?></strong>
                    <span><?= e($roleLabel) ?></span>
                </p>

                <form action="<?= e(app_url('handlers/auth/logout.php')) ?>" method="POST">
                    <button type="submit" class="logout-button">
                        <?= icon('logout') ?>
                        Logout
                    </button>
                </form>

            </div>

        </aside>

        <div class="nav-scrim" data-nav-close></div>

        <main class="main" id="konten">

            <div class="topbar">

                <div class="topbar-lead">

                    <button
                        type="button"
                        class="nav-toggle"
                        aria-expanded="false"
                        aria-controls="mainNav"
                    >
                        <?= icon('menu') ?>
                        Menu
                    </button>

                    <div>
                        <h1><?= e($pageHeading) ?></h1>

                        <?php if ($pageSubtitle !== ''): ?>
                            <p class="subtitle"><?= e($pageSubtitle) ?></p>
                        <?php endif; ?>
                    </div>

                </div>

                <span class="account">
                    <span><?= e($roleLabel) ?></span>
                    <?= e($userName) ?>
                </span>

            </div>

<?php endif; ?>
