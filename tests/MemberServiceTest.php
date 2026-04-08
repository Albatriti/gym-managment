<?php
require_once __DIR__ . '/../data/MemberRepository.php';
require_once __DIR__ . '/../services/MemberService.php';

class MemberServiceTest {
    private MemberService $service;
    private string $testCsv;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct() {
        $this->testCsv = __DIR__ . '/test_members.csv';
        $this->setupTestData();
        $repo = new MemberRepository();
        // Override CSV file për teste
        $reflection = new ReflectionClass($repo);
        $prop = $reflection->getProperty('csvFile');
        $prop->setAccessible(true);
        $prop->setValue($repo, $this->testCsv);
        $this->service = new MemberService($repo);
    }

    private function setupTestData(): void {
        $file = fopen($this->testCsv, 'w');
        fputcsv($file, ['id', 'first_name', 'last_name', 'email', 'phone', 'membership_status', 'membership_expiry']);
        fputcsv($file, [1, 'Arben', 'Krasniqi', 'arben@test.com', '+38344111', 'active', '2026-05-01']);
        fputcsv($file, [2, 'Drita', 'Morina', 'drita@test.com', '+38344222', 'expired', '2026-03-01']);
        fputcsv($file, [3, 'Blerim', 'Hoxha', 'blerim@test.com', '+38344333', 'active', '2026-06-01']);
        fclose($file);
    }

    private function assert(string $testName, bool $condition): void {
        if ($condition) {
            echo "✅ PASS: {$testName}\n";
            $this->passed++;
        } else {
            echo "❌ FAIL: {$testName}\n";
            $this->failed++;
        }
    }

    // Test 1: listo() kthen të gjithë anëtarët
    public function test_listo_kthen_te_gjithe(): void {
        $result = $this->service->listo();
        $this->assert('listo() kthen 3 anëtarë', count($result) === 3);
    }

    // Test 2: listo() me filter kthen vetëm active
    public function test_listo_me_filter_active(): void {
        $result = $this->service->listo('active');
        $this->assert('listo(active) kthen 2 anëtarë', count($result) === 2);
    }

    // Test 3: shto() me të dhëna valid
    public function test_shto_valid(): void {
        $result = $this->service->shto([
            'first_name'        => 'Valbona',
            'last_name'         => 'Hyseni',
            'email'             => 'valbona@test.com',
            'phone'             => '+38344444',
            'membership_status' => 'active',
            'membership_expiry' => '2026-07-01',
        ]);
        $this->assert('shto() me të dhëna valid kthen sukses', $result['success'] === true);
    }

    // Test 4: shto() me emër bosh
    public function test_shto_emer_bosh(): void {
        $result = $this->service->shto([
            'first_name' => '',
            'last_name'  => 'Hyseni',
            'email'      => 'test@test.com',
        ]);
        $this->assert('shto() me emër bosh kthen error', $result['success'] === false);
    }

    // Test 5: shto() me email jo valid
    public function test_shto_email_jo_valid(): void {
        $result = $this->service->shto([
            'first_name' => 'Fisnik',
            'last_name'  => 'Gashi',
            'email'      => 'email-jo-valid',
        ]);
        $this->assert('shto() me email jo valid kthen error', $result['success'] === false);
    }

    // Test 6: gjej() me ID ekzistuese
    public function test_gjej_id_ekzistuese(): void {
        $result = $this->service->gjej(1);
        $this->assert('gjej(1) gjen anëtarin', $result !== null && $result['first_name'] === 'Arben');
    }

    // Test 7: gjej() me ID jo ekzistuese
    public function test_gjej_id_jo_ekzistuese(): void {
        $result = $this->service->gjej(999);
        $this->assert('gjej(999) kthen null', $result === null);
    }

    // Test 8: kerko() gjen anëtarin
    public function test_kerko_ekzistues(): void {
        $result = $this->service->kerko('Arben');
        $this->assert('kerko("Arben") gjen anëtarin', count($result) >= 1);
    }

    // Test 9: kerko() me emër që nuk ekziston
    public function test_kerko_jo_ekzistues(): void {
        $result = $this->service->kerko('NukEkziston123');
        $this->assert('kerko("NukEkziston123") kthen listë bosh', count($result) === 0);
    }

    // Test 10: statistika() kthen numra korrekt
    public function test_statistika(): void {
        $result = $this->service->statistika();
        $this->assert('statistika() kthen total > 0', $result['total'] > 0);
    }

    public function run(): void {
        echo "\n==========================================\n";
        echo "   GymFlow — Unit Tests — MemberService\n";
        echo "==========================================\n\n";

        $this->test_listo_kthen_te_gjithe();
        $this->test_listo_me_filter_active();
        $this->test_shto_valid();
        $this->test_shto_emer_bosh();
        $this->test_shto_email_jo_valid();
        $this->test_gjej_id_ekzistuese();
        $this->test_gjej_id_jo_ekzistuese();
        $this->test_kerko_ekzistues();
        $this->test_kerko_jo_ekzistues();
        $this->test_statistika();

        echo "\n==========================================\n";
        echo "   ✅ Passed: {$this->passed} | ❌ Failed: {$this->failed}\n";
        echo "==========================================\n\n";

        // Fshi CSV e testit
        if (file_exists($this->testCsv)) unlink($this->testCsv);
    }
}

$test = new MemberServiceTest();
$test->run();
//```

//Për ta ekzekutuar, shko te browser:
//```
//http://localhost/gym-managment/tests/MemberServiceTest.php