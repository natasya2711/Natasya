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
$token_valid = false;
$email = '';

// Cek token dari URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verifikasi token
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $token_valid = true;
        $row = $result->fetch_assoc();
        $email = $row['email'];
    } else {
        $error_message = "Token tidak valid atau sudah kedaluwarsa.";
    }
    $stmt->close();
} else {
    $error_message = "Token tidak ditemukan.";
}

// Proses reset password
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password minimal 6 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } else {
        // Verifikasi token sekali lagi
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $reset_email = $row['email'];
            
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password user
            $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $reset_email);
            
            if ($stmt->execute()) {
                // Hapus token setelah berhasil reset
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->bind_param("s", $reset_email);
                $stmt->execute();
                
                $success_message = "Password berhasil diubah! Anda akan diarahkan ke halaman login.";
                $token_valid = false; // Disable form
            } else {
                $error_message = "Gagal mengubah password. Silakan coba lagi.";
            }
        } else {
            $error_message = "Token tidak valid atau sudah kedaluwarsa.";
            $token_valid = false;
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
    <title>Reset Password - Catatan Keuangan Pribadi</title>
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

        .reset-container {
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

        .reset-header {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .reset-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .reset-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .reset-form {
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
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .btn {
            background: linear-gradient(45deg, #f39c12, #e67e22);
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
            box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3);
        }

        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #f39c12;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #e67e22;
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

        .email-info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #2196f3;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .reset-container {
                margin: 10px;
            }
            
            .reset-header {
                padding: 30px 20px;
            }
            
            .reset-form {
                padding: 30px 20px;
            }
            
            .reset-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>🔐 Reset Password</h1>
            <p>Buat password baru untuk akun Anda</p>
        </div>
        
        <div class="reset-form">
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
            
            <?php if ($token_valid && empty($success_message)): ?>
                <div class="email-info">
                    <strong>📧 Reset untuk:</strong> <?php echo htmlspecialchars($email); ?>
                </div>
                
                <form method="POST" action="" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    
                    <div class="form-group">
                        <label for="new_password">🔒 Password Baru</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Masukkan password baru" required>
                        <div class="password-requirements">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">🔒 Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required>
                    </div>
                    
                    <button type="submit" class="btn" id="submitBtn">
                        <span class="btn-text">💾 Simpan Password Baru</span>
                        <span class="btn-loading" style="display: none;">⏳ Memproses...</span>
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <div class="back-link">
                <p><a href="login.php">← Kembali ke Login</a></p>
                <?php if (!empty($success_message)): ?>
                    <p style="margin-top: 10px;"><a href="register.php">Buat Akun Baru</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($token_valid && empty($success_message)): ?>
            document.getElementById('new_password').focus();
            <?php endif; ?>
            
            const form = document.getElementById('resetForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.querySelector('.btn-text');
            const btnLoading = document.querySelector('.btn-loading');
            
            if (form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    btnText.style.display = 'none';
                    btnLoading.style.display = 'inline';
                });
            }
            
            // Validasi password match secara real-time
            const confirmPasswordInput = document.getElementById('confirm_password');
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const password = document.getElementById('new_password').value;
                    const confirmPassword = this.value;
                    
                    if (confirmPassword && password !== confirmPassword) {
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.style.borderColor = '#e9ecef';
                    }
                });
            }
        });

        // Auto redirect setelah sukses
        <?php if (!empty($success_message)): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>