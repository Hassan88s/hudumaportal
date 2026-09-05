<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendJobAlertSMS implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;

    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle()
    {
        $receiverNumber = preg_replace('/[^0-9+]/', '', $this->phone);

        try {
            $from = env('NEXTSMS_SENDER', 'Hudumaportal'); 
            $reference = uniqid();
            $apiUrl = 'https://messaging-service.co.tz/api/sms/v1/text/single';
            $authKey = env('NEXTSMS_AUTH_KEY');

            $postData = [
                'from' => $from,
                'to' => $receiverNumber,
                'text' => $this->message,
                'reference' => $reference,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . $authKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error("NextSMS cURL error for {$receiverNumber}: {$error}");
            } else {
                Log::info("SMS sent to {$receiverNumber}. Response: {$response}");
            }
        } catch (\Exception $e) {
            Log::error("SMS Exception for {$receiverNumber}: " . $e->getMessage());
        }
    }
}

?>