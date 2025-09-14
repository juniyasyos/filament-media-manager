<?php

namespace Juniyasyos\FilamentMediaManager\Resources\MediaResource\Schemas;

use Filament\Schemas\Schema;

class MediaForm
{
    public static function schema(Schema $schema): Schema
    {
        // Media resource currently does not define a form.
        // Keep passthrough to comply with Filament v4 structure.
        return $schema;
    }
}

