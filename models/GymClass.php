<?php

class GymClass {
    private int $id;
    private string $name;
    private string $time;
    private int $capacity;
    private int $enrolled;
    private string $trainer;
    private string $room;
    private string $status;

    public function __construct(int $id, string $name, string $time, int $capacity, string $trainer, string $room) {
        $this->id = $id;
        $this->name = $name;
        $this->time = $time;
        $this->capacity = $capacity;
        $this->enrolled = 0;
        $this->trainer = $trainer;
        $this->room = $room;
        $this->status = 'active';
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getTime(): string { return $this->time; }
    public function getCapacity(): int { return $this->capacity; }
    public function getEnrolled(): int { return $this->enrolled; }
    public function getTrainer(): string { return $this->trainer; }
    public function getRoom(): string { return $this->room; }
    public function getStatus(): string { return $this->status; }
    public function getAvailableSpots(): int { return $this->capacity - $this->enrolled; }

    public function setEnrolled(int $enrolled): void { $this->enrolled = $enrolled; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function addEnrollment(): void { $this->enrolled++; }
}