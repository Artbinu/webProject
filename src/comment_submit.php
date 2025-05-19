<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $post_id = (int)$_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (!$comment) {
        echo "<script>alert('댓글을 입력하세요.'); history.back();</script>";
        exit;
    }

    // 사용자 ID 조회 (Users 테이블 ID 기준)
    $sql = "SELECT ID FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "<script>alert('사용자 정보가 유효하지 않습니다.'); history.back();</script>";
        exit;
    }

    $user_db_id = $user['ID'];

    // 댓글 저장
    $stmt = $conn->prepare("INSERT INTO Comment (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_db_id, $comment);

    if ($stmt->execute()) {
        header("Location: post_view.php?post_id=" . $post_id);
        exit;
    } else {
        echo "<script>alert('댓글 저장 중 오류 발생'); history.back();</script>";
    }

    $stmt->close();
}
?>
