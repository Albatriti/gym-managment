<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: /gym-managment/login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id']) && isset($_POST['member_id'])) {
    $classId  = (int)$_POST['class_id'];
    $memberId = (int)$_POST['member_id'];

    // Kontrollo nëse anëtari e ka rezervuar tashmë këtë klasë
    $stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE member_id = :mid AND class_id = :cid");
    $stmt->execute([':mid' => $memberId, ':cid' => $classId]);
    $alreadyReserved = $stmt->fetchColumn();

    if ($alreadyReserved > 0) {
        header('Location: classes.php?error=already_reserved');
        exit;
    }

    // Kontrollo nëse ka vende të lira
    $stmt = $db->prepare("SELECT capacity, enrolled FROM classes WHERE id = :id");
    $stmt->execute([':id' => $classId]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($class && $class['enrolled'] < $class['capacity']) {
        // Shto rezervimin në tabelën reservations
        $stmt = $db->prepare("INSERT INTO reservations (member_id, class_id) VALUES (:mid, :cid)");
        $stmt->execute([':mid' => $memberId, ':cid' => $classId]);

        // Përditëso numrin e enrolled në classes
        $stmt = $db->prepare("UPDATE classes SET enrolled = enrolled + 1 WHERE id = :id");
        $stmt->execute([':id' => $classId]);

        header('Location: classes.php?success=1');
    } else {
        header('Location: classes.php?error=full');
    }
    exit;
}

header('Location: classes.php');
exit;