<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $mailer;

    public function __construct() {
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            $this->mailer = new PHPMailer(true);
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USERNAME'];
            $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Changed to SMTPS for port 465
            $this->mailer->Port = $_ENV['SMTP_PORT'];
            $this->mailer->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $this->mailer->isHTML(true);
            
            // Add debug info
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug: $str");
            };
            
            // Set timeout
            $this->mailer->Timeout = 10;
            $this->mailer->SMTPKeepAlive = true;
        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize mailer: " . $e->getMessage());
        }
    }

    public function getConnections() {
        $connections = [];
        $dbCount = 1;
        
        while (isset($_ENV["DB_{$dbCount}_HOST"])) {
            if (!empty($_ENV["DB_{$dbCount}_HOST"])) {
                $connections[] = [
                    'id' => $dbCount,
                    'host' => $_ENV["DB_{$dbCount}_HOST"],
                    'database_name' => $_ENV["DB_{$dbCount}_NAME"],
                    'username' => $_ENV["DB_{$dbCount}_USER"],
                    'password' => $_ENV["DB_{$dbCount}_PASS"],
                    'email' => $_ENV["DB_{$dbCount}_EMAIL"] ?? null
                ];
            }
            $dbCount++;
        }
        
        return $connections;
    }

    public function sendVerificationCode($email, $connectionId) {
        try {
            // Validate inputs
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email address format'];
            }

            $connectionId = intval($connectionId);
            if ($connectionId <= 0) {
                return ['success' => false, 'message' => 'Invalid connection ID'];
            }

            // Verify email is authorized for this connection
            $connection = null;
            foreach ($this->getConnections() as $conn) {
                if ($conn['id'] === $connectionId && strtolower($conn['email']) === strtolower($email)) {
                    $connection = $conn;
                    break;
                }
            }

            if (!$connection) {
                return ['success' => false, 'message' => 'This email is not authorized for the selected database'];
            }

            // Generate and hash OTP
            $code = sprintf("%06d", random_int(0, 999999));
            $hash = password_hash($code, PASSWORD_DEFAULT);

            // Store verification data
            $_SESSION['verification'] = [
                'hash' => $hash,
                'email' => $email,
                'connection_id' => $connectionId,
                'expires' => time() + (15 * 60), // 15 minutes
                'attempts' => 0
            ];

            // Prepare email content
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Database Access Verification Code';
            
            // Create HTML email content
            $emailContent = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #0d6efd;'>Your Verification Code</h2>
                        <p>Hello,</p>
                        <p>Your verification code for LMS Recovery Analytics database access is:</p>
                        <div style='background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                            {$code}
                        </div>
                        <p>This code will expire in 15 minutes.</p>
                        <p>If you didn't request this code, please ignore this email.</p>
                    </div>
                </body>
                </html>";
            
            $this->mailer->Body = $emailContent;
            $this->mailer->AltBody = "Your verification code is: {$code}. This code will expire in 15 minutes.";

            // Send email
            if (!$this->mailer->send()) {
                error_log("Mailer Error: " . $this->mailer->ErrorInfo);
                return ['success' => false, 'message' => 'Failed to send verification code. Please try again.'];
            }

            $_SESSION['pending_email'] = $email;
            $_SESSION['pending_connection'] = $connectionId;

            return ['success' => true, 'message' => 'Verification code sent successfully'];
            
        } catch (Exception $e) {
            error_log("Send verification code error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error sending verification code: ' . $e->getMessage()];
        }
    }

    private function getEmailTemplate($code) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #0d6efd;'>Database Access Verification</h2>
            <p>Your verification code is:</p>
            <h1 style='font-size: 32px; letter-spacing: 5px; background: #f8f9fa; padding: 15px; text-align: center; border-radius: 5px;'>{$code}</h1>
            <p>This code will expire in 15 minutes.</p>
            <p style='color: #6c757d; font-size: 14px;'>If you didn't request this code, please ignore this email.</p>
        </div>";
    }

    public function verifyCode($email, $connectionId, $code) {
        try {
            // Basic validation
            if (!isset($_SESSION['verification'])) {
                return ['success' => false, 'message' => 'No verification in progress'];
            }

            $verification = $_SESSION['verification'];

            // Check if verification is expired
            if ($verification['expires'] < time()) {
                $this->clearVerificationSession();
                return ['success' => false, 'message' => 'Verification code has expired. Please request a new code.'];
            }

            // Check if too many attempts
            if (($verification['attempts'] ?? 0) >= 3) {
                $this->clearVerificationSession();
                return ['success' => false, 'message' => 'Too many invalid attempts. Please request a new code.'];
            }

            // Validate email and connection match
            if ($verification['email'] !== $email || $verification['connection_id'] !== intval($connectionId)) {
                return ['success' => false, 'message' => 'Invalid verification attempt'];
            }

            // Increment attempt counter
            $_SESSION['verification']['attempts'] = ($verification['attempts'] ?? 0) + 1;

            // Verify code
            if (!password_verify($code, $verification['hash'])) {
                return ['success' => false, 'message' => 'Invalid verification code'];
            }

            // Get connection details
            $connection = null;
            foreach ($this->getConnections() as $conn) {
                if ($conn['id'] === intval($connectionId)) {
                    $connection = $conn;
                    break;
                }
            }

            if (!$connection) {
                return ['success' => false, 'message' => 'Database configuration not found'];
            }

            // Store verified connection
            $_SESSION['verified_connection'] = [
                'email' => $email,
                'connection_id' => $connectionId,
                'timestamp' => time(),
                'database' => $connection['database_name'],
                'host' => $connection['host']
            ];

            $this->clearVerificationSession();
            return ['success' => true, 'message' => 'Verification successful'];

        } catch (Exception $e) {
            error_log("Code verification error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error during verification. Please try again.'];
        }
    }

    private function clearVerificationSession() {
        unset($_SESSION['verification']);
        unset($_SESSION['pending_email']);
        unset($_SESSION['pending_connection']);
    }

    public function getCurrentConnection() {
        return $_SESSION['verified_connection'] ?? null;
    }
}
