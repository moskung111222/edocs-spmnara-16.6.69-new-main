<?php
namespace App\Config;

class Mail {
    /**
     * Get PHPMailer SMTP credentials and settings.
     * @return array
     */
    public static function getConfig() {
        return [
            'host'       => Env::get('SMTP_HOST', 'sandbox.smtp.mailtrap.io'),
            'port'       => (int)Env::get('SMTP_PORT', 2525),
            'username'   => Env::get('SMTP_USER', ''),
            'password'   => Env::get('SMTP_PASS', ''),
            'encryption' => Env::get('SMTP_ENCRYPTION', 'tls'),
            'from_email' => Env::get('SMTP_FROM_EMAIL', 'no-reply@spmnara.go.th'),
            'from_name'  => Env::get('SMTP_FROM_NAME', 'สพม.นราธิวาส - ระบบยื่นคำขอเอกสารออนไลน์')
        ];
    }
}
