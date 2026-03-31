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
        $members = $this->repository->getAll();
        if ($filter === '') return $members;
        return array_values(array_filter($members, function($m) use ($filter) {
            return $m['membership_status'] === $filter;
        }));
    }

    // Metoda 2: Shto me validim
    public function shto(array $data): array {
        // Validim — emri nuk duhet të jetë bosh
        if (empty(trim($data['first_name']))) {
            return ['success' => false, 'message' => 'Emri nuk mund të jetë bosh!'];
        }
        // Validim — mbiemri nuk duhet të jetë bosh
        if (empty(trim($data['last_name']))) {
            return ['success' => false, 'message' => 'Mbiemri nuk mund të jetë bosh!'];
        }
        // Validim — email nuk duhet të jetë bosh
        if (empty(trim($data['email']))) {
            return ['success' => false, 'message' => 'Email nuk mund të jetë bosh!'];
        }
        // Validim — email duhet të jetë valid
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email nuk është valid!'];
        }
        // Validim — kontroll për email duplikat
        $existing = $this->repository->getAll();
        foreach ($existing as $m) {
            if ($m['email'] === $data['email']) {
                return ['success' => false, 'message' => 'Ky email është already i regjistruar!'];
            }
        }

        $this->repository->add($data);
        $this->repository->save();
        return ['success' => true, 'message' => 'Anëtari u shtua me sukses!'];
    }

    // Metoda 3: Gjej sipas ID
    public function gjej(int $id): ?array {
        return $this->repository->getById($id);
    }

    // Update me validim
    public function perditeso(int $id, array $data): array {
        if (empty(trim($data['first_name']))) {
            return ['success' => false, 'message' => 'Emri nuk mund të jetë bosh!'];
        }
        if (empty(trim($data['last_name']))) {
            return ['success' => false, 'message' => 'Mbiemri nuk mund të jetë bosh!'];
        }
        if (empty(trim($data['email']))) {
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
    }

    // Delete
    public function fshi(int $id): array {
        $member = $this->repository->getById($id);
        if (!$member) {
            return ['success' => false, 'message' => 'Anëtari nuk u gjet!'];
        }
        $this->repository->delete($id);
        return ['success' => true, 'message' => 'Anëtari u fshi me sukses!'];
    }
}