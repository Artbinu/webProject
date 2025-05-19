<?php
session_start();
require_once 'db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$verified = false;
$user_data = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_pw = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if ($user_data && password_verify($input_pw, $user_data['password'])) {
        $verified = true;
    } else {
        $errors[] = "비밀번호가 올바르지 않습니다.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>마이페이지</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="d-flex align-items-center">
      <div class="hamburger" id="hamburger">☰</div>
    </div>
    <div class="center-text">Digital Security</div>
    <div class="d-flex align-items-center">
      <a href="mypage.php" class="btn btn-outline-primary btn-sm">마이페이지</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="category-sidebar" id="categorySidebar">
    <div class="list-group">
      <a href="#" class="list-group-item list-group-item-action active">전체</a>
      <a href="#" class="list-group-item list-group-item-action">공지사항</a>
      <a href="#" class="list-group-item list-group-item-action">자유게시판</a>
      <a href="#" class="list-group-item list-group-item-action">질문답변</a>
      <a href="#" class="list-group-item list-group-item-action">기타</a>
    </div>
  </div>

  <div class="mypage-container">
    <?php if (!$verified): ?>
      <div class="mypage-title">비밀번호 확인</div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
      <?php endif; ?>

      <form method="post" action="mypage.php">
        <div class="mb-3">
          <label for="password" class="form-label">비밀번호</label>
          <input type="password" class="form-control" id="password" name="password" required/>
        </div>
        <button type="submit" class="btn btn-primary">확인</button>
      </form>
    <?php else: ?>
      <div class="mypage-title">마이페이지</div>
      <div class="mypage-info"><strong>아이디:</strong> <?= htmlspecialchars($user_data['user_id']) ?></div>
      <div class="mypage-info"><strong>이름:</strong> <?= htmlspecialchars($user_data['name']) ?></div>
      <div class="mypage-info"><strong>이메일:</strong> <?= htmlspecialchars($user_data['email']) ?></div>
      <div class="mypage-info"><strong>생년월일:</strong> <?= htmlspecialchars($user_data['birth']) ?></div>
      <div class="mypage-info"><strong>가입일:</strong> <?= htmlspecialchars($user_data['created_date']) ?></div>
      <div class="mypage-info"><strong>등급:</strong> <?= $user_data['role_code'] == 9 ? "관리자" : "일반회원" ?></div>
    <?php endif; ?>
  </div>

  <script>
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('categorySidebar');
    let timer;

    hamburger.addEventListener('mouseenter', () => {
      sidebar.classList.add('show');
      clearTimeout(timer);
    });

    sidebar.addEventListener('mouseleave', () => {
      timer = setTimeout(() => {
        sidebar.classList.remove('show');
      }, 2000);
    });
  </script>
</body>
</html>
