<?php

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Services\TransactionService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $member = $middleware->requireRole('member');

    $memberId = (int) $_SESSION['user_id'];

    $catalogService = new CatalogService($storage);
    $transactionService = new TransactionService($storage);

    $ebooks = $catalogService->getAllEbooks();
    $transactions = $transactionService->getMemberTransactions($memberId);

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
    <title>Peminjaman Ebook | Digital Library</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #263247;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 { margin: 0 0 8px; }

        .subtitle { color: #718096; margin: 0; }

        .back, .submit {
            background: #334eac;
            color: white;
            border: 0;
            padding: 11px 17px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
        }

        .section {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }

        .section h2 { margin-top: 0; }

        .ebooks {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        .ebook {
            padding: 18px;
            border: 1px solid #e5e9f2;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .ebook h3 { margin: 0; font-size: 17px; }
        .author { color: #718096; font-size: 13px; }
        .description { font-size: 14px; line-height: 1.5; }

        .select-ebook {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-top: 10px;
        }

        .submit { margin-top: 20px; }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            text-align: left;
            padding: 13px;
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

        .cancel {
            background: #dc3545;
            color: white;
            border: 0;
            padding: 7px 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            background: #e8f5e9;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .error { background: #fde2e4; }
        .empty { color: #718096; text-align: center; padding: 25px; }

        @media(max-width: 600px) {
            body { padding: 15px; }
            .section { padding: 16px; }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Peminjaman Ebook</h1>
            <p class="subtitle">Pilih ebook yang ingin kamu pinjam.</p>
        </div>

        <a class="back" href="<?= e(app_url('pages/member/dashboard.php')) ?>">
            ← Dashboard
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message">
            Pengajuan peminjaman berhasil dikirim.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error">
            <?= e($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Katalog Ebook</h2>

        <?php if (empty($ebooks)): ?>
            <p class="empty">Belum ada ebook yang tersedia.</p>
        <?php else: ?>

            <form method="POST" action="<?= e(app_url('handlers/transaction/borrow.php')) ?>">

                <div class="ebooks">

                    <?php foreach ($ebooks as $ebook): ?>
                        <div class="ebook">

                            <h3><?= e($ebook['title'] ?? 'Tanpa Judul') ?></h3>

                            <div class="author">
                                Penulis: <?= e($ebook['author'] ?? '-') ?>
                            </div>

                            <div class="description">
                                <?= e($ebook['description'] ?? 'Tidak ada deskripsi.') ?>
                            </div>

                            <label class="select-ebook">
                                <input
                                    type="checkbox"
                                    name="ebook_ids[]"
                                    value="<?= e($ebook['id'] ?? '') ?>"
                                >
                                Pilih ebook
                            </label>

                        </div>
                    <?php endforeach; ?>

                </div>

                <button type="submit" class="submit">
                    Ajukan Peminjaman
                </button>

            </form>

        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Pengajuan Peminjaman Saya</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ebook</th>
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
                            Kamu belum memiliki pengajuan peminjaman.
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

                            <td>
                                <?= e(implode(', ', $ebookNames) ?: '-') ?>
                            </td>

                            <td><?= e($transaction['borrow_date'] ?? '-') ?></td>

                            <td><?= e($transaction['due_date'] ?? '-') ?></td>

                            <td>
                                <span class="badge <?= e($status) ?>">
                                    <?= e($statusLabels[$status] ?? $status) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($status === 'pending'): ?>

                                    <form method="POST" action="<?= e(app_url('handlers/transaction/cancel.php')) ?>">
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $transaction['id'] ?>"
                                        >

                                        <button type="submit" class="cancel">
                                            Batalkan
                                        </button>
                                    </form>

                                <?php elseif ($status === 'approved'): ?>

                                    <?php foreach (($transaction['details'] ?? []) as $detail): ?>
                                        <a
                                            href="<?= e(app_url('pages/member/reader.php?id=' . urlencode((string) ($detail['ebook_id'] ?? '')))) ?>"
                                            class="submit"
                                            style="display:inline-block; text-decoration:none; margin:3px;"
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