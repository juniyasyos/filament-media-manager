<?php

namespace Juniyasyos\FilamentMediaManager\Resources\Actions;

use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;

class EditCurrentFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        $form = config('filament-media-manager.model.folder')::query()->where('id',$folder_id)->with('users')->first()?->toArray();
        $form['users'] = collect($form['users'])->pluck('id')->toArray();

        return Actions\Action::make('edit_current_folder')
            ->hiddenLabel()
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->tooltip(trans('filament-media-manager::messages.media.actions.edit.label'))
            ->label(trans('filament-media-manager::messages.media.actions.edit.label'))
            ->icon('heroicon-o-pencil-square')
            ->color('warning')
            ->form(function (){
                return [
                    Section::make('Basic Information')
                        ->description('Update folder name and description')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('filament-media-manager::messages.folders.columns.name'))
                                ->columnSpanFull()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->label(trans('filament-media-manager::messages.folders.columns.description'))
                                ->columnSpanFull()
                                ->maxLength(255),
                        ])
                        ->columns(1),

                    Section::make('Appearance')
                        ->description('Customize folder appearance')
                        ->schema([
                            Forms\Components\TextInput::make('icon')
                                ->label(trans('filament-media-manager::messages.folders.columns.icon'))
                                ->placeholder('heroicon-o-folder')
                                ->maxLength(255),
                            Forms\Components\ColorPicker::make('color')
                                ->label(trans('filament-media-manager::messages.folders.columns.color')),
                        ])
                        ->columns(2),

                    Section::make('Security Settings')
                        ->description('Configure folder access and protection')
                        ->schema([
                            Forms\Components\Toggle::make('is_protected')
                                ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('password')
                                ->label(trans('filament-media-manager::messages.folders.columns.password'))
                                ->hidden(fn($get) => !$get('is_protected'))
                                ->confirmed()
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
                                ->maxLength(255),
                        ])
                        ->columns(1),

                    Section::make('User Access Control')
                        ->description('Manage folder sharing and user permissions')
                        ->visible(filament('filament-media-manager')->allowUserAccess)
                        ->schema([
                            Forms\Components\Toggle::make('is_public')
                                ->label(trans('filament-media-manager::messages.folders.columns.is_public'))
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('has_user_access')
                                ->hidden(fn($get) => $get('is_public'))
                                ->label(trans('filament-media-manager::messages.folders.columns.has_user_access'))
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('users')
                                ->required(fn($get) => $get('has_user_access'))
                                ->hidden(fn($get) => !$get('has_user_access'))
                                ->label(trans('filament-media-manager::messages.folders.columns.users'))
                                ->searchable()
                                ->multiple()
                                ->options(User::query()->where('id', '!=', auth()->user()->id)->pluck(config('filament-media-manager.user.column_name'), 'id')->toArray())
                        ])
                        ->columns(1)
                ];
            })
            ->fillForm($form)
            ->action(function (array $data) use ($folder_id){
                $folder = config('filament-media-manager.model.folder')::find($folder_id);

                if ($folder) {
                    // Handle password change properly
                    if (!empty($data['password'])) {
                        $data['password'] = bcrypt($data['password']);
                    } else {
                        unset($data['password'], $data['password_confirmation']);
                    }

                    $folder->update($data);

                    // Sync users if provided
                    if (isset($data['users'])) {
                        $folder->users()->sync($data['users']);
                    }

                    // Update folder path if name changed
                    if ($folder->wasChanged('name')) {
                        $folder->updatePath();
                    }

                    Notification::make()
                        ->title('Folder Updated Successfully')
                        ->body("Folder '{$folder->name}' has been updated")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error Updating Folder')
                        ->body('Folder not found')
                        ->danger()
                        ->send();
                }
            });
    }
}
