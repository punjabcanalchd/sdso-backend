<?php 
namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send notification to a user.
     */
    public function sendNotification(int $userId, string $message): array
    {
        $returned = [];
        if (!empty($userId)) {

            $allTokens = NotificationToken::where('user_id', $userId)
                ->whereNotNull('token')
                ->get()
                ->toArray();

            if (!empty($allTokens)) {
                foreach ($allTokens as $tokenData) {
                    if (!empty($tokenData['token'])) {
                        $this->sendGCM(
                            $message,
                            $tokenData['token'],
                            $tokenData['user_id']
                        );
                        $returned[$tokenData['user_id']] = $tokenData['user_id'];
                    }
                }
            }
        }
        return $returned;    
    }

    /**
     * Send push notification through FCM.
     */
    public function sendGCM($message, $token, $userId)
    {
        $stripTags = strip_tags($message);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'notification' => [
                    'body' => $stripTags,
                    'title' => 'Notification',
                ],
                'data' => [
                    'message' => $stripTags,
                ],
                'to' => $token,
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: key=' . env('FCM_SERVER_KEY'),
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        $log = new NotificationLog();
        $log->user_id = $userId;
        $log->notification_text = $message . ' - ' . ($error ?: $response);
        $log->save();

        return [
            'response' => $response,
            'error' => $error,
        ];
    }
}    

?>