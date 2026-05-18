<?php
require_once '../data/Database.php';
$db = Database::getInstance()->getConnection();

$trainers = $db->query("
    SELECT u.first_name, u.last_name, u.email, t.specialization, t.id,
           GROUP_CONCAT(c.name SEPARATOR '|') as classes
    FROM users u
    JOIN trainers t ON u.id = t.user_id
    LEFT JOIN classes c ON c.trainer_id = t.id AND c.status = 'active'
    GROUP BY t.id, u.first_name, u.last_name, u.email, t.specialization
    ORDER BY u.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trajnerët — APEX Gym</title>
  <link rel="stylesheet" href="css/landing.css"/>
  <style>
    .trainer-card {
      background: var(--dark2);
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s;
    }
    .trainer-card:hover {
      border-color: rgba(232,255,71,0.2);
      transform: translateY(-6px);
    }
    .trainer-avatar {
      width: 100%;
      height: 180px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 4rem;
    }
    .trainer-body { padding: 28px; }
    .trainer-name {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.4rem;
      letter-spacing: 2px;
      margin-bottom: 4px;
    }
    .trainer-spec {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--primary);
      margin-bottom: 16px;
    }
    .trainer-classes {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 16px;
    }
    .trainer-class-tag {
      font-size: 0.68rem;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 10px;
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: 2px;
      color: var(--text-muted);
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
      <span>Trajnerët</span>
    </div>
    <span class="section-tag">Ekipi Ynë</span>
    <h1 class="section-title" style="font-size:clamp(3rem,7vw,6rem);">Trajnerët<br/>Profesionalë</h1>
    <p class="section-desc">
      <?php echo count($trainers); ?> trajnerë të certifikuar me passion për të ndihmuar çdo anëtar.
    </p>
  </div>
</div>

<div class="section">

  <?php if (empty($trainers)): ?>
  <div style="text-align:center;padding:80px 0;color:var(--text-muted);">
    <div style="font-size:3rem;margin-bottom:16px;">🏋️</div>
    <div style="font-family:'Bebas Neue',cursive;font-size:1.5rem;letter-spacing:2px;margin-bottom:8px;">Ekipi po formohet</div>
    <div style="font-size:0.88rem;font-weight:300;">Trajnerët tanë do të shfaqen së shpejti!</div>
  </div>
  <?php else: ?>

  <?php
  $avatarColors = [
    ['bg' => 'rgba(232,255,71,0.08)', 'emoji' => '🧘'],
    ['bg' => 'rgba(255,71,87,0.08)',  'emoji' => '🥊'],
    ['bg' => 'rgba(46,213,115,0.08)', 'emoji' => '💪'],
    ['bg' => 'rgba(30,144,255,0.08)', 'emoji' => '🏋️'],
    ['bg' => 'rgba(255,165,2,0.08)',  'emoji' => '🏃'],
    ['bg' => 'rgba(232,71,255,0.08)', 'emoji' => '🤸'],
  ];
  ?>

  <div class="card-grid card-grid-3" style="margin-bottom:80px;">
    <?php foreach ($trainers as $i => $t):
      $color = $avatarColors[$i % count($avatarColors)];
      $initials = strtoupper(substr($t['first_name'],0,1) . substr($t['last_name'],0,1));
      $classList = $t['classes'] ? explode('|', $t['classes']) : [];
    ?>
    <div class="trainer-card reveal">
      <div class="trainer-avatar" style="background:<?php echo $color['bg']; ?>;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
          <div style="font-size:3rem;"><?php echo $color['emoji']; ?></div>
          <div style="font-family:'Bebas Neue',cursive;font-size:1.2rem;letter-spacing:2px;color:var(--primary);">
            <?php echo $initials; ?>
          </div>
        </div>
      </div>
      <div class="trainer-body">
        <div class="trainer-name">
          <?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name']); ?>
        </div>
        <div class="trainer-spec">
          <?php echo htmlspecialchars($t['specialization'] ?? 'Trajner i Certifikuar'); ?>
        </div>
        <?php if (!empty($classList)): ?>
        <div class="trainer-classes">
          <?php foreach ($classList as $cls): ?>
          <span class="trainer-class-tag"><?php echo htmlspecialchars($cls); ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

  <!-- Join Team CTA -->
  <div style="background:var(--dark2);border:1px solid var(--border);border-radius:12px;padding:60px;text-align:center;" class="reveal">
    <span class="section-tag">Karrierë</span>
    <h3 style="font-family:'Bebas Neue',cursive;font-size:2.5rem;letter-spacing:2px;margin-bottom:16px;">Bashkohu me Ekipin Tonë</h3>
    <p style="color:var(--text-muted);font-size:0.9rem;font-weight:300;margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto;line-height:1.8;">
      Je trajner i certifikuar? APEX Gym kërkon trajnerë të talentuar për t'u bashkuar me ekipin tonë.
    </p>
    <a href="contact.html" class="btn-primary">Na Kontakto</a>
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