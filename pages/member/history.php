<?php

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $member = $middleware->requireRole('member');

    $service = new TransactionService($storage);
    $transactions = $service->getMemberTransactions((int) $_SESSION['user_id']);

} catch (Throwable $e) {
    http_response_code(403);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$statusLabels = [
    'pending' => 'Menunggu Persetujuan',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    'returned' => 'Dikembalikan'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman | Digital Library</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #263247;
        }
        .container { max-width: 1100px; margin: auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        h1 { margin: 0 0 8px; }
        .subtitle { color: #718096; margin: 0; }
        .back {
            padding: 11px 17px;
            border-radius: 8px;
            background: #334eac;
            color: white;
            text-decoration: none;
        }
        .panel {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #edf0f5;
            font-size: 14px;
        }
        th { background: #f8f9fc; color: #718096; }
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #edf0f5;
            font-size: 12px;
        }
        .pending { background: #fff3cd; color: #946200; }
        .approved { background: #d9f5e5; color: #176b3a; }
        .rejected, .cancelled { background: #fde2e4; color: #a12b35; }
        .returned { background: #e0ebff; color: #2456a6; }
        .empty { text-align: center; color: #718096; padding: 30px; }
        @media(max-width: 600px) {
            body { padding: 15px; }
            .panel { padding: 15px; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Riwayat Peminjaman</h1>
            <p class="subtitle">Daftar seluruh transaksi peminjaman ebook kamu.</p>
        </div>

        <a class="back" href="<?= e(app_url('pages/member/dashboard.php')) ?>">
            ← Dashboard
        </a>
    </div>

    <div class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Judul Ebook</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($transactions as $transaction): ?>
                        <?php
                            $status = $transaction['status'] ?? 'pending';

                            $ebookNames = array_map(
                                fn($detail) => $detail['ebook_title'] ?? 'Ebook',
                                $transaction['details'] ?? []
                            );
                        ?>

                        <tr>
                            <td>#<?= e($transaction['id'] ?? '-') ?></td>
                            <td><?= e(implode(', ', $ebookNames) ?: '-') ?></td>
                            <td><?= e($transaction['borrow_date'] ?? '-') ?></td>
                            <td><?= e($transaction['due_date'] ?? '-') ?></td>
                            <td>
                                <span class="badge <?= e($status) ?>">
                                    <?= e($statusLabels[$status] ?? $status) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($status === 'approved'): ?>
                                    <?php foreach (($transaction['details'] ?? []) as $detail): ?>
                                        <a
                                            href="<?= e(app_url('pages/member/reader.php?id=' . urlencode((string) ($detail['ebook_id'] ?? '')))) ?>"
                                            style="display:inline-block; background:#334eac; color:white; padding:7px 10px; border-radius:6px; text-decoration:none; margin:3px;"
                                        >
                                            Baca <?= e($detail['ebook_title'] ?? 'Ebook') ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>