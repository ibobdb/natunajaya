<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstructors extends ListRecords
{
  protected static string $resource = InstructorResource::class;

  protected function getHeaderActions(): array
  {
    return [
      // Removing the create action to prevent new instructors from being created
      // Actions\CreateAction::make(),
    ];
  }
}
