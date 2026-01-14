<?php
/**
 * Get Sarpras Data
 * API untuk mendapatkan data sarpras berdasarkan ID
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
    $stmt = $pdo->prepare("SELECT * FROM sarpras WHERE id = ?");
    $stmt->execute([$id]);
    $sarpras = $stmt->fetch();

    if ($sarpras) {
        echo json_encode($sarpras);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Sarpras not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID required']);
}
?>
