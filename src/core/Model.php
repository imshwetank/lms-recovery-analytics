<?php

namespace Core;

abstract class Model {
    protected $db;
    protected $table;
    protected $connection = null;
    protected $primaryKey = 'id';

    public function __construct($connection = null) {
        $this->connection = $connection;
        $this->db = Database::getInstance($connection);
    }

    public function useConnection($connection) {
        $this->connection = $connection;
        $this->db = Database::getInstance($connection);
        return $this;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, [$this->primaryKey => $id]);
    }

    public function delete($id) {
        return $this->db->delete($this->table, [$this->primaryKey => $id]);
    }

    public function where($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function raw($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}
