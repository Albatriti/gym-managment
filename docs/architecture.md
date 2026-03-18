# 🏗️ Arkitektura e Gym Management System

## 📁 Struktura e Plotë e Projektit
```
gym-managment/
│
├── models/                  → Shtresa 1: Modelet
│   ├── Member.php
│   ├── Trainer.php
│   ├── Payment.php
│   └── GymClass.php
│
├── services/                → Shtresa 2: Logjika e Biznesit
│   ├── MemberService.php
│   ├── TrainerService.php
│   ├── PaymentService.php
│   └── ClassService.php
│
├── data/                    → Shtresa 3: Databaza
│   ├── Database.php
│   └── DatabaseRepository.php
│
├── views/                   → Shtresa 4: Ndërfaqja
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── members.php
│   │   ├── trainers.php
│   │   ├── payments.php
│   │   └── classes.php
│   ├── staff/
│   │   ├── dashboard.php
│   │   ├── checkin.php
│   │   └── payments.php
│   └── member/
│       ├── dashboard.php
│       ├── classes.php
│       └── history.php
│
├── docs/                    → Dokumentimi
│   ├── architecture.md
│   └── class-diagram.md
│
├── index.php                → Pika e hyrjes (max 10 rreshta)
├── .gitignore
└── README.md
```

---

## 🔄 Si Komunikojnë Shtresat
```
[ views/ ]  →  [ services/ ]  →  [ models/ ]
                     ↓
               [ data/ ]
                     ↓
               [ MySQL DB ]
```

**Shpjegimi:**
1. **Views** kërkon të dhëna nga **Services**
2. **Services** aplikon logjikën e biznesit dhe përdor **Models**
3. **Services** komunikon me **Data** për të lexuar/shkruar në databazë
4. **Data** komunikon direkt me **MySQL**

---

## 📋 Shtresa 1 — Models (models/)

**Qëllimi:** Reprezenton objektet reale të sistemit

| Klasa | Përshkrimi |
|-------|------------|
| `Member.php` | Anëtari i palestrës — emri, emaili, statusi i anëtarësimit |
| `Trainer.php` | Trajneri — emri, specializimi, klasat e caktuara |
| `Payment.php` | Pagesa — shuma, data, metoda, statusi |
| `GymClass.php` | Klasa e stërvitjes — ora, kapaciteti, trajneri, salla |

**Rregullat:**
- Atributet janë gjithmonë `private`
- Qasja bëhet vetëm përmes `getters` dhe `setters`
- Nuk kanë lidhje direkte me databazën

---

## 📋 Shtresa 2 — Services (services/)

**Qëllimi:** Përmban të gjithë logjikën e biznesit

| Klasa | Përgjegjësia |
|-------|--------------|
| `MemberService.php` | Shton, fshin, filtron anëtarë aktiv/skaduar |
| `TrainerService.php` | Menaxhon trajnerët dhe klasat e tyre |
| `PaymentService.php` | Regjistron pagesa, llogarit totalin mujor |
| `ClassService.php` | Menaxhon klasat, rezervimet, kapacitetin |

**Rregullat:**
- Merr objekte nga Models
- Nuk shkruan HTML
- Komunikon me Data layer për databazën

---

## 📋 Shtresa 3 — Data (data/)

**Qëllimi:** Menaxhon lidhjen dhe operacionet me MySQL

| Klasa/Interface | Përshkrimi |
|-----------------|------------|
| `Database.php` | Singleton — krijon një lidhje të vetme me MySQL |
| `IRepository.php` | Interface me metodat: getAll(), getById(), add(), update(), delete() |
| `DatabaseRepository.php` | Implementon IRepository — ekzekuton queries SQL |

**Rregullat:**
- Vetëm kjo shtresë komunikon me databazën
- Përdor PDO për siguri kundër SQL Injection
- Singleton Pattern siguron një lidhje të vetme

---

## 📋 Shtresa 4 — Views (views/)

**Qëllimi:** Ndërfaqja vizuale e përdoruesit

**E ndarë sipas roleve:**

| Roli | Faqet | Aksesi |
|------|-------|--------|
| **Admin** | dashboard, members, trainers, payments, classes | I plotë |
| **Staff** | dashboard, checkin, payments | I kufizuar |
| **Member** | dashboard, classes, history | Vetëm profili |

**Rregullat:**
- HTML + CSS + JavaScript
- PHP vetëm për të shfaqur të dhëna nga Services
- Nuk ka logjikë biznesi

---

## 🔐 Repository Pattern
```php
interface IRepository {
    getAll(): array
    getById(int $id): mixed
    add(array $data): bool
    update(int $id, array $data): bool
    delete(int $id): bool
}

class DatabaseRepository implements IRepository {
    // Implementimi me MySQL + PDO
}
```

**Pse Repository Pattern?**
- Ndan logjikën e biznesit nga databaza
- Nëse ndryshon databaza (MySQL → PostgreSQL), ndryshohet vetëm `DatabaseRepository`
- Services nuk e dinë si ruhen të dhënat

---

## 🔒 Singleton Pattern — Database
```php
$db = Database::getInstance(); // Gjithmonë e njëjta lidhje
```

**Pse Singleton?**
- Vetëm një lidhje aktive me databazën
- Kursen resurse të serverit
- Qasje e lehtë nga çdo pjesë e kodit

---

## 👥 Rolet dhe Aksesi

|  Funksionaliteti   | Admin | Staff | Member |
|--------------------|-------|-------|--------|
| Shto/Fshi anëtarë  | ✅ | ❌ | ❌ |
| Regjistro pagesa   | ✅ | ✅ | ❌ |
| Check-in           | ✅ | ✅ | ❌ |
| Shiko klasat       | ✅ | ✅ | ✅ |
| Rezervo klasë      | ❌ | ❌ | ✅ |
| Raporte financiare | ✅ | ❌ | ❌ |
| Profili personal   | ❌ | ❌ | ✅ |

---

## ⚙️ Teknologjitë

|  Teknologjia   |  Përdorimi  |
|----------------|-----------|
| **PHP 8**      | Backend, logjika, session management |
| **MySQL**      | Databaza relacionale |
| **PDO**        | Lidhja e sigurt me databazën |
| **HTML5**      | Struktura e faqeve |
| **CSS3**       | Stilizimi dhe dizajni |
| **JavaScript** | Interaktiviteti në browser |

---

## 📌 Arsyet e Vendimeve Kryesore

**1. Pse PHP + MySQL?**
Kombinim i thjeshtë dhe i fuqishëm për aplikacione web universitare. XAMPP mundëson setup të shpejtë pa konfigurim të komplikuar.

**2. Pse shtresa të ndara?**
Çdo shtresë ka një përgjegjësi të vetme — parimi Single Responsibility (SOLID). Kodi është më i lexueshëm, më i mirëmbajtur dhe më i lehtë për t'u testuar.

**3. Pse PDO dhe jo mysqli?**
PDO mbështet shumë databaza dhe ofron prepared statements për siguri kundër SQL Injection.

**4. Pse role të ndara në views/?**
Çdo rol ka nevojat e veta — ndarja në admin/, staff/, member/ e bën kodin të organizuar dhe siguron që secili sheh vetëm atë që i takon.