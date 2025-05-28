<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransHelper
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
        
        // For debugging
        Log::info('Midtrans Config: ', [
            'serverKey' => Config::$serverKey ? 'Set (hidden)' : 'Not set',
            'clientKey' => Config::$clientKey ? 'Set (hidden)' : 'Not set',
            'isProduction' => Config::$isProduction,
            'apiUrl' => Config::$isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com'
        ]);
    }

    /**
     * Create Snap payment transaction
     *
     * @param array $params Payment parameters
     * @return string Snap token
     */
    public function createTransaction(array $params)
    {
        try {
            // Log the parameters (exclude sensitive data)
            Log::info('Creating Midtrans transaction with params', [
                'order_id' => $params['transaction_details']['order_id'] ?? 'not set',
                'gross_amount' => $params['transaction_details']['gross_amount'] ?? 'not set'
            ]);
            
            // Create Snap token
            $snapToken = Snap::getSnapToken($params);
            
            Log::info('Snap token created successfully', ['token' => $snapToken ? 'Set (hidden)' : 'Not created']);
            
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans transaction: ' . $e->getMessage());
            throw $e;
        }
    }
}
