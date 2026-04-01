# Sprint 2 Plan — Albatriti
Data: 1 Prill 2026

## Gjendja Aktuale

### Çka funksionon tani:
- Login / Register / Logout me session management
- Panel i Admin me CRUD të plotë për anëtarë, trajnerë, klasat dhe pagesat
- Panel i Staff me Check-in dhe regjistrim pagesash
- Panel i Member me profil personal, rezervim klasash dhe historik
- FileRepository me CSV për operacione CRUD (Member)
- MemberService me Dependency Injection dhe validim
- Databaza MySQL e lidhur me PDO
- Arkitektura e shtresuar: Models, Services, Data, Views
- Repository Pattern + Singleton Pattern
- Diagrami UML i klasave
- Dokumentimi i arkitekturës (docs/architecture.md)

### Çka nuk funksionon:
- Nuk ka kërkim/filtrim të avancuar të anëtarëve
- Nuk ka statistika të detajuara financiare
- Error handling nuk është i plotë — programi mund të crashojë
- Nuk ka Unit Tests

### A kompajlohet dhe ekzekutohet programi?
Po — projekti ekzekutohet normalisht në XAMPP (localhost)

---

## Plani i Sprintit

### Feature e Re — Kërkim + Statistika
Do të implementohet një sistem kërkimi i avancuar dhe statistika:

**Kërkim i Anëtarëve:**
- Useri shkruan emrin në input → programi filtron listën në kohë reale
- Kërkim sipas emrit, mbiemrit ose email-it
- Filtrim sipas statusit të anëtarësimit (active/expired/pending)
- Rrjedha: UI → MemberService → MemberRepository → members.csv

**Statistika:**
- Totali i anëtarëve aktiv, skaduar dhe pending
- Totali i pagesave mujore
- Anëtari me pagesën më të lartë (max)
- Mesatarja e pagesave

### Error Handling — Pjesët që mund të crashojnë:

1. **File CSV mungon** — nëse `members.csv` nuk ekziston, programi tregon mesazh: "File nuk u gjet, po krijoj file të ri..." dhe krijon CSV të ri automatikisht

2. **Input i gabuar** — nëse useri shkruan email jo valid ose lë fushat bosh, shfaqet mesazh i qartë: "Ju lutem plotësoni të gjitha fushat" ose "Email nuk është valid" — programi nuk mbyllet

3. **ID nuk ekziston** — nëse useri kërkon anëtar me ID që nuk ekziston, shfaqet mesazh: "Anëtari nuk u gjet" — jo exception në ekran

### Teste — Çka do të testohet:
- `listo()` — kthehet lista e plotë e anëtarëve
- `shto()` me të dhëna valid — kthehet sukses
- `shto()` me emër bosh — kthehet error
- `shto()` me email jo valid — kthehet error
- `gjej()` me ID ekzistuese — gjendet anëtari
- `gjej()` me ID jo ekzistuese — kthehet null

---

## Afati
- Sprint Plan: 1 Prill 2026 (sot)
- Sprint Delivery: Martë, 8 Prill 2026, ora 08:30