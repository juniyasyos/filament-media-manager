<?php

return [
    "model" => [
        "folder" => \Juniyasyos\FilamentMediaManager\Models\Folder::class,
        "media" => \Juniyasyos\FilamentMediaManager\Models\Media::class,
    ],

    "api" => [
        "active" => false,
        "middlewares" => [
            "api",
            "auth:sanctum"
        ],
        "prefix" => "api/media-manager",
        "resources" => [
            "folders" => \Juniyasyos\FilamentMediaManager\Http\Resources\FoldersResource::class,
            "folder" => \Juniyasyos\FilamentMediaManager\Http\Resources\FolderResource::class,
            "media" => \Juniyasyos\FilamentMediaManager\Http\Resources\MediaResource::class
        ]
    ],

    "filament" => [
        "active" => true,
        "resources" => [
            \Juniyasyos\FilamentMediaManager\Resources\FolderResource::class,
            \Juniyasyos\FilamentMediaManager\Resources\MediaResource::class,
        ]
    ],

    "user" => [
        'column_name' => 'name',
    ],

    'allow_user_access' => true,

    'slug_folder' => 'folder',

    "navigation_sort" => 0,

    // Additional plugin defaults
    'allow_subfolders' => true,
    'allow_user_access' => true,
    'allow_create_subfolder' => true,
    'allow_edit_folder' => true,
    'allow_delete_folder' => true,
    'allow_create_media' => true,
    'allow_edit_media' => true,
    'allow_delete_media' => true,
];
