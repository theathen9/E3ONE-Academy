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
        try {

            $stmt = $this->conn->prepare($sql);

            $stmt->execute($params);

            return $stmt;

        } catch (PDOException $e) {

            throw new Exception(
                "Database Error: " . $e->getMessage()
            );
        }
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


    // ➕ INSERT (returns last insert ID)
    public function insert($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $this->conn->lastInsertId();
    }


    // ✏️ UPDATE (returns affected rows)
    public function update($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $stmt->rowCount();
    }


    // ❌ DELETE (returns affected rows)
    public function delete($sql, $params = [])
    {
        $stmt = $this->execute($sql, $params);

        return $stmt->rowCount();
    }


    // 🔁 TRANSACTIONS
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
