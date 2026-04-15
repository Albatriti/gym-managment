# 📋 Project Audit — GymFlow Management System
Data: 15 Prill 2026
Autori: Albatriti

---

## 1. Përshkrimi i Shkurtër i Projektit

### Çka bën sistemi?
GymFlow është një aplikacion web për menaxhimin e plotë të një palestrë. Sistemi mundëson regjistrimin dhe menaxhimin e anëtarëve, trajnerëve, klasave të stërvitjes, pagesave mujore dhe hyrjeve (check-in) në palestër.

### Kush janë përdoruesit kryesorë?
- **Admin** — menaxheri i palestrës që ka akses të plotë
- **Staff** — punonjësit që regjistrojnë pagesa dhe check-in
- **Member** — anëtarët e palestrës që shohin profilin dhe rezervojnë klasa

### Funksionaliteti kryesor:
- Login / Register me role të ndryshme
- CRUD i plotë për anëtarë, trajnerë, klasa dhe pagesa
- Check-in system për hyrjet në palestër
- FileRepository me CSV për operacione CRUD
- Unit teste për MemberService

---

## 2. Çka Funksionon Mirë

1. **Arkitektura e shtresuar** — ndarja e qartë në Models, Services, Data dhe Views funksionon mirë dhe e bën kodin të organizuar

2. **Sistemi i roleve** — ndarja Admin/Staff/Member me session management funksionon saktë — çdo rol sheh vetëm atë që i takon

3. **Repository Pattern** — IRepository interface + DatabaseRepository + MemberRepository (CSV) japin fleksibilitet të mirë dhe mundësojnë ndryshimin e databazës pa ndikuar logjikën

4. **Validimi i inputit** — MemberService ka validim të mirë me mesazhe të qarta për userin

5. **Dizajni vizual** — dark theme profesional, responsive dhe i qartë për t'u naviguar

---

## 3. Dobësitë e Projektit

### Dobësia 1 — Duplikim i madh i kodit të sidebar-it
Çdo faqe PHP ka kodin e sidebar-it të shkruar brenda `<script>` tag. Nëse ndryshon një link, duhet ndryshuar në 10+ skedarë. Kjo shkel parimin DRY (Don't Repeat Yourself).

### Dobësia 2 — Fjalëkalimet nuk kontrollohen për kompleksitet
Gjatë regjistrimit, useri mund të vendosë fjalëkalim "1" ose "a" — nuk ka kontroll të gjatësisë minimale apo kompleksitetit. Kjo është rrezik sigurie.

### Dobësia 3 — SQL Injection mbrojtje e paplotë
Disa queries në faqet e views përdorin PDO me prepared statements, por nuk ka kontroll sistematik. Nëse zhvilluesi shton query të re pa prepared statement, sistemi është i cenueshëm.

### Dobësia 4 — Mungesa e paginimit
Nëse databaza ka 1000 anëtarë, faqja i lexon të gjithë dhe i shfaq njëherësh. Kjo ngadalëson sistemin dhe krijon probleme performance.

### Dobësia 5 — Session timeout nuk ekziston
Useri mund të mbajë session aktiv pafundësisht pa u çloguar. Nëse lë kompjuterin hapur, dikush tjetër mund të hyjë në sistem pa fjalëkalim.

### Dobësia 6 — MemberRepository dhe DatabaseRepository janë të pakoordinuara
Projekti ka dy sisteme paralele — CSV (MemberRepository) dhe MySQL (DatabaseRepository) — që nuk sinkronizohen. Anëtari i shtuar në CSV nuk shfaqet në MySQL dhe anasjelltas.

### Dobësia 7 — Mungojnë mesazhet e suksesit pas redirect
Pas shtimit/fshirjes/editimit të anëtarit, faqja ridrejtohet por useri nuk sheh asnjë konfirmim. Flash messages mungojnë.

---

## 4. Tre Përmirësimet që do t'i Implementoj

### Përmirësimi 1 — Validim i fjalëkalimit gjatë regjistrimit

**Problemi:** Useri mund të regjistrohet me fjalëkalim "1" — nuk ka kontroll të gjatësisë apo kompleksitetit. Kjo është dobësi e madhe sigurie.

**Zgjidhja:** Shtohet validim në `register.php` që kërkon: minimum 8 karaktere, të paktën 1 numër dhe 1 shkronjë të madhe. Mesazh i qartë shfaqet nëse fjalëkalimi nuk plotëson kushtet.

**Pse ka rëndësi:** Fjalëkalimet e dobëta janë shkaku kryesor i hackimit të llogarive. Një sistem menaxhimi me të dhëna personale dhe financiare duhet të ketë mbrojtje minimale të fjalëkalimeve.

---

### Përmirësimi 2 — Flash Messages pas operacioneve CRUD

**Problemi:** Pas shtimit, editimit ose fshirjes së anëtarit, faqja ridrejtohet me `header('Location: members.php')` por useri nuk sheh asnjë konfirmim nëse operacioni u krye me sukses. Kjo krijon konfuzion.

**Zgjidhja:** Implementohet sistem flash messages duke përdorur `$_SESSION`. Para redirect-it ruhet mesazhi në session, dhe në faqen tjetër mesazhi lexohet, shfaqet dhe fshihet menjëherë.

**Pse ka rëndësi:** UX i mirë kërkon feedback të qartë. Useri duhet të dijë gjithmonë nëse operacioni i tij u krye me sukses apo dështoi.

---

### Përmirësimi 3 — Përmirësim i README dhe dokumentimit

**Problemi:** README aktual nuk ka instruksione të qarta setup, nuk shpjegon si të krijohet databaza hap pas hapi dhe nuk ka seksion troubleshooting. Dikush që klonon projektin nuk di si ta startojë.

**Zgjidhja:** README plotësohet me: instruksione setup hap pas hapi, SQL script për krijimin e databazës, seksion troubleshooting për gabimet e zakonshme dhe screenshot të ndërfaqes.

**Pse ka rëndësi:** Dokumentimi i mirë është pjesë e pandashme e softuerit profesional. Kodi pa dokumentim të mirë është i pavlefshëm për të tjerët.

---

## 5. Një Pjesë që Ende nuk e Kuptoj Plotësisht

Pjesa që e kam më të paqartë është **sinkronizimi midis dy sistemeve të të dhënave** — CSV (MemberRepository) dhe MySQL (DatabaseRepository). Projekti aktualisht i përdor të dyja paralelisht pa sinkronizim. Nuk jam i sigurt si të menaxhoj situatën kur të dhënat duhet të jenë konzistente ndërmjet dy burimeve. Në projekte reale kjo zgjidhet me një pattern të vetëm databaze, por për qëllimet e kësaj lënde ku detyra kërkon CSV, nuk e kam të qartë se cila është mënyra më e mirë për t'i koordinuar.