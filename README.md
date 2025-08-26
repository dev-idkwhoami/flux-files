# Flux Files - Laravel File Manager Package

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-blue.svg)](https://php.net)

A modern, elegant file manager package for Laravel applications built with FluxUI and Livewire. Provides a complete file management solution with drag-and-drop uploads, folder navigation, file selection components, and comprehensive file operations.

## Features

- 🗂️ **File Browser**: Navigate through folders with breadcrumb navigation
- 📤 **File Upload**: Drag-and-drop file uploads with progress indication
- 🎯 **File Selection**: Easy file picker component for forms
- 📁 **Folder Management**: Create, rename, and delete folders
- 🔄 **File Operations**: Rename and delete files
- 🎨 **FluxUI Integration**: Beautiful, responsive UI components
- 🌓 **Dark Mode**: Automatic light/dark mode compatibility
- 🏗️ **Multi-tenancy**: Built-in tenant isolation support
- 📱 **Responsive**: Mobile-friendly design
- 🔒 **Validation**: Comprehensive file type and size validation

## Requirements

- PHP 8.4 or higher
- Laravel 11.x
- Livewire Flux 2.2.3+
- Livewire Flux Pro 2.2.3+

## Installation

You can install the package via composer:

```bash
composer require idkwhoami/flux-files
```

Run the installation command to publish the necessary files:

```bash
php artisan flux-files:install
```

This command will:
- Publish the configuration file
- Publish the database migrations
- Publish the models to your app
- Publish language files

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file is published to `config/flux-files.php`. Here are the main configuration options:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use for file storage.
    |
    */
    'disk' => env('FLUX_FILES_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | File Validation
    |--------------------------------------------------------------------------
    |
    | Configure file upload restrictions and validation rules.
    |
    */
    'validation' => [
        'max_file_size' => env('FLUX_FILES_MAX_SIZE', 10240), // KB
        'allowed_extensions' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'archives' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'audio' => ['mp3', 'wav', 'ogg', 'flac'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        ],
        'mime_types' => [
            // Auto-generated based on extensions
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Eloquent Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the ID type for models (bigint, uuid, ulid).
    |
    */
    'eloquent' => [
        'id_type' => env('FLUX_FILES_ID_TYPE', 'bigint'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenant support for file isolation.
    |
    */
    'multi_tenant' => [
        'enabled' => env('FLUX_FILES_MULTI_TENANT', false),
        'tenant_model' => null, // Your tenant model class
        'tenant_key' => 'tenant_id',
    ],
];
```

## Usage

### File Browser Component

The file browser component provides a complete file management interface:

```blade
<livewire:flux-files.browser 
    :current-folder-id="null"
    view-mode="grid"
    :selectable="false" />
```

**Properties:**
- `current-folder-id`: ID of the folder to display (null for root)
- `view-mode`: Display mode ('grid' or 'table')
- `selectable`: Whether files can be selected

**Events:**
- `file-selected`: Fired when a file is selected
- `folder-changed`: Fired when navigating to a different folder

### File Selection Component

Use this component to add file selection to forms:

```blade
<livewire:flux-files.select 
    name="avatar"
    :selected-file-id="$user->avatar_id"
    placeholder="Select an image..."
    :required="true" />
```

**Properties:**
- `name`: Form field name
- `selected-file-id`: ID of the currently selected file
- `placeholder`: Placeholder text
- `required`: Whether selection is required

### File Upload Component

For drag-and-drop file uploads:

```blade
<livewire:flux-files.upload 
    :target-folder-id="$folderId"
    :allowed-types="['images', 'documents']"
    :max-files="5" />
```

**Properties:**
- `target-folder-id`: ID of the folder to upload to
- `allowed-types`: Array of allowed file type categories
- `max-files`: Maximum number of files to upload

### Blade Components

The package provides numerous Blade components for custom implementations:

#### File Display Components
```blade
<x-flux-files:file-icon :file="$file" />
<x-flux-files:folder-icon :folder="$folder" />
<x-flux-files:file-item-grid :file="$file" />
<x-flux-files:file-item-table :file="$file" />
<x-flux-files:file-preview :file="$file" />
<x-flux-files:file-size :file="$file" />
<x-flux-files:file-date :file="$file" />
```

#### Navigation Components
```blade
<x-flux-files:breadcrumbs :current-folder="$folder" />
<x-flux-files:view-mode-toggle />
<x-flux-files:sort-controls />
```

#### Upload Components
```blade
<x-flux-files:drop-zone />
<x-flux-files:upload-progress :files="$files" />
<x-flux-files:file-upload-item :file="$file" />
```

### Models

The package includes two main models:

#### File Model

```php
use App\Models\File;

// Get file URL
$url = $file->getUrl();

// Get preview URL (for images)
$previewUrl = $file->getPreviewUrl();

// Check file type
$isImage = $file->isImage();
$isAudio = $file->isAudio();
$isVideo = $file->isVideo();

// Get human-readable size
$size = $file->getHumanReadableSize();

// Relationships
$folder = $file->folder;
$tenant = $file->tenant; // if multi-tenancy enabled

// Scopes
$files = File::byTenant($tenantId)->get();
$images = File::byMimeType('image')->get();
$folderFiles = File::inFolder($folderId)->get();
```

#### Folder Model

```php
use App\Models\Folder;

// Get full folder path
$path = $folder->getFullPath();

// Check if folder is ancestor of another
$isAncestor = $folder->isAncestorOf($otherFolder);

// Get all descendants
$descendants = $folder->getDescendants();

// Create subfolder
$subfolder = $folder->createSubfolder('New Folder');

// Relationships
$files = $folder->files;
$children = $folder->children;
$parent = $folder->parent;

// Scopes
$rootFolders = Folder::roots()->get();
$tenantFolders = Folder::byTenant($tenantId)->get();
```

### Services

#### FileStorageService

```php
use Idkwhoami\FluxFiles\Services\FileStorageService;

$storage = app(FileStorageService::class);

// Store file
$file = $storage->store($uploadedFile, $folderId);

// Delete file
$storage->delete($file);

// Move file
$storage->move($file, $newFolderId);

// Copy file
$newFile = $storage->copy($file, $targetFolderId);

// Check if file exists
$exists = $storage->exists($file);
```

#### FileValidationService

```php
use Idkwhoami\FluxFiles\Services\FileValidationService;

$validator = app(FileValidationService::class);

// Validate file
$isValid = $validator->validateFile($uploadedFile);

// Validate file type
$isValidType = $validator->validateFileType($uploadedFile, ['images']);

// Validate file size
$isValidSize = $validator->validateFileSize($uploadedFile);

// Get validation errors
$errors = $validator->getErrors();
```

## Advanced Usage

### Multi-tenancy

Enable multi-tenancy in your configuration:

```php
'multi_tenant' => [
    'enabled' => true,
    'tenant_model' => App\Models\Tenant::class,
    'tenant_key' => 'tenant_id',
],
```

Then use the tenant scope in your queries:

```php
// Get files for current tenant
$files = File::byTenant(auth()->user()->tenant_id)->get();

// Get folders for current tenant
$folders = Folder::byTenant(auth()->user()->tenant_id)->get();
```

### Custom File Types

Add custom file type validation:

```php
// In your service provider
use Idkwhoami\FluxFiles\Services\FileValidationService;

FileValidationService::addCustomType('custom', [
    'extensions' => ['custom', 'ext'],
    'mime_types' => ['application/custom'],
    'max_size' => 5120, // KB
]);
```

### Events

Listen for file events:

```php
use Idkwhoami\FluxFiles\Events\FileUploaded;
use Idkwhoami\FluxFiles\Events\FileDeleted;

// In your EventServiceProvider
protected $listen = [
    FileUploaded::class => [
        // Your listeners
    ],
    FileDeleted::class => [
        // Your listeners
    ],
];
```

### Custom Views

Publish and customize the views:

```bash
php artisan vendor:publish --tag=flux-files-views
```

Views will be published to `resources/views/vendor/flux-files/`.

## Commands

The package includes helpful Artisan commands:

### Install Command

```bash
php artisan flux-files:install
```

Publishes all necessary files and sets up the package.

### Publishing Individual Assets

```bash
# Publish configuration
php artisan vendor:publish --tag=flux-files-config

# Publish migrations
php artisan vendor:publish --tag=flux-files-migrations

# Publish models
php artisan vendor:publish --tag=flux-files-models

# Publish views
php artisan vendor:publish --tag=flux-files-views

# Publish language files
php artisan vendor:publish --tag=flux-files-lang

# Publish everything
php artisan vendor:publish --tag=flux-files
```

## Testing

Run the package tests:

```bash
composer test
```

Run code analysis:

```bash
composer analyse
```

Format code:

```bash
composer pint
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security

If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

## Credits

- [idkwhoami](https://github.com/idkwhoami)
- Built with [FluxUI](https://fluxui.dev)
- Powered by [Livewire](https://livewire.laravel.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

**Flux Files** - Making file management in Laravel applications elegant and effortless.
