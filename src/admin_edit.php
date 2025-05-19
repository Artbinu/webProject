<?php
session_start();
require_once 'db.php';

// 관리자만 접근 가능
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 9) {
    echo "<script>alert('관리자 전용입니다.'); location.href='main.php';</script>";
    exit;
}

$errors = [];
$success = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='admin.php';</script>";
    exit;
}

// 정보 조회
$stmt = $conn->prepare("SELECT user_id, name, email, role_code FROM Users WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role_code = (int)$_POST['role_code'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "올바른 이메일 형식이 아닙니다.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, role_code = ? WHERE ID = ?");
        $stmt->bind_param("ssii", $name, $email, $role_code, $id);
        if ($stmt->execute()) {
            $success = "사용자 정보가 성공적으로 수정되었습니다.";
        } else {
            $errors[] = "수정 중 오류가 발생했습니다.";
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
  <title>사용자 수정</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">관리자 사용자 수정</div>
    <div class="d-flex align-items-center">
      <a href="admin.php" class="btn btn-outline-secondary btn-sm">관리자 홈</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="form-container">
    <div class="form-title">사용자 정보 수정</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?= implode("<br>", array_map('htmlspecialchars', $errors)) ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">아이디</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_id']) ?>" disabled>
      </div>
      <div class="mb-3">
        <label for="name" class="form-label">이름</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">이메일</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="role_code" class="form-label">등급</label>
        <select class="form-control" name="role_code">
          <option value="1" <?= $user['role_code'] == 1 ? 'selected' : '' ?>>일반회원</option>
          <option value="9" <?= $user['role_code'] == 9 ? 'selected' : '' ?>>관리자</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">수정 완료</button>
    </form>
  </div>
</body>
</html>
