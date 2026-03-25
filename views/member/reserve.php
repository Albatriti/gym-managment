<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: ../../login.php');
    exit;
}
require_once '../../data/Database.php';
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id']) && isset($_POST['member_id'])) {
    // Kontrollo nëse ka vende të lira
    $stmt = $db->prepare("SELECT capacity, enrolled FROM classes WHERE id = :id");
    $stmt->execute([':id' => $_POST['class_id']]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($class && $class['enrolled'] < $class['capacity']) {
        // Shto rezervimin
        $stmt = $db->prepare("UPDATE classes SET enrolled = enrolled + 1 WHERE id = :id");
        $stmt->execute([':id' => $_POST['class_id']]);
        // Regjistro check-in
        $stmt = $db->prepare("INSERT INTO checkins (member_id, status) VALUES (:mid, 'ok')");
        $stmt->execute([':mid' => $_POST['member_id']]);
        header('Location: classes.php?success=1');
    } else {
        header('Location: classes.php?error=1');
    }
    exit;
}

header('Location: classes.php');
exit;
