<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Create a new notification and insert it into the queue
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function createNotification(Request $request)
    {
        try {
            $notification = Notification::insertNotification([
                'channel' => $request->channel ?? 'whatsapp',
                'type' => $request->type,
                'content' => $request->content ?? $this->generateContent($request->type, $request->metadata ?? []),
                'recipient' => $request->recipient,
                'send_date' => $request->send_date ?? now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification queued successfully',
                'notification_id' => $notification->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate notification content based on template and type
     * 
     * @param string $type
     * @param array $data
     * @return string
     */
    protected function generateContent($type, array $data)
    {
        switch ($type) {
            case 'schedule_update':
                return $this->scheduleUpdateTemplate($data);

            case 'payment_success':
                return $this->paymentSuccessTemplate($data);

            case 'registrasi':
                return $this->registrasiTemplate($data);

            case 'reminder':
                return $this->reminderTemplate($data);

            case 'course_complete':
                return $this->courseCompleteTemplate($data);

            default:
                return $data['content'] ?? 'No content available';
        }
    }

    /**
     * Template for payment success notifications
     * 
     * @param array $data
     * @return string
     */
    protected function paymentSuccessTemplate(array $data)
    {
        $studentName = $data['student_name'] ?? 'Siswa';
        $courseName = $data['course_name'] ?? 'Kursus Mengemudi';
        $amount = $data['amount'] ?? '';
        $paymentDate = $data['payment_date'] ?? date('d M Y');
        $invoiceNumber = $data['invoice_number'] ?? '';

        return "Halo {$studentName}, terima kasih telah mempercayakan pendidikan mengemudi di sekolah kami.\n\n" .
            "Berikut detail terkait pembayaran Anda:\n" .
            "Kursus: {$courseName}\n" .
            "Tanggal: {$paymentDate}\n" .
            "Biaya: Rp {$amount}\n" .
            "No. Invoice: {$invoiceNumber}\n\n" .
            "Pembayaran bisa dilakukan melalui rekening bank kami atau bayar tunai di kantor kami.";
    }

    /**
     * Template for registration notifications
     * 
     * @param array $data
     * @return string
     */
    protected function registrasiTemplate(array $data)
    {
        $studentName = $data['student_name'] ?? 'Siswa';
        $courseName = $data['course_name'] ?? 'Kursus Mengemudi';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $loginUrl = $data['login_url'] ?? 'https://natunadrivingschool.com/login';

        return "Halo {$studentName}, terima kasih telah mempercayakan pendidikan mengemudi di sekolah kami.\n\n" .
            "Berikut detail terkait pendaftaran Anda:\n" .
            "Username: {$username}\n" .
            "Setip notifikasi akan dikirim ke nomor WhatsApp Anda.\n" .
            "Silakan login ke {$loginUrl} untuk mengakses akun Anda dan melihat jadwal kursus.";
    }

    /**
     * Template for schedule update notifications
     * 
     * @param array $data
     * @return string
     */
    protected function scheduleUpdateTemplate(array $data)
    {
        $studentName = $data['student_name'] ?? 'Siswa';
        $courseName = $data['course_name'] ?? 'Kursus Mengemudi';
        $session = $data['session'] ?? '';
        $date = $data['date'] ?? 'Jadwal yang ditentukan';
        $time = $data['time'] ?? '';
        $instructor = $data['instructor'] ?? 'instruktur yang ditugaskan';

        return "Halo {$studentName}, jadwal anda telah di diperbarui.\n\n" .
            "Berikut detail jadwal anda:\n" .
            "Tanggal      : {$date} {$time} WIB\n" .
            "Kursus       : {$courseName}\n" .
            "Instruktur   : Rp {$instructor}\n" .
            "Mobil        : " . ($data['cars'] ?? 'Mobil yang ditentukan') . "\n" .
            "Mohon hadir tepat waktu. Jika ada kendala, silakan hubungi admin kami.";
    }

    /**
     * Template for reminder notifications
     * 
     * @param array $data
     * @return string
     */
    protected function reminderTemplate(array $data)
    {
        $studentName = $data['student_name'] ?? 'Siswa';
        $courseName = $data['course_name'] ?? 'Kursus Mengemudi';
        $session = $data['session'] ?? '';
        $date = $data['date'] ?? 'besok';
        $time = $data['time'] ?? '';
        $instructor = $data['instructor'] ?? 'instruktur yang ditugaskan';

        return "Halo {$studentName}, anda mempunyai jadwal hari ini.\n\n" .
            "Berikut detail jadwal anda:\n" .
            "Tanggal      : {$date} {$time} WIB\n" .
            "Kursus       : {$courseName}\n" .
            "Instruktur   : Rp {$instructor}\n" .
            "Mobil        : Rp {$session}\n" .
            "Mohon hadir tepat waktu. Jika ada kendala, silakan hubungi admin kami.";
    }

    /**
     * Template for course completion notifications
     * 
     * @param array $data
     * @return string
     */
    protected function courseCompleteTemplate(array $data)
    {
        $studentName = $data['student_name'] ?? 'Siswa';
        $courseName = $data['course_name'] ?? 'Kursus Mengemudi';
        $score = $data['score'] ?? '';
        $completionDate = $data['completion_date'] ?? date('d M Y');
        $certificateNumber = $data['certificate_number'] ?? '';
        $instructorName = $data['instructor_name'] ?? 'Instruktur';

        return "Halo {$studentName}, terima kasih telah mempercayakan pendidikan mengemudi di sekolah kami.\n\n" .
            "Berikut detail terkait kelulusan Anda:\n" .
            "Kursus: {$courseName}\n" .
            "Tanggal: {$completionDate}\n" .
            "Sertifikat Anda dapat diambil di Kantor Natuna Jaya Driving School.";
    }

    /**
     * Static method to create a schedule update notification
     *
     * @param string $recipient Phone number
     * @param array $data Notification data
     * @return Notification
     */
    public static function scheduleUpdate($recipient, array $data)
    {
        return Notification::insertNotification([
            'channel' => 'whatsapp',
            'type' => 'schedule_update',
            'content' => (new self)->scheduleUpdateTemplate($data),
            'recipient' => $recipient,
            'send_date' => now()
        ]);
    }

    /**
     * Static method to create a payment success notification
     *
     * @param string $recipient Phone number
     * @param array $data Notification data
     * @return Notification
     */
    public static function paymentSuccess($recipient, array $data)
    {
        return Notification::insertNotification([
            'channel' => 'whatsapp',
            'type' => 'payment_success',
            'content' => (new self)->paymentSuccessTemplate($data),
            'recipient' => $recipient,
            'send_date' => now()
        ]);
    }

    /**
     * Static method to create a registration notification
     *
     * @param string $recipient Phone number
     * @param array $data Notification data
     * @return Notification
     */
    public static function registrasi($recipient, array $data)
    {
        return Notification::insertNotification([
            'channel' => 'whatsapp',
            'type' => 'registrasi',
            'content' => (new self)->registrasiTemplate($data),
            'recipient' => $recipient,
            'send_date' => now()
        ]);
    }

    /**
     * Static method to create a reminder notification
     *
     * @param string $recipient Phone number
     * @param array $data Notification data
     * @return Notification
     */
    public static function reminder($recipient, array $data)
    {
        return Notification::insertNotification([
            'channel' => 'whatsapp',
            'type' => 'reminder',
            'content' => (new self)->reminderTemplate($data),
            'recipient' => $recipient,
            'send_date' => now()
        ]);
    }

    /**
     * Static method to create a course complete notification
     *
     * @param string $recipient Phone number
     * @param array $data Notification data
     * @return Notification
     */
    public static function courseComplete($recipient, array $data)
    {
        return Notification::insertNotification([
            'channel' => 'whatsapp',
            'type' => 'course_complete',
            'content' => (new self)->courseCompleteTemplate($data),
            'recipient' => $recipient,
            'send_date' => now()
        ]);
    }
}
