<?php
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $confirm_password = $_POST["confirm_password"];
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $birth = $_POST["birth"];

    // 유효성 검사
    if (empty($user_id) || empty($name) || empty($email) || empty($birth)) {
        $errors[] = "모든 항목을 입력해주세요.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "유효하지 않은 이메일 형식입니다.";
    }

    if ($_POST["password"] !== $confirm_password) {
        $errors[] = "비밀번호가 일치하지 않습니다.";
    }

    // 아이디 중복 검사
    $check_sql = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "이미 존재하는 아이디입니다.";
    }

    // 오류 없을 시 회원 등록
    if (empty($errors)) {
        $insert_sql = "INSERT INTO Users (role_code, user_id, password, name, email, birth) VALUES (1, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssss", $user_id, $password, $name, $email, $birth);

        if ($stmt->execute()) {
            $success = "회원가입이 완료되었습니다. 로그인해 주세요.";
        } else {
            $errors[] = "회원가입 중 오류가 발생했습니다.";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>회원가입</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">Digital Security</div>
  </div>

  <div class="form-container">
    <div class="form-title">회원가입</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?= implode("<br>", array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php elseif (!empty($success)): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form action="register.php" method="post">
      <div class="mb-3">
        <label for="username" class="form-label">아이디</label>
        <input type="text" class="form-control" id="username" name="username" required/>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">비밀번호</label>
        <input type="password" class="form-control" id="password" name="password" required />
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">비밀번호 확인</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
      </div>
      <div class="mb-3">
        <label for="name" class="form-label">이름</label>
        <input type="text" class="form-control" id="name" name="name" required/>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">이메일</label>
        <input type="email" class="form-control" id="email" name="email" required/>
      </div>
      <div class="mb-3">
        <label for="birth" class="form-label">생년월일</label>
        <input type="date" class="form-control" id="birth" name="birth" required/>
      </div>
      <button type="submit" class="btn btn-primary">회원가입</button>
    </form>
    <div class="text-center mt-3">
      <a href="login.php">로그인이 필요하신가요?</a>
    </div>
  </div>
</body>
</html>
