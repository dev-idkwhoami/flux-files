<?php

namespace Tests\Feature;

use Idkwhoami\FluxFiles\Livewire\FileUpload;
use Idkwhoami\FluxFiles\Models\File;
use Idkwhoami\FluxFiles\Services\ChunkedUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileUploadComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test storage
        Storage::fake('local');

        // Ensure temp directory exists
        $tempDir = config('flux-files.upload.temp_directory');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }

    /** @test */
    public function it_can_render_file_upload_component()
    {
        $component = Livewire::test(FileUpload::class);

        $component->assertStatus(200)
                  ->assertSee('Drag and drop files here')
                  ->assertSee('Browse Files');
    }

    /** @test */
    public function it_can_initialize_component_with_configuration()
    {
        $component = Livewire::test(FileUpload::class, [
            'allowedTypes' => ['jpg', 'png'],
            'maxFileSize' => 5242880, // 5MB
            'maxFiles' => 5,
            'multiple' => true,
            'showPreviews' => true,
            'dragDrop' => true
        ]);

        $component->assertSet('allowedTypes', ['jpg', 'png'])
                  ->assertSet('maxFileSize', 5242880)
                  ->assertSet('maxFiles', 5)
                  ->assertSet('multiple', true)
                  ->assertSet('showPreviews', true)
                  ->assertSet('dragDrop', true);
    }

    /** @test */
    public function it_can_get_chunking_configuration()
    {
        $component = Livewire::test(FileUpload::class);

        $config = $component->call('getChunkingConfig');

        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('chunk_size', $config);
        $this->assertArrayHasKey('max_parallel_uploads', $config);
        $this->assertArrayHasKey('min_file_size_for_chunking', $config);
    }

    /** @test */
    public function it_can_initialize_chunked_upload()
    {
        $component = Livewire::test(FileUpload::class);

        $result = $component->call('initializeChunkedUpload', 'test.jpg', 10485760, 'image/jpeg');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('upload_id', $result);
        $this->assertArrayHasKey('chunk_size', $result);
        $this->assertArrayHasKey('should_chunk', $result);
    }

    /** @test */
    public function it_validates_files_before_chunked_upload()
    {
        $component = Livewire::test(FileUpload::class, [
            'allowedTypes' => ['jpg', 'png'],
            'maxFileSize' => 1048576 // 1MB
        ]);

        // Test invalid file type
        $result = $component->call('initializeChunkedUpload', 'test.exe', 2097152, 'application/exe');
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);

        // Test file too large
        $result = $component->call('initializeChunkedUpload', 'test.jpg', 2097152, 'image/jpeg');
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);

        // Test valid file
        $result = $component->call('initializeChunkedUpload', 'test.jpg', 512000, 'image/jpeg');
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_upload_chunks()
    {
        $component = Livewire::test(FileUpload::class);

        // Initialize upload
        $initResult = $component->call('initializeChunkedUpload', 'test.txt', 1024, 'text/plain');
        $this->assertTrue($initResult['success']);

        $uploadId = $initResult['upload_id'];

        // Upload a chunk (base64 encoded "test content")
        $chunkData = base64_encode('test content');
        $result = $component->call('uploadChunk', $uploadId, 0, $chunkData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('progress', $result);
    }

    /** @test */
    public function it_can_cancel_chunked_upload()
    {
        $component = Livewire::test(FileUpload::class);

        // Initialize upload
        $initResult = $component->call('initializeChunkedUpload', 'test.txt', 1024, 'text/plain');
        $uploadId = $initResult['upload_id'];

        // Cancel upload
        $component->call('cancelChunkedUpload', $uploadId);

        // Verify upload directory was cleaned up
        $tempDir = config('flux-files.upload.temp_directory');
        $uploadPath = $tempDir . '/' . $uploadId;
        $this->assertFalse(is_dir($uploadPath));
    }

    /** @test */
    public function it_shows_file_previews_for_previewable_files()
    {
        $component = Livewire::test(FileUpload::class, ['showPreviews' => true]);

        // Create a mock UploadedFile
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $isPreviewable = $component->call('isPreviewable', $file);
        $this->assertTrue($isPreviewable);

        $previewUrl = $component->call('getPreviewUrl', $file);
        $this->assertNotNull($previewUrl);
    }

    /** @test */
    public function it_returns_correct_file_icons()
    {
        $component = Livewire::test(FileUpload::class);

        // Test different file types
        $imageFile = UploadedFile::fake()->image('test.jpg');
        $icon = $component->call('getFileIcon', $imageFile);
        $this->assertEquals(config('flux-files.ui.file_icons.image'), $icon);

        // Test with a mock file that returns specific mime type
        $mockFile = new class () extends UploadedFile {
            public function getMimeType(): string
            {
                return 'application/pdf';
            }
        };

        $icon = $component->call('getFileIcon', $mockFile);
        $this->assertEquals(config('flux-files.ui.file_icons.document'), $icon);
    }

    /** @test */
    public function it_handles_validation_errors_properly()
    {
        $component = Livewire::test(FileUpload::class, [
            'maxFiles' => 2
        ]);

        // Simulate adding more files than allowed
        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
            UploadedFile::fake()->image('test3.jpg')
        ];

        $component->set('files', $files);
        $component->call('validateFiles');

        $this->assertArrayHasKey('max_files', $component->get('validationErrors'));
    }

    /** @test */
    public function chunked_upload_service_cleanup_works()
    {
        $chunkedService = app(ChunkedUploadService::class);

        // Create a test upload directory with old timestamp
        $tempDir = config('flux-files.upload.temp_directory');
        $oldUploadId = 'old-upload-' . time();
        $oldUploadPath = $tempDir . '/' . $oldUploadId;

        mkdir($oldUploadPath, 0755, true);

        // Create metadata with old timestamp
        $oldMetadata = [
            'upload_id' => $oldUploadId,
            'created_at' => now()->subHours(2)->toISOString()
        ];

        file_put_contents($oldUploadPath . '/metadata.json', json_encode($oldMetadata));

        // Run cleanup
        $cleaned = $chunkedService->cleanupExpiredUploads();

        $this->assertGreaterThan(0, $cleaned);
        $this->assertFalse(is_dir($oldUploadPath));
    }
}
