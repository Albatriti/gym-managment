<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT m.id as member_id FROM members m WHERE m.user_id = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
$memberId = $member['member_id'];

// Merr pagesat
$payments = $db->prepare("
    SELECT * FROM payments
    WHERE member_id = :mid
    ORDER BY payment_date DESC
");
$payments->execute([':mid' => $memberId]);
$payments = $payments->fetchAll(PDO::FETCH_ASSOC);

// Merr check-in-et
$checkins = $db->prepare("
    SELECT * FROM checkins
    WHERE member_id = :mid
    ORDER BY checkin_time DESC
");
$checkins->execute([':mid' => $memberId]);
$checkins = $checkins->fetchAll(PDO::FETCH_ASSOC);

$totalPaid = array_sum(array_column(array_filter($payments, fn($p) => $p['status'] === 'paid'), 'amount'));
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Historiku — GymFlow Member</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Historiku</span>
    <div class="topbar-actions">
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
      <div class="stat-card green">
        <div class="stat-icon">💰</div>
        <div class="stat-value"><?php echo number_format($totalPaid, 2); ?>€</div>
        <div class="stat-label">Totali i Paguar</div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-icon">💳</div>
        <div class="stat-value"><?php echo count($payments); ?></div>
        <div class="stat-label">Pagesa Totale</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo count($checkins); ?></div>
        <div class="stat-label">Hyrje Totale</div>
      </div>
    </div>

    <div class="section-grid">

      <!-- Pagesat -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Historiku i Pagesave</span>
          <span style="font-size:0.78rem;color:var(--text-muted);"><?php echo count($payments); ?> pagesa</span>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Data</th>
                <th>Shuma</th>
                <th>Metoda</th>
                <th>Statusi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
              <tr>
                <td style="color:var(--text-muted);"><?php echo $p['payment_date']; ?></td>
                <td style="color:<?php echo $p['status']==='paid' ? 'var(--success)' : 'var(--warning)'; ?>;font-weight:600;">
                  <?php echo number_format($p['amount'], 2); ?>€
                </td>
                <td><?php echo htmlspecialchars($p['method']); ?></td>
                <td>
                  <span class="badge-status <?php echo $p['status']==='paid' ? 'status-active' : 'status-pending'; ?>">
                    <?php echo $p['status']==='paid' ? 'Paguar' : 'Pending'; ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($payments)): ?>
              <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;">Nuk ka pagesa.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Check-in-et -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Historiku i Hyrjeve</span>
          <span style="font-size:0.78rem;color:var(--text-muted);"><?php echo count($checkins); ?> hyrje</span>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Data</th>
                <th>Ora</th>
                <th>Statusi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($checkins as $c): ?>
              <tr>
                <td style="color:var(--text-muted);"><?php echo date('d/m/Y', strtotime($c['checkin_time'])); ?></td>
                <td style="color:var(--text-muted);"><?php echo date('H:i', strtotime($c['checkin_time'])); ?></td>
                <td>
                  <span class="badge-status <?php echo $c['status']==='ok' ? 'status-active' : 'status-expired'; ?>">
                    <?php echo $c['status']==='ok' ? 'Hyrje OK' : 'Skaduar'; ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($checkins)): ?>
              <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px;">Nuk ka hyrje.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="/gym-managment/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const links = [
      { href: '/gym-managment/views/member/dashboard.php', icon: '📊', label: 'Dashboard', id: 'dashboard' },
      { href: '/gym-managment/views/member/classes.php',   icon: '📅', label: 'Klasat',    id: 'classes'   },
      { href: '/gym-managment/views/member/history.php',   icon: '📋', label: 'Historiku', id: 'history'   },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link ${l.id === 'history' ? 'active' : ''}">
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
</script>
</body>
</html>