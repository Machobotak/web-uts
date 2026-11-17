<?php

namespace App\Services;

use App\Models\Category;
use App\Storage\JsonStorage;

class CategoryService
{
    private JsonStorage $storage;
    private string $filename = 'categories.json';

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getAllCategories(): array
    {
        return $this->storage->read($this->filename);
    }

    public function findById(int $id): ?array
    {
        foreach ($this->getAllCategories() as $category) {
            if ((int) ($category['id'] ?? 0) === $id) {
                return $category;
            }
        }

        return null;
    }

    private function generateId(): int
    {
        $categories = $this->getAllCategories();

        if (empty($categories)) {
            return 1;
        }

        return max(array_column($categories, 'id')) + 1;
    }

    private function nameExists(string $name, ?int $excludeId = null): bool
    {
        foreach ($this->getAllCategories() as $category) {
            if (
                strtolower(trim($category['name'] ?? '')) === strtolower($name) &&
                (int) ($category['id'] ?? 0) !== $excludeId
            ) {
                return true;
            }
        }

        return false;
    }

    public function createCategory(array $data): Category
    {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException('Nama kategori wajib diisi.');
        }

        if ($this->nameExists($name)) {
            throw new \InvalidArgumentException('Nama kategori sudah digunakan.');
        }

        $category = new Category([
            'id' => $this->generateId(),
            'name' => $name,
            'description' => $description
        ]);

        $categories = $this->getAllCategories();
        $categories[] = $category->toArray();

        $this->storage->write($this->filename, $categories);

        return $category;
    }

    public function updateCategory(int $id, array $data): Category
    {
        $categories = $this->getAllCategories();
        $index = null;

        foreach ($categories as $i => $category) {
            if ((int) ($category['id'] ?? 0) === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            throw new \InvalidArgumentException('Kategori tidak ditemukan.');
        }

        $name = trim($data['name'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException('Nama kategori wajib diisi.');
        }

        if ($this->nameExists($name, $id)) {
            throw new \InvalidArgumentException('Nama kategori sudah digunakan.');
        }

        $category = new Category($categories[$index]);
        $category->setName($name);
        $category->setDescription(trim($data['description'] ?? ''));

        $categories[$index] = $category->toArray();
        $this->storage->write($this->filename, $categories);

        return $category;
    }

    public function deleteCategory(int $id): void
    {
        $categories = $this->getAllCategories();
        $ebooks = $this->storage->read('ebooks.json');

        foreach ($ebooks as $ebook) {
            if ((int) ($ebook['category_id'] ?? 0) === $id) {
                throw new \InvalidArgumentException(
                    'Kategori tidak bisa dihapus karena masih digunakan oleh ebook.'
                );
            }
        }

        $filtered = array_values(array_filter(
            $categories,
            fn($category) => (int) ($category['id'] ?? 0) !== $id
        ));

        if (count($filtered) === count($categories)) {
            throw new \InvalidArgumentException('Kategori tidak ditemukan.');
        }

        $this->storage->write($this->filename, $filtered);
    }
}
