<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

// Regjistro pagesë
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $db->prepare("INSERT INTO payments (member_id, amount, payment_date, method, period, status) VALUES (:mid, :amount, :date, :method, :period, 'paid')");
    $stmt->execute([
        ':mid'    => $_POST['member_id'],
        ':amount' => $_POST['amount'],
        ':date'   => $_POST['payment_date'],
        ':method' => $_POST['method'],
        ':period' => $_POST['period']
    ]);
    header('Location: payments.php');
    exit;
}

// Merr të gjitha pagesat
$payments = $db->query("
    SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as member_name
    FROM payments p
    JOIN members m ON p.member_id = m.id
    JOIN users u ON m.user_id = u.id
    ORDER BY p.payment_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Merr anëtarët për modal
$members = $db->query("
    SELECT m.id, CONCAT(u.first_name, ' ', u.last_name) as full_name
    FROM members m
    JOIN users u ON m.user_id = u.id
    ORDER BY u.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalPaid    = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid' AND MONTH(payment_date)=MONTH(NOW())")->fetchColumn();
$totalPending = $db->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$totalAll     = $db->query("SELECT COUNT(*) FROM payments")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pagesat — GymFlow Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gym-managment/style.css" />
</head>

<body>
    <aside class="sidebar" id="sidebar"></aside>
    <div class="main-content">
        <header class="topbar">
            <span class="topbar-title">Pagesat</span>
            <div class="topbar-actions">
                <button class="btn-primary-custom" onclick="openModal('addPaymentModal')">+ Regjistro Pagesë</button>
                <a href="/gym-managment/logout.php" class="btn-ghost" style="text-decoration:none;">Dil</a>
            </div>
        </header>
        <main class="page-content">

            <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="stat-card green">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value"><?php echo number_format($totalPaid, 2); ?>€</div>
                    <div class="stat-label">Fitimi Këtë Muaj</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo $totalPending; ?></div>
                    <div class="stat-label">Pagesa Pending</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?php echo $totalAll; ?></div>
                    <div class="stat-label">Pagesa Totale</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Historiku i Pagesave</span>
                    <div style="display:flex;gap:8px;">
                        <select class="form-control" style="width:auto;padding:6px 12px;font-size:0.8rem;" onchange="filterByStatus(this.value)">
                            <option value="">Të gjitha</option>
                            <option value="paid">Paguar</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Skaduar</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Anëtari</th>
                                <th>Shuma</th>
                                <th>Data</th>
                                <th>Periudha</th>
                                <th>Metoda</th>
                                <th>Statusi</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTable">
                            <?php foreach ($payments as $i => $p): ?>
                            <tr data-status="<?php echo $p['status']; ?>">
                                <td style="color:var(--text-muted);">PAY-<?php echo str_pad($p['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><strong><?php echo htmlspecialchars($p['member_name']); ?></strong></td>
                                <td style="color:<?php echo $p['status']==='paid' ? 'var(--success)' : 'var(--warning)'; ?>;font-weight:600;">
                                    <?php echo number_format($p['amount'], 2); ?>€
                                </td>
                                <td><?php echo $p['payment_date']; ?></td>
                                <td><?php echo htmlspecialchars($p['period'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($p['method']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $p['status']==='paid' ? 'status-active' : ($p['status']==='pending' ? 'status-pending' : 'status-expired'); ?>">
                                        <?php echo $p['status']==='paid' ? 'Paguar' : ($p['status']==='pending' ? 'Pending' : 'Skaduar'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">Nuk ka pagesa të regjistruara.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal: Regjistro Pagesë -->
    <div class="modal-overlay" id="addPaymentModal">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">Regjistro Pagesë</span>
                <button class="btn-icon" onclick="closeModal('addPaymentModal')">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add" />
                <div class="form-group">
                    <label class="form-label">Anëtari</label>
                    <select class="form-control" name="member_id" required>
                        <option value="">— Zgjidh Anëtarin —</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shuma (€)</label>
                        <input class="form-control" type="number" name="amount" value="30" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Metoda</label>
                        <select class="form-control" name="method">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Kartë</option>
                            <option value="transfer">🏦 Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data e Pagesës</label>
                        <input class="form-control" type="date" name="payment_date" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Periudha</label>
                        <input class="form-control" type="text" name="period" placeholder="p.sh. Mars 2026" />
                    </div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                    <button class="btn-ghost" type="button" onclick="closeModal('addPaymentModal')">Anulo</button>
                    <button class="btn-primary-custom" type="submit">✅ Regjistro</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/gym-managment/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSidebar('payments');
        });

        function filterByStatus(val) {
            document.querySelectorAll('#paymentsTable tr').forEach(row => {
                row.style.display = (!val || row.dataset.status === val) ? '' : 'none';
            });
        }
    </script>
</body>

</html>