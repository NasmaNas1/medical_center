<?php

namespace App\Filament\Resources\SubSpecializationResource\Pages;

use App\Filament\Resources\SubSpecializationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubSpecialization extends EditRecord
{
    protected static string $resource = SubSpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
