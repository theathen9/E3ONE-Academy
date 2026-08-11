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


    // SELECT
    // =========================
    // SELECT
    // =========================
    public function select($columns = "*")
    {
        if (is_array($columns)) {
            $this->select = implode(", ", $columns);
        } else {
            $this->select = $columns;
        }

        return $this;
    }


    public function count()
    {
        $sql = $this->buildCountSQL();

        $row = $this->db->selectOne(
            $sql,
            $this->params
        );

        $this->reset();

        return $row ? (int)$row['total'] : 0;
    }

    // WHERE

    public function where($column, $operator, $value)
    {
        $this->where[] = "$column $operator ?";
        $this->params[] = $value;

        return $this;
    }


    public function orWhere($column, $operator, $value)
    {
        $this->orWhere[] = "$column $operator ?";
        $this->params[] = $value;

        return $this;
    }


    // =========================
    // WHERE RAW
    // =========================
    public function whereRaw($condition, array $params = [])
    {
        $this->rawWhere[] = $condition;

        if (!empty($params)) {
            $this->params = array_merge(
                $this->params,
                $params
            );
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
    public function limit($limit)
    {
        $this->limit = " LIMIT " . (int)$limit;

        return $this;
    }


    public function offset($offset)
    {
        $this->offset = " OFFSET " . (int)$offset;

        return $this;
    }

    // =========================
    // RELATIONSHIP (BASIC)
    // =========================
    public function with($relation)
    {
        $this->relations[] = $relation;

        return $this;
    }


    // =========================
    // BUILD SQL
    // =========================
    private function buildSQL()
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";


        if ($this->joins) {
            $sql .= " " . implode(" ", $this->joins);
        }


        $conditions = [];


        if ($this->where) {
            $conditions[] = implode(" AND ", $this->where);
        }


        if ($this->orWhere) {
            $conditions[] = "(" . implode(" OR ", $this->orWhere) . ")";
        }


        if ($this->rawWhere) {
            $conditions[] = implode(" AND ", $this->rawWhere);
        }


        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }


        $sql .= $this->group;
        $sql .= $this->order;
        $sql .= $this->limit;
        $sql .= $this->offset;


        return $sql;
    }

    private function buildCountSQL()
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}";


        $conditions = [];


        if ($this->where) {
            $conditions[] = implode(" AND ", $this->where);
        }


        if ($this->orWhere) {
            $conditions[] =
                "(" . implode(" OR ", $this->orWhere) . ")";
        }


        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }


        return $sql;
    }

    // =========================
    // GET
    // =========================
    public function get()
    {
        $rows = $this->db->select(
            $this->buildSQL(),
            $this->params
        );

        $this->reset();

        return $rows ?: [];
    }

    // =========================
    // FIRST
    // =========================
    public function first()
    {
        return $this->limit(1)->get()[0] ?? null;
    }

    public function find($id)
    {
        $row = $this->db->selectOne(
            "SELECT {$this->select}
             FROM {$this->table}
             WHERE {$this->primaryKey}=?
             LIMIT 1",
            [$id]
        );

        $this->reset();

        return $row;
    }

    // =========================
    // INSERT
    // =========================
    public function insert($data)
    {
        $fields = array_keys($data);

        $sql = "INSERT INTO {$this->table}
                (" . implode(",", $fields) . ")
                VALUES
                (" . implode(",", array_fill(0, count($fields), "?")) . ")";


        return $this->db->insert(
            $sql,
            array_values($data)
        );
    }

    // UPDATE

    public function update($data)
    {
        $set = [];

        $values = [];


        foreach ($data as $key => $value) {

            $set[] = "$key=?";

            $values[] = $value;
        }


        $sql =
            "UPDATE {$this->table}
             SET " . implode(",", $set);


        if ($this->where) {

            $sql .= " WHERE " . implode(
                " AND ",
                $this->where
            );
        }


        $values = array_merge(
            $values,
            $this->params
        );


        $result = $this->db->update(
            $sql,
            $values
        );


        $this->reset();


        return $result;
    }

    // DELETE

    public function delete()
    {
        $sql =
            "DELETE FROM {$this->table}";


        if ($this->where) {

            $sql .= " WHERE " .
                implode(
                    " AND ",
                    $this->where
                );
        }


        $result = $this->db->delete(
            $sql,
            $this->params
        );


        $this->reset();


        return $result;
    }



    // SEARCH

    public function search($value, $columns)
    {
        $items = [];


        foreach ($columns as $column) {

            $items[] = "$column LIKE ?";

            $this->params[] = "%$value%";
        }


        $this->where[] =
            "(" . implode(" OR ", $items) . ")";


        return $this;
    }



    // PAGINATION

    public function paginate($perPage, $page = 1)
    {
        $offset =
            ($page - 1) * $perPage;


        return [
            "data" => $this
                ->limit($perPage)
                ->offset($offset)
                ->get(),

            "page" => $page,
            "per_page" => $perPage
        ];
    }


    // =========================
    // DEBUG SQL (Laravel style)
    // =========================
    public function toSql()
    {
        return $this->buildSQL();
    }


    // =========================
    // increment
    // =========================
    public function increment($column, $amount = 1)
    {
        $sql = "UPDATE {$this->table} SET {$column} = {$column} + ?";

        if ($this->where) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        $params = array_merge([$amount], $this->params);
        $types = $this->type($amount) . $this->types;

        $this->reset();

        return $this->db->update($sql, $types, $params);
    }

    // =========================
    // BIND HELPERS
    // =========================
    private function bind($value)
    {
        $this->params[] = $value;
        $this->types .= $this->type($value);
    }

    private function type($val)
    {
        if (is_int($val)) return "i";
        if (is_float($val)) return "d";
        return "s";
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
