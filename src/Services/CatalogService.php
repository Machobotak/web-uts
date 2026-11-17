<?php

namespace App\Services;

use App\Storage\JsonStorage;

class CatalogService
{
    private JsonStorage $storage;

    public function __construct(JsonStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getAllEbooks(): array
    {
        return $this->storage->read('ebooks.json');
    }

    public function findById(string $id): ?array
    {
        $ebooks = $this->getAllEbooks();

        foreach ($ebooks as $ebook) {
            if ((string) ($ebook['id'] ?? '') === $id) {
                return $ebook;
            }
        }

        return null;
    }

    public function search(string $keyword = '', string $category = ''): array
    {
        $ebooks = $this->getAllEbooks();

        return array_values(array_filter($ebooks, function ($ebook) use ($keyword, $category) {
            $matchKeyword = true;
            $matchCategory = true;

            if ($keyword !== '') {
                $text = strtolower(
                    ($ebook['title'] ?? '') . ' ' .
                    ($ebook['author'] ?? '') . ' ' .
                    ($ebook['description'] ?? '')
                );

                $matchKeyword = str_contains($text, strtolower($keyword));
            }

            if ($category !== '') {
                $matchCategory =
                    strtolower((string) ($ebook['category'] ?? '')) ===
                    strtolower($category);
            }

            return $matchKeyword && $matchCategory;
        }));
    }
}