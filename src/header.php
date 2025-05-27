<?php
// header.php

// 세션 시작 (로그인 상태 체크 등 필요 시)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Website</title>
    <!-- CSS 파일 연결 -->
    <link rel="stylesheet" href="/css/style.css">
    <link href="footer.css" rel="stylesheet" />
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/">My Website</a></h1>
            <nav>
                <ul>
                    <li><a href="/">홈</a></li>
                    <li><a href="/about.php">소개</a></li>
                    <li><a href="/contact.php">문의하기</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/profile.php">내 정보</a></li>
                        <li><a href="/logout.php">로그아웃</a></li>
                    <?php else: ?>
                        <li><a href="/login.php">로그인</a></li>
                        <li><a href="/register.php">회원가입</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
