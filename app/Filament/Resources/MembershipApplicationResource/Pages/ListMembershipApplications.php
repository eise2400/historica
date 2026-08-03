<?php

namespace App\Filament\Resources\MembershipApplicationResource\Pages;

use App\Filament\Resources\MembershipApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembershipApplications extends ListRecords
{
    protected static string $resource = MembershipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
