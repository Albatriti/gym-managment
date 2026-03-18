<?php

require_once 'Database.php';

interface IRepository {
    public function getAll(): array;
    public function getById(int $id): mixed;
    public function add(array $data): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

class DatabaseRepository implements IRepository {
    protected PDO $connection;
    protected string $table;

    public function __construct(string $table) {
        $this->connection = Database::getInstance()->getConnection();
        $this->table = $table;
    }

    public function getAll(): array {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): mixed {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add(array $data): bool {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})"
        );
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool {
        $set = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $stmt = $this->connection->prepare(
            "UPDATE {$this->table} SET {$set} WHERE id = :id"
        );
        $data[':id'] = $id;
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}