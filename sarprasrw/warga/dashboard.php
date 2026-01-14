<?php
/**
 * Warga Dashboard
 * Dashboard untuk Warga
 */

session_start();

// Cek apakah user sudah login dan role warga
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'warga') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mendapatkan data warga
function getAllSarpras() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM sarpras WHERE status = 'tersedia' ORDER BY nama ASC");
    return $stmt->fetchAll();
}

function getPeminjamanWarga($user_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, GROUP_CONCAT(CONCAT(s.nama, ' (', dp.jumlah_pinjam, ')') SEPARATOR ', ') as sarpras_list
        FROM peminjaman p
        LEFT JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
        LEFT JOIN sarpras s ON dp.sarpras_id = s.id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getStatusPeminjaman($user_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as jumlah FROM peminjaman WHERE user_id = ? GROUP BY status");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Ambil data untuk dashboard
$sarpras_list = getAllSarpras();
$peminjaman_list = getPeminjamanWarga($_SESSION['user_id']);
$status_peminjaman = getStatusPeminjaman($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Warga SARPRAS RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>SARPRAS RW
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajukan_peminjaman.php"><i class="fas fa-plus-circle me-1"></i>Ajukan Peminjaman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat.php"><i class="fas fa-history me-1"></i>Riwayat</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['nama']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Message -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-user-circle me-2"></i>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!
                        </h4>
                        <p class="card-text">Kelola peminjaman sarana dan prasarana RW Anda dengan mudah.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Peminjaman -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h5>Menunggu</h5>
                        <h3 class="text-warning"><?php echo $status_peminjaman['menunggu'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h5>Disetujui</h5>
                        <h3 class="text-success"><?php echo $status_peminjaman['disetujui'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h5>Ditolak</h5>
                        <h3 class="text-danger"><?php echo $status_peminjaman['ditolak'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-double fa-2x text-info mb-2"></i>
                        <h5>Selesai</h5>
                        <h3 class="text-info"><?php echo $status_peminjaman['selesai'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Daftar Sarpras Tersedia -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Sarpras Tersedia</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($sarpras_list as $sarpras): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <?php if ($sarpras['foto']): ?>
                                                <img src="../assets/img/<?php echo htmlspecialchars($sarpras['foto']); ?>"
                                                     alt="Foto Sarpras" class="img-fluid mb-2" style="height: 150px; object-fit: cover;">
                                            <?php endif; ?>
                                            <h6 class="card-title"><?php echo htmlspecialchars($sarpras['nama']); ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($sarpras['kategori']); ?><br>
                                                    <i class="fas fa-cubes me-1"></i>Jumlah: <?php echo $sarpras['jumlah']; ?><br>
                                                    <i class="fas fa-info-circle me-1"></i>Kondisi: <?php echo ucfirst($sarpras['kondisi']); ?>
                                                </small>
                                            </p>
                                            <a href="ajukan_peminjaman.php?sarpras_id=<?php echo $sarpras['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-hand-paper me-1"></i>Ajukan Pinjam
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peminjaman Terbaru -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Peminjaman Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($peminjaman_list)): ?>
                            <p class="text-muted">Belum ada riwayat peminjaman</p>
                        <?php else: ?>
                            <?php foreach ($peminjaman_list as $peminjaman): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block">
                                        <?php echo date('d/m/Y H:i', strtotime($peminjaman['created_at'])); ?>
                                    </small>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($peminjaman['sarpras_list']); ?></strong><br>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?> -
                                                <?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php
                                            echo $peminjaman['status'] == 'menunggu' ? 'warning' :
                                                 ($peminjaman['status'] == 'disetujui' ? 'success' :
                                                  ($peminjaman['status'] == 'ditolak' ? 'danger' : 'info'));
                                        ?>">
                                            <?php echo ucfirst($peminjaman['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="ajukan_peminjaman.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ajukan Peminjaman
                            </a>
                            <a href="riwayat.php" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>Lihat Riwayat
                            </a>
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
