<?php

interface IRepository {
    public function getAll(): array;
    public function getById(int $id): mixed;
    public function add(array $data): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}