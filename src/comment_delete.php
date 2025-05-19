<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment_id = (int)$_POST['comment_id'];
    $post_id = (int)$_POST['post_id'];

    // 로그인한 사용자 ID 확인
    $sql = "SELECT ID FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "<script>alert('사용자 정보가 유효하지 않습니다.'); history.back();</script>";
        exit;
    }

    $user_id = $user['ID'];

    // 댓글 작성자가 본인인지 확인
    $sql = "SELECT * FROM Comment WHERE comment_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();
    $stmt->close();

    if (!$comment) {
        echo "<script>alert('댓글을 삭제할 권한이 없습니다.'); history.back();</script>";
        exit;
    }

    // 삭제 수행
    $stmt = $conn->prepare("DELETE FROM Comment WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    if ($stmt->execute()) {
        header("Location: post_view.php?post_id=$post_id");
        exit;
    } else {
        echo "<script>alert('댓글 삭제 중 오류 발생'); history.back();</script>";
    }
    $stmt->close();
}
?>
