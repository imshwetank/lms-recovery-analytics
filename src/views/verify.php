<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Initialize auth controller
$auth = new AuthController();

// Get available connections
$connections = $auth->getConnections();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_code'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $connectionId = filter_var($_POST['connection_id'], FILTER_VALIDATE_INT);
        
        if ($email && $connectionId) {
            if ($auth->sendVerificationCode($email, $connectionId)) {
                $message = 'Verification code sent to your email';
                $_SESSION['pending_email'] = $email;
                $_SESSION['pending_connection'] = $connectionId;
            } else {
                $message = 'Error sending verification code';
            }
        } else {
            $message = 'Invalid email or connection';
        }
    } elseif (isset($_POST['verify_code'])) {
        $code = $_POST['code'];
        $email = $_SESSION['pending_email'] ?? '';
        $connectionId = $_SESSION['pending_connection'] ?? '';
        
        if ($code && $email && $connectionId) {
            $result = $auth->verifyCode($email, $connectionId, $code);
            if ($result['success']) {
                header('Location: charts.php');
                exit;
            } else {
                $message = $result['message'];
            }
        } else {
            $message = 'Invalid verification attempt';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['pending_email'])): ?>
                            <!-- Step 1: Select Connection and Enter Email -->
                            <form method="post" class="mb-3">
                                <div class="mb-3">
                                    <label for="connection" class="form-label">Select Database Connection</label>
                                    <select name="connection_id" id="connection" class="form-select" required>
                                        <option value="">Select Connection</option>
                                        <?php foreach ($connections as $conn): ?>
                                            <option value="<?php echo htmlspecialchars($conn['id']); ?>">
                                                <?php echo htmlspecialchars($conn['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <button type="submit" name="send_code" class="btn btn-primary">Send Verification Code</button>
                            </form>
                        <?php else: ?>
                            <!-- Step 2: Enter Verification Code -->
                            <form method="post">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Enter Verification Code</label>
                                    <input type="text" name="code" id="code" class="form-control" required
                                           pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit code">
                                </div>
                                <button type="submit" name="verify_code" class="btn btn-primary">Verify Code</button>
                                <a href="verify.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
