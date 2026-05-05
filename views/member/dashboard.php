<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT u.first_name, u.last_name, u.email, m.phone, m.membership_status, m.membership_expiry, m.id as member_id
    FROM users u
    JOIN members m ON u.id = m.user_id
    WHERE u.id = :uid
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

$totalPayments = $db->prepare("SELECT COUNT(*) FROM payments WHERE member_id = :mid");
$totalPayments->execute([':mid' => $member['member_id']]);
$totalPayments = $totalPayments->fetchColumn();

$totalReservations = $db->prepare("SELECT COUNT(*) FROM checkins WHERE member_id = :mid");
$totalReservations->execute([':mid' => $member['member_id']]);
$totalReservations = $totalReservations->fetchColumn();

$availableClasses = $db->query("SELECT COUNT(*) FROM classes WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard — GymFlow Member</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gym-managment/style.css" />
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

            <!-- Profili -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-body" style="display:flex;align-items:center;gap:20px;">
                    <div style="width:64px;height:64px;border-radius:50%;background:var(--primary);color:var(--dark);display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',cursive;font-size:1.4rem;flex-shrink:0;">
                        <?php echo strtoupper(substr($member['first_name'],0,1) . substr($member['last_name'],0,1)); ?>
                    </div>
                    <div style="flex:1;">
                        <div style="font-family:'Bebas Neue',cursive;font-size:1.4rem;letter-spacing:2px;">
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </div>
                        <div style="font-size:0.82rem;color:var(--text-muted);"><?php echo htmlspecialchars($member['email']); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span class="badge-status <?php echo $member['membership_status'] === 'active' ? 'status-active' : 'status-expired'; ?>" style="font-size:0.82rem;">
                            <?php echo $member['membership_status'] === 'active' ? 'Anëtarësi Aktive' : 'Anëtarësia Skaduar'; ?>
                        </span>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:6px;">
                            Deri: <?php echo $member['membership_expiry'] ?? '—'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="stat-card yellow">
                    <div class="stat-icon">💳</div>
                    <div class="stat-value"><?php echo $totalPayments; ?></div>
                    <div class="stat-label">Pagesa Totale</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $totalReservations; ?></div>
                    <div class="stat-label">Hyrje Totale</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?php echo $availableClasses; ?></div>
                    <div class="stat-label">Klasa Aktive</div>
                </div>
            </div>
          <div style="margin-top:20px;">
  <a href="/gym-managment/views/member/change-password.php" class="btn-primary-custom" style="text-decoration:none;">
    🔒 Ndrysho Fjalëkalimin
  </a>
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
        <p>Member Panel</p>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Menuja</div>
        ${navItems}
      </nav>
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar"><?php echo strtoupper(substr($member['first_name'],0,1) . substr($member['last_name'],0,1)); ?></div>
          <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($member['first_name']); ?></div>
            <div class="user-role">Member</div>
          </div>
        </div>
      </div>`;
        });
    </script>
</body>

</html>