# 📈 Improvement Report — GymFlow Management System
Data: 15 Prill 2026
Autori: Albatriti

---

## Përmirësimi 1 — Validim i Fjalëkalimit

### Çka ishte problem:
Formulari i regjistrimit pranonte çdo fjalëkalim pa asnjë kontroll — edhe fjalëkalimin "1". Kjo ishte dobësi e madhe sigurie për një sistem që ruan të dhëna personale dhe financiare.

### Çfarë ndryshova:
U shtua validim i fjalëkalimit në `register.php` në dy nivele:

**Frontend (JavaScript)** — kontrollon në kohë reale para submit:
```javascript
function validatePassword(password) {
    if (password.length < 8)
        return 'Fjalëkalimi duhet të ketë minimum 8 karaktere!';
    if (!/[A-Z]/.test(password))
        return 'Fjalëkalimi duhet të ketë të paktën 1 shkronjë të madhe!';
    if (!/[0-9]/.test(password))
        return 'Fjalëkalimi duhet të ketë të paktën 1 numër!';
    return null;
}
```

**Backend (PHP)** — validim i dytë për siguri:
```php
if (strlen($_POST['password']) < 8) {
    $error = 'Fjalëkalimi duhet të ketë minimum 8 karaktere!';
}
if (!preg_match('/[A-Z]/', $_POST['password'])) {
    $error = 'Fjalëkalimi duhet të ketë të paktën 1 shkronjë të madhe!';
}
if (!preg_match('/[0-9]/', $_POST['password'])) {
    $error = 'Fjalëkalimi duhet të ketë të paktën 1 numër!';
}
```

### Pse versioni i ri është më i mirë:
Validimi në dy nivele (frontend + backend) siguron që asnjë fjalëkalim i dobët nuk mund të kalojë — as nëse JavaScript është i çaktivizuar. Useri merr feedback të menjëhershëm pa pasur nevojë të presë submit-in.

---

## Përmirësimi 2 — Flash Messages

### Çka ishte problem:
Pas çdo operacioni CRUD (shto/edito/fshi), faqja ridrejtohej pa dhënë asnjë feedback. Useri nuk dinte nëse operacioni u krye me sukses apo dështoi — kjo ishte UX i dobët.

### Çfarë ndryshova:
U implementua sistem flash messages duke përdorur `$_SESSION`:

**Para redirect (p.sh. pas shtimit):**
```php
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Anëtari u shtua me sukses!'];
header('Location: members.php');
exit;
```

**Në faqen që merr redirect:**
```php
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
```

**Shfaqja në HTML:**
```php
<?php if (isset($flash)): ?>
<div class="flash-<?php echo $flash['type']; ?>">
    <?php echo $flash['message']; ?>
</div>
<?php endif; ?>
```

### Pse versioni i ri është më i mirë:
Useri tani merr konfirmim të qartë pas çdo veprimi. Flash message shfaqet vetëm një herë dhe fshihet automatikisht — nuk persiston nëse faqja rifreskohej.

---

## Përmirësimi 3 — Dokumentimi i Përmirësuar

### Çka ishte problem:
README-ja ekzistuese kishte instruksione të pakompletuara për setup. Dikush që klononte projektin nga GitHub nuk dinte si të krijonte databazën, cilat kredenciale të vendoste apo si të zgjidhte gabimet e zakonshme.

### Çfarë ndryshova:
U shtuan në README:

**Seksioni Troubleshooting:**
```markdown
## 🔧 Troubleshooting

### "Connection refused" nga MySQL
→ Sigurohu që MySQL është startuar në XAMPP Control Panel

### "Table doesn't exist"  
→ Ekzekuto SQL scriptin në phpMyAdmin

### Faqja shfaq kod PHP
→ Sigurohu që Apache është startuar dhe po hap nga localhost, jo file://
```

**SQL Script i plotë** për krijimin e databazës dhe të gjitha tabelave me një klikim.

**Seksioni Quick Start** me 5 hapa të qartë për setup nga zero.

### Pse versioni i ri është më i mirë:
Dokumentimi i mirë e bën projektin të aksesushëm për këdo. Një README i qartë kursen kohë dhe krijon impresion profesional.

---

## Çka Mbetet Ende e Dobët

1. **Paginimi mungon** — nëse databaza rritet shumë, faqet do të ngadalësohen
2. **Session timeout nuk ekziston** — useri mund të mbajë session aktiv pafundësisht
3. **Dy sistemet e të dhënave (CSV + MySQL) nuk sinkronizohen** — kjo është dobësia kryesore arkitekturore e projektit
4. **Mungojnë teste për UI** — testet ekzistojnë vetëm për Service layer