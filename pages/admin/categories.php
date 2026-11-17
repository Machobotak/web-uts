<?php
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\CategoryService;
use App\Middleware\AuthMiddleware;

$auth = new AuthService($storage);
(new AuthMiddleware($auth))->requireRole('admin');

$categoryService = new CategoryService($storage);
$categories = $categoryService->getAllCategories();

$adminName = $_SESSION['user_name'] ?? 'Admin';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Digital Library</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#172554}
        .sidebar{position:fixed;left:0;top:0;width:240px;height:100vh;background:#192956;color:white;padding:18px 9px}
        .brand{font-size:22px;font-weight:bold;margin:0 4px 34px}
        .section{font-size:12px;color:#bfdbfe;margin:24px 10px 12px}
        .nav{display:block;color:white;text-decoration:none;padding:13px 12px;border-radius:8px;margin-bottom:5px;font-size:14px}
        .nav:hover,.nav.active{background:#2861e8}
        .logout{margin-top:25px;background:#dc2626;text-align:center}
        .main{margin-left:240px;padding:24px 30px;min-height:100vh}
        .top{display:flex;justify-content:space-between;gap:15px;align-items:center;margin-bottom:25px;flex-wrap:wrap}
        h1{font-size:26px;margin:0}
        .admin{color:#334155}
        .panel{background:white;padding:24px;border-radius:13px;margin-bottom:24px;box-shadow:0 3px 12px #0f172a0d}
        h2{font-size:19px;margin:0 0 20px}
        label{display:block;font-weight:bold;font-size:14px;margin-bottom:8px}
        input,textarea{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:7px;font:inherit;margin-bottom:16px}
        textarea{min-height:90px;resize:vertical}
        button{border:0;border-radius:7px;padding:11px 17px;background:#2861e8;color:white;cursor:pointer;font-size:14px}
        button:hover{opacity:.9}
        .edit{background:#d97706;margin-right:5px}
        .delete{background:#dc2626}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:600px}
        th,td{padding:13px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:14px}
        th{background:#f8fafc;color:#334155}
        .message{display:none;padding:12px;border-radius:7px;margin-bottom:16px}
        .success{display:block;background:#dcfce7;color:#166534}
        .error{display:block;background:#fee2e2;color:#991b1b}
        @media(max-width:700px){
            .sidebar{position:static;width:100%;height:auto}
            .brand{margin-bottom:15px}
            .main{margin-left:0;padding:20px 14px}
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand">?? Digital Library</div>
    <div class="section">MENU UTAMA</div>
    <a class="nav" href="<?= e(app_url('pages/admin/dashboard.php')) ?>">Dashboard</a>
    <a class="nav" href="<?= e(app_url('pages/admin/ebooks.php')) ?>">Manajemen Ebook</a>
    <a class="nav active" href="<?= e(app_url('pages/admin/categories.php')) ?>">Manajemen Kategori</a>
    <a class="nav" href="<?= e(app_url('pages/admin/members.php')) ?>">Manajemen Member</a>
    <a class="nav" href="<?= e(app_url('pages/admin/transactions.php')) ?>">Manajemen Transaksi</a>
    <div class="section">AKUN</div>
    <form method="POST" action="<?= e(app_url('handlers/auth/logout.php')) ?>">
        <button class="nav logout" type="submit" style="width:100%">Logout</button>
    </form>
</aside>

<main class="main">
    <div class="top">
        <h1>Manajemen Kategori</h1>
        <div class="admin">Admin: <?= e($adminName) ?></div>
    </div>

    <section class="panel">
        <h2>Tambah Kategori Baru</h2>
        <div id="message" class="message"></div>
        <form id="categoryForm">
            <label for="name">Nama Kategori</label>
            <input id="name" name="name" type="text" placeholder="Masukkan nama kategori" required maxlength="100">

            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" placeholder="Masukkan deskripsi kategori"></textarea>

            <button type="submit">Tambah Kategori</button>
        </form>
    </section>

    <section class="panel">
        <h2>Daftar Kategori (<?= count($categories) ?>)</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTable">
                <?php if (empty($categories)): ?>
                    <tr><td colspan="4">Belum ada data kategori.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $i => $category): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($category['name'] ?? '') ?></td>
                        <td><?= e($category['description'] ?? '-') ?></td>
                        <td>
                            <button class="edit" type="button"
                                onclick="editCategory(<?= (int) $category['id'] ?>, <?= e(json_encode($category['name'] ?? '')) ?>, <?= e(json_encode($category['description'] ?? '')) ?>)">Edit</button>
                            <button class="delete" type="button"
                                onclick="deleteCategory(<?= (int) $category['id'] ?>)">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
const createUrl = <?= json_encode(app_url('handlers/category/create.php')) ?>;
const updateUrl = <?= json_encode(app_url('handlers/category/update.php')) ?>;
const deleteUrl = <?= json_encode(app_url('handlers/category/delete.php')) ?>;

const form = document.getElementById('categoryForm');
const message = document.getElementById('message');

function showMessage(text, success) {
    message.textContent = text;
    message.className = 'message ' + (success ? 'success' : 'error');
}

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    const data = {
        name: document.getElementById('name').value.trim(),
        description: document.getElementById('description').value.trim()
    };

    try {
        const response = await fetch(createUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menambahkan kategori.');
        }

        showMessage(result.message, true);
        form.reset();
        setTimeout(() => location.reload(), 700);
    } catch (error) {
        showMessage(error.message, false);
    }
});

async function editCategory(id, oldName, oldDescription) {
    const name = prompt('Nama kategori:', oldName);
    if (name === null) return;

    const description = prompt('Deskripsi kategori:', oldDescription);
    if (description === null) return;

    try {
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id, name, description})
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengubah kategori.');
        }

        alert(result.message);
        location.reload();
    } catch (error) {
        alert(error.message);
    }
}

async function deleteCategory(id) {
    if (!confirm('Yakin ingin menghapus kategori ini?')) return;

    try {
        const response = await fetch(deleteUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal menghapus kategori.');
        }

        alert(result.message);
        location.reload();
    } catch (error) {
        alert(error.message);
    }
}
</script>
</body>
</html>
