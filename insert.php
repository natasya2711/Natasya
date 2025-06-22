<?php
session_start();
require 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $user_name = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $category_name = trim($_POST['category_name']);
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];

    // Validasi data
    if (empty($user_name) || empty($category_name) || empty($type) || $amount <= 0 || empty($transaction_date)) {
        die("Data tidak lengkap atau tidak valid. <a href='index.php'>Kembali</a>");
    }

    // Insert dengan user_id
    $stmt = $conn->prepare("INSERT INTO FinanceRecords (user_id, user_name, email, category_name, type, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdss", $user_id, $user_name, $email, $category_name, $type, $amount, $description, $transaction_date);

    if ($stmt->execute()) {
        // Redirect ke halaman utama dengan parameter success
        header("Location: index.php?success=1&tab=daftar-transaksi");
        exit();
    } else {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
                .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #dc3545; }
                .btn { background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; transition: all 0.3s ease; }
                .btn:hover { background: #0056b3; transform: translateY(-2px); }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='error'>
                    <h3>❌ Gagal Menyimpan Data</h3>
                    <p>Error: " . htmlspecialchars($stmt->error) . "</p>
                </div>
                <a href='index.php' class='btn'>🔙 Kembali</a>
            </div>
        </body>
        </html>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika bukan POST request, redirect ke halaman utama
    header("Location: index.php");
    exit();
}
?>