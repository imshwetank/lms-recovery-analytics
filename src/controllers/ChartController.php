<?php
require_once __DIR__ . '/../models/LoanPassbookModel.php';

class ChartController {
    private $model;

    public function __construct() {
        $this->model = new LoanPassbookModel();
    }

    public function index() {
        $branches = $this->model->getBranches();
        include __DIR__ . '/../views/charts.php';
    }

    public function getData() {
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'branch' => $_GET['branch'] ?? null,
            'type' => $_GET['type'] ?? null,
            'isOD' => $_GET['isOD'] ?? null,
            'period' => $_GET['period'] ?? 'daily' // daily, weekly, monthly, yearly
        ];

        $data = $this->model->getFilteredData($filters);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
