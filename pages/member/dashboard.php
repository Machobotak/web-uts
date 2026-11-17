<?php

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Services\TransactionService;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $auth = new AuthService($storage);
    $middleware = new AuthMiddleware($auth);
    $member = $middleware->requireRole('member');

    $transactionService = new TransactionService($storage);
    $transactions = $transactionService->getMemberTransactions((int) $member->getId());

    $totalTransactions = count($transactions);
    $pendingTransactions = 0;
    $approvedTransactions = 0;

    foreach ($transactions as $transaction) {
        if (($transaction['status'] ?? '') === 'pending') {
            $pendingTransactions++;
        }

        if (($transaction['status'] ?? '') === 'approved') {
            $approvedTransactions++;
        }
    }
} catch (Throwable $e) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login sebagai member.');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$name = $member->getName();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - Digital Library</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #253047;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 22px;
        }

        .welcome {
            background: #263b65;
            color: white;
            padding: 30px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin: 0 0 10px;
        }

        .welcome p {
            margin: 0;
            color: #dce5f7;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000a;
        }

        .stat-card p {
            color: #718096;
            margin: 0 0 12px;
        }

        .stat-card h2 {
            font-size: 30px;
            margin: 0;
            color: #263b65;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .menu a {
            display: block;
            background: white;
            padding: 24px;
            border-radius: 12px;
            color: #263b65;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 3px 12px #0000000a;
            transition: .2s;
        }

        .menu a:hover {
            transform: translateY(-3px);
            background: #edf2ff;
        }

        .menu span {
            display: block;
            font-size: 14px;
            font-weight: normal;
            color: #718096;
            margin-top: 9px;
        }

        @media (max-width: 700px) {
            .stats, .menu {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <section class="welcome">
        <h1>Selamat Datang, <?= e($name) ?>!</h1>
        <p>Kelola aktivitas peminjaman dan temukan ebook favoritmu di Digital Library.</p>
    </section>

    <section class="stats">
        <div class="stat-card">
            <p>Total Peminjaman</p>
            <h2><?= $totalTransactions ?></h2>
        </div>

        <div class="stat-card">
            <p>Menunggu Persetujuan</p>
            <h2><?= $pendingTransactions ?></h2>
        </div>

        <div class="stat-card">
            <p>Peminjaman Disetujui</p>
            <h2><?= $approvedTransactions ?></h2>
        </div>
    </section>

    <section class="menu">
        <a href="borrowings.php">
            Cari dan Pinjam Ebook
            <span>Lihat katalog dan ajukan peminjaman ebook.</span>
        </a>

        <a href="history.php">
            Riwayat Peminjaman
            <span>Lihat status dan daftar peminjamanmu.</span>
        </a>

        <a href="profile.php">
            Profil Saya
            <span>Lihat dan kelola informasi akun.</span>
        </a>
    </section>

</div>
</body>
</html>