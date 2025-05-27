<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 9) {
    echo "<script>alert('관리자 전용입니다.'); location.href='main.php';</script>";
    exit;
}

// 카테고리 추가 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $cate_name = trim($_POST['new_category']);
    if ($cate_name !== '') {
        $stmt = $conn->prepare("INSERT INTO Category (cate_name) VALUES (?)");
        $stmt->bind_param("s", $cate_name);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_category.php");
        exit;
    }
}

// 카테고리 삭제 처리
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Category WHERE category_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_category.php");
    exit;
}

// 카테고리 목록 조회
$categories = $conn->query("SELECT * FROM Category ORDER BY category_id");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>카테고리 관리</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
  <link href="footer.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">카테고리 관리</div>
    <div class="d-flex align-items-center">
      <a href="admin.php" class="btn btn-outline-primary btn-sm">관리자 홈</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="container mt-4">
    <h4>카테고리 목록</h4>
    <ul class="list-group mb-4">
      <?php while ($row = $categories->fetch_assoc()): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?= htmlspecialchars($row['cate_name']) ?>
          <a href="admin_category.php?delete=<?= $row['category_id'] ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('이 카테고리를 삭제하시겠습니까?');">삭제</a>
        </li>
      <?php endwhile; ?>
    </ul>

    <form method="post" class="d-flex gap-2">
      <input type="text" name="new_category" class="form-control" placeholder="새 카테고리 이름 입력" required>
      <button type="submit" class="btn btn-primary">추가</button>
    </form>
  </div>
<?php include 'footer.php'; ?>
</body>
</html>
