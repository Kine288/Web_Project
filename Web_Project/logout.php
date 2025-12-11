<?php
// Khởi tạo session
session_start();

// Xóa tất cả dữ liệu trong session
session_unset();

// Hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập (hoặc trang khác bạn muốn)
header("Location: login.php");
exit();
?>
