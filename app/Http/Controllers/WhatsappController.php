<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Schedule;
use App\Models\Order;
use App\Models\Car;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsappController extends Controller
{
  /**
   * Base URL for WhatsApp API
   * 
   * @return string The API base URL
   */
  public function getWhatsappApiUrl()
  {
    return env('WHATSAPP_API_URL', 'http://localhost');
  }

  /**
   * Get the health check endpoint URL
   * 
   * @return string The health check endpoint URL
   */
  public function getHealthCheckUrl()
  {
    return $this->getWhatsappApiUrl() . '/health';
  }

  /**
   * Get the send message endpoint URL
   * 
   * @return string The send message endpoint URL
   */
  public function getSendMessageUrl()
  {
    return $this->getWhatsappApiUrl() . '/api-keys/send-messages';
  }

  /**
   * Maximum retry attempts for failed messages
   */
  protected int $maxRetries = 3;

  /**
   * Retry delay in seconds
   */
  protected int $retryDelay = 5;

  /**
   * Send WhatsApp message with retry mechanism
   *
   * @param array|string $numbers
   * @param string $content
   * @param int|null $currentAttempt
   * @return array
   */  public function sendMessage($numbers, $content, ?int $currentAttempt = 0)
  {
    try {
      // Use the dedicated method to get the send message endpoint URL
      $url = $this->getSendMessageUrl();

      $apiKey = env('WHATSAPP_KEY', '');

      // Validate and format phone numbers
      $validatedNumbers = $this->formatPhoneNumbers($numbers);

      // If no valid numbers, return error
      if (empty($validatedNumbers)) {
        Log::error("WhatsApp API: No valid phone numbers provided");
        return [
          'status' => 'error',
          'message' => 'No valid phone numbers provided'
        ];
      }

      // Create a debug log file
      file_put_contents(
        storage_path('logs/whatsapp_debug.log'),
        date('Y-m-d H:i:s') . " - Sending WhatsApp message (Attempt: " . ($currentAttempt + 1) . ")\n" .
          "URL: {$url}\n" .
          "Has API Key: " . (!empty($apiKey) ? "Yes" : "No") . "\n" .
          "Numbers: " . json_encode($validatedNumbers) . "\n",
        FILE_APPEND
      );

      $payload = [
        'numbers' => $validatedNumbers,
        'content' => $content
      ];

      Log::info('WhatsApp: Sending API request', [
        'url' => $url,
        'numbers' => is_array($numbers) ? $numbers : [$numbers],
        'attempt' => $currentAttempt + 1
      ]);

      $response = Http::withHeaders([
        'x-api-key' => $apiKey
      ])->timeout(15)->post($url, $payload);

      file_put_contents(
        storage_path('logs/whatsapp_debug.log'),
        date('Y-m-d H:i:s') . " - Response received\n" .
          "Status: " . $response->status() . "\n" .
          "Body: " . $response->body() . "\n",
        FILE_APPEND
      );

      // Check if the response indicates a failure that should be retried
      $shouldRetry = !$response->successful() && $currentAttempt < $this->maxRetries;

      if ($shouldRetry) {
        $currentAttempt++;
        Log::warning("WhatsApp API request failed. Retrying ({$currentAttempt}/{$this->maxRetries})...", [
          'status' => $response->status(),
          'body' => $response->body()
        ]);

        // Wait before retrying
        sleep($this->retryDelay);

        // Recursive retry
        return $this->sendMessage($numbers, $content, $currentAttempt);
      }
      $result = $response->json() ?: ['status' => 'success', 'code' => $response->status()];

      // If this was a final attempt and still failed, record for later retry
      if (!$response->successful() && $currentAttempt >= $this->maxRetries) {
        $recipientNumber = is_array($numbers) ? ($numbers[0] ?? 'unknown') : $numbers;
        $this->recordFailedMessage($recipientNumber, null, $content, 'API response: ' . $response->body());
      }

      return $result;
    } catch (\Exception $e) {
      Log::error('WhatsApp API Error: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'attempt' => $currentAttempt + 1
      ]);

      file_put_contents(
        storage_path('logs/whatsapp_debug.log'),
        date('Y-m-d H:i:s') . " - Error\n" .
          "Attempt: " . ($currentAttempt + 1) . "\n" .
          "Message: " . $e->getMessage() . "\n" .
          "Trace: " . $e->getTraceAsString() . "\n",
        FILE_APPEND
      );

      // Retry on exception if we haven't reached max retries
      if ($currentAttempt < $this->maxRetries) {
        $currentAttempt++;
        Log::warning("WhatsApp API request threw an exception. Retrying ({$currentAttempt}/{$this->maxRetries})...");

        // Wait before retrying
        sleep($this->retryDelay);

        // Recursive retry
        return $this->sendMessage($numbers, $content, $currentAttempt);
      }

      // Record the failed message for later retry
      $recipientNumber = is_array($numbers) ? ($numbers[0] ?? 'unknown') : $numbers;
      $this->recordFailedMessage($recipientNumber, null, $content, $e->getMessage());

      return [
        'status' => 'error',
        'message' => $e->getMessage()
      ];
    }
  }

  /**
   * Send welcome message after registration
   *
   * @param \App\Models\Student $student
   * @return array
   */
  public function sendWelcomeMessage(Student $student)
  {
    try {
      $template = $this->getWelcomeTemplate($student->name);
      return $this->sendMessage($student->user->phone, $template);
    } catch (\Exception $e) {
      Log::error('WhatsApp Error: ' . $e->getMessage());
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Send schedule update notification
   *
   * @param \App\Models\Student $student
   * @param \App\Models\Schedule $schedule
   * @return array
   */
  public function sendScheduleUpdateNotification(Student $student, Schedule $schedule)
  {
    try {
      // Write to debug log
      file_put_contents(
        storage_path('logs/whatsapp_debug.log'),
        date('Y-m-d H:i:s') . " - Schedule update notification\n" .
          "Student ID: {$student->id}, Name: {$student->name}\n" .
          "Phone: " . ($student->user->phone ?? 'No phone found') . "\n" .
          "Schedule ID: {$schedule->id}\n",
        FILE_APPEND
      );

      $template = $this->getScheduleUpdateTemplate($student->name, $schedule);
      $result = $this->sendMessage($student->user->phone, $template);

      return $result;
    } catch (\Exception $e) {
      Log::error('WhatsApp Error: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
      ]);

      file_put_contents(
        storage_path('logs/whatsapp_debug.log'),
        date('Y-m-d H:i:s') . " - Error sending schedule notification\n" .
          "Error: " . $e->getMessage() . "\n",
        FILE_APPEND
      );

      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Send payment confirmation
   *
   * @param \App\Models\Student $student
   * @param \App\Models\Order $order
   * @return array
   */
  public function sendPaymentConfirmation(Student $student, Order $order)
  {
    try {
      $template = $this->getPaymentConfirmationTemplate($student->name, $order);
      return $this->sendMessage($student->user->phone, $template);
    } catch (\Exception $e) {
      Log::error('WhatsApp Error: ' . $e->getMessage());
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Send reminder message
   *
   * @param \App\Models\Student $student
   * @param \App\Models\Schedule $schedule
   * @return array
   */
  public function sendReminderMessage(Student $student, Schedule $schedule)
  {
    try {
      $template = $this->getReminderTemplate($student->name, $schedule);
      return $this->sendMessage($student->user->phone, $template);
    } catch (\Exception $e) {
      Log::error('WhatsApp Error: ' . $e->getMessage());
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Get welcome template message
   *
   * @param string $name
   * @return string
   */    public function getWelcomeTemplate($name)
  {
    return "Halo {$name}! 👋\n\n" .
      "Selamat datang di *Natuna Jaya Driving School*! 🚗\n\n" .
      "Terima kasih telah mendaftar di kursus kami. Kami sangat senang Anda memilih kami untuk membantu Anda mendapatkan SIM dan menjadi pengemudi yang terampil.\n\n" .
      "Tim kami akan segera menghubungi Anda untuk mengkonfirmasi jadwal kursus pertama Anda.\n\n" .
      "Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.\n\n" .
      "Salam,\n" .
      "Tim Natuna Jaya Driving School";
  }
  /**
   * Get schedule update template message
   *
   * @param string $name
   * @param \App\Models\Schedule $schedule
   * @return string
   */    public function getScheduleUpdateTemplate($name, Schedule $schedule)
  {
    try {
      // Safely get date and time
      $dateTime = "Jadwal belum ditentukan";
      if ($schedule->start_date) {
        $dateTime = $schedule->start_date->format('d M Y - H:i');
      }

      // Safely get instructor name
      $instructor = 'Instruktor';
      if ($schedule->instructor_id && $schedule->instructor) {
        $instructor = $schedule->instructor->name ?? 'Instruktor';
      }

      // Safely get car details
      $car = 'Mobil yang ditentukan';
      if ($schedule->car_id && $schedule->car) {
        $car = $schedule->car->name ?? 'Mobil yang ditentukan';
      }

      // Get session info
      $session = $schedule->for_session ?? '';
      $sessionText = !empty($session) ? "Sesi: #{$session}\n" : "";

      // Get status info for additional context
      $status = $schedule->status ?? '';
      $statusInfo = "";

      if ($status === 'complete') {
        $statusInfo = "Status: ✅ Sesi telah selesai\n";
      } elseif ($status === 'ready') {
        $statusInfo = "Status: ✅ Siap untuk dijalankan\n";
      } elseif ($status === 'waiting_signature') {
        $statusInfo = "Status: ⏳ Menunggu tanda tangan\n";
      }

      return "Halo {$name}! 📅\n\n" .
        "*Pemberitahuan Perubahan Jadwal Kursus Mengemudi*\n\n" .
        "Jadwal kursus mengemudi Anda telah diperbarui:\n" .
        "📆 Tanggal & Waktu: {$dateTime} WIB\n" .
        "👨‍🏫 Instruktur: {$instructor}\n" .
        "🚗 Kendaraan: {$car}\n" .
        "{$sessionText}" .
        "{$statusInfo}\n" .
        "Silakan periksa aplikasi Natuna Jaya jika Anda ingin melihat detail lengkap atau menghubungi kami.\n\n" .
        "Terima kasih,\n" .
        "Tim Natuna Jaya Driving School";
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'schedule_id' => $schedule->id ?? 'unknown'
      ]);

      // Fallback template
      return "Halo {$name}!\n\nJadwal kursus mengemudi Anda telah diperbarui. Silakan cek aplikasi untuk detailnya.\n\nTerima kasih,\nTim Natuna Jaya Driving School";
    }
  }
  /**
   * Get payment confirmation template message
   *
   * @param string $name
   * @param \App\Models\Order $order
   * @return string
   */    public function getPaymentConfirmationTemplate($name, Order $order)
  {
    try {
      $amount = number_format($order->amount, 0, ',', '.');
      $invoiceId = $order->invoice_id ?? $order->id;
      $date = now()->locale('id')->isoFormat('D MMMM YYYY');
      $time = now()->format('H:i');

      // Get course information
      $courseName = "Kursus Mengemudi";
      $courseType = "";
      $sessionCount = 0;

      if ($order->course) {
        $courseName = $order->course->name ?? "Kursus Mengemudi";
        $courseType = $order->course->type ?? "";
        $sessionCount = $order->course->session ?? 0;
      }

      // Get payment method if available
      $paymentMethod = $order->payment_method ?? "Online";

      // Get branch info if available
      $branchInfo = "Cabang Utama";
      $branchContact = env('BRANCH_CONTACT', '');

      // Create branding header
      $logoText = "🚗 *NATUNA JAYA DRIVING SCHOOL* 🚙\n";
      $separator = "━━━━━━━━━━━━━━━━━━━━━━━━\n";

      // Build the message with enhanced branding
      $message = $logoText . $separator .
        "Halo {$name}! 💳\n\n" .
        "*✅ KONFIRMASI PEMBAYARAN BERHASIL ✅*\n\n" .
        "Terima kasih! Pembayaran Anda sebesar *Rp {$amount}* untuk Invoice #{$invoiceId} telah kami terima.\n\n" .
        $separator .
        "*DETAIL PEMBAYARAN*\n" .
        "🧾 Invoice ID: #{$invoiceId}\n" .
        "💰 Jumlah: Rp {$amount}\n" .
        "📅 Tanggal: {$date}\n" .
        "⏰ Waktu: {$time} WIB\n" .
        "💳 Metode: {$paymentMethod}\n" .
        $separator;

      // Add course details if available
      if (!empty($courseName)) {
        $message .= "*DETAIL KURSUS*\n";
        $message .= "📚 Paket: {$courseName}\n";

        if (!empty($courseType)) {
          $message .= "🚗 Tipe: {$courseType}\n";
        }

        if ($sessionCount > 0) {
          $message .= "🔄 Jumlah Sesi: {$sessionCount} sesi\n";
        }

        $message .= $separator;
      }

      $message .= "*LANGKAH SELANJUTNYA*\n" .
        "1️⃣ Login ke aplikasi Natuna Jaya\n" .
        "2️⃣ Atur jadwal kursus Anda\n" .
        "3️⃣ Konfirmasikan jadwal dengan instruktor\n" .
        $separator .
        "*BUTUH BANTUAN?*\n" .
        "Hubungi kami melalui:\n" .
        "📱 WhatsApp: " . (!empty($branchContact) ? $branchContact : "No. ini") . "\n" .
        "📧 Email: info@natunajaya.com\n" .
        "🌐 Website: www.natunajaya.com\n" .
        $separator .
        "Terima kasih telah memilih *Natuna Jaya Driving School* sebagai partner belajar mengemudi Anda!\n\n" .
        "Salam,\n" .
        "Tim Natuna Jaya\n" .
        "_{$branchInfo}_";

      return $message;
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'order_id' => $order->id ?? 'unknown'
      ]);

      // Fallback template
      return "Halo {$name}!\n\nPembayaran Anda telah berhasil. Terima kasih.\n\nTim Natuna Jaya Driving School";
    }
  }

  /**
   * Get reminder template message
   *
   * @param string $name
   * @param \App\Models\Schedule $schedule
   * @return string
   */    public function getReminderTemplate($name, Schedule $schedule)
  {
    try {
      // Safely get date and time
      $dateTime = "Jadwal belum ditentukan";
      if ($schedule->start_date) {
        $dateTime = $schedule->start_date->format('d M Y - H:i');
      }

      // Safely get instructor name
      $instructor = 'Instruktor';
      if ($schedule->instructor_id && $schedule->instructor) {
        $instructor = $schedule->instructor->name ?? 'Instruktor';
      }

      // Get car details if available
      $car = 'Mobil yang ditentukan';
      if ($schedule->car_id && $schedule->car) {
        $car = $schedule->car->name ?? 'Mobil yang ditentukan';
      }

      $location = 'Natuna Jaya Driving School';

      return "Halo {$name}! ⏰\n\n" .
        "*Pengingat Jadwal Kursus Mengemudi*\n\n" .
        "Mengingatkan bahwa Anda memiliki jadwal kursus mengemudi besok:\n" .
        "📆 Tanggal & Waktu: {$dateTime} WIB\n" .
        "👨‍🏫 Instruktur: {$instructor}\n" .
        "🚗 Kendaraan: {$car}\n" .
        "📍 Lokasi: {$location}\n\n" .
        "Pastikan Anda hadir tepat waktu dan membawa perlengkapan yang diperlukan.\n\n" .
        "Jika Anda perlu menjadwalkan ulang, harap hubungi kami segera.\n\n" .
        "Terima kasih,\n" .
        "Tim Natuna Jaya Driving School";
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage());

      // Fallback template
      return "Halo {$name}!\n\nIni adalah pengingat untuk jadwal kursus mengemudi Anda besok. Silakan cek aplikasi untuk detailnya.\n\nTerima kasih,\nTim Natuna Jaya Driving School";
    }
  }

  /**
   * Get pending payment template message
   *
   * @param string $name
   * @param \App\Models\Order $order
   * @return string
   */
  public function getPendingPaymentTemplate($name, Order $order)
  {
    try {
      $amount = number_format($order->amount, 0, ',', '.');
      $invoiceId = $order->invoice_id ?? $order->id;
      $date = now()->format('d M Y');
      $paymentMethod = $order->payment_method ?? "Online";

      $message = "Halo {$name}! ⏳\n\n" .
        "*Pembayaran Dalam Proses*\n\n" .
        "Pembayaran Anda sebesar Rp {$amount} untuk Invoice #{$invoiceId} sedang dalam proses.\n\n" .
        "Detail Pembayaran:\n" .
        "🧾 Invoice ID: #{$invoiceId}\n" .
        "💰 Jumlah: Rp {$amount}\n" .
        "📅 Tanggal: {$date}\n" .
        "💳 Metode Pembayaran: {$paymentMethod}\n\n" .
        "Kami akan memberitahu Anda segera setelah pembayaran berhasil dikonfirmasi.\n\n" .
        "Jika Anda memiliki pertanyaan, silakan hubungi kami melalui aplikasi atau WhatsApp ini.\n\n" .
        "Terima kasih,\n" .
        "Tim Natuna Jaya Driving School";

      return $message;
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'order_id' => $order->id ?? 'unknown'
      ]);

      // Fallback template
      return "Halo {$name}!\n\nPembayaran Anda sedang dalam proses. Kami akan memberitahu Anda segera setelah dikonfirmasi.\n\nTim Natuna Jaya Driving School";
    }
  }

  /**
   * Get failed payment template message
   *
   * @param string $name
   * @param \App\Models\Order $order
   * @return string
   */
  public function getFailedPaymentTemplate($name, Order $order)
  {
    try {
      $amount = number_format($order->amount, 0, ',', '.');
      $invoiceId = $order->invoice_id ?? $order->id;
      $date = now()->format('d M Y');
      $paymentMethod = $order->payment_method ?? "Online";

      $message = "Halo {$name}! ❌\n\n" .
        "*Notifikasi Pembayaran Gagal*\n\n" .
        "Mohon maaf, pembayaran Anda sebesar Rp {$amount} untuk Invoice #{$invoiceId} tidak berhasil diproses.\n\n" .
        "Detail Pembayaran:\n" .
        "🧾 Invoice ID: #{$invoiceId}\n" .
        "💰 Jumlah: Rp {$amount}\n" .
        "📅 Tanggal: {$date}\n" .
        "💳 Metode Pembayaran: {$paymentMethod}\n\n" .
        "Anda dapat mencoba melakukan pembayaran kembali melalui aplikasi Natuna Jaya.\n\n" .
        "Jika Anda membutuhkan bantuan, silakan hubungi tim layanan pelanggan kami melalui aplikasi atau WhatsApp ini.\n\n" .
        "Terima kasih,\n" .
        "Tim Natuna Jaya Driving School";

      return $message;
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'order_id' => $order->id ?? 'unknown'
      ]);

      // Fallback template
      return "Halo {$name}!\n\nMohon maaf, pembayaran Anda tidak berhasil diproses. Silakan coba lagi.\n\nTim Natuna Jaya Driving School";
    }
  }

  /**
   * Get expired payment template message
   *
   * @param string $name
   * @param \App\Models\Order $order
   * @return string
   */
  public function getExpiredPaymentTemplate($name, Order $order)
  {
    try {
      $amount = number_format($order->amount, 0, ',', '.');
      $invoiceId = $order->invoice_id ?? $order->id;
      $date = now()->format('d M Y');
      $paymentMethod = $order->payment_method ?? "Online";

      $message = "Halo {$name}! ⏰\n\n" .
        "*Notifikasi Pembayaran Kedaluwarsa*\n\n" .
        "Mohon maaf, batas waktu pembayaran Anda sebesar Rp {$amount} untuk Invoice #{$invoiceId} telah berakhir.\n\n" .
        "Detail Pembayaran:\n" .
        "🧾 Invoice ID: #{$invoiceId}\n" .
        "💰 Jumlah: Rp {$amount}\n" .
        "📅 Tanggal: {$date}\n" .
        "💳 Metode Pembayaran: {$paymentMethod}\n\n" .
        "Silakan buat pembayaran baru melalui aplikasi Natuna Jaya jika Anda masih berminat untuk mendaftar kursus.\n\n" .
        "Jika Anda memiliki pertanyaan, silakan hubungi kami melalui aplikasi atau WhatsApp ini.\n\n" .
        "Terima kasih,\n" .
        "Tim Natuna Jaya Driving School";

      return $message;
    } catch (\Exception $e) {
      Log::error('WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'order_id' => $order->id ?? 'unknown'
      ]);

      // Fallback template
      return "Halo {$name}!\n\nMohon maaf, batas waktu pembayaran Anda telah berakhir. Silakan buat pembayaran baru jika Anda masih berminat.\n\nTim Natuna Jaya Driving School";
    }
  }

  /**
   * Send payment status notification based on status
   * 
   * @param \App\Models\Student $student
   * @param \App\Models\Order $order
   * @param string $status
   * @return array
   */
  public function sendPaymentStatusNotification(Student $student, Order $order, string $status)
  {
    try {
      $template = null;

      switch ($status) {
        case 'success':
          $template = $this->getPaymentConfirmationTemplate($student->name, $order);
          break;
        case 'pending':
          $template = $this->getPendingPaymentTemplate($student->name, $order);
          break;
        case 'failed':
          $template = $this->getFailedPaymentTemplate($student->name, $order);
          break;
        case 'expired':
          $template = $this->getExpiredPaymentTemplate($student->name, $order);
          break;
        default:
          Log::warning("Unknown payment status for notification: {$status}");
          return ['status' => 'error', 'message' => 'Unknown payment status'];
      }

      return $this->sendMessage($student->user->phone, $template);
    } catch (\Exception $e) {
      Log::error('WhatsApp Payment Status Notification Error: ' . $e->getMessage(), [
        'status' => $status,
        'order_id' => $order->id,
        'student_id' => $student->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Send bulk WhatsApp messages to multiple recipients
   * 
   * @param array $recipients Array of recipients with format [['number' => '1234567890', 'name' => 'John Doe']]
   * @param string $templateMethod The name of the template method to use
   * @param array $templateParams Additional parameters for the template
   * @return array Results for each recipient
   */
  public function sendBulkMessages(array $recipients, string $templateMethod, array $templateParams = [])
  {
    $results = [];
    $successCount = 0;
    $failureCount = 0;

    foreach ($recipients as $recipient) {
      try {
        if (empty($recipient['number'])) {
          $results[$recipient['name'] ?? 'Unknown'] = [
            'status' => 'error',
            'message' => 'No phone number provided'
          ];
          $failureCount++;
          continue;
        }

        // Generate the message content using the specified template method
        if (method_exists($this, $templateMethod)) {
          $params = array_merge([$recipient['name'] ?? 'Student'], $templateParams);
          $content = call_user_func_array([$this, $templateMethod], $params);
        } else {
          throw new \InvalidArgumentException("Template method {$templateMethod} does not exist");
        }

        // Send the message
        $result = $this->sendMessage($recipient['number'], $content);
        $results[$recipient['name'] ?? $recipient['number']] = $result;

        // Track success/failure
        if ($result['status'] === 'success' || ($result['status_code'] ?? 0) == 200) {
          $successCount++;
        } else {
          $failureCount++;
        }

        // Add small delay between messages to avoid rate limiting
        usleep(200000); // 200ms delay
      } catch (\Exception $e) {
        Log::error('Error sending bulk WhatsApp message: ' . $e->getMessage(), [
          'recipient' => $recipient,
          'template' => $templateMethod
        ]);

        $results[$recipient['name'] ?? ($recipient['number'] ?? 'Unknown')] = [
          'status' => 'error',
          'message' => $e->getMessage()
        ];
        $failureCount++;
      }
    }

    return [
      'details' => $results,
      'summary' => [
        'total' => count($recipients),
        'success' => $successCount,
        'failure' => $failureCount
      ]
    ];
  }

  /**
   * Generate a generic notification template
   * 
   * @param string $name Recipient's name
   * @param string $title Message title/subject
   * @param string $message Main message content
   * @param array $fields Associative array of fields to include in format ['label' => 'value']
   * @param string $footer Optional footer text
   * @return string Formatted WhatsApp message
   */
  public function getGenericTemplate($name, $title, $message, array $fields = [], $footer = null)
  {
    $template = "Halo {$name}! 📌\n\n";
    $template .= "*{$title}*\n\n";
    $template .= "{$message}\n\n";

    // Add fields if provided
    if (!empty($fields)) {
      foreach ($fields as $label => $value) {
        $template .= "{$label}: {$value}\n";
      }
      $template .= "\n";
    }

    // Add footer if provided, otherwise use default
    if ($footer) {
      $template .= "{$footer}";
    } else {
      $template .= "Terima kasih,\n";
      $template .= "Tim Natuna Jaya Driving School";
    }

    return $template;
  }

  /**
   * Send a custom notification using the generic template
   * 
   * @param Student $student
   * @param string $title
   * @param string $message
   * @param array $fields
   * @param string|null $footer
   * @return array
   */
  public function sendCustomNotification(Student $student, $title, $message, array $fields = [], $footer = null)
  {
    try {
      $template = $this->getGenericTemplate($student->name, $title, $message, $fields, $footer);
      return $this->sendMessage($student->user->phone, $template);
    } catch (\Exception $e) {
      Log::error('WhatsApp Custom Notification Error: ' . $e->getMessage());
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Record failed message for later retry
   * 
   * @param string $number Recipient phone number
   * @param string $name Recipient name
   * @param string $content Message content
   * @param string $error Error message
   * @return \App\Models\WhatsappFailedMessage
   */
  protected function recordFailedMessage($number, $name, $content, $error)
  {
    try {
      return \App\Models\WhatsappFailedMessage::create([
        'recipient_number' => $number,
        'recipient_name' => $name,
        'content' => $content,
        'error_message' => $error,
        'retry_count' => 0,
        'status' => 'failed'
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to record failed WhatsApp message: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Retry sending failed messages
   * 
   * @param int $maxMessages Maximum number of messages to retry
   * @param int $maxRetries Maximum retry attempts per message
   * @return array Results summary
   */
  public function retryFailedMessages($maxMessages = 10, $maxRetries = 3)
  {
    $results = [
      'total_processed' => 0,
      'success' => 0,
      'failed' => 0,
      'details' => []
    ];

    $failedMessages = \App\Models\WhatsappFailedMessage::retryable($maxRetries)
      ->orderBy('created_at')
      ->limit($maxMessages)
      ->get();

    $results['total_processed'] = $failedMessages->count();

    foreach ($failedMessages as $message) {
      try {
        // Increment retry count and update retry time
        $message->retry_count += 1;
        $message->last_retry_at = now();
        $message->save();

        // Attempt to send the message
        $result = $this->sendMessage($message->recipient_number, $message->content);

        // Update message status based on result
        if ($result['status'] === 'success' || isset($result['status_code']) && $result['status_code'] == 200) {
          $message->status = 'success';
          $message->save();
          $results['success']++;
        } else {
          $message->error_message = $result['message'] ?? json_encode($result);
          $message->save();
          $results['failed']++;
        }

        $results['details'][$message->id] = $result;

        // Add small delay between retries
        usleep(200000); // 200ms delay
      } catch (\Exception $e) {
        Log::error('Error retrying WhatsApp message: ' . $e->getMessage(), [
          'message_id' => $message->id
        ]);

        $message->error_message = $e->getMessage();
        $message->save();

        $results['failed']++;
        $results['details'][$message->id] = [
          'status' => 'error',
          'message' => $e->getMessage()
        ];
      }
    }

    return $results;
  }

  /**
   * Check WhatsApp API connection status
   * 
   * @return array Status information
   */  public function checkApiStatus()
  {
    try {
      // Use the health check endpoint instead of api-keys/status
      $url = $this->getHealthCheckUrl();

      $apiKey = env('WHATSAPP_KEY', '');

      Log::info('WhatsApp: Checking API status', ['url' => $url]);

      $response = Http::withHeaders([
        'x-api-key' => $apiKey
      ])->timeout(10)->get($url);

      $data = $response->json() ?: [];

      return [
        'status' => $response->successful() ? 'ok' : 'error',
        'http_code' => $response->status(),
        'message' => $response->successful() ? 'API connection successful' : 'API connection failed',
        'data' => $data
      ];
    } catch (\Exception $e) {
      Log::error('WhatsApp API Status Check Error: ' . $e->getMessage());

      return [
        'status' => 'error',
        'http_code' => 500,
        'message' => $e->getMessage(),
        'data' => []
      ];
    }
  }

  /**
   * Validate a phone number format
   * 
   * @param string $number Phone number to validate
   * @return string Formatted phone number or null if invalid
   */
  public function validatePhoneNumber($number)
  {
    if (empty($number)) {
      return null;
    }

    // Remove any non-numeric characters
    $number = preg_replace('/[^0-9]/', '', $number);

    // Check if it starts with '0', replace with country code '62' (Indonesia)
    if (substr($number, 0, 1) === '0') {
      $number = '62' . substr($number, 1);
    }

    // If no country code, add '62' (Indonesia)
    if (strlen($number) <= 10) {
      $number = '62' . $number;
    }

    // Ensure minimum length
    if (strlen($number) < 10) {
      return null;
    }

    return $number;
  }

  /**
   * Format array of phone numbers for sending
   * 
   * @param array|string $numbers Phone number(s) to format
   * @return array Formatted phone numbers
   */
  protected function formatPhoneNumbers($numbers)
  {
    if (!is_array($numbers)) {
      $numbers = [$numbers];
    }

    $validNumbers = [];
    foreach ($numbers as $number) {
      $formatted = $this->validatePhoneNumber($number);
      if ($formatted) {
        $validNumbers[] = $formatted;
      } else {
        Log::warning("Invalid phone number skipped: {$number}");
      }
    }

    return $validNumbers;
  }

  /**
   * Get admin payment notification template
   *
   * @param \App\Models\Order $order
   * @param string $status
   * @return string
   */
  public function getAdminPaymentNotificationTemplate(Order $order, string $status)
  {
    try {
      $amount = number_format($order->amount, 0, ',', '.');
      $invoiceId = $order->invoice_id ?? $order->id;
      $date = now()->format('d M Y H:i');
      $paymentMethod = $order->payment_method ?? "Online";

      $statusEmoji = "📊";
      $statusText = "diperbarui ke {$status}";

      if ($status == 'success') {
        $statusEmoji = "✅";
        $statusText = "berhasil";
      } elseif ($status == 'pending') {
        $statusEmoji = "⏳";
        $statusText = "dalam proses";
      } elseif ($status == 'failed') {
        $statusEmoji = "❌";
        $statusText = "gagal";
      } elseif ($status == 'expired') {
        $statusEmoji = "⏰";
        $statusText = "kedaluwarsa";
      }

      // Get student name if available
      $studentName = "N/A";
      $studentPhone = "N/A";

      if ($order->student && $order->student->user) {
        $studentName = $order->student->name ?? "N/A";
        $studentPhone = $order->student->user->phone ?? "N/A";
      }

      // Get course information
      $courseName = "N/A";

      if ($order->course) {
        $courseName = $order->course->name ?? "N/A";
      }

      $message = "*Notifikasi Pembayaran Admin* {$statusEmoji}\n\n" .
        "Pembayaran dengan status *{$statusText}*\n\n" .
        "Detail Pembayaran:\n" .
        "🧾 Invoice ID: #{$invoiceId}\n" .
        "💰 Jumlah: Rp {$amount}\n" .
        "📅 Waktu: {$date}\n" .
        "💳 Metode: {$paymentMethod}\n\n" .
        "Detail Siswa:\n" .
        "👤 Nama: {$studentName}\n" .
        "📱 Telepon: {$studentPhone}\n" .
        "📚 Kursus: {$courseName}\n\n" .
        "Login ke dashboard admin untuk lebih detail.";

      return $message;
    } catch (\Exception $e) {
      Log::error('Admin WhatsApp Template Error: ' . $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'order_id' => $order->id ?? 'unknown'
      ]);

      // Fallback template
      $orderId = $order->invoice_id;
      if (empty($orderId)) {
        $orderId = $order->id;
      }
      return "Notifikasi Pembayaran Admin: Pembayaran dengan ID #{$orderId} status {$status}";
    }
  }

  /**
   * Send payment notification to admin
   *
   * @param \App\Models\Order $order
   * @param string $status
   * @return array
   */
  public function sendAdminPaymentNotification(Order $order, string $status)
  {
    try {
      // Get admin phone numbers from environment or settings
      $adminPhones = explode(',', env('ADMIN_PHONE_NUMBERS', ''));

      // Filter out empty values
      $adminPhones = array_filter($adminPhones);

      if (empty($adminPhones)) {
        Log::warning('No admin phone numbers configured for payment notifications');
        return ['status' => 'error', 'message' => 'No admin phone numbers configured'];
      }

      $template = $this->getAdminPaymentNotificationTemplate($order, $status);

      return $this->sendMessage($adminPhones, $template);
    } catch (\Exception $e) {
      Log::error('Admin WhatsApp Notification Error: ' . $e->getMessage(), [
        'status' => $status,
        'order_id' => $order->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  /**
   * Format date in Indonesian format
   *
   * @param \Carbon\Carbon|null $date
   * @return string
   */
  protected function formatIndonesianDate($date = null)
  {
    $date = $date ?? now();
    return $date->locale('id')->isoFormat('D MMMM YYYY');
  }

  /**
   * Format time with date in Indonesian format
   *
   * @param \Carbon\Carbon|null $date
   * @return string
   */
  protected function formatIndonesianDateTime($date = null)
  {
    $date = $date ?? now();
    return $date->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB';
  }
}
