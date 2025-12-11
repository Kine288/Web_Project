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
            $error_msg = "Tài khoản của bạn đã bị KHÓA. Vui lòng liên hệ Admin!";
        } 
        elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role']; 

            logActivity($conn, $user['id'], "Đăng nhập thành công");

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error_msg = "Mật khẩu sai!";
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
    <title>Đăng Nhập Hệ Thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-login {
            background: #764ba2;
            border: none;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #5b3a7d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-user-circle fa-3x text-primary"></i>
                        <h3 class="mt-2">Đăng Nhập</h3>
                    </div>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fa-solid fa-triangle-exclamation"></i> <?= $error_msg ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required placeholder="Nhập email...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-login py-2">Đăng Nhập</button>
                    </form>
                    <div class="text-center mt-3">
                        <p class="small">Chưa có tài khoản? <a href="register.php" class="text-decoration-none fw-bold">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>