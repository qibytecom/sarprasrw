<?php
/**
 * Database Connection Test
 * File untuk testing koneksi database
 */

echo "<h1>Test Koneksi Database SARPRAS RW</h1>";

// Include database configuration
require_once 'config/database.php';

try {
    $pdo = getDB();
    echo "<p style='color: green;'>✅ Koneksi database berhasil!</p>";

    // Test query untuk cek tabel users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "<p>📊 Total users: " . $result['total'] . "</p>";

    // Test query untuk cek tabel sarpras
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sarpras");
    $result = $stmt->fetch();
    echo "<p>📦 Total sarpras: " . $result['total'] . "</p>";

    // Test query untuk cek tabel peminjaman
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman");
    $result = $stmt->fetch();
    echo "<p>📋 Total peminjaman: " . $result['total'] . "</p>";

    echo "<p style='color: green;'>✅ Semua tabel database tersedia!</p>";
    echo "<p><a href='index.php'>➡️ Ke halaman utama</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p style='color: orange;'>⚠️ Pastikan:</p>";
    echo "<ul>";
    echo "<li>MySQL service sudah berjalan di XAMPP</li>";
    echo "<li>Database 'sarpras_rw' sudah di-import dari file database.sql</li>";
    echo "<li>Username dan password database sudah benar (default: root, kosong)</li>";
    echo "</ul>";
}
?>
