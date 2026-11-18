<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Ebook;
use App\Storage\JsonStorage;

class CatalogService
{
    /** Folder penyimpanan cover ebook, relatif terhadap root proyek. */
    public const COVER_DIRECTORY = 'public/uploads/covers';

    /** Folder penyimpanan file ebook, relatif terhadap root proyek. */
    public const EBOOK_DIRECTORY = 'storage/ebooks';

    private const COVER_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const COVER_MAX_SIZE = 2097152; // 2 MB

    private const EBOOK_EXTENSIONS = ['pdf', 'epub'];
    private const EBOOK_MAX_SIZE = 20971520; // 20 MB

    private JsonStorage $storage;
    private string $categoryFile = 'categories.json';
    private string $ebookFile = 'ebooks.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil seluruh data kategori diurutkan berdasarkan nama.
     */
    public function getAllCategories(): array
    {
        $categories = $this->storage->read($this->categoryFile);

        usort($categories, function (array $a, array $b) {
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });

        return $categories;
    }

    /**
     * Mengambil kategori beserta jumlah ebook yang memakainya.
     */
    public function getCategoriesWithEbookCount(): array
    {
        $counts = [];

        foreach ($this->readEbooks() as $ebook) {
            $id = (int) ($ebook['id_category'] ?? 0);

            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        $categories = $this->getAllCategories();

        foreach ($categories as $index => $category) {
            $id = (int) ($category['id_category'] ?? 0);

            $categories[$index]['ebook_count'] = $counts[$id] ?? 0;
        }

        return $categories;
    }

    /**
     * Mencari kategori berdasarkan ID.
     */
    public function findCategoryById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        foreach ($this->getAllCategories() as $category) {
            if ((int) ($category['id_category'] ?? 0) === $id) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Mencari kategori berdasarkan nama.
     */
    public function findCategoryByName(
        string $name,
        ?int $excludeId = null
    ): ?array {
        $name = strtolower(trim($name));

        foreach ($this->getAllCategories() as $category) {
            $sameName = strtolower(trim($category['name'] ?? '')) === $name;
            $differentId = (int) ($category['id_category'] ?? 0) !== $excludeId;

            if ($sameName && $differentId) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Membuat ID kategori berikutnya.
     */
    private function generateCategoryId(): int
    {
        $categories = $this->storage->read($this->categoryFile);

        if (empty($categories)) {
            return 1;
        }

        return max(array_map(
            fn($category) => (int) ($category['id_category'] ?? 0),
            $categories
        )) + 1;
    }

    /**
     * Menambahkan kategori baru.
     */
    public function createCategory(array $data): Category
    {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama kategori wajib diisi.'
            );
        }

        if ($this->findCategoryByName($name) !== null) {
            throw new \InvalidArgumentException(
                'Nama kategori sudah digunakan.'
            );
        }

        $category = new Category([
            'id_category' => $this->generateCategoryId(),
            'name' => $name,
            'description' => $description
        ]);

        $categories = $this->storage->read($this->categoryFile);
        $categories[] = $category->toArray();

        $this->storage->write($this->categoryFile, $categories);

        return $category;
    }

    /**
     * Mengubah data kategori.
     */
    public function updateCategory(int $id, array $data): Category
    {
        $categories = $this->storage->read($this->categoryFile);
        $categoryIndex = null;

        foreach ($categories as $index => $category) {
            if ((int) ($category['id_category'] ?? 0) === $id) {
                $categoryIndex = $index;
                break;
            }
        }

        if ($categoryIndex === null) {
            throw new \InvalidArgumentException(
                'Kategori tidak ditemukan.'
            );
        }

        $current = $categories[$categoryIndex];

        $name = trim($data['name'] ?? $current['name'] ?? '');
        $description = trim(
            $data['description'] ?? $current['description'] ?? ''
        );

        if ($name === '') {
            throw new \InvalidArgumentException(
                'Nama kategori wajib diisi.'
            );
        }

        if ($this->findCategoryByName($name, $id) !== null) {
            throw new \InvalidArgumentException(
                'Nama kategori sudah digunakan.'
            );
        }

        $category = new Category($current);

        $category->setName($name);
        $category->setDescription($description);

        $categories[$categoryIndex] = $category->toArray();

        $this->storage->write($this->categoryFile, $categories);

        return $category;
    }

    /**
     * Menghapus kategori.
     */
    public function deleteCategory(int $id): void
    {
        $categories = $this->storage->read($this->categoryFile);
        $categoryIndex = null;

        foreach ($categories as $index => $category) {
            if ((int) ($category['id_category'] ?? 0) === $id) {
                $categoryIndex = $index;
                break;
            }
        }

        if ($categoryIndex === null) {
            throw new \InvalidArgumentException(
                'Kategori tidak ditemukan.'
            );
        }

        $totalEbook = $this->countEbooksByCategory($id);

        if ($totalEbook > 0) {
            throw new \InvalidArgumentException(
                'Kategori tidak dapat dihapus karena masih digunakan oleh '
                . $totalEbook . ' ebook.'
            );
        }

        array_splice($categories, $categoryIndex, 1);

        $this->storage->write($this->categoryFile, $categories);
    }

    /**
     * Menghitung jumlah ebook pada kategori tertentu.
     */
    public function countEbooksByCategory(int $categoryId): int
    {
        $total = 0;

        foreach ($this->readEbooks() as $ebook) {
            if ((int) ($ebook['id_category'] ?? 0) === $categoryId) {
                $total++;
            }
        }

        return $total;
    }

    /**
     * Membuat peta ID kategori ke nama kategori.
     */
    public function getCategoryNameMap(): array
    {
        $map = [];

        foreach ($this->getAllCategories() as $category) {
            $map[(int) ($category['id_category'] ?? 0)] =
                $category['name'] ?? '';
        }

        return $map;
    }

    /*
    |--------------------------------------------------------------------------
    | Ebook
    |--------------------------------------------------------------------------
    */

    /**
     * Membaca data ebook apa adanya tanpa pengurutan.
     */
    private function readEbooks(): array
    {
        return $this->storage->read($this->ebookFile);
    }

    /**
     * Mengambil seluruh ebook, data terbaru lebih dulu.
     */
    public function getAllEbooks(): array
    {
        $ebooks = $this->readEbooks();

        usort($ebooks, function (array $a, array $b) {
            return (int) ($b['id_ebook'] ?? 0)
                <=> (int) ($a['id_ebook'] ?? 0);
        });

        return $ebooks;
    }

    /**
     * Mencari ebook berdasarkan ID.
     */
    public function findEbookById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        foreach ($this->readEbooks() as $ebook) {
            if ((int) ($ebook['id_ebook'] ?? 0) === $id) {
                return $ebook;
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah ISBN sudah dipakai ebook lain.
     */
    private function isbnExists(string $isbn, ?int $excludeId = null): bool
    {
        $isbn = strtolower(trim($isbn));

        if ($isbn === '') {
            return false;
        }

        foreach ($this->readEbooks() as $ebook) {
            $sameIsbn = strtolower(trim($ebook['isbn'] ?? '')) === $isbn;
            $differentId = (int) ($ebook['id_ebook'] ?? 0) !== $excludeId;

            if ($sameIsbn && $differentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mencari ebook berdasarkan kata kunci, kategori, dan status.
     */
    public function searchEbooks(array $filters = []): array
    {
        $keyword = strtolower(trim($filters['q'] ?? ''));
        $categoryId = (int) ($filters['category'] ?? 0);
        $status = trim($filters['status'] ?? '');

        return array_values(array_filter(
            $this->getAllEbooks(),
            function (array $ebook) use ($keyword, $categoryId, $status) {
                if (
                    $categoryId > 0 &&
                    (int) ($ebook['id_category'] ?? 0) !== $categoryId
                ) {
                    return false;
                }

                if ($status !== '' && ($ebook['status'] ?? '') !== $status) {
                    return false;
                }

                if ($keyword === '') {
                    return true;
                }

                $haystack = strtolower(implode(' ', [
                    $ebook['title'] ?? '',
                    $ebook['author'] ?? '',
                    $ebook['publisher'] ?? '',
                    $ebook['isbn'] ?? ''
                ]));

                return str_contains($haystack, $keyword);
            }
        ));
    }

    /**
     * Membuat ID ebook berikutnya.
     */
    private function generateEbookId(): int
    {
        $ebooks = $this->readEbooks();

        if (empty($ebooks)) {
            return 1;
        }

        return max(array_map(
            fn($ebook) => (int) ($ebook['id_ebook'] ?? 0),
            $ebooks
        )) + 1;
    }

    /**
     * Memvalidasi data ebook sebelum disimpan.
     */
    private function validateEbookData(
        array $data,
        ?int $excludeId = null
    ): array {
        $idCategory = (int) ($data['id_category'] ?? 0);
        $title = trim($data['title'] ?? '');
        $author = trim($data['author'] ?? '');
        $publisher = trim($data['publisher'] ?? '');
        $publicationYear = (int) ($data['publication_year'] ?? 0);
        $isbn = trim($data['isbn'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = trim($data['status'] ?? Ebook::STATUS_AVAILABLE);
        // license_total adalah kapasitas lisensi. Nilai license_limit
        // (sisa lisensi) dihitung terpisah karena ikut berubah saat dipinjam.
        // Data lama belum punya license_total sehingga memakai license_limit.
        $licenseTotal = (int) (
            $data['license_total']
                ?? $data['license_limit']
                ?? 1
        );

        if ($title === '') {
            throw new \InvalidArgumentException(
                'Judul ebook wajib diisi.'
            );
        }

        if ($author === '') {
            throw new \InvalidArgumentException(
                'Nama penulis wajib diisi.'
            );
        }

        if ($this->findCategoryById($idCategory) === null) {
            throw new \InvalidArgumentException(
                'Kategori ebook tidak ditemukan.'
            );
        }

        if (
            $publicationYear !== 0 &&
            (
                $publicationYear < 1000 ||
                $publicationYear > (int) date('Y') + 1
            )
        ) {
            throw new \InvalidArgumentException(
                'Tahun terbit tidak valid.'
            );
        }

        if (
            !in_array(
                $status,
                [Ebook::STATUS_AVAILABLE, Ebook::STATUS_UNAVAILABLE],
                true
            )
        ) {
            throw new \InvalidArgumentException(
                'Status ebook tidak valid.'
            );
        }

        if ($licenseTotal < 0) {
            throw new \InvalidArgumentException(
                'Jumlah lisensi tidak valid.'
            );
        }

        if ($this->isbnExists($isbn, $excludeId)) {
            throw new \InvalidArgumentException(
                'ISBN sudah digunakan oleh ebook lain.'
            );
        }

        return [
            'id_category' => $idCategory,
            'title' => $title,
            'author' => $author,
            'publisher' => $publisher,
            'publication_year' => $publicationYear,
            'isbn' => $isbn,
            'description' => $description,
            'status' => $status,
            'license_total' => $licenseTotal
        ];
    }

    /**
     * Menambahkan ebook baru beserta cover dan file ebook.
     */
    public function createEbook(
        array $data,
        array $coverUpload = [],
        array $ebookUpload = []
    ): Ebook {
        $validated = $this->validateEbookData($data);

        $cover = $this->storeUpload(
            $coverUpload,
            self::COVER_DIRECTORY,
            self::COVER_EXTENSIONS,
            self::COVER_MAX_SIZE,
            'cover'
        );

        $filePath = $this->storeUpload(
            $ebookUpload,
            self::EBOOK_DIRECTORY,
            self::EBOOK_EXTENSIONS,
            self::EBOOK_MAX_SIZE,
            'ebook'
        );

        $ebook = new Ebook([
            'id_ebook' => $this->generateEbookId(),
            'id_category' => $validated['id_category'],
            'title' => $validated['title'],
            'author' => $validated['author'],
            'publisher' => $validated['publisher'],
            'publication_year' => $validated['publication_year'],
            'isbn' => $validated['isbn'],
            'description' => $validated['description'],
            'cover' => $cover,
            'file_path' => $filePath,
            'status' => $validated['status'],
            // Ebook baru belum dipinjam sehingga sisa lisensi = kapasitas.
            'license_total' => $validated['license_total'],
            'license_limit' => $validated['license_total']
        ]);

        $ebooks = $this->readEbooks();
        $ebooks[] = $ebook->toArray();

        $this->storage->write($this->ebookFile, $ebooks);

        return $ebook;
    }

    /**
     * Mengubah data ebook. Cover dan file hanya diganti jika diunggah.
     */
    public function updateEbook(
        int $id,
        array $data,
        array $coverUpload = [],
        array $ebookUpload = []
    ): Ebook {
        $ebooks = $this->readEbooks();
        $ebookIndex = null;

        foreach ($ebooks as $index => $ebook) {
            if ((int) ($ebook['id_ebook'] ?? 0) === $id) {
                $ebookIndex = $index;
                break;
            }
        }

        if ($ebookIndex === null) {
            throw new \InvalidArgumentException(
                'Ebook tidak ditemukan.'
            );
        }

        $current = $ebooks[$ebookIndex];

        // Selisih kapasitas dengan sisa lisensi = salinan yang sedang dipinjam.
        $currentModel = new Ebook($current);
        $borrowed = max(
            0,
            $currentModel->getLicenseTotal() - $currentModel->getLicenseLimit()
        );

        $validated = $this->validateEbookData(
            array_merge($current, $data),
            $id
        );

        if ($validated['license_total'] < $borrowed) {
            throw new \InvalidArgumentException(
                'Jumlah lisensi tidak boleh lebih kecil dari '
                . $borrowed . ' salinan yang sedang dipinjam.'
            );
        }

        $ebook = new Ebook($current);

        $ebook->setIdCategory($validated['id_category']);
        $ebook->setTitle($validated['title']);
        $ebook->setAuthor($validated['author']);
        $ebook->setPublisher($validated['publisher']);
        $ebook->setPublicationYear($validated['publication_year']);
        $ebook->setIsbn($validated['isbn']);
        $ebook->setDescription($validated['description']);
        $ebook->setLicenseTotal($validated['license_total']);
        $ebook->setLicenseLimit($validated['license_total'] - $borrowed);

        // Status hanya diubah manual bila admin mengirimkannya; selain itu
        // status mengikuti ketersediaan sisa lisensi.
        if (array_key_exists('status', $data)) {
            $ebook->setStatus($validated['status']);
        } elseif ($ebook->hasLicenseAvailable()) {
            $ebook->setStatus(Ebook::STATUS_AVAILABLE);
        } else {
            $ebook->setStatus(Ebook::STATUS_UNAVAILABLE);
        }

        $cover = $this->storeUpload(
            $coverUpload,
            self::COVER_DIRECTORY,
            self::COVER_EXTENSIONS,
            self::COVER_MAX_SIZE,
            'cover'
        );

        if ($cover !== '') {
            $this->removeFile(self::COVER_DIRECTORY, $current['cover'] ?? '');

            $ebook->setCover($cover);
        }

        $filePath = $this->storeUpload(
            $ebookUpload,
            self::EBOOK_DIRECTORY,
            self::EBOOK_EXTENSIONS,
            self::EBOOK_MAX_SIZE,
            'ebook'
        );

        if ($filePath !== '') {
            $this->removeFile(
                self::EBOOK_DIRECTORY,
                $current['file_path'] ?? ''
            );

            $ebook->setFilePath($filePath);
        }

        $ebooks[$ebookIndex] = $ebook->toArray();

        $this->storage->write($this->ebookFile, $ebooks);

        return $ebook;
    }

    /**
     * Menyesuaikan sisa lisensi ebook dan menyelaraskan statusnya.
     * Dipakai oleh alur transaksi saat ebook dipinjam atau dikembalikan.
     *
     * @param int $delta Nilai negatif mengurangi lisensi, positif menambah.
     */
    public function adjustEbookLicense(int $idEbook, int $delta): array
    {
        $ebooks = $this->readEbooks();

        foreach ($ebooks as $index => $data) {
            if ((int) ($data['id_ebook'] ?? 0) !== $idEbook) {
                continue;
            }

            $ebook = new Ebook($data);

            $needed = -$delta;

            if ($needed > $ebook->getLicenseLimit()) {
                throw new \InvalidArgumentException(
                    'Stok lisensi ebook "' . $ebook->getTitle()
                    . '" tidak mencukupi.'
                );
            }

            // Sisa lisensi tidak boleh melampaui kapasitas totalnya.
            $limit = min(
                $ebook->getLicenseTotal(),
                $ebook->getLicenseLimit() + $delta
            );

            $ebook->setLicenseLimit(max(0, $limit));

            $ebook->setStatus(
                $ebook->hasLicenseAvailable()
                    ? Ebook::STATUS_AVAILABLE
                    : Ebook::STATUS_UNAVAILABLE
            );

            $ebooks[$index] = $ebook->toArray();

            $this->storage->write($this->ebookFile, $ebooks);

            return $ebooks[$index];
        }

        throw new \InvalidArgumentException('Ebook tidak ditemukan.');
    }

    /**
     * Menghapus ebook beserta cover dan file penyimpanannya.
     */
    public function deleteEbook(int $id): void
    {
        $ebooks = $this->readEbooks();
        $ebookIndex = null;

        foreach ($ebooks as $index => $ebook) {
            if ((int) ($ebook['id_ebook'] ?? 0) === $id) {
                $ebookIndex = $index;
                break;
            }
        }

        if ($ebookIndex === null) {
            throw new \InvalidArgumentException(
                'Ebook tidak ditemukan.'
            );
        }

        $deleted = $ebooks[$ebookIndex];

        array_splice($ebooks, $ebookIndex, 1);

        $this->storage->write($this->ebookFile, $ebooks);

        $this->removeFile(self::COVER_DIRECTORY, $deleted['cover'] ?? '');
        $this->removeFile(self::EBOOK_DIRECTORY, $deleted['file_path'] ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | File Upload
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan file upload dan mengembalikan nama filenya.
     * Mengembalikan string kosong jika tidak ada file yang diunggah.
     */
    private function storeUpload(
        array $upload,
        string $directory,
        array $allowedExtensions,
        int $maxSize,
        string $prefix
    ): string {
        $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException(
                'File gagal diunggah. Silakan coba kembali.'
            );
        }

        $tmpName = $upload['tmp_name'] ?? '';

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \InvalidArgumentException(
                'File upload tidak valid.'
            );
        }

        if ((int) ($upload['size'] ?? 0) > $maxSize) {
            throw new \InvalidArgumentException(
                'Ukuran file melebihi batas '
                . round($maxSize / 1048576) . ' MB.'
            );
        }

        $extension = strtolower(
            pathinfo($upload['name'] ?? '', PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException(
                'Format file tidak didukung. Gunakan: '
                . implode(', ', $allowedExtensions) . '.'
            );
        }

        $targetDirectory = $this->projectPath($directory);

        if (
            !is_dir($targetDirectory) &&
            !mkdir($targetDirectory, 0775, true) &&
            !is_dir($targetDirectory)
        ) {
            throw new \RuntimeException(
                'Folder penyimpanan file tidak dapat dibuat.'
            );
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(8))
            . '.' . $extension;

        if (
            !move_uploaded_file(
                $tmpName,
                $targetDirectory . DIRECTORY_SEPARATOR . $filename
            )
        ) {
            throw new \RuntimeException(
                'File gagal disimpan ke folder penyimpanan.'
            );
        }

        return $filename;
    }

    /**
     * Menghapus file penyimpanan jika ada.
     */
    private function removeFile(string $directory, string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $path = $this->projectPath($directory)
            . DIRECTORY_SEPARATOR . basename($filename);

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Path absolut berkas ebook yang siap dibaca, atau null bila hilang.
     * Dipakai halaman detail dan pembaca agar keduanya menilai hal yang sama.
     */
    public function resolveEbookFile(array $ebook): ?string
    {
        $baseDirectory = realpath(
            $this->projectPath(self::EBOOK_DIRECTORY)
        );
        $fileName = basename((string) ($ebook['file_path'] ?? ''));

        if ($baseDirectory === false || $fileName === '') {
            return null;
        }

        $path = realpath($baseDirectory . DIRECTORY_SEPARATOR . $fileName);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (
            $path === false
            || !is_file($path)
            || !in_array($extension, ['pdf', 'epub'], true)
            || !str_starts_with(
                strtolower($path),
                strtolower($baseDirectory . DIRECTORY_SEPARATOR)
            )
        ) {
            return null;
        }

        return $path;
    }

    /**
     * Mengubah path relatif menjadi path absolut di root proyek.
     */
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}

