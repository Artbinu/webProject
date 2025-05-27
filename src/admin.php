<?php
session_start();
require_once 'db.php';

// 관리자 권한 확인
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 9) {
    echo "<script>alert('관리자 전용입니다.'); location.href='main.php';</script>";
    exit;
}

$sql = "SELECT ID, user_id, name, email, role_code FROM Users ORDER BY created_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>관리자 페이지</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="style.css" rel="stylesheet" />
  <link href="footer.css" rel="stylesheet" />
</head>
<body>
  <div class="top-bar">
    <div class="center-text">Digital Security - 관리자</div>
    <div class="d-flex align-items-center">
      <a href="logout.php" class="btn btn-secondary btn-sm">로그아웃</a>
    </div>
  </div>

  <div class="container mt-4">
    <h3>회원 목록</h3>
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>아이디</th>
          <th>이름</th>
          <th>이메일</th>
          <th>등급</th>
          <th>관리</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['ID'] ?></td>
          <td><?= htmlspecialchars($row['user_id']) ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= $row['role_code'] == 9 ? "관리자" : "일반회원" ?></td>
          <td>
            <a href="admin_edit.php?id=<?= $row['ID'] ?>" class="btn btn-sm btn-warning">수정</a>
            <a href="admin_block.php?id=<?= $row['ID'] ?>" class="btn btn-sm btn-danger">차단</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>
