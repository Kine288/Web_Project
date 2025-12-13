<?php
session_start();
require 'config.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (isset($user['status']) && $user['status'] == 0) {
            $error_msg = "Tài khoản bị khóa. Liên hệ Admin!";
        } 
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role']; 
            logActivity($conn, $user['id'], "Đăng nhập thành công");
            if ($user['role'] == 'admin') { header("Location: admin.php"); } 
            else { header("Location: dashboard.php"); }
            exit;
        } else {
            $error_msg = "Mật khẩu không đúng!";
        }
    } else {
        $error_msg = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập | Project Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 2.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background: rgba(255,255,255,0.9);
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
            border-color: #8bb9fe;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 12px;
            background: #4e54c8;
            border: none;
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.4);
        }
        .brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="glass-card">
                    <div class="text-center mb-4">
                        <div class="brand-icon">
                            <i class="fa-solid fa-layer-group fa-2x"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Chào mừng trở lại!</h4>
                        <p class="text-muted small">Vui lòng đăng nhập để tiếp tục</p>
                    </div>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger d-flex align-items-center rounded-3 p-2 small mb-3">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_msg ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Nhập email">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Đăng Nhập</button>
                    </form>
                    <div class="text-center">
                        <span class="text-muted small">Chưa có tài khoản?</span> 
                        <a href="register.php" class="text-decoration-none fw-bold" style="color: #4e54c8;">Đăng ký ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>