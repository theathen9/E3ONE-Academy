<?php

namespace App\Core;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table);
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find($id): ?array
    {
        return static::query()
            ->where(static::$primaryKey, '=', $id)
            ->first();
    }

    public static function create(array $data): bool
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO " . static::$table .
            " ($columns) VALUES ($placeholders)";

        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute(array_values($data));
    }

    public static function updateById($id, array $data): bool
    {
        $set = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
        }

        $sql = "UPDATE " . static::$table .
            " SET " . implode(',', $set) .
            " WHERE " . static::$primaryKey . " = ?";

        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([...array_values($data), $id]);
    }

    public static function delete($id): bool
    {
        $sql = "DELETE FROM " . static::$table .
            " WHERE " . static::$primaryKey . " = ?";

        $stmt = Database::connection()->prepare($sql);

        return $stmt->execute([$id]);
    }
}
?>
