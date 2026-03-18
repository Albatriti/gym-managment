<?php

require_once '../models/Payment.php';

class PaymentService {
    private array $payments = [];

    public function getAll(): array {
        return $this->payments;
    }

    public function getById(int $id): ?Payment {
        foreach ($this->payments as $payment) {
            if ($payment->getId() === $id) {
                return $payment;
            }
        }
        return null;
    }

    public function add(Payment $payment): void {
        $this->payments[] = $payment;
    }

    public function delete(int $id): void {
        $this->payments = array_filter($this->payments, function($p) use ($id) {
            return $p->getId() !== $id;
        });
    }

    public function getByMemberId(int $memberId): array {
        return array_filter($this->payments, function($p) use ($memberId) {
            return $p->getMemberId() === $memberId;
        });
    }

    public function getPendingPayments(): array {
        return array_filter($this->payments, function($p) {
            return $p->getStatus() === 'pending';
        });
    }

    public function getMonthlyTotal(): float {
        $total = 0;
        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'paid') {
                $total += $payment->getAmount();
            }
        }
        return $total;
    }

    public function getTotalCount(): int {
        return count($this->payments);
    }
}