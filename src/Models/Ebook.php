<?php

namespace App\Models;

class Ebook
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_UNAVAILABLE = 'unavailable';

    protected int $idEbook;
    protected int $idCategory;
    protected string $title;
    protected string $author;
    protected string $publisher;
    protected int $publicationYear;
    protected string $isbn;
    protected string $description;
    protected string $cover;
    protected string $filePath;
    protected string $status;
    protected int $licenseLimit;
    protected int $licenseTotal;
    protected string $createAt;
    protected string $updateAt;

    public function __construct(array $data)
    {
        $this->idEbook = (int) ($data['id_ebook'] ?? 0);
        $this->idCategory = (int) ($data['id_category'] ?? 0);
        $this->title = $data['title'] ?? '';
        $this->author = $data['author'] ?? '';
        $this->publisher = $data['publisher'] ?? '';
        $this->publicationYear = (int) ($data['publication_year'] ?? 0);
        $this->isbn = $data['isbn'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->cover = $data['cover'] ?? '';
        $this->filePath = $data['file_path'] ?? '';
        $this->status = $data['status'] ?? self::STATUS_AVAILABLE;
        // license_limit adalah jumlah lisensi yang masih tersedia, sedangkan
        // license_total menyimpan kapasitas aslinya agar dapat dipulihkan.
        $this->licenseLimit = max(0, (int) ($data['license_limit'] ?? 1));
        $this->licenseTotal = max(
            $this->licenseLimit,
            (int) ($data['license_total'] ?? $this->licenseLimit)
        );
        $this->createAt = $data['create_at'] ?? date('Y-m-d H:i:s');
        $this->updateAt = $data['update_at'] ?? date('Y-m-d H:i:s');
    }

    // Mengambil ID ebook
    public function getIdEbook(): int
    {
        return $this->idEbook;
    }

    // Mengambil ID kategori ebook
    public function getIdCategory(): int
    {
        return $this->idCategory;
    }

    // Mengambil judul ebook
    public function getTitle(): string
    {
        return $this->title;
    }

    // Mengambil nama penulis
    public function getAuthor(): string
    {
        return $this->author;
    }

    // Mengambil nama penerbit
    public function getPublisher(): string
    {
        return $this->publisher;
    }

    // Mengambil tahun terbit
    public function getPublicationYear(): int
    {
        return $this->publicationYear;
    }

    // Mengambil ISBN
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    // Mengambil deskripsi ebook
    public function getDescription(): string
    {
        return $this->description;
    }

    // Mengambil path file cover
    public function getCover(): string
    {
        return $this->cover;
    }

    // Mengambil path file ebook
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    // Mengambil status ebook
    public function getStatus(): string
    {
        return $this->status;
    }

    // Mengambil batas lisensi peminjaman
    public function getLicenseLimit(): int
    {
        return $this->licenseLimit;
    }

    // Mengambil waktu data dibuat
    public function getCreateAt(): string
    {
        return $this->createAt;
    }

    // Mengambil waktu data terakhir diubah
    public function getUpdateAt(): string
    {
        return $this->updateAt;
    }

    // Mengubah kategori ebook
    public function setIdCategory(int $idCategory): void
    {
        if ($idCategory <= 0) {
            throw new \InvalidArgumentException('Kategori tidak valid.');
        }

        $this->idCategory = $idCategory;
        $this->touchUpdateAt();
    }

    // Mengubah judul ebook
    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->touchUpdateAt();
    }

    // Mengubah nama penulis
    public function setAuthor(string $author): void
    {
        $this->author = $author;
        $this->touchUpdateAt();
    }

    // Mengubah nama penerbit
    public function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
        $this->touchUpdateAt();
    }

    // Mengubah tahun terbit
    public function setPublicationYear(int $publicationYear): void
    {
        if ($publicationYear < 0) {
            throw new \InvalidArgumentException('Tahun terbit tidak valid.');
        }

        $this->publicationYear = $publicationYear;
        $this->touchUpdateAt();
    }

    // Mengubah ISBN
    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
        $this->touchUpdateAt();
    }

    // Mengubah deskripsi ebook
    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->touchUpdateAt();
    }

    // Mengubah path file cover
    public function setCover(string $cover): void
    {
        $this->cover = $cover;
        $this->touchUpdateAt();
    }

    // Mengubah path file ebook
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
        $this->touchUpdateAt();
    }

    // Mengubah status ebook
    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_AVAILABLE, self::STATUS_UNAVAILABLE], true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        $this->status = $status;
        $this->touchUpdateAt();
    }

    // Mengubah sisa lisensi peminjaman yang tersedia
    public function setLicenseLimit(int $licenseLimit): void
    {
        if ($licenseLimit < 0) {
            throw new \InvalidArgumentException('Batas lisensi tidak valid.');
        }

        $this->licenseLimit = $licenseLimit;
        $this->touchUpdateAt();
    }

    // Mengambil total lisensi yang dimiliki ebook
    public function getLicenseTotal(): int
    {
        return $this->licenseTotal;
    }

    // Mengubah total lisensi yang dimiliki ebook
    public function setLicenseTotal(int $licenseTotal): void
    {
        if ($licenseTotal < 0) {
            throw new \InvalidArgumentException('Total lisensi tidak valid.');
        }

        $this->licenseTotal = $licenseTotal;
        $this->touchUpdateAt();
    }

    // Memeriksa apakah masih ada lisensi yang dapat dipinjam
    public function hasLicenseAvailable(): bool
    {
        return $this->licenseLimit > 0;
    }

    // Memeriksa apakah ebook dapat dipinjam
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    // Memperbarui waktu perubahan data
    private function touchUpdateAt(): void
    {
        $this->updateAt = date('Y-m-d H:i:s');
    }

    // Mengubah data objek menjadi array
    public function toArray(): array
    {
        return [
            'id_ebook' => $this->idEbook,
            'id_category' => $this->idCategory,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publicationYear,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'cover' => $this->cover,
            'file_path' => $this->filePath,
            'status' => $this->status,
            'license_limit' => $this->licenseLimit,
            'license_total' => $this->licenseTotal,
            'create_at' => $this->createAt,
            'update_at' => $this->updateAt
        ];
    }
}