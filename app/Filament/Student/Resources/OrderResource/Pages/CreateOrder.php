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

        // Redirect to payment page with the order ID
        $this->redirect(route('student.payment.show', ['order' => $order->id]), navigate: true);
    }
}
