<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lock_time'] = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['username']);
    $password = $_POST['password'];

    // 로그인 잠금 체크
    if ($_SESSION['login_attempts'] >= 5) {
        $lock_time = $_SESSION['lock_time'];
        if (time() - $lock_time < 300) {
            $errors[] = "⛔ 로그인 5회 실패. 5분 후 다시 시도하세요.";
        } else {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_time'] = null;
        }
    }

    if (empty($errors)) {
        $sql = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // 로그인 성공
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role_code'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_time'] = null;

            header("Location: main.php");
            exit;
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_time'] = time();
            }
            $errors[] = "아이디 또는 비밀번호가 올바르지 않습니다.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>로그인 페이지</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
  <link href="footer.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">Digital Security</div>
  </div>

  <div class="login-container">
    <div class="login-title">로그인</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
      <div class="mb-3">
        <label for="username" class="form-label">아이디</label>
        <input type="text" class="form-control" id="username" name="username" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">비밀번호</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <button type="submit" class="btn btn-primary">로그인</button>
    </form>
    <div class="text-center mt-3">
      <a href="register.php">회원가입이 필요하신가요?</a>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>
