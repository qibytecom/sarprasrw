<?php
/**
 * Database Configuration
 * File konfigurasi koneksi database untuk sistem SARPRAS RW
 */

// Konfigurasi database
$host = 'localhost';
$dbname = 'sarpras_rw';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password (kosong)

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set error mode ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}

/**
 * Fungsi untuk mendapatkan koneksi database
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}

/**
 * Fungsi untuk menutup koneksi database
 */
function closeDB() {
    global $pdo;
    $pdo = null;
}
?>
