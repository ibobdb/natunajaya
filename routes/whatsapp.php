<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| WhatsApp API Routes
|--------------------------------------------------------------------------
*/

// WhatsApp API status check
Route::get('/api/whatsapp/status', function (Request $request) {
  $controller = new WhatsappController();
  return response()->json($controller->checkApiStatus());
})->middleware(['auth:sanctum']);

// Send a WhatsApp message
Route::post('/api/whatsapp/send', function (Request $request) {
  try {
    $request->validate([
      'phone' => 'required|string',
      'message' => 'required|string',
      'name' => 'nullable|string',
    ]);

    $controller = new WhatsappController();
    $result = $controller->sendMessage(
      $request->phone,
      $request->message
    );

    return response()->json([
      'status' => 'success',
      'message' => 'WhatsApp message sent',
      'data' => $result
    ]);
  } catch (\Exception $e) {
    Log::error('WhatsApp API Error: ' . $e->getMessage());

    return response()->json([
      'status' => 'error',
      'message' => $e->getMessage(),
    ], 500);
  }
})->middleware(['auth:sanctum']);

// Send a template message
Route::post('/api/whatsapp/send-template', function (Request $request) {
  try {
    $request->validate([
      'phone' => 'required|string',
      'template' => 'required|string',
      'name' => 'required|string',
      'data' => 'nullable|array',
    ]);

    $controller = new WhatsappController();
    $templateMethod = 'get' . ucfirst($request->template) . 'Template';

    // Check if template method exists
    if (!method_exists($controller, $templateMethod)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Template not found: ' . $request->template
      ], 404);
    }

    // Generate content from template
    $params = array_merge([$request->name], $request->data ?? []);
    $content = call_user_func_array([$controller, $templateMethod], $params);

    // Send message
    $result = $controller->sendMessage(
      $request->phone,
      $content
    );

    return response()->json([
      'status' => 'success',
      'message' => 'WhatsApp template message sent',
      'template' => $request->template,
      'data' => $result
    ]);
  } catch (\Exception $e) {
    Log::error('WhatsApp API Template Error: ' . $e->getMessage());

    return response()->json([
      'status' => 'error',
      'message' => $e->getMessage(),
    ], 500);
  }
})->middleware(['auth:sanctum']);
