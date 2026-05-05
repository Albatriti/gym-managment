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

$classes = $db->query("
    SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as trainer_name
    FROM classes c
    LEFT JOIN trainers t ON c.trainer_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE c.status = 'active'
    ORDER BY c.time ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Klasat — GymFlow Member</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Klasat</span>
    <div class="topbar-actions">
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);">
      <div class="stat-card yellow">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo count($classes); ?></div>
        <div class="stat-label">Klasa Aktive</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo count(array_filter($classes, fn($c) => $c['capacity'] > $c['enrolled'])); ?></div>
        <div class="stat-label">Vende të Lira</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
      <?php foreach ($classes as $c): ?>
      <?php $pct = $c['capacity'] > 0 ? round(($c['enrolled']/$c['capacity'])*100) : 0; ?>
      <?php $barColor = $pct >= 100 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--primary)'); ?>
      <?php $full = $c['enrolled'] >= $c['capacity']; ?>
      <div class="card">
        <div class="card-body">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
            <div>
              <div style="font-family:'Bebas Neue',cursive;font-size:1.15rem;letter-spacing:2px;">
                <?php echo htmlspecialchars($c['name']); ?>
              </div>
              <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                🕐 <?php echo htmlspecialchars($c['time']); ?>
              </div>
            </div>
            <span class="badge-status <?php echo $full ? 'status-expired' : 'status-active'; ?>">
              <?php echo $full ? 'Plot' : 'Aktive'; ?>
            </span>
          </div>
          <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:14px;">
            👤 <?php echo htmlspecialchars($c['trainer_name'] ?? '—'); ?>
            &nbsp;|&nbsp;
            📍 <?php echo htmlspecialchars($c['room'] ?? '—'); ?>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.78rem;color:var(--text-muted);margin-bottom:6px;">
            <span><?php echo $c['enrolled']; ?> / <?php echo $c['capacity']; ?> vende</span>
            <span><?php echo $pct; ?>%</span>
          </div>
          <div class="progress-bar-wrap" style="margin-bottom:14px;">
            <div class="progress-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $barColor; ?>;"></div>
          </div>
          <?php if (!$full): ?>
          <form method="POST" action="reserve.php">
            <input type="hidden" name="class_id" value="<?php echo $c['id']; ?>"/>
            <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>"/>
            <button class="btn-primary-custom" type="submit" style="width:100%;justify-content:center;">
              Rezervo Vendin
            </button>
          </form>
          <?php else: ?>
          <button class="btn-ghost" style="width:100%;" disabled>Klasa Plotë</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($classes)): ?>
      <div class="card" style="grid-column:span 3;">
        <div class="card-body" style="text-align:center;color:var(--text-muted);padding:32px;">
          Nuk ka klasa aktive momentalisht.
        </div>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<script src="/gym-managment/main.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const links = [
      { href: '/gym-managment/views/member/dashboard.php',       icon: '📊', label: 'Dashboard',   id: 'dashboard'      },
      { href: '/gym-managment/views/member/classes.php',         icon: '📅', label: 'Klasat',      id: 'classes'        },
      { href: '/gym-managment/views/member/history.php',         icon: '📋', label: 'Historiku',   id: 'history'        },
      { href: '/gym-managment/views/member/change-password.php', icon: '🔒', label: 'Fjalëkalimi', id: 'changepassword' },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link ${l.id === 'classes' ? 'active' : ''}">
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