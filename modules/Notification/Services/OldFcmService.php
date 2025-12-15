<?php

namespace Modules\Notification\Services;

class OldFcmService
{
    private $keyData;

    public function __construct()
    {
        $keyFilePath = storage_path('app/firebase.json');
        $this->keyData = json_decode(file_get_contents($keyFilePath), true);
    }

    public static function pushFcmNotes($fcmData, $tokens)
    {
        $send_process = 0;
        if (is_array($tokens) && !empty($tokens)) {
            foreach ($tokens as $type => $token) {
                $payload = [
                    'message' => [
                        'token' => $token
                    ]
                ];

                if ($type == 'android') {
                    $payload['message']['notification'] = [
                        'title' => $fcmData['title'] ?? "",
                        'body' => $fcmData['body'] ?? "",
                    ];
                    $payload['message']['data'] = self::stringifyData($fcmData);
                }


                if ($type == 'ios') {
                    $payload['message']['apns'] = [
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $fcmData['title'] ?? "",
                                    'body' => $fcmData['body'] ?? "",
                                ],
                                'sound' => 'default',
                                // 'badge' => 1,
                                'data' => self::stringifyData($fcmData)
                            ],
                        ],
                        'fcm_options' => [
                            'analytics_label' => 'ios_notification',
                        ],
                    ];

                    $payload['message']['data'] = self::stringifyData($fcmData);
                }

                $send_process += self::send($payload);
            }
            return $send_process;  // Return total number of successful sends
        }

        return "No Users";  // Return message if no users provided
    }

    private static function send($notification)
    {
        $project_id = (new self)->keyData['project_id'];
        $serverKey = self::getToken();  // Get the OAuth token
        $headers = [
            'Authorization: Bearer ' . $serverKey,
            'Content-Type: application/json',
        ];
        // Log::channel('single')->info(json_encode($notification));
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$project_id/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Temporarily bypass SSL verification
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
        // Execute post and handle errors
        $result = curl_exec($ch);
        if ($result === FALSE) {
            // Log::error('Curl failed: ' . curl_error($ch));  // Log specific cURL error
            return 0;  // Return failure status
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // dd($responseCode);
        if ($responseCode != 200) {
            // Log::error('FCM Response Error: ' . $result);  // Log specific FCM error response
            return 0;  // Return failure status
        }
        // Log::error('FCM Response Success: ' . $result);
        curl_close($ch);
        return 1;  // Return success status
    }

    private static function getToken()
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = time();

        $claims = [
            'iss' => (new self)->keyData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,  // Token valid for 1 hour
            'iat' => $now
        ];

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlClaims = self::base64UrlEncode(json_encode($claims));
        $signatureInput = $base64UrlHeader . '.' . $base64UrlClaims;
        openssl_sign($signatureInput, $signature, (new self)->keyData['private_key'], 'sha256WithRSAEncryption');
        $base64UrlSignature = self::base64UrlEncode($signature);
        $jwt = $signatureInput . '.' . $base64UrlSignature;
        // Make a request to get the access token
        $postFields = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        if ($response === FALSE) {
            // Log::error('Curl failed: ' . curl_error($ch));
            return false;
        }
        $responseData = json_decode($response, true);
        curl_close($ch);
        return $responseData['access_token'];
    }

    private static function stringifyData($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Convert arrays/objects to JSON strings
                $data[$key] = json_encode($value);
            } else {
                // Ensure all other values are strings
                $data[$key] = (string)$value;
            }
        }
        return $data;
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

