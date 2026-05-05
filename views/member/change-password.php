<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: /gym-managment/login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword     = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Merr fjalëkalimin aktual
    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($currentPassword, $user['password'])) {
        $flash = ['type' => 'danger', 'message' => 'Fjalëkalimi aktual është i gabuar!'];
    } elseif (strlen($newPassword) < 8) {
        $flash = ['type' => 'danger', 'message' => 'Fjalëkalimi i ri duhet të ketë minimum 8 karaktere!'];
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $flash = ['type' => 'danger', 'message' => 'Fjalëkalimi duhet të ketë të paktën 1 shkronjë të madhe!'];
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $flash = ['type' => 'danger', 'message' => 'Fjalëkalimi duhet të ketë të paktën 1 numër!'];
    } elseif ($newPassword !== $confirmPassword) {
        $flash = ['type' => 'danger', 'message' => 'Fjalëkalimet nuk përputhen!'];
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $hashed, ':id' => $_SESSION['user_id']]);
        $flash = ['type' => 'success', 'message' => 'Fjalëkalimi u ndryshua me sukses!'];
    }
}

// Merr të dhënat e member-it
$stmt = $db->prepare("SELECT u.first_name, u.last_name, m.id as member_id FROM users u JOIN members m ON u.id = m.user_id WHERE u.id = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ndrysho Fjalëkalimin — GymFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Ndrysho Fjalëkalimin</span>
    <div class="topbar-actions">
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div style="max-width:480px;margin:0 auto;">

      <?php if ($flash): ?>
      <div style="background:<?php echo $flash['type']==='success' ? 'rgba(46,213,115,0.1)' : 'rgba(255,71,87,0.1)'; ?>;
                  border:1px solid <?php echo $flash['type']==='success' ? 'rgba(46,213,115,0.3)' : 'rgba(255,71,87,0.3)'; ?>;
                  color:<?php echo $flash['type']==='success' ? 'var(--success)' : 'var(--danger)'; ?>;
                  padding:12px 18px;border-radius:8px;margin-bottom:20px;font-size:0.88rem;">
        <?php echo $flash['message']; ?>
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Ndrysho Fjalëkalimin</span>
        </div>
        <div class="card-body">

          <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;padding:14px;background:var(--dark3);border-radius:10px;">
            <div style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:var(--dark);display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',cursive;font-size:1rem;flex-shrink:0;">
              <?php echo strtoupper(substr($member['first_name'],0,1) . substr($member['last_name'] ?? '',0,1)); ?>
            </div>
            <div>
              <div style="font-weight:600;"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></div>
              <div style="font-size:0.78rem;color:var(--text-muted);">Member</div>
            </div>
          </div>

          <form method="POST" onsubmit="return validatePassForm()">
            <div class="form-group">
              <label class="form-label">Fjalëkalimi Aktual</label>
              <input class="form-control" type="password" name="current_password" placeholder="••••••••" required/>
            </div>
            <div class="form-group">
              <label class="form-label">Fjalëkalimi i Ri</label>
              <input class="form-control" type="password" name="new_password" id="newPass" placeholder="••••••••" required/>
              <div style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;">
                Minimum 8 karaktere, 1 shkronjë e madhe, 1 numër
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmo Fjalëkalimin e Ri</label>
              <input class="form-control" type="password" name="confirm_password" id="confirmPass" placeholder="••••••••" required/>
            </div>
            <div id="pass-error" style="font-size:0.78rem;color:var(--danger);margin-bottom:12px;min-height:18px;"></div>
            <button class="btn-primary-custom" type="submit" style="width:100%;justify-content:center;padding:12px;">
              🔒 Ndrysho Fjalëkalimin
            </button>
          </form>

          <div style="margin-top:16px;text-align:center;">
            <a href="/gym-managment/views/member/dashboard.php" style="font-size:0.82rem;color:var(--text-muted);text-decoration:none;">
              ← Kthehu te Dashboard
            </a>
          </div>

        </div>
      </div>

    </div>
  </main>
</div>

<script src="/gym-managment/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const links = [
      { href: '/gym-managment/views/member/dashboard.php',         icon: '📊', label: 'Dashboard',        id: 'dashboard'       },
      { href: '/gym-managment/views/member/classes.php',           icon: '📅', label: 'Klasat',           id: 'classes'         },
      { href: '/gym-managment/views/member/history.php',           icon: '📋', label: 'Historiku',        id: 'history'         },
      { href: '/gym-managment/views/member/change-password.php',   icon: '🔒', label: 'Fjalëkalimi',      id: 'changepassword'  },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link ${l.id === 'changepassword' ? 'active' : ''}">
        <span class="nav-icon">${l.icon}</span> ${l.label}
      </a>`).join('');
    document.getElementById('sidebar').innerHTML = `
      <div class="sidebar-logo">
        <div class="logo-icon">🏟️</div>
        <h1>GYMFLOW</h1>
        <p>Member Panel</p>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Menuja</div>
        ${navItems}
      </nav>
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar">ME</div>
          <div class="user-info">
            <div class="user-name"><?php echo $_SESSION['firstName']; ?></div>
            <div class="user-role">Member</div>
          </div>
        </div>
      </div>`;
  });

  function validatePassForm() {
    const newPass     = document.getElementById('newPass').value;
    const confirmPass = document.getElementById('confirmPass').value;
    const errEl       = document.getElementById('pass-error');

    if (newPass.length < 8) {
      errEl.textContent = '❌ Minimum 8 karaktere!';
      return false;
    }
    if (!/[A-Z]/.test(newPass)) {
      errEl.textContent = '❌ Të paktën 1 shkronjë e madhe!';
      return false;
    }
    if (!/[0-9]/.test(newPass)) {
      errEl.textContent = '❌ Të paktën 1 numër!';
      return false;
    }
    if (newPass !== confirmPass) {
      errEl.textContent = '❌ Fjalëkalimet nuk përputhen!';
      return false;
    }
    errEl.textContent = '';
    return true;
  }
</script>
</body>
</html>