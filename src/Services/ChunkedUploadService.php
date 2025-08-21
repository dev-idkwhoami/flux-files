<?php

namespace Idkwhoami\FluxFiles\Services;

use Idkwhoami\FluxFiles\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChunkedUploadService
{
    protected FileStorageService $storageService;
    protected string $tempDirectory;
    protected int $chunkSize;
    protected int $maxParallelUploads;
    protected int $minFileSizeForChunking;
    protected bool $chunkingEnabled;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
        $this->tempDirectory = config('flux-files.upload.temp_directory');
        $this->chunkSize = config('flux-files.upload.chunk_size', 1048576);
        $this->maxParallelUploads = config('flux-files.upload.max_parallel_uploads', 3);
        $this->minFileSizeForChunking = config('flux-files.upload.min_file_size_for_chunking', 5242880);
        $this->chunkingEnabled = config('flux-files.upload.chunking_enabled', true);

        $this->ensureTempDirectoryExists();
    }

    public function shouldUseChunking(UploadedFile $file): bool
    {
        return $this->chunkingEnabled && $file->getSize() >= $this->minFileSizeForChunking;
    }

    public function initializeChunkedUpload(UploadedFile $file): string
    {
        $uploadId = Str::uuid()->toString();
        $uploadPath = $this->tempDirectory . '/' . $uploadId;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Store upload metadata
        $metadata = [
            'upload_id' => $uploadId,
            'original_name' => $file->getClientOriginalName(),
            'total_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'total_chunks' => ceil($file->getSize() / $this->chunkSize),
            'received_chunks' => [],
            'created_at' => now()->toISOString(),
        ];

        file_put_contents($uploadPath . '/metadata.json', json_encode($metadata));

        return $uploadId;
    }

    public function uploadChunk(string $uploadId, int $chunkIndex, string $chunkData): array
    {
        $uploadPath = $this->tempDirectory . '/' . $uploadId;

        if (!is_dir($uploadPath)) {
            throw new \Exception('Upload session not found');
        }

        // Get metadata
        $metadataPath = $uploadPath . '/metadata.json';
        if (!file_exists($metadataPath)) {
            throw new \Exception('Upload metadata not found');
        }

        $metadata = json_decode(file_get_contents($metadataPath), true);

        // Save chunk
        $chunkPath = $uploadPath . '/chunk_' . $chunkIndex;
        file_put_contents($chunkPath, base64_decode($chunkData));

        // Update metadata
        $metadata['received_chunks'][] = $chunkIndex;
        $metadata['received_chunks'] = array_unique($metadata['received_chunks']);
        sort($metadata['received_chunks']);

        file_put_contents($metadataPath, json_encode($metadata));

        $progress = (count($metadata['received_chunks']) / $metadata['total_chunks']) * 100;

        // Check if upload is complete
        $isComplete = count($metadata['received_chunks']) === $metadata['total_chunks'];

        if ($isComplete) {
            return [
                'completed' => true,
                'progress' => 100,
                'file_id' => $this->assembleAndStoreFile($uploadId, $metadata)
            ];
        }

        return [
            'completed' => false,
            'progress' => round($progress, 2),
            'received_chunks' => count($metadata['received_chunks']),
            'total_chunks' => $metadata['total_chunks']
        ];
    }

    protected function assembleAndStoreFile(string $uploadId, array $metadata): int
    {
        $uploadPath = $this->tempDirectory . '/' . $uploadId;

        // Create assembled file
        $assembledPath = $uploadPath . '/assembled_file';
        $assembledFile = fopen($assembledPath, 'wb');

        if (!$assembledFile) {
            throw new \Exception('Could not create assembled file');
        }

        // Assemble chunks in order
        for ($i = 0; $i < $metadata['total_chunks']; $i++) {
            $chunkPath = $uploadPath . '/chunk_' . $i;

            if (!file_exists($chunkPath)) {
                fclose($assembledFile);
                throw new \Exception("Missing chunk: $i");
            }

            $chunkData = file_get_contents($chunkPath);
            fwrite($assembledFile, $chunkData);
        }

        fclose($assembledFile);

        // Verify file size
        $assembledSize = filesize($assembledPath);
        if ($assembledSize !== $metadata['total_size']) {
            throw new \Exception('Assembled file size mismatch');
        }

        // Create UploadedFile instance for storage service
        $uploadedFile = new UploadedFile(
            $assembledPath,
            $metadata['original_name'],
            $metadata['mime_type'],
            null,
            true // test mode to avoid is_uploaded_file() check
        );

        // Store using existing storage service
        $storedFile = $this->storageService->store(
            $uploadedFile,
            null, // folderId will be set by the calling component
            config('flux-files.storage.disk'),
            null // tenantId will be set by the calling component
        );

        // Cleanup temporary files
        $this->cleanupUpload($uploadId);

        return $storedFile->id;
    }

    public function getUploadProgress(string $uploadId): array
    {
        $uploadPath = $this->tempDirectory . '/' . $uploadId;
        $metadataPath = $uploadPath . '/metadata.json';

        if (!file_exists($metadataPath)) {
            throw new \Exception('Upload session not found');
        }

        $metadata = json_decode(file_get_contents($metadataPath), true);
        $progress = (count($metadata['received_chunks']) / $metadata['total_chunks']) * 100;

        return [
            'upload_id' => $uploadId,
            'progress' => round($progress, 2),
            'received_chunks' => count($metadata['received_chunks']),
            'total_chunks' => $metadata['total_chunks'],
            'total_size' => $metadata['total_size'],
            'original_name' => $metadata['original_name']
        ];
    }

    public function cancelUpload(string $uploadId): void
    {
        $this->cleanupUpload($uploadId);
    }

    public function cleanupUpload(string $uploadId): void
    {
        $uploadPath = $this->tempDirectory . '/' . $uploadId;

        if (is_dir($uploadPath)) {
            $this->deleteDirectory($uploadPath);
        }
    }

    public function cleanupExpiredUploads(): int
    {
        $cleanupInterval = config('flux-files.upload.cleanup_interval', 3600);
        $cutoffTime = now()->subSeconds($cleanupInterval);
        $cleaned = 0;

        if (!is_dir($this->tempDirectory)) {
            return $cleaned;
        }

        $uploads = scandir($this->tempDirectory);

        foreach ($uploads as $upload) {
            if ($upload === '.' || $upload === '..') {
                continue;
            }

            $uploadPath = $this->tempDirectory . '/' . $upload;
            $metadataPath = $uploadPath . '/metadata.json';

            if (!file_exists($metadataPath)) {
                continue;
            }

            $metadata = json_decode(file_get_contents($metadataPath), true);
            $createdAt = \Carbon\Carbon::parse($metadata['created_at']);

            if ($createdAt->lt($cutoffTime)) {
                $this->cleanupUpload($upload);
                $cleaned++;

                Log::info('Cleaned up expired chunked upload', [
                    'upload_id' => $upload,
                    'original_name' => $metadata['original_name'] ?? 'unknown',
                    'created_at' => $createdAt->toISOString()
                ]);
            }
        }

        return $cleaned;
    }

    protected function ensureTempDirectoryExists(): void
    {
        if (!is_dir($this->tempDirectory)) {
            mkdir($this->tempDirectory, 0755, true);
        }
    }

    protected function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            is_dir($filePath) ? $this->deleteDirectory($filePath) : unlink($filePath);
        }

        rmdir($path);
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getMaxParallelUploads(): int
    {
        return $this->maxParallelUploads;
    }

    public function getMinFileSizeForChunking(): int
    {
        return $this->minFileSizeForChunking;
    }
}
