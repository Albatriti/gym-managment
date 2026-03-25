# 🏋️ GymFlow — Gym Management System

Sistem i plotë për menaxhimin e palestrës i zhvilluar si projekt semestral për lëndën **Inxhinieri Softuerike** në Universitetin e Mitrovicës "Isa Boletini". GymFlow mundëson menaxhimin e anëtarëve, trajnerëve, pagesave, klasave dhe hyrjeve në palestër përmes një ndërfaqeje moderne dhe intuitive.

---

## 📋 Përshkrimi i Projektit

GymFlow është një aplikacion web i ndërtuar me arkitekturë të shtresuar (Layered Architecture) që ndjek parimet e SOLID dhe aplikon Design Patterns si Repository Pattern dhe Singleton Pattern. Sistemi është i ndarë në tre role kryesore — Admin, Staff dhe Member — ku secili rol ka akses në funksionalitete të ndryshme sipas përgjegjësive të tij.

Projekti është ndërtuar mbi bazën e **PHP 8** për backend, **MySQL** për databazën relacionale dhe **HTML5, CSS3, JavaScript** për ndërfaqen vizuale. Dizajni është modern, dark-themed dhe plotësisht responsive.

---

## 🚀 Funksionalitetet Kryesore

### 👑 Admin
- Shton, edito dhe fshin anëtarë të rinj në sistem
- Menaxhon trajnerët dhe u cakton klasa
- Shikon raportet financiare mujore dhe historikun e pagesave
- Menaxhon klasat e stërvitjes dhe kapacitetin e tyre
- Monitoron check-in-et dhe hyrjet në palestër
- Ka akses të plotë në të gjitha funksionalitetet e sistemit

### 👷 Staff
- Regjistron pagesat mujore të anëtarëve
- Bën check-in të anëtarëve kur hyjnë në palestër
- Kontrollon statusin e anëtarësimit gjatë hyrjes
- Shikon historikun e pagesave

### 🏃 Member
- Shikon profilin personal dhe statusin e anëtarësimit
- Rezervon vende në klasat e stërvitjes
- Shikon historikun e pagesave dhe hyrjeve
- Monitoron aktivitetin personal në palestër

---

## 🛠️ Teknologjitë e Përdorura

| Teknologjia | Versioni | Përdorimi |
|-------------|----------|-----------|
| **PHP** | 8.x | Backend, logjika e biznesit, session management |
| **MySQL** | 8.x | Databaza relacionale |
| **PDO** | — | Lidhja e sigurt me databazën |
| **HTML5** | — | Struktura e faqeve |
| **CSS3** | — | Stilizimi dhe dizajni |
| **JavaScript** | ES6+ | Interaktiviteti në browser |
| **XAMPP** | — | Serveri lokal për zhvillim |

---

## 🏗️ Arkitektura e Projektit

Projekti ndjek **Layered Architecture** me 4 shtresa të qarta:
```
┌─────────────────────────────────┐
│         Views (UI Layer)        │  ← HTML + CSS + JS + PHP
├─────────────────────────────────┤
│      Services (Logic Layer)     │  ← Logjika e biznesit
├─────────────────────────────────┤
│       Models (Data Layer)       │  ← Objektet e sistemit
├─────────────────────────────────┤
│     Data (Database Layer)       │  ← MySQL + PDO
└─────────────────────────────────┘
```

### Shtresat

**1. Models** — Klasat e modelit që reprezentojnë objektet e sistemit:
- `Member.php` — Anëtari i palestrës
- `Trainer.php` — Trajneri
- `Payment.php` — Pagesa mujore
- `GymClass.php` — Klasa e stërvitjes

**2. Services** — Logjika e biznesit për çdo entitet:
- `MemberService.php` — Menaxhimi i anëtarëve
- `TrainerService.php` — Menaxhimi i trajnerëve
- `PaymentService.php` — Menaxhimi i pagesave
- `ClassService.php` — Menaxhimi i klasave

**3. Data** — Lidhja me databazën:
- `Database.php` — Singleton Pattern për lidhjen me MySQL
- `DatabaseRepository.php` — Repository Pattern me CRUD operacione
- `IRepository.php` — Interface me kontratin e operacioneve

**4. Views** — Ndërfaqja vizuale e ndarë sipas roleve:
- `views/admin/` — Paneli i administratorit
- `views/staff/` — Paneli i stafit
- `views/member/` — Paneli i anëtarit

---

## 📁 Struktura e Projektit
```
gym-managment/
│
├── 📄 login.php                    ← Faqja e hyrjes
├── 📄 register.php                 ← Faqja e regjistrimit
├── 📄 logout.php                   ← Dalja nga sistemi
├── 📄 index.php                    ← Redirect te login
│
├── 📁 views/
│   ├── 📁 admin/
│   │   ├── dashboard.php           ← Paneli kryesor i adminit
│   │   ├── members.php             ← Menaxhimi i anëtarëve
│   │   ├── trainers.php            ← Menaxhimi i trajnerëve
│   │   ├── classes.php             ← Menaxhimi i klasave
│   │   ├── payments.php            ← Menaxhimi i pagesave
│   │   └── checkin.php             ← Check-in i anëtarëve
│   ├── 📁 staff/
│   │   ├── dashboard.php           ← Paneli kryesor i stafit
│   │   ├── checkin.php             ← Check-in i anëtarëve
│   │   └── payments.php            ← Regjistrimi i pagesave
│   └── 📁 member/
│       ├── dashboard.php           ← Profili personal
│       ├── classes.php             ← Shikimi dhe rezervimi i klasave
│       ├── history.php             ← Historiku i pagesave dhe hyrjeve
│       └── reserve.php             ← Procesimi i rezervimit
│
├── 📁 models/
│   ├── Member.php
│   ├── Trainer.php
│   ├── Payment.php
│   └── GymClass.php
│
├── 📁 services/
│   ├── MemberService.php
│   ├── TrainerService.php
│   ├── PaymentService.php
│   └── ClassService.php
│
├── 📁 data/
│   ├── Database.php
│   └── DatabaseRepository.php
│
├── 📁 docs/
│   ├── architecture.md             ← Dokumentimi i arkitekturës
│   └── class-diagram.png           ← Diagrami UML i klasave
│
├── 📄 style.css                    ← Stilizimi global
├── 📄 main.js                      ← JavaScript global
├── 📄 .gitignore
└── 📄 README.md
```

---

## 🗄️ Struktura e Databazës
```sql
users         → id, first_name, last_name, email, password, role
members       → id, user_id, phone, membership_status, membership_expiry
trainers      → id, user_id, specialization
classes       → id, name, time, capacity, enrolled, trainer_id, room, status
payments      → id, member_id, amount, payment_date, method, period, status
checkins      → id, member_id, checkin_time, status
```

**Relacionet:**
- `members.user_id` → `users.id`
- `trainers.user_id` → `users.id`
- `classes.trainer_id` → `trainers.id`
- `payments.member_id` → `members.id`
- `checkins.member_id` → `members.id`

---

## ▶️ Si ta Ekzekutosh

### Kërkesat
- XAMPP (Apache + MySQL)
- PHP 8.x
- Browser modern

### Hapat

**1. Klono projektin:**
```bash
git clone https://github.com/Albatriti/gym-managment.git
```

**2. Vendose në XAMPP:**
```
C:/xampp/htdocs/gym-managment/
```

**3. Starto XAMPP:**
- Hap XAMPP Control Panel
- Starto **Apache** dhe **MySQL**

**4. Krijo databazën:**
- Shko te `http://localhost/phpmyadmin`
- Krijo databazë të re: `gym_management_db`
- Ekzekuto SQL-in nga `docs/database.sql`

**5. Hap projektin:**
```
http://localhost/gym-managment/
```

**6. Hyr me:**
| Roli | Email | Fjalëkalimi |
|------|-------|-------------|
| Admin | admin@gym.com | password |
| Staff | staff@gym.com | password |
| Member | member@gym.com | password |

---

## 🔐 Design Patterns të Aplikuara

### Repository Pattern
```php
interface IRepository {
    public function getAll(): array;
    public function getById(int $id): mixed;
    public function add(array $data): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
```

### Singleton Pattern
```php
$db = Database::getInstance()->getConnection();
```

---

## 📌 Parimet SOLID të Aplikuara

| Parimi | Shpjegimi | Aplikimi |
|--------|-----------|----------|
| **S** — Single Responsibility | Çdo klasë ka një përgjegjësi | Member ruan të dhëna, MemberService menaxhon logjikën |
| **O** — Open/Closed | Hapur për zgjerim, mbyllur për modifikim | Services mund të zgjerohen pa ndryshuar modelet |
| **D** — Dependency Inversion | Varen nga abstraktet | Services varen nga IRepository, jo DatabaseRepository |

---

## 👥 Rolet dhe Aksesi

| Funksionaliteti | Admin | Staff | Member |
|-----------------|:-----:|:-----:|:------:|
| Shto/Fshi anëtarë | ✅ | ❌ | ❌ |
| Shto/Fshi trajnerë | ✅ | ❌ | ❌ |
| Menaxho klasat | ✅ | ❌ | ❌ |
| Regjistro pagesa | ✅ | ✅ | ❌ |
| Check-in | ✅ | ✅ | ❌ |
| Shiko klasat | ✅ | ✅ | ✅ |
| Rezervo klasë | ❌ | ❌ | ✅ |
| Historiku personal | ❌ | ❌ | ✅ |
| Raporte financiare | ✅ | ❌ | ❌ |

---

## 📊 User Stories (MoSCoW)

| # | User Story | Roli | MoSCoW |
|---|-----------|------|--------|
| 1 | Shtimi i anëtarëve të rinj | Admin | 🔴 Must Have |
| 2 | Regjistrimi i pagesave mujore | Staff | 🔴 Must Have |
| 3 | Shikimi i statusit të anëtarësimit | Member | 🔴 Must Have |
| 4 | Shtimi i trajnerëve dhe caktimi i klasave | Admin | 🟠 Should Have |
| 5 | Rezervimi i klasave | Member | 🔴 Must Have |
| 6 | Raportet financiare mujore | Admin | 🟠 Should Have |
| 7 | Check-in i anëtarëve | Staff | 🔴 Must Have |
| 8 | Historiku i pagesave dhe klasave | Member | 🟠 Should Have |
| 9 | Fshirja e anëtarëve dhe trajnerëve | Admin | 🟠 Should Have |
| 10 | Njoftimet për pagesa dhe rezervime | Member | 🟡 Could Have |

---

## 👨‍💻 Autori

**Albatrit Alushaj** — Universiteti i Mitrovicës "Isa Boletini"
Fakulteti i Shkencave Kompjuterike · Inxhinieri Softuerike · Semestri Veror 2026

---

*Projekt Semestral — Inxhinieri Softuerike Java 3 · Asistent: Agon Bajgora*