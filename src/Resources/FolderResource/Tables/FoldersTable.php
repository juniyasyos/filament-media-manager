<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FoldersTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (request()->has('model_type') && !request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->where('model_id', null)
                        ->whereNotNull('collection');
                } else if (request()->has('model_type') && request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->whereNotNull('model_id')
                        ->where('collection', request()->get('collection'));
                } else {
                    $query->where('model_id', null)
                        ->where('collection', null)->orWhere('model_type', null);
                }
            })
            ->content(fn() => view('filament-media-manager::pages.folders'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}

