<?php
/**
 * Get Warga Data
 * API untuk mendapatkan data warga berdasarkan ID
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include database configuration
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, nama, alamat, no_hp, username FROM users WHERE id = ? AND role = 'warga'");
    $stmt->execute([$id]);
    $warga = $stmt->fetch();

    if ($warga) {
        echo json_encode($warga);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Warga not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID required']);
}
?>
