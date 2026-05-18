<?php
require_once '../data/Database.php';
$db = Database::getInstance()->getConnection();

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
  <title>Klasat — APEX Gym</title>
  <link rel="stylesheet" href="css/landing.css"/>
  <style>
    .class-card {
      background: var(--dark2);
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s;
    }
    .class-card:hover {
      border-color: rgba(232,255,71,0.2);
      transform: translateY(-4px);
    }
    .class-card-header {
      padding: 28px;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .class-card-body { padding: 28px; }
    .class-name {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.5rem;
      letter-spacing: 2px;
      margin-bottom: 8px;
    }
    .class-desc {
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 300;
      line-height: 1.6;
      margin-bottom: 20px;
    }
    .class-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      font-size: 0.78rem;
      color: var(--text-muted);
    }
    .class-meta span {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .progress-wrap {
      background: var(--dark3);
      border-radius: 4px;
      height: 4px;
      overflow: hidden;
      margin-top: 16px;
    }
    .progress-fill {
      height: 100%;
      border-radius: 4px;
      background: var(--primary);
      transition: width 1s ease;
    }
    .spots-text {
      font-size: 0.72rem;
      color: var(--text-muted);
      margin-top: 6px;
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>

<nav class="navbar">
  <a href="index.html" class="nav-logo">
    <div>
      <div class="nav-logo-text">APEX</div>
      <div class="nav-logo-sub">Gym & Fitness</div>
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="index.html">Kryefaqja</a></li>
    <li><a href="services.html">Shërbimet</a></li>
    <li><a href="classes.php">Klasat</a></li>
    <li><a href="trainers.php">Trajnerët</a></li>
    <li><a href="pricing.html">Çmimet</a></li>
    <li><a href="contact.html">Kontakti</a></li>
  </ul>
  <div class="nav-actions">
    <a href="../login.php" class="btn-nav-login">Hyr</a>
    <a href="../register.php" class="btn-nav-register">Regjistrohu</a>
  </div>
  <div class="hamburger"><span></span><span></span><span></span></div>
</nav>

<div class="page-hero">
  <div class="page-hero-content">
    <div class="breadcrumb">
      <a href="index.html">Kryefaqja</a>
      <span>→</span>
      <span>Klasat</span>
    </div>
    <span class="section-tag">Orari Javor</span>
    <h1 class="section-title" style="font-size:clamp(3rem,7vw,6rem);">Klasat<br/>Tona</h1>
    <p class="section-desc">
      <?php echo count($classes); ?> klasa aktive. Rezervo vendin tënd online nga paneli personal.
    </p>
  </div>
</div>

<div class="section">

  <?php if (empty($classes)): ?>
  <div style="text-align:center;padding:80px 0;color:var(--text-muted);">
    <div style="font-size:3rem;margin-bottom:16px;">📅</div>
    <div style="font-family:'Bebas Neue',cursive;font-size:1.5rem;letter-spacing:2px;margin-bottom:8px;">Klasa së shpejti</div>
    <div style="font-size:0.88rem;font-weight:300;">Orari i klasave po përgatitet. Kontrolloni përsëri së shpejti!</div>
  </div>
  <?php else: ?>

  <div class="card-grid card-grid-3">
    <?php
    $icons = ['🧘','⚡','🤸','💃','🥊','🚴','🏋️','🌊','🥋'];
    $i = 0;
    foreach ($classes as $c):
      $pct = $c['capacity'] > 0 ? round(($c['enrolled']/$c['capacity'])*100) : 0;
      $barColor = $pct >= 100 ? '#ff4757' : ($pct >= 70 ? '#ffa502' : '#E8FF47');
      $full = $c['enrolled'] >= $c['capacity'];
      $icon = $icons[$i % count($icons)];
      $i++;
    ?>
    <div class="class-card reveal">
      <div class="class-card-header">
        <div style="font-size:2rem;"><?php echo $icon; ?></div>
        <?php if ($full): ?>
          <span style="font-size:0.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:4px 10px;border-radius:2px;background:rgba(255,71,87,0.12);color:#ff4757;">Plot</span>
        <?php else: ?>
          <span style="font-size:0.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:4px 10px;border-radius:2px;background:rgba(46,213,115,0.12);color:#2ed573;">Aktive</span>
        <?php endif; ?>
      </div>
      <div class="class-card-body">
        <div class="class-name"><?php echo htmlspecialchars($c['name']); ?></div>
        <div class="class-meta">
          <span>🕐 <?php echo htmlspecialchars($c['time']); ?></span>
          <span>👤 <?php echo htmlspecialchars($c['trainer_name'] ?? '—'); ?></span>
          <?php if ($c['room']): ?>
          <span>📍 <?php echo htmlspecialchars($c['room']); ?></span>
          <?php endif; ?>
        </div>
        <div class="progress-wrap">
          <div class="progress-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $barColor; ?>;"></div>
        </div>
        <div class="spots-text">
          <span><?php echo $c['enrolled']; ?> / <?php echo $c['capacity']; ?> vende</span>
          <span><?php echo $pct; ?>%</span>
        </div>
        <div style="margin-top:16px;">
          <?php if (!$full): ?>
          <a href="../register.php" class="btn-primary" style="width:100%;justify-content:center;font-size:0.78rem;padding:12px;">
            Rezervo — Regjistrohu
          </a>
          <?php else: ?>
          <button style="width:100%;padding:12px;background:transparent;border:1px solid var(--border);border-radius:3px;color:var(--text-muted);font-size:0.78rem;cursor:not-allowed;">
            Klasa Plotë
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

  <!-- CTA -->
  <div style="text-align:center;margin-top:64px;padding:48px;background:var(--dark2);border:1px solid var(--border);border-radius:12px;" class="reveal">
    <h3 style="font-family:'Bebas Neue',cursive;font-size:2.5rem;letter-spacing:2px;margin-bottom:12px;">Rezervo Vendin Tënd</h3>
    <p style="color:var(--text-muted);font-size:0.9rem;font-weight:300;margin-bottom:28px;">Regjistrohu dhe rezervo klasat nga paneli personal online.</p>
    <a href="../register.php" class="btn-primary">Regjistrohu & Rezervo</a>
  </div>

</div>

<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <span class="logo-text">APEX</span>
      <p>Palestra premium në Mitrovicë për ata që duan më shumë nga jeta dhe trupi i tyre.</p>
    </div>
    <div class="footer-col">
      <h4>Navigimi</h4>
      <ul>
        <li><a href="index.html">Kryefaqja</a></li>
        <li><a href="services.html">Shërbimet</a></li>
        <li><a href="classes.php">Klasat</a></li>
        <li><a href="trainers.php">Trajnerët</a></li>
        <li><a href="pricing.html">Çmimet</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Llogaria</h4>
      <ul>
        <li><a href="../login.php">Hyr</a></li>
        <li><a href="../register.php">Regjistrohu</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Kontakti</h4>
      <ul>
        <li><a href="contact.html">📍 Mitrovicë, Kosovë</a></li>
        <li><a href="tel:+38344000000">📞 +383 44 000 000</a></li>
        <li><a href="mailto:info@apexgym.com">✉️ info@apexgym.com</a></li>
        <li><a href="#">⏰ 06:00 — 22:00</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 APEX Gym Mitrovicë. Të gjitha të drejtat e rezervuara.</p>
    <p>Developed by Albatriti</p>
  </div>
</footer>

<script src="js/landing.js"></script>
</body>
</html>