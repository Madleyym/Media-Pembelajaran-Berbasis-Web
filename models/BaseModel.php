<?php
abstract class BaseModel
{
    protected PDO $conn;
    protected string $table;
    protected array $fillable = [];

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll($conditions = [], $orderBy = null)
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $fields = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($values)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_values($data));

        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $fields = implode('=?, ', array_keys($data)) . '=?';

        $sql = "UPDATE {$this->table} SET $fields WHERE id = ?";
        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
