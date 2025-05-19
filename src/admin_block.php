<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 9) {
    echo "<script>alert('관리자 전용입니다.'); location.href='main.php';</script>";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='admin.php';</script>";
    exit;
}

$block_until = date("Y-m-d H:i:s", strtotime("+1 day"));

$stmt = $conn->prepare("UPDATE Users SET blocked_until = ? WHERE ID = ?");
$stmt->bind_param("si", $block_until, $id);

if ($stmt->execute()) {
    echo "<script>alert('사용자가 1일간 차단되었습니다.'); location.href='admin.php';</script>";
} else {
    echo "<script>alert('차단 처리 중 오류 발생.'); location.href='admin.php';</script>";
}
$stmt->close();
