<?php
session_start();
require 'config.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// 1. Khóa / mở tài khoản
if (isset($_POST['toggle_status'])) {
    $uid = $_POST['user_id'];
    $new_stt = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stt, $uid);
    $stmt->execute();
    
    $act = ($new_stt == 0) ? "Đã KHÓA user ID $uid" : "Đã MỞ KHÓA user ID $uid";
    logActivity($conn, $_SESSION['user_id'], $act);
    echo "<script>alert('Cập nhật trạng thái thành công!');</script>";
}

// Lấy dl
$users = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
$logs = $conn->query("SELECT l.*, u.email FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
$projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-size: 0.95rem; }
        .admin-header { background: #343a40; color: white; padding: 15px 0; margin-bottom: 30px; }
        .card-table { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        .table thead th { background-color: #e9ecef; border-bottom: 2px solid #dee2e6; color: #555; }
        
        /* Style riêng cho phần dự án */
        .project-header { background: #212529; color: white; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; }
        .project-date { font-size: 0.8rem; color: #adb5bd; font-weight: normal; }
        .badge-role { width: 80px; text-align: center; }
    </style>
</head>
<body>
    
    <div class="admin-header shadow">
        <div class="container d-flex justify-content-between align-items-center">
            <h3 class="m-0"><i class="fa-solid fa-user-shield"></i> Quản Trị Hệ Thống</h3>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Đăng xuất</a>
        </div>
    </div>

    <div class="container">
        
        <div class="card card-table">
            <div class="card-header bg-white fw-bold text-primary py-3">
                <i class="fa-solid fa-users"></i> 1. Quản lý Người dùng
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge bg-secondary"><?= $u['role'] ?></span></td>
                                <td>
                                    <?= ($u['status'] == 1) ? '<span class="text-success fw-bold">Hoạt động</span>' : '<span class="text-danger fw-bold">Đã khóa</span>' ?>
                                </td>
                                <td>
                                    <?php if ($u['role'] != 'admin'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if ($u['status'] == 1): ?>
                                                <input type="hidden" name="new_status" value="0">
                                                <button type="submit" name="toggle_status" class="btn btn-outline-danger btn-sm py-0" onclick="return confirm('Khóa user này?');">Khóa</button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_status" value="1">
                                                <button type="submit" name="toggle_status" class="btn btn-outline-success btn-sm py-0">Mở</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h4 class="text-secondary mt-5 mb-3"><i class="fa-solid fa-folder-tree"></i> 2. Thông Tin Chi Tiết Dự Án</h4>
        
        <div class="row">
            <?php foreach ($projects as $pj): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="project-header rounded-top">
                            <span class="fw-bold"><i class="fa-solid fa-box-archive"></i> <?= htmlspecialchars($pj['title']) ?></span>
                            <span class="project-date">
                                <i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($pj['created_at'])) ?>
                            </span>
                        </div>
                        
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Thành viên</th>
                                        <th class="text-end pe-3">Vai trò</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Sắp xếp thành viên theo thứ tự vai trò
                                    $stmt = $conn->prepare("
                                        SELECT pm.*, u.email 
                                        FROM project_members pm 
                                        JOIN users u ON pm.user_id = u.id 
                                        WHERE pm.project_id = ? 
                                        ORDER BY FIELD(pm.role, 'owner', 'moderator', 'contributor', 'viewer')
                                    ");
                                    $stmt->bind_param("i", $pj['id']);
                                    $stmt->execute();
                                    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    if (count($members) > 0):
                                        foreach ($members as $mem):
                                            $badge_class = 'bg-secondary'; // Viewer
                                            $icon = '';
                                            
                                            if ($mem['role'] == 'owner') {
                                                $badge_class = 'bg-danger'; // Owner màu đỏ
                                                $icon = '<i class="fa-solid fa-crown text-warning me-1"></i>';
                                            } elseif ($mem['role'] == 'moderator') {
                                                $badge_class = 'bg-primary'; // Mod màu xanh dương
                                            } elseif ($mem['role'] == 'contributor') {
                                                $badge_class = 'bg-info text-dark'; // Contributor
                                            }
                                    ?>
                                            <tr>
                                                <td class="ps-3 align-middle">
                                                    <?= $icon ?><?= htmlspecialchars($mem['email']) ?>
                                                </td>
                                                <td class="text-end pe-3 align-middle">
                                                    <span class="badge <?= $badge_class ?> badge-role">
                                                        <?= ucfirst($mem['role']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php 
                                        endforeach; 
                                    else:
                                    ?>
                                        <tr><td colspan="2" class="text-center text-muted p-3">Chưa có thành viên nào</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-muted small">
                            <i class="fa-solid fa-align-left"></i> <?= htmlspecialchars(substr($pj['description'], 0, 80)) ?>...
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card card-table mt-4">
            <div class="card-header bg-white fw-bold text-secondary py-3">
                <i class="fa-solid fa-history"></i> 3. Nhật Ký Hệ Thống
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Người dùng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['created_at'] ?></td>
                                <td><strong><?= htmlspecialchars($log['email'] ?? 'Unknown') ?></strong></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>