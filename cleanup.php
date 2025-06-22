<?php
// File ini bisa dijalankan via cron job untuk membersihkan token yang expired
// Contoh cron job: 0 */6 * * * /usr/bin/php /path/to/cleanup_expired_tokens.php

require_once 'config.php';

try {
    // Hapus token yang sudah expired
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE expires < NOW()");
    $stmt->execute();
    
    $deleted_count = $stmt->affected_rows;
    
    echo "Cleanup completed. Deleted {$deleted_count} expired tokens.\n";
    
    // Log ke file jika diperlukan
    $log_message = date('Y-m-d H:i:s') . " - Cleanup: {$deleted_count} expired tokens deleted\n";
    file_put_contents('cleanup.log', $log_message, FILE_APPEND | LOCK_EX);
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    
    // Log error
    $log_message = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents('cleanup.log', $log_message, FILE_APPEND | LOCK_EX);
}

$conn->close();
?>