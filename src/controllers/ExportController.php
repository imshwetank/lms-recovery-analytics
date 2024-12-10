<?php
require_once __DIR__ . '/../models/LoanPassbookModel.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController {
    private $model;

    public function __construct() {
        $this->model = new LoanPassbookModel();
    }

    public function exportToExcel() {
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'branch' => $_GET['branch'] ?? null,
            'type' => $_GET['type'] ?? null,
            'isOD' => $_GET['isOD'] ?? null,
            'period' => 'daily'
        ];

        $data = $this->model->getFilteredData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'Period',
            'Normal Recovery',
            'Advance Recovery',
            'OS Recovery',
            'Arrear Recovery',
            'Close Loans',
            'Death Recovery',
            'Total Transactions'
        ];

        foreach (range('A', 'H') as $i => $col) {
            $sheet->setCellValue($col . '1', $headers[$i]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['period']);
            $sheet->setCellValue('B' . $row, $item['normal_recovery']);
            $sheet->setCellValue('C' . $row, $item['advance_recovery']);
            $sheet->setCellValue('D' . $row, $item['os_recovery']);
            $sheet->setCellValue('E' . $row, $item['arrear_recovery']);
            $sheet->setCellValue('F' . $row, $item['close_loans']);
            $sheet->setCellValue('G' . $row, $item['death_recovery']);
            $sheet->setCellValue('H' . $row, $item['total_transactions']);
            $row++;
        }

        // Style the header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');

        // Set the content type
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="recovery_report.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
