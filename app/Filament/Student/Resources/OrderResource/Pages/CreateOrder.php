<?php

namespace App\Filament\Student\Resources\OrderResource\Pages;

use App\Filament\Student\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    // Override the afterCreate method to redirect to payment page
    protected function afterCreate(): void
    {
        // Get the created order
        $order = $this->record;

        // Redirect to the correct URL format: /student/payment?inv=invoice_id
        $this->redirect("/student/payment?inv={$order->invoice_id}", navigate: true);
    }

    protected function getRedirectUrl(): string
    {
        return "/student/payment?inv={$this->record->invoice_id}";
    }
}
