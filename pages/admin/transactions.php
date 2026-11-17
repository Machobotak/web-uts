<?php

use App\Services\AuthService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $admin = $middleware->requireRole('admin');

    $transactionService = new TransactionService($storage);
    $transactions = $transactionService->getAllTransactions();

} catch (Throwable $e) {
    http_response_code(403);
    exit(htmlspecialchars($e->getMessage()));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$total = count($transactions);
$pending = count(array_filter($transactions, fn($t) => ($t['status'] ?? '') === 'pending'));
$approved = count(array_filter($transactions, fn($t) => ($t['status'] ?? '') === 'approved'));
$rejected = count(array_filter($transactions, fn($t) => ($t['status'] ?? '') === 'rejected'));

$statusLabels = [
    'pending' => 'Menunggu',
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
    <title>Manajemen Transaksi | Digital Library</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #263247;
        }
        .container { max-width: 1200px; margin: auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        h1 { margin: 0 0 8px; font-size: 27px; }
        .subtitle { color: #718096; margin: 0; }
        .back {
            text-decoration: none;
            color: white;
            background: #334eac;
            padding: 11px 17px;
            border-radius: 8px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }
        .stat, .panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }
        .stat p { margin: 0 0 10px; color: #718096; }
        .stat strong { font-size: 27px; }
        .panel { overflow-x: auto; }
        .panel h2 { margin-top: 0; font-size: 19px; }
        table { width: 100%; border-collapse: collapse; min-width: 850px; }
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #edf0f5;
            font-size: 14px;
            vertical-align: top;
        }
        th { color: #718096; background: #f8f9fc; }
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            background: #edf0f5;
        }
        .pending { color: #946200; background: #fff3cd; }
        .approved { color: #176b3a; background: #d9f5e5; }
        .rejected, .cancelled { color: #a12b35; background: #fde2e4; }
        .returned { color: #2456a6; background: #e0ebff; }
        .actions { display: flex; gap: 7px; flex-wrap: wrap; }
        button {
            border: 0;
            padding: 8px 11px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
        }
        .approve { background: #198754; }
        .reject { background: #dc3545; }
        .detail {
            display: inline-block;
            padding: 8px 11px;
            text-decoration: none;
            border-radius: 6px;
            background: #e8edff;
            color: #334eac;
            font-size: 13px;
        }
        .message { padding: 12px; margin-bottom: 15px; border-radius: 8px; background: #e8f5e9; }
        .error { background: #fde2e4; }
        .empty { text-align: center; color: #718096; padding: 30px; }
        @media(max-width: 750px) {
            body { padding: 16px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Manajemen Transaksi</h1>
            <p class="subtitle">Kelola pengajuan peminjaman ebook anggota.</p>
        </div>
        <a class="back" href="<?= e(app_url('pages/admin/dashboard.php')) ?>">← Dashboard</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message">
            Transaksi berhasil <?= e($_GET['success']) ?>.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error">
            <?= e($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat">
            <p>Total Transaksi</p>
            <strong><?= $total ?></strong>
        </div>
        <div class="stat">
            <p>Menunggu Persetujuan</p>
            <strong><?= $pending ?></strong>
        </div>
        <div class="stat">
            <p>Disetujui</p>
            <strong><?= $approved ?></strong>
        </div>
        <div class="stat">
            <p>Ditolak</p>
            <strong><?= $rejected ?></strong>
        </div>
    </div>

    <div class="panel">
        <h2>Daftar Transaksi Peminjaman</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Member</th>
                    <th>Ebook</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada transaksi peminjaman.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                        $status = $transaction['status'] ?? 'pending';
                        $memberName = $transaction['member']['name'] ?? 'Member tidak ditemukan';
                        $ebookNames = array_map(
                            fn($detail) => $detail['ebook_title'] ?? $detail['ebook']['title'] ?? 'Ebook',
                            $transaction['details'] ?? []
                        );
                    ?>
                    <tr>
                        <td>#<?= e($transaction['id'] ?? '-') ?></td>
                        <td><?= e($memberName) ?></td>
                        <td><?= e(implode(', ', $ebookNames) ?: '-') ?></td>
                        <td><?= e($transaction['borrow_date'] ?? '-') ?></td>
                        <td><?= e($transaction['due_date'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= e($status) ?>">
                                <?= e($statusLabels[$status] ?? $status) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="detail" href="<?= e(app_url('pages/admin/transaction-detail.php?id=' . (int) ($transaction['id'] ?? 0))) ?>">Detail</a>

                                <?php if ($status === 'pending'): ?>
                                    <form method="POST" action="<?= e(app_url('handlers/transaction/approve.php')) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $transaction['id'] ?>">
                                        <button class="approve" type="submit">Setujui</button>
                                    </form>
                                    <form method="POST" action="<?= e(app_url('handlers/transaction/reject.php')) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $transaction['id'] ?>">
                                        <button class="reject" type="submit">Tolak</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>