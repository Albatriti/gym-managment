<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$totalMembers  = $db->query("SELECT COUNT(*) FROM members")->fetchColumn();
$activeMembers = $db->query("SELECT COUNT(*) FROM members WHERE membership_status = 'active'")->fetchColumn();
$totalPayments = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'paid' AND MONTH(payment_date) = MONTH(NOW())")->fetchColumn();
$pendingPayments = $db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$totalClasses  = $db->query("SELECT COUNT(*) FROM classes WHERE status = 'active'")->fetchColumn();
$todayCheckins = $db->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_time) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — GymFlow Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Dashboard</span>
    <div class="topbar-actions">
      <span style="font-size:0.8rem; color:var(--text-muted);">
        Mirë se vjen, <?php echo $_SESSION['firstName']; ?>!
      </span>
      <a href="../../logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="stat-grid">
      <div class="stat-card yellow">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?php echo $activeMembers; ?></div>
        <div class="stat-label">Anëtarë Aktivë</div>
        <span class="stat-badge badge-yellow">Totali: <?php echo $totalMembers; ?></span>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">💰</div>
        <div class="stat-value"><?php echo number_format($totalPayments, 2); ?>€</div>
        <div class="stat-label">Fitimi Mujor</div>
        <span class="stat-badge badge-green">Mars 2026</span>
      </div>
      <div class="stat-card red">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?php echo $pendingPayments; ?></div>
        <div class="stat-label">Pagesa Pending</div>
        <span class="stat-badge badge-red">Kërkon vëmendje</span>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo $totalClasses; ?></div>
        <div class="stat-label">Klasa Aktive</div>
        <span class="stat-badge badge-blue">Check-in sot: <?php echo $todayCheckins; ?></span>
      </div>
    </div>

  </main>
</div>
<script src="/gym-managment/main.js?v=2"></script>
<script>initSidebar('dashboard');</script>
</body>
</html>