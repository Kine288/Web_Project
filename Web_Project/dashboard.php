<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// 1. Tạo dự án
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_project'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO projects (title, description) VALUES (?, ?)"); 
    $stmt->bind_param("ss", $title, $desc);
    
    if ($stmt->execute()) {
        $new_project_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, 'owner')");
        $stmt2->bind_param("ii", $user_id, $new_project_id);
        $stmt2->execute();
        logActivity($conn, $user_id, "Đã tạo dự án mới: $title (ID $new_project_id)");
        echo "<script>alert('Tạo dự án thành công!'); window.location.href='dashboard.php';</script>";
    }
}

// 2. Yêu cầu tham gia dự án của người khác
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_access'])) {
    $pj_id = $_POST['project_id'];
    $check = $conn->prepare("SELECT id FROM project_requests WHERE user_id = ? AND project_id = ?");
    $check->bind_param("ii", $user_id, $pj_id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO project_requests (user_id, project_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $pj_id);
        $stmt->execute();
        logActivity($conn, $user_id, "Đã gửi yêu cầu tham gia dự án: $pj_title (ID $pj_id)");
        echo "<script>alert('Đã gửi yêu cầu! Chờ duyệt.'); window.location.href='dashboard.php';</script>";
    }
}

// Duyệt / Từ chối yêu cầu tham gia dự án của mình
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['handle_request'])) {
    $req_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    $stmt_info = $conn->prepare("SELECT user_id, project_id FROM project_requests WHERE id = ?");
    $stmt_info->bind_param("i", $req_id);
    $stmt_info->execute();
    $req_data = $stmt_info->get_result()->fetch_assoc();
    
    if ($req_data) {
        $target_user = $req_data['user_id'];
        $target_project = $req_data['project_id'];
        
        $check_owner = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ? AND role = 'owner'");
        $check_owner->bind_param("ii", $user_id, $target_project);
        $check_owner->execute();
        
        if ($check_owner->get_result()->num_rows > 0) {
            if ($action == 'approve') {
                $assigned_role = isset($_POST['role']) ? $_POST['role'] : 'contributor';
                if (!in_array($assigned_role, ['viewer', 'contributor', 'moderator'])) $assigned_role = 'contributor';

                $check_member = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ?");
                $check_member->bind_param("ii", $target_user, $target_project);
                $check_member->execute();
                
                if ($check_member->get_result()->num_rows == 0) {
                    $stmt_add = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, ?)");
                    $stmt_add->bind_param("iis", $target_user, $target_project, $assigned_role);
                    
                    if ($stmt_add->execute()) {
                        $conn->query("UPDATE project_requests SET status = 'approved' WHERE id = $req_id");
                        logActivity($conn, $user_id, "Đã duyệt user $target_user vào dự án $target_project quyền $assigned_role");
                        // logActivity($conn, $user_id, "Đã **duyệt** user $target_email vào dự án '$pj_title' (ID $target_project) với quyền **$assigned_role**");
                        echo "<script>alert('Đã thêm thành viên: " . ucfirst($assigned_role) . "');</script>";
                    }
                } else {
                    $conn->query("UPDATE project_requests SET status = 'approved' WHERE id = $req_id");
                    echo "<script>alert('Người dùng này đã là thành viên của dự án rồi!');</script>";
                }
            } else {
                $conn->query("UPDATE project_requests SET status = 'rejected' WHERE id = $req_id");
                logActivity($conn, $user_id, "Đã **từ chối** yêu cầu tham gia dự án '$pj_title' (ID $target_project) của user $target_email.");
                echo "<script>alert('Đã từ chối yêu cầu.');</script>";
            }
        }
    }
}

// Thêm thành viên trực tiếp
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member_direct'])) {
    $pj_id = $_POST['project_id'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $check_owner = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ? AND role = 'owner'");
    $check_owner->bind_param("ii", $user_id, $pj_id);
    $check_owner->execute();

    if ($check_owner->get_result()->num_rows > 0) {
        $stmt_u = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_u->bind_param("s", $email);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        
        if ($res_u->num_rows > 0) {
            $target_uid = $res_u->fetch_assoc()['id'];
            $check_exist = $conn->prepare("SELECT * FROM project_members WHERE user_id = ? AND project_id = ?");
            $check_exist->bind_param("ii", $target_uid, $pj_id);
            $check_exist->execute();
            
            if ($check_exist->get_result()->num_rows == 0) {
                $stmt_add = $conn->prepare("INSERT INTO project_members (user_id, project_id, role) VALUES (?, ?, ?)");
                $stmt_add->bind_param("iis", $target_uid, $pj_id, $role);
                $stmt_add->execute();
                logActivity($conn, $user_id, "Đã thêm trực tiếp user **$email** vào dự án '$pj_title' (ID $pj_id) với quyền **$role**.");                echo "<script>alert('Đã thêm thành viên thành công!');</script>";
            } else {
                echo "<script>alert('Người dùng này đã có trong dự án!');</script>";
            }
        } else {
            echo "<script>alert('Không tìm thấy email người dùng này!');</script>";
        }
    }
}

// Xóa dự án 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_project'])) {
    $pj_id = $_POST['project_id'];
    $check_owner = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ? AND role = 'owner'");
    $check_owner->bind_param("ii", $user_id, $pj_id);
    $check_owner->execute();
    
    if ($check_owner->get_result()->num_rows > 0) {
        $stmt_del = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt_del->bind_param("i", $pj_id);
        if ($stmt_del->execute()) {
            logActivity($conn, $user_id, "Đã xóa dự án ID $pj_id");
            echo "<script>alert('Đã xóa dự án!'); window.location.href='dashboard.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Bạn không có quyền xóa dự án này!');</script>";
    }
}

// Cập nhật quyền cho các thành viên trong dự án
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_member_role'])) {
    $pj_id = $_POST['project_id'];
    $target_email = $_POST['target_email'];
    $new_role = $_POST['role'];

    $check_owner = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ? AND role = 'owner'");
    $check_owner->bind_param("ii", $user_id, $pj_id);
    $check_owner->execute();

    if ($check_owner->get_result()->num_rows > 0) {
        $stmt_u = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_u->bind_param("s", $target_email);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();

        if ($res_u->num_rows > 0) {
            $target_uid = $res_u->fetch_assoc()['id'];
            if ($target_uid != $user_id) {
                $stmt_update = $conn->prepare("UPDATE project_members SET role = ? WHERE user_id = ? AND project_id = ?");
                $stmt_update->bind_param("sii", $new_role, $target_uid, $pj_id);
                if ($stmt_update->execute()) {
                    logActivity($conn, $user_id, "Đã cập nhật quyền user **$target_email** trong dự án '$pj_title' (ID $pj_id) từ **$old_role** thành **$new_role**.");
                    echo "<script>alert('Đã cập nhật quyền thành công!');</script>";
                }
            } else {
                echo "<script>alert('Bạn không thể tự thay đổi quyền của chính mình!');</script>";
            }
        }
    } else {
        echo "<script>alert('Bạn không có quyền thực hiện thao tác này!');</script>";
    }
}

// Quản lý comment của Owner và Moderator
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_comment'])) {
        $pj_id = $_POST['project_id'];
        $content = htmlspecialchars($_POST['content']);
        $stmt = $conn->prepare("SELECT role FROM project_members WHERE user_id=? AND project_id=?");
        $stmt->bind_param("ii", $user_id, $pj_id);
        $stmt->execute();
        $role_data = $stmt->get_result()->fetch_assoc();

        // Lấy tên dl để ghi vào log
        $stmt_pj_title = $conn->prepare("SELECT title FROM projects WHERE id = ?");
        $stmt_pj_title->bind_param("i", $pj_id);
        $stmt_pj_title->execute();
        $pj_title = $stmt_pj_title->get_result()->fetch_assoc()['title'];

        if ($role_data && in_array($role_data['role'], ['owner', 'moderator', 'contributor'])) {
            $my_role = strtolower(trim($role_data['role']));
            $is_approved = ($my_role === 'owner' || $my_role === 'moderator') ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, content, is_approved) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $pj_id, $user_id, $content, $is_approved);
            if ($stmt->execute()) {
                $cmt_id = $conn->insert_id;
                $status_log = ($is_approved == 1) ? "Đã bình luận công khai" : "Đã bình luận (**Chờ duyệt**)";
                logActivity($conn, $user_id, "$status_log vào dự án '$pj_title' (ID $pj_id). Comment ID: $cmt_id");
            }
        }
    }
    if (isset($_POST['approve_comment'])) {
        $cmt_id = $_POST['comment_id'];
        $pj_id = $_POST['project_id'];

        // Lấy thông tin cmt để ghi vào log
        $stmt_cmt_info = $conn->prepare("
            SELECT u.email, p.title as pj_title 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            JOIN projects p ON c.project_id = p.id 
            WHERE c.id = ?
        ");

        $check = $conn->prepare("SELECT role FROM project_members WHERE user_id=? AND project_id=? AND role='owner'");
        $check->bind_param("ii", $user_id, $pj_id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $stmt_up = $conn->prepare("UPDATE comments SET is_approved = 1 WHERE id = ?");
            $stmt_up->bind_param("i", $cmt_id);
           if ($stmt_up->execute()) {
                logActivity($conn, $user_id, "Đã **duyệt** bình luận ID $cmt_id (của user $author_email) trong dự án '$pj_title'.");
                header("Location: dashboard.php?view_project_id=" . $pj_id);
                exit;
            }
        }
    }
    
    // Xóa comment (Chỉ Owner và Moderator)
    if (isset($_POST['delete_comment'])) {
        $cmt_id = $_POST['comment_id'];
        // Lấy thông tin comment + role của người cmt
        $stmt_get = $conn->prepare("
            SELECT c.project_id, c.user_id, pm.role as author_role 
            FROM comments c 
            LEFT JOIN project_members pm ON c.user_id = pm.user_id AND c.project_id = pm.project_id 
            WHERE c.id = ?
        ");
        $stmt_get->bind_param("i", $cmt_id);
        $stmt_get->execute();
        $cmt = $stmt_get->get_result()->fetch_assoc();
        
        if ($cmt) {
            $pj_id = $cmt['project_id'];
            $author_id = $cmt['user_id'];
            $author_role = strtolower(trim($cmt['author_role'] ?? 'viewer')); // Default viewer nếu không tìm thấy role
            
            // Lấy role của người đang thực hiện xóa (Bản thân)
            $stmt_role = $conn->prepare("SELECT role FROM project_members WHERE user_id = ? AND project_id = ?");
            $stmt_role->bind_param("ii", $user_id, $pj_id);
            $stmt_role->execute();
            $my_role_data = $stmt_role->get_result()->fetch_assoc();
            $my_role = strtolower(trim($my_role_data['role'] ?? ''));
            
            $allowed = false;
            
            // 1. Chính chủ được xóa
            if ($user_id == $author_id) {
                $allowed = true;
            }
            // 2. Owner được xóa tất cả
            elseif ($my_role == 'owner') {
                $allowed = true;
            }
            // 3. Moderator được xóa Contributor và Viewer (Không được xóa Owner, Moderator)
            elseif ($my_role == 'moderator') {
                if (in_array($author_role, ['contributor', 'viewer'])) {
                    $allowed = true;
                }
            }

            if ($allowed) {
                $conn->query("DELETE FROM comments WHERE id = $cmt_id");
                header("Location: dashboard.php?view_project_id=" . $pj_id);
                exit;
            }
        }
    }
    
    if (isset($_POST['edit_comment'])) {
        $cmt_id = $_POST['comment_id'];
        $new_content = htmlspecialchars($_POST['content']);
        $stmt_get = $conn->prepare("SELECT project_id, user_id FROM comments WHERE id = ?");
        $stmt_get->bind_param("i", $cmt_id);
        $stmt_get->execute();
        $cmt = $stmt_get->get_result()->fetch_assoc();

        // Lấy tên dự án để ghi vào log
        $stmt_pj_title = $conn->prepare("SELECT title FROM projects WHERE id = ?");
        $stmt_pj_title->bind_param("i", $cmt['project_id']);
        $stmt_pj_title->execute();
        $pj_title = $stmt_pj_title->get_result()->fetch_assoc()['title'];

        if ($cmt && $user_id == $cmt['user_id']) {
            $stmt_up = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
            $stmt_up->bind_param("si", $new_content, $cmt_id);
            if ($stmt_up->execute()) {
                logActivity($conn, $user_id, "Đã **sửa** bình luận ID $cmt_id trong dự án '$pj_title' (ID {$cmt['project_id']}).");
                header("Location: dashboard.php?view_project_id=" . $cmt['project_id']);
                exit;
            }
        } else {
            logActivity($conn, $user_id, "Thất bại: Cố gắng sửa bình luận ID $cmt_id của người khác trong dự án '$pj_title'.");
        }
    }
}

// Lấy dữ liệu
$search_query = "";
$all_projects = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = trim($_GET['q']);
    $term = "%" . $search_query . "%";
    $stmt_search = $conn->prepare("SELECT * FROM projects WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt_search->bind_param("ss", $term, $term);
    $stmt_search->execute();
    $all_projects = $stmt_search->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $all_projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
}

$stmt_my_pj = $conn->prepare("SELECT p.id, p.title FROM projects p JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?");
$stmt_my_pj->bind_param("i", $user_id);
$stmt_my_pj->execute();
$my_projects_list = $stmt_my_pj->get_result()->fetch_all(MYSQLI_ASSOC);

$view_project_id = isset($_GET['view_project_id']) ? $_GET['view_project_id'] : null;
$projects_to_show = [];

if ($view_project_id) {
    $stmt_one = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt_one->bind_param("i", $view_project_id);
    $stmt_one->execute();
    $res_one = $stmt_one->get_result();
    if($res_one->num_rows > 0) {
        $projects_to_show[] = $res_one->fetch_assoc();
    }
} else {
    $projects_to_show = $all_projects;
}

$my_roles = [];
$stmt = $conn->prepare("SELECT project_id, role FROM project_members WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $my_roles[$row['project_id']] = strtolower(trim($row['role']));

$my_requests = [];
$stmt = $conn->prepare("SELECT project_id, status FROM project_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $my_requests[$row['project_id']] = $row['status'];

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

// Cập nhật lấy cmt để lấy cả role người viết
$comments = $conn->query("
    SELECT c.*, u.email, pm.role as author_role 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    LEFT JOIN project_members pm ON c.user_id = pm.user_id AND c.project_id = pm.project_id
    ORDER BY c.created_at ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Project Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --bg-light: #f3f4f6;
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #1f2937;
        }
        .navbar { background: #ffffff; height: 70px; border-bottom: 1px solid #e5e7eb; }
        .navbar-brand { font-weight: 700; color: var(--primary-color) !important; width: var(--sidebar-width); text-align: center; }
        .search-bar input { background-color: #f3f4f6; border: none; width: 350px; transition: all 0.3s; }
        .search-bar input:focus { background-color: #fff; box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2); }
        
        .sidebar { width: var(--sidebar-width); background: #fff; position: fixed; top: 70px; bottom: 0; left: 0; border-right: 1px solid #e5e7eb; padding: 20px 15px; overflow-y: auto; z-index: 1000; }
        .main-content { margin-left: var(--sidebar-width); margin-top: 70px; padding: 30px; }
        .nav-link { color: #4b5563; border-radius: 8px; padding: 10px 15px; font-weight: 500; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background-color: #eff6ff; color: var(--primary-color); }

        .project-card { border: none; border-radius: 16px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; text-decoration: none; color: inherit; }
        .project-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .project-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-right: 15px; }
        .icon-blue { background: #dbeafe; color: #2563eb; }
        .icon-purple { background: #f3e8ff; color: #9333ea; }
        .icon-green { background: #dcfce7; color: #16a34a; }

        .badge-role { font-size: 0.7rem; padding: 4px 8px; border-radius: 6px; text-transform: uppercase; font-weight: 600; }
        .badge-owner { background: #fee2e2; color: #dc2626; }
        .badge-mod { background: #e0e7ff; color: #4f46e5; }
        .badge-contrib { background: #dcfce7; color: #16a34a; }
        .badge-viewer { background: #f3f4f6; color: #6b7280; }

        .description-box { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 20px; }
        .chat-bubble { background: #fff; padding: 12px 16px; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 10px; position: relative; }
        .chat-bubble.mine { background: #eff6ff; border-color: #bfdbfe; margin-left: 20%; }
        .chat-bubble.other { margin-right: 20%; }

        @media (max-width: 768px) { .sidebar { display: none; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <nav class="navbar fixed-top px-3">
        <div class="d-flex align-items-center">
            <a class="navbar-brand text-start" href="dashboard.php">
                <i class="fa-solid fa-cube me-2"></i> SPCK Manager
            </a>
            <button class="btn btn-light d-md-none ms-2"><i class="fa-solid fa-bars"></i></button>
        </div>

        <div class="search-bar d-none d-md-block mx-auto">
            <form action="dashboard.php" method="GET" class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="q" class="form-control rounded-pill ps-5" 
                       placeholder="Tìm kiếm dự án, tasks..." 
                       value="<?= htmlspecialchars($search_query) ?>">
            </form>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <span class="d-none d-sm-inline small fw-bold"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item small text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <nav class="sidebar">
        <div class="d-grid mb-4">
            <button class="btn btn-primary fw-bold py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                <i class="fa-solid fa-plus me-2"></i> Tạo Dự Án
            </button>
        </div>
        <small class="text-uppercase text-muted fw-bold ps-3 mb-2 d-block" style="font-size: 0.7rem;">Menu Chính</small>
        <div class="nav flex-column mb-4">
            <a href="dashboard.php" class="nav-link <?= (!$view_project_id && $search_query == '') ? 'active' : '' ?>">
                <i class="fa-solid fa-border-all me-2"></i> Tổng quan
            </a>
        </div>
        <small class="text-uppercase text-muted fw-bold ps-3 mb-2 d-block" style="font-size: 0.7rem;">Dự án của bạn</small>
        <div class="nav flex-column">
            <?php foreach($my_projects_list as $mp): ?>
                <a class="nav-link <?= ($view_project_id == $mp['id']) ? 'active' : '' ?>" href="dashboard.php?view_project_id=<?= $mp['id'] ?>">
                    <i class="fa-regular fa-folder me-2"></i> <?= htmlspecialchars($mp['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0 text-dark">
                <?php if($view_project_id): ?>
                    <a href="dashboard.php" class="text-muted text-decoration-none fw-normal">Dự án /</a> Chi tiết
                <?php elseif($search_query): ?>
                    Kết quả tìm kiếm: "<?= htmlspecialchars($search_query) ?>"
                <?php else: ?>
                    Tất cả dự án
                <?php endif; ?>
            </h4>
            <?php if($search_query): ?>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">Xóa tìm kiếm</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($pending_requests) && !$view_project_id): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center rounded-3 mb-4">
                <i class="fa-solid fa-bell me-3 text-warning fa-lg"></i>
                <div class="flex-grow-1">
                    <strong>Cần phê duyệt:</strong> Có <?= count($pending_requests) ?> yêu cầu tham gia dự án của bạn.
                </div>
                <?php $req = $pending_requests[0]; ?>
                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="handle_request" value="1">
                    <input type="hidden" name="request_id" value="<?= $req['req_id'] ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-dark">Duyệt nhanh 1</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (empty($projects_to_show)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-magnifying-glass fa-2x mb-3 opacity-25"></i>
                <p>Không tìm thấy dự án nào.</p>
            </div>
        <?php else: ?>
            
            <?php if (!$view_project_id): ?>
                <div class="row g-4">
                    <?php foreach ($projects_to_show as $pj): 
                        $pj_id = $pj['id'];
                        $my_role = $my_roles[$pj_id] ?? null;
                        
                        $icon_bg = ($pj_id % 2 == 0) ? 'icon-blue' : (($pj_id % 3 == 0) ? 'icon-green' : 'icon-purple');
                        $icon_class = ($pj_id % 2 == 0) ? 'fa-layer-group' : 'fa-cube';
                        
                        $badge_cls = match($my_role) {
                            'owner' => 'badge-owner', 'moderator' => 'badge-mod', 'contributor' => 'badge-contrib', default => 'badge-viewer'
                        };
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="project-card p-4 h-100 d-flex flex-column position-relative">
                            <div class="d-flex align-items-start mb-3">
                                <div class="project-icon <?= $icon_bg ?>">
                                    <i class="fa-solid <?= $icon_class ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($pj['title']) ?></h5>
                                    <div class="text-muted small"><i class="fa-regular fa-clock me-1"></i> <?= date('d/m/Y', strtotime($pj['created_at'])) ?></div>
                                </div>
                                <?php if($my_role): ?>
                                    <span class="badge-role <?= $badge_cls ?>"><?= ucfirst($my_role) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle small d-flex align-items-center justify-content-center border border-white" style="width:24px;height:24px;font-size:10px;">A</div>
                                    <div class="bg-primary text-white rounded-circle small d-flex align-items-center justify-content-center border border-white ms-n2" style="width:24px;height:24px;font-size:10px;">B</div>
                                    <span class="ms-2 small text-muted">Thành viên</span>
                                </div>
                                <?php if ($my_role): ?>
                                    <a href="dashboard.php?view_project_id=<?= $pj_id ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold stretched-link">
                                        Vào dự án <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <?php if (isset($my_requests[$pj_id]) && $my_requests[$pj_id] == 'pending'): ?>
                                        <button class="btn btn-sm btn-secondary rounded-pill disabled">Đã xin vào</button>
                                    <?php else: ?>
                                        <form method="POST" class="position-relative z-2">
                                            <input type="hidden" name="request_access" value="1">
                                            <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill">Xin tham gia</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: 
                // Chi tiết dự án khi ấn vào
                $pj = $projects_to_show[0];
                $pj_id = $pj['id'];
                $my_role = $my_roles[$pj_id] ?? null;
                $can_access = in_array($my_role, ['owner', 'moderator', 'contributor', 'viewer']);
            ?>
                
                <?php if($can_access): ?>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="description-box shadow-sm">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="project-icon icon-blue me-3"><i class="fa-solid fa-layer-group"></i></div>
                                    <div>
                                        <h3 class="fw-bold m-0"><?= htmlspecialchars($pj['title']) ?></h3>
                                        <span class="badge bg-secondary">Project ID: #<?= $pj_id ?></span>
                                    </div>
                                </div>
                                <h6 class="text-uppercase text-muted fw-bold small mb-2">Mô tả dự án</h6>
                                <p class="text-secondary" style="line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($pj['description'])) ?>
                                </p>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="m-0 fw-bold"><i class="fa-regular fa-comments me-2"></i>Thảo luận nhóm</h6>
                                </div>
                                <div class="card-body bg-light" style="max-height: 400px; overflow-y: auto;">
                                    <?php 
                                    $has_cmt = false;
                                    foreach ($comments as $cmt):
                                        if ($cmt['project_id'] == $pj_id):
                                            $is_approved = $cmt['is_approved'];
                                            $is_mine = ($cmt['user_id'] == $user_id);
                                            
                                            // Lấy role của tác giả comment
                                            $author_role = strtolower(trim($cmt['author_role'] ?? 'viewer'));
                                            
                                            if ($is_approved == 1 || $my_role == 'owner' || $my_role == 'moderator' || $is_mine):
                                                $has_cmt = true;
                                                $bubble_cls = $is_mine ? 'mine' : 'other';
                                                
                                                // Nút xóa cho Moderator
                                                $can_delete = false;
                                                if ($is_mine) $can_delete = true;
                                                elseif ($my_role == 'owner') $can_delete = true;
                                                elseif ($my_role == 'moderator' && in_array($author_role, ['contributor', 'viewer'])) $can_delete = true;
                                    ?>
                                        <div class="chat-bubble <?= $bubble_cls ?> shadow-sm">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="fw-bold <?= $is_mine ? 'text-primary' : 'text-dark' ?>"><?= htmlspecialchars($cmt['email']) ?></small>
                                                <small class="text-muted" style="font-size:0.7em"><?= date('H:i d/m', strtotime($cmt['created_at'])) ?></small>
                                            </div>
                                            <div class="text-dark"><?= htmlspecialchars($cmt['content']) ?></div>
                                            <div class="mt-2 d-flex justify-content-end gap-2 opacity-50 hover-opacity-100">
                                                <?php if($is_approved == 0): ?><span class="badge bg-warning text-dark me-auto">Chờ duyệt</span><?php endif; ?>
                                                <?php if($is_approved == 0 && $my_role == 'owner'): ?>
                                                    <form method="POST" class="m-0"><input type="hidden" name="approve_comment" value="1"><input type="hidden" name="comment_id" value="<?= $cmt['id'] ?>"><input type="hidden" name="project_id" value="<?= $pj_id ?>"><button class="btn btn-link p-0 text-success"><i class="fa-solid fa-check"></i></button></form>
                                                <?php endif; ?>
                                                
                                                <?php if($can_delete): ?>
                                                    <form method="POST" class="m-0" onsubmit="return confirm('Xóa?');"><input type="hidden" name="delete_comment" value="1"><input type="hidden" name="comment_id" value="<?= $cmt['id'] ?>"><button class="btn btn-link p-0 text-danger"><i class="fa-solid fa-trash"></i></button></form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; endif; endforeach; ?>
                                    <?php if(!$has_cmt) echo "<p class='text-center text-muted small my-5'>Chưa có tin nhắn nào.</p>"; ?>
                                </div>
                                <div class="card-footer bg-white p-3">
                                    <?php if (in_array($my_role, ['owner', 'moderator', 'contributor'])): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="add_comment" value="1">
                                            <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                            <input type="text" name="content" class="form-control rounded-pill bg-light" placeholder="Nhập tin nhắn..." required>
                                            <button class="btn btn-primary rounded-circle" style="width: 40px; height: 40px;"><i class="fa-solid fa-paper-plane"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-center small text-muted">Chế độ xem (Viewer) không thể chat.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 mb-3">
                                <div class="card-header bg-white fw-bold py-3">Thành viên</div>
                                <ul class="list-group list-group-flush">
                                    <?php
                                    // Sắp xếp thành viên theo vai trò trong nhóm
                                    $stmt_mem = $conn->prepare("SELECT u.email, pm.role FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.project_id = ? ORDER BY FIELD(pm.role, 'owner', 'moderator', 'contributor', 'viewer')");
                                    $stmt_mem->bind_param("i", $pj_id);
                                    $stmt_mem->execute();
                                    $mems = $stmt_mem->get_result()->fetch_all(MYSQLI_ASSOC);
                                    foreach($mems as $m):
                                        $is_me = ($m['email'] == $_SESSION['user_email']);
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div class="d-flex align-items-center text-truncate" style="max-width: 50%;">
                                            <div class="bg-light rounded-circle text-center me-2" style="width:30px;height:30px;line-height:30px;font-size:0.8em">
                                                <?= strtoupper(substr($m['email'],0,1)) ?>
                                            </div>
                                            <div class="text-truncate">
                                                <span class="small fw-medium"><?= htmlspecialchars($m['email']) ?></span>
                                                <?php if($is_me): ?> <span class="text-muted" style="font-size: 0.7em;">(Bạn)</span> <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center">
                                            <?php 
                                            // Hiện form sửa khi là owner
                                            if ($my_role == 'owner' && $m['role'] != 'owner'): 
                                            ?>
                                                <form method="POST" class="d-flex align-items-center gap-1">
                                                    <input type="hidden" name="update_member_role" value="1">
                                                    <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                                    <input type="hidden" name="target_email" value="<?= $m['email'] ?>">
                                                    
                                                    <select name="role" class="form-select form-select-sm py-0 px-2 shadow-none border-0 bg-light fw-bold text-dark" 
                                                            style="font-size: 0.75rem; width: auto; height: 26px; background-image: none; cursor: pointer; text-align: center;" 
                                                            onchange="this.form.submit()">
                                                        <option value="moderator" <?= $m['role'] == 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                                        <option value="contributor" <?= $m['role'] == 'contributor' ? 'selected' : '' ?>>Contributor</option>
                                                        <option value="viewer" <?= $m['role'] == 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                                    </select>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border"><?= ucfirst($m['role']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if($my_role == 'owner'): ?>
                                <div class="card-body border-top">
                                    <h6 class="small fw-bold mb-2">Thêm thành viên</h6>
                                    <form method="POST">
                                        <input type="hidden" name="add_member_direct" value="1">
                                        <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                        <input type="email" name="email" class="form-control form-control-sm mb-2" placeholder="Email..." required>
                                        <div class="d-flex gap-2">
                                            <select name="role" class="form-select form-select-sm">
                                                <option value="contributor">Contributor</option>
                                                <option value="viewer">Viewer</option>
                                            </select>
                                            <button class="btn btn-sm btn-dark">Thêm</button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if($my_role == 'owner'): ?>
                            <div class="card border-danger shadow-sm rounded-4">
                                <div class="card-body">
                                    <h6 class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xóa dự án</h6>
                                    <p class="small text-muted mb-2">Xóa dự án sẽ mất toàn bộ dữ liệu.</p>
                                    <form method="POST" onsubmit="return confirm('Chắc chắn xóa?');">
                                        <input type="hidden" name="delete_project" value="1">
                                        <input type="hidden" name="project_id" value="<?= $pj_id ?>">
                                        <button class="btn btn-outline-danger btn-sm w-100">Xóa Dự Án Này</button>
                                    </form>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">Bạn không có quyền truy cập dự án này.</div>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="createProjectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Dự án mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="create_project" value="1">
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">Tên dự án</label>
                            <input type="text" name="title" class="form-control bg-light" required placeholder="VD: Website Bán Hàng...">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold small text-muted">Mô tả ngắn</label>
                            <textarea name="description" class="form-control bg-light" rows="3" required placeholder="Mục tiêu dự án..."></textarea>
                        </div>
                        <button class="btn btn-primary w-100 py-2 rounded-3 fw-bold">Tạo ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>