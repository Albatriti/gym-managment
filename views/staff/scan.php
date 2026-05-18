<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: /gym-managment/login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

$result = null;
$token  = $_GET['token'] ?? '';

if ($token) {
    $stmt = $db->prepare("
        SELECT u.first_name, u.last_name,
               m.id as member_id, m.membership_status,
               m.membership_expiry, m.qr_token
        FROM members m
        JOIN users u ON m.user_id = u.id
        WHERE m.qr_token = :token
    ");
    $stmt->execute([':token' => $token]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        $result = ['type' => 'error', 'message' => 'QR Code nuk u njoh!', 'icon' => '❌'];
    } else {
        $stmt = $db->prepare("
            SELECT * FROM payments
            WHERE member_id = :mid
            AND status = 'paid'
            ORDER BY payment_date DESC
            LIMIT 1
        ");
        $stmt->execute([':mid' => $member['member_id']]);
        $lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member['membership_status'] !== 'active' || !$lastPayment) {
            $result = ['type' => 'danger', 'message' => 'Pagesa nuk është kryer! Check-in refuzuar.', 'icon' => '🔒', 'member' => $member];
        } else {
            $stmt = $db->prepare("INSERT INTO checkins (member_id, status) VALUES (:mid, 'ok')");
            $stmt->execute([':mid' => $member['member_id']]);
            $result = ['type' => 'success', 'message' => 'Check-in u regjistrua me sukses!', 'icon' => '✅', 'member' => $member];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Scan — GymFlow</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/gym-managment/style.css"/>
  <style>
    .scan-wrapper {
      max-width: 560px;
      margin: 0 auto;
    }
    .camera-container {
      position: relative;
      width: 100%;
      background: var(--dark2);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .camera-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .camera-title {
      font-family: 'Bebas Neue', cursive;
      font-size: 1rem;
      letter-spacing: 2px;
      color: var(--text);
    }
    .camera-status {
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 20px;
    }
    .status-scanning {
      background: rgba(232,255,71,0.12);
      color: var(--primary);
      animation: blink 1.5s infinite;
    }
    .status-idle {
      background: rgba(136,136,136,0.12);
      color: var(--text-muted);
    }
    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.4; }
    }
    #cameraView {
      width: 100%;
      height: 340px;
      object-fit: cover;
      display: block;
    }
    .scan-overlay {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
      top: 52px;
    }
    .scan-frame {
      width: 220px;
      height: 220px;
      position: relative;
    }
    .scan-frame::before,
    .scan-frame::after {
      content: '';
      position: absolute;
      width: 40px;
      height: 40px;
      border-color: var(--primary);
      border-style: solid;
    }
    .scan-frame::before {
      top: 0; left: 0;
      border-width: 3px 0 0 3px;
      border-radius: 4px 0 0 0;
    }
    .scan-frame::after {
      bottom: 0; right: 0;
      border-width: 0 3px 3px 0;
      border-radius: 0 0 4px 0;
    }
    .scan-frame-tr {
      position: absolute;
      top: 0; right: 0;
      width: 40px; height: 40px;
      border-top: 3px solid var(--primary);
      border-right: 3px solid var(--primary);
      border-radius: 0 4px 0 0;
    }
    .scan-frame-bl {
      position: absolute;
      bottom: 0; left: 0;
      width: 40px; height: 40px;
      border-bottom: 3px solid var(--primary);
      border-left: 3px solid var(--primary);
      border-radius: 0 0 0 4px;
    }
    .scan-line {
      position: absolute;
      left: 10px; right: 10px;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--primary), transparent);
      animation: scanLine 2s ease-in-out infinite;
      top: 10px;
    }
    @keyframes scanLine {
      0% { top: 10px; opacity: 1; }
      50% { top: calc(100% - 10px); opacity: 0.8; }
      100% { top: 10px; opacity: 1; }
    }
    #canvas { display: none; }
    .camera-footer {
      padding: 14px 20px;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .camera-hint {
      font-size: 0.78rem;
      color: var(--text-muted);
      font-weight: 300;
    }

    /* Result */
    .result-card {
      border-radius: 16px;
      padding: 40px 32px;
      text-align: center;
      margin-bottom: 16px;
      animation: fadeUp 0.4s ease;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .result-icon { font-size: 4rem; margin-bottom: 16px; display: block; }
    .result-name {
      font-family: 'Bebas Neue', cursive;
      font-size: 1.8rem;
      letter-spacing: 2px;
      margin-bottom: 6px;
    }
    .result-msg {
      font-size: 0.88rem;
      font-weight: 300;
      opacity: 0.8;
      margin-bottom: 20px;
    }
    .result-time {
      font-size: 0.75rem;
      opacity: 0.6;
      letter-spacing: 1px;
    }

    /* Manual input */
    .manual-card {
      background: var(--dark2);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
    }
    .manual-title {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 12px;
    }
    .manual-form {
      display: flex;
      gap: 8px;
    }
    .manual-input {
      flex: 1;
      background: var(--dark3);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 14px;
      color: var(--text);
      font-size: 0.85rem;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      transition: border-color 0.2s;
    }
    .manual-input:focus { border-color: var(--primary); }
    .manual-btn {
      background: var(--primary);
      color: var(--dark);
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      transition: background 0.2s;
      white-space: nowrap;
    }
    .manual-btn:hover { background: var(--primary-dark, #c8df20); }
  </style>
</head>
<body>
<aside class="sidebar" id="sidebar"></aside>
<div class="main-content">
  <header class="topbar">
    <span class="topbar-title">QR Scan — Check-In</span>
    <div class="topbar-actions">
      <a href="/gym-managment/views/staff/checkin.php" class="btn-ghost" style="text-decoration:none;">← Check-In Manual</a>
      <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
    </div>
  </header>
  <main class="page-content">
    <canvas id="canvas"></canvas>

    <div class="scan-wrapper">

      <?php if ($result): ?>

        <!-- RESULT -->
        <?php if ($result['type'] === 'success'): ?>
        <div class="result-card" style="background:rgba(46,213,115,0.08);border:2px solid rgba(46,213,115,0.3);">
          <span class="result-icon">✅</span>
          <?php if (isset($result['member'])): ?>
          <div class="result-name" style="color:var(--success);">
            <?php echo htmlspecialchars($result['member']['first_name'] . ' ' . $result['member']['last_name']); ?>
          </div>
          <?php endif; ?>
          <div class="result-msg" style="color:var(--success);"><?php echo $result['message']; ?></div>
          <div class="result-time"><?php echo date('H:i — d/m/Y'); ?></div>
        </div>

        <?php elseif ($result['type'] === 'danger'): ?>
        <div class="result-card" style="background:rgba(255,71,87,0.08);border:2px solid rgba(255,71,87,0.3);">
          <span class="result-icon">🔒</span>
          <?php if (isset($result['member'])): ?>
          <div class="result-name" style="color:var(--danger);">
            <?php echo htmlspecialchars($result['member']['first_name'] . ' ' . $result['member']['last_name']); ?>
          </div>
          <?php endif; ?>
          <div class="result-msg" style="color:var(--danger);"><?php echo $result['message']; ?></div>
          <div class="result-time"><?php echo date('H:i — d/m/Y'); ?></div>
        </div>

        <?php else: ?>
        <div class="result-card" style="background:rgba(255,71,87,0.08);border:2px solid rgba(255,71,87,0.3);">
          <span class="result-icon">❌</span>
          <div class="result-name" style="color:var(--danger);">Gabim</div>
          <div class="result-msg" style="color:var(--danger);"><?php echo $result['message']; ?></div>
        </div>
        <?php endif; ?>

        <a href="scan.php" class="btn-primary-custom" style="width:100%;justify-content:center;margin-bottom:12px;">
          📱 Skano Sërish
        </a>
        <a href="checkin.php" class="btn-ghost" style="width:100%;justify-content:center;text-decoration:none;display:flex;">
          ← Check-In Manual
        </a>

      <?php else: ?>

        <!-- CAMERA -->
        <div class="camera-container">
          <div class="camera-header">
            <span class="camera-title">📷 Kamera Live</span>
            <span class="camera-status status-idle" id="cameraStatus">Duke u ngarkuar...</span>
          </div>

          <video id="cameraView" autoplay playsinline muted></video>

          <div class="scan-overlay">
            <div class="scan-frame">
              <div class="scan-frame-tr"></div>
              <div class="scan-frame-bl"></div>
              <div class="scan-line"></div>
            </div>
          </div>

          <div class="camera-footer">
            <span class="camera-hint">📱 Mbaj QR Code-in brenda kornizës</span>
            <button class="manual-btn" style="padding:6px 14px;font-size:0.72rem;" onclick="switchCamera()">
              🔄 Ndrysho Kamerën
            </button>
          </div>
        </div>

        <!-- MANUAL INPUT -->
        <div class="manual-card">
          <div class="manual-title">Token Manual (Plan B)</div>
          <form class="manual-form" method="GET">
            <input class="manual-input" type="text" name="token" placeholder="Vendos token-in manualisht..."/>
            <button class="manual-btn" type="submit">Verifiko</button>
          </form>
        </div>

      <?php endif; ?>

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="/gym-managment/main.js"></script>
<script>
  // ── Sidebar ──
  document.addEventListener('DOMContentLoaded', function() {
    const role = '<?php echo $_SESSION['role']; ?>';
    const links = role === 'admin' ? [
      { href: '/gym-managment/views/admin/dashboard.php', icon: '📊', label: 'Dashboard',   id: 'dashboard' },
      { href: '/gym-managment/views/admin/members.php',   icon: '👥', label: 'Anëtarët',    id: 'members'   },
      { href: '/gym-managment/views/admin/trainers.php',  icon: '🏋️', label: 'Trajnerët',   id: 'trainers'  },
      { href: '/gym-managment/views/admin/classes.php',   icon: '📅', label: 'Klasat',      id: 'classes'   },
      { href: '/gym-managment/views/admin/payments.php',  icon: '💳', label: 'Pagesat',     id: 'payments'  },
      { href: '/gym-managment/views/admin/checkin.php',   icon: '✅', label: 'Check-In',    id: 'checkin'   },
      { href: '/gym-managment/change-password.php',       icon: '🔒', label: 'Fjalëkalimi', id: 'changepassword' },
    ] : [
      { href: '/gym-managment/views/staff/dashboard.php', icon: '📊', label: 'Dashboard',   id: 'dashboard' },
      { href: '/gym-managment/views/staff/checkin.php',   icon: '✅', label: 'Check-In',    id: 'checkin'   },
      { href: '/gym-managment/views/staff/payments.php',  icon: '💳', label: 'Pagesat',     id: 'payments'  },
      { href: '/gym-managment/change-password.php',       icon: '🔒', label: 'Fjalëkalimi', id: 'changepassword' },
    ];
    const navItems = links.map(l => `
      <a href="${l.href}" class="nav-link">
        <span class="nav-icon">${l.icon}</span> ${l.label}
      </a>`).join('');
    const panelName = role === 'admin' ? 'Admin Panel' : 'Staff Panel';
    const avatar    = role === 'admin' ? 'AD' : 'ST';
    document.getElementById('sidebar').innerHTML = `
      <div class="sidebar-logo">
        <div class="logo-icon">🏟️</div>
        <h1>APEX GYM</h1>
        <p>${panelName}</p>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Menuja</div>
        ${navItems}
      </nav>
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar">${avatar}</div>
          <div class="user-info">
            <div class="user-name"><?php echo $_SESSION['firstName']; ?></div>
            <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
          </div>
        </div>
      </div>`;
  });

  <?php if (!$result): ?>
  // ── Camera & QR Scanning ──
  const video    = document.getElementById('cameraView');
  const canvas   = document.getElementById('canvas');
  const ctx = canvas.getContext('2d', { willReadFrequently: true });
  const status   = document.getElementById('cameraStatus');
  let scanning   = false;
  let stream     = null;
  let facingMode = 'environment'; // rear camera default

  async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: facingMode,
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        });
        video.srcObject = stream;
        video.play();
        video.addEventListener('loadeddata', () => {
            canvas.width  = video.videoWidth  || 640;
            canvas.height = video.videoHeight || 480;
            status.textContent = '● Duke skanuar...';
            status.className   = 'camera-status status-scanning';
            scanning = true;
            setTimeout(() => requestAnimationFrame(scanFrame), 500);
        });
    } catch (err) {
        status.textContent = 'Kamera nuk u gjet';
        status.className   = 'camera-status status-idle';
        console.error('Camera error:', err);
    }
}

  function scanFrame() {
    if (!scanning) return;
    
    if (video.readyState === video.HAVE_ENOUGH_DATA && 
        video.videoWidth > 0 && video.videoHeight > 0) {
        
        // Siguro që canvas ka dimensionet e duhura
        if (canvas.width !== video.videoWidth) {
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
        }
        
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        try {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'attemptBoth'
            });
            
            if (code && code.data) {
                scanning = false;
                status.textContent = '✅ QR u lexua!';
                status.className   = 'camera-status status-scanning';
                
                console.log('QR Data:', code.data);
                
                // Extract token
                try {
                    const url   = new URL(code.data);
                    const token = url.searchParams.get('token');
                    if (token) {
                        window.location.href = '/gym-managment/views/staff/scan.php?token=' + token;
                        return;
                    }
                } catch {
                    // Nuk është URL — përdor direkt si token
                }
                window.location.href = '/gym-managment/views/staff/scan.php?token=' + encodeURIComponent(code.data);
                return;
            }
        } catch(e) {
            console.error('jsQR error:', e);
        }
    }
    requestAnimationFrame(scanFrame);
}

  async function switchCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    if (stream) stream.getTracks().forEach(t => t.stop());
    await startCamera();
  }

  startCamera();
  <?php endif; ?>

  <?php if ($result && $result['type'] === 'success'): ?>
  setTimeout(() => {
    window.location.href = '/gym-managment/views/staff/checkin.php';
  }, 4000);
  <?php endif; ?>
</script>
</body>
</html>