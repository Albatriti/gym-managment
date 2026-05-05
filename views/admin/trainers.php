<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Shto trajner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (:fn, :ln, :email, :pass, 'staff')");
    $stmt->execute([':fn' => $_POST['first_name'], ':ln' => $_POST['last_name'], ':email' => $_POST['email'], ':pass' => $hashed]);
    $userId = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO trainers (user_id, specialization) VALUES (:uid, :spec)");
    $stmt->execute([':uid' => $userId, ':spec' => $_POST['specialization']]);
    header('Location: trainers.php');
    exit;
}

// Fshi trajner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $_POST['user_id']]);
    header('Location: trainers.php');
    exit;
}

// Merr të gjithë trajnerët
$trainers = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.email, t.specialization, t.id as trainer_id
    FROM users u
    JOIN trainers t ON u.id = t.user_id
    ORDER BY u.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trajnerët — GymFlow Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">Trajnerët</span>
    <div class="topbar-actions">
      <button class="btn-primary-custom" onclick="openModal('addTrainerModal')">+ Shto Trajner</button>
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">

    <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);">
      <div class="stat-card yellow">
        <div class="stat-icon">🏋️</div>
        <div class="stat-value"><?php echo count($trainers); ?></div>
        <div class="stat-label">Trajnerë Aktivë</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?php echo $db->query("SELECT COUNT(*) FROM classes")->fetchColumn(); ?></div>
        <div class="stat-label">Klasa Totale</div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
      <?php foreach ($trainers as $t): ?>
      <?php $initials = strtoupper(substr($t['first_name'],0,1) . substr($t['last_name'],0,1)); ?>
      <?php $colors = ['#E8FF47','#1e90ff','#2ed573','#ffa502','#ff6b9d']; ?>
      <?php $color = $colors[($t['trainer_id']-1) % count($colors)]; ?>
      <?php $textColor = $color === '#E8FF47' ? '#0a0a0a' : '#fff'; ?>
      <div class="card">
        <div class="card-body" style="text-align:center;">
          <div style="width:64px;height:64px;border-radius:50%;background:<?php echo $color; ?>;color:<?php echo $textColor; ?>;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',cursive;font-size:1.4rem;margin:0 auto 14px;">
            <?php echo $initials; ?>
          </div>
          <div style="font-family:'Bebas Neue',cursive;font-size:1.1rem;letter-spacing:2px;">
            <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>
          </div>
          <div style="font-size:0.78rem;color:var(--text-muted);margin:4px 0 14px;">
            <?php echo htmlspecialchars($t['specialization'] ?? '—'); ?>
          </div>
          <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:14px;">
            <?php echo htmlspecialchars($t['email']); ?>
          </div>
          <form method="POST" onsubmit="return confirm('A je i sigurt?')">
            <input type="hidden" name="action" value="delete"/>
            <input type="hidden" name="user_id" value="<?php echo $t['id']; ?>"/>
            <button class="btn-danger" type="submit">Fshi</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($trainers)): ?>
      <div class="card" style="grid-column:span 3;">
        <div class="card-body" style="text-align:center;color:var(--text-muted);padding:32px;">
          Nuk ka trajnerë të regjistruar.
        </div>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- Modal: Shto Trajner -->
<div class="modal-overlay" id="addTrainerModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Shto Trajner të Ri</span>
      <button class="btn-icon" onclick="closeModal('addTrainerModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add"/>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Emri</label>
          <input class="form-control" type="text" name="first_name" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Mbiemri</label>
          <input class="form-control" type="text" name="last_name" required/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Fjalëkalimi</label>
        <input class="form-control" type="password" name="password" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Specializimi</label>
        <input class="form-control" type="text" name="specialization" placeholder="p.sh. Yoga & Pilates"/>
      </div>
      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
        <button class="btn-ghost" type="button" onclick="closeModal('addTrainerModal')">Anulo</button>
        <button class="btn-primary-custom" type="submit">✅ Shto Trajnerin</button>
      </div>
    </form>
  </div>
</div>

<script src="/gym-managment/main.js?v=2"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    initSidebar('trainers');
  });
</script>
</body>
</html>