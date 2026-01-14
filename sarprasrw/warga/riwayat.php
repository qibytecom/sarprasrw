<?php
/**
 * Riwayat Peminjaman - Warga
 * Halaman untuk melihat riwayat peminjaman warga
 */

session_start();

// Cek apakah user sudah login dan role warga
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'warga') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mendapatkan riwayat peminjaman warga
function getRiwayatPeminjaman($user_id, $limit = null) {
    $pdo = getDB();
    $sql = "
        SELECT p.*, GROUP_CONCAT(CONCAT(s.nama, ' (', dp.jumlah_pinjam, ')') SEPARATOR ', ') as sarpras_list,
               GROUP_CONCAT(CONCAT('Kondisi: ', COALESCE(dp.kondisi_kembali, '-'), ', Catatan: ', COALESCE(dp.catatan, '-')) SEPARATOR '; ') as detail_kembali
        FROM peminjaman p
        LEFT JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
        LEFT JOIN sarpras s ON dp.sarpras_id = s.id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ";

    if ($limit) {
        $sql .= " LIMIT $limit";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Ambil data riwayat
$riwayat_list = getRiwayatPeminjaman($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Warga SARPRAS RW</title>
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
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajukan_peminjaman.php"><i class="fas fa-plus-circle me-1"></i>Ajukan Peminjaman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="riwayat.php"><i class="fas fa-history me-1"></i>Riwayat</a>
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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Peminjaman</h5>
                        <a href="ajukan_peminjaman.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Ajukan Peminjaman Baru
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($riwayat_list)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada riwayat peminjaman</h5>
                                <p class="text-muted">Anda belum pernah mengajukan peminjaman sarpras.</p>
                                <a href="ajukan_peminjaman.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Ajukan Peminjaman Sekarang
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Sarpras</th>
                                            <th>Status</th>
                                            <th>Detail Pengembalian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($riwayat_list as $riwayat): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($riwayat['tanggal_pinjam'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($riwayat['tanggal_kembali'])); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($riwayat['sarpras_list']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $riwayat['status'] == 'menunggu' ? 'warning' :
                                                             ($riwayat['status'] == 'disetujui' ? 'success' :
                                                              ($riwayat['status'] == 'ditolak' ? 'danger' : 'info'));
                                                    ?>">
                                                        <?php echo ucfirst($riwayat['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($riwayat['status'] == 'selesai' && $riwayat['detail_kembali']): ?>
                                                        <button class="btn btn-sm btn-info" onclick="showDetail('<?php echo htmlspecialchars($riwayat['detail_kembali']); ?>')">
                                                            <i class="fas fa-eye"></i> Lihat
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        function showDetail(detail) {
            const details = detail.split('; ');
            let html = '<div class="list-group">';

            details.forEach(item => {
                html += `<div class="list-group-item">
                    <small class="text-muted">${item}</small>
                </div>`;
            });

            html += '</div>';

            document.getElementById('detailContent').innerHTML = html;

            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        }
    </script>
</body>
</html>
