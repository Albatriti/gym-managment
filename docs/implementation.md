# 📋 Implementimi CRUD — Gym Management System

## Çfarë u Implementua

Ky dokument shpjegon implementimin e plotë të operacioneve CRUD për modelin **Member** duke përdorur arkitekturën e shtresuar të projektit.

---

## Ushtrimi 1 — Model + Repository

### Member (models/Member.php)
Modeli `Member` ka 7 atribute:
- `id` — identifikuesi unik
- `firstName` — emri
- `lastName` — mbiemri
- `email` — adresa email
- `phone` — numri i telefonit
- `membershipStatus` — statusi (active/expired/pending)
- `membershipExpiry` — data e skadimit

### FileRepository (data/MemberRepository.php)
`MemberRepository` implementon `IRepository` dhe lexon/shkruan nga **CSV file**:

| Metoda | Përshkrimi |
|--------|------------|
| `getAll()` | Lexon të gjitha rekordet nga CSV |
| `getById(id)` | Gjen një rekord sipas ID |
| `add(data)` | Shton rekord të ri në CSV |
| `update(id, data)` | Përditëson rekord ekzistues |
| `delete(id)` | Fshin rekord nga CSV |
| `save()` | Verifikon ekzistencën e CSV file |

### CSV File (data/members.csv)
```csv
id,first_name,last_name,email,phone,membership_status,membership_expiry
1,Arben,Krasniqi,arben.k@email.com,+383441234567,active,2026-05-01
2,Drita,Morina,drita.m@email.com,+383442345678,active,2026-05-15
3,Blerim,Hoxha,blerim.h@email.com,+383443456789,expired,2026-03-10
4,Valbona,Hyseni,valbona.h@email.com,+383444567890,active,2026-06-20
5,Mimoza,Bajra,mimoza.b@email.com,+383445678901,pending,2026-03-14
```

---

## Ushtrimi 2 — Service me Logjikë

### MemberService (services/MemberService.php)

**Dependency Injection:** Repository hyn si parameter në konstruktor:
```php
public function __construct(MemberRepository $repository) {
    $this->repository = $repository;
}
```

**3 Metodat Kryesore:**

| Metoda | Përshkrimi | Validimi |
|--------|------------|----------|
| `listo(filter)` | Liston anëtarët me filtrim sipas statusit | — |
| `shto(data)` | Shton anëtar të ri | Emri jo bosh, email valid, email unik |
| `gjej(id)` | Gjen anëtar sipas ID | — |

**Validimi i Input:**
```php
if (empty(trim($data['first_name'])))
    return ['success' => false, 'message' => 'Emri nuk mund të jetë bosh!'];

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
    return ['success' => false, 'message' => 'Email nuk është valid!'];
```

---

## Ushtrimi 3 — UI Funksionale

### Rrjedha e Plotë
```
UI (members.php) → MemberService → MemberRepository → members.csv
```

### Faqja views/admin/members.php
- **Lista** — shfaq të gjithë anëtarët nga databaza MySQL
- **Shto** — forma për shtim anëtari të ri
- **Edito** — klikimi i ✏️ hap modalin e editimit
- **Fshi** — butoni Fshi me konfirmim
- **Filter** — filtrim sipas statusit (aktiv/skaduar/pending)

### CRUD End-to-End
1. **Create** → Forma shton anëtar → ruhet në databazë
2. **Read** → Lista lexon nga databaza dhe shfaq në tabelë
3. **Update** → Butoni ✏️ → modal → përditësohet në databazë
4. **Delete** → Butoni Fshi → konfirmim → fshihet nga databaza

---

## Ushtrimi 4 — Update + Delete

### Update
```php
// Repository
public function update(int $id, array $data): bool {
    // Lexon CSV, gjen rekordin, e përditëson, rrishkruan CSV
}

// Service
public function perditeso(int $id, array $data): array {
    // Validim + thirrje Repository
}

// UI
<a href="?edit=<?php echo $m['id']; ?>">✏️</a>
```

### Delete
```php
// Repository
public function delete(int $id): bool {
    // Lexon CSV, filtron rekordin, rrishkruan CSV
}

// Service
public function fshi(int $id): array {
    // Kontrollon ekzistencën + thirrje Repository
}

// UI
<form method="POST">
    <input type="hidden" name="action" value="delete"/>
    <button class="btn-danger">Fshi</button>
</form>
```

---

## Parimet SOLID të Aplikuara

**S — Single Responsibility:**
- `MemberRepository` — vetëm operacionet CSV
- `MemberService` — vetëm logjika e biznesit
- `members.php` — vetëm ndërfaqja

**D — Dependency Inversion:**
- `MemberService` varet nga `IRepository` interface
- Jo nga implementimi konkret

---

## Teknologjitë

| Teknologjia | Përdorimi |
|-------------|-----------|
| PHP 8 | Backend, logjika |
| MySQL | Databaza kryesore |
| CSV File | FileRepository për detyrën |
| PDO | Lidhja e sigurt me MySQL |
| HTML/CSS/JS | Ndërfaqja |

---

## Autori
**Albatriti** — Universiteti i Mitrovicës "Isa Boletini"
Inxhinieri Softuerike · Semestri Veror 2026