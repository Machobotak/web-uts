<?php

namespace App\Models;

class Ebook
{
    private string $id;
    private string $title;
    private string $author;
    private string $description;
    private string $categoryId;
    private string $filePath;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (string) ($data['id'] ?? '');
        $this->title = $data['title'] ?? '';
        $this->author = $data['author'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->categoryId = (string) ($data['category_id'] ?? '');
        $this->filePath = $data['file_path'] ?? '';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getDescription(): string { return $this->description; }
    public function getCategoryId(): string { return $this->categoryId; }
    public function getFilePath(): string { return $this->filePath; }

    public function setTitle(string $title): void
    {
        $this->title = trim($title);
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setAuthor(string $author): void
    {
        $this->author = trim($author);
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'file_path' => $this->filePath,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}