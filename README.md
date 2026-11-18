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

Pembaca ebook memakai pdf.js 4.10.38 (Apache-2.0) yang di-vendor di
`public/assets/pdfjs/` — halaman PDF digambar ke `<canvas>` sehingga tidak
ada tombol unduh/cetak bawaan penampil PDF browser.

## Menjalankan

```bash
php -S localhost:8000 -t public
```

Buka http://localhost:8000. `index.php` di root hanya mengarahkan ke `public/`.
