<?php
$localhost = 'localhost';
$username = 'root';
$password = '0835072866';
$db = 'spck';
$conn = mysqli_connect($localhost, $username, $password, $db);

if(!$conn)
{
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');

// Hàm ghi nhật ký hoạt động
function logActivity($conn, $user_id, $action) {
    if ($conn && $user_id) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $action);
        $stmt->execute();
        $stmt->close();
    }
}
?>