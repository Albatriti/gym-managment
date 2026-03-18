<?php

class Member {
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $phone;
    private string $membershipStatus;
    private string $membershipExpiry;

    public function __construct(int $id, string $firstName, string $lastName, string $email, string $phone, string $membershipExpiry) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->membershipExpiry = $membershipExpiry;
        $this->membershipStatus = 'active';
    }

    public function getId(): int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getMembershipStatus(): string { return $this->membershipStatus; }
    public function getMembershipExpiry(): string { return $this->membershipExpiry; }

    public function setMembershipStatus(string $status): void { $this->membershipStatus = $status; }
    public function setMembershipExpiry(string $date): void { $this->membershipExpiry = $date; }
}