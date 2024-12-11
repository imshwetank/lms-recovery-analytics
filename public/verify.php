<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
if (!isset($_ENV['DB_1_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Initialize auth controller
$auth = new AuthController();

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Handle email verification
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'send_code') {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                echo json_encode(['success' => false, 'message' => 'Invalid email']);
                exit;
            }
            
            // Get connection for email
            $connections = $auth->getConnections();
            $connection = null;
            foreach ($connections as $conn) {
                if (strtolower($conn['email']) === strtolower($email)) {
                    $connection = $conn;
                    break;
                }
            }
            
            if (!$connection) {
                echo json_encode(['success' => false, 'message' => 'No database found for this email']);
                exit;
            }
            
            $result = $auth->sendVerificationCode($email, $connection['id']);
            echo json_encode($result);
            exit;
        }
        
        if ($_POST['action'] === 'verify_code') {
            $code = $_POST['code'];
            $email = $_SESSION['pending_email'] ?? '';
            $connectionId = $_SESSION['pending_connection'] ?? '';
            
            if (!$code || !$email || !$connectionId) {
                echo json_encode(['success' => false, 'message' => 'Invalid verification attempt']);
                exit;
            }
            
            $result = $auth->verifyCode($email, $connectionId, $code);
            echo json_encode($result);
            exit;
        }
    }
    
    // Handle email check
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
        $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
            exit;
        }
        
        $connections = $auth->getConnections();
        $connection = null;
        foreach ($connections as $conn) {
            if (strtolower($conn['email']) === strtolower($email)) {
                $connection = $conn;
                break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'connection' => $connection
        ]);
        exit;
    }
}

// Get available connections for email suggestions
$connections = $auth->getConnections();
$emailList = array_unique(array_filter(array_column($connections, 'email')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .loading {
            position: relative;
            pointer-events: none;
        }
        .loading:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8) url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSI0MHB4IiB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE4IiBzdHJva2U9IiMwZDZlZmQiIHN0cm9rZS13aWR0aD0iNCIgZmlsbD0ibm9uZSI+CiAgICAgICAgPGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJyb3RhdGUiIGZyb209IjAgMjAgMjAiIHRvPSIzNjAgMjAgMjAiIGR1cj0iMXMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIi8+CiAgICA8L2NpcmNsZT4KPC9zdmc+') center no-repeat;
        }
        .database-options {
            display: none;
            margin-top: 10px;
        }
        .database-option {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .database-option:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .database-option.selected {
            background-color: #e9ecef;
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13,110,253,0.25);
        }
        .email-suggestions {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0 0 8px 8px;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .email-suggestion {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .email-suggestion:hover {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Database Connection Verification</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION['pending_email'])): ?>
                            <form id="verificationForm" class="mb-3" method="post">
                                <div class="mb-3 position-relative">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control" required
                                           autocomplete="off" placeholder="Enter your email">
                                    <div class="email-suggestions" id="emailSuggestions"></div>
                                </div>
                                
                                <input type="hidden" name="action" value="send_code">
                                <button type="submit" class="btn btn-primary w-100" disabled id="sendCodeBtn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Send Verification Code
                                </button>
                            </form>
                        <?php else: ?>
                            <form id="codeVerificationForm">
                                <div class="mb-4">
                                    <label for="code" class="form-label">Enter Verification Code</label>
                                    <input type="text" name="code" id="code" class="form-control form-control-lg text-center" 
                                           required pattern="[0-9]{6}" maxlength="6" placeholder="000000"
                                           style="letter-spacing: 5px; font-size: 24px;">
                                    <small class="form-text text-muted">
                                        Check your email for the 6-digit verification code
                                    </small>
                                </div>
                                <input type="hidden" name="action" value="verify_code">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">Verify Code</button>
                                    <a href="verify.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const emailInput = document.getElementById('email');
        const verificationForm = document.getElementById('verificationForm');
        const sendCodeBtn = document.getElementById('sendCodeBtn');
        const emailSuggestions = document.getElementById('emailSuggestions');
        const emailList = <?php echo json_encode($emailList); ?>;
        
        // Show message using SweetAlert2
        function showMessage(type, message, callback) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : 'Error',
                text: message,
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                if (callback) callback();
            });
        }

        // Handle email input and suggestions
        emailInput.addEventListener('input', function() {
            const value = this.value.toLowerCase().trim();
            
            // Show matching emails
            const matches = emailList.filter(email => 
                email.toLowerCase().includes(value)
            );
            
            if (value && matches.length > 0) {
                emailSuggestions.innerHTML = matches.map(email => `
                    <div class="email-suggestion" data-email="${email}">${email}</div>
                `).join('');
                emailSuggestions.style.display = 'block';
                sendCodeBtn.disabled = false;
            } else {
                emailSuggestions.style.display = 'none';
                sendCodeBtn.disabled = true;
            }
        });

        // Handle email suggestion click
        emailSuggestions.addEventListener('click', function(e) {
            const suggestion = e.target.closest('.email-suggestion');
            if (suggestion) {
                const email = suggestion.dataset.email;
                emailInput.value = email;
                emailSuggestions.style.display = 'none';
                sendCodeBtn.disabled = false;
            }
        });

        // Handle verification form submission
        if (verificationForm) {
            verificationForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                if (!email) {
                    showMessage('error', 'Please enter your email');
                    return;
                }

                try {
                    sendCodeBtn.disabled = true;
                    const spinner = sendCodeBtn.querySelector('.spinner-border');
                    if (spinner) spinner.classList.remove('d-none');
                    
                    const formData = new FormData();
                    formData.append('action', 'send_code');
                    formData.append('email', email);
                    
                    const response = await fetch('verify.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    
                    if (data.success) {
                        showMessage('success', data.message || 'Verification code sent successfully', () => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to send verification code');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showMessage('error', error.message || 'Error sending verification code. Please try again.');
                } finally {
                    sendCodeBtn.disabled = false;
                    const spinner = sendCodeBtn.querySelector('.spinner-border');
                    if (spinner) spinner.classList.add('d-none');
                }
            });
        }
    </script>
</body>
</html>
