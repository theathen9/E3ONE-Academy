<?php
// ./core/DB.php

class DB
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }


    // 🔥 CORE QUERY EXECUTOR (INSERT/UPDATE/DELETE)
    public function execute($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed");
        }

        if (!$stmt->execute($params)) {
            throw new Exception(
                "Execute failed: " . implode(
                    ", ",
                    $stmt->errorInfo()
                )
            );
        }

        return $stmt;
    }


    // 🔍 SELECT MULTIPLE ROWS
    public function select($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 🔍 SELECT SINGLE ROW
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // ➕ INSERT (returns PostgreSQL ID)
    public function insert($sql, $params = [])
    {
        $this->execute($sql, $params);

        return $this->conn->lastInsertId();
    }


    // ✏️ UPDATE
    public function update($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $stmt->rowCount();
    }


    // ❌ DELETE
    public function delete($sql, $params = [])
    {
        $stmt = $this->execute($sql, params);

        return $stmt->rowCount();
    }


    // 🔁 TRANSACTION
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }


    public function commit()
    {
        $this->conn->commit();
    }


    public function rollback()
    {
        $this->conn->rollBack();
    }


    // 🧠 GET RAW CONNECTION
    public function getConnection()
    {
        return $this->conn;
    }
}
