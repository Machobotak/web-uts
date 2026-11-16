<?php
namespace App\Storage;

class JsonStorage
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    /**
     * Membaca data dari file JSON.
     */
    public function read(string $filename): array
    {
        $filePath = $this->getFilePath($filename);

        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \RuntimeException(
                "Gagal membaca file: {$filename}"
            );
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \RuntimeException(
                "Format JSON tidak valid: {$filename}"
            );
        }

        return $data;
    }

    /**
     * Menyimpan data ke file JSON.
     */
    public function write(string $filename, array $data): bool
    {
        $filePath = $this->getFilePath($filename);

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            throw new \RuntimeException(
                "Gagal mengubah data menjadi JSON."
            );
        }

        $result = file_put_contents($filePath, $json, LOCK_EX);

        if ($result === false) {
            throw new \RuntimeException(
                "Gagal menyimpan file: {$filename}"
            );
        }

        return true;
    }

    /**
     * Membuat path file JSON.
     */
    private function getFilePath(string $filename): string
    {
        if (
            basename($filename) !== $filename ||
            !str_ends_with($filename, '.json')
        ) {
            throw new \InvalidArgumentException(
                "Nama file tidak valid."
            );
        }

        return $this->directory . DIRECTORY_SEPARATOR . $filename;
    }
}
