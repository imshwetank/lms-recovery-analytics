<?php

namespace Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static $instance = null;
    private $mailer;

    private function __construct() {
        $this->initializeMailer();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeMailer() {
        try {
            $this->mailer = new PHPMailer(true);
            
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USERNAME'];
            $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = 465;
            
            // Debug settings
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug: $str");
            };
            
            // Sender settings
            $this->mailer->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $this->mailer->isHTML(true);
            
            // Connection settings
            $this->mailer->Timeout = 30;
            $this->mailer->SMTPKeepAlive = true;
            
        } catch (Exception $e) {
            error_log("Mailer initialization failed: " . $e->getMessage());
        }
    }

    public function sendOTP($email, $otp) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Your OTP for Login';
            $this->mailer->Body = "Your OTP is: {$otp}. It will expire in 10 minutes.";
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send OTP: " . $e->getMessage());
            throw $e;
        }
    }
}
