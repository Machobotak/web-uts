<?php

use App\Services\AuthService;
use App\Services\MemberService;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $authService = new AuthService($storage);
    $middleware = new AuthMiddleware($authService);
    $admin = $middleware->requireRole('admin');
} catch (Throwable $e) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login sebagai admin.');
}

function member_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$memberService = new MemberService($storage);
$members = $memberService->getAllMembers();

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editMember = $editId ? $memberService->findById($editId) : null;
$adminName = $admin->getName();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Member | Digital Library</title>

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
            gap: 15px;
            margin-bottom: 25px;
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
            font-size: 19px;
            margin-bottom: 20px;
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

        .full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 14px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 75px;
        }

        button, .button {
            border: none;
            border-radius: 7px;
            padding: 10px 14px;
            cursor: pointer;
            font-size: 13px;
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

        .success {
            background: #16a34a;
            color: white;
        }

        .danger {
            background: #dc2626;
            color: white;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
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
            flex-wrap: wrap;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .active {
            background: #dcfce7;
            color: #166534;
        }

        .inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .message {
            display: none;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .message.show {
            display: block;
        }

        .message.ok {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .logout-form {
            margin-top: 30px;
        }

        .logout {
            width: 100%;
            background: #dc2626;
            color: white;
        }

        .search {
            max-width: 350px;
            margin-bottom: 16px;
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

            .full {
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

        <a href="<?= member_e(app_url('pages/admin/dashboard.php')) ?>">
            Dashboard
        </a>

        <a href="<?= member_e(app_url('pages/admin/ebooks.php')) ?>">
            Manajemen Ebook
        </a>

        <a href="<?= member_e(app_url('pages/admin/categories.php')) ?>">
            Manajemen Kategori
        </a>

        <a href="<?= member_e(app_url('pages/admin/members.php')) ?>" class="active">
            Manajemen Member
        </a>

        <a href="<?= member_e(app_url('pages/admin/transactions.php')) ?>">
            Manajemen Transaksi
        </a>

        <p>AKUN</p>

        <form action="<?= member_e(app_url('handlers/auth/logout.php')) ?>"
              method="POST" class="logout-form">
            <button type="submit" class="logout">Logout</button>
        </form>
    </aside>

    <main class="main">

        <div class="topbar">
            <h1>Manajemen Member</h1>
            <span>Admin: <?= member_e($adminName) ?></span>
        </div>

        <div id="message" class="message"></div>

        <section class="panel">
            <h2><?= $editMember ? 'Edit Data Member' : 'Tambah Member Baru' ?></h2>

            <form id="memberForm">
                <input type="hidden" name="id_user"
                       value="<?= member_e($editMember['id_user'] ?? '') ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" required
                               value="<?= member_e($editMember['name'] ?? '') ?>"
                               placeholder="Masukkan nama member">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required
                               value="<?= member_e($editMember['email'] ?? '') ?>"
                               placeholder="Masukkan email">
                    </div>

                    <div class="form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="phone"
                               value="<?= member_e($editMember['phone'] ?? '') ?>"
                               placeholder="Masukkan nomor HP">
                    </div>

                    <div class="form-group">
                        <label>Password <?= $editMember ? '(Kosongkan jika tidak diubah)' : '' ?></label>
                        <input type="password" name="password"
                               <?= $editMember ? '' : 'required minlength="8"' ?>
                               minlength="8"
                               placeholder="<?= $editMember ? 'Password baru (opsional)' : 'Minimal 8 karakter' ?>">
                    </div>

                    <div class="form-group full">
                        <label>Alamat</label>
                        <textarea name="address"
                                  placeholder="Masukkan alamat member"><?= member_e($editMember['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group full">
                        <div class="actions">
                            <button type="submit" class="primary">
                                <?= $editMember ? 'Simpan Perubahan' : 'Tambah Member' ?>
                            </button>

                            <?php if ($editMember): ?>
                                <a href="<?= member_e(app_url('pages/admin/members.php')) ?>"
                                   class="button secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </form>
        </section>

        <section class="panel">
            <h2>Daftar Member (<?= count($members) ?>)</h2>

            <input type="text" id="searchMember" class="search"
                   placeholder="Cari nama atau email member...">

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Nomor HP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="memberTable">
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="6">Belum ada data member.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $index => $member): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= member_e($member['name'] ?? '-') ?></td>
                                <td><?= member_e($member['email'] ?? '-') ?></td>
                                <td><?= member_e($member['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="status <?= ($member['status'] ?? '') === 'active' ? 'active' : 'inactive' ?>">
                                        <?= ($member['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="button primary"
                                           href="<?= member_e(app_url('pages/admin/members.php?edit=' . urlencode((string) $member['id_user']))) ?>">
                                            Edit
                                        </a>

                                        <button type="button"
                                                class="status-button <?= ($member['status'] ?? '') === 'active' ? 'danger' : 'success' ?>"
                                                data-id="<?= member_e($member['id_user']) ?>"
                                                data-status="<?= ($member['status'] ?? '') === 'active' ? 'inactive' : 'active' ?>">
                                            <?= ($member['status'] ?? '') === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
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

<script>
const messageBox = document.getElementById('message');

function showMessage(message, success) {
    messageBox.textContent = message;
    messageBox.className = 'message show ' + (success ? 'ok' : 'error');
}

async function sendRequest(url, formData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Permintaan gagal.');
    }

    return result;
}

document.getElementById('memberForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const id = formData.get('id_user');
    const isEdit = id !== '';

    const url = isEdit
        ? '<?= member_e(app_url('handlers/member/update.php')) ?>'
        : '<?= member_e(app_url('handlers/member/create.php')) ?>';

    try {
        const result = await sendRequest(url, formData);
        showMessage(result.message, true);

        setTimeout(() => {
            window.location.href = '<?= member_e(app_url('pages/admin/members.php')) ?>';
        }, 700);
    } catch (error) {
        showMessage(error.message, false);
    }
});

document.querySelectorAll('.status-button').forEach(button => {
    button.addEventListener('click', async function() {
        const id = this.dataset.id;
        const status = this.dataset.status;

        const confirmation = status === 'inactive'
            ? 'Yakin ingin menonaktifkan member ini?'
            : 'Yakin ingin mengaktifkan kembali member ini?';

        if (!confirm(confirmation)) return;

        const formData = new FormData();
        formData.append('id_user', id);
        formData.append('status', status);

        try {
            const result = await sendRequest(
                '<?= member_e(app_url('handlers/member/status.php')) ?>',
                formData
            );

            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});

document.getElementById('searchMember').addEventListener('input', function() {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('#memberTable tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(keyword)
            ? ''
            : 'none';
    });
});
</script>

</body>
</html>
