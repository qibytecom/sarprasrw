<?php
/**
 * Logout Script
 * Script untuk logout dari sistem SARPRAS RW
 */

session_start();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit();
?>
