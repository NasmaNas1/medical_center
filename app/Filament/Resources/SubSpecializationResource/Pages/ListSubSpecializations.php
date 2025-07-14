<?php

namespace App\Filament\Resources\SubSpecializationResource\Pages;

use App\Filament\Resources\SubSpecializationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubSpecializations extends ListRecords
{
    protected static string $resource = SubSpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
