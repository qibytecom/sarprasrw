<?php
/**
 * Get Peminjaman Detail
 * API untuk mendapatkan detail peminjaman
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

// Include database configuration
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as nama_warga, u.no_hp
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $peminjaman = $stmt->fetch();

    if ($peminjaman) {
        // Get detail sarpras
        $stmt2 = $pdo->prepare("
            SELECT dp.*, s.nama as nama_sarpras, s.kategori, s.foto
            FROM detail_peminjaman dp
            JOIN sarpras s ON dp.sarpras_id = s.id
            WHERE dp.peminjaman_id = ?
        ");
        $stmt2->execute([$id]);
        $details = $stmt2->fetchAll();

        // Generate HTML content
        $html = '
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Warga:</strong> ' . htmlspecialchars($peminjaman['nama_warga']) . '<br>
                    <strong>No HP:</strong> ' . htmlspecialchars($peminjaman['no_hp']) . '<br>
                    <strong>Status:</strong> <span class="badge bg-' .
                        ($peminjaman['status'] == 'menunggu' ? 'warning' :
                         ($peminjaman['status'] == 'disetujui' ? 'success' :
                          ($peminjaman['status'] == 'ditolak' ? 'danger' : 'info'))) . '">' .
                        ucfirst($peminjaman['status']) . '</span>
                </div>
                <div class="col-md-6">
                    <strong>Tanggal Pinjam:</strong> ' . date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])) . '<br>
                    <strong>Tanggal Kembali:</strong> ' . date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])) . '<br>
                    <strong>Diajukan:</strong> ' . date('d/m/Y H:i', strtotime($peminjaman['created_at'])) . '
                </div>
            </div>
            <hr>
            <h6>Sarpras yang Dipinjam:</h6>
            <div class="row">';

        foreach ($details as $detail) {
            $html .= '
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            ' . ($detail['foto'] ? '<img src="../assets/img/' . htmlspecialchars($detail['foto']) . '" alt="Foto" class="img-fluid mb-2" style="max-height: 100px;">' : '') . '
                            <h6>' . htmlspecialchars($detail['nama_sarpras']) . '</h6>
                            <small class="text-muted">
                                Kategori: ' . htmlspecialchars($detail['kategori']) . '<br>
                                Jumlah: ' . $detail['jumlah_pinjam'] . '
                            </small>
                        </div>
                    </div>
                </div>';
        }

        $html .= '</div>';

        echo $html;
    } else {
        echo '<div class="alert alert-danger">Peminjaman tidak ditemukan</div>';
    }
} else {
    echo '<div class="alert alert-warning">ID peminjaman tidak valid</div>';
}
?>
