<?php
/**
 * Admin Dashboard
 * Dashboard untuk Admin RW
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mendapatkan statistik
function getTotalSarpras() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sarpras");
    return $stmt->fetch()['total'];
}

function getTotalPeminjaman() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman");
    return $stmt->fetch()['total'];
}

function getPeminjamanAktif() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'disetujui'");
    return $stmt->fetch()['total'];
}

function getTotalWarga() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga'");
    return $stmt->fetch()['total'];
}

function getRecentPeminjaman() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as nama_warga
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSarprasByStatus() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT status, COUNT(*) as jumlah FROM sarpras GROUP BY status");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Ambil data statistik
$total_sarpras = getTotalSarpras();
$total_peminjaman = getTotalPeminjaman();
$peminjaman_aktif = getPeminjamanAktif();
$total_warga = getTotalWarga();
$recent_peminjaman = getRecentPeminjaman();
$sarpras_status = getSarprasByStatus();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin SARPRAS RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-building me-2"></i>SARPRAS RW</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a href="sarpras.php"><i class="fas fa-boxes me-2"></i>Sarpras</a></li>
            <li><a href="warga.php"><i class="fas fa-users me-2"></i>Warga</a></li>
            <li><a href="peminjaman.php"><i class="fas fa-clipboard-list me-2"></i>Peminjaman</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h4>
                <div>
                    <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Sarpras</h6>
                                    <h2 class="mb-0"><?php echo $total_sarpras; ?></h2>
                                </div>
                                <i class="fas fa-boxes fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Peminjaman</h6>
                                    <h2 class="mb-0"><?php echo $total_peminjaman; ?></h2>
                                </div>
                                <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Peminjaman Aktif</h6>
                                    <h2 class="mb-0"><?php echo $peminjaman_aktif; ?></h2>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Warga</h6>
                                    <h2 class="mb-0"><?php echo $total_warga; ?></h2>
                                </div>
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Peminjaman -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Peminjaman Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Warga</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_peminjaman as $peminjaman): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($peminjaman['nama_warga']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $peminjaman['status'] == 'menunggu' ? 'warning' :
                                                             ($peminjaman['status'] == 'disetujui' ? 'success' :
                                                              ($peminjaman['status'] == 'ditolak' ? 'danger' : 'info'));
                                                    ?>">
                                                        <?php echo ucfirst($peminjaman['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sarpras Status -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Sarpras</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($sarpras_status as $status => $jumlah): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?php echo ucfirst($status); ?></span>
                                    <span class="badge bg-<?php
                                        echo $status == 'tersedia' ? 'success' :
                                             ($status == 'dipinjam' ? 'warning' : 'danger');
                                    ?>"><?php echo $jumlah; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="sarpras.php" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Sarpras
                                </a>
                                <a href="warga.php" class="btn btn-outline-success">
                                    <i class="fas fa-user-plus me-2"></i>Tambah Warga
                                </a>
                                <a href="peminjaman.php" class="btn btn-outline-warning">
                                    <i class="fas fa-list me-2"></i>Kelola Peminjaman
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
