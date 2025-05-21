<?php

namespace App\Filament\Resources\InstructorResource\Pages;

use App\Filament\Resources\InstructorResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManageInstructors extends ManageRecords
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data, $livewire) {
                    return DB::transaction(function () use ($data) {
                        // Create user with instructor role
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password']),
                            'role' => 'instructor', // Set role directly in the users table
                        ]);

                        // Create instructor record - Fix for get_class() error
                        $modelClass = $this->getModel();
                        $instructor = new $modelClass();
                        $instructor->fill([
                            'name' => $data['name'],
                            'user_id' => $user->id,
                        ]);
                        $instructor->save();

                        return $instructor;
                    });
                }),
        ];
    }
}
