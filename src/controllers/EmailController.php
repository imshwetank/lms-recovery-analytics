<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailController {
    private $mailer;

    public function __construct() {
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            $this->mailer = new PHPMailer(true);
            
            // Debug output
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: $str");
            };

            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'];        // smtp.hostinger.com
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USERNAME']; // report@mics.asia
            $this->mailer->Password = $_ENV['SMTP_PASSWORD']; // PA$$w0rd2326
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = 465;
            
            // Default settings
            $this->mailer->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $this->mailer->isHTML(true);
            
            error_log("Mailer initialized with host: {$_ENV['SMTP_HOST']}, username: {$_ENV['SMTP_USERNAME']}");
        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize mailer: " . $e->getMessage());
        }
    }

    public function sendVerificationEmail($to, $code) {
        try {
            error_log("Attempting to send verification email to: $to with code: $code");
            
            // Reset mailer state
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();
            
            // Set recipient
            $this->mailer->addAddress($to);
            $this->mailer->Subject = 'LMS Recovery Analytics - Verification Code';
            
            // Create HTML email content
            $emailContent = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 5px;'>
                        <h2 style='color: #0d6efd; margin-bottom: 20px;'>Your Verification Code</h2>
                        <p>Hello,</p>
                        <p>Your verification code for LMS Recovery Analytics is:</p>
                        <div style='background-color: #ffffff; padding: 15px; text-align: center; font-size: 32px; 
                                  font-weight: bold; letter-spacing: 8px; margin: 20px 0; border-radius: 5px; 
                                  border: 2px solid #e9ecef;'>
                            {$code}
                        </div>
                        <p>This code will expire in 15 minutes.</p>
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            If you didn't request this code, please ignore this email.
                        </p>
                    </div>
                </body>
                </html>";
            
            $this->mailer->Body = $emailContent;
            $this->mailer->AltBody = "Your verification code is: {$code}. This code will expire in 15 minutes.";
            
            // Send email
            $result = $this->mailer->send();
            error_log("Email sent successfully to: $to");
            return true;
            
        } catch (Exception $e) {
            error_log("Failed to send email: " . $e->getMessage());
            throw new Exception("Failed to send verification email: " . $e->getMessage());
        }
    }
}
