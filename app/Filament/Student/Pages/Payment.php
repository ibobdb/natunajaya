<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Request;

class Payment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Payment';
    protected static string $view = 'filament.student.pages.payment';
    protected static bool $shouldRegisterNavigation = false;

    public string $invoiceNumber = ''; // Initialize with default empty string
    public $order = null;
    public function mount(): void
    {
        // Get invoice number from query parameter
        $this->invoiceNumber = Request::query('inv') ?? '';
        $this->order = \App\Models\Order::where('invoice_id', $this->invoiceNumber)
            ->with(['course', 'student'])
            ->first();
    }
    // protected function getViewData(): array
    // {
    //     $order = \App\Models\Order::where('invoice_id', $this->invoiceNumber)->first();

    //     return [
    //         'order' => $order,
    //         // Add any other data needed by your view
    //     ];
    // }
}
