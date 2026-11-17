<?php

namespace App\Models;

class Category
{
    protected int $idCategory;
    protected string $name;
    protected string $description;
    protected string $createAt;

    public function __construct(array $data)
    {
        $this->idCategory = (int) ($data['id_category'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->createAt = $data['create_at'] ?? date('Y-m-d H:i:s');
    }

    // Mengambil ID kategori
    public function getIdCategory(): int
    {
        return $this->idCategory;
    }

    // Mengambil nama kategori
    public function getName(): string
    {
        return $this->name;
    }

    // Mengambil deskripsi kategori
    public function getDescription(): string
    {
        return $this->description;
    }

    // Mengambil waktu data dibuat
    public function getCreateAt(): string
    {
        return $this->createAt;
    }

    // Mengubah nama kategori
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Mengubah deskripsi kategori
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    // Mengubah data objek menjadi array
    public function toArray(): array
    {
        return [
            'id_category' => $this->idCategory,
            'name' => $this->name,
            'description' => $this->description,
            'create_at' => $this->createAt
        ];
    }
}
