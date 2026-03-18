<?php

require_once '../models/GymClass.php';

class ClassService {
    private array $classes = [];

    public function getAll(): array {
        return $this->classes;
    }

    public function getById(int $id): ?GymClass {
        foreach ($this->classes as $class) {
            if ($class->getId() === $id) {
                return $class;
            }
        }
        return null;
    }

    public function add(GymClass $class): void {
        $this->classes[] = $class;
    }

    public function delete(int $id): void {
        $this->classes = array_filter($this->classes, function($c) use ($id) {
            return $c->getId() !== $id;
        });
    }

    public function reserve(int $classId): bool {
        $class = $this->getById($classId);
        if ($class && $class->getAvailableSpots() > 0) {
            $class->addEnrollment();
            return true;
        }
        return false;
    }

    public function getAvailableClasses(): array {
        return array_filter($this->classes, function($c) {
            return $c->getAvailableSpots() > 0;
        });
    }

    public function getTotalCount(): int {
        return count($this->classes);
    }
}