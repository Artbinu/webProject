<?php
session_start();
require_once 'db.php';

if (!isset($_GET['post_id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='main.php';</script>";
    exit;
}

$post_id = (int)$_GET['post_id'];

// 조회수 증가
$conn->query("UPDATE Post SET views = views + 1 WHERE post_id = $post_id");

// 게시글 정보 조회
$sql = "SELECT p.*, u.name AS author_name, c.cate_name
        FROM Post p
        JOIN Users u ON p.user_id = u.ID
        JOIN Category c ON p.category_id = c.category_id
        WHERE p.post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "<script>alert('존재하지 않는 게시글입니다.'); location.href='main.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>게시글 보기</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">게시글 보기</div>
    <div class="d-flex align-items-center">
      <a href="main.php" class="btn btn-outline-primary btn-sm">메인</a>
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="container mt-4">
    <div class="post-box">
      <h4><?= htmlspecialchars($post['title']) ?></h4>
      <div class="text-muted">
        작성자: <?= htmlspecialchars($post['author_name']) ?> |
        카테고리: <?= htmlspecialchars($post['cate_name']) ?> |
        작성일: <?= $post['created_at'] ?> |
        조회수: <?= $post['views'] + 1 ?>
      </div>
      <div class="mt-3"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
    </div>

    <!-- 글 작성자만 수정/삭제 버튼 노출 -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $post['user_id']): ?>
      <div class="mt-3">
        <a href="post_edit.php?post_id=<?= $post_id ?>" class="btn btn-warning btn-sm">수정</a>
        <a href="post_delete.php?post_id=<?= $post_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
      </div>
    <?php endif; ?>

    <!-- 댓글 작성 영역 (로그인 사용자 전용) -->
    <div class="mt-4">
      <h5>댓글</h5>
      <?php if (isset($_SESSION['user_id'])): ?>
        <form action="comment_submit.php" method="post">
          <input type="hidden" name="post_id" value="<?= $post_id ?>">
          <textarea name="comment" class="form-control" rows="3" required></textarea>
          <button type="submit" class="btn btn-primary mt-2">댓글 작성</button>
        </form>
      <?php else: ?>
        <div class="alert alert-info mt-2">댓글을 작성하려면 <a href="login.php">로그인</a>이 필요합니다.</div>
      <?php endif; ?>
    </div>
    
    <!-- 댓글 출력 영역 -->
    <div class="mt-4">
    <h5>댓글 목록</h5>

    <?php
    $stmt = $conn->prepare("
        SELECT c.comment_id, c.comment, c.created_date, u.name 
        FROM Comment c
        JOIN Users u ON c.user_id = u.ID
        WHERE c.post_id = ?
        ORDER BY c.created_date DESC
    ");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $comments = $stmt->get_result();

    if ($comments->num_rows > 0):
        while ($comment = $comments->fetch_assoc()):
    ?>
        <div class="mb-3 p-3 border rounded">
        <div><strong><?= htmlspecialchars($comment['name']) ?></strong> | <?= $comment['created_date'] ?></div>
        <div class="mt-2"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>

        <?php
        // 댓글 작성자와 로그인 유저가 같을 때만 삭제 버튼 노출
        if (isset($_SESSION['user_id'])) {
            $check_stmt = $conn->prepare("SELECT user_id FROM Users WHERE user_id = ?");
            $check_stmt->bind_param("s", $_SESSION['user_id']);
            $check_stmt->execute();
            $user_check = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if ($user_check && $user_check['user_id'] === $_SESSION['user_id']) {
                echo '<form method="post" action="comment_delete.php" class="mt-2">
                        <input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '">
                        <input type="hidden" name="post_id" value="' . $post_id . '">
                        <button type="submit" class="btn btn-sm btn-danger">삭제</button>
                        </form>';
            }
        }
        ?>
        </div>
    <?php
        endwhile;
    else:
        echo "<p>댓글이 없습니다.</p>";
    endif;
    $stmt->close();
    ?>
    </div>
  </div>
</body>
</html>
