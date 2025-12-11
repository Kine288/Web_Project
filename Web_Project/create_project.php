<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Thêm dự án vào cơ sở dữ liệu
    $stmt = $conn->prepare("INSERT INTO projects (title, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $description);
    if ($stmt->execute()) {
        // Lấy ID dự án mới
        $project_id = $stmt->insert_id;
        
        // Mặc định thêm người tạo làm chủ sở hữu của dự án
        $stmt2 = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, 'owner')");
        $stmt2->bind_param("ii", $user_id, $project_id);
        $stmt2->execute();
        
        echo "Dự án đã được tạo thành công!";
    } else {
        echo "Lỗi khi tạo dự án!";
    }
}
?>

<form method="POST">
    Tiêu đề dự án: <input type="text" name="title" required><br>
    Mô tả: <textarea name="description" required></textarea><br>
    <button type="submit">Tạo Dự Án</button>
    <a href="logout.php">Đăng xuất</a>
</form>
