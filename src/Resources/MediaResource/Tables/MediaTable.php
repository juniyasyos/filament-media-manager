<?php

namespace Juniyasyos\FilamentMediaManager\Resources\MediaResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
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
                ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Media Details')
                        ->modalContent(fn ($record) => view('filament-media-manager::modals.media-preview', ['media' => $record])),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (Media $record) => $record->getUrl())
                        ->openUrlInNewTab(),
                    \Filament\Actions\DeleteAction::make()
                        ->before(function (Media $record) {
                            // Clean up media files
                            $record->delete();
                        }),
                ])->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Clean up media files for bulk delete
                            foreach ($records as $record) {
                                $record->delete();
                            }
                        }),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_name')
                    ->label('Collection')
                    ->options(function () {
                        return Media::query()
                            ->distinct()
                            ->pluck('collection_name', 'collection_name')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('File Type')
                    ->options([
                        'image/jpeg' => 'JPEG Image',
                        'image/png' => 'PNG Image',
                        'image/gif' => 'GIF Image',
                        'image/svg+xml' => 'SVG Image',
                        'application/pdf' => 'PDF Document',
                        'video/mp4' => 'MP4 Video',
                        'video/webm' => 'WebM Video',
                    ]),
                Tables\Filters\Filter::make('large_files')
                    ->label('Large Files (>1MB)')
                    ->query(fn ($query) => $query->where('size', '>', 1048576)),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('upload_multiple')
                    ->label('Upload Multiple Files')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('primary')
                    ->visible(fn () => request()->has('folder_id')),
            ]);
    }
}

