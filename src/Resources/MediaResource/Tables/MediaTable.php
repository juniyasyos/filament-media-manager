<?php

namespace Juniyasyos\FilamentMediaManager\Resources\MediaResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class MediaTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (request()->has('folder_id') && !empty('folder_id')) {
                    $folder = Folder::find(request()->get('folder_id'));
                    if ($folder) {
                        $query->where('collection_name', $folder->collection);
                    }
                }
            })
            ->emptyState(fn() => view('filament-media-manager::pages.media'))
            ->content(function () {
                return view('filament-media-manager::pages.media');
            })
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('image')
                        ->width('250px')
                        ->height('250px')
                        ->square()
                        ->label(trans('filament-media-manager::messages.media.columns.image'))
                        ->default(function (Media $media) {
                            return $media->getUrl();
                        }),
                ]),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('model.name')
                        ->label(trans('filament-media-manager::messages.media.columns.model'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('collection_name')
                        ->label(trans('filament-media-manager::messages.media.columns.collection_name'))
                        ->badge()
                        ->icon('heroicon-o-folder')
                        ->searchable(),
                ]),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label(trans('filament-media-manager::messages.media.columns.name'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('file_name')
                        ->label(trans('filament-media-manager::messages.media.columns.file_name'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('mime_type')
                        ->label(trans('filament-media-manager::messages.media.columns.mime_type'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('disk')
                        ->label(trans('filament-media-manager::messages.media.columns.disk'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('conversions_disk')
                        ->label(trans('filament-media-manager::messages.media.columns.conversions_disk'))
                        ->searchable(),
                    Tables\Columns\TextColumn::make('size')
                        ->label(trans('filament-media-manager::messages.media.columns.size'))
                        ->numeric()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('order_column')
                        ->label(trans('filament-media-manager::messages.media.columns.order_column'))
                        ->numeric()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultSort('order_column', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

