<?php
require_once __DIR__ . '/../models/LoanPassbookModel.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController {
    private $model;

    public function __construct() {
        $this->model = new LoanPassbookModel();
    }

    public function sendEmail() {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address']);
            exit;
        }

        $filters = [
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'branch' => $_POST['branch'] ?? null,
            'type' => $_POST['type'] ?? null,
            'isOD' => $_POST['isOD'] ?? null,
            'period' => 'daily'
        ];

        $data = $this->model->getFilteredData($filters);
        
        // Create HTML table for email
        $table = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
        $table .= '<tr style="background-color: #f2f2f2;">';
        $table .= '<th>Period</th>';
        $table .= '<th>Normal Recovery</th>';
        $table .= '<th>Advance Recovery</th>';
        $table .= '<th>OS Recovery</th>';
        $table .= '<th>Arrear Recovery</th>';
        $table .= '<th>Close Loans</th>';
        $table .= '<th>Death Recovery</th>';
        $table .= '<th>Total Transactions</th>';
        $table .= '</tr>';

        foreach ($data as $row) {
            $table .= '<tr>';
            $table .= "<td>{$row['period']}</td>";
            $table .= "<td>{$row['normal_recovery']}</td>";
            $table .= "<td>{$row['advance_recovery']}</td>";
            $table .= "<td>{$row['os_recovery']}</td>";
            $table .= "<td>{$row['arrear_recovery']}</td>";
            $table .= "<td>{$row['close_loans']}</td>";
            $table .= "<td>{$row['death_recovery']}</td>";
            $table .= "<td>{$row['total_transactions']}</td>";
            $table .= '</tr>';
        }
        $table .= '</table>';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($_POST['email']);

            $mail->isHTML(true);
            $mail->Subject = 'LMS Recovery Analytics Report';
            
            $body = '<h2>LMS Recovery Analytics Report</h2>';
            $body .= "<p>Period: {$filters['start_date']} to {$filters['end_date']}</p>";
            if ($filters['branch']) $body .= "<p>Branch: {$filters['branch']}</p>";
            $body .= $table;

            $mail->Body = $body;

            $mail->send();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send email: ' . $mail->ErrorInfo]);
        }
    }
}
