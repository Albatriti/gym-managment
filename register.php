<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'data/Database.php';
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $_POST['email']]);
    if ($stmt->fetch()) {
        $error = "Ky email është already i regjistruar!";
    } else {
    $passError = '';
    if (strlen($_POST['password']) < 8) {
        $passError = 'Fjalëkalimi duhet të ketë minimum 8 karaktere!';
    } elseif (!preg_match('/[A-Z]/', $_POST['password'])) {
        $passError = 'Fjalëkalimi duhet të ketë të paktën 1 shkronjë të madhe!';
    } elseif (!preg_match('/[0-9]/', $_POST['password'])) {
        $passError = 'Fjalëkalimi duhet të ketë të paktën 1 numër!';
    }

    if (!empty($passError)) {
        $error = $passError;
    } else {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (:fn, :ln, :email, :password, :role)");
        $stmt->execute([
            ':fn'       => $_POST['first_name'],
            ':ln'       => $_POST['last_name'],
            ':email'    => $_POST['email'],
            ':password' => $hashed,
            ':role'     => $_POST['role']
        ]);
        $userId = $db->lastInsertId();

        if ($_POST['role'] === 'member') {
            $stmt = $db->prepare("INSERT INTO members (user_id, phone, membership_status, membership_expiry) VALUES (:uid, :phone, 'active', :expiry)");
            $stmt->execute([':uid' => $userId, ':phone' => $_POST['phone'], ':expiry' => date('Y-m-d', strtotime('+1 month'))]);
        } elseif ($_POST['role'] === 'trainer') {
            $stmt = $db->prepare("INSERT INTO trainers (user_id, specialization) VALUES (:uid, :spec)");
            $stmt->execute([':uid' => $userId, ':spec' => $_POST['specialization'] ?? '']);
        }

        $success = "Llogaria u krijua me sukses! Mund të hysh tani.";
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Regjistrohu — GymFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #E8FF47;
      --dark: #0a0a0a;
      --dark2: #111111;
      --dark3: #1a1a1a;
      --border: #2a2a2a;
      --text: #f0f0f0;
      --text-muted: #888;
      --danger: #ff4757;
      --success: #2ed573;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .register-wrapper {
      display: flex;
      width: 100%;
      max-width: 900px;
      min-height: 580px;
      border: 1px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
      margin: 20px;
    }
    .register-left {
      flex: 1;
      background: var(--primary);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px;
    }
    .register-left h1 {
      font-family: 'Bebas Neue', cursive;
      font-size: 3.5rem;
      color: var(--dark);
      letter-spacing: 4px;
      line-height: 1;
    }
    .register-left p {
      font-size: 0.85rem;
      color: var(--dark);
      margin-top: 8px;
      opacity: 0.7;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .register-left .logo-icon { font-size: 3rem; margin-bottom: 12px; }
    .register-right {
      flex: 1;
      background: var(--dark2);
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 48px;
      overflow-y: auto;
    }
    .register-right h2 {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.8rem;
      letter-spacing: 2px;
      margin-bottom: 8px;
    }
    .register-right p.subtitle {
      font-size: 0.82rem;
      color: var(--text-muted);
      margin-bottom: 28px;
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 16px; }
    .form-label {
      display: block;
      font-size: 0.72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-muted);
      margin-bottom: 6px;
    }
    .form-control {
      width: 100%;
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 11px 14px;
      color: var(--text);
      font-size: 0.88rem;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-control:focus { border-color: var(--primary); }
    .form-control option { background: var(--dark3); }
    .extra-field { display: none; }
    .btn-register {
      width: 100%;
      background: var(--primary);
      color: var(--dark);
      border: none;
      padding: 13px;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      letter-spacing: 1px;
      transition: all 0.2s;
      margin-top: 8px;
    }
    .btn-register:hover { background: #c8df20; }
    .error {
      background: rgba(255,71,87,0.1);
      border: 1px solid rgba(255,71,87,0.3);
      color: var(--danger);
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.82rem;
      margin-bottom: 16px;
    }
    .success {
      background: rgba(46,213,115,0.1);
      border: 1px solid rgba(46,213,115,0.3);
      color: var(--success);
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.82rem;
      margin-bottom: 16px;
    }
    .login-link {
      text-align: center;
      margin-top: 16px;
      font-size: 0.82rem;
      color: var(--text-muted);
    }
    .login-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: all 0.2s;
    }
    .login-link a:hover {
      color: var(--dark);
      background: var(--primary);
      padding: 2px 8px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
<div class="register-wrapper">
  <div class="register-left">
    <div class="logo-icon">🏟️</div>
    <h1>GYMFLOW</h1>
    <p>Management System</p>
  </div>
  <div class="register-right">
    <h2>Krijo Llogari</h2>
    <p class="subtitle">Plotëso të dhënat për t'u regjistruar</p>

    <?php if (isset($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Emri</label>
          <input class="form-control" type="text" name="first_name" placeholder="Emri" required />
        </div>
        <div class="form-group">
          <label class="form-label">Mbiemri</label>
          <input class="form-control" type="text" name="last_name" placeholder="Mbiemri" required />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" placeholder="emri@email.com" required />
      </div>
      <div class="form-group">
        <label class="form-label">Fjalëkalimi</label>
        <input class="form-control" type="password" name="password" placeholder="••••••••" required />
        <div id="pass-msg" style="font-size:0.78rem;margin-top:6px;min-height:18px;"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Roli</label>
        <select class="form-control" name="role" id="roleSelect" onchange="toggleFields(this.value)">
          <option value="member">Member</option>
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="form-group extra-field" id="phoneField">
        <label class="form-label">Telefoni</label>
        <input class="form-control" type="text" name="phone" placeholder="+383 44 000 000" />
      </div>
      <button class="btn-register" type="submit">REGJISTROHU</button>
    </form>

    <div class="login-link">
      Ke llogari? <a href="login.php">Hyr këtu</a>
    </div>
  </div>
</div>
<script>
  function toggleFields(role) {
    document.getElementById('phoneField').style.display = role === 'member' ? 'block' : 'none';
  }
  toggleFields('member');
</script>
<script>
function validateForm() {
    const password = document.querySelector('input[name="password"]').value;
    const msgEl   = document.getElementById('pass-msg');

    if (password.length < 8) {
        msgEl.textContent = '❌ Fjalëkalimi duhet të ketë minimum 8 karaktere!';
        msgEl.style.color = 'var(--danger)';
        return false;
    }
    if (!/[A-Z]/.test(password)) {
        msgEl.textContent = '❌ Fjalëkalimi duhet të ketë të paktën 1 shkronjë të madhe!';
        msgEl.style.color = 'var(--danger)';
        return false;
    }
    if (!/[0-9]/.test(password)) {
        msgEl.textContent = '❌ Fjalëkalimi duhet të ketë të paktën 1 numër!';
        msgEl.style.color = 'var(--danger)';
        return false;
    }
    return true;
}
</script>
</body>
</html>