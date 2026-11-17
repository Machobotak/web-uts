<?php

use App\Services\AuthService;
use App\Services\CatalogService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

// Cek akses admin.
try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $admin = $middleware->requireRole('admin');
} catch (Throwable $e) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login sebagai admin.');
}

// Fungsi keamanan output.
function ebook_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$catalogService = new CatalogService($storage);

$ebooks = [];
$categories = [];
$error = '';
$success = '';

// Upload PDF ebook.
function ebook_upload_pdf(): string
{
    if (!isset($_FILES['ebook_pdf']) ||
        $_FILES['ebook_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    $file = $_FILES['ebook_pdf'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload PDF gagal.');
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Ukuran PDF maksimal 10 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        throw new RuntimeException('File harus berformat PDF.');
    }

    $handle = fopen($file['tmp_name'], 'rb');
    $signature = fread($handle, 5);
    fclose($handle);

    if ($signature !== '%PDF-') {
        throw new RuntimeException('File PDF tidak valid.');
    }

    $uploadDirectory = dirname(__DIR__, 2) . '/storage/ebooks';

    if (!is_dir($uploadDirectory) &&
        !mkdir($uploadDirectory, 0755, true)) {
        throw new RuntimeException('Folder penyimpanan PDF gagal dibuat.');
    }

    $filename = bin2hex(random_bytes(16)) . '.pdf';
    $destination = $uploadDirectory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('PDF gagal disimpan.');
    }

    return 'storage/ebooks/' . $filename;
}

$uploadedPath = '';

// Proses tambah, edit, dan hapus melalui CatalogService.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id = trim($_POST['id'] ?? '');

            $uploadedPath = ebook_upload_pdf();

            if ($uploadedPath !== '') {
                $_POST['file_path'] = $uploadedPath;
            }

            if ($id !== '') {
                $catalogService->updateEbook($id, $_POST);
                $success = 'Ebook berhasil diperbarui.';
            } else {
                $catalogService->createEbook($_POST);
                $success = 'Ebook berhasil ditambahkan.';
            }

            $uploadedPath = '';
        } elseif ($action === 'delete') {
            $id = trim($_POST['id'] ?? '');

            if ($id === '') {
                throw new InvalidArgumentException('ID ebook tidak valid.');
            }

            $catalogService->deleteEbook($id);
            $success = 'Ebook berhasil dihapus.';
        } else {
            throw new InvalidArgumentException('Aksi tidak dikenali.');
        }
    } catch (Throwable $e) {
        if ($uploadedPath !== '') {
            $fullPath = dirname(__DIR__, 2) . '/' . $uploadedPath;

            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $error = $e->getMessage();
    }
}
// Ambil data terbaru.
$ebooks = $catalogService->getAllEbooks();
$ebooks = is_array($ebooks) ? $ebooks : [];

$categories = $storage->read('categories.json');
$categories = is_array($categories) ? $categories : [];

// Data untuk form edit.
$editId = $_GET['edit'] ?? '';
$editEbook = null;

if ($editId !== '') {
    $editEbook = $catalogService->findById((string) $editId);
}

$adminName = $admin->getName();
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ebook | Digital Library</title>

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
            min-width: 0;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }

        .topbar h1 {
            font-size: 26px;
        }

        .panel {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px #0000000a;
        }

        .panel h2 {
            margin-bottom: 20px;
            font-size: 19px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        button, .button {
            border: none;
            border-radius: 7px;
            padding: 11px 16px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .primary {
            background: #2563eb;
            color: white;
        }

        .secondary {
            background: #64748b;
            color: white;
        }

        .danger {
            background: #dc2626;
            color: white;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #475569;
        }

        .actions {
            display: flex;
            gap: 7px;
        }

        .logout-form {
            margin-top: 30px;
        }

        .logout {
            width: 100%;
            background: #dc2626;
            color: white;
        }

        @media(max-width: 700px) {
            .layout {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .main {
                padding: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
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
        <div class="brand">Digital Library</div>

        <p>MENU UTAMA</p>

        <a href="<?= ebook_e(app_url('pages/admin/dashboard.php')) ?>">
            Dashboard
        </a>

        <a href="<?= ebook_e(app_url('pages/admin/ebooks.php')) ?>" class="active">
            Manajemen Ebook
        </a>

        <a href="<?= ebook_e(app_url('pages/admin/categories.php')) ?>">
            Manajemen Kategori
        </a>

        <a href="<?= ebook_e(app_url('pages/admin/members.php')) ?>">
            Manajemen Member
        </a>

        <a href="<?= ebook_e(app_url('pages/admin/transactions.php')) ?>">
            Manajemen Transaksi
        </a>

        <p>AKUN</p>

        <form action="<?= ebook_e(app_url('handlers/auth/logout.php')) ?>"
              method="POST"
              class="logout-form">
            <button type="submit" class="logout">Logout</button>
        </form>
    </aside>

    <main class="main">

        <div class="topbar">
            <h1>Manajemen Ebook</h1>
            <span>Admin: <?= ebook_e($adminName) ?></span>
        </div>

        <?php if ($success): ?>
            <div class="success-message"><?= ebook_e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?= ebook_e($error) ?></div>
        <?php endif; ?>

        <section class="panel">
            <h2><?= $editEbook ? 'Edit Ebook' : 'Tambah Ebook Baru' ?></h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">

                <input type="hidden" name="id"
                       value="<?= ebook_e($editEbook['id'] ?? '') ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Judul Ebook</label>
                        <input type="text" name="title" required
                               value="<?= ebook_e($editEbook['title'] ?? '') ?>"
                               placeholder="Masukkan judul ebook">
                    </div>

                    <div class="form-group">
                        <label>Penulis</label>
                        <input type="text" name="author" required
                               value="<?= ebook_e($editEbook['author'] ?? '') ?>"
                               placeholder="Masukkan nama penulis">
                    </div>

                    <div class="form-group full">
                        <label>Kategori</label>
                        <select name="category_id">
                            <option value="">Pilih kategori</option>

                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= ebook_e($category['id'] ?? '') ?>"
                                    <?= (string)($editEbook['category_id'] ?? '') ===
                                        (string)($category['id'] ?? '') ? 'selected' : '' ?>>
                                    <?= ebook_e($category['name'] ?? 'Kategori') ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="form-group full">
                        <label>Upload File Ebook (PDF, maksimal 10 MB)</label>
                        <input type="file" name="ebook_pdf" accept="application/pdf">
                        <?php if (!empty($editEbook['file_path'])): ?>
                            <small>PDF saat ini sudah tersedia. Kosongkan jika tidak ingin menggantinya.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full">
                        <label>Deskripsi</label>
                        <textarea name="description"
                                  placeholder="Masukkan deskripsi ebook"><?= ebook_e($editEbook['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group full">
                        <div class="actions">
                            <button type="submit" class="primary">
                                <?= $editEbook ? 'Simpan Perubahan' : 'Tambah Ebook' ?>
                            </button>

                            <?php if ($editEbook): ?>
                                <a href="<?= ebook_e(app_url('pages/admin/ebooks.php')) ?>"
                                   class="button secondary">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </form>
        </section>

        <section class="panel">
            <h2>Daftar Ebook (<?= count($ebooks) ?>)</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Ebook</th>
                            <th>Penulis</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($ebooks)): ?>
                        <tr>
                            <td colspan="5">Belum ada data ebook.</td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($ebooks as $index => $ebook): ?>
                            <?php
                            $categoryName = '-';

                            foreach ($categories as $category) {
                                if (
                                    (string)($category['id'] ?? '') ===
                                    (string)($ebook['category_id'] ?? '')
                                ) {
                                    $categoryName = $category['name'] ?? '-';
                                    break;
                                }
                            }
                            ?>

                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= ebook_e($ebook['title'] ?? '-') ?></td>
                                <td><?= ebook_e($ebook['author'] ?? '-') ?></td>
                                <td><?= ebook_e($categoryName) ?></td>
                                <td>
                                    <div class="actions">
                                        <a class="button primary"
                                           href="<?= ebook_e(app_url('pages/admin/ebooks.php?edit=' . urlencode((string)($ebook['id'] ?? '')))) ?>">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus ebook ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id"
                                                   value="<?= ebook_e($ebook['id'] ?? '') ?>">

                                            <button type="submit" class="danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>
</body>
</html>

