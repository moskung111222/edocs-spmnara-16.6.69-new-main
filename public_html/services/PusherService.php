<?php
namespace App\Services;

use App\Config\Config;
use Exception;

class PusherService {
    /**
     * Trigger a Pusher event
     * @param string $channel
     * @param string $event
     * @param array $data
     * @return bool
     */
    public static function trigger($channel, $event, $data) {
        $appId = Config::getPusherAppId();
        $key = Config::getPusherKey();
        $secret = Config::getPusherSecret();
        $cluster = Config::getPusherCluster();

        if (empty($appId) || empty($key) || empty($secret) || empty($cluster)) {
            error_log("Pusher settings not configured.");
            return false;
        }

        $path = "/apps/{$appId}/events";
        $body = json_encode([
            'name' => $event,
            'channels' => [$channel],
            'data' => json_encode($data)
        ]);

        $auth_timestamp = time();
        $auth_version = '1.0';
        $body_md5 = md5($body);

        $params = [
            'auth_key' => $key,
            'auth_timestamp' => $auth_timestamp,
            'auth_version' => $auth_version,
            'body_md5' => $body_md5
        ];
        ksort($params);
        $query_string = http_build_query($params);

        $string_to_sign = "POST\n{$path}\n{$query_string}";
        $auth_signature = hash_hmac('sha256', $string_to_sign, $secret);

        $url = "https://api-{$cluster}.pusher.com{$path}?{$query_string}&auth_signature={$auth_signature}";

        try {
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception("Failed to initialize cURL.");
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception("cURL error: " . $error);
            }
            curl_close($ch);
            return true;
        } catch (Exception $e) {
            error_log("Pusher trigger failed: " . $e->getMessage());
            return false;
        }
    }
}
