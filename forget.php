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
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error_message = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } else {
        // Cek apakah email ada di database
        $stmt = $conn->prepare("SELECT id, username FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Generate token untuk reset password
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token berlaku 1 jam
            
            // Simpan token ke database
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires = ?");
            $stmt->bind_param("sssss", $email, $token, $expires, $token, $expires);
            
            if ($stmt->execute()) {
                // Dalam implementasi nyata, Anda akan mengirim email dengan link reset
                // Untuk demo, kami akan menampilkan link reset
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                
                $success_message = "Link reset password telah dibuat. Dalam implementasi nyata, link ini akan dikirim ke email Anda:<br><br>
                                 <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; word-break: break-all; font-family: monospace; font-size: 0.9rem;'>
                                 <a href='" . $reset_link . "' target='_blank'>" . $reset_link . "</a>
                                 </div><br>
                                 <small><strong>Catatan:</strong> Link ini berlaku selama 1 jam.</small>";
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi.";
            }
        } else {
            // Untuk keamanan, tetap tampilkan pesan sukses meskipun email tidak ditemukan
            $success_message = "Jika email terdaftar, link reset password akan dikirim ke email Anda.";
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
    <title>Lupa Password - Catatan Keuangan Pribadi</title>
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

        .forgot-container {
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

        .forgot-header {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .forgot-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .forgot-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .forgot-form {
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
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
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
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
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
            line-height: 1.5;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #c0392b;
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

        .info-box {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #2196f3;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .forgot-container {
                margin: 10px;
            }
            
            .forgot-header {
                padding: 30px 20px;
            }
            
            .forgot-form {
                padding: 30px 20px;
            }
            
            .forgot-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>🔑 Lupa Password</h1>
            <p>Reset password Anda</p>
        </div>
        
        <div class="forgot-form">
            <?php if (empty($success_message)): ?>
                <div class="info-box">
                    <strong>ℹ️ Informasi:</strong> Masukkan email yang terdaftar untuk mendapatkan link reset password.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <strong>✅ Berhasil:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($success_message)): ?>
            <form method="POST" action="" id="forgotForm">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <span class="btn-text">🔄 Kirim Link Reset</span>
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
            <?php if (empty($success_message)): ?>
            document.getElementById('email').focus();
            <?php endif; ?>
            
            const form = document.getElementById('forgotForm');
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
        });
    </script>
</body>
</html>