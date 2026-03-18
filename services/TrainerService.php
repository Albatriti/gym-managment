<?php

require_once '../models/Trainer.php';

class TrainerService {
    private array $trainers = [];

    public function getAll(): array {
        return $this->trainers;
    }

    public function getById(int $id): ?Trainer {
        foreach ($this->trainers as $trainer) {
            if ($trainer->getId() === $id) {
                return $trainer;
            }
        }
        return null;
    }

    public function add(Trainer $trainer): void {
        $this->trainers[] = $trainer;
    }

    public function delete(int $id): void {
        $this->trainers = array_filter($this->trainers, function($t) use ($id) {
            return $t->getId() !== $id;
        });
    }

    public function getTotalCount(): int {
        return count($this->trainers);
    }

    public function assignClass(int $trainerId, string $className): void {
        $trainer = $this->getById($trainerId);
        if ($trainer) {
            $trainer->addClass($className);
        }
    }
}