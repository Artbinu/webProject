<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$post_id = (int)($_GET['post_id'] ?? 0);

if ($post_id === 0) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='main.php';</script>";
    exit;
}

// 현재 로그인한 사용자 DB ID 확인
$stmt = $conn->prepare("SELECT ID FROM Users WHERE user_id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<script>alert('사용자 정보가 유효하지 않습니다.'); location.href='main.php';</script>";
    exit;
}

$user_id = $user['ID'];

// 게시글이 본인 소유인지 확인
$stmt = $conn->prepare("SELECT * FROM Post WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "<script>alert('삭제 권한이 없습니다.'); location.href='main.php';</script>";
    exit;
}

// 게시글 삭제
$stmt = $conn->prepare("DELETE FROM Post WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
if ($stmt->execute()) {
    echo "<script>alert('게시글이 삭제되었습니다.'); location.href='main.php';</script>";
} else {
    echo "<script>alert('삭제 중 오류 발생.'); history.back();</script>";
}
$stmt->close();
?>
