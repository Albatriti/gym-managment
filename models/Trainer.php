<?php

class Trainer {
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $specialization;
    private array $assignedClasses = [];

    public function __construct(int $id, string $firstName, string $lastName, string $specialization) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->specialization = $specialization;
    }

    public function getId(): int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getSpecialization(): string { return $this->specialization; }
    public function getAssignedClasses(): array { return $this->assignedClasses; }

    public function addClass(string $className): void { $this->assignedClasses[] = $className; }
    public function setSpecialization(string $spec): void { $this->specialization = $spec; }
}