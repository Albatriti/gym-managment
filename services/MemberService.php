<?php

require_once '../models/Member.php';

class MemberService {
    private array $members = [];

    public function getAll(): array {
        return $this->members;
    }

    public function getById(int $id): ?Member {
        foreach ($this->members as $member) {
            if ($member->getId() === $id) {
                return $member;
            }
        }
        return null;
    }

    public function add(Member $member): void {
        $this->members[] = $member;
    }

    public function delete(int $id): void {
        $this->members = array_filter($this->members, function($m) use ($id) {
            return $m->getId() !== $id;
        });
    }

    public function getActiveMembers(): array {
        return array_filter($this->members, function($m) {
            return $m->getMembershipStatus() === 'active';
        });
    }

    public function getExpiredMembers(): array {
        return array_filter($this->members, function($m) {
            return $m->getMembershipStatus() === 'expired';
        });
    }

    public function getTotalCount(): int {
        return count($this->members);
    }
}