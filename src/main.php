<?php session_start(); ?>
<?php
require_once 'db.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// 게시글 수 카운트
if ($category_id) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM Post WHERE category_id = ?");
    $count_stmt->bind_param("i", $category_id);
    $count_stmt->execute();
    $count_stmt->bind_result($total_posts);
    $count_stmt->fetch();
    $count_stmt->close();
} else {
    $result_count = $conn->query("SELECT COUNT(*) AS cnt FROM Post");
    $total_posts = $result_count->fetch_assoc()['cnt'];
}

$total_pages = ceil($total_posts / $limit);

// 게시글 목록 쿼리
if ($category_id) {
    $stmt = $conn->prepare("SELECT p.*, u.name AS author_name, c.cate_name
                            FROM Post p
                            JOIN Users u ON p.user_id = u.ID
                            JOIN Category c ON p.category_id = c.category_id
                            WHERE p.category_id = ?
                            ORDER BY p.created_at DESC
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $category_id, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT p.*, u.name AS author_name, c.cate_name
                            FROM Post p
                            JOIN Users u ON p.user_id = u.ID
                            JOIN Category c ON p.category_id = c.category_id
                            ORDER BY p.created_at DESC
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>웹 애플리케이션 메인 페이지</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <!-- ✅ 상단 바 -->
  <div class="top-bar">
    <div class="d-flex align-items-center">
      <div class="hamburger" id="hamburger">☰</div>
    </div>
    <div class="center-text">Digital Security</div>
    <div class="d-flex align-items-center">

      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['role'] == 9): ?>
          <a href="admin.php" class="btn btn-outline-primary btn-sm">관리자 페이지</a>
        <?php else: ?>
          <a href="mypage.php" class="btn btn-outline-primary btn-sm">마이페이지</a>
        <?php endif; ?>
        <a href="post_write.php" class="btn btn-success btn-sm">글쓰기</a>
        <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary btn-sm">로그인</a>
        <a href="register.php" class="btn btn-secondary btn-sm">회원가입</a>
      <?php endif; ?>

    </div>
  </div>

  <!-- ✅ 사이드바 -->
  <div class="category-sidebar" id="categorySidebar">
    <div class="list-group">
      <a href="main.php?category=0" class="list-group-item">전체</a>
      <a href="main.php?category=1" class="list-group-item">공지사항</a>
      <a href="main.php?category=2" class="list-group-item">자유게시판</a>
      <a href="main.php?category=3" class="list-group-item">질문답변</a>
      <a href="main.php?category=4" class="list-group-item">기타</a>
    </div>
  </div>

  <!-- ✅ 본문 -->
  <div class="container mt-4">
    <form action="/search.php" method="get" class="search-bar">
      <input type="text" name="query" class="form-control" placeholder="게시글 검색...">
    </form>

    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="post-box">
      <div class="post-title"><?= htmlspecialchars($row['title']) ?></div>
      <div class="text-muted">
        작성자: <?= htmlspecialchars($row['author_name']) ?> |
        카테고리: <?= htmlspecialchars($row['cate_name']) ?> |
        작성시간: <?= $row['created_at'] ?> |
        조회수: <?= $row['views'] ?? 0 ?>
      </div>
      <div class="mt-2"><?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 150))) ?>...</div>
    </div>
    <?php endwhile; ?>
  </div>

    <!-- 페이지 번호 -->
    <div class="page-number mt-4">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="main.php?page=<?= $i ?><?= $category_id ? "&category=$category_id" : '' ?>">
        <span class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></span>
        </a>
    <?php endfor; ?>
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
