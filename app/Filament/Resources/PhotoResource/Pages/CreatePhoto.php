<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhoto extends CreateRecord
{
    protected static string $resource = PhotoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}
