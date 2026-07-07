<?php
// ./core/DB.php

class DB
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // 🔥 CORE QUERY EXECUTOR (for INSERT/UPDATE/DELETE)
    public function execute($sql, $types = "", $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        // bind params if exists
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    // 🔍 SELECT MULTIPLE ROWS
    public function select($sql, $types = "", $params = [])
    {
        $stmt = $this->execute($sql, $types, $params);
        return $stmt->get_result();
    }

    // 🔍 SELECT SINGLE ROW
    public function selectOne($sql, $types = "", $params = [])
    {
        $result = $this->select($sql, $types, $params);
        return $result->fetch_assoc();
    }

    // ➕ INSERT (returns last insert ID)
    public function insert($sql, $types = "", $params = [])
    {
        $stmt = $this->execute($sql, $types, $params);
        return $this->conn->insert_id;
    }

    // ✏️ UPDATE (returns affected rows)
    public function update($sql, $types = "", $params = [])
    {
        $stmt = $this->execute($sql, $types, $params);
        return $stmt->affected_rows;
    }

    // ❌ DELETE (returns affected rows)
    public function delete($sql, $types = "", $params = [])
    {
        $stmt = $this->execute($sql, $types, $params);
        return $stmt->affected_rows;
    }

    // 🔁 TRANSACTIONS
    public function beginTransaction()
    {
        $this->conn->begin_transaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollback()
    {
        $this->conn->rollback();
    }

    // 🧠 GET RAW CONNECTION (optional advanced use)
    public function getConnection()
    {
        return $this->conn;
    }
}