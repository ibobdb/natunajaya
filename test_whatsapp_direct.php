<?php

// Enhanced test script for WhatsApp API connectivity testing
// This script runs independently of the Laravel framework

echo "=================================================\n";
echo "WhatsApp API Direct Connection Test Tool\n";
echo "=================================================\n\n";

// Check if .env file exists and load it
$dotenvLoaded = false;
if (file_exists(__DIR__ . '/.env')) {
  // Try to extract values from .env file
  $envContent = file_get_contents(__DIR__ . '/.env');
  preg_match('/WHATSAPP_API_URL=([^\n]*)/', $envContent, $apiUrlMatches);
  preg_match('/WHATSAPP_KEY=([^\n]*)/', $envContent, $apiKeyMatches);

  $apiUrl = isset($apiUrlMatches[1]) ? trim($apiUrlMatches[1]) : null;
  $apiKey = isset($apiKeyMatches[1]) ? trim($apiKeyMatches[1]) : null;

  if ($apiUrl && $apiKey) {
    echo "Environment variables loaded from .env file\n";
    $dotenvLoaded = true;
  }
}

// Manual configuration if not loaded from .env
if (!$dotenvLoaded) {
  // Configuration - update these values
  $apiUrl = "https://api.blastify.tech"; // Update with your API URL
  $apiKey = "YOUR_API_KEY"; // Replace with your actual API key

  echo "Using hardcoded configuration\n";
}

// Phone number to test with
$phoneNumber = "6282169072681"; // Replace with a valid test number

// Test mode - set to false to actually send messages
$testMode = true;

echo "Configuration:\n";
echo "API URL: " . $apiUrl . "\n";
echo "API Key: " . ($apiKey ? "Set (hidden)" : "Not set") . "\n";
echo "Test Phone: " . $phoneNumber . "\n";
echo "Test Mode: " . ($testMode ? "Yes (no messages will be sent)" : "No (will send real messages)") . "\n\n";

// Menu
echo "Select a test to run:\n";
echo "1. API Connection Test (no message sent)\n";
echo "2. Send Basic Test Message\n";
echo "3. Test With Custom Message\n";
echo "4. Exit\n\n";

// Get user input
$choice = readline("Enter your choice (1-4): ");

switch ($choice) {
  case '1':
    testApiConnection($apiUrl, $apiKey);
    break;
  case '2':
    if ($testMode) {
      echo "\nTest mode is ON - would send this message:\n";
      echo "\"Test WhatsApp message sent at " . date('Y-m-d H:i:s') . "\"\n";
      echo "To number: $phoneNumber\n";
    } else {
      sendTestMessage($apiUrl, $apiKey, $phoneNumber);
    }
    break;
  case '3':
    $message = readline("Enter your custom message: ");
    if ($testMode) {
      echo "\nTest mode is ON - would send this message:\n";
      echo "\"$message\"\n";
      echo "To number: $phoneNumber\n";
    } else {
      sendTestMessage($apiUrl, $apiKey, $phoneNumber, $message);
    }
    break;
  case '4':
    echo "Exiting.\n";
    exit(0);
  default:
    echo "Invalid choice.\n";
    exit(1);
}

/**
 * Test API connection without sending a message
 */
function testApiConnection($apiUrl, $apiKey)
{
  echo "\nTesting API connection...\n";

  // Make sure we have the base API URL without any endpoints
  $testUrl = rtrim(preg_replace('/\/api-keys\/send-messages$/', '', $apiUrl), '/');

  // Construct the health check endpoint
  $testUrl .= '/health';


  echo "URL: $testUrl\n";

  $ch = curl_init($testUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $apiKey
  ]);

  $response = curl_exec($ch);
  $error = curl_error($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  echo "HTTP Code: $httpCode\n";

  if ($error) {
    echo "Error: $error\n\n";
  } else {
    echo "Response: $response\n\n";

    if ($httpCode >= 200 && $httpCode < 300) {
      echo "Connection test successful!\n";
    } else {
      echo "Connection test failed. Check your API URL and key.\n";
    }
  }
}

/**
 * Send a test message
 */
function sendTestMessage($apiUrl, $apiKey, $phoneNumber, $customMessage = null)
{
  echo "\nSending test message to WhatsApp API...\n";

  if (!str_contains($apiUrl, 'send-messages')) {
    if (str_contains($apiUrl, '/api-keys')) {
      $apiUrl .= '/send-messages';
    } else {
      $apiUrl .= '/api-keys/send-messages';
    }
  }

  echo "URL: $apiUrl\n";

  $content = $customMessage ?? "Test WhatsApp message sent at " . date('Y-m-d H:i:s');

  $payload = [
    'numbers' => [$phoneNumber],
    'content' => $content
  ];

  echo "Message: \"$content\"\n";
  echo "To: $phoneNumber\n\n";

  $ch = curl_init($apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $apiKey
  ]);

  $response = curl_exec($ch);
  $error = curl_error($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  echo "HTTP Code: $httpCode\n";

  if ($error) {
    echo "Error: $error\n\n";
  } else {
    echo "Response: $response\n\n";

    if ($httpCode >= 200 && $httpCode < 300) {
      echo "Message sent successfully!\n";
    } else {
      echo "Failed to send message. Check your API URL and key.\n";
    }
  }
}

echo "\nTest completed.\n";
