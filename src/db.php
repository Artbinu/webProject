<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'board_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB 연결 실패: " . $conn->connect_error);
}
?>
