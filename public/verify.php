<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/EmailController.php';
require_once __DIR__ . '/../src/config/env.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle session destroy on cancel
if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    session_destroy();
    header('Location: verify.php');
    exit;
}

$authController = new AuthController();
$emailController = new EmailController();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'send_code':
                    if (!isset($_POST['email']) || empty($_POST['email'])) {
                        throw new Exception('Email is required');
                    }
                    
                    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email format');
                    }
                    
                    if (!$authController->isAuthorizedEmail($email)) {
                        throw new Exception('Email not authorized');
                    }
                    
                    // Generate and send verification code
                    $code = sprintf("%06d", mt_rand(0, 999999));
                    $hash = password_hash($code, PASSWORD_DEFAULT);
                    
                    // Store in session
                    $_SESSION['verification'] = [
                        'email' => $email,
                        'hash' => $hash,
                        'expires' => time() + (15 * 60),
                        'attempts' => 0
                    ];
                    
                    // Send email
                    $emailController->sendVerificationEmail($email, $code);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Verification code sent successfully'
                    ]);
                    break;
                    
                case 'verify_code':
                    if (!isset($_POST['code']) || empty($_POST['code'])) {
                        throw new Exception('Verification code is required');
                    }
                    
                    if (!isset($_SESSION['verification']) || !isset($_SESSION['verification']['email'])) {
                        throw new Exception('No verification in progress');
                    }
                    
                    $code = $_POST['code'];
                    $email = $_SESSION['verification']['email'];
                    
                    // Check if verification has expired
                    if (time() > $_SESSION['verification']['expires']) {
                        unset($_SESSION['verification']);
                        throw new Exception('Verification code has expired');
                    }
                    
                    // Check attempts
                    if ($_SESSION['verification']['attempts'] >= 3) {
                        unset($_SESSION['verification']);
                        throw new Exception('Too many failed attempts');
                    }
                    
                    // Verify code
                    if (!password_verify($code, $_SESSION['verification']['hash'])) {
                        $_SESSION['verification']['attempts']++;
                        $remainingAttempts = 3 - $_SESSION['verification']['attempts'];
                        throw new Exception("Invalid code. {$remainingAttempts} attempts remaining");
                    }
                    
                    // Code is valid
                    $_SESSION['verified_email'] = $email;
                    unset($_SESSION['verification']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Verification successful'
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
        } else {
            throw new Exception('Action is required');
        }
    } catch (Exception $e) {
        error_log("Error in verify.php: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get authorized emails for suggestions
$authorizedEmails = $authController->getAuthorizedEmails();
error_log("Loaded authorized emails: " . print_r($authorizedEmails, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .verification-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .email-suggestions {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .suggestion-item:hover {
            background-color: #f8f9fa;
        }
        .otp-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .btn-loading {
            position: relative;
        }
        .btn-loading .spinner-border {
            margin-right: 8px;
        }
        .btn-loading.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }
        .btn .spinner-border {
            width: 1.2rem;
            height: 1.2rem;
            border-width: 0.15em;
            vertical-align: -0.125em;
            display: none;
        }
        .btn.loading .spinner-border {
            display: inline-block;
        }
        .btn.loading .btn-text {
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <?php if (!isset($_SESSION['verification'])): ?>
            <!-- Email Form -->
            <div id="emailSection">
                <h2 class="text-center mb-4">Email Verification</h2>
                <form id="emailForm">
                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="position-relative">
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required 
                                   placeholder="Enter your email">
                            <div id="emailSuggestions" class="email-suggestions"></div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" id="sendCodeBtn" class="btn btn-primary btn-lg">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            <span class="btn-text">Send Verification Code</span>
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <!-- OTP Form -->
            <div id="otpSection">
                <h2 class="text-center mb-4">Enter Verification Code</h2>
                <div class="text-center mb-4">
                    <p>We've sent a verification code to:</p>
                    <h5 class="text-primary"><?php echo htmlspecialchars($_SESSION['verification']['email']); ?></h5>
                </div>
                <form id="otpForm">
                    <div class="form-group mb-4">
                        <input type="text" class="form-control form-control-lg otp-input" id="otp" name="otp" 
                               maxlength="6" pattern="[0-9]{6}" required placeholder="000000">
                        <div class="form-text text-center">Enter the 6-digit verification code</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" id="verifyBtn" class="btn btn-success btn-lg">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            <span class="btn-text">Verify Code</span>
                        </button>
                        <a href="verify.php?action=cancel" class="btn btn-outline-secondary">Cancel & Start Over</a>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <p class="mb-0">Didn't receive the code?</p>
                    <button id="resendBtn" class="btn btn-link">Resend Code</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            const authorizedEmails = <?php echo json_encode($authorizedEmails); ?>;
            console.log('Available emails:', authorizedEmails);
            
            // Email suggestions handling
            function showEmailSuggestions(input) {
                const value = input.toLowerCase();
                const suggestions = authorizedEmails.filter(email => 
                    email.toLowerCase().includes(value)
                );
                
                const $suggestions = $('#emailSuggestions');
                if (suggestions.length > 0 && value) {
                    $suggestions.html(suggestions.map(email => 
                        `<div class="suggestion-item">${email}</div>`
                    ).join('')).show();
                } else {
                    $suggestions.hide();
                }
            }

            $('#email').on('input', function() {
                showEmailSuggestions(this.value);
            });

            $(document).on('click', '.suggestion-item', function() {
                $('#email').val($(this).text());
                $('#emailSuggestions').hide();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.form-group').length) {
                    $('#emailSuggestions').hide();
                }
            });

            // Loading state handling
            function setLoading(button, isLoading) {
                const $btn = $(button);
                if (isLoading) {
                    $btn.addClass('loading disabled')
                       .prop('disabled', true);
                } else {
                    $btn.removeClass('loading disabled')
                       .prop('disabled', false);
                }
            }

            // Send verification code
            $('#emailForm').on('submit', function(e) {
                e.preventDefault();
                const email = $('#email').val().trim();
                
                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter your email address'
                    });
                    return;
                }

                setLoading('#sendCodeBtn', true);
                
                $.ajax({
                    url: 'verify.php',
                    method: 'POST',
                    data: {
                        action: 'send_code',
                        email: email
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Code Sent!',
                                text: `Verification code has been sent to ${email}`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to send verification code'
                        });
                    },
                    complete: function() {
                        setLoading('#sendCodeBtn', false);
                    }
                });
            });

            // Format OTP input
            $('#otp').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Verify OTP
            $('#otpForm').on('submit', function(e) {
                e.preventDefault();
                const code = $('#otp').val().trim();
                
                if (!code || code.length !== 6) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter a valid 6-digit code'
                    });
                    return;
                }

                setLoading('#verifyBtn', true);
                
                $.ajax({
                    url: 'verify.php',
                    method: 'POST',
                    data: {
                        action: 'verify_code',
                        code: code
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Verification successful',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'index.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Verification failed'
                        });
                    },
                    complete: function() {
                        setLoading('#verifyBtn', false);
                    }
                });
            });

            // Resend code
            $('#resendBtn').click(function() {
                const email = '<?php echo isset($_SESSION['verification']['email']) ? $_SESSION['verification']['email'] : ''; ?>';
                if (!email) return;

                setLoading('#resendBtn', true);
                
                $.ajax({
                    url: 'verify.php',
                    method: 'POST',
                    data: {
                        action: 'send_code',
                        email: email
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Code Resent!',
                                text: `New verification code has been sent to ${email}`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to resend code'
                        });
                    },
                    complete: function() {
                        setLoading('#resendBtn', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
