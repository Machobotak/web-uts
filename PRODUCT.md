# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

PHP 8.5 (tanpa framework, front controller per halaman) + HTML/CSS/JS vanilla; data di `data/*.json` lewat `src/Storage/JsonStorage.php`; aset di `public/assets/`; ebook PDF/EPUB di `storage/ebooks/`. Palet warna dan stack adalah batasan mengikat dari pengguna.

## Users

- **Member (pemakai utama, publik umum):** orang yang menemukan perpustakaan digital ini sendiri, mendaftar akun, lalu menjelajah katalog, mengajukan peminjaman ebook, membaca ebook di dalam browser, dan memantau jatuh tempo serta riwayatnya. Tidak ada pustakawan yang mendampingi; seluruh alur harus bisa diselesaikan sendiri.
- **Admin:** pengelola yang menyetujui/menolak permintaan pinjam, mencatat pengembalian, dan merawat data ebook, kategori, serta member.

## Product Purpose

Perpustakaan digital mandiri: katalog ebook yang bisa dicari, dipinjam dengan masa berlaku, dan dibaca langsung di browser tanpa mengunduh berkas. Sukses berarti member bisa berpindah dari "menemukan buku" ke "sedang membaca" tanpa hambatan dan tanpa bertanya ke admin, sementara admin bisa menuntaskan antrean persetujuan dengan cepat.

## Positioning

Peminjaman berbasis lisensi dengan batas waktu yang ditegakkan di server: satu ebook punya sejumlah lisensi, detail pinjaman memegang lisensi sampai dikembalikan atau lewat `due_date`, dan pembaca menolak akses begitu masa pinjam berakhir. Bukan toko ebook, bukan pembaca PDF biasa — hak baca adalah keadaan yang dihitung, bukan tautan yang dibagikan.

## Operating Context

- Dipakai di browser desktop maupun ponsel; banyak member membuka dari perangkat pribadi dengan koneksi biasa.
- Alur member: katalog publik → detail ebook → registrasi/login → ajukan pinjam (bisa beberapa ebook sekaligus) → tunggu persetujuan admin → baca di halaman pembaca → kembalikan/kadaluarsa.
- Alur admin: dashboard ringkas → antrean transaksi `pending` → setujui/tolak → catat pengembalian → rawat katalog dan member.
- Data contoh di `data/` dan cover di `public/uploads/covers/` adalah isi nyata yang dipakai saat demo; tidak ada konten marketing yang boleh diciptakan.

## Capabilities and Constraints

- Peran: `admin` dan `member`; halaman dijaga `AuthMiddleware::requireRole()`.
- Katalog: pencarian kata kunci (judul/penulis/deskripsi) dan filter kategori.
- Peminjaman: `TransactionService::createBorrowing()` dengan `DEFAULT_LOAN_DAYS = 7`; lisensi dihitung dari `license_limit` ebook.
- Status transaksi: `pending`, `approved`, `rejected`, `cancelled`, `completed`; status detail: `active`, `returned`, `overdue`, `cancelled`.
- Jatuh tempo ditegakkan di server lewat `TransactionDetail::isOverdue()` karena status detail tidak pernah berubah otomatis (tidak ada cron).
- Pembaca: pdf.js yang di-vendor, halaman digambar ke `<canvas>`, tanpa tombol unduh/cetak; EPUB tidak dapat dirender dan hanya diberi pesan.
- Endpoint `?stream=1` menolak permintaan bernavigasi (`Sec-Fetch-Dest: document/iframe/frame`) dan memakai `Cache-Control: no-store`.
- Batasan teknis mengikat: tetap PHP + HTML + CSS + JS tanpa framework/build step; palet warna yang ada dipertahankan; tidak menambah dependensi server (tanpa Ghostscript/Imagick).

## Brand Commitments

- Nama produk "Digital Library" dan bahasa antarmuka Indonesia.
- Palet warna incumbent dipertahankan: biru `#2563eb` / `#1d4ed8`, biru muda `#eff6ff`, navy `#172554`, abu tinta `#111827`–`#9ca3af`, kanvas `#f5f7fb`, permukaan putih.

## Evidence on Hand

- Isi katalog nyata: `data/ebooks.json`, `data/categories.json`, cover `public/uploads/covers/`.
- Akun demo: member `test@gmail.com`, admin di `data/users.json`.
- Berkas ebook uji: `storage/ebooks/ebook_ec6f284900eadd6b.pdf` (8 halaman).
- Tidak ada testimoni, jumlah pengguna, penghargaan, atau metrik yang boleh dikarang.

## Product Principles

1. Alur pinjam sampai baca harus selesai tanpa bertanya ke admin.
2. Hak baca adalah keadaan yang dihitung server, dan UI harus selalu menyatakan sisanya dengan jelas.
3. Antrean admin adalah pekerjaan, bukan halaman pajangan: satu keputusan per baris, tanpa pindah halaman.
4. Katalog adalah tempat menemukan, bukan etalase jualan; yang tampil adalah bukti isi (cover, penulis, kategori, ketersediaan).
5. Tidak ada elemen yang hanya hiasan bila tidak membantu menemukan, meminjam, atau membaca.

## Accessibility & Inclusion

- Bahasa Indonesia, teks status tidak boleh mengandalkan warna saja (badge selalu berisi kata).
- Target: markup semantik, kontras teks minimal WCAG AA, navigasi keyboard penuh termasuk pembaca ebook, dan layout yang tetap utuh pada 360px.
