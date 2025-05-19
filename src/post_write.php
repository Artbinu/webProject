<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// 차단 여부 검사
$sql = "SELECT ID, blocked_until FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['blocked_until'] && strtotime($user['blocked_until']) > time()) {
    echo "<script>alert('현재 차단된 사용자입니다. 게시글 작성은 1일 후 가능합니다.'); history.back();</script>";
    exit;
}

$user_db_id = $user['ID'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = $_POST['category_id'];

    if (!$title || !$content || !$category_id) {
        $errors[] = "모든 필드를 입력해야 합니다.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO Post (user_id, category_id, title, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_db_id, $category_id, $title, $content);
        if ($stmt->execute()) {
            header("Location: main.php");
            exit;
        } else {
            $errors[] = "게시글 등록 중 오류 발생";
        }
        $stmt->close();
    }
}

// 카테고리 조회
$categories = $conn->query("SELECT * FROM Category ORDER BY category_id");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>글쓰기</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">글쓰기</div>
    <div class="d-flex align-items-center">
      <a href="main.php" class="btn btn-outline-primary btn-sm">메인</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="form-container">
    <div class="form-title">게시글 작성</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?= implode("<br>", array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">카테고리</label>
        <select name="category_id" class="form-control" required>
          <option value="">선택</option>
          <?php while ($row = $categories->fetch_assoc()): ?>
            <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['cate_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">제목</label>
        <input type="text" name="title" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label">내용</label>
        <textarea name="content" class="form-control" rows="10" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">작성 완료</button>
    </form>
  </div>
</body>
</html>
