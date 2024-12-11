<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $mailer;
    private $authorizedEmails = [];

    public function __construct() {
        $this->initializeMailer();
        $this->loadAuthorizedEmails();
    }

    private function loadAuthorizedEmails() {
        $dbIndex = 1;
        while (isset($_ENV["DB_{$dbIndex}_EMAIL"])) {
            $email = $_ENV["DB_{$dbIndex}_EMAIL"];
            if ($email) {
                $this->authorizedEmails[] = $email;
            }
            $dbIndex++;
        }
        error_log("Loaded authorized emails: " . print_r($this->authorizedEmails, true));
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
            
            // Additional SSL settings
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize mailer: " . $e->getMessage());
        }
    }

    public function getAuthorizedEmails() {
        error_log("Returning authorized emails: " . print_r($this->authorizedEmails, true));
        return $this->authorizedEmails;
    }

    public function isAuthorizedEmail($email) {
        $isAuthorized = in_array($email, $this->authorizedEmails);
        error_log("Checking if {$email} is authorized: " . ($isAuthorized ? 'yes' : 'no'));
        return $isAuthorized;
    }

    public function sendVerificationCode($email, $connectionId = null) {
        try {
            error_log("Starting verification code send for email: $email");
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Check if email is authorized
            if (!$this->isAuthorizedEmail($email)) {
                throw new Exception("Email not authorized");
            }

            // Get database config for this email
            $dbConfig = $this->getDatabaseConfig($email);
            if (!$dbConfig) {
                throw new Exception("Invalid email configuration");
            }

            // Generate verification code
            $code = sprintf("%06d", mt_rand(0, 999999));
            $hash = password_hash($code, PASSWORD_DEFAULT);
            
            // Store verification data in session
            $_SESSION['verification'] = [
                'email' => $email,
                'hash' => $hash,
                'expires' => time() + (15 * 60), // 15 minutes
                'attempts' => 0,
                'connection_id' => $dbConfig['name']
            ];
            
            $_SESSION['pending_email'] = $email;
            $_SESSION['pending_connection'] = $dbConfig['name'];

            // Send verification email
            $emailController = new EmailController();
            $emailController->sendVerificationEmail($email, $code);
            
            error_log("Verification code sent successfully to: $email");
            return ['success' => true, 'message' => 'Verification code sent successfully'];
            
        } catch (Exception $e) {
            error_log("Error sending verification code: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function verifyCode($email, $code) {
        try {
            error_log("Starting verification for email: $email with code: $code");
            
            if (!isset($_SESSION['verification'])) {
                throw new Exception("No verification in progress");
            }

            $verification = $_SESSION['verification'];
            error_log("Verification data found: " . print_r($verification, true));
            
            // Check if verification has expired
            if (time() > $verification['expires']) {
                $this->clearVerificationSession();
                throw new Exception("Verification code has expired");
            }

            // Check if too many attempts
            if ($verification['attempts'] >= 3) {
                $this->clearVerificationSession();
                throw new Exception("Too many attempts");
            }

            // Verify email matches
            if ($verification['email'] !== $email) {
                throw new Exception("Email mismatch");
            }

            // Verify the code
            if (!password_verify($code, $verification['hash'])) {
                $_SESSION['verification']['attempts']++;
                $remainingAttempts = 3 - $_SESSION['verification']['attempts'];
                throw new Exception("Invalid code. You have {$remainingAttempts} attempts remaining.");
            }

            error_log("Code verified successfully");
            
            // Code is valid - store connection in session
            $_SESSION['verified_connection'] = [
                'email' => $verification['email'],
                'connection_id' => $verification['connection_id'],
                'verified_at' => time()
            ];

            // Clear verification data but keep verified_connection
            $this->clearVerificationSession();

            return [
                'success' => true, 
                'message' => 'Verification successful!'
            ];
            
        } catch (Exception $e) {
            error_log("Verification error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function clearVerificationSession() {
        unset($_SESSION['verification']);
        unset($_SESSION['pending_email']);
        unset($_SESSION['pending_connection']);
    }

    public function getCurrentConnection() {
        return $_SESSION['verified_connection'] ?? null;
    }

    public function getDatabaseConfig($email) {
        $dbIndex = 1;
        while (isset($_ENV["DB_{$dbIndex}_EMAIL"])) {
            if ($_ENV["DB_{$dbIndex}_EMAIL"] === $email) {
                return [
                    'host' => $_ENV["DB_{$dbIndex}_HOST"],
                    'name' => $_ENV["DB_{$dbIndex}_NAME"],
                    'user' => $_ENV["DB_{$dbIndex}_USER"],
                    'pass' => $_ENV["DB_{$dbIndex}_PASS"]
                ];
            }
            $dbIndex++;
        }
        return null;
    }
}
