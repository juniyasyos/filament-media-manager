<?php

namespace Juniyasyos\FilamentMediaManager\Resources\Actions;

use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;

class CreateSubFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        return Actions\Action::make('create_sub_folder')
            ->hidden(fn()=> !filament('filament-media-manager')->allowSubFolders)
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->color('info')
            ->hiddenLabel()
            ->tooltip(trans('filament-media-manager::messages.media.actions.sub_folder.label'))
            ->label(trans('filament-media-manager::messages.media.actions.sub_folder.label'))
            ->icon('heroicon-o-folder-minus')
            ->form([
                Section::make('Basic Information')
                    ->description('Enter folder name and basic details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(trans('filament-media-manager::messages.folders.columns.name'))
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('collection', Str::slug($get('name')));
                            })
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('collection')
                            ->label(trans('filament-media-manager::messages.folders.columns.collection'))
                            ->columnSpanFull()
                            ->unique(Folder::class)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(trans('filament-media-manager::messages.folders.columns.description'))
                            ->columnSpanFull()
                            ->maxLength(255),
                    ])->columns(1),

                Section::make('Appearance Settings')
                    ->description('Customize folder appearance')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->label(trans('filament-media-manager::messages.folders.columns.icon'))
                            ->placeholder('heroicon-o-folder')
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('color')
                            ->label(trans('filament-media-manager::messages.folders.columns.color')),
                    ])->columns(2),

                Section::make('Security')
                    ->description('Configure password protection')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Toggle::make('is_protected')
                            ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('password')
                            ->label(trans('filament-media-manager::messages.folders.columns.password'))
                            ->hidden(fn($get) => !$get('is_protected'))
                            ->password()
                            ->revealable()
                            ->required(fn($get) => $get('is_protected'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(trans('filament-media-manager::messages.folders.columns.password_confirmation'))
                            ->hidden(fn($get) => !$get('is_protected'))
                            ->password()
                            ->required(fn($get) => $get('is_protected'))
                            ->revealable()
                            ->same('password')
                            ->maxLength(255)
                    ])->columns(1)
            ])
            ->action(function (array $data) use ($folder_id) {
                $parentFolder = Folder::find($folder_id);
                if ($parentFolder) {
                    $data['user_id'] = auth()->user()->id;
                    $data['user_type'] = get_class(auth()->user());
                    $data['parent_id'] = $folder_id;
                    $data['model_id'] = $parentFolder->model_id;
                    $data['model_type'] = $parentFolder->model_type;

                    $subFolder = Folder::create($data);

                    // Update path and depth automatically via model observers
                    $subFolder->updatePath();

                    Notification::make()
                        ->title('Subfolder Created Successfully')
                        ->body("Subfolder '{$data['name']}' has been created in '{$parentFolder->name}'")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error Creating Subfolder')
                        ->body('Parent folder not found')
                        ->danger()
                        ->send();
                }
            });
    }
}
