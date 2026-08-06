<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use App\Models\Category;
use App\Models\Location;
use Filament\Resources\Pages\Page;

class BulkUploadPhotos extends Page
{
    protected static string $resource = PhotoResource::class;

    protected static string $view = 'filament.resources.photo-resource.pages.bulk-upload-photos';

    protected static ?string $title = 'Sammelupload';

    public function getCategories()
    {
        return Category::orderBy('order')->get();
    }

    public function getLocations()
    {
        return Location::orderBy('name')->get();
    }
}
