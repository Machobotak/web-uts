<?php
/**
 * Partial tata letak: <head> + navbar.
 *
 * Dipakai halaman: require __DIR__ . '/../partials/header.php';
 * Variabel yang dibaca:
 *   $pageTitle      judul lengkap dokumen (<title>)
 *   $activeNav      kunci menu aktif: home | catalog | login | register
 *   $pageCss        nama file CSS halaman di public/assets/css (mis. 'home.css')
 *   $useAuthStyles  true untuk memuat auth-form.css
 */
if (!function_exists('app_url')) {
    http_response_code(404);
    exit;
}

$pageTitle = $pageTitle ?? 'Digital Library';
$activeNav = $activeNav ?? '';
$pageCss = $pageCss ?? '';
$useAuthStyles = $useAuthStyles ?? false;

$cssFiles = ['style.css'];

if ($useAuthStyles) {
    $cssFiles[] = 'auth-form.css';
}

if ($pageCss !== '') {
    $cssFiles[] = $pageCss;
}

$navItems = [
    'home' => ['label' => 'Home', 'url' => app_url('pages/public/home.php')],
    'catalog' => ['label' => 'Katalog', 'url' => app_url('pages/public/catalog.php')],
    'login' => ['label' => 'Login', 'url' => app_url('pages/auth/login.php')],
    'register' => ['label' => 'Registrasi', 'url' => app_url('pages/auth/register.php')],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?></title>

<?php foreach ($cssFiles as $cssFile): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('public/assets/css/' . $cssFile)) ?>">
<?php endforeach; ?>
</head>

<body>

<nav class="navbar">

    <a href="<?= htmlspecialchars(app_url('pages/public/home.php')) ?>" class="logo">
        Digital Library
    </a>

    <div class="nav-links">
        <?php foreach ($navItems as $key => $item): ?>
            <a
                href="<?= htmlspecialchars($item['url']) ?>"
                <?= $key === $activeNav ? 'aria-current="page"' : '' ?>
            ><?= $item['label'] ?></a>
        <?php endforeach; ?>
    </div>

</nav>
