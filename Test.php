<?php
// vulnerable.php - ví dụ minh họa lỗ hổng SQL Injection

// giả sử request: vulnerable.php?id=42
$id = $_GET['id'] ?? '';

// KẾT NỐI DB (mysqli procedural)
$mysqli = mysqli_connect('127.0.0.1', 'dbuser', 'dbpass', 'mydb');
if (!$mysqli) {
    die('DB connect error');
}

// DANGEROUS: chuỗi truy vấn ghép trực tiếp từ input
$sql = "SELECT * FROM users WHERE id = " . $id;

$result = mysqli_query($mysqli, $sql);
if (!$result) {
    die('Query error: ' . mysqli_error($mysqli));
}

while ($row = mysqli_fetch_assoc($result)) {
    echo 'User: ' . htmlspecialchars($row['username']) . "<br>";
}
