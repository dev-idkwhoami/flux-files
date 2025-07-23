<?php

return [

    "tenancy" => [
        "enabled" => false,
        "class" => null // your Tenant class e.g. App\Models\Tenant::class
    ],

    "eloquent" => [
        "id_type" => 'bigint', // can be either 'ulid', 'uuid', 'bigint'
        "folder" => [
            "class" => \App\Models\Folder::class,
        ],
        "file" => [
            "class" => \App\Models\File::class,
        ],
    ]

];
