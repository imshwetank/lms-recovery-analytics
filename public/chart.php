<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/DatabaseController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is verified
if (!isset($_SESSION['verified_connection'])) {
    header('Location: verify.php');
    exit;
}

// Get the verified email and connect to appropriate database
$verifiedEmail = $_SESSION['verified_connection']['email'];
try {
    $db = new DatabaseController($verifiedEmail);
    $dbName = $db->getDatabaseName();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Handle AJAX requests for chart data
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    try {
        $data = $db->getChartData();
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Recovery Analytics - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">LMS Recovery Analytics</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Database: <?php echo htmlspecialchars($dbName); ?></span>
                <a href="verify.php?action=logout" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Loan Recovery Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="loanChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch chart data
            $.ajax({
                url: 'chart.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const ctx = document.getElementById('loanChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: response.data.map(item => item.month),
                                datasets: [{
                                    label: 'Number of Loans',
                                    data: response.data.map(item => item.count),
                                    borderColor: 'rgb(75, 192, 192)',
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching chart data:', error);
                }
            });
        });
    </script>
</body>
</html>
