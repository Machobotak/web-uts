
<?php

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    // Inisialisasi autentikasi.
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);

    // Pastikan pengguna sudah login sebagai admin.
    $admin = $middleware->requireRole('admin');

    // Ambil data JSON.
    $users = $authService->getAllUsers();
    $ebooks = $storage->read('ebooks.json');
    $categories = $storage->read('categories.json');
    $transactions = $storage->read('transactions.json');

    // Pastikan data berbentuk array.
    $users = is_array($users) ? $users : [];
    $ebooks = is_array($ebooks) ? $ebooks : [];
    $categories = is_array($categories) ? $categories : [];
    $transactions = is_array($transactions) ? $transactions : [];

    // Hitung statistik.
    $members = array_filter($users, fn($u) =>
        ($u['role'] ?? '') === 'member'
    );

    $activeMembers = array_filter($members, fn($u) =>
        ($u['status'] ?? '') === 'active'
    );

    $pendingTransactions = array_filter($transactions, fn($t) =>
        ($t['status'] ?? '') === 'pending'
    );

} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Terjadi kesalahan pada Dashboard</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$adminName = $admin->getName();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Digital Library</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f1f5f9;
            color: #1e293b;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #172554;
            color: white;
            padding: 25px 18px;
            flex-shrink: 0;
        }

        .brand {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 35px;
        }

        .sidebar p {
            color: #bfdbfe;
            font-size: 12px;
            margin: 20px 10px 10px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            margin-bottom: 5px;
            color: #e2e8f0;
            text-decoration: none;
            border-radius: 7px;
            font-size: 14px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        .main {
            flex: 1;
            padding: 30px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .topbar h1 {
            font-size: 26px;
        }

        .welcome {
            background: #2563eb;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .welcome h2 {
            margin-bottom: 8px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #0000000a;
        }

        .card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .card h2 {
            font-size: 30px;
            color: #1d4ed8;
        }

        .section {
            background: white;
            padding: 22px;
            border-radius: 10px;
            overflow-x: auto;
        }

        .section h2 {
            margin-bottom: 18px;
            font-size: 19px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th, td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #475569;
        }

        .status {
            color: #15803d;
            font-weight: bold;
        }

        .logout-form {
            margin-top: 30px;
        }

        .logout {
            width: 100%;
            border: none;
            background: #dc2626;
            color: white;
            padding: 12px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout:hover {
            background: #b91c1c;
        }

        @media(max-width: 850px) {
            .sidebar {
                width: 190px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 600px) {
            .layout {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main {
                padding: 18px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar h1 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">📚 Digital Library</div>

        <p>MENU UTAMA</p>

        <a href="<?= e(app_url('pages/admin/dashboard.php')) ?>" class="active">
            Dashboard
        </a>

        <a href="<?= e(app_url('pages/admin/ebooks.php')) ?>">
            Manajemen Ebook
        </a>

        <a href="<?= e(app_url('pages/admin/categories.php')) ?>">
            Manajemen Kategori
        </a>

        <a href="<?= e(app_url('pages/admin/members.php')) ?>">
            Manajemen Member
        </a>

        <a href="<?= e(app_url('pages/admin/transactions.php')) ?>">
            Manajemen Transaksi
        </a>

        <p>AKUN</p>

        <form
            action="<?= e(app_url('handlers/auth/logout.php')) ?>"
            method="POST"
            class="logout-form"
        >
            <button type="submit" class="logout">
                Logout
            </button>
        </form>
    </aside>

    <main class="main">

        <div class="topbar">
            <h1>Dashboard Admin</h1>
            <span>Administrator</span>
        </div>

        <section class="welcome">
            <h2>Selamat Datang, <?= e($adminName) ?>!</h2>
            <p>Kelola sistem perpustakaan digital melalui dashboard ini.</p>
        </section>

        <section class="stats">

            <div class="card">
                <p>Total Member</p>
                <h2><?= count($members) ?></h2>
            </div>

            <div class="card">
                <p>Member Aktif</p>
                <h2><?= count($activeMembers) ?></h2>
            </div>

            <div class="card">
                <p>Total Ebook</p>
                <h2><?= count($ebooks) ?></h2>
            </div>

            <div class="card">
                <p>Total Kategori</p>
                <h2><?= count($categories) ?></h2>
            </div>

            <div class="card">
                <p>Total Transaksi</p>
                <h2><?= count($transactions) ?></h2>
            </div>

            <div class="card">
                <p>Menunggu Persetujuan</p>
                <h2><?= count($pendingTransactions) ?></h2>
            </div>

        </section>

        <section class="section">
            <h2>Data Member Terbaru</h2>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $memberList = array_values($members);
                $memberList = array_slice($memberList, -5);
                $memberList = array_reverse($memberList);

                if (empty($memberList)):
                ?>
                    <tr>
                        <td colspan="4">Belum ada data member.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($memberList as $index => $member): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($member['name'] ?? '-') ?></td>
                            <td><?= e($member['email'] ?? '-') ?></td>
                            <td class="status">
                                <?= e($member['status'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>
</body>
</html>
