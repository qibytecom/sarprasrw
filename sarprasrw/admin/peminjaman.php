<?php
/**
 * Manajemen Peminjaman - Admin
 * Halaman untuk mengelola peminjaman sarpras
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mengelola peminjaman
function getAllPeminjaman() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as nama_warga, u.no_hp
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPeminjamanDetail($peminjaman_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT dp.*, s.nama as nama_sarpras, s.kategori
        FROM detail_peminjaman dp
        JOIN sarpras s ON dp.sarpras_id = s.id
        WHERE dp.peminjaman_id = ?
    ");
    $stmt->execute([$peminjaman_id]);
    return $stmt->fetchAll();
}

function approvePeminjaman($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'disetujui' WHERE id = ?");
    return $stmt->execute([$id]);
}

function rejectPeminjaman($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'ditolak' WHERE id = ?");
    return $stmt->execute([$id]);
}

function returnPeminjaman($id, $details) {
    $pdo = getDB();

    // Update status peminjaman
    $stmt1 = $pdo->prepare("UPDATE peminjaman SET status = 'selesai' WHERE id = ?");
    $stmt1->execute([$id]);

    // Update detail peminjaman dengan kondisi kembali
    foreach ($details as $detail) {
        $stmt2 = $pdo->prepare("
            UPDATE detail_peminjaman
            SET kondisi_kembali = ?, catatan = ?
            WHERE peminjaman_id = ? AND sarpras_id = ?
        ");
        $stmt2->execute([
            $detail['kondisi_kembali'],
            $detail['catatan'],
            $id,
            $detail['sarpras_id']
        ]);

        // Update status sarpras kembali ke tersedia jika kondisi baik
        if ($detail['kondisi_kembali'] == 'baik') {
            $stmt3 = $pdo->prepare("UPDATE sarpras SET status = 'tersedia' WHERE id = ?");
            $stmt3->execute([$detail['sarpras_id']]);
        } else {
            $stmt3 = $pdo->prepare("UPDATE sarpras SET status = 'rusak', kondisi = ? WHERE id = ?");
            $stmt3->execute([$detail['kondisi_kembali'], $detail['sarpras_id']]);
        }
    }

    return true;
}

// Proses form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'approve') {
        $id = (int)$_POST['id'];

        if (approvePeminjaman($id)) {
            $message = 'Peminjaman berhasil disetujui!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menyetujui peminjaman!';
            $message_type = 'danger';
        }
    } elseif ($action == 'reject') {
        $id = (int)$_POST['id'];

        if (rejectPeminjaman($id)) {
            $message = 'Peminjaman berhasil ditolak!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menolak peminjaman!';
            $message_type = 'danger';
        }
    } elseif ($action == 'return') {
        $id = (int)$_POST['id'];
        $details = [];

        // Parse detail peminjaman dari form
        if (isset($_POST['sarpras_id'])) {
            foreach ($_POST['sarpras_id'] as $key => $sarpras_id) {
                $details[] = [
                    'sarpras_id' => $sarpras_id,
                    'kondisi_kembali' => $_POST['kondisi_kembali'][$key],
                    'catatan' => trim($_POST['catatan'][$key])
                ];
            }
        }

        if (returnPeminjaman($id, $details)) {
            $message = 'Pengembalian berhasil diproses!';
            $message_type = 'success';
        } else {
            $message = 'Gagal memproses pengembalian!';
            $message_type = 'danger';
        }
    }
}

// Ambil data peminjaman
$peminjaman_list = getAllPeminjaman();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Admin SARPRAS RW</title>
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
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a href="sarpras.php"><i class="fas fa-boxes me-2"></i>Sarpras</a></li>
            <li><a href="warga.php"><i class="fas fa-users me-2"></i>Warga</a></li>
            <li class="active"><a href="peminjaman.php"><i class="fas fa-clipboard-list me-2"></i>Peminjaman</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-clipboard-list me-2"></i>Manajemen Peminjaman</h4>
                <div>
                    <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Peminjaman Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Warga</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($peminjaman_list as $peminjaman): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($peminjaman['nama_warga']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($peminjaman['no_hp']); ?></small>
                                        </td>
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
                                        <td>
                                            <button class="btn btn-sm btn-info me-1" onclick="viewDetail(<?php echo $peminjaman['id']; ?>)">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <?php if ($peminjaman['status'] == 'menunggu'): ?>
                                                <button class="btn btn-sm btn-success me-1" onclick="approvePeminjaman(<?php echo $peminjaman['id']; ?>)">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectPeminjaman(<?php echo $peminjaman['id']; ?>)">
                                                    <i class="fas fa-times"></i> Tolak
                                                </button>
                                            <?php elseif ($peminjaman['status'] == 'disetujui'): ?>
                                                <button class="btn btn-sm btn-primary" onclick="returnPeminjaman(<?php echo $peminjaman['id']; ?>)">
                                                    <i class="fas fa-undo"></i> Kembalikan
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Peminjaman Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Return Peminjaman Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Konfirmasi Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body" id="returnContent">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Konfirmasi Pengembalian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check me-2"></i>Konfirmasi Persetujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyetujui peminjaman ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="id" id="approve_id">
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times me-2"></i>Konfirmasi Penolakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menolak peminjaman ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id" id="reject_id">
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        function viewDetail(id) {
            fetch(`get_peminjaman_detail.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }

        function approvePeminjaman(id) {
            document.getElementById('approve_id').value = id;
            const modal = new bootstrap.Modal(document.getElementById('approveModal'));
            modal.show();
        }

        function rejectPeminjaman(id) {
            document.getElementById('reject_id').value = id;
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        function returnPeminjaman(id) {
            fetch(`get_peminjaman_return.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('returnContent').innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('returnModal'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
