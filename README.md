# Digital Library

Aplikasi perpustakaan digital berbasis PHP dengan penyimpanan JSON (tanpa database).

## Struktur

| Folder | Isi |
| --- | --- |
| `config/` | Bootstrap aplikasi (autoload, session, konfigurasi path) |
| `src/Models/` | Entity: User, Admin, Member, Category, Ebook, Transaction, TransactionDetail |
| `src/Services/` | Logika bisnis: AuthService, CatalogService, TransactionService |
| `src/Storage/` | JsonStorage — baca/tulis file JSON |
| `handlers/` | Endpoint POST (auth, ebook, category, member, transaction) |
| `pages/` | View per role: public, auth, member, admin |
| `public/` | Document root — `index.php`, assets, uploads |
| `data/` | "Database" JSON: users, categories, ebooks, transactions |
| `storage/` | File ebook (PDF/EPUB) |

Pembaca ebook memakai pdf.js 3.11.174 (Apache-2.0) yang di-vendor di
`public/assets/pdfjs/` dan dimuat sebagai skrip klasik (UMD), sehingga tetap
jalan di browser lama tanpa dukungan ES module. Halaman PDF digambar ke
`<canvas>`, jadi tidak ada tombol unduh/cetak bawaan penampil PDF browser.

## Menjalankan

```bash
php -S localhost:8000 -t public
```

Buka http://localhost:8000. `index.php` di root hanya mengarahkan ke `public/`.
