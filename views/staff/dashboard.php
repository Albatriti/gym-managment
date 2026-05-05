<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: /gym-managment/login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$todayCheckins   = $db->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_time) = CURDATE()")->fetchColumn();
$pendingPayments = $db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$activeMembers   = $db->query("SELECT COUNT(*) FROM members WHERE membership_status = 'active'")->fetchColumn();
$totalClasses    = $db->query("SELECT COUNT(*) FROM classes WHERE status = 'active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — GymFlow Staff</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Dashboard</span>
    <div class="topbar-actions">
      <span style="font-size:0.8rem;color:var(--text-muted);">
        Mirë se vjen, <?php echo $_SESSION['firstName']; ?>!
      </span>
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="stat-grid">
      <div class="stat-card yellow">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo $todayCheckins; ?></div>
        <div class="stat-label">Check-In Sot</div>
        <span class="stat-badge badge-yellow">Sot</span>
      </div>
      <div class="stat-card red">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?php echo $pendingPayments; ?></div>
        <div class="stat-label">Pagesa Pending</div>
        <span class="stat-badge badge-red">Kërkon vëmendje</span>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?php echo $activeMembers; ?></div>
        <div class="stat-label">Anëtarë Aktivë</div>
        <span class="stat-badge badge-green">Aktiv</span>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo $totalClasses; ?></div>
        <div class="stat-label">Klasa Aktive</div>
        <span class="stat-badge badge-blue">Sot</span>
      </div>
    </div>

  </main>
</div>

<script src="/gym-managment/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const links = [
      { href: '/gym-managment/views/staff/dashboard.php', icon: '📊', label: 'Dashboard', id: 'dashboard' },
      { href: '/gym-managment/views/staff/checkin.php',   icon: '✅', label: 'Check-In',  id: 'checkin'   },
      { href: '/gym-managment/views/staff/payments.php',  icon: '💳', label: 'Pagesat',   id: 'payments'  },
      { href: '/gym-managment/change-password.php', icon: '🔒', label: 'Fjalëkalimi', id: 'changepassword' },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link ${l.id === 'dashboard' ? 'active' : ''}">
        <span class="nav-icon">${l.icon}</span> ${l.label}
      </a>`).join('');
    document.getElementById('sidebar').innerHTML = `
      <div class="sidebar-logo">
        <div class="logo-icon">🏟️</div>
        <h1>GYMFLOW</h1>
        <p>Staff Panel</p>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Menuja</div>
        ${navItems}
      </nav>
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar">ST</div>
          <div class="user-info">
            <div class="user-name"><?php echo $_SESSION['firstName']; ?></div>
            <div class="user-role">Staff</div>
          </div>
        </div>
      </div>`;
  });
</script>
</body>
</html>