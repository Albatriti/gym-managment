# Demo Plan — GymFlow Management System
Autori: Albatrit lushaj
Data: 5 Maj 2026

---

## 1. Titulli i Projektit
**GymFlow** — Sistem i Menaxhimit të Palestrës

---

## 2. Problemi që Zgjidh

Palestrat tradicionale menaxhojnë anëtarët, pagesat dhe oraret me fletore ose Excel — një metodë e ngadaltë, e paorganizuar dhe e prirur ndaj gabimeve. GymFlow zgjidh këtë problem duke ofruar një sistem të centralizuar dixhital ku:

- Admini menaxhon anëtarët, trajnerët, klasat dhe pagesat nga një panel i vetëm
- Stafi regjistron hyrjet dhe pagesat shpejt dhe saktë
- Anëtarët shohin statusin e tyre dhe rezervojnë klasa online

---

## 3. Përdoruesit Kryesorë

| Roli | Kush është? | Çfarë bën? |
|------|-------------|------------|
| **Admin** | Menaxheri i palestrës | Menaxhim i plotë i sistemit |
| **Staff** | Punonjësi i recepsionit | Check-in dhe pagesa |
| **Member** | Anëtari i palestrës | Rezervim klasash dhe historiku |

---

## 4. Flow-i që do ta Demonstroj

### Flow kryesor: Login → Dashboard → CRUD → Check-in

**Hapat e demonstrimit:**

1. **Login si Admin** — hyr me kredencialet e adminit
2. **Dashboard** — shfaq statistikat live (anëtarë aktivë, pagesa, klasa)
3. **Shto anëtar të ri** — plotëso formën, anëtari shtohet me fjalëkalim automatik
4. **Shto klasë** — shto klasë të re me trajner dhe kapacitet
5. **Logout → Login si Member** — kyçu si anëtari i ri
6. **Rezervo klasë** — anëtari rezervon vendin në klasë
7. **Logout → Login si Staff** — kyçu si staff
8. **Check-in** — regjistro hyrjen e anëtarit në palestër

### Pse zgjodha këtë flow?
Ky flow tregon të tre rolet në veprim dhe demonstron komunikimin e plotë të sistemit nga krijimi i anëtarit deri te hyrja fizike në palestër — është flow-i më i plotë dhe më i kuptueshëm për audiencën.

---

## 5. Një Problem Real që e kam Zgjidhur

### Problemi:
Anëtarët mund të rezervonin të njëjtën klasë shumë herë — nuk kishte asnjë kufizim. Kjo shkaktonte numra të gabuar të vendeve të rezervuara dhe konfuzion në sistem.

### Ku ishte problemi:
Në skedarin `views/member/reserve.php` — nuk bëhej asnjë kontroll nëse anëtari e kishte rezervuar tashmë atë klasë.

### Si e zgjidha:
- Krijova tabelën `reservations` në MySQL me `UNIQUE KEY (member_id, class_id)` — kjo në nivel databaze bllokon duplikatin
- Shtova kontroll në PHP para çdo rezervimi:
```php
$stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE member_id = :mid AND class_id = :cid");
$stmt->execute([':mid' => $memberId, ':cid' => $classId]);
if ($stmt->fetchColumn() > 0) {
    header('Location: classes.php?error=already_reserved');
    exit;
}
```
- Shtova mesazhe të qarta për userin: "E ke rezervuar tashmë këtë klasë!"

---

## 6. Çka Mbetet Ende e Dobët

1. **Paginimi mungon** — nëse databaza ka shumë anëtarë, lista ngadalësohet
2. **Dy sisteme paralele** — CSV (MemberRepository) dhe MySQL nuk sinkronizohen me njëra-tjetrën
3. **Session timeout nuk ekziston** — useri mund të mbajë session aktiv pafundësisht
4. **Sidebar i duplikuar** — kodi i sidebar-it është i shkruar manualisht në shumë skedarë në vend të një komponenti të vetëm të ripërdorshëm

---

## 7. Struktura e Prezantimit (5–7 minuta)

### Hyrja (1 minutë)
- Prezantohem dhe prezantoj projektin
- Shpjegoj shkurt çfarë problemi zgjidh GymFlow
- Tregoj tre rolet: Admin, Staff, Member

### Demo Live (3–4 minuta)
- **[1 min]** Login si Admin → Dashboard me statistika live
- **[1 min]** Shto anëtar të ri + shto klasë të re
- **[1 min]** Login si Member → rezervo klasë
- **[30 sek]** Login si Staff → check-in i anëtarit

### Shpjegimi Teknik (1 minutë)
- Arkitektura e shtresuar: Models → Services → Data → Views
- Repository Pattern + Singleton Pattern
- Tri rolet me session management

### Problemi + Zgjidhja (30 sekonda)
- Tregoj problemin e rezervimit të duplikuar
- Shpjegoj zgjidhjen me tabelën reservations dhe UNIQUE KEY

### Mbyllja (30 sekonda)
- Çka funksionon: CRUD i plotë, 3 role, rezervime, check-in, fjalëkalim
- Çka mbetet: paginim, session timeout, sinkronizim CSV/MySQL
- Falënderim

---

## 8. Plan B — Nëse Diçka Nuk Funksionon Live

| Problemi | Plan B |
|----------|--------|
| XAMPP nuk starton | Screenshot të faqeve kryesore të gatshme |
| Databaza nuk lidhet | Demo me të dhëna të parapopulluara nga screenshots |
| Login nuk funksionon | README me instruksione dhe output të gatshëm |
| Rezervimi dështon | Shfaq kodin direkt në VS Code dhe shpjego logjikën |

**Skedarët e gatshëm për Plan B:**
- Screenshots të të gjitha faqeve kryesore
- `docs/architecture.md` për shpjegim teknik
- `docs/demo-plan.md` si udhëzues prezantimi
- README me flow të plotë të sistemit

---

## 9. Kredencialet për Demo

| Roli | Email | Fjalëkalimi |
|------|-------|-------------|
| Admin | *(albatritalushaj10@gmail.com)* | *(Albi2006)* |
| Staff | *(email-i i staff)* | Gym@12345 |
| Member | *(email-i i member)* | Gym@12345 |

> **Shënim:** Krijo llogaritë para demonstrimit dhe verifiko që funksionojnë!