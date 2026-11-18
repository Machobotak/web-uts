<?php
/**
 * Partial tata letak halaman dashboard: <head> + sidebar navigasi.
 *
 * Dipakai halaman admin/member:
 *   require __DIR__ . '/../partials/dashboard-header.php';
 *
 * Variabel yang dibaca:
 *   $pageTitle     judul dokumen (<title>)
 *   $pageHeading   judul halaman pada topbar
 *   $pageSubtitle  deskripsi singkat di bawah judul (opsional)
 *   $activeNav     kunci menu aktif, lihat $navItems di bawah
 *   $userName      nama pengguna yang sedang login
 *   $userRole      admin | member
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}

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

if ($userRole === 'admin') {
    $navItems = [
        'dashboard' => [
            'label' => 'Dashboard',
            'url' => app_url('pages/admin/dashboard.php'),
        ],
        'ebooks' => [
            'label' => 'Manajemen Ebook',
            'url' => app_url('pages/admin/ebooks.php'),
        ],
        'categories' => [
            'label' => 'Manajemen Kategori',
            'url' => app_url('pages/admin/categories.php'),
        ],
        'members' => [
            'label' => 'Manajemen Member',
            'url' => app_url('pages/admin/members.php'),
        ],
        'transactions' => [
            'label' => 'Manajemen Transaksi',
            'url' => app_url('pages/admin/transactions.php'),
        ],
    ];
    $roleLabel = 'Admin';
} else {
    $navItems = [
        'dashboard' => [
            'label' => 'Dashboard',
            'url' => app_url('pages/member/dashboard.php'),
        ],
        'borrowings' => [
            'label' => 'Pinjam Ebook',
            'url' => app_url('pages/member/borrowings.php'),
        ],
        'history' => [
            'label' => 'Riwayat Peminjaman',
            'url' => app_url('pages/member/history.php'),
        ],
        'profile' => [
            'label' => 'Profil Saya',
            'url' => app_url('pages/member/profile.php'),
        ],
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

    <link rel="stylesheet" href="<?= e(app_url('public/assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(app_url('public/assets/css/dashboard.css')) ?>">
</head>

<body>

<div class="layout">

    <aside class="sidebar">

        <div class="brand">Digital Library</div>

        <p class="sidebar-label">Menu Utama</p>

        <?php foreach ($navItems as $key => $item): ?>
            <a
                href="<?= e($item['url']) ?>"
                class="nav-link <?= $key === $activeNav ? 'is-active' : '' ?>"
            ><?= e($item['label']) ?></a>
        <?php endforeach; ?>

        <p class="sidebar-label">Akun</p>

        <form
            action="<?= e(app_url('handlers/auth/logout.php')) ?>"
            method="POST"
            class="logout-form"
        >
            <button type="submit" class="logout-button">Logout</button>
        </form>

    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1><?= e($pageHeading) ?></h1>

                <?php if ($pageSubtitle !== ''): ?>
                    <p class="subtitle"><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>

            <span class="account">
                <?= e($roleLabel) ?>: <?= e($userName) ?>
            </span>
        </div>
