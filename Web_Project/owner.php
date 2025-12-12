<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Lấy danh sách tất cả người dùng từ bảng users
$sql_users = "SELECT * FROM users";
$stmt = $conn->prepare($sql_users);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Truy vấn lấy tất cả các dự án đã duyệt của người dùng cụ thể
$sql_projects = "
    SELECT p.id AS project_id, p.title, pm.role     
    FROM project_members pm
    JOIN projects p ON pm.project_id = p.id
    WHERE pm.user_id = ?";  

$stmt = $conn->prepare($sql_projects);
$stmt->bind_param("i", $user_id);  // Gắn user_id vào truy vấn
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Truy vấn lấy tất cả các dự án đã duyệt của người dùng cụ thể
$sql_projects_owner = "
    SELECT p.id AS project_id, p.title, pm.role     
    FROM project_members pm
    JOIN projects p ON pm.project_id = p.id
    WHERE pm.user_id = ? AND pm.role = 'owner'";  

$stmt = $conn->prepare($sql_projects_owner);
$stmt->bind_param("i", $user_id);  // Gắn user_id vào truy vấn
$stmt->execute();
$projects_owner = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách bình luận chưa duyệt
$sql_comments = "SELECT c.id, c.content, c.project_id, c.user_id, u.email FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.is_approved = 0";
$stmt = $conn->prepare($sql_comments);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách bình luận đã duyệt hoặc từ chối
$sql_approved_comments = "SELECT c.id, c.content, c.project_id, c.user_id, u.email, c.is_approved FROM comments c
                          JOIN users u ON c.user_id = u.id
                          WHERE c.is_approved IN (1, 0)";
$stmt = $conn->prepare($sql_approved_comments);
$stmt->execute();
$approved_comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Nếu có hành động thay đổi quyền, xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['project_id']) && isset($_POST['role'])) {
    $user_id = $_POST['user_id'];
    $project_id = $_POST['project_id'];
    $role = $_POST['role'];

    // Cập nhật vai trò của người dùng trong dự án
    $stmt = $conn->prepare("UPDATE project_members SET role = ? WHERE user_id = ? AND project_id = ?");
    $stmt->bind_param("sii", $role, $user_id, $project_id);
    $stmt->execute();
    echo "Cập nhật quyền thành công!";
}



// Thêm người dùng vào dự án
if (isset($_POST['add_user_to_project'])) {
    $email = $_POST['email'];
    $project_id = $_POST['project_id'];
    $role = $_POST['role'];

    // Lấy user_id từ email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $user_id = $user['id'];

        // Kiểm tra người dùng đã là thành viên trong dự án chưa
        $stmt = $conn->prepare("SELECT * FROM project_members WHERE user_id = ? AND project_id = ?");
        $stmt->bind_param("ii", $user_id, $project_id);
        $stmt->execute();
        $existing_member = $stmt->get_result()->fetch_assoc();

        if ($existing_member) {
            echo "Người dùng này đã là thành viên của dự án.";
        } else {
            // Thêm người dùng vào dự án với vai trò
            $stmt = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $project_id, $role);
            $stmt->execute();

            echo "Người dùng đã được thêm vào dự án với vai trò: $role";
        }
    } else {
        echo "Không tìm thấy người dùng với email này.";
    }
}

// Xử lý xóa dự án
if (isset($_POST['delete_project'])) {
    $project_id = $_POST['project_id'];

    // Kiểm tra nếu người dùng là chủ sở hữu của dự án
    $stmt = $conn->prepare("SELECT * FROM project_members WHERE project_id = ? AND user_id = ? AND role = 'owner'");
    $stmt->bind_param("ii", $project_id, $_SESSION['user_id']);
    $stmt->execute();
    $owner_check = $stmt->get_result()->fetch_assoc();

    if ($owner_check) {
        // Xóa dự án
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();

        echo "Dự án đã được xóa thành công!";
    } else {
        echo "Bạn không có quyền xóa dự án này.";
    }
}

// Xử lý thêm ghi chú
if (isset($_POST['add_note'])) {
    $content = $_POST['content'];
    $project_id = $_POST['project_id'];

    // Thêm ghi chú vào cơ sở dữ liệu
    $stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, content, is_approved) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iis", $project_id, $_SESSION['user_id'], $content);
    $stmt->execute();

    echo "Ghi chú đã được thêm thành công!";
}

// Duyệt hoặc từ chối ghi chú
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_id']) && isset($_POST['action'])) {
    $comment_id = $_POST['comment_id'];
    $action = $_POST['action']; // 'approve' hoặc 'reject'
    $is_approved = ($action == 'approve') ? 1 : 0;

    // Cập nhật trạng thái duyệt bình luận
    $stmt = $conn->prepare("UPDATE comments SET is_approved = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_approved, $comment_id);
    $stmt->execute();
    echo "Cập nhật trạng thái bình luận thành công!";
}
?>

<a href="create_project.php">Tạo dự án mới.</a>


<h1>Thêm Người Dùng vào Dự Án của bạn.</h1>

    <label for="email">Email Người Dùng:</label>
    <input type="email" name="email" required>
    <br>

    <label for="project_id">Chọn Dự Án:</label>
    <select name="project_id" required>
        <?php foreach ($projects_owner as $project): ?>
            <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <br>

    <label for="role">Vai Trò:</label>
    <select name="role" required>
        <option value="viewer">Người Xem</option>
        <option value="contributor">Người Đóng Góp</option>
        <option value="moderator">Người Điều Hành</option>
    </select>
    <br>

    <button type="submit" name="add_user_to_project">Thêm Người Dùng</button>
</form>

<h1>Xóa Dự Án</h1>
<form method="POST">
    <label for="project_id">Chọn Dự Án để Xóa:</label>
    <select name="project_id" required>
        <?php foreach ($projects_owner as $project): ?>
            <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <br>

    <button type="submit" name="delete_project">Xóa Dự Án</button>
</form>

<h1>Quản Lý Ghi Chú</h1>
<form method="POST">
    <label for="content">Nội Dung Ghi Chú:</label>
    <textarea name="content" rows="4" required></textarea>
    <br>

    <label for="project_id">Chọn Dự Án:</label>
    <select name="project_id" required>
        <?php foreach ($projects_owner as $project): ?>
            <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <br>

    <button type="submit" name="add_note">Thêm Ghi Chú</button>
</form>

<h1>Cập Nhật Trạng Thái Ghi Chú</h1>
<table>
    <thead>
        <tr>
            <th>Ghi Chú</th>
            <th>Người Dùng</th>
            <th>Trạng Thái</th>
            <th>Hành Động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($approved_comments as $comment): ?>
            <tr>
                <td><?= htmlspecialchars($comment['content']) ?></td>
                <td><?= htmlspecialchars($comment['email']) ?></td>
                <td><?= $comment['is_approved'] == 1 ? 'Duyệt' : 'Từ chối' ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" name="action" value="approve">Duyệt</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" name="action" value="reject">Từ chối</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h1>Các Dự Án Bạn Tham Gia</h1>
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: center;">
    <thead>
        <tr>
            <th>Tên Dự Án</th>
            <th>Vai Trò Của Bạn</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= htmlspecialchars($project['role']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">Bạn chưa tham gia dự án nào.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<a href="logout.php">Đăng xuất</a>

<style>
    /* Cơ bản: Thiết lập font và khoảng cách */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7fc;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h1 {
        color: #5A9BFF;
        text-align: center;
        padding: 20px 0;
    }

    h2 {
        color: #5A9BFF;
        padding: 10px 0;
    }

    /* Khung chứa form */
    form {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        margin: 20px auto;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
    }

    input, textarea, select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 14px;
    }

    button {
        background-color: #5A9BFF;
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #4a8fe7;
    }

    /* Bảng hiển thị dữ liệu */
    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f5f8fa;
        color: #555;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    /* Nút hành động trong bảng */
    button[type="submit"] {
        background-color: #28a745;
        margin-top: 5px;
        font-size: 14px;
        padding: 8px 12px;
        cursor: pointer;
    }

    button[type="submit"]:hover {
        background-color: #218838;
    }

    /* Nút xóa và sửa */
    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-buttons button {
        background-color: #ffc107;
        color: white;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        border-radius: 5px;
    }

    .action-buttons button:hover {
        background-color: #e0a800;
    }

    .action-buttons button.delete {
        background-color: #dc3545;
    }

    .action-buttons button.delete:hover {
        background-color: #c82333;
    }

    /* Các phần thông báo */
    .notification {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        background-color: #28a745;
        color: white;
        border-radius: 5px;
        display: none;
    }

    .notification.error {
        background-color: #dc3545;
    }

    /* Liên kết đăng xuất */
    a {
        display: block;
        text-align: center;
        margin-top: 20px;
        font-size: 18px;
        color: #007bff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
