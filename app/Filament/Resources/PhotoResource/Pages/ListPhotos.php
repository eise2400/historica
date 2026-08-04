<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotos extends ListRecords
{
    protected static string $resource = PhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulkUpload')
                ->label('Sammelupload')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(fn () => PhotoResource::getUrl('bulk-upload')),
            Actions\CreateAction::make()
                ->label('Foto hinzufügen'),
        ];
    }
}
