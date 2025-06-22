<?php 
session_start();
require 'config.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Keuangan Pribadi</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .user-info {
            position: absolute;
            top: 20px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-welcome {
            font-size: 1rem;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 15px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .tab-button {
            flex: 1;
            padding: 20px;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6c757d;
        }

        .tab-button.active {
            background: white;
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
        }

        .tab-button:hover {
            background: #e9ecef;
        }

        .tab-content {
            display: none;
            padding: 40px;
            animation: fadeIn 0.3s ease-in;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .amount {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .income {
            color: #27ae60;
        }

        .expense {
            color: #e74c3c;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border-left: 5px solid #3498db;
        }

        .summary-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .summary-card .amount {
            font-size: 1.5rem;
        }

        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 50px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .container {
                margin: 10px;
            }
            
            .tab-content {
                padding: 20px;
            }

            .user-info {
                position: static;
                justify-content: center;
                margin-top: 15px;
            }

            .header {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                <span class="user-welcome">👋 Selamat datang, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
            </div>
            <h1>💰 Catatan Keuangan Pribadi</h1>
            <p>Kelola keuangan Anda dengan mudah dan efisien</p>
        </div>

        <div class="tabs">
            <button class="tab-button active" onclick="openTab(event, 'tambah-transaksi')">
                📝 Tambah Transaksi
            </button>
            <button class="tab-button" onclick="openTab(event, 'daftar-transaksi')">
                📊 Daftar Transaksi
            </button>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div class="success-message" id="successMessage">
            <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px 40px; border-radius: 8px; border-left: 5px solid #28a745;">
                <strong>✅ Berhasil!</strong> Transaksi telah disimpan dengan sukses.
            </div>
        </div>
        <?php endif; ?>

        <!-- Tab Tambah Transaksi -->
        <div id="tambah-transaksi" class="tab-content active">
            <h2 style="margin-bottom: 30px; color: #2c3e50;">✨ Tambah Transaksi Baru</h2>
            
            <form method="POST" action="insert.php" style="max-width: 800px; margin: 0 auto;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user_name">👤 Nama</label>
                        <input type="text" id="user_name" name="user_name" placeholder="Masukkan nama Anda" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">📧 Email</label>
                        <input type="email" id="email" name="email" placeholder="email@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_name">🏷️ Kategori</label>
                        <input type="text" id="category_name" name="category_name" placeholder="Contoh: Makanan, Transport, Gaji" required>
                    </div>
                    <div class="form-group">
                        <label for="type">💱 Jenis Transaksi</label>
                        <select id="type" name="type" required>
                            <option value="Pemasukan">💚 Pemasukan</option>
                            <option value="Pengeluaran">❤️ Pengeluaran</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">💵 Jumlah (Rp)</label>
                        <input type="number" id="amount" name="amount" placeholder="0" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="transaction_date">📅 Tanggal Transaksi</label>
                        <input type="date" id="transaction_date" name="transaction_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">📝 Deskripsi</label>
                    <textarea id="description" name="description" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                </div>

                <button type="submit" class="btn">💾 Simpan Transaksi</button>
            </form>
        </div>

        <!-- Tab Daftar Transaksi -->
        <div id="daftar-transaksi" class="tab-content">
            <h2 style="margin-bottom: 30px; color: #2c3e50;">📈 Ringkasan & Daftar Transaksi</h2>
            
            <?php
            // Hitung ringkasan untuk user yang sedang login
            $summary_query = "SELECT 
                SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) as total_expense,
                COUNT(*) as total_transactions
                FROM FinanceRecords 
                WHERE user_id = ?";
            $stmt = $conn->prepare($summary_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $summary_result = $stmt->get_result();
            $summary = $summary_result->fetch_assoc();
            
            $total_income = $summary['total_income'] ?? 0;
            $total_expense = $summary['total_expense'] ?? 0;
            $balance = $total_income - $total_expense;
            $total_transactions = $summary['total_transactions'] ?? 0;
            ?>

            <div class="summary">
                <div class="summary-card">
                    <h3>💚 Total Pemasukan</h3>
                    <div class="amount income">Rp <?= number_format($total_income, 0, ',', '.') ?></div>
                </div>
                <div class="summary-card">
                    <h3>❤️ Total Pengeluaran</h3>
                    <div class="amount expense">Rp <?= number_format($total_expense, 0, ',', '.') ?></div>
                </div>
                <div class="summary-card">
                    <h3>💰 Saldo</h3>
                    <div class="amount <?= $balance >= 0 ? 'income' : 'expense' ?>">
                        Rp <?= number_format($balance, 0, ',', '.') ?>
                    </div>
                </div>
                <div class="summary-card">
                    <h3>📊 Total Transaksi</h3>
                    <div class="amount" style="color: #3498db;"><?= $total_transactions ?></div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>👤 Nama</th>
                            <th>🏷️ Kategori</th>
                            <th>💱 Jenis</th>
                            <th>💵 Jumlah</th>
                            <th>📅 Tanggal</th>
                            <th>📝 Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tampilkan transaksi hanya untuk user yang sedang login
                        $stmt = $conn->prepare("SELECT * FROM FinanceRecords WHERE user_id = ? ORDER BY transaction_date DESC, created_at DESC");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $amount_class = $row['type'] == 'Pemasukan' ? 'income' : 'expense';
                                $type_icon = $row['type'] == 'Pemasukan' ? '💚' : '❤️';
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['user_name']) . "</td>
                                    <td>" . htmlspecialchars($row['category_name']) . "</td>
                                    <td>{$type_icon} " . htmlspecialchars($row['type']) . "</td>
                                    <td class='amount {$amount_class}'>Rp " . number_format($row['amount'], 0, ',', '.') . "</td>
                                    <td>" . date('d/m/Y', strtotime($row['transaction_date'])) . "</td>
                                    <td>" . htmlspecialchars($row['description'] ?: '-') . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='no-data'>Belum ada transaksi yang tercatat</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tabbuttons;
            
            // Sembunyikan semua tab content
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            // Hilangkan class active dari semua tab button
            tabbuttons = document.getElementsByClassName("tab-button");
            for (i = 0; i < tabbuttons.length; i++) {
                tabbuttons[i].classList.remove("active");
            }
            
            // Tampilkan tab yang dipilih dan set button sebagai active
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // Set tanggal hari ini sebagai default
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('transaction_date').value = today;

            // Cek parameter URL untuk membuka tab yang sesuai
            const urlParams = new URLSearchParams(window.location.search);
            const tabToOpen = urlParams.get('tab');
            if (tabToOpen) {
                const tabButton = document.querySelector(`[onclick*="${tabToOpen}"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }

            // Auto-hide success message after 5 seconds
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 300);
                }, 5000);
            }
        });

        // Format input amount dengan pemisah ribuan
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                e.target.setAttribute('data-value', value);
            }
        });
    </script>
</body>
</html>
