---
name: Digital Library
description: Perpustakaan digital dengan rak berlabel — nomor panggil, pita kategori, lisensi, dan sisa masa pinjam bekerja sebagai alat, bukan hiasan.
colors:
  primary: "#2563eb"
  primary-deep: "#1d4ed8"
  primary-soft: "#eff6ff"
  navy: "#172554"
  sky: "#075985"
  sky-soft: "#e0f2fe"
  ink-strong: "#111827"
  ink: "#1f2937"
  text: "#374151"
  text-soft: "#4b5563"
  muted: "#6b7280"
  faint: "#9ca3af"
  line: "#e5e7eb"
  line-strong: "#d1d5db"
  page: "#f5f7fb"
  surface: "#ffffff"
  rail-text: "#e2e8f0"
  rail-muted: "#93a3bd"
  band-navy: "#172554"
  band-blue: "#2563eb"
  band-sky: "#075985"
  band-slate: "#93a3bd"
  stamp-pending-bg: "#fef3c7"
  stamp-pending-fg: "#92400e"
  stamp-approved-bg: "#dcfce7"
  stamp-approved-fg: "#166534"
  stamp-rejected-bg: "#fee2e2"
  stamp-rejected-fg: "#991b1b"
  stamp-completed-bg: "#eff6ff"
  stamp-completed-fg: "#1d4ed8"
  stamp-active-bg: "#e0f2fe"
  stamp-active-fg: "#075985"
  stamp-pending-edge: "#fde68a"
  stamp-approved-edge: "#bbf7d0"
  stamp-rejected-edge: "#fecaca"
  stamp-active-edge: "#bae6fd"
  notice-success-bg: "#ecfdf5"
  notice-success-edge: "#a7f3d0"
  notice-success-fg: "#065f46"
  notice-error-bg: "#fef2f2"
typography:
  display:
    fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif"
    fontSize: "clamp(2.1rem, 1.4rem + 2.6vw, 3.3rem)"
    fontWeight: 700
    lineHeight: 1.05
    letterSpacing: "-0.04em"
  heading:
    fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif"
    fontSize: "clamp(1.55rem, 1.25rem + 1vw, 1.9rem)"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "-0.02em"
  title:
    fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif"
    fontSize: "0.95rem"
    fontWeight: 650
    lineHeight: 1.35
    letterSpacing: "normal"
  body:
    fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: "normal"
  label:
    fontFamily: "-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif"
    fontSize: "0.7rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.1em"
  call-code:
    fontFamily: "ui-monospace, \"Cascadia Mono\", \"Segoe UI Mono\", Consolas, \"Liberation Mono\", monospace"
    fontSize: "0.72rem"
    fontWeight: 400
    lineHeight: 1.4
    letterSpacing: "0.02em"
rounded:
  sm: "3px"
  badge: "4px"
  control: "6px"
  base: "8px"
  tile: "10px"
  card: "12px"
  panel: "14px"
  feature: "16px"
  pill: "999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "22px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.base}"
    padding: "0 16px"
    height: "40px"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text}"
    rounded: "{rounded.base}"
    padding: "0 16px"
    height: "40px"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.primary-deep}"
    rounded: "{rounded.base}"
    padding: "0 16px"
    height: "40px"
  button-danger:
    backgroundColor: "transparent"
    textColor: "{colors.stamp-rejected-fg}"
    rounded: "{rounded.base}"
    padding: "0 16px"
    height: "40px"
  badge:
    backgroundColor: "{colors.page}"
    textColor: "{colors.text-soft}"
    rounded: "{rounded.badge}"
    padding: "3px 8px"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.base}"
    padding: "10px 12px"
    height: "42px"
  book-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
  panel:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.panel}"
    padding: "20px"
  nav-link:
    backgroundColor: "transparent"
    textColor: "{colors.rail-text}"
    rounded: "{rounded.control}"
    padding: "9px 12px"
  nav-link-active:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "9px 12px"
---

## Overview

Koleksi diperlakukan sebagai **rak berlabel**. Setiap buku membawa nomor panggil, pita kategori, angka lisensi, dan stempel ketersediaan; label itu dipakai untuk memutuskan, bukan untuk mengisi ruang. Struktur halaman mengikuti pekerjaan nyata: menyaring rak, memilih buku, membaca sisa masa pinjam, lalu memutuskan antrean di admin.

Dunia visualnya adalah ruang baca kampus yang tenang: kanvas abu-biru `#f5f7fb` sebagai lantai, permukaan putih sebagai buku, navy `#172554` sebagai rel navigasi dan alas cetak, dan satu aksen biru `#2563eb` untuk tindakan serta pilihan. Tidak ada gradien dekoratif, ilustrasi karangan, atau kartu seragam berisi ikon + judul + paragraf.

Yang harus terasa: ketertiban arsip. Kode, tanggal, ID, dan lisensi memakai angka tabular dan mono; semuanya rata dan bisa dibandingkan sekilas.

## Colors

Aksen tunggal. `#2563eb` hanya untuk aksi utama, tautan aktif, penanda pilihan, dan cincin fokus; `#1d4ed8` untuk teks tautan dan hover; `#eff6ff` sebagai latar lembut (sampul, badge selesai).

Empat pita kategori, semuanya dari palet incumbent:

| Pita | Nilai | Teks |
| --- | --- | --- |
| `band-1` | `#172554` navy | putih |
| `band-2` | `#2563eb` biru | putih |
| `band-3` | `#075985` sky | putih |
| `band-4` | `#93a3bd` abu-biru | `#1f2937` |

Stempel status memakai pasangan latar/teks tetap, tanpa opacity: menunggu `#fef3c7`/`#92400e`, disetujui `#dcfce7`/`#166534`, ditolak `#fee2e2`/`#991b1b`, selesai `#eff6ff`/`#1d4ed8`, aktif `#e0f2fe`/`#075985`.

Garis memisahkan, bukan menghias: `#e5e7eb` untuk pemisah biasa, `#d1d5db` untuk batas kontrol dan kepala tabel. Teks memakai tangga `#111827` → `#1f2937` → `#374151` → `#4b5563` → `#6b7280` → `#9ca3af`; jangan memakai abu di luar tangga ini.

## Typography

System UI stack tanpa webfont; bobot 650–700 untuk judul, 400–500 untuk teks, 600–700 untuk label. Angka tanggal, ID, lisensi, dan statistik selalu `font-variant-numeric: tabular-nums`.

- **Display** hanya di beranda: `clamp(2.1rem, 1.4rem + 2.6vw, 3.3rem)`, `line-height 1.05`, tracking `-0.04em`.
- **Heading** halaman: `clamp(1.55rem, 1.25rem + 1vw, 1.9rem)`.
- **Label** small-caps 0.66–0.72rem, tracking 0.06–0.1em, uppercase — dipakai untuk kepala tabel, label sidebar, eyebrow, dan label stat.
- **Mono** hanya untuk nomor panggil (`NO·003`) dan ID transaksi/entitas (`#11`).

Paragraf dibatasi 46–70ch; `text-wrap: balance` untuk judul, `pretty` untuk paragraf.

## Layout

- Wadah publik: `min(1160px, 92%)`, rata tengah.
- Shell aplikasi: grid `250px minmax(0, 1fr)`; sidebar sticky setinggi viewport; konten utama mengisi sisa lebar dengan padding `clamp(20px, 4vw, 38px)`.
- Beranda: dua kolom `1.05fr / 0.95fr` dengan rak pratinjau di kanan; rak turun ke bawah judul di bawah 900px.
- Rak buku: `repeat(auto-fill, minmax(210px, 1fr))`, jarak `22px 18px`; sampul `4 / 5`.
- Statistik: satu panel bergaris dengan pembatas 1px antar sel (`repeat(auto-fit, minmax(148px, 1fr))`), bukan kartu terpisah.
- Tabel: lebar penuh di dalam `.table-wrap` yang bisa menggeser horizontal; kolom angka rata kanan, kolom aksi tidak pernah membungkus.
- Breakpoint: `900px` sidebar jadi off-canvas + scrim; `800px` navbar publik; `760px` grid dua kolom jadi satu; `560px` padding dan tipografi menyusut.

## Elevation & Depth

Sistem ini rata. Kedalaman datang dari garis 1px dan perbedaan permukaan, bukan bayangan:

- Kartu, panel, dan tabel memakai `1px solid #e5e7eb` di atas `#f5f7fb` atau putih.
- Satu-satunya bayangan tetap: `--shadow-float: 0 18px 40px -24px rgba(17, 24, 39, 0.35)` untuk lapisan yang benar-benar mengambang (sidebar mobile dan kanvas pembaca).
- Fokus memakai cincin `0 0 0 3px rgba(37, 99, 235, 0.22)` plus garis aksen; tidak ada glow.
- Hover mengubah warna garis/latar, tidak mengangkat elemen (`translateY` dilarang).

## Shapes

Sudut mengikuti ukuran objek: 4px untuk stempel dan pita, 6px untuk tautan navigasi dan kontrol kecil, 8px (`--radius`) untuk tombol dan input, 10px untuk baris cepat dan bar pembaca, 12px untuk kartu buku, 14px untuk panel dan statistik, 16px untuk kartu auth, rak pratinjau, dan detail, 999px hanya untuk meter.

Bentuk tegas: pita dan stempel persegi bersudut kecil, bukan pil. Tidak ada sudut acak di luar tangga ini, tidak ada border-radius pada tabel.

## Components

### Button

`.btn` = inline-flex, tinggi minimum 40px, padding `0 16px`, radius 8px, font 0.9rem/600, ikon SVG 16px. Varian: `primary` (aksen penuh), `secondary` (putih + garis `#d1d5db`), `outline` (transparan + garis aksen), `quiet` (transparan tanpa garis), `danger` (transparan + garis, teks `#991b1b`, hover latar `#fee2e2`), `light` (putih di atas navy). `btn-sm` = 32px, padding `0 10px`, 0.8rem.

Aksi destruktif selalu `danger` dan dipisah dari aksi utama; hanya satu tombol `primary` per tampilan.

### Stamp (badge)

`.badge` = inline-flex, radius 4px, padding `3px 8px`, 0.72rem/700, `white-space: nowrap`, garis 1px sewarna keluarga. Dipakai untuk status transaksi dan ketersediaan lisensi. Teks status berbahasa Indonesia apa adanya: Menunggu, Disetujui, Ditolak, Dibatalkan, Selesai, Lewat tenggat.

### Book card

`article.book-card` (katalog) atau `label.book-card` (peminjaman) — sampul `4/5` dengan inisial atau cover asli, stempel ketersediaan di pojok, pita kategori + nomor panggil mono, judul dua baris, penulis, deskripsi, dan kaki berisi lisensi `8/10` serta aksi. Di katalog seluruh kartu adalah tautan overlay (`.book-card-link`, inset 0); di peminjaman seluruh kartu adalah label yang mencentang checkbox `.book-check` di kaki. Kartu terpilih: garis aksen + cincin fokus; kartu habis: latar `#f5f7fb`, checkbox nonaktif.

### Panel & stat

`.panel` = putih, garis 1px, radius 14px, padding 20px, dengan `.panel-head` berisi judul dan aksi (tombol kecil atau input pencarian). `.stats` = satu panel yang dibagi garis 1px; tiap `.stat` berisi label small-caps, angka 1.9rem tabular, dan catatan 0.76rem.

### Table

Ledger, bukan kartu: kepala tabel uppercase 0.68rem berwarna `#6b7280` dengan garis bawah `#d1d5db`; baris berpemisah `#e5e7eb`; hover baris memakai `#f5f7fb`; sel pertama tanpa padding kiri. ID berupa `#11` mono; judul tebal dengan ID di baris kedua.

### Dialog

`<dialog class="dialog">` native untuk konfirmasi tindakan yang mengubah data — bukan editor baris di dalam tabel. Panel putih radius 12px, padding 22px, garis 1px, `--shadow-float`, lebar maksimum 440px, di atas backdrop `rgba(23, 37, 84, 0.45)`. Susunan: `.dialog-eyebrow` small-caps 0.68rem untuk konteks (mis. `Transaksi #10`), `h2` pertanyaan singkat, `.dialog-note` berisi ringkasan target, field opsional, lalu `.dialog-actions` rata kanan dengan Batal (`secondary`) dan aksi utama (`primary`, atau `danger` untuk penolakan). Ditutup lewat Batal, Escape, atau klik backdrop; fokus kembali ke tombol pemicu.

### Due meter

`.due` = grid dengan tanggal tabular dan catatan; `.meter` = track 999px setinggi 4px berisi sisa masa pinjam. `is-low` memakai aksen, `is-overdue` memakai `#991b1b` untuk teks dan meter. Meter selalu disertai teks sisa hari — warna bukan satu-satunya penanda.

### Navigation

Publik: navbar tipis dengan logo rak + empat tautan teks; tautan aktif `aria-current="page"` berwarna aksen. Aplikasi: sidebar navy 250px dengan brand, label bagian, tautan berikon, dan kaki berisi identitas + tombol keluar; tautan aktif berlatar aksen penuh. Di bawah 900px sidebar menjadi laci dengan scrim, ditutup lewat tombol, scrim, Escape, atau pemilihan tautan.

## Do's and Don'ts

### Do:
- **Do** pakai satu aksen `#2563eb` untuk tindakan; turunkan ke `#1d4ed8` hanya untuk teks tautan/hover.
- **Do** tampilkan nomor panggil, lisensi, dan sisa masa pinjam sebagai teks nyata, bukan ikon.
- **Do** pakai angka tabular untuk tanggal, ID, lisensi, dan statistik.
- **Do** pertahankan garis 1px sebagai pemisah utama; gunakan `--shadow-float` hanya untuk lapisan mengambang.
- **Do** tandai keadaan terpilih dengan garis aksen + cincin, dan sertai warna dengan teks.

### Don't:
- **Don't** memakai gradien, bayangan berlapis, atau animasi angkat pada hover.
- **Don't** membuat kartu statistik terpisah-pisah; statistik adalah satu panel bergaris.
- **Don't** memakai pil untuk stempel status, pita kategori, atau tombol.
- **Don't** menambah warna di luar palet: hue baru, ungu, hijau dekoratif, atau abu di luar tangga tinta.
- **Don't** memakai emoji sebagai ikon; ikon adalah SVG garis 1.6 dengan ukuran 16–18px.
