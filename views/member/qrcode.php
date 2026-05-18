<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: /gym-managment/login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Merr të dhënat e member-it
$stmt = $db->prepare("
    SELECT u.first_name, u.last_name, u.email,
           m.id as member_id, m.membership_status,
           m.membership_expiry, m.qr_token
    FROM users u
    JOIN members m ON u.id = m.user_id
    WHERE u.id = :uid
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Nëse nuk ka qr_token, gjenero një të ri
if (!$member['qr_token']) {
    $token = hash('sha256', $member['member_id'] . '-apex-gym-' . time());
    $stmt = $db->prepare("UPDATE members SET qr_token = :token WHERE id = :id");
    $stmt->execute([':token' => $token, ':id' => $member['member_id']]);
    $member['qr_token'] = $token;
}

// Kontrollo pagesën e fundit
$stmt = $db->prepare("
    SELECT * FROM payments
    WHERE member_id = :mid
    AND status = 'paid'
    ORDER BY payment_date DESC
    LIMIT 1
");
$stmt->execute([':mid' => $member['member_id']]);
$lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);

// QR Code URL — përdor API falas
$scanUrl = "http://localhost/gym-managment/views/staff/scan.php?token=" . $member['qr_token'];
$qrUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($scanUrl);

$isValid = $member['membership_status'] === 'active' && $lastPayment;
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Code — GymFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
  <style>
    .qr-wrapper {
      max-width: 420px;
      margin: 0 auto;
    }
    .qr-card {
      background: var(--dark2);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .qr-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .qr-body {
      padding: 32px;
      text-align: center;
    }
    .qr-image-wrap {
      display: inline-block;
      padding: 16px;
      background: white;
      border-radius: 12px;
      margin-bottom: 20px;
      position: relative;
    }
    .qr-image-wrap img {
      display: block;
      width: 220px;
      height: 220px;
    }
    .qr-invalid-overlay {
      position: absolute;
      inset: 0;
      background: rgba(255,71,87,0.85);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .qr-invalid-overlay .icon { font-size: 2.5rem; }
    .qr-invalid-overlay .text {
      font-size: 0.82rem;
      font-weight: 700;
      color: white;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .member-name {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.4rem;
      letter-spacing: 2px;
      margin-bottom: 4px;
    }
    .member-id {
      font-size: 0.72rem;
      color: var(--text-muted);
      letter-spacing: 2px;
      text-transform: uppercase;
      font-family: monospace;
      margin-bottom: 20px;
    }
    .validity-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 1px;
    }
    .valid-badge {
      background: rgba(46,213,115,0.12);
      border: 1px solid rgba(46,213,115,0.3);
      color: var(--success);
    }
    .invalid-badge {
      background: rgba(255,71,87,0.12);
      border: 1px solid rgba(255,71,87,0.3);
      color: var(--danger);
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
      font-size: 0.85rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--text-muted); font-weight: 300; }
    .info-value { color: var(--text); font-weight: 500; }
  </style>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">QR Code Personal</span>
    <div class="topbar-actions">
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="qr-wrapper">

      <!-- QR Card -->
      <div class="qr-card">
        <div class="qr-header">
          <div>
            <div style="font-family:'Bebas Neue',cursive;font-size:1rem;letter-spacing:2px;">APEX GYM</div>
            <div style="font-size:0.7rem;color:var(--text-muted);letter-spacing:1px;">Mitrovicë, Kosovë</div>
          </div>
          <div style="font-size:1.5rem;">🏟️</div>
        </div>

        <div class="qr-body">
          <!-- QR Code Image -->
          <div class="qr-image-wrap">
            <img src="<?php echo $qrUrl; ?>" alt="QR Code" id="qrImage"/>
            <?php if (!$isValid): ?>
            <div class="qr-invalid-overlay">
              <div class="icon">🔒</div>
              <div class="text">Jo Valid</div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Member Info -->
          <div class="member-name">
            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
          </div>
          <div class="member-id">
            ID: <?php echo str_pad($member['member_id'], 6, '0', STR_PAD_LEFT); ?>
          </div>

          <!-- Validity Badge -->
          <?php if ($isValid): ?>
          <span class="validity-badge valid-badge">
            ✅ QR Valid — Pagesa e Kryer
          </span>
          <?php else: ?>
          <span class="validity-badge invalid-badge">
            ❌ QR Jo Valid — Pagesa Mungon
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Info Card -->
      <div class="qr-card">
        <div class="qr-body" style="padding:24px;">
          <div class="info-row">
            <span class="info-label">Statusi</span>
            <span class="info-value">
              <span class="badge-status <?php echo $member['membership_status']==='active' ? 'status-active' : 'status-expired'; ?>">
                <?php echo $member['membership_status']==='active' ? 'Aktiv' : 'Skaduar'; ?>
              </span>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Anëtarësia deri</span>
            <span class="info-value"><?php echo $member['membership_expiry'] ?? '—'; ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Pagesa e fundit</span>
            <span class="info-value">
              <?php echo $lastPayment ? $lastPayment['payment_date'] . ' — ' . $lastPayment['amount'] . '€' : '—'; ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">QR Valid</span>
            <span class="info-value" style="color:<?php echo $isValid ? 'var(--success)' : 'var(--danger)'; ?>">
              <?php echo $isValid ? 'Po ✅' : 'Jo ❌'; ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Info message -->
      <?php if (!$isValid): ?>
      <div style="background:rgba(255,71,87,0.08);border:1px solid rgba(255,71,87,0.2);border-radius:10px;padding:16px;font-size:0.82rem;color:var(--danger);text-align:center;line-height:1.6;">
        ⚠️ QR Code juaj nuk është valid. Pagesa mujore nuk është kryer. Kontaktoni stafin për të paguar dhe aktivizuar QR Code.
      </div>
      <?php else: ?>
      <div style="background:rgba(46,213,115,0.06);border:1px solid rgba(46,213,115,0.15);border-radius:10px;padding:16px;font-size:0.82rem;color:var(--text-muted);text-align:center;line-height:1.6;">
        💡 Tregojeni ose skanojeni këtë QR Code në recepsion për check-in të shpejtë.
      </div>
      <?php endif; ?>

    </div>

  </main>
</div>

<script src="/gym-managment/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const links = [
      { href: '/gym-managment/views/member/dashboard.php',   icon: '📊', label: 'Dashboard',   id: 'dashboard'      },
      { href: '/gym-managment/views/member/classes.php',     icon: '📅', label: 'Klasat',      id: 'classes'        },
      { href: '/gym-managment/views/member/history.php',     icon: '📋', label: 'Historiku',   id: 'history'        },
      { href: '/gym-managment/views/member/qrcode.php',      icon: '📱', label: 'QR Code',     id: 'qrcode'         },
      { href: '/gym-managment/change-password.php',          icon: '🔒', label: 'Fjalëkalimi', id: 'changepassword' },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link ${l.id === 'qrcode' ? 'active' : ''}">
        <span class="nav-icon">${l.icon}</span> ${l.label}
      </a>`).join('');
    document.getElementById('sidebar').innerHTML = `
      <div class="sidebar-logo">
        <div class="logo-icon">🏟️</div>
        <h1>APEX GYM</h1>
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
</script>
</body>
</html>