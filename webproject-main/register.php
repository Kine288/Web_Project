<?php
require 'config.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Email không hợp lệ!";
    }
    elseif (strlen($password) < 6) {
        $error_msg = "Mật khẩu phải có ít nhất 6 ký tự!";
    }
    else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_msg = "Email này đã được đăng ký!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, password, status) VALUES (?, ?, 1)"); // Thêm status = 1
            $stmt->bind_param("ss", $email, $hashed_password);

            if ($stmt->execute()) {
                // Dùng JS chuyển hướng để mượt mà hơn
                echo "<script>
                    alert('Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập.');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
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
        .btn-register {
            background: #28a745;
            border: none;
            transition: 0.3s;
        }
        .btn-register:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-user-plus fa-3x text-success"></i>
                        <h3 class="mt-2">Đăng Ký Mới</h3>
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
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-register py-2">Đăng Ký</button>
                    </form>
                    <div class="text-center mt-3">
                        <p class="small">Đã có tài khoản? <a href="login.php" class="text-decoration-none fw-bold">Đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>