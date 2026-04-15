<?php
require_once __DIR__ . '/../data/MemberRepository.php';

class MemberService {
    private MemberRepository $repository;

    // Dependency Injection — Repository hyn si parameter
    public function __construct(MemberRepository $repository) {
        $this->repository = $repository;
    }

    // Metoda 1: Listo me filtrim sipas statusit
    public function listo(string $filter = ''): array {
        try {
            $members = $this->repository->getAll();
            if ($filter === '') return $members;
            return array_values(array_filter($members, function($m) use ($filter) {
                return $m['membership_status'] === $filter;
            }));
        } catch (Exception $e) {
            error_log("MemberService::listo() error: " . $e->getMessage());
            return [];
        }
    }

    // Metoda 2: Shto me validim
    public function shto(array $data): array {
        try {
            // Validim — emri
            if (empty(trim($data['first_name'] ?? ''))) {
                return ['success' => false, 'message' => 'Emri nuk mund të jetë bosh!'];
            }
            // Validim — mbiemri
            if (empty(trim($data['last_name'] ?? ''))) {
                return ['success' => false, 'message' => 'Mbiemri nuk mund të jetë bosh!'];
            }
            // Validim — email
            if (empty(trim($data['email'] ?? ''))) {
                return ['success' => false, 'message' => 'Email nuk mund të jetë bosh!'];
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Email nuk është valid!'];
            }
            // Validim — email duplikat
            foreach ($this->repository->getAll() as $m) {
                if ($m['email'] === $data['email']) {
                    return ['success' => false, 'message' => 'Ky email është already i regjistruar!'];
                }
            }
            $this->repository->add($data);
            return ['success' => true, 'message' => 'Anëtari u shtua me sukses!'];
        } catch (Exception $e) {
            error_log("MemberService::shto() error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ndodhi një gabim i papritur!'];
        }
    }

    // Metoda 3: Gjej sipas ID
    public function gjej(int $id): ?array {
        try {
            if ($id <= 0) return null;
            $member = $this->repository->getById($id);
            return $member ?? null;
        } catch (Exception $e) {
            error_log("MemberService::gjej() error: " . $e->getMessage());
            return null;
        }
    }

    public function perditeso(int $id, array $data): array {
        try {
            if (empty(trim($data['first_name'] ?? ''))) {
                return ['success' => false, 'message' => 'Emri nuk mund të jetë bosh!'];
            }
            if (empty(trim($data['last_name'] ?? ''))) {
                return ['success' => false, 'message' => 'Mbiemri nuk mund të jetë bosh!'];
            }
            if (empty(trim($data['email'] ?? ''))) {
                return ['success' => false, 'message' => 'Email nuk mund të jetë bosh!'];
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Email nuk është valid!'];
            }
            $result = $this->repository->update($id, $data);
            if ($result) {
                return ['success' => true, 'message' => 'Anëtari u përditësua me sukses!'];
            }
            return ['success' => false, 'message' => 'Anëtari nuk u gjet!'];
        } catch (Exception $e) {
            error_log("MemberService::perditeso() error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ndodhi një gabim i papritur!'];
        }
    }

    // Delete
    public function fshi(int $id): array {
        try {
            $member = $this->repository->getById($id);
            if (!$member) {
                return ['success' => false, 'message' => 'Anëtari nuk u gjet!'];
            }
            $this->repository->delete($id);
            return ['success' => true, 'message' => 'Anëtari u fshi me sukses!'];
        } catch (Exception $e) {
            error_log("MemberService::fshi() error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ndodhi një gabim i papritur!'];
        }
    }

    public function kerko(string $query): array {
        try {
            if (empty(trim($query))) return $this->listo();
            $members = $this->repository->getAll();
            return array_values(array_filter($members, function($m) use ($query) {
                $q = strtolower(trim($query));
                return str_contains(strtolower($m['first_name']), $q) ||
                       str_contains(strtolower($m['last_name']), $q) ||
                       str_contains(strtolower($m['email']), $q);
            }));
        } catch (Exception $e) {
            error_log("MemberService::kerko() error: " . $e->getMessage());
            return [];
        }
    }

    public function statistika(): array {
        try {
            $members = $this->repository->getAll();
            $total   = count($members);
            $active  = count(array_filter($members, fn($m) => $m['membership_status'] === 'active'));
            $expired = count(array_filter($members, fn($m) => $m['membership_status'] === 'expired'));
            $pending = count(array_filter($members, fn($m) => $m['membership_status'] === 'pending'));
            return [
                'total'           => $total,
                'active'          => $active,
                'expired'         => $expired,
                'pending'         => $pending,
                'active_percent'  => $total > 0 ? round(($active / $total) * 100) : 0,
                'expired_percent' => $total > 0 ? round(($expired / $total) * 100) : 0,
            ];
        } catch (Exception $e) {
            error_log("MemberService::statistika() error: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'expired' => 0, 'pending' => 0, 'active_percent' => 0, 'expired_percent' => 0];
        }
    }
}