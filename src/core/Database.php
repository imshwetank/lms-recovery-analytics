<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instances = [];
    private $connection;
    private $config;

    private function __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    public static function getInstance($connection = null) {
        $config = require __DIR__ . '/../config/database.php';
        
        // Use default connection if none specified
        if ($connection === null) {
            $connection = $config['default'];
        }

        // Check if connection exists in config
        if (!isset($config['connections'][$connection])) {
            throw new \Exception("Database connection '{$connection}' not found in config");
        }

        // Create new instance if doesn't exist
        if (!isset(self::$instances[$connection])) {
            self::$instances[$connection] = new self($config['connections'][$connection]);
        }

        return self::$instances[$connection];
    }

    private function connect() {
        try {
            $dsn = sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $values)
        );

        return $this->query($sql, array_values($data));
    }

    public function update($table, $data, $where) {
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));

        $whereFields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($where));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $fields),
            implode(' AND ', $whereFields)
        );

        return $this->query($sql, array_merge(array_values($data), array_values($where)));
    }

    public function delete($table, $where) {
        $whereFields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($where));

        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereFields)
        );

        return $this->query($sql, array_values($where));
    }
}
