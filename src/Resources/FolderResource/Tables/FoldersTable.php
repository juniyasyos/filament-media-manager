<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Tables;

use Filament\Tables;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FoldersTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Handle hierarchy navigation
                $parentId = request()->get('parent_id');

                if ($parentId !== null) {
                    // Show children of specific parent
                    $query->where('parent_id', $parentId === 'root' ? null : $parentId);
                } else if (request()->has('model_type') && !request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->where('model_id', null)
                        ->whereNotNull('collection')
                        ->where('parent_id', null); // Only show root folders for this case
                } else if (request()->has('model_type') && request()->has('collection')) {
                    $query->where('model_type', request()->get('model_type'))
                        ->whereNotNull('model_id')
                        ->where('collection', request()->get('collection'))
                        ->where('parent_id', null); // Only show root folders for this case
                } else {
                    // Show root level folders (parent_id is null)
                    $query->where('parent_id', null)
                        ->where(function ($query) {
                            $query->where('model_id', null)
                                ->where('collection', null)
                                ->orWhere('model_type', null);
                        });
                }
            })
            ->content(fn() => view('filament-media-manager::pages.folders'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    // ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('depth')
                    ->label('Level')
                    // ->sortable()
                    // ->toggleable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 0 => 'primary',
                        $state <= 2 => 'warning',
                        default => 'success'
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Folder')
                    ->searchable()
                    // ->toggleable()
                    ->placeholder('Root Level'),
                Tables\Columns\TextColumn::make('path')
                    ->label('Full Path')
                    ->searchable()
                    // ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([
                "12",
                "24",
                "48",
                "96",
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('depth')
                    ->label('Folder Level')
                    ->options([
                        0 => 'Root Level',
                        1 => 'Level 1',
                        2 => 'Level 2',
                        3 => 'Level 3+',
                    ])
                    ->query(function ($query, array $data) {
                        if (isset($data['value'])) {
                            if ($data['value'] == 3) {
                                return $query->where('depth', '>=', 3);
                            }
                            return $query->where('depth', $data['value']);
                        }
                        return $query;
                    }),
                Tables\Filters\TernaryFilter::make('is_protected')
                    ->label('Password Protected')
                    ->placeholder('All folders')
                    ->trueLabel('Protected only')
                    ->falseLabel('Unprotected only'),
            ])
            ->actions([
                ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->label('View Contents')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => request()->url() . '?parent_id=' . $record->id),
                    \Filament\Actions\EditAction::make()
                        ->visible(fn () => hexa()->can('folder.update')),
                    \Filament\Actions\DeleteAction::make()
                        ->visible(fn () => hexa()->can('folder.delete'))
                        ->before(function ($record) {
                            // Clean up hierarchy when deleting
                            $record->folders()->delete();
                        }),
                ])->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
            ])
            ->bulkActions([
                // \Filament\Actions\BulkActionGroup::make([
                //     \Filament\Actions\DeleteBulkAction::make()
                //         ->visible(fn () => hexa()->can('folder.delete'))
                //         ->before(function ($records) {
                //             // Clean up hierarchy for bulk delete
                //             foreach ($records as $record) {
                //                 $record->folders()->delete();
                //             }
                //         }),
                // ]),
            ]);
    }
}

