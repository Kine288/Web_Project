<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- XỬ LÝ 1: TẠO DỰ ÁN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_project'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO projects (title, description, is_approved) VALUES (?, ?, 1)"); 
    $stmt->bind_param("ss", $title, $desc);
    
    if ($stmt->execute()) {
        $new_project_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, 'owner')");
        $stmt2->bind_param("ii", $user_id, $new_project_id);
        $stmt2->execute();
        logActivity($conn, $user_id, "Đã tạo dự án mới: $title");
        echo "<script>alert('Tạo dự án thành công!'); window.location.href='dashboard.php';</script>";
    }
}

// --- XỬ LÝ 2: GỬI YÊU CẦU THAM GIA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_access'])) {
    $pj_id = $_POST['project_id'];
    $check = $conn->prepare("SELECT id FROM project_requests WHERE user_id = ? AND project_id = ?");
    $check->bind_param("ii", $user_id, $pj_id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO project_requests (user_id, project_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $pj_id);
        $stmt->execute();
        echo "<script>alert('Đã gửi yêu cầu! Chờ duyệt.'); window.location.href='dashboard.php';</script>";
    }
}

// --- XỬ LÝ 3: DUYỆT / TỪ CHỐI YÊU CẦU (NÂNG CẤP: CHỌN QUYỀN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['handle_request'])) {
    $req_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approve' hoặc 'reject'
    
    // Lấy thông tin request
    $stmt_info = $conn->prepare("SELECT user_id, project_id FROM project_requests WHERE id = ?");
    $stmt_info->bind_param("i", $req_id);
    $stmt_info->execute();
    $req_data = $stmt_info->get_result()->fetch_assoc();
    
    if ($req_data) {
        $target_user = $req_data['user_id'];
        $target_project = $req_data['project_id'];
        
        // Kiểm tra quyền Owner
        $check_owner = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ? AND role = 'owner'");
        $check_owner->bind_param("ii", $user_id, $target_project);
        $check_owner->execute();
        
        if ($check_owner->get_result()->num_rows > 0) {
            if ($action == 'approve') {
                // Lấy quyền được chọn từ Form (Mặc định là contributor nếu lỗi)
                $assigned_role = isset($_POST['role']) ? $_POST['role'] : 'contributor';
                
                // Validate role để bảo mật (Không cho phép hack thành owner)
                if (!in_array($assigned_role, ['viewer', 'contributor'])) {
                    $assigned_role = 'contributor';
                }

                // 1. Update request status
                $conn->query("UPDATE project_requests SET status = 'approved' WHERE id = $req_id");
                
                // 2. Insert member với quyền đã chọn
                $stmt_add = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, ?)");
                $stmt_add->bind_param("iis", $target_user, $target_project, $assigned_role);
                $stmt_add->execute();
                
                logActivity($conn, $user_id, "Đã duyệt user $target_user vào dự án $target_project với quyền $assigned_role");
                echo "<script>alert('Đã duyệt thành viên với quyền: " . ucfirst($assigned_role) . "');</script>";
            } else {
                $conn->query("UPDATE project_requests SET status = 'rejected' WHERE id = $req_id");
                echo "<script>alert('Đã từ chối yêu cầu.');</script>";
            }
        }
    }
}

// --- XỬ LÝ 4: COMMENT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $pj_id = $_POST['project_id'];
    $content = htmlspecialchars($_POST['content']);
    $stmt = $conn->prepare("SELECT role FROM project_members WHERE user_id=? AND project_id=?");
    $stmt->bind_param("ii", $user_id, $pj_id);
    $stmt->execute();
    $role_data = $stmt->get_result()->fetch_assoc();
    if ($role_data && in_array($role_data['role'], ['owner', 'moderator', 'contributor'])) {
        $stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, content, is_approved) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("iis", $pj_id, $user_id, $content);
        $stmt->execute();
        header("Location: dashboard.php");
        exit;
    }
}

// --- LẤY DỮ LIỆU ---
$projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$my_roles = [];
$stmt = $conn->prepare("SELECT project_id, role FROM project_members WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $my_roles[$row['project_id']] = $row['role'];

$my_requests = [];
$stmt = $conn->prepare("SELECT project_id, status FROM project_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $my_requests[$row['project_id']] = $row['status'];

// Lấy danh sách chờ duyệt (Pending Requests)
$sql_pending = "SELECT r.id as req_id, r.created_at, u.email, p.title 
                FROM project_requests r
                JOIN projects p ON r.project_id = p.id
                JOIN users u ON r.user_id = u.id
                JOIN project_members pm ON p.id = pm.project_id
                WHERE pm.user_id = ? AND pm.role = 'owner' AND r.status = 'pending'";
$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pending_requests = $stmt_pending->get_result()->fetch_all(MYSQLI_ASSOC);

$comments = $conn->query("SELECT c.*, u.email FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .role-badge { font-size: 0.8em; }
        .project-card { transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .project-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .comment-box { background: #f1f3f5; border-radius: 10px; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-layer-group"></i> Project Manager</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['user_email']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-light text-primary fw-bold">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <?php if (!empty($pending_requests)): ?>
            <div class="card mb-4 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fa-solid fa-bell"></i> Có <?= count($pending_requests) ?> yêu cầu tham gia dự án
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Người yêu cầu</th>
                                <th>Xin vào dự án</th>
                                <th>Thời gian</th>
                                <th class="text-end pe-3" style="width: 350px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_requests as $req): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= htmlspecialchars($req['email']) ?></td>
                                    <td><?= htmlspecialchars($req['title']) ?></td>
                                    <td><?= $req['created_at'] ?></td>
                                    <td class="text-end pe-3">
                                        <form method="POST" class="d-flex justify-content-end align-items-center gap-2">
                                            <input type="hidden" name="handle_request" value="1">
                                            <input type="hidden" name="request_id" value="<?= $req['req_id'] ?>">
                                            
                                            <select name="role" class="form-select form-select-sm" style="width: auto;">
                                                <option value="viewer">Viewer (Chỉ xem)</option>
                                                <option value="contributor" selected>Contributor (Đóng góp)</option>
                                                <option value="moderator">Moderator (Điều hành)</option>
                                            </select>

                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-check"></i> Duyệt
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm" onclick="return confirm('Từ chối?');">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="card mb-5 border-success border-2 shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fa-solid fa-plus-circle"></i> Tạo Dự Án Mới
            </div>
            <div class="card-body bg-white">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="create_project" value="1">
                    <div class="col-md-4">
                        <input type="text" name="title" class="form-control" required placeholder="Tên dự án...">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="description" class="form-control" required placeholder="Mô tả nội dung dự án...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Tạo Ngay</button>
                    </div>
                </form>
            </div>
        </div>

        <h4 class="mb-3 text-secondary"><i class="fa-solid fa-list"></i> Danh Sách Dự Án</h4>
        <?php if (empty($projects)): ?>
            <div class="alert alert-info text-center">Chưa có dự án nào.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($projects as $pj): ?>
                    <?php 
                        $pj_id = $pj['id'];
                        $my_role = isset($my_roles[$pj_id]) ? $my_roles[$pj_id] : null;
                        $can_access = ($my_role == 'owner' || $my_role == 'moderator' || $my_role == 'contributor');
                        $request_status = isset($my_requests[$pj_id]) ? $my_requests[$pj_id] : null;

                        $badge_class = 'bg-secondary';
                        if($my_role == 'owner') $badge_class = 'bg-danger';
                        if($my_role == 'moderator') $badge_class = 'bg-primary';
                        if($my_role == 'contributor') $badge_class = 'bg-info text-dark';
                    ?>

                    <div class="col-12 mb-4">
                        <div class="card project-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="card-title text-primary m-0">
                                        <i class="fa-solid fa-folder-open"></i> <?= htmlspecialchars($pj['title']) ?>
                                    </h4>
                                    <?php if ($my_role): ?>
                                        <span class="badge rounded-pill <?= $badge_class ?> role-badge"><?= ucfirst($my_role) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($can_access): ?>
                                    <div class="mt-3">
                                        <div class="p-3 bg-light rounded border mb-3">
                                            <strong><i class="fa-solid fa-align-left"></i> Mô tả:</strong> <?= htmlspecialchars($pj['description']) ?>
                                        </div>
                                        <div class="comment-box">
                                            <?php 
                                            $has_cmt = false;
                                            foreach ($comments as $cmt): 
                                                if ($cmt['project_id'] == $pj_id): $has_cmt = true;
                                            ?>
                                                <div class="border-bottom py-2">
                                                    <strong class="text-dark"><?= htmlspecialchars($cmt['email']) ?>:</strong> 
                                                    <span class="text-secondary"><?= htmlspecialchars($cmt['content']) ?></span>
                                                </div>
                                            <?php endif; endforeach; 
                                            if(!$has_cmt) echo "<span class='text-muted small'>Chưa có bình luận.</span>";
                                            ?>
                                        </div>
                                        <form method="POST" class="mt-3 d-flex">
                                            <input type="hidden" name="add_comment" value="1">
                                            <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                            <input type="text" name="content" class="form-control me-2" required placeholder="Viết bình luận...">
                                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="access-denied mt-3 d-flex flex-column align-items-center">
                                        <i class="fa-solid fa-lock fa-2x mb-2"></i>
                                        <strong class="mb-2">Nội dung bảo mật</strong>
                                        <?php if ($request_status == 'pending'): ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Đã gửi yêu cầu - Chờ duyệt</button>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="request_access" value="1">
                                                <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                                <button type="submit" class="btn btn-warning btn-sm fw-bold">Gửi yêu cầu tham gia</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>