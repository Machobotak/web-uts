# Digital Library

Perpustakaan digital mandiri: katalog e-book yang bisa dicari, dipinjam dengan masa berlaku, dan dibaca langsung di browser tanpa mengunduh berkas. Akses baca dihitung di server — satu judul punya sejumlah lisensi, pinjaman memegang satu lisensi sampai dikembalikan atau lewat jatuh tempo.

Aplikasi PHP tanpa framework dan tanpa build step. Data disimpan di berkas JSON, antarmuka berbahasa Indonesia.

## Akun demo

Akun di bawah ini sudah ada di `data/users.json` dan sudah diuji bisa login lewat `pages/auth/login.php`.

| Peran | Email | Password | Status |
| --- | --- | --- | --- |
| Admin | `admin@library.com` | `admin123` | aktif |
| Member | `test@gmail.com` | `12345678` | aktif |

Setelah login, admin diarahkan ke `pages/admin/dashboard.php`, member ke `pages/member/dashboard.php`.

Catatan:

- Ini kredensial demo yang tersimpan sebagai hash di `data/users.json` (repo ini). Jangan dipakai di server yang bisa diakses publik; ganti atau hapus datanya sebelum dipasang.
- Akun lain di `data/users.json` (`member@test.com`, `frontend@test.com`, `testmember@example.com`) juga aktif, tetapi password-nya tidak didokumentasikan di sini. Dua akun berstatus `inactive` (`memberbaru@test.com`, `memberbaru@example.com`) akan ditolak saat login.
- Member baru bisa mendaftar sendiri lewat `pages/auth/register.php` — password minimal 8 karakter, email harus belum terdaftar.

## Kebutuhan

- PHP 8.0 atau lebih baru (`str_contains`, `str_starts_with`), diuji pada PHP 8.5.
- Ekstensi `mbstring` (dipakai untuk inisial judul di rak).
- Tidak butuh database, Composer, atau Node.

## Menjalankan

Document root harus **root repo**, bukan `public/`, karena halaman di `pages/` dan `handlers/` me-`require` `config/bootstrap.php` lewat path relatif.

```bash
php -S localhost:8000 -t .
```

Buka <http://localhost:8000/>. `index.php` di root hanya mengarahkan ke `pages/public/home.php`.

URL penting:

| Halaman | URL |
| --- | --- |
| Beranda | `/pages/public/home.php` |
| Katalog | `/pages/public/catalog.php` |
| Login / daftar | `/pages/auth/login.php`, `/pages/auth/register.php` |
| Dashboard admin | `/pages/admin/dashboard.php` |
| Dashboard member | `/pages/member/dashboard.php` |

Aplikasi juga bisa dipasang di subfolder (mis. `C:/laragon/www/web-uts` lewat `http://localhost/web-uts/`) karena `app_url()` menyesuaikan prefix dari `SCRIPT_NAME`.

## Struktur

| Path | Isi |
| --- | --- |
| `index.php` | Pengalihan root ke beranda publik |
| `config/bootstrap.php` | Autoload `App\`, session, `app_url()`, `asset_url()`, `wants_json()`, instance `JsonStorage` |
| `src/Models/` | Entity: `User`, `Admin`, `Member`, `Category`, `Ebook`, `Transaction`, `TransactionDetail` |
| `src/Services/` | Logika bisnis: `AuthService`, `CatalogService`, `MemberService`, `ProfileService`, `TransactionService` |
| `src/Storage/` | `JsonStorage` — baca/tulis `data/*.json` dengan lock |
| `src/Middleware/` | `AuthMiddleware` — `requireLogin()` dan `requireRole('admin'|'member')` |
| `src/Support/` | `Shelf` — nomor panggil, pita kategori, format tanggal, sisa masa pinjam |
| `handlers/` | Endpoint JSON per aksi (auth, ebook, category, member, profile, transaction) |
| `pages/public/` | Beranda, katalog, detail e-book |
| `pages/auth/` | Login, registrasi |
| `pages/member/` | Dashboard, rak peminjaman, riwayat, profil, pembaca |
| `pages/admin/` | Dashboard, ebook, kategori, member, transaksi, detail transaksi |
| `pages/partials/` | Header/footer publik dan shell dashboard |
| `public/assets/` | CSS, JS, dan pdf.js |
| `data/` | "Database" JSON: `users`, `categories`, `ebooks`, `transactions`, `transaction_details` |
| `storage/ebooks/` | Berkas PDF/EPUB |
| `public/uploads/covers/` | Berkas cover |

## Alur member

1. Beranda atau katalog (`?search=` dan `?category=`) → detail e-book.
2. Daftar atau login, lalu ajukan pinjam dari halaman detail (`?pilih=<id>`).
3. Pengajuan berstatus `pending` menunggu keputusan admin; member bisa membatalkannya selama masih `pending`.
4. Setelah `approved`, e-book muncul di rak `pages/member/borrowings.php` dan bisa dibaca di `pages/member/reader.php`.
5. Riwayat seluruh transaksi ada di `pages/member/history.php`; profil dan ganti password di `pages/member/profile.php`.

## Alur admin

1. Dashboard menampilkan statistik katalog dan lima pengajuan terlama yang masih menunggu.
2. `pages/admin/transactions.php` adalah antrean: setujui (`handlers/transaction/approve.php`), tolak dengan alasan (`handlers/transaction/reject.php`), atau catat pengembalian (`handlers/transaction/return.php`).
3. `pages/admin/transaction-detail.php` merinci judul dan lisensi per transaksi.
4. `pages/admin/ebooks.php` mengelola katalog termasuk unggah cover (jpg/jpeg/png/webp, maks 2 MB) dan berkas e-book (pdf/epub, maks 20 MB).
5. `pages/admin/categories.php` dan `pages/admin/members.php` merawat kategori serta akun member (termasuk menonaktifkan akun).

## Aturan peminjaman

- Masa pinjam `TransactionService::DEFAULT_LOAN_DAYS = 7` hari; `due_date` dihitung server saat pengajuan dibuat.
- Status transaksi: `pending`, `approved`, `rejected`, `cancelled`, `completed`.
- Status detail: `active`, `returned`, `overdue`, `cancelled`.
- Lisensi dipesan sejak pengajuan dibuat agar peminjaman tidak melebihi `license_limit`, dan dikembalikan saat pengajuan ditolak/dibatalkan atau e-book dikembalikan.
- Tidak ada cron: status detail tidak berubah sendiri. Pembaca menolak akses lewat `TransactionDetail::isOverdue()`, jadi e-book yang lewat jatuh tempo langsung tertutup meski datanya masih `active`.
- Hanya pemilik pinjaman yang bisa membaca; admin tidak bisa meminjam dan ditolak di halaman pembaca.

## Pembaca e-book

- pdf.js 3.11.174 (Apache-2.0) di-vendor di `public/assets/pdfjs/`, dimuat sebagai skrip klasik (UMD) sehingga jalan di browser lama tanpa ES module.
- Halaman PDF digambar ke `<canvas>` oleh `public/assets/js/reader.js`, jadi tidak ada tombol unduh atau cetak bawaan penampil PDF browser.
- Berkas dikirim lewat `pages/member/reader.php?id=<id>&stream=1` setelah hak baca diperiksa, dengan `Cache-Control: no-store` dan penolakan `Sec-Fetch-Dest: document/iframe/frame`.
- EPUB tidak dirender; halaman pembaca hanya menampilkan pesan agar meminta versi PDF ke admin.

## Catatan data

- `data/*.json` adalah sumber data sekaligus state aplikasi: setiap login, pengajuan, persetujuan, dan perubahan katalog menulis ulang berkas ini. Jangan commit perubahan yang berasal dari percobaan lokal.
- `storage/ebooks/` dan `public/uploads/covers/` di-ignore git kecuali `.gitkeep`, jadi hasil unggahan tidak ikut repo.
- Isi contoh saat ini: 3 e-book (2 kategori), 10 transaksi, 12 detail, 7 akun. Hanya `test aja` (id 3) yang berkas PDF dan cover-nya benar-benar ada di disk; judul lain menunjuk berkas yang sudah hilang sehingga tampil sebagai inisial dan gagal dibuka di pembaca. Lengkapi berkasnya lewat form admin bila perlu demo penuh.
- Karena document root ada di root repo, `data/users.json`, `config/bootstrap.php`, dan `storage/ebooks/*.pdf` bisa diakses langsung lewat URL pada server pengembangan. Untuk produksi, arahkan document root ke `public/` dan salin `pages/`, `handlers/`, `src/`, `config/`, `data/`, `storage/` di luar webroot, atau blokir path tersebut di konfigurasi server.

## Lisensi

Kode aplikasi milik proyek ini. pdf.js © Mozilla Foundation, Apache License 2.0 — lihat header lisensi di `public/assets/pdfjs/pdf.min.js`.
