<?php
require_once __DIR__ . '/../app/config/database.php';

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // SELECT semua data
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        return $this->db->query($sql);
    }

    // SELECT dengan WHERE
    public function where($column, $value, $operator = '=') {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} ?");
        $stmt->bind_param($this->getType($value), $value);
        $stmt->execute();
        return $stmt->get_result();
    }

    // SELECT dengan multiple WHERE
    public function whereMultiple($conditions) {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $whereClauses = [];
        $params = [];
        $types = '';

        foreach ($conditions as $condition) {
            $whereClauses[] = "{$condition[0]} {$condition[1]} ?";
            $params[] = $condition[2];
            $types .= $this->getType($condition[2]);
        }

        $sql .= implode(' AND ', $whereClauses);
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    // SELECT satu data by ID
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // INSERT data
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        $types = '';
        $values = [];
        foreach ($data as $value) {
            $types .= $this->getType($value);
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        return $this->db->insert_id;
    }

    // UPDATE data
    public function update($id, $data) {
        $sets = [];
        $params = [];
        $types = '';

        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
            $types .= $this->getType($value);
        }

        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // DELETE data
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Query kustom
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                $types .= $this->getType($param);
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Eksekusi query INSERT/UPDATE/DELETE
    public function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                $types .= $this->getType($param);
            }
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute();
    }

    // Dapatkan jumlah baris
    public function count($column = null, $value = null) {
    if ($column && $value !== null) {
        // Kalau value array → pakai IN
        if (is_array($value)) {
            if (empty($value)) return 0;
            $placeholders = implode(',', array_fill(0, count($value), '?'));
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} IN ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $types = str_repeat('i', count($value));
            $stmt->bind_param($types, ...$value);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = ?");
            $stmt->bind_param($this->getType($value), $value);
        }
    } else {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

    // Mulai transaksi
    public function beginTransaction() {
        $this->db->begin_transaction();
    }

    // Commit transaksi
    public function commit() {
        $this->db->commit();
    }

    // Rollback transaksi
    public function rollback() {
        $this->db->rollback();
    }

    // Escape string
    public function escape($string) {
        return $this->db->real_escape_string($string);
    }

    // Dapatkan koneksi mentah
    public function getConnection() {
        return $this->db;
    }

    // Helper: tentukan tipe untuk bind_param
    private function getType($value) {
        if (is_int($value)) return 'i';
        if (is_float($value)) return 'd';
        return 's';
    }
}