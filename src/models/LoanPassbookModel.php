<?php

namespace Models;

use Core\Model;

class LoanPassbookModel extends Model {
    protected $table = 'loan_passbook';
    protected $primaryKey = 'id';

    public function getBranches() {
        // Get branches from both databases
        $db1Branches = $this->useConnection('db1')
            ->raw("SELECT DISTINCT branch_id FROM {$this->table}")
            ->fetchAll();

        $db2Branches = $this->useConnection('db2')
            ->raw("SELECT DISTINCT branch_id FROM {$this->table}")
            ->fetchAll();

        // Merge and unique branches
        $branches = array_merge($db1Branches, $db2Branches);
        $branches = array_unique(array_column($branches, 'branch_id'));
        sort($branches);

        return $branches;
    }

    public function getFilteredData($filters) {
        $params = [];
        $where = [];

        // Build WHERE clause
        if (!empty($filters['branch'])) {
            $where[] = "branch_id = ?";
            $params[] = $filters['branch'];
        }

        if (!empty($filters['type'])) {
            $where[] = "recovery_type = ?";
            $params[] = $filters['type'];
        }

        if (isset($filters['isOD'])) {
            $where[] = "is_od = ?";
            $params[] = $filters['isOD'];
        }

        // Add date range
        if (!empty($filters['start_date'])) {
            $where[] = "date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "date <= ?";
            $params[] = $filters['end_date'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Group by clause based on period
        $groupBy = $this->getGroupByClause($filters['period']);

        // Get data from both databases
        $sql = "
            SELECT 
                {$groupBy['select']},
                COUNT(*) as total_transactions,
                SUM(CASE WHEN recovery_type = 1 THEN amount ELSE 0 END) as normal_recovery,
                SUM(CASE WHEN recovery_type = 2 THEN amount ELSE 0 END) as advance_recovery,
                SUM(CASE WHEN recovery_type = 3 THEN amount ELSE 0 END) as os_recovery,
                SUM(CASE WHEN recovery_type = 4 THEN amount ELSE 0 END) as arrear_recovery,
                SUM(CASE WHEN recovery_type = 5 THEN amount ELSE 0 END) as close_loans,
                SUM(CASE WHEN recovery_type = 6 THEN amount ELSE 0 END) as death_recovery,
                SUM(CASE WHEN is_od = 1 THEN amount ELSE 0 END) as od_recovered,
                SUM(CASE WHEN is_od = 2 THEN amount ELSE 0 END) as od_unrecovered,
                SUM(principal) as total_principal,
                SUM(interest) as total_interest,
                SUM(amount) as total_amount
            FROM {$this->table}
            {$whereClause}
            GROUP BY {$groupBy['groupBy']}
            ORDER BY {$groupBy['orderBy']}
        ";

        // Get data from both databases
        $db1Data = $this->useConnection('db1')->raw($sql, $params)->fetchAll();
        $db2Data = $this->useConnection('db2')->raw($sql, $params)->fetchAll();

        // Merge and process data
        return $this->mergeAndProcessData($db1Data, $db2Data, $groupBy['period']);
    }

    private function getGroupByClause($period) {
        switch ($period) {
            case 'yearly':
                return [
                    'select' => "YEAR(date) as period",
                    'groupBy' => "YEAR(date)",
                    'orderBy' => "YEAR(date)",
                    'period' => 'yearly'
                ];
            case 'monthly':
                return [
                    'select' => "DATE_FORMAT(date, '%Y-%m') as period",
                    'groupBy' => "DATE_FORMAT(date, '%Y-%m')",
                    'orderBy' => "DATE_FORMAT(date, '%Y-%m')",
                    'period' => 'monthly'
                ];
            case 'weekly':
                return [
                    'select' => "CONCAT(YEAR(date), LPAD(WEEK(date), 2, '0')) as period",
                    'groupBy' => "YEAR(date), WEEK(date)",
                    'orderBy' => "YEAR(date), WEEK(date)",
                    'period' => 'weekly'
                ];
            default: // daily
                return [
                    'select' => "DATE(date) as period",
                    'groupBy' => "DATE(date)",
                    'orderBy' => "DATE(date)",
                    'period' => 'daily'
                ];
        }
    }

    private function mergeAndProcessData($db1Data, $db2Data, $period) {
        $mergedData = [];

        // Process DB1 data
        foreach ($db1Data as $row) {
            $period = $row['period'];
            if (!isset($mergedData[$period])) {
                $mergedData[$period] = $row;
            } else {
                $this->addValues($mergedData[$period], $row);
            }
        }

        // Process DB2 data
        foreach ($db2Data as $row) {
            $period = $row['period'];
            if (!isset($mergedData[$period])) {
                $mergedData[$period] = $row;
            } else {
                $this->addValues($mergedData[$period], $row);
            }
        }

        // Sort by period
        ksort($mergedData);

        return array_values($mergedData);
    }

    private function addValues(&$target, $source) {
        $fieldsToAdd = [
            'total_transactions',
            'normal_recovery',
            'advance_recovery',
            'os_recovery',
            'arrear_recovery',
            'close_loans',
            'death_recovery',
            'od_recovered',
            'od_unrecovered',
            'total_principal',
            'total_interest',
            'total_amount'
        ];

        foreach ($fieldsToAdd as $field) {
            $target[$field] += $source[$field];
        }
    }
}
