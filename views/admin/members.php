<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Shto anëtar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (:fn, :ln, :email, :pass, 'member')");
    $stmt->execute([':fn' => $_POST['first_name'], ':ln' => $_POST['last_name'], ':email' => $_POST['email'], ':pass' => $hashed]);
    $userId = $db->lastInsertId();
    $stmt = $db->prepare("INSERT INTO members (user_id, phone, membership_status, membership_expiry) VALUES (:uid, :phone, 'active', :expiry)");
    $stmt->execute([':uid' => $userId, ':phone' => $_POST['phone'], ':expiry' => $_POST['expiry']]);
    header('Location: members.php');
    exit;
}

// Fshi anëtar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $_POST['user_id']]);
    header('Location: members.php');
    exit;
}

// Merr të gjithë anëtarët
$members = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.email, m.phone, m.membership_status, m.membership_expiry, m.id as member_id
    FROM users u
    JOIN members m ON u.id = m.user_id
    ORDER BY u.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalActive  = $db->query("SELECT COUNT(*) FROM members WHERE membership_status = 'active'")->fetchColumn();
$totalExpired = $db->query("SELECT COUNT(*) FROM members WHERE membership_status = 'expired'")->fetchColumn();
$total        = count($members);
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anëtarët — GymFlow Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gym-managment/style.css" />
</head>

<body>
    <aside class="sidebar" id="sidebar"></aside>
    <div class="main-content">
        <header class="topbar">
            <span class="topbar-title">Anëtarët</span>
            <div class="topbar-actions">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Kërko anëtar..." oninput="filterMembers()" />
                </div>
                <button class="btn-primary-custom" onclick="openModal('addMemberModal')">+ Shto Anëtar</button>
                <a href="../../logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
            </div>
        </header>
        <main class="page-content">

            <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="stat-card yellow">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?php echo $totalActive; ?></div>
                    <div class="stat-label">Anëtarë Aktivë</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value"><?php echo $totalExpired; ?></div>
                    <div class="stat-label">Anëtarësi Skaduar</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo $total; ?></div>
                    <div class="stat-label">Totali</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Lista e Anëtarëve</span>
                    <span style="font-size:0.78rem; color:var(--text-muted);"><?php echo $total; ?> anëtarë</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Emri</th>
                                <th>Email</th>
                                <th>Telefoni</th>
                                <th>Anëtarësia deri</th>
                                <th>Statusi</th>
                                <th>Veprimet</th>
                            </tr>
                        </thead>
                        <tbody id="membersTable">
                            <?php foreach ($members as $i => $m): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?php echo str_pad($i+1, 3, '0', STR_PAD_LEFT); ?></td>
                                <td><strong><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?></strong></td>
                                <td style="color:var(--text-muted);"><?php echo htmlspecialchars($m['email']); ?></td>
                                <td style="color:var(--text-muted);"><?php echo htmlspecialchars($m['phone'] ?? '—'); ?></td>
                                <td><?php echo $m['membership_expiry'] ?? '—'; ?></td>
                                <td>
                                    <span class="badge-status <?php echo $m['membership_status'] === 'active' ? 'status-active' : ($m['membership_status'] === 'expired' ? 'status-expired' : 'status-pending'); ?>">
                                        <?php echo $m['membership_status'] === 'active' ? 'Aktiv' : ($m['membership_status'] === 'expired' ? 'Skaduar' : 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('A je i sigurt?')">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="user_id" value="<?php echo $m['id']; ?>" />
                                        <button class="btn-danger" type="submit">Fshi</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--text-muted); padding:32px;">Nuk ka anëtarë të regjistruar.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal: Shto Anëtar -->
    <div class="modal-overlay" id="addMemberModal">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">Shto Anëtar të Ri</span>
                <button class="btn-icon" onclick="closeModal('addMemberModal')">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add" />
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Emri</label>
                        <input class="form-control" type="text" name="first_name" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mbiemri</label>
                        <input class="form-control" type="text" name="last_name" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Fjalëkalimi</label>
                    <input class="form-control" type="password" name="password" required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Telefoni</label>
                        <input class="form-control" type="text" name="phone" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anëtarësia deri</label>
                        <input class="form-control" type="date" name="expiry" required />
                    </div>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                    <button class="btn-ghost" type="button" onclick="closeModal('addMemberModal')">Anulo</button>
                    <button class="btn-primary-custom" type="submit">✅ Shto Anëtarin</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/gym-managment/main.js"></script>
    <script>
        initSidebar('members');

        function filterMembers() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('#membersTable tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        }
    </script>
</body>

</html>