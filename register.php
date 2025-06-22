<?php
session_start();
require 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    
    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi!";
    } elseif (strlen($username) < 3) {
        $error_message = "Username minimal 3 karakter!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username sudah digunakan!";
        } else {
            // Hash password dan simpan user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO Users (username, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success_message = "Akun berhasil dibuat! Silakan login.";
            } else {
                $error_message = "Gagal membuat akun. Silakan coba lagi.";
            }
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
    <title>Register - Catatan Keuangan Pribadi</title>
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

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
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

        .register-header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .register-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .register-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .register-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #dc3545;
            font-weight: 500;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
            font-weight: 500;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            color: #6c757d;
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

        .password-requirements {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }

        @media (max-width: 480px) {
            .register-container {
                margin: 10px;
            }
            
            .register-header {
                padding: 30px 20px;
            }
            
            .register-form {
                padding: 30px 20px;
            }
            
            .register-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Daftar Akun</h1>
            <p>Buat akun baru untuk memulai</p>
        </div>
        
        <div class="register-form">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <strong>✅ Berhasil:</strong> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($success_message)): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" placeholder="Minimal 3 karakter" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">📧 Email (Opsional)</label>
                    <input type="email" id="email" name="email" placeholder="email@example.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <div class="password-requirements">Minimal 6 karakter</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">🔒 Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                </div>
                
                <button type="submit" class="btn">🚀 Daftar Sekarang</button>
            </form>
            <?php endif; ?>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <div class="login-link">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script>
        // Focus pada input username saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (empty($success_message)): ?>
            document.getElementById('username').focus();
            <?php endif; ?>
        });

        // Validasi password match secara real-time
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e9ecef';
            }
        });

        // Auto redirect ke login setelah registrasi berhasil
        <?php if (!empty($success_message)): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>