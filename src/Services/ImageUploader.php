<?php

namespace App\Services;

class ImageUploader
{
    private const MAX_BYTES    = 8 * 1024 * 1024; // 8 MB
    private const MAX_WIDTH    = 6000;
    private const MAX_HEIGHT   = 6000;
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private string $uploadDir;
    private string $webPath;
    private array  $errors = [];

    public function __construct(string $uploadDir, string $webPath)
    {
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->webPath   = rtrim($webPath, '/');
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function uploadMany(array $files): array
    {
        $this->errors = [];
        $results      = [];

        $normalized = $this->normalizeFilesArray($files);

        foreach ($normalized as $index => $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $result = $this->uploadOne($file, $index);
            if ($result !== null) {
                $results[] = $result;
            }
        }

        return $results;
    }

    public function delete(string $filename): void
    {
        $path = $this->uploadDir . '/' . $filename;
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function uploadOne(array $file, int $index): ?string
    {
        $label = 'Image ' . ($index + 1);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[$index] = $label . ': ' . $this->uploadErrorMessage($file['error']);
            return null;
        }

        if ($file['size'] > self::MAX_BYTES) {
            $this->errors[$index] = $label . ': File exceeds 8 MB limit.';
            return null;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errors[$index] = $label . ': Invalid upload.';
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_MIME)) {
            $this->errors[$index] = $label . ': Only JPEG, PNG, and WebP images are allowed.';
            return null;
        }

        if (function_exists('getimagesize')) {
            $info = @getimagesize($file['tmp_name']);
            if ($info === false) {
                $this->errors[$index] = $label . ': File is not a valid image.';
                return null;
            }
            [$width, $height] = $info;
            if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
                $this->errors[$index] = $label . ': Image dimensions exceed 6000×6000 px.';
                return null;
            }
        }

        $ext      = self::ALLOWED_MIME[$mime];
        $filename = $this->generateFilename($ext);
        $dest     = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->errors[$index] = $label . ': Could not save file. Check directory permissions.';
            return null;
        }

        chmod($dest, 0644);

        return $filename;
    }

    private function generateFilename(string $ext): string
    {
        do {
            $name = bin2hex(random_bytes(16)) . '.' . $ext;
        } while (file_exists($this->uploadDir . '/' . $name));

        return $name;
    }

    private function normalizeFilesArray(array $files): array
    {
        if (!is_array($files['name'])) {
            return [$files];
        }

        $out = [];
        foreach ($files['name'] as $i => $name) {
            $out[] = [
                'name'     => $name,
                'tmp_name' => $files['tmp_name'][$i],
                'size'     => $files['size'][$i],
                'error'    => $files['error'][$i],
            ];
        }
        return $out;
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL   => 'Upload was interrupted.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server upload directory missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not write file.',
            default              => 'Upload failed (code ' . $code . ').',
        };
    }
}
