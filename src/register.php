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

    // 생년월일 조합
    $birth_year = $_POST["birth_year"];
    $birth_month = $_POST["birth_month"];
    $birth_day = $_POST["birth_day"];
    $birth = "$birth_year-$birth_month-$birth_day";

    // 유효성 검사
    if (empty($user_id) || empty($name) || empty($email) || empty($birth_year) || empty($birth_month) || empty($birth_day)) {
        $errors[] = "모든 항목을 입력해주세요.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "유효하지 않은 이메일 형식입니다.";
    }

    if ($_POST["password"] !== $confirm_password) {
        $errors[] = "비밀번호가 일치하지 않습니다.";
    }

    if (!checkdate((int)$birth_month, (int)$birth_day, (int)$birth_year)) {
        $errors[] = "유효하지 않은 생년월일입니다.";
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
  <link href="footer.css" rel="stylesheet" />
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
        <label class="form-label">생년월일</label>
        <div class="d-flex gap-2">
          <select class="form-select" name="birth_year" required>
            <option value="">년</option>
            <?php
              $current_year = date("Y");
              for ($i = $current_year; $i >= 1900; $i--) {
                  echo "<option value=\"$i\">$i</option>";
              }
            ?>
          </select>

          <select class="form-select" name="birth_month" required>
            <option value="">월</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
              <option value="<?= sprintf('%02d', $i) ?>"><?= $i ?></option>
            <?php endfor; ?>
          </select>

          <select class="form-select" name="birth_day" required>
            <option value="">일</option>
            <?php for ($i = 1; $i <= 31; $i++): ?>
              <option value="<?= sprintf('%02d', $i) ?>"><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">이메일</label>
        <input type="email" class="form-control" id="email" name="email" required/>
      </div>
      <button type="submit" class="btn btn-primary">회원가입</button>
    </form>

    <div class="text-center mt-3">
      <a href="login.php">로그인이 필요하신가요?</a>
    </div>
  </div>

<?php include 'footer.php'; ?>
</body>
</html>