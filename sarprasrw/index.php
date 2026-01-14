<?php
/**
 * Index Page - Entry Point
 * Halaman utama sistem SARPRAS RW
 */

session_start();

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: warga/dashboard.php');
    }
    exit();
}

// Jika belum login, redirect ke halaman login
header('Location: auth/login.php');
exit();
?>
