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

    "user" => [
      'column_name' => 'name', // Change the value if your field in users table is different from "name"
    ],
];
