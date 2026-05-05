<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Shto klasë
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $db->prepare("INSERT INTO classes (name, time, capacity, trainer_id, room) VALUES (:name, :time, :capacity, :trainer_id, :room)");
    $stmt->execute([
        ':name'       => $_POST['name'],
        ':time'       => $_POST['time'],
        ':capacity'   => $_POST['capacity'],
        ':trainer_id' => $_POST['trainer_id'] ?: null,
        ':room'       => $_POST['room']
    ]);
    header('Location: classes.php');
    exit;
}

// Fshi klasë
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $stmt = $db->prepare("DELETE FROM classes WHERE id = :id");
    $stmt->execute([':id' => $_POST['class_id']]);
    header('Location: classes.php');
    exit;
}

// Merr të gjitha klasat
$classes = $db->query("
    SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as trainer_name
    FROM classes c
    LEFT JOIN trainers t ON c.trainer_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY c.time ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Merr trajnerët për modal
$trainers = $db->query("
    SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as full_name
    FROM trainers t
    JOIN users u ON t.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Klasat — GymFlow Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gym-managment/style.css" />
</head>

<body>
    <aside class="sidebar" id="sidebar"></aside>
    <div class="main-content">
        <header class="topbar">
            <span class="topbar-title">Klasat</span>
            <div class="topbar-actions">
                <button class="btn-primary-custom" onclick="openModal('addClassModal')">+ Shto Klasë</button>
                <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
            </div>
        </header>
        <main class="page-content">

            <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="stat-card yellow">
                    <div class="stat-icon">📅</div>
                    <div class="stat-value"><?php echo count($classes); ?></div>
                    <div class="stat-label">Klasa Totale</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $db->query("SELECT COUNT(*) FROM classes WHERE status='active'")->fetchColumn(); ?></div>
                    <div class="stat-label">Klasa Aktive</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">👤</div>
                    <div class="stat-value"><?php echo $db->query("SELECT COALESCE(SUM(enrolled),0) FROM classes")->fetchColumn(); ?></div>
                    <div class="stat-label">Rezervime Totale</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                <?php foreach ($classes as $c): ?>
                <?php $pct = $c['capacity'] > 0 ? round(($c['enrolled']/$c['capacity'])*100) : 0; ?>
                <?php $barColor = $pct >= 100 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--primary)'); ?>
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
                            <span class="badge-status <?php echo $c['status'] === 'active' ? 'status-active' : 'status-expired'; ?>">
                                <?php echo $c['status'] === 'active' ? 'Aktive' : 'Anuluar'; ?>
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
                        <form method="POST" onsubmit="return confirm('A je i sigurt?')">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="class_id" value="<?php echo $c['id']; ?>" />
                            <button class="btn-danger" type="submit" style="width:100%;">Fshi Klasën</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($classes)): ?>
                <div class="card" style="grid-column:span 3;">
                    <div class="card-body" style="text-align:center;color:var(--text-muted);padding:32px;">
                        Nuk ka klasa të regjistruara.
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <!-- Modal: Shto Klasë -->
    <div class="modal-overlay" id="addClassModal">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">Shto Klasë të Re</span>
                <button class="btn-icon" onclick="closeModal('addClassModal')">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add" />
                <div class="form-group">
                    <label class="form-label">Emri i Klasës</label>
                    <input class="form-control" type="text" name="name" placeholder="p.sh. Yoga, Crossfit..." required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ora</label>
                        <input class="form-control" type="time" name="time" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kapaciteti</label>
                        <input class="form-control" type="number" name="capacity" placeholder="20" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Trajneri</label>
                    <select class="form-control" name="trainer_id">
                        <option value="">— Zgjidh Trajnerin —</option>
                        <?php foreach ($trainers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Salla</label>
                    <input class="form-control" type="text" name="room" placeholder="p.sh. Salla A" />
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                    <button class="btn-ghost" type="button" onclick="closeModal('addClassModal')">Anulo</button>
                    <button class="btn-primary-custom" type="submit">✅ Shto Klasën</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/gym-managment/main.js?v=2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSidebar('classes');
        });
    </script>
</body>

</html>