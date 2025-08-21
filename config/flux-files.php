<?php

use Idkwhoami\FluxFiles\Enums\FileExtension;
use Idkwhoami\FluxFiles\Enums\MimeType;

return [

    'storage' => [
        'disk' => env('FLUX_FILES_DISK', 'local'),
    ],

    'localization' => [
        'en' => [
            'formats' => [
                'date' => 'd/m/Y',
                'datetime' => 'd/m/Y H:i:s',
            ],
        ],
        'de' => [
            'formats' => [
                'date' => 'd.m.Y',
                'datetime' => 'd.m.Y H:i:s',
            ]
        ]
    ],

    'validation' => [
        'allowed_extensions' => FileExtension::allExtensions(),

        'allowed_mime_types' => MimeType::allowedMimeTypes(),

        'blocked_mime_types' => MimeType::blockedMimeTypes(),

        'max_file_size' => env('FLUX_FILES_MAX_SIZE', 10485760), // 10MB in bytes
        'max_files_per_upload' => env('FLUX_FILES_MAX_COUNT', 10),
    ],

    'upload' => [
        'chunk_size' => env('FLUX_FILES_CHUNK_SIZE', 1048576), // 1MB
        'chunking_enabled' => env('FLUX_FILES_CHUNKING_ENABLED', true),
        'max_parallel_uploads' => env('FLUX_FILES_MAX_PARALLEL', 3),
        'min_file_size_for_chunking' => env('FLUX_FILES_MIN_CHUNK_SIZE', 5242880), // 5MB
        'timeout' => env('FLUX_FILES_TIMEOUT', 120), // 2 minutes
        'temp_directory' => storage_path('app/private/flux-files'),
        'auto_cleanup_temp' => true,
        'cleanup_interval' => 3600 // 1 hour
    ],

    'thumbnails' => [
        'enabled' => true,
        'max_width' => 300,
        'max_height' => 300,
        'quality' => 85,
        'formats' => FileExtension::imageExtensions()
    ],

    'ui' => [
        'items_per_page' => env('FLUX_FILES_ITEMS_PER_PAGE', 20),
        'default_view_mode' => env('FLUX_FILES_VIEW_MODE', 'grid'), // 'grid', 'list'
        'breadcrumbs_max_items' => 5,
        'file_icons' => [
            'image' => 'image',
            'video' => 'video',
            'audio' => 'music',
            'document' => 'file-text',
            'archive' => 'folder-archive',
            'default' => 'file-question-mark'
        ]
    ],

    'security' => [
        'scan_uploads' => true,
        'log_security_events' => true,
        'max_filename_length' => 255,
        'sanitize_filenames' => true
    ],

    "tenancy" => [
        "enabled" => env('FLUX_FILES_TENANCY', false),
        "model" => null, // your Tenant class e.g. App\Models\Tenant::class
    ],

    "eloquent" => [
        "prefix" => env('FLUX_FILES_TABLE_PREFIX'),
        "id_type" => env('FLUX_FILES_ID_TYPE', 'bigint'), // can be either 'ulid', 'uuid', 'bigint'
        "folder" => [
            "model" => \Idkwhoami\FluxFiles\Models\Folder::class,
        ],
        "file" => [
            "model" => \Idkwhoami\FluxFiles\Models\File::class,
        ],
    ],

    'permissions' => [
        'enabled' => false,
        'policies' => [
            'file' => null,
            'folder' => null,
        ]
    ],

    'events' => [
        'log_file_operations' => true,
        'listeners' => [
            'file_uploaded' => [],
            'file_deleted' => [],
            'file_moved' => [],
            'file_copied' => []
        ]
    ]

];
