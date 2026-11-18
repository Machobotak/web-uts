<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Services\AuthService;
use App\Services\MemberService;
use App\Middleware\AuthMiddleware;

$authService = new AuthService($storage);
$middleware = new AuthMiddleware($authService);

// Halaman hanya untuk admin yang sudah login.
$admin = $middleware->requireRole('admin');

$memberService = new MemberService($storage);
$members = $memberService->getAllMembers();

// Mode edit bila tersedia ?edit=<id_user>.
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editMember = $editId ? $memberService->findById($editId) : null;

$pageTitle = 'Manajemen Member | Digital Library';
$pageHeading = 'Manajemen member';
$pageSubtitle = 'Kelola akun member dan status aksesnya.';
$activeNav = 'members';
$userName = $admin->getName();
$userRole = 'admin';

require __DIR__ . '/../partials/dashboard-header.php';

?>

<div id="message" class="notice" hidden></div>

<section class="panel">

    <div class="panel-head">
        <h2><?= $editMember ? 'Edit data member' : 'Tambah member baru' ?></h2>

        <?php if ($editMember): ?>
            <span class="badge badge-completed">
                ID <?= e($editMember['id_user'] ?? '') ?>
            </span>
        <?php endif; ?>
    </div>

    <form id="memberForm">

        <input
            type="hidden"
            name="id_user"
            value="<?= e($editMember['id_user'] ?? '') ?>"
        >

        <div class="form-grid">

            <div class="field">
                <label for="member-name">Nama Lengkap</label>

                <input
                    type="text"
                    id="member-name"
                    name="name"
                    required
                    value="<?= e($editMember['name'] ?? '') ?>"
                    placeholder="Masukkan nama member"
                >
            </div>

            <div class="field">
                <label for="member-email">Email</label>

                <input
                    type="email"
                    id="member-email"
                    name="email"
                    required
                    value="<?= e($editMember['email'] ?? '') ?>"
                    placeholder="Masukkan email"
                >
            </div>

            <div class="field">
                <label for="member-phone">Nomor HP</label>

                <input
                    type="text"
                    id="member-phone"
                    name="phone"
                    value="<?= e($editMember['phone'] ?? '') ?>"
                    placeholder="Masukkan nomor HP"
                >
            </div>

            <div class="field">
                <label for="member-password">
                    Password <?= $editMember ? '(Kosongkan jika tidak diubah)' : '' ?>
                </label>

                <input
                    type="password"
                    id="member-password"
                    name="password"
                    minlength="8"
                    <?= $editMember ? '' : 'required' ?>
                    placeholder="<?= $editMember
                        ? 'Password baru (opsional)'
                        : 'Minimal 8 karakter' ?>"
                >
            </div>

        </div>

        <div class="field">
            <label for="member-address">Alamat</label>

            <textarea
                id="member-address"
                name="address"
                placeholder="Masukkan alamat member"
            ><?= e($editMember['address'] ?? '') ?></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">
                <?= $editMember ? 'Simpan Perubahan' : 'Tambah Member' ?>
            </button>

            <?php if ($editMember): ?>
                <a
                    href="<?= e(app_url('pages/admin/members.php')) ?>"
                    class="btn btn-outline"
                >Batal</a>
            <?php endif; ?>
        </div>

    </form>

</section>

<section class="panel">

    <div class="panel-head">
        <h2>Daftar member</h2>
        <span class="hint"><?= count($members) ?> akun</span>

        <input
            type="search"
            id="searchMember"
            class="search-input"
            placeholder="Cari nama atau email"
            aria-label="Cari member"
        >
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="number">No</th>
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
                    <td colspan="6" class="empty">Belum ada data member.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $index => $member): ?>
                    <?php $isActive = ($member['status'] ?? '') === 'active'; ?>
                    <tr>
                        <td class="number"><?= $index + 1 ?></td>
                        <td>
                            <strong><?= e($member['name'] ?? '-') ?></strong>
                            <span class="id">#<?= (int) ($member['id_user'] ?? 0) ?></span>
                        </td>
                        <td><?= e($member['email'] ?? '-') ?></td>
                        <td class="number"><?= e(($member['phone'] ?? '') !== '' ? $member['phone'] : '—') ?></td>
                        <td>
                            <span class="badge <?= $isActive ? 'badge-approved' : 'badge-rejected' ?>">
                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a
                                    class="btn btn-secondary btn-sm"
                                    href="<?= e(app_url('pages/admin/members.php?edit=' . urlencode((string) $member['id_user']))) ?>"
                                >Edit</a>

                                <button
                                    type="button"
                                    class="btn <?= $isActive ? 'btn-danger' : 'btn-secondary' ?> btn-sm status-button"
                                    data-id="<?= (int) $member['id_user'] ?>"
                                    data-status="<?= $isActive ? 'inactive' : 'active' ?>"
                                ><?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<script>
const messageBox = document.getElementById('message');
const membersUrl = <?= json_encode(app_url('pages/admin/members.php')) ?>;
const updateUrl = <?= json_encode(app_url('handlers/member/update.php')) ?>;
const createUrl = <?= json_encode(app_url('handlers/member/create.php')) ?>;
const statusUrl = <?= json_encode(app_url('handlers/member/status.php')) ?>;

function showMessage(text, success) {
    messageBox.textContent = text;
    messageBox.className = 'notice ' + (success ? 'notice-success' : 'notice-error');
    messageBox.hidden = false;

    if (success && window.flashMessage) {
        window.flashMessage(text, true);
    }
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

document.getElementById('memberForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    const isEdit = formData.get('id_user') !== '';

    try {
        const result = await sendRequest(isEdit ? updateUrl : createUrl, formData);
        showMessage(result.message, true);

        setTimeout(() => {
            window.location.href = membersUrl;
        }, 700);
    } catch (error) {
        showMessage(error.message, false);
    }
});

document.querySelectorAll('.status-button').forEach(button => {
    button.addEventListener('click', async function () {
        const status = this.dataset.status;

        const confirmation = status === 'inactive'
            ? 'Klik lagi untuk menonaktifkan'
            : 'Klik lagi untuk mengaktifkan';

        if (!window.armAction(this, confirmation)) return;

        const formData = new FormData();
        formData.append('id_user', this.dataset.id);
        formData.append('status', status);

        try {
            const result = await sendRequest(statusUrl, formData);
            showMessage(result.message, true);
            setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            showMessage(error.message, false);
        }
    });
});

document.getElementById('searchMember').addEventListener('input', function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll('#memberTable tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(keyword)
            ? ''
            : 'none';
    });
});
</script>

<?php require __DIR__ . '/../partials/dashboard-footer.php'; ?>
