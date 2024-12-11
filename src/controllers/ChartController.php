<?php

namespace Controllers;

use Core\Controller;
use Models\LoanPassbookModel;

class ChartController extends Controller {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new LoanPassbookModel();
    }

    public function index() {
        // Apply middleware
        $this->middleware->handle(['auth']);
        
        $branches = $this->model->getBranches();
        return $this->render('charts/index', [
            'branches' => $branches,
            'error' => $this->getError(),
            'success' => $this->getSuccess()
        ]);
    }

    public function getData() {
        // Apply middleware
        $this->middleware->handle(['auth', 'api']);
        
        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'branch' => 'numeric',
            'type' => 'numeric',
            'period' => 'required'
        ];

        if (!$this->validate($_GET, $rules)) {
            return $this->json([
                'error' => true,
                'message' => 'Invalid input parameters'
            ]);
        }
        
        $filters = [
            'start_date' => $_GET['start_date'],
            'end_date' => $_GET['end_date'],
            'branch' => $_GET['branch'] ?? null,
            'type' => $_GET['type'] ?? null,
            'isOD' => $_GET['isOD'] ?? null,
            'period' => $_GET['period'] ?? 'daily'
        ];

        try {
            $data = $this->model->getFilteredData($filters);
            return $this->json([
                'error' => false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            error_log("Chart data error: " . $e->getMessage());
            return $this->json([
                'error' => true,
                'message' => 'Failed to fetch chart data'
            ]);
        }
    }

    public function getBranchData($branchId) {
        // Apply middleware
        $this->middleware->handle(['auth', 'api']);
        
        try {
            $data = $this->model->getBranchData($branchId);
            return $this->json([
                'error' => false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            error_log("Branch data error: " . $e->getMessage());
            return $this->json([
                'error' => true,
                'message' => 'Failed to fetch branch data'
            ]);
        }
    }

    public function getTypeData($type) {
        // Apply middleware
        $this->middleware->handle(['auth', 'api']);
        
        try {
            $data = $this->model->getTypeData($type);
            return $this->json([
                'error' => false,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            error_log("Type data error: " . $e->getMessage());
            return $this->json([
                'error' => true,
                'message' => 'Failed to fetch type data'
            ]);
        }
    }
}
