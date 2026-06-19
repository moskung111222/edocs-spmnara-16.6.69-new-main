<?php
namespace App\Services;

use App\Config\Mail as MailConfig;
use Exception;

class MailService {
    /**
     * Send an HTML email.
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML body
     * @return bool
     */
    public static function send($to, $subject, $body) {
        $config = MailConfig::getConfig();
        
        // Attempt to load PHPMailer from vendor if present
        $vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }

        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $config['host'];
                $mail->SMTPAuth   = !empty($config['username']);
                $mail->Username   = $config['username'];
                $mail->Password   = $config['password'];
                $mail->SMTPSecure = $config['encryption'] === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $config['port'];
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($config['from_email'], $config['from_name']);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                return $mail->send();
            } catch (Exception $e) {
                error_log("PHPMailer failed, falling back to mail(). Error: " . $e->getMessage());
                return self::fallbackMail($to, $subject, $body, $config);
            }
        } else {
            return self::fallbackMail($to, $subject, $body, $config);
        }
    }

    /**
     * Fallback using PHP's native mail function.
     */
    private static function fallbackMail($to, $subject, $body, $config) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?utf-8?B?" . base64_encode($config['from_name']) . "?= <" . $config['from_email'] . ">\r\n";
        
        // Convert to base64 encoding to prevent character corruption
        $encodedSubject = "=?utf-8?B?" . base64_encode($subject) . "?=";
        
        return mail($to, $encodedSubject, $body, $headers);
    }
}
