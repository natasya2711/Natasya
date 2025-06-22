<?php
$host = "localhost";
$dbname = "PersonalFinanceDB";
$user = "root";
$pass = ""; // ganti jika ada password

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
