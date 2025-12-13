<?php
require 'config.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Email không hợp lệ!";
    }
    elseif (strlen($password) < 6) {
        $error_msg = "Mật khẩu phải có ít nhất 6 ký tự!";
    }
    elseif ($password !== $confirm_password) {
        $error_msg = "Mật khẩu xác nhận không khớp!";
    }
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error_msg = "Email này đã được sử dụng!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Mặc định đăng ký là 'user'
            $stmt = $conn->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'user', 1)");
            $stmt->bind_param("ss", $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Đăng ký thành công!');
                    window.location.href = 'login.php';
                </script>";
                exit();
            } else {
                $error_msg = "Lỗi hệ thống, vui lòng thử lại!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Tài Khoản</title>
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
            width: 100%;
            max-width: 450px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background: rgba(255,255,255,0.9);
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.15);
            border-color: #28a745;
        }
        .btn-register {
            border-radius: 10px;
            padding: 12px;
            background: linear-gradient(to right, #11998e, #38ef7d);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4);
        }
        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="glass-card">
            <div class="text-center mb-4">
                <div class="brand-icon">
                    <i class="fa-solid fa-user-plus fa-2x"></i>
                </div>
                <h4 class="fw-bold text-dark">Tạo tài khoản mới</h4>
            </div>

            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 p-2 small mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary">Nhập lại mật khẩu</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Nhập lại mật khẩu trên">
                </div>
                <button type="submit" class="btn btn-register w-100 mb-3">Đăng Ký</button>
            </form>
            <div class="text-center">
                <span class="text-muted small">Đã có tài khoản?</span> 
                <a href="login.php" class="text-decoration-none fw-bold" style="color: #11998e;">Đăng nhập</a>
            </div>
        </div>
    </div>
</body>
</html>