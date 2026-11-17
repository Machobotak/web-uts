<?php

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $admin = $middleware->requireRole('admin');

    $id = (int) ($_GET['id'] ?? 0);
    $service = new TransactionService($storage);
    $transaction = $service->findById($id);

    if ($transaction === null) {
        http_response_code(404);
        exit('Transaksi tidak ditemukan.');
    }
} catch (Throwable $e) {
    http_response_code(403);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$statusLabels = [
    'pending' => 'Menunggu',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'returned' => 'Dikembalikan'
];

$status = $transaction['status'] ?? 'pending';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi | Digital Library</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px;
            background: #f4f6fb;
            font-family: Arial, sans-serif;
            color: #263247;
        }
        .container { max-width: 850px; margin: auto; }
        .panel {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }
        h1 { margin-top: 0; }
        .subtitle { color: #718096; }
        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin: 25px 0;
        }
        .item {
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
        }
        .item small { display: block; color: #718096; margin-bottom: 8px; }
        .item strong { overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td {
            text-align: left;
            padding: 13px;
            border-bottom: 1px solid #edf0f5;
        }
        th { color: #718096; }
        .badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #edf0f5;
        }
        .pending { background: #fff3cd; color: #946200; }
        .approved { background: #d9f5e5; color: #176b3a; }
        .rejected, .cancelled { background: #fde2e4; color: #a12b35; }
        .returned { background: #e0ebff; color: #2456a6; }
        .back {
            display: inline-block;
            margin-top: 22px;
            padding: 11px 17px;
            color: white;
            background: #334eac;
            border-radius: 8px;
            text-decoration: none;
        }
        @media(max-width: 600px) {
            body { padding: 15px; }
            .info { grid-template-columns: 1fr; }
            .panel { padding: 18px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="panel">
        <h1>Detail Transaksi</h1>
        <p class="subtitle">Informasi lengkap pengajuan peminjaman ebook.</p>

        <div class="info">
            <div class="item">
                <small>ID Transaksi</small>
                <strong>#<?= e($transaction['id'] ?? '-') ?></strong>
            </div>
            <div class="item">
                <small>Nama Member</small>
                <strong><?= e($transaction['member']['name'] ?? 'Member tidak ditemukan') ?></strong>
            </div>
            <div class="item">
                <small>Email Member</small>
                <strong><?= e($transaction['member']['email'] ?? '-') ?></strong>
            </div>
            <div class="item">
                <small>Status</small>
                <strong>
                    <span class="badge <?= e($status) ?>">
                        <?= e($statusLabels[$status] ?? $status) ?>
                    </span>
                </strong>
            </div>
            <div class="item">
                <small>Tanggal Peminjaman</small>
                <strong><?= e($transaction['borrow_date'] ?? '-') ?></strong>
            </div>
            <div class="item">
                <small>Tanggal Jatuh Tempo</small>
                <strong><?= e($transaction['due_date'] ?? '-') ?></strong>
            </div>
        </div>

        <h2>Daftar Ebook</h2>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Ebook</th>
                    <th>ID Ebook</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaction['details'])): ?>
                    <tr>
                        <td colspan="3">Tidak ada detail ebook.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaction['details'] as $index => $detail): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($detail['ebook_title'] ?? 'Ebook') ?></td>
                            <td><?= e($detail['ebook_id'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <a class="back" href="<?= e(app_url('pages/admin/transactions.php')) ?>">
            ← Kembali ke Transaksi
        </a>
    </div>
</div>
</body>
</html>