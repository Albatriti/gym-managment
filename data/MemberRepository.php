<?php
require_once __DIR__ . '/IRepository.php';

class MemberRepository implements IRepository {
    private string $csvFile;

    public function __construct() {
        $this->csvFile = __DIR__ . '/members.csv';
        $this->initFile();
    }

    // Error Handling — nëse CSV nuk ekziston, krijohet automatikisht
    private function initFile(): void {
        if (!file_exists($this->csvFile)) {
            $file = fopen($this->csvFile, 'w');
            fputcsv($file, ['id', 'first_name', 'last_name', 'email', 'phone', 'membership_status', 'membership_expiry']);
            fclose($file);
        }
    }

    public function getAll(): array {
        $members = [];
        try {
            if (!file_exists($this->csvFile)) {
                $this->initFile();
                return $members;
            }
            $file = fopen($this->csvFile, 'r');
            $header = fgetcsv($file);
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) >= 7) {
                    $members[] = [
                        'id'                => $row[0],
                        'first_name'        => $row[1],
                        'last_name'         => $row[2],
                        'email'             => $row[3],
                        'phone'             => $row[4],
                        'membership_status' => $row[5],
                        'membership_expiry' => $row[6],
                    ];
                }
            }
            fclose($file);
        } catch (Exception $e) {
            error_log("MemberRepository::getAll() error: " . $e->getMessage());
        }
        return $members;
    }

    public function getById(int $id): mixed {
        try {
            foreach ($this->getAll() as $member) {
                if ((int)$member['id'] === $id) return $member;
            }
        } catch (Exception $e) {
            error_log("MemberRepository::getById() error: " . $e->getMessage());
        }
        return null;
    }

    public function add(array $data): bool {
        try {
            $members = $this->getAll();
            $newId   = count($members) > 0 ? max(array_column($members, 'id')) + 1 : 1;
            $file    = fopen($this->csvFile, 'a');
            fputcsv($file, [
                $newId,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone']             ?? '',
                $data['membership_status'] ?? 'active',
                $data['membership_expiry'] ?? date('Y-m-d', strtotime('+1 month')),
            ]);
            fclose($file);
            return true;
        } catch (Exception $e) {
            error_log("MemberRepository::add() error: " . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool {
        try {
            $members = $this->getAll();
            $updated = false;
            $file    = fopen($this->csvFile, 'w');
            fputcsv($file, ['id', 'first_name', 'last_name', 'email', 'phone', 'membership_status', 'membership_expiry']);
            foreach ($members as $member) {
                if ((int)$member['id'] === $id) {
                    fputcsv($file, [
                        $id,
                        $data['first_name']        ?? $member['first_name'],
                        $data['last_name']          ?? $member['last_name'],
                        $data['email']              ?? $member['email'],
                        $data['phone']              ?? $member['phone'],
                        $data['membership_status']  ?? $member['membership_status'],
                        $data['membership_expiry']  ?? $member['membership_expiry'],
                    ]);
                    $updated = true;
                } else {
                    fputcsv($file, array_values($member));
                }
            }
            fclose($file);
            return $updated;
        } catch (Exception $e) {
            error_log("MemberRepository::update() error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $members = $this->getAll();
            $deleted = false;
            $file    = fopen($this->csvFile, 'w');
            fputcsv($file, ['id', 'first_name', 'last_name', 'email', 'phone', 'membership_status', 'membership_expiry']);
            foreach ($members as $member) {
                if ((int)$member['id'] === $id) {
                    $deleted = true;
                } else {
                    fputcsv($file, array_values($member));
                }
            }
            fclose($file);
            return $deleted;
        } catch (Exception $e) {
            error_log("MemberRepository::delete() error: " . $e->getMessage());
            return false;
        }
    }

    public function save(): bool {
        return file_exists($this->csvFile);
    }
}