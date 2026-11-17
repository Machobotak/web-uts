<?php

namespace App\Services;

use App\Models\Ebook;
use App\Storage\JsonStorage;

class CatalogService
{
    private JsonStorage $storage;
    private string $ebookFile = 'ebooks.json';
    private string $categoryFile = 'categories.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getAllEbooks(): array
    {
        return $this->storage->read($this->ebookFile);
    }

    public function findById(string $id): ?array
    {
        foreach ($this->getAllEbooks() as $ebook) {
            if ((string) ($ebook['id'] ?? '') === $id) {
                return $ebook;
            }
        }

        return null;
    }

    private function validateData(array $data): array
    {
        $title = trim($data['title'] ?? '');
        $author = trim($data['author'] ?? '');
        $description = trim($data['description'] ?? '');
        $categoryId = trim((string) ($data['category_id'] ?? ''));

        if ($title === '' || $author === '') {
            throw new \InvalidArgumentException('Judul dan penulis wajib diisi.');
        }

        if ($categoryId !== '') {
            $found = false;

            foreach ($this->storage->read($this->categoryFile) as $category) {
                if ((string) ($category['id'] ?? '') === $categoryId) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \InvalidArgumentException('Kategori yang dipilih tidak ditemukan.');
            }
        }

        return [
            'title' => $title,
            'author' => $author,
            'description' => $description,
            'category_id' => $categoryId
        ];
    }

    public function createEbook(array $data): Ebook
    {
        $validated = $this->validateData($data);

        $ebook = new Ebook(array_merge($validated, [
            'id' => uniqid('ebook_'),
            'file_path' => $data['file_path'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));

        $ebooks = $this->getAllEbooks();
        $ebooks[] = $ebook->toArray();

        $this->storage->write($this->ebookFile, $ebooks);

        return $ebook;
    }

    public function updateEbook(string $id, array $data): Ebook
    {
        $ebooks = $this->getAllEbooks();
        $index = null;

        foreach ($ebooks as $i => $item) {
            if ((string) ($item['id'] ?? '') === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException('Ebook tidak ditemukan.');
        }

        $validated = $this->validateData($data);
        $ebook = new Ebook($ebooks[$index]);

        $ebook->setTitle($validated['title']);
        $ebook->setAuthor($validated['author']);
        $ebook->setDescription($validated['description']);
        $ebook->setCategoryId($validated['category_id']);

        if (!empty($data['file_path'])) {
            $ebook->setFilePath($data['file_path']);
        }

        $ebooks[$index] = $ebook->toArray();
        $this->storage->write($this->ebookFile, $ebooks);

        return $ebook;
    }

    public function deleteEbook(string $id): void
    {
        $ebooks = $this->getAllEbooks();
        $target = null;

        foreach ($ebooks as $ebook) {
            if ((string) ($ebook['id'] ?? '') === $id) {
                $target = $ebook;
                break;
            }
        }

        if ($target === null) {
            throw new \InvalidArgumentException('Ebook tidak ditemukan.');
        }

        $filtered = array_values(array_filter(
            $ebooks,
            fn($ebook) => (string) ($ebook['id'] ?? '') !== $id
        ));

        $this->storage->write($this->ebookFile, $filtered);

        $filePath = $target['file_path'] ?? '';

        if ($filePath !== '') {
            $fullPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR
                . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}