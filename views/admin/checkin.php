<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Regjistro check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkin') {
    $stmt = $db->prepare("SELECT m.id, m.membership_status FROM members m JOIN users u ON m.user_id = u.id WHERE u.id = :uid");
    $stmt->execute([':uid' => $_POST['user_id']]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $status = $member['membership_status'] === 'active' ? 'ok' : 'expired';
        $stmt = $db->prepare("INSERT INTO checkins (member_id, status) VALUES (:mid, :status)");
        $stmt->execute([':mid' => $member['id'], ':status' => $status]);
    }
    header('Location: checkin.php');
    exit;
}

// Merr check-in-et e sotme
$checkins = $db->query("
    SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as member_name, c.checkin_time
    FROM checkins c
    JOIN members m ON c.member_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE DATE(c.checkin_time) = CURDATE()
    ORDER BY c.checkin_time DESC
")->fetchAll(PDO::FETCH_ASSOC);

$todayTotal   = count($checkins);
$todayExpired = count(array_filter($checkins, fn($c) => $c['status'] === 'expired'));

// Merr të gjithë anëtarët për kërkim
$members = $db->query("
    SELECT u.id, u.first_name, u.last_name, m.membership_status
    FROM users u
    JOIN members m ON u.id = m.user_id
    ORDER BY u.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Check-In — GymFlow Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gym-managment/style.css" />
</head>

<body>
    <aside class="sidebar" id="sidebar"></aside>
    <div class="main-content">
        <header class="topbar">
            <span class="topbar-title">Check-In</span>
            <div class="topbar-actions">
                <span style="font-size:0.8rem;color:var(--text-muted);" id="liveClock"></span>
                <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
            </div>
        </header>
        <main class="page-content">

            <div style="display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;">

                <!-- Check-In Panel -->
                <div>
                    <div class="card" style="margin-bottom:16px;">
                        <div class="card-header"><span class="card-title">Regjistro Hyrjen</span></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Kërko Anëtarin</label>
                                <input class="form-control" type="text" id="checkinSearch" placeholder="Emri i anëtarit..." oninput="searchMember(this.value)" autocomplete="off" />
                            </div>
                            <div id="suggestions" style="display:none;background:var(--dark3);border:1px solid var(--border);border-radius:8px;margin-top:-8px;margin-bottom:14px;overflow:hidden;"></div>
                            <div id="selectedMember" style="display:none;background:var(--dark3);border-radius:10px;padding:14px;margin-bottom:16px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div id="selAvatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:var(--dark);display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',cursive;font-size:1rem;flex-shrink:0;"></div>
                                    <div>
                                        <div id="selName" style="font-weight:600;"></div>
                                        <div id="selStatus" style="font-size:0.78rem;margin-top:2px;"></div>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" id="checkinForm">
                                <input type="hidden" name="action" value="checkin" />
                                <input type="hidden" name="user_id" id="selectedUserId" />
                                <button class="btn-primary-custom" type="submit" style="width:100%;justify-content:center;padding:12px;" onclick="return validateCheckin()">
                                    ✅ Regjistro Check-In
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><span class="card-title">Statistikat Sot</span></div>
                        <div class="card-body">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <div style="background:var(--dark3);border-radius:10px;padding:14px;text-align:center;">
                                    <div style="font-family:'Bebas Neue',cursive;font-size:2rem;color:var(--primary);"><?php echo $todayTotal; ?></div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Check-In Sot</div>
                                </div>
                                <div style="background:var(--dark3);border-radius:10px;padding:14px;text-align:center;">
                                    <div style="font-family:'Bebas Neue',cursive;font-size:2rem;color:var(--danger);"><?php echo $todayExpired; ?></div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Skaduar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Historiku Sot</span>
                        <span style="font-size:0.78rem;color:var(--text-muted);"><?php echo $todayTotal; ?> hyrje</span>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ora</th>
                                    <th>Anëtari</th>
                                    <th>Statusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($checkins as $c): ?>
                                <tr>
                                    <td style="color:var(--text-muted);font-size:0.82rem;">
                                        <?php echo date('H:i', strtotime($c['checkin_time'])); ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($c['member_name']); ?></strong></td>
                                    <td>
                                        <span class="badge-status <?php echo $c['status'] === 'ok' ? 'status-active' : 'status-expired'; ?>">
                                            <?php echo $c['status'] === 'ok' ? 'Hyrje OK' : 'Skaduar'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($checkins)): ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;color:var(--text-muted);padding:32px;">Nuk ka check-in sot.</td>
                                </tr>
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
            initSidebar('checkin');
        });

        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent =
                now.toLocaleTimeString('sq-AL', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Members data nga PHP
        const members = <?php echo json_encode($members); ?>;
        let selectedMember = null;

        function searchMember(q) {
            const box = document.getElementById('suggestions');
            if (!q.trim()) {
                box.style.display = 'none';
                return;
            }
            const matches = members.filter(m =>
                (m.first_name + ' ' + m.last_name).toLowerCase().includes(q.toLowerCase())
            );
            if (!matches.length) {
                box.style.display = 'none';
                return;
            }
            box.innerHTML = matches.map(m => `
      <div onclick="selectMember(${m.id}, '${m.first_name} ${m.last_name}', '${m.membership_status}')"
        style="padding:10px 14px;cursor:pointer;font-size:0.85rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;"
        onmouseover="this.style.background='var(--dark4)'" onmouseout="this.style.background=''">
        <span>${m.first_name} ${m.last_name}</span>
        <span class="badge-status ${m.membership_status === 'active' ? 'status-active' : 'status-expired'}" style="font-size:0.68rem;">
          ${m.membership_status === 'active' ? 'Aktiv' : 'Skaduar'}
        </span>
      </div>`).join('');
            box.style.display = 'block';
        }

        function selectMember(id, name, status) {
            selectedMember = {
                id,
                name,
                status
            };
            document.getElementById('selectedUserId').value = id;
            document.getElementById('checkinSearch').value = name;
            document.getElementById('suggestions').style.display = 'none';
            const initials = name.split(' ').map(p => p[0]).join('').toUpperCase();
            document.getElementById('selAvatar').textContent = initials;
            document.getElementById('selName').textContent = name;
            document.getElementById('selStatus').innerHTML = status === 'active' ?
                '<span class="badge-status status-active">Anëtarësi Aktive</span>' :
                '<span class="badge-status status-expired">Anëtarësia ka Skaduar</span>';
            document.getElementById('selectedMember').style.display = 'block';
        }

        function validateCheckin() {
            if (!selectedMember) {
                showToast('Zgjidh një anëtar fillimisht!', 'warning');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>