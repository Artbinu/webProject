<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$post_id = (int)$_GET['post_id'] ?? 0;

// 로그인 사용자 DB ID 조회
$stmt = $conn->prepare("SELECT ID FROM Users WHERE user_id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<script>alert('잘못된 사용자입니다.'); location.href='main.php';</script>";
    exit;
}
$user_db_id = $user['ID'];

// 게시글 가져오기
$stmt = $conn->prepare("SELECT * FROM Post WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_db_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "<script>alert('수정 권한이 없습니다.'); location.href='main.php';</script>";
    exit;
}

// 수정 처리
$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = (int)$_POST['category_id'];

    if (!$title || !$content || !$category_id) {
        $errors[] = "모든 필드를 입력해 주세요.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE Post SET title = ?, content = ?, category_id = ? WHERE post_id = ?");
        $stmt->bind_param("ssii", $title, $content, $category_id, $post_id);
        if ($stmt->execute()) {
            header("Location: post_view.php?post_id=" . $post_id);
            exit;
        } else {
            $errors[] = "수정 중 오류가 발생했습니다.";
        }
        $stmt->close();
    }
}

// 카테고리 목록
$categories = $conn->query("SELECT * FROM Category ORDER BY category_id");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>게시글 수정</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">게시글 수정</div>
    <div class="d-flex align-items-center">
      <a href="main.php" class="btn btn-outline-primary btn-sm">메인</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="form-container">
    <div class="form-title">게시글 수정</div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><?= implode("<br>", array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">카테고리</label>
        <select name="category_id" class="form-control" required>
          <?php while ($row = $categories->fetch_assoc()): ?>
            <option value="<?= $row['category_id'] ?>" <?= $row['category_id'] == $post['category_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($row['cate_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">제목</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required />
      </div>
      <div class="mb-3">
        <label class="form-label">내용</label>
        <textarea name="content" class="form-control" rows="10" required><?= htmlspecialchars($post['content']) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">수정 완료</button>
    </form>
  </div>
</body>
</html>
