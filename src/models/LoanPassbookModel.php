<?php
class LoanPassbookModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getBranches() {
        $stmt = $this->pdo->query("SELECT DISTINCT branch_id FROM loan_passbook ORDER BY branch_id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getFilteredData($filters) {
        $where = ["1=1"];
        $params = [];

        if ($filters['start_date'] && $filters['end_date']) {
            $where[] = "recovery_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if ($filters['branch']) {
            $where[] = "branch_id = :branch";
            $params[':branch'] = $filters['branch'];
        }

        if ($filters['type'] !== null) {
            $where[] = "type = :type";
            $params[':type'] = $filters['type'];
        }

        if ($filters['isOD'] !== null) {
            $where[] = "IsOD = :isOD";
            $params[':isOD'] = $filters['isOD'];
        }

        $groupBy = "DATE(recovery_date)";
        switch ($filters['period']) {
            case 'weekly':
                $groupBy = "YEARWEEK(recovery_date)";
                break;
            case 'monthly':
                $groupBy = "DATE_FORMAT(recovery_date, '%Y-%m')";
                break;
            case 'yearly':
                $groupBy = "YEAR(recovery_date)";
                break;
        }

        $sql = "SELECT 
                    $groupBy as period,
                    branch_id,
                    SUM(CASE WHEN type = 1 THEN recovered_amt ELSE 0 END) as normal_recovery,
                    SUM(CASE WHEN type = 2 THEN recovered_amt ELSE 0 END) as advance_recovery,
                    SUM(CASE WHEN type = 3 THEN recovered_amt ELSE 0 END) as os_recovery,
                    SUM(CASE WHEN type = 4 THEN recovered_amt ELSE 0 END) as arrear_recovery,
                    SUM(CASE WHEN type = 5 THEN recovered_amt ELSE 0 END) as close_loans,
                    SUM(CASE WHEN type = 6 THEN recovered_amt ELSE 0 END) as death_recovery,
                    COUNT(*) as total_transactions,
                    SUM(recovered_amt) as total_amount,
                    SUM(principal) as total_principal,
                    SUM(interest) as total_interest,
                    SUM(CASE WHEN IsOD = 1 THEN recovered_amt ELSE 0 END) as od_recovery,
                    SUM(CASE WHEN IsOD = 0 THEN recovered_amt ELSE 0 END) as regular_recovery
                FROM loan_passbook
                WHERE " . implode(" AND ", $where) . "
                GROUP BY $groupBy, branch_id
                ORDER BY period, branch_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
