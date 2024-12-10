<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

// Route handling
$controller = $_GET['controller'] ?? 'chart';
$action = $_GET['action'] ?? 'index';

// Load appropriate controller
switch ($controller) {
    case 'chart':
        require_once __DIR__ . '/../src/controllers/ChartController.php';
        $chartController = new ChartController();
        if ($action === 'getData') {
            $chartController->getData();
        } else {
            $chartController->index();
        }
        break;
    case 'export':
        require_once __DIR__ . '/../src/controllers/ExportController.php';
        $exportController = new ExportController();
        $exportController->exportToExcel();
        break;
    case 'email':
        require_once __DIR__ . '/../src/controllers/EmailController.php';
        $emailController = new EmailController();
        $emailController->sendEmail();
        break;
    default:
        header('Location: ?controller=chart');
        break;
}
