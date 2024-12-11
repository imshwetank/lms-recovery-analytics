<?php

class DatabaseController {
    private $connection = null;
    private $config = null;

    public function __construct($email = null) {
        if ($email) {
            $this->connectByEmail($email);
        }
    }

    public function connectByEmail($email) {
        // Get database config for this email
        $auth = new AuthController();
        $this->config = $auth->getDatabaseConfig($email);
        
        if (!$this->config) {
            throw new Exception("No database configuration found for email: $email");
        }

        try {
            $this->connection = new PDO(
                "mysql:host={$this->config['host']};dbname={$this->config['name']};charset=utf8mb4",
                $this->config['user'],
                $this->config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            error_log("Successfully connected to database {$this->config['name']} for email {$email}");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        }
    }

    public function getConnection() {
        if (!$this->connection) {
            throw new Exception("No active database connection");
        }
        return $this->connection;
    }

    public function getDatabaseName() {
        return $this->config ? $this->config['name'] : null;
    }

    // Add methods for your chart data queries here
    public function getChartData() {
        try {
            $stmt = $this->connection->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM loans
                GROUP BY month
                ORDER BY month
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching chart data: " . $e->getMessage());
            throw new Exception("Failed to fetch chart data");
        }
    }
}
