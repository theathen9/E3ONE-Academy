<?php

class ORM
{
    private $db;
    private $table;

    private $select = "*";
    private $joins = [];
    private $where = [];
    private $orWhere = [];
    private $rawWhere = [];
    private $params = [];
    // private $types = "";
    private $order = "";
    private $group = "";
    private $limit = "";
    private $offset = "";

    private $relations = [];

    private $primaryKey;

    public function __construct($db, $table, $primaryKey = "id")
    {
        $this->db = $db;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    // ========================
    // Conditional Methods
    // ========================
    private function allowedOperator($operator)
    {
        $allowed = [
            '=',
            '!=',
            '<>',
            '>',
            '<',
            '>=',
            '<=',
            'LIKE',
            'ILIKE',   // PostgreSQL case-insensitive LIKE
            'IN',
            'NOT IN',
            'IS',
            'IS NOT'
        ];

        $operator = strtoupper($operator);

        if (!in_array($operator, $allowed, true)) {
            throw new InvalidArgumentException("Invalid operator: {$operator}");
        }

        return $operator;
    }

    private function loadBelongsTo(array $rows, string $relationName, array $relation)
    {
        $foreignKey = $relation['foreignKey'];
        $ownerKey   = $relation['ownerKey'];

        $ids = array_unique(array_column($rows, $foreignKey));

        if (empty($ids)) {
            return $rows;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT * FROM {$relation['table']}
            WHERE {$ownerKey} IN ($placeholders)";

        $records = $this->db->select($sql, $ids);

        $map = [];

        foreach ($records as $record) {
            $map[$record[$ownerKey]] = $record;
        }

        foreach ($rows as &$row) {
            $row[$relationName] = $map[$row[$foreignKey]] ?? null;
        }

        return $rows;
    }

    private function loadHasMany(array $rows, string $relationName, array $relation)
    {
        $localKey   = $relation['localKey'];
        $foreignKey = $relation['foreignKey'];

        $ids = array_unique(array_column($rows, $localKey));

        if (empty($ids)) {
            return $rows;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT *
            FROM {$relation['table']}
            WHERE {$foreignKey} IN ($placeholders)";

        $records = $this->db->select($sql, $ids);

        $grouped = [];

        foreach ($records as $record) {
            $grouped[$record[$foreignKey]][] = $record;
        }

        foreach ($rows as &$row) {
            $row[$relationName] = $grouped[$row[$localKey]] ?? [];
        }

        return $rows;
    }

    private function loadHasOne(array $rows, string $relationName, array $relation)
    {
        $localKey   = $relation['localKey'];
        $foreignKey = $relation['foreignKey'];

        $ids = array_unique(array_column($rows, $localKey));

        if (empty($ids)) {
            return $rows;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT *
            FROM {$relation['table']}
            WHERE {$foreignKey} IN ($placeholders)";

        $records = $this->db->select($sql, $ids);

        $map = [];

        foreach ($records as $record) {
            $map[$record[$foreignKey]] = $record;
        }

        foreach ($rows as &$row) {
            $row[$relationName] = $map[$row[$localKey]] ?? null;
        }

        return $rows;
    }

    private function loadRelations(array $rows)
    {
        if (empty($rows) || empty($this->relations)) {
            return $rows;
        }

        foreach ($this->relations as $relationName) {

            if (!method_exists($this, $relationName)) {
                throw new Exception("Relationship '{$relationName}' not found.");
            }

            $relation = $this->{$relationName}();

            switch ($relation['type']) {

                case 'belongsTo':
                    $rows = $this->loadBelongsTo($rows, $relationName, $relation);
                    break;

                case 'hasMany':
                    $rows = $this->loadHasMany($rows, $relationName, $relation);
                    break;

                case 'hasOne':
                    $rows = $this->loadHasOne($rows, $relationName, $relation);
                    break;

                default:
                    throw new Exception("Unsupported relationship type: {$relation['type']}");
            }
        }

        return $rows;
    }



    // =========================
    // SELECT
    // =========================
    public function select($columns = "*")
    {
        if (is_array($columns)) {
            $columns = implode(", ", $columns);
        }

        $this->select = $columns;

        return $this;
    }


    public function count()
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";

        $conditions = [];

        if (!empty($this->where)) {
            $conditions[] = implode(" AND ", $this->where);
        }

        if (!empty($this->orWhere)) {
            $conditions[] = "(" . implode(" OR ", $this->orWhere) . ")";
        }

        if (!empty($this->rawWhere)) {
            $conditions[] = implode(" AND ", $this->rawWhere);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $row = $this->db->selectOne($sql, $this->params);

        $this->reset();

        return $row ? (int)$row['total'] : 0;
    }

    // =========================
    // WHERE
    // =========================
    public function where($column, $operator, $value)
    {
        $operator = $this->allowedOperator($operator);

        $this->where[] = "$column $operator ?";
        $this->bind($value);

        return $this;
    }


    public function orWhere($column, $operator, $value)
    {
        $operator = $this->allowedOperator($operator);

        $this->orWhere[] = "$column $operator ?";
        $this->bind($value);

        return $this;
    }


    public function whereRaw($condition, array $bindings = [])
    {
        $this->rawWhere[] = $condition;

        foreach ($bindings as $binding) {
            $this->bind($binding);
        }

        return $this;
    }

    // =========================
    // JOIN
    // =========================
    // public function join($table, $first, $operator, $second, $type = "INNER")
    // {
    //     $this->joins[] = "$type JOIN $table ON $first $operator $second";
    //     return $this;
    // }

    public function join($table, $condition, $type = "INNER")
    {
        $allowed = [
            "INNER",
            "LEFT",
            "RIGHT",
            "FULL",
            "LEFT OUTER",
            "RIGHT OUTER"

        ];

        $type = strtoupper($type);

        if (!in_array($type, $allowed)) {
            throw new Exception("Invalid join type");
        }

        $this->joins[] = "$type JOIN $table ON $condition";

        return $this;
    }

    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    // =========================
    // ORDER / GROUP
    // =========================
    public function orderBy($column, $dir = "ASC")
    {
        $dir = strtoupper($dir);

        if (!in_array($dir, ["ASC", "DESC"])) {
            throw new Exception("Invalid order direction");
        }

        $this->order = " ORDER BY $column $dir";

        return $this;
    }


    public function groupBy($column)
    {
        $this->group = " GROUP BY $column";

        return $this;
    }

    // =========================
    // LIMIT / OFFSET
    // =========================
    public function limit(int $limit)
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    // =========================
    // RELATIONSHIP (BASIC)
    // =========================
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        $this->relations = array_merge($this->relations, $relations);

        return $this;
    }
    // =========================
    // BUILD SQL
    // =========================
    private function buildSQL()
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        $conditions = [];

        if (!empty($this->where)) {
            $conditions[] = implode(" AND ", $this->where);
        }

        if (!empty($this->orWhere)) {
            $conditions[] = "(" . implode(" OR ", $this->orWhere) . ")";
        }

        if (!empty($this->rawWhere)) {
            $conditions[] = implode(" AND ", $this->rawWhere);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if (!empty($this->group)) {
            $sql .= $this->group;
        }

        if (!empty($this->order)) {
            $sql .= $this->order;
        }

        if (!empty($this->limit)) {
            $sql .= $this->limit;
        }

        if (!empty($this->offset)) {
            $sql .= $this->offset;
        }

        return $sql;
    }

    // =========================
    // GET
    // =========================
    public function get()
    {
        $sql = $this->buildSQL();

        $rows = $this->db->select($sql, $this->params);

        if (!empty($rows) && !empty($this->relations)) {
            $rows = $this->loadRelations($rows);
        }

        $this->reset();

        return $rows ?: [];
    }

    public function hasOne($relatedTable, $foreignKey, $localKey = "id")
    {
        return [
            'type' => 'hasOne',
            'table' => $relatedTable,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey
        ];
    }

    // =========================
    // FIRST
    // =========================
    // public function first()
    // {
    //     $data = $this->limit(1)->get();

    //     return !empty($data) ? $data[0] : null;
    // }
    public function first()
    {
        return $this->limit(1)->get()[0] ?? null;
    }

    // =========================
    // increment
    // =========================
    public function increment($column, $amount = 1)
    {
        $sql = "UPDATE {$this->table} SET {$column} = {$column} + ?";

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        $params = array_merge([$amount], $this->params);

        $this->reset();

        return $this->db->update($sql, $params);
    }

    // =========================
    // PAGINATION
    // =========================
    public function paginate($perPage, $page = 1)
    {
        $offset = ($page - 1) * $perPage;

        $data = $this->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            "data" => $data,
            "page" => $page,
            "per_page" => $perPage
        ];
    }

    public function find($id)
    {
        $sql = "SELECT {$this->select}
            FROM {$this->table}
            WHERE {$this->primaryKey} = ?
            LIMIT 1";

        $row = $this->db->selectOne($sql, [$id]);

        $this->reset();

        return $row ?: null;
    }



    // =========================
    // INSERT
    // =========================
    public function insert(array $data)
    {
        $fields = array_keys($data);
        $placeholders = implode(", ", array_fill(0, count($fields), "?"));

        $sql = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ")
            VALUES ({$placeholders})";

        return $this->db->insert($sql, array_values($data));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(array $data)
    {
        $set = [];
        $params = [];

        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $params[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(", ", $set);

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        $params = array_merge($params, $this->params);

        $this->reset();

        return $this->db->update($sql, $params);
    }

    // =========================
    // DELETE
    // =========================
    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        $params = $this->params;

        $this->reset();

        return $this->db->delete($sql, $params);
    }

    // =========================
    // SEARCH
    // =========================
    public function search($value, array $columns)
    {
        $group = [];

        foreach ($columns as $column) {
            $group[] = "{$column} ILIKE ?";
            $this->bind("%{$value}%");
        }

        $this->where[] = "(" . implode(" OR ", $group) . ")";

        return $this;
    }

    // =========================
    // BIND HELPERS
    // =========================
    private function bind($value)
    {
        $this->params[] = $value;
    }

    // =========================
    // DEBUG SQL (Laravel style)
    // =========================
    public function toSql()
    {
        return $this->buildSQL();
    }

    // ========================
    // Relationship definitions (belongsTo(), hasMany(), hasOne())
    // ========================
    // A model class that declares relationships
    // Eager loading via with()

    public function belongsTo($relatedTable, $foreignKey, $ownerKey = "id")
    {
        return [
            'type' => 'belongsTo',
            'table' => $relatedTable,
            'foreignKey' => $foreignKey,
            'ownerKey' => $ownerKey
        ];
    }

    public function hasMany($relatedTable, $foreignKey, $localKey = "id")
    {
        return [
            'type' => 'hasMany',
            'table' => $relatedTable,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey
        ];
    }

    // =========================
    // RESET
    // =========================
    public function reset()
    {
        $this->select = "*";
        $this->joins = [];
        $this->where = [];
        $this->orWhere = [];
        $this->rawWhere = [];
        $this->params = [];
        $this->order = "";
        $this->group = "";
        $this->limit = "";
        $this->offset = "";
        $this->relations = [];

        return $this;
    }
}
