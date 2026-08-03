<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use App\Models\Person;
use Filament\Resources\Pages\Page;

class ManageTags extends Page
{
    protected static string $resource = PhotoResource::class;

    protected static string $view = 'filament.resources.photo-resource.pages.manage-tags';

    public $record;

    public function mount(int|string $record): void
    {
        $this->record = static::getResource()::resolveRecordRouteBinding($record);
    }

    public function getTitle(): string
    {
        return 'Personen markieren: '.$this->record->title;
    }

    protected function getViewData(): array
    {
        return [
            'photo' => $this->record->load('personTags.person', 'personTags.suggestedBy'),
            'people' => Person::orderBy('last_name')->orderBy('first_name')->get(),
        ];
    }
}
