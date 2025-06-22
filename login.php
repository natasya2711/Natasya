<?php
session_start();
require 'config.php';
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password harus diisi!";
    } else {
        // Cek user di database
        $stmt = $conn->prepare("SELECT id, username, password FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Username atau password salah!";
            }
        } else {
            $error_message = "Username atau password salah!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Catatan Keuangan Pribadi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .login-form {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-block;
            margin: 5px 10px;
        }
        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        .forgot-password-link {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password-link a {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .forgot-password-link a:hover {
            color: #495057;
            text-decoration: underline;
        }
        .divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
            color: #6c757d;
            font-size: 14px;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
            z-index: 1;
        }
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            .login-header {
                padding: 20px;
            }
            .login-form {
                padding: 20px;
            }
            .register-link a {
                display: block;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>💰 Catatan Keuangan</h1>
            <p>Kelola keuangan pribadi Anda dengan mudah</p>
        </div>
        
        <form class="login-form" method="POST" action="">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    ⚠️ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Masukkan username Anda"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Masukkan password Anda"
                       required>
            </div>
            
            <div class="forgot-password-link">
                <a href="forget.php">🔑 Lupa password?</a>
            </div>
            
            <br>
            
            <button type="submit" class="btn-login">
                🔐 Masuk
            </button>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <div class="register-link">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </div>
        </form>
    </div>

    <script>
        // Auto focus pada input username saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Animasi shake untuk error
        <?php if (!empty($error_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.login-container');
            container.style.animation = 'shake 0.5s ease-in-out';
        });
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
        <?php endif; ?>
    </script>
</body>
</html>