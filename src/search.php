<?php
session_start();
require_once 'db.php';

$query = trim($_GET['query'] ?? '');
$search_results = [];

if ($query !== '') {
    $sql = "SELECT p.*, u.name AS author_name, c.cate_name
            FROM Post p
            JOIN Users u ON p.user_id = u.ID
            JOIN Category c ON p.category_id = c.category_id
            WHERE p.title LIKE ? OR p.content LIKE ?
            ORDER BY p.created_at DESC";

    $likeQuery = "%{$query}%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $search_results = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>검색 결과</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
  <link href="footer.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">검색 결과</div>
    <div class="d-flex align-items-center">
      <a href="main.php" class="btn btn-outline-primary btn-sm">메인으로</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="container mt-4">
    <h4>"<?= htmlspecialchars($query) ?>" 검색 결과</h4>

    <?php if ($search_results && $search_results->num_rows > 0): ?>
      <?php while ($row = $search_results->fetch_assoc()): ?>
        <div class="post-box">
          <div class="post-title">
            <a href="post_view.php?post_id=<?= $row['post_id'] ?>">
              <?= htmlspecialchars($row['title']) ?>
            </a>
          </div>
          <div class="text-muted">
            작성자: <?= htmlspecialchars($row['author_name']) ?> |
            카테고리: <?= htmlspecialchars($row['cate_name']) ?> |
            작성일: <?= $row['created_at'] ?> |
            조회수: <?= $row['views'] ?>
          </div>
          <div class="mt-2">
            <?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 100))) ?>...
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="mt-3">검색 결과가 없습니다.</p>
    <?php endif; ?>
  </div>
<?php include 'footer.php'; ?>
</body>
</html>
