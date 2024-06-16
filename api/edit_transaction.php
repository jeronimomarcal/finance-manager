<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE transactions SET amount = ?, type = ?, category = ?, date = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$amount, $type, $category, $date, $description, $id, $user_id]);

    echo json_encode(['status' => 'success']);
}
?>
