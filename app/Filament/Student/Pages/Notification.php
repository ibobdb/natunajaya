<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Auth;

class Notification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.student.pages.notification';
    protected static ?string $navigationLabel = 'Aktifkan Notifikasi';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'phone' => $user->phone ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('phone')
                    ->label('Nomor HP')
                    ->required('Nomor HP wajib diisi')
                    ->tel()
                    ->placeholder('Contoh: 08123456789')
                    ->minLength(11, 'Nomor HP minimal 11 karakter')
                    ->maxLength(13, 'Nomor HP maksimal 13 karakter')
                    ->regex('/^08\d{9,11}$/', 'Nomor HP harus diawali dengan 08 dan hanya berisi angka'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Add a small delay to show the loading indicator
        sleep(1);

        // Save the phone number to the user record
        $user = Auth::user();
        $user->phone = $data['phone'];
        $user->save();

        FilamentNotification::make()
            ->title('Nomor HP berhasil diperbaharui')
            ->body('Notifikasi WhatsApp telah diaktifkan untuk nomor ' . $data['phone'])
            ->success()
            ->send();
    }
}
