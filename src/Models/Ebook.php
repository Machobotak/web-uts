<?php

namespace App\Models;

class Ebook
{
    private string $id;
    private string $title;
    private string $author;
    private string $category;
    private string $description;
    private string $cover;
    private string $file;

    public function __construct(array $data)
    {
        $this->id = (string) ($data['id'] ?? '');
        $this->title = (string) ($data['title'] ?? '');
        $this->author = (string) ($data['author'] ?? '');
        $this->category = (string) ($data['category'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->cover = (string) ($data['cover'] ?? '');
        $this->file = (string) ($data['file'] ?? '');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCover(): string
    {
        return $this->cover;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'category' => $this->category,
            'description' => $this->description,
            'cover' => $this->cover,
            'file' => $this->file,
        ];
    }
}