<?php

class Payment {
    private int $id;
    private int $memberId;
    private float $amount;
    private string $paymentDate;
    private string $method;
    private string $status;
    private string $period;

    public function __construct(int $id, int $memberId, float $amount, string $paymentDate, string $method, string $period) {
        $this->id = $id;
        $this->memberId = $memberId;
        $this->amount = $amount;
        $this->paymentDate = $paymentDate;
        $this->method = $method;
        $this->period = $period;
        $this->status = 'paid';
    }

    public function getId(): int { return $this->id; }
    public function getMemberId(): int { return $this->memberId; }
    public function getAmount(): float { return $this->amount; }
    public function getPaymentDate(): string { return $this->paymentDate; }
    public function getMethod(): string { return $this->method; }
    public function getPeriod(): string { return $this->period; }
    public function getStatus(): string { return $this->status; }

    public function setStatus(string $status): void { $this->status = $status; }
}